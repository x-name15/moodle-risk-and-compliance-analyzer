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

/**
 * Role risk model class.
 *
 * Represents the risk score and findings for a specific Moodle role.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class role_risk {
    /** @var int Internal ID. */
    public $id;

    /** @var int ID of the associated scan. */
    public $scanid;

    /** @var int The Moodle role ID. */
    public $roleid;

    /** @var int Aggregated risk score. */
    public $riskscore;

    /** @var int Number of critical capabilities detected. */
    public $criticalcapcount;

    /** @var int Timestamp when record was created. */
    public $timecreated;

    /**
     * Creates instance from DB record.
     *
     * @param \stdClass $record The database record.
     * @return self
     */
    public static function from_record(\stdClass $record): self {
        $obj = new self();
        $obj->id = $record->id ?? 0;
        $obj->scanid = $record->scanid;
        $obj->roleid = $record->roleid;
        $obj->riskscore = $record->risk_score ?? 0;
        $obj->criticalcapcount = $record->critical_cap_count ?? 0;
        $obj->timecreated = $record->timecreated ?? time();
        return $obj;
    }

    /**
     * Saves the role risk record to database.
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
        $record->risk_score = $this->riskscore;
        $record->critical_cap_count = $this->criticalcapcount;
        $record->timecreated = $this->timecreated;
        $this->id = $DB->insert_record('local_mrca_role_risks', $record);
        return $this->id;
    }
}
