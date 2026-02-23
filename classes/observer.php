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
 * Event observer — triggers auto-scan on plugin install.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca;

defined('MOODLE_INTERNAL') || die();

class observer {
    /**
     * Observer for \core\event\plugin_enabled.
     * Triggers a scan if the setting is enabled.
     *
     * @param \core\event\plugin_enabled $event
     */
    public static function plugin_enabled(\core\event\plugin_enabled $event) {
        if (get_config('local_mrca', 'autoscan_new_plugins')) {
            $task = new \local_mrca\task\scan_adhoc();
            \core\task\manager::queue_adhoc_task($task);
        }
    }
}
