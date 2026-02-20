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
 * Site risk computed model — calculates the normalized 0-100 index.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\models;

defined('MOODLE_INTERNAL') || die();

class site_risk {

    /** @var float Site Risk Index 0-100. */
    public $index;

    /** @var string Classification: healthy, low, moderate, high, critical. */
    public $classification;

    /** @var int Total risk points. */
    public $total_risk_points;

    /** @var int Plugins scanned. */
    public $plugins_scanned;

    /** @var int Roles scanned. */
    public $roles_scanned;

    /**
     * Calculates the site risk from scan data.
     *
     * @param int $total_plugin_risk Sum of all plugin risk scores.
     * @param int $total_role_risk Sum of all role risk scores.
     * @param int $plugins_scanned Number of plugins scanned.
     * @param int $roles_scanned Number of roles scanned.
     * @return self
     */
    public static function calculate(int $total_plugin_risk, int $total_role_risk,
                                      int $plugins_scanned, int $roles_scanned): self {
        $model = new \local_mrca\engine\scoring_model();
        $obj = new self();

        $obj->total_risk_points = $total_plugin_risk + $total_role_risk;
        $obj->plugins_scanned = $plugins_scanned;
        $obj->roles_scanned = $roles_scanned;

        // Max possible: each plugin could score ~195 (65*3), each role ~65.
        // But realistically we normalize against a reasonable ceiling.
        $max_plugin = $plugins_scanned * 100; // Reasonable max per plugin.
        $max_role = $roles_scanned * 65;
        $max_possible = max($max_plugin + $max_role, 1);

        $obj->index = $model->calculate_site_risk_index(
            (float)$obj->total_risk_points,
            (float)$max_possible
        );
        $obj->classification = $model->classify_site_risk($obj->index);

        return $obj;
    }
}
