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

/**
 * Scoring model class.
 *
 * Handles formal risk computation for plugins, roles, and site index.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
     * @param int $privacyscore
     * @param int $dependencyscore
     * @param int $capabilityscore
     * @return int
     */
    public function calculate_plugin_risk(int $privacyscore, int $dependencyscore, int $capabilityscore): int {
        return $privacyscore + $dependencyscore + $capabilityscore;
    }

    /**
     * Calculates the role-level risk score.
     *
     * RoleRisk = CriticalCapabilityWeight + OverrideWeight + PluginExposureWeight
     *
     * @param int $criticalcapweight Score from critical capabilities.
     * @param int $overrideweight Score from dangerous overrides.
     * @param int $pluginexposureweight Score from plugin exposure linkage.
     * @return int
     */
    public function calculate_role_risk(int $criticalcapweight, int $overrideweight, int $pluginexposureweight): int {
        return $criticalcapweight + $overrideweight + $pluginexposureweight;
    }

    /**
     * Applies the correlation multiplier.
     *
     * If both PluginRisk and RoleRisk exceed the threshold:
     * FinalRisk = (PluginRisk + RoleRisk) * 1.5
     * Else:
     * FinalRisk = PluginRisk + RoleRisk
     *
     * @param int $pluginrisk
     * @param int $rolerisk
     * @param int $threshold
     * @return float
     */
    public function apply_correlation(int $pluginrisk, int $rolerisk, int $threshold = self::DEFAULT_THRESHOLD): float {
        if ($pluginrisk > $threshold && $rolerisk > $threshold) {
            return ($pluginrisk + $rolerisk) * self::CORRELATION_MULTIPLIER;
        }
        return (float)($pluginrisk + $rolerisk);
    }

    /**
     * Calculates the Site Risk Index (0–100).
     *
     * SiteRiskIndex = (Total Risk Points / Maximum Possible Points) * 100
     *
     * @param float $totalriskpoints Total accumulated risk.
     * @param float $maxpossiblepoints Maximum theoretical risk for this installation.
     * @return float Normalized 0–100.
     */
    public function calculate_site_risk_index(float $totalriskpoints, float $maxpossiblepoints): float {
        if ($maxpossiblepoints <= 0) {
            return 0.0;
        }
        $index = ($totalriskpoints / $maxpossiblepoints) * 100;
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
