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

/**
 * Site risk class.
 *
 * Model representing the aggregated risk score for the entire Moodle site.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class site_risk {
    /** @var float Site Risk Index 0-100. */
    public $index;

    /** @var string Classification: healthy, low, moderate, high, critical. */
    public $classification;

    /** @var int Total risk points. */
    public $totalriskpoints;

    /** @var int Plugins scanned. */
    public $pluginsscanned;

    /** @var int Roles scanned. */
    public $rolesscanned;

    /**
     * Calculates the site risk from scan data.
     *
     * @param int $totalpluginrisk Sum of all plugin risk scores.
     * @param int $totalrolerisk Sum of all role risk scores.
     * @param int $pluginsscanned Number of plugins scanned.
     * @param int $rolesscanned Number of roles scanned.
     * @return self
     */
    public static function calculate(
        int $totalpluginrisk,
        int $totalrolerisk,
        int $pluginsscanned,
        int $rolesscanned
    ): self {
        $model = new \local_mrca\engine\scoring_model();
        $obj = new self();

        $obj->totalriskpoints = $totalpluginrisk + $totalrolerisk;
        $obj->pluginsscanned = $pluginsscanned;
        $obj->rolesscanned = $rolesscanned;

        // Max possible: each plugin could score ~195 (65*3), each role ~65.
        // But realistically we normalize against a reasonable ceiling.
        $maxplugin = $pluginsscanned * 100; // Reasonable max per plugin.
        $maxrole = $rolesscanned * 65;
        $maxpossible = max($maxplugin + $maxrole, 1);

        $obj->index = $model->calculate_site_risk_index(
            (float)$obj->totalriskpoints,
            (float)$maxpossible
        );
        $obj->classification = $model->classify_site_risk($obj->index);

        return $obj;
    }
}
