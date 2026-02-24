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
 * Plugin risk data model.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\models;

/**
 * Plugin risk model class.
 *
 * Represents the risk scores for a specific plugin component.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin_risk {
    /** @var int Internal ID. */
    public $id;

    /** @var int ID of the associated scan. */
    public $scanid;

    /** @var string Plugin component name. */
    public $component;

    /** @var int Privacy risk score. */
    public $privacyscore;

    /** @var int Dependency risk score. */
    public $dependencyscore;

    /** @var int Capability risk score. */
    public $capabilityscore;

    /** @var int Total aggregated risk score. */
    public $totalscore;

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
        $obj->component = $record->component;
        $obj->privacyscore = $record->privacy_score ?? 0;
        $obj->dependencyscore = $record->dependency_score ?? 0;
        $obj->capabilityscore = $record->capability_score ?? 0;
        $obj->totalscore = $record->total_score ?? 0;
        $obj->timecreated = $record->timecreated ?? time();
        return $obj;
    }

    /**
     * Converts to DB record format.
     *
     * @return \stdClass
     */
    public function to_record(): \stdClass {
        $record = new \stdClass();
        if (!empty($this->id)) {
            $record->id = $this->id;
        }
        $record->scanid = $this->scanid;
        $record->component = $this->component;
        $record->privacy_score = $this->privacyscore ?? 0;
        $record->dependency_score = $this->dependencyscore ?? 0;
        $record->capability_score = $this->capabilityscore ?? 0;
        $record->total_score = $this->totalscore ?? 0;
        $record->timecreated = $this->timecreated;
        return $record;
    }

    /**
     * Saves the model to the database.
     *
     * @return int Record ID.
     */
    public function save(): int {
        global $DB;
        if (empty($this->timecreated)) {
            $this->timecreated = time();
        }
        $this->totalscore = $this->privacyscore + $this->dependencyscore + $this->capabilityscore;
        $this->id = $DB->insert_record('local_mrca_plugin_risks', $this->to_record());
        return $this->id;
    }
}
