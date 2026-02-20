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
 * Privacy API provider for MRCA.
 *
 * Declares what user data MRCA stores and provides export/delete
 * functionality for GDPR compliance.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider implementation for local_mrca.
 *
 * MRCA stores minimal user data: only the userid of administrators
 * who whitelist fields during scans. Scan results, risk scores,
 * and alerts are systemic data not tied to individual users.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider
{

    /**
     * Describes the user data stored by MRCA.
     *
     * @param collection $collection The collection to add metadata to.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection
    {
        $collection->add_database_table('local_mrca_whitelist', [
            'userid' => 'privacy:metadata:whitelist:userid',
            'component' => 'privacy:metadata:whitelist:component',
            'table_name' => 'privacy:metadata:whitelist:table_name',
            'field_name' => 'privacy:metadata:whitelist:field_name',
            'timecreated' => 'privacy:metadata:whitelist:timecreated',
        ], 'privacy:metadata:whitelist');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist
    {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {local_mrca_whitelist} w
                  JOIN {context} ctx ON ctx.instanceid = 0 AND ctx.contextlevel = :contextlevel
                 WHERE w.userid = :userid";

        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_SYSTEM,
            'userid' => $userid,
        ]);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist to populate.
     */
    public static function get_users_in_context(userlist $userlist): void
    {
        $context = $userlist->get_context();

        if (!$context instanceof \context_system) {
            return;
        }

        $sql = "SELECT DISTINCT userid FROM {local_mrca_whitelist}";
        $userlist->add_from_sql('userid', $sql, []);
    }

    /**
     * Export all user data for the specified user in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export for.
     */
    public static function export_user_data(approved_contextlist $contextlist): void
    {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_system) {
                continue;
            }

            $records = $DB->get_records('local_mrca_whitelist', ['userid' => $userid], 'timecreated ASC');

            if (empty($records)) {
                continue;
            }

            $data = [];
            foreach ($records as $record) {
                $data[] = (object)[
                    'component' => $record->component,
                    'table_name' => $record->table_name,
                    'field_name' => $record->field_name,
                    'timecreated' => \core_privacy\local\request\transform::datetime($record->timecreated),
                ];
            }

            writer::with_context($context)->export_data(
            [get_string('pluginname', 'local_mrca'), get_string('whitelist', 'local_mrca')],
                (object)['whitelist_entries' => $data]
            );
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void
    {
        global $DB;

        if (!$context instanceof \context_system) {
            return;
        }

        $DB->delete_records('local_mrca_whitelist');
    }

    /**
     * Delete all data for the specified user in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to delete for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void
    {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_system) {
                continue;
            }

            $DB->delete_records('local_mrca_whitelist', ['userid' => $userid]);
        }
    }

    /**
     * Delete multiple users' data within a single context.
     *
     * @param approved_userlist $userlist The approved userlist to delete for.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void
    {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_system) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $DB->delete_records_select('local_mrca_whitelist', "userid {$insql}", $inparams);
    }
}