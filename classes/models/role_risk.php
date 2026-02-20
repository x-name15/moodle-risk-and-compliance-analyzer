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
 * Role risk data model.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\models;

defined('MOODLE_INTERNAL') || die();

class role_risk {

    /** @var int */
    public $id;
    /** @var int */
    public $scanid;
    /** @var int */
    public $roleid;
    /** @var int */
    public $risk_score;
    /** @var int */
    public $critical_cap_count;
    /** @var int */
    public $timecreated;

    /**
     * Creates from DB record.
     *
     * @param \stdClass $record
     * @return self
     */
    public static function from_record(\stdClass $record): self {
        $obj = new self();
        $obj->id = $record->id ?? 0;
        $obj->scanid = $record->scanid;
        $obj->roleid = $record->roleid;
        $obj->risk_score = $record->risk_score ?? 0;
        $obj->critical_cap_count = $record->critical_cap_count ?? 0;
        $obj->timecreated = $record->timecreated ?? time();
        return $obj;
    }

    /**
     * Saves to database.
     *
     * @return int Record ID.
     */
    public function save(): int {
        global $DB;
        if (empty($this->timecreated)) {
            $this->timecreated = time();
        }
        $record = new \stdClass();
        $record->scanid = $this->scanid;
        $record->roleid = $this->roleid;
        $record->risk_score = $this->risk_score;
        $record->critical_cap_count = $this->critical_cap_count;
        $record->timecreated = $this->timecreated;
        $this->id = $DB->insert_record('local_mrca_role_risks', $record);
        return $this->id;
    }
}
