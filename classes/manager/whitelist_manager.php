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
 * Whitelist manager — manages safe-listed fields.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\manager;

/**
 * Whitelist manager class.
 *
 * Handles the logic for adding, removing, and checking fields that have been
 * manually marked as safe by an administrator to avoid PII false positives.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class whitelist_manager {
    /**
     * Adds a field to the whitelist.
     *
     * @param string $component The component name.
     * @param string $table The database table name.
     * @param string $field The field name.
     * @param int $userid The ID of the user performing the action.
     * @return int The new record ID.
     */
    public function add(string $component, string $table, string $field, int $userid): int {
        global $DB;

        if ($this->is_whitelisted($component, $table, $field)) {
            return 0;
        }

        $record = new \stdClass();
        $record->component = $component;
        $record->table_name = $table;
        $record->field_name = $field;
        $record->userid = $userid;
        $record->timecreated = time();

        return $DB->insert_record('local_mrca_whitelist', $record);
    }

    /**
     * Removes a field from the whitelist.
     *
     * @param int $id The whitelist record ID.
     * @return bool True on success, false otherwise.
     */
    public function remove(int $id): bool {
        global $DB;
        return $DB->delete_records('local_mrca_whitelist', ['id' => $id]);
    }

    /**
     * Checks if a field is whitelisted.
     *
     * @param string $component The component name.
     * @param string $table The database table name.
     * @param string $field The field name.
     * @return bool True if whitelisted, false otherwise.
     */
    public function is_whitelisted(string $component, string $table, string $field): bool {
        global $DB;
        return $DB->record_exists('local_mrca_whitelist', [
            'component' => $component,
            'table_name' => $table,
            'field_name' => $field,
        ]);
    }

    /**
     * Gets all whitelisted items.
     *
     * @return array List of whitelisted records.
     */
    public function get_all(): array {
        global $DB;
        return $DB->get_records('local_mrca_whitelist', null, 'timecreated DESC');
    }
}
