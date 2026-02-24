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
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\scanners;

defined('MOODLE_INTERNAL') || die();

/**
 * Analyzes plugin file organization and quality.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class structural_scanner {
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
     * Scans a plugin's structural integrity.
     *
     * @param string $component Component name.
     * @return array Structural findings with score.
     */
    public function scan(string $component): array {
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
        $this->check_base_structure($dir, $findings, $score);

        if ($findings['has_version_file']) {
            $this->check_version_metadata($dir, $findings, $score);
        }

        $this->scan_php_sources($dir, $findings, $score);
        $findings['structural_score'] = min($score, 65);

        return $findings;
    }

    /**
     * Checks basic file structure of the plugin.
     *
     * @param string $dir Plugin directory.
     * @param array $findings Findings array.
     * @param int $score Risk score.
     * @return void
     */
    private function check_base_structure(string $dir, array &$findings, int &$score): void {
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
    }

    /**
     * Validates metadata inside version.php.
     *
     * @param string $dir Plugin directory.
     * @param array $findings Findings array.
     * @param int $score Risk score.
     * @return void
     */
    private function check_version_metadata(string $dir, array &$findings, int &$score): void {
        $versioncontent = @file_get_contents($dir . '/version.php');
        if ($versioncontent !== false) {
            if (strpos($versioncontent, '$plugin->maturity') !== false) {
                $findings['has_maturity'] = true;
            } else {
                $score += self::SCORE_NO_MATURITY;
                $findings['issues'][] = get_string('structural_no_maturity', 'local_mrca');
            }

            if (strpos($versioncontent, '$plugin->cron') !== false) {
                $findings['uses_legacy_cron'] = true;
                $score += self::SCORE_LEGACY_CRON;
                $findings['issues'][] = get_string('structural_legacy_cron', 'local_mrca');
            }
        }
    }

    /**
     * Scans PHP files for deprecated and unsafe function calls.
     *
     * @param string $dir Plugin directory.
     * @param array $findings Findings array.
     * @param int $score Risk score.
     * @return void
     */
    private function scan_php_sources(string $dir, array &$findings, int &$score): void {
        $phpfiles = $this->get_php_files($dir);
        $deprecatedscore = 0;

        foreach ($phpfiles as $file) {
            $content = @file_get_contents($file);
            if ($content === false) {
                continue;
            }

            $relative = str_replace($dir . '/', '', $file);
            $deprecatedscore += $this->scan_content($content, $relative, $findings);
        }

        $score += min($deprecatedscore, self::DEPRECATED_CALLS_CAP);
    }

    /**
     * Scans content of a single file.
     *
     * @param string $content
     * @param string $relative
     * @param array $findings
     * @return int
     */
    private function scan_content(string $content, string $relative, array &$findings): int {
        $score = 0;
        foreach (self::DEPRECATED_FUNCTIONS as $func => $reason) {
            if ($this->contains_function_call($content, $func)) {
                $findings['deprecated_calls'][] = ['file' => $relative, 'function' => $func, 'reason' => $reason];
                $score += self::SCORE_DEPRECATED_CALL;
            }
        }
        foreach (self::UNSAFE_PHP_FUNCTIONS as $func => $reason) {
            if ($this->contains_function_call($content, $func)) {
                $findings['unsafe_calls'][] = ['file' => $relative, 'function' => $func, 'reason' => $reason];
                $score += self::SCORE_DEPRECATED_CALL;
            }
        }
        return $score;
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
        if ($depth > 5) {
            return $files;
        }

        $entries = @scandir($dir);
        if ($entries === false) {
            return $files;
        }

        foreach ($entries as $entry) {
            if (in_array($entry, ['.', '..', 'vendor', 'node_modules', '.git'])) {
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

    /**
     * Checks if a file content contains a function call.
     *
     * @param string $content File content.
     * @param string $function Function name.
     * @return bool
     */
    private function contains_function_call(string $content, string $function): bool {
        $pattern = '/(?<!\w)(?<!->)(?<!::)(?<!function\s)' . preg_quote($function, '/') . '\s*\(/';
        if (strpos($content, $function) === false) {
            return false;
        }
        return (bool)preg_match($pattern, $content);
    }
}
