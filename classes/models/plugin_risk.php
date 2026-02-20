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

defined('MOODLE_INTERNAL') || die();

class plugin_risk {

    /** @var int */
    public $id;
    /** @var int */
    public $scanid;
    /** @var string */
    public $component;
    /** @var int */
    public $privacy_score;
    /** @var int */
    public $dependency_score;
    /** @var int */
    public $capability_score;
    /** @var int */
    public $total_score;
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
        $obj->component = $record->component;
        $obj->privacy_score = $record->privacy_score ?? 0;
        $obj->dependency_score = $record->dependency_score ?? 0;
        $obj->capability_score = $record->capability_score ?? 0;
        $obj->total_score = $record->total_score ?? 0;
        $obj->timecreated = $record->timecreated ?? time();
        return $obj;
    }

    /**
     * Converts to DB record.
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
        $record->privacy_score = $this->privacy_score;
        $record->dependency_score = $this->dependency_score;
        $record->capability_score = $this->capability_score;
        $record->total_score = $this->total_score;
        $record->timecreated = $this->timecreated;
        return $record;
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
        $this->total_score = $this->privacy_score + $this->dependency_score + $this->capability_score;
        $this->id = $DB->insert_record('local_mrca_plugin_risks', $this->to_record());
        return $this->id;
    }
}
