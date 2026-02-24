<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Dependency scanner — plugin compatibility and dependency analysis.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\scanners;

use core_plugin_manager;

/**
 * Dependency scanner class.
 *
 * Analyzes plugin requirements, validates dependencies, and detects deprecated APIs.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dependency_scanner
{
    /** @var int Plugins not updated in this many years are flagged. */
    const OUTDATED_THRESHOLD_YEARS = 2;

    /** @var array Known deprecated Moodle API patterns (class/function → replacement). */
    private const DEPRECATED_API_PATTERNS = [
        'events_trigger_legacy' => 'Events 2 API (\\core\\event\\*)',
        'get_context_instance' => 'context_*::instance()',
        'add_to_log' => 'Events API',
        'get_completion_info' => 'New completion API',
        'plagiarism_get_links' => 'plagiarism API v2',
        'print_error' => 'throw new moodle_exception()',
    ];

    /**
     * Scans a plugin's dependency health.
     *
     * @param string $component Component name (e.g. 'mod_forum').
     * @return array Findings with keys: core_mismatch, missing_dependencies, outdated,
     * deprecated_apis, no_recent_update, dependency_count, score_details.
     */
    public function scan(string $component): array {
        $findings = [
            'core_mismatch' => false,
            'missing_dependencies' => [],
            'outdated' => false,
            'deprecated_apis' => [],
            'no_recent_update' => false,
            'dependency_count' => 0,
            'version_gap' => 0,
        ];

        $pluginman = core_plugin_manager::instance();
        $allplugins = $pluginman->get_plugins();

        // Find the plugin info object.
        $plugininfo = null;
        foreach ($allplugins as $type => $pluginsbytype) {
            foreach ($pluginsbytype as $name => $info) {
                if ($info->component === $component) {
                    $plugininfo = $info;
                    break 2;
                }
            }
        }

        if (!$plugininfo) {
            return $findings;
        }

        // 1. Core version compatibility check.
        if (!empty($plugininfo->versionrequires)) {
            $coreversion = get_config('', 'version');
            if ($coreversion < $plugininfo->versionrequires) {
                $findings['core_mismatch'] = true;
            }
        }

        // 2. Check declared dependencies.
        if (!empty($plugininfo->dependencies)) {
            $findings['dependency_count'] = count($plugininfo->dependencies);
            foreach ($plugininfo->dependencies as $depcomponent => $depversion) {
                $depdir = \core_component::get_component_directory($depcomponent);
                if (empty($depdir) || !is_dir($depdir)) {
                    $findings['missing_dependencies'][] = $depcomponent;
                }
            }
        }

        // 3. Check if plugin version is very old.
        if (!empty($plugininfo->versiondb)) {
            // Moodle version timestamps follow YYYYMMDDXX format.
            $versionyear = (int)substr((string)$plugininfo->versiondb, 0, 4);
            $currentyear = (int)date('Y');
            $findings['version_gap'] = $currentyear - $versionyear;
            if ($findings['version_gap'] >= self::OUTDATED_THRESHOLD_YEARS) {
                $findings['outdated'] = true;
                $findings['no_recent_update'] = true;
            }
        }

        // 4. Scan for deprecated API usage in PHP files.
        $dir = \core_component::get_component_directory($component);
        if ($dir && is_dir($dir)) {
            $findings['deprecated_apis'] = $this->detect_deprecated_apis($dir);
        }

        return $findings;
    }

    /**
     * Scans PHP files in a plugin directory for deprecated API calls.
     *
     * @param string $dir Plugin directory path.
     * @return array List of deprecated API usages found.
     */
    private function detect_deprecated_apis(string $dir): array {
        $deprecatedfound = [];
        $files = $this->get_php_files($dir);

        foreach ($files as $file) {
            $content = @file_get_contents($file);
            if ($content === false) {
                continue;
            }

            $relative = str_replace($dir . '/', '', $file);

            foreach (self::DEPRECATED_API_PATTERNS as $pattern => $replacement) {
                if (strpos($content, $pattern) !== false) {
                    // Verify it's not in a comment.
                    $lines = explode("\n", $content);
                    foreach ($lines as $linenum => $line) {
                        $trimmed = ltrim($line);
                        if (
                            strpos($trimmed, '//') === 0 || strpos($trimmed, '*') === 0 ||
                            strpos($trimmed, '/*') === 0
                        ) {
                            continue;
                        }
                        if (strpos($line, $pattern) !== false) {
                            $deprecatedfound[] = [
                                'file' => $relative,
                                'line' => $linenum + 1,
                                'deprecated' => $pattern,
                                'replacement' => $replacement,
                            ];
                            break; // One finding per file per pattern is sufficient.
                        }
                    }
                }
            }
        }

        return $deprecatedfound;
    }

    /**
     * Recursively finds PHP files in a directory.
     *
     * @param string $dir Directory to search.
     * @param int $depth Current recursion depth.
     * @return array List of absolute file paths.
     */
    private function get_php_files(string $dir, int $depth = 0): array {
        $files = [];

        if ($depth > 4) {
            return $files;
        }

        $entries = @scandir($dir);
        if ($entries === false) {
            return $files;
        }

        foreach ($entries as $entry) {
            if (
                $entry === '.' || $entry === '..' || $entry === 'vendor' ||
                $entry === 'node_modules' || $entry === '.git' || $entry === 'tests'
            ) {
                continue;
            }

            $path = $dir . '/' . $entry;

            if (is_dir($path)) {
                $files = array_merge($files, $this->get_php_files($path, $depth + 1));
            } else if (pathinfo($entry, PATHINFO_EXTENSION) === 'php') {
                $files[] = $path;
            }
        }

        return $files;
    }
}
