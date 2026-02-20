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
 * Structural scanner — plugin code structure and quality analysis.
 *
 * Analyzes plugin file organization, detects deprecated function usage,
 * validates version metadata, and checks coding standards compliance.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\scanners;

defined('MOODLE_INTERNAL') || die();

class structural_scanner
{

    /** @var int Score for missing version.php. */
    const SCORE_NO_VERSION_FILE = 15;

    /** @var int Score for missing install.xml when tables exist. */
    const SCORE_NO_DB_SCHEMA = 10;

    /** @var int Score for missing lang directory. */
    const SCORE_NO_LANG = 5;

    /** @var int Score for missing README. */
    const SCORE_NO_README = 3;

    /** @var int Score for missing unit tests. */
    const SCORE_NO_TESTS = 5;

    /** @var int Score per deprecated function call found. */
    const SCORE_DEPRECATED_CALL = 8;

    /** @var int Score for using legacy cron. */
    const SCORE_LEGACY_CRON = 10;

    /** @var int Score for missing maturity declaration. */
    const SCORE_NO_MATURITY = 5;

    /** @var int Maximum score contribution from deprecated calls. */
    const DEPRECATED_CALLS_CAP = 40;

    /** @var array Known deprecated Moodle functions to detect. */
    private const DEPRECATED_FUNCTIONS = [
        'print_object' => 'Use debugging() or var_dump for debugging.',
        'print_header' => 'Replaced by $OUTPUT->header().',
        'print_footer' => 'Replaced by $OUTPUT->footer().',
        'print_heading' => 'Replaced by $OUTPUT->heading().',
        'print_table' => 'Replaced by html_writer::table().',
        'print_simple_box' => 'Deprecated UI function.',
        'choose_from_menu' => 'Replaced by html_writer::select().',
        'helpbutton' => 'Replaced by $OUTPUT->help_icon().',
        'print_recent_activity_note' => 'Deprecated activity function.',
        'get_context_instance' => 'Use context_*::instance() instead.',
        'add_to_log' => 'Replaced by Events API (\\core\\event).',
        'events_trigger' => 'Replaced by Events 2 API.',
        'print_error' => 'Use throw new moodle_exception() instead.',
    ];

    /** @var array PHP functions that should not be used in Moodle plugins. */
    private const UNSAFE_PHP_FUNCTIONS = [
        'eval' => 'Arbitrary code execution risk.',
        'exec' => 'System command execution.',
        'shell_exec' => 'System command execution.',
        'passthru' => 'System command execution.',
        'popen' => 'Process execution.',
        'proc_open' => 'Process execution.',
        'dl' => 'Dynamic extension loading.',
        'mysql_query' => 'Deprecated MySQL extension. Use $DB API.',
        'mysql_connect' => 'Deprecated MySQL extension. Use $DB API.',
        'mysqli_query' => 'Direct DB access. Use Moodle $DB API.',
    ];

    /**
     * Scans a plugin's structural integrity with deep analysis.
     *
     * @param string $component Component name.
     * @return array Structural findings with score.
     */
    public function scan(string $component): array
    {
        $findings = [
            'has_version_file' => false,
            'has_db_schema' => false,
            'has_lang_file' => false,
            'has_readme' => false,
            'has_tests' => false,
            'has_maturity' => false,
            'uses_legacy_cron' => false,
            'deprecated_calls' => [],
            'unsafe_calls' => [],
            'structural_score' => 0,
            'issues' => [],
        ];

        $dir = \core_component::get_component_directory($component);
        if (!$dir || !is_dir($dir)) {
            $findings['structural_score'] = self::SCORE_NO_VERSION_FILE;
            $findings['issues'][] = get_string('structural_no_directory', 'local_mrca');
            return $findings;
        }

        $score = 0;

        // 1. File structure checks.
        $findings['has_version_file'] = file_exists($dir . '/version.php');
        if (!$findings['has_version_file']) {
            $score += self::SCORE_NO_VERSION_FILE;
            $findings['issues'][] = get_string('structural_no_version', 'local_mrca');
        }

        $findings['has_db_schema'] = file_exists($dir . '/db/install.xml');
        $findings['has_lang_file'] = is_dir($dir . '/lang');
        if (!$findings['has_lang_file']) {
            $score += self::SCORE_NO_LANG;
            $findings['issues'][] = get_string('structural_no_lang', 'local_mrca');
        }

        $findings['has_readme'] = file_exists($dir . '/README.md') || file_exists($dir . '/readme.md');
        if (!$findings['has_readme']) {
            $score += self::SCORE_NO_README;
            $findings['issues'][] = get_string('structural_no_readme', 'local_mrca');
        }

        $findings['has_tests'] = is_dir($dir . '/tests');
        if (!$findings['has_tests']) {
            $score += self::SCORE_NO_TESTS;
            $findings['issues'][] = get_string('structural_no_tests', 'local_mrca');
        }

        // 2. Check version.php for maturity and legacy cron.
        if ($findings['has_version_file']) {
            $version_content = @file_get_contents($dir . '/version.php');
            if ($version_content !== false) {
                // Maturity check.
                if (strpos($version_content, '$plugin->maturity') !== false) {
                    $findings['has_maturity'] = true;
                }
                else {
                    $score += self::SCORE_NO_MATURITY;
                    $findings['issues'][] = get_string('structural_no_maturity', 'local_mrca');
                }

                // Legacy cron check.
                if (strpos($version_content, '$plugin->cron') !== false) {
                    $findings['uses_legacy_cron'] = true;
                    $score += self::SCORE_LEGACY_CRON;
                    $findings['issues'][] = get_string('structural_legacy_cron', 'local_mrca');
                }
            }
        }

        // 3. Scan PHP source files for deprecated/unsafe function calls.
        $php_files = $this->get_php_files($dir);
        $deprecated_score = 0;

        foreach ($php_files as $file) {
            $content = @file_get_contents($file);
            if ($content === false) {
                continue;
            }

            $relative = str_replace($dir . '/', '', $file);

            // Check deprecated Moodle functions.
            foreach (self::DEPRECATED_FUNCTIONS as $func => $reason) {
                if ($this->contains_function_call($content, $func)) {
                    $findings['deprecated_calls'][] = [
                        'file' => $relative,
                        'function' => $func,
                        'reason' => $reason,
                    ];
                    $deprecated_score += self::SCORE_DEPRECATED_CALL;
                }
            }

            // Check unsafe PHP functions.
            foreach (self::UNSAFE_PHP_FUNCTIONS as $func => $reason) {
                if ($this->contains_function_call($content, $func)) {
                    $findings['unsafe_calls'][] = [
                        'file' => $relative,
                        'function' => $func,
                        'reason' => $reason,
                    ];
                    $deprecated_score += self::SCORE_DEPRECATED_CALL;
                }
            }
        }

        // Cap deprecated calls score.
        $score += min($deprecated_score, self::DEPRECATED_CALLS_CAP);

        // Cap total structural score at 65.
        $findings['structural_score'] = min($score, 65);

        return $findings;
    }

    /**
     * Recursively finds PHP files in a directory.
     *
     * @param string $dir Directory to search.
     * @param int $depth Current recursion depth.
     * @return array List of absolute file paths.
     */
    private function get_php_files(string $dir, int $depth = 0): array
    {
        $files = [];

        // Limit recursion to avoid scanning massive plugin trees.
        if ($depth > 5) {
            return $files;
        }

        $entries = @scandir($dir);
        if ($entries === false) {
            return $files;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || $entry === 'vendor' ||
            $entry === 'node_modules' || $entry === '.git') {
                continue;
            }

            $path = $dir . '/' . $entry;

            if (is_dir($path)) {
                $files = array_merge($files, $this->get_php_files($path, $depth + 1));
            }
            elseif (pathinfo($entry, PATHINFO_EXTENSION) === 'php') {
                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * Checks if a file content contains a function call (not class/method name or comment).
     *
     * Uses a simple heuristic: looks for the function name followed by '('.
     * Skips matches inside single-line comments or class/method definitions.
     *
     * @param string $content File content.
     * @param string $function Function name.
     * @return bool
     */
    private function contains_function_call(string $content, string $function): bool
    {
        // Pattern: function name followed by opening parenthesis, not preceded by
        // 'function ', '->' or '::' (those would be definitions or method calls on objects).
        $pattern = '/(?<!\w)(?<!->)(?<!::)(?<!function\s)' . preg_quote($function, '/') . '\s*\(/';

        // Quick pre-check to avoid regex on files that don't contain the name at all.
        if (strpos($content, $function) === false) {
            return false;
        }

        // Remove comments to avoid false positives.
        $lines = explode("\n", $content);
        $in_block_comment = false;
        $clean_content = '';

        foreach ($lines as $line) {
            $trimmed = ltrim($line);

            if ($in_block_comment) {
                if (strpos($line, '*/') !== false) {
                    $in_block_comment = false;
                }
                continue;
            }

            if (strpos($trimmed, '/*') === 0 || strpos($trimmed, '/**') === 0) {
                if (strpos($line, '*/') === false) {
                    $in_block_comment = true;
                }
                continue;
            }

            // Skip single-line comments.
            if (strpos($trimmed, '//') === 0) {
                continue;
            }

            $clean_content .= $line . "\n";
        }

        return (bool)preg_match($pattern, $clean_content);
    }
}