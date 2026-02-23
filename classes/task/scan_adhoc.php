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
 * Ad-hoc scan task.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\task;

defined('MOODLE_INTERNAL') || die();

use core\task\adhoc_task;

class scan_adhoc extends adhoc_task {
    /**
     * Execute the task.
     */
    public function execute() {
        mtrace("MRCA: Starting Ad-hoc Risk Scan...");
        $task = new \local_mrca\task\run_scan();
        $task->execute();
        mtrace("MRCA: Ad-hoc Risk Scan completed.");
    }
}
