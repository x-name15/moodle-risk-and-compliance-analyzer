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
 * Merged from the original plugin_scanner and db_scanner classes.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\scanners;

defined('MOODLE_INTERNAL') || die();

use core_component;
use core_plugin_manager;
use xmldb_file;

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

        $suspicious_fields = [];
        $whitelist_manager = new \local_mrca\manager\whitelist_manager();

        if (file_exists($dbfile)) {
            $xmldb_file = new xmldb_file($dbfile);
            if ($xmldb_file->loadXMLStructure()) {
                $structure = $xmldb_file->getStructure();
                if ($structure) {
                    $tables = $structure->getTables();
                    foreach ($tables as $table) {
                        $tablename = $table->getName();
                        $fields = $table->getFields();
                        foreach ($fields as $field) {
                            $fieldname = $field->getName();

                            if ($whitelist_manager->is_whitelisted($component, $tablename, $fieldname)) {
                                continue;
                            }

                            if ($this->is_suspicious($fieldname)) {
                                $suspicious_fields[] = [
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

        return $suspicious_fields;
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
     * @param array $suspicious_fields
     * @param \local_mrca\heuristics\crypto_analyzer $crypto_analyzer
     * @return array Enriched suspicious fields with 'is_encrypted' flag.
     */
    public function check_content_analysis(
        array $suspicious_fields,
        \local_mrca\heuristics\crypto_analyzer $crypto_analyzer
    ): array {
        global $DB;

        foreach ($suspicious_fields as &$finding) {
            $table = $finding['table'];
            $field = $finding['field'];

            $finding['is_encrypted'] = false;

            try {
                if (!$DB->get_manager()->table_exists($table)) {
                    continue;
                }

                $records = $DB->get_records($table, null, '', $field, 0, 5);

                $encrypted_count = 0;
                $total_count = 0;

                foreach ($records as $record) {
                    $value = $record->$field;
                    if (empty($value)) {
                        continue;
                    }

                    $total_count++;
                    if ($crypto_analyzer->is_encrypted($value)) {
                        $encrypted_count++;
                    }
                }

                if ($total_count > 0 && ($encrypted_count / $total_count) > 0.6) {
                    $finding['is_encrypted'] = true;
                    $finding['reason'] .= ' (' . get_string('verified_encrypted', 'local_mrca') . ')';
                } else if ($total_count > 0) {
                    $finding['reason'] .= ' (' . get_string('verified_plaintext', 'local_mrca') . ')';
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $suspicious_fields;
    }
}
