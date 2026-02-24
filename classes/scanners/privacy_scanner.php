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
 * Privacy scanner — PII detection and Privacy API compliance.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\scanners;

use core_component;
use core_plugin_manager;
use xmldb_file;

/**
 * Privacy scanner class.
 *
 * Handles PII detection in database structures and Privacy API compliance checks.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class privacy_scanner {
    /** @var array Keywords that might indicate PII. */
    private const PII_KEYWORDS = [
        'email', 'phone', 'mobile', 'address', 'city', 'country',
        'firstname', 'lastname', 'fullname', 'username', 'password',
        'ip', 'ipaddress', 'token', 'secret', 'auth', 'passport',
        'dni', 'ssn', 'birth', 'dob', 'gender', 'sex', 'location',
        'gps', 'lat', 'long', 'card', 'bank', 'account', 'creditcard',
    ];

    /**
     * Returns a list of all installed plugins.
     *
     * @return array List of plugin info objects grouped by type.
     */
    public function get_all_plugins(): array {
        $pluginman = core_plugin_manager::instance();
        return $pluginman->get_plugins();
    }

    /**
     * Checks if a plugin implements the Privacy API metadata provider.
     *
     * @param string $component The component name (e.g., 'mod_forum').
     * @return bool
     */
    public function has_privacy_provider(string $component): bool {
        $classname = "\\$component\\privacy\\provider";
        if (class_exists($classname)) {
            $rc = new \ReflectionClass($classname);
            return $rc->implementsInterface(\core_privacy\local\metadata\provider::class);
        }
        return false;
    }

    /**
     * Scans a specific plugin for privacy compliance indicators.
     *
     * @param string $component
     * @return array
     */
    public function scan_plugin(string $component): array {
        return [
            'component' => $component,
            'has_privacy_provider' => $this->has_privacy_provider($component),
        ];
    }

    /**
     * Scans the database structure of a plugin for potential PII fields.
     *
     * @param string $component The component name.
     * @return array List of suspicious fields.
     */
    public function scan_db_structure(string $component): array {
        $dir = core_component::get_component_directory($component);
        $dbfile = $dir . '/db/install.xml';

        $suspiciousfields = [];
        $whitelistmanager = new \local_mrca\manager\whitelist_manager();

        if (file_exists($dbfile)) {
            $xmldbfile = new xmldb_file($dbfile);
            if ($xmldbfile->loadXMLStructure()) {
                $structure = $xmldbfile->getStructure();
                if ($structure) {
                    $tables = $structure->getTables();
                    foreach ($tables as $table) {
                        $tablename = $table->getName();
                        $fields = $table->getFields();
                        foreach ($fields as $field) {
                            $fieldname = $field->getName();

                            if ($whitelistmanager->is_whitelisted($component, $tablename, $fieldname)) {
                                continue;
                            }

                            if ($this->is_suspicious($fieldname)) {
                                $suspiciousfields[] = [
                                    'table' => $tablename,
                                    'field' => $fieldname,
                                    'component' => $component,
                                    'reason' => get_string('reason_keyword_match', 'local_mrca'),
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $suspiciousfields;
    }

    /**
     * Checks if a field name matches PII heuristics.
     *
     * @param string $fieldname
     * @return bool
     */
    private function is_suspicious(string $fieldname): bool {
        foreach (self::PII_KEYWORDS as $keyword) {
            if (stripos($fieldname, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks the actual content of suspicious fields to determine if they are encrypted.
     *
     * @param array $suspiciousfields
     * @param \local_mrca\heuristics\crypto_analyzer $cryptoanalyzer
     * @return array Enriched suspicious fields with 'is_encrypted' flag.
     */
    public function check_content_analysis(
        array $suspiciousfields,
        \local_mrca\heuristics\crypto_analyzer $cryptoanalyzer
    ): array {
        global $DB;

        foreach ($suspiciousfields as &$finding) {
            $table = $finding['table'];
            $field = $finding['field'];

            $finding['is_encrypted'] = false;

            try {
                if (!$DB->get_manager()->table_exists($table)) {
                    continue;
                }

                $records = $DB->get_records($table, null, '', $field, 0, 5);

                $encryptedcount = 0;
                $totalcount = 0;

                foreach ($records as $record) {
                    $value = $record->$field;
                    if (empty($value)) {
                        continue;
                    }

                    $totalcount++;
                    if ($cryptoanalyzer->is_encrypted($value)) {
                        $encryptedcount++;
                    }
                }

                if ($totalcount > 0 && ($encryptedcount / $totalcount) > 0.6) {
                    $finding['is_encrypted'] = true;
                    $finding['reason'] .= ' (' . get_string('verified_encrypted', 'local_mrca') . ')';
                } else if ($totalcount > 0) {
                    $finding['reason'] .= ' (' . get_string('verified_plaintext', 'local_mrca') . ')';
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $suspiciousfields;
    }
}
