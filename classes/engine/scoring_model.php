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
 * Scoring model — formal risk computation per REBRAND.MD §5.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\engine;

defined('MOODLE_INTERNAL') || die();

class scoring_model {
    /** @var float Correlation multiplier when both plugin and role risk exceed thresholds. */
    const CORRELATION_MULTIPLIER = 1.5;

    /** @var int Default threshold for triggering correlation multiplier. */
    const DEFAULT_THRESHOLD = 40;

    /**
     * Calculates the total plugin risk score.
     *
     * PluginRisk = PrivacyScore + DependencyScore + CapabilityDefinitionScore
     *
     * @param int $privacy_score
     * @param int $dependency_score
     * @param int $capability_score
     * @return int
     */
    public function calculate_plugin_risk(int $privacy_score, int $dependency_score, int $capability_score): int {
        return $privacy_score + $dependency_score + $capability_score;
    }

    /**
     * Calculates the role-level risk score.
     *
     * RoleRisk = CriticalCapabilityWeight + OverrideWeight + PluginExposureWeight
     *
     * @param int $critical_cap_weight Score from critical capabilities.
     * @param int $override_weight Score from dangerous overrides.
     * @param int $plugin_exposure_weight Score from plugin exposure linkage.
     * @return int
     */
    public function calculate_role_risk(int $critical_cap_weight, int $override_weight, int $plugin_exposure_weight): int {
        return $critical_cap_weight + $override_weight + $plugin_exposure_weight;
    }

    /**
     * Applies the correlation multiplier.
     *
     * If both PluginRisk and RoleRisk exceed the threshold:
     *   FinalRisk = (PluginRisk + RoleRisk) * 1.5
     * Else:
     *   FinalRisk = PluginRisk + RoleRisk
     *
     * @param int $plugin_risk
     * @param int $role_risk
     * @param int $threshold
     * @return float
     */
    public function apply_correlation(int $plugin_risk, int $role_risk, int $threshold = self::DEFAULT_THRESHOLD): float {
        if ($plugin_risk > $threshold && $role_risk > $threshold) {
            return ($plugin_risk + $role_risk) * self::CORRELATION_MULTIPLIER;
        }
        return (float)($plugin_risk + $role_risk);
    }

    /**
     * Calculates the Site Risk Index (0–100).
     *
     * SiteRiskIndex = (Total Risk Points / Maximum Possible Points) * 100
     *
     * @param float $total_risk_points Total accumulated risk.
     * @param float $max_possible_points Maximum theoretical risk for this installation.
     * @return float Normalized 0–100.
     */
    public function calculate_site_risk_index(float $total_risk_points, float $max_possible_points): float {
        if ($max_possible_points <= 0) {
            return 0.0;
        }
        $index = ($total_risk_points / $max_possible_points) * 100;
        return min(100.0, max(0.0, round($index, 2)));
    }

    /**
     * Classifies the site risk score.
     *
     * @param float $index The site risk index (0–100).
     * @return string 'healthy', 'low', 'moderate', 'high', 'critical'
     */
    public function classify_site_risk(float $index): string {
        if ($index <= 20) {
            return 'healthy';
        } else if ($index <= 40) {
            return 'low';
        } else if ($index <= 60) {
            return 'moderate';
        } else if ($index <= 80) {
            return 'high';
        } else {
            return 'critical';
        }
    }
}
