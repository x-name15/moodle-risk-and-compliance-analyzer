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
 * Risk engine — core scoring logic for MRCA.
 *
 * Migrated and expanded from local_privacy_inspector\heuristics\engine.
 * Now handles privacy, dependency, and capability risk scoring.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\engine;

defined('MOODLE_INTERNAL') || die();

class risk_engine {
    // Privacy scoring constants (from REBRAND.MD §4.1).
    const SCORE_NO_PRIVACY_API = 30;
    const SCORE_NO_EXPORT_DELETE = 25;
    const SCORE_PII_FIELD = 20;
    const SCORE_PII_UNENCRYPTED = 15;
    const SCORE_CUSTOM_TABLE_NO_RETENTION = 10;

    // Dependency scoring constants (from REBRAND.MD §4.2).
    const SCORE_CORE_VERSION_MISMATCH = 25;
    const SCORE_MISSING_DEPENDENCY = 20;
    const SCORE_PLUGIN_OUTDATED = 15;
    const SCORE_DEPRECATED_API = 10;
    const SCORE_NO_RECENT_UPDATE = 10;

    // Capability scoring constants (from REBRAND.MD §4.3).
    const SCORE_CRITICAL_CAP_NON_ADMIN = 25;
    const SCORE_MULTIPLE_CRITICAL_CAPS = 15;
    const SCORE_SUSPICIOUS_OVERRIDE = 10;

    /**
     * Calculates the privacy risk score for a plugin.
     *
     * @param array $plugin_scan_data Plugin API check data.
     * @param array $db_findings Suspicious DB field findings.
     * @return int
     */
    public function calculate_privacy_score(array $plugin_scan_data, array $db_findings): int {
        $score = 0;

        // 1. Check Privacy API.
        if (empty($plugin_scan_data['has_privacy_provider'])) {
            $score += self::SCORE_NO_PRIVACY_API;
        }

        // 2. DB Findings (PII Fields).
        if (!empty($db_findings)) {
            $pii_score_accumulated = 0;

            foreach ($db_findings as $finding) {
                $field_score = self::SCORE_PII_FIELD;
                $fieldname = strtolower($finding['field']);

                // Context Weighting.
                if (
                    strpos($fieldname, 'password') !== false ||
                    strpos($fieldname, 'secret') !== false ||
                    strpos($fieldname, 'token') !== false
                ) {
                    $field_score = 35; // Critical.
                } else if (
                    strpos($fieldname, 'email') !== false ||
                          strpos($fieldname, 'phone') !== false ||
                          strpos($fieldname, 'mobile') !== false ||
                          strpos($fieldname, 'dni') !== false ||
                          strpos($fieldname, 'ssn') !== false
                ) {
                    $field_score = 25; // High.
                } else if (
                    strpos($fieldname, 'ip') !== false ||
                          strpos($fieldname, 'address') !== false ||
                          strpos($fieldname, 'city') !== false
                ) {
                    $field_score = 15; // Medium.
                }

                // Encryption Reward.
                if (!empty($finding['is_encrypted'])) {
                    $field_score = (int)($field_score * 0.2);
                }

                $pii_score_accumulated += $field_score;
            }

            // Cap PII score contribution at 65.
            if ($pii_score_accumulated > 65) {
                $pii_score_accumulated = 65;
            }

            $score += $pii_score_accumulated;
        }

        return (int)$score;
    }

    /**
     * Calculates the dependency risk score for a plugin.
     *
     * @param array $dependency_findings Dependency check results.
     * @return int
     */
    public function calculate_dependency_score(array $dependency_findings): int {
        $score = 0;

        if (!empty($dependency_findings['core_mismatch'])) {
            $score += self::SCORE_CORE_VERSION_MISMATCH;
        }
        if (!empty($dependency_findings['missing_dependencies'])) {
            $score += self::SCORE_MISSING_DEPENDENCY * count($dependency_findings['missing_dependencies']);
        }
        if (!empty($dependency_findings['outdated'])) {
            $score += self::SCORE_PLUGIN_OUTDATED;
        }
        if (!empty($dependency_findings['deprecated_apis'])) {
            $score += self::SCORE_DEPRECATED_API * min(count($dependency_findings['deprecated_apis']), 3);
        }
        if (!empty($dependency_findings['no_recent_update'])) {
            $score += self::SCORE_NO_RECENT_UPDATE;
        }

        // Cap at 65.
        return min($score, 65);
    }

    /**
     * Calculates the capability definition risk score for a plugin.
     *
     * @param array $capability_findings Capability check results.
     * @return int
     */
    public function calculate_capability_score(array $capability_findings): int {
        $score = 0;

        if (!empty($capability_findings['critical_caps_non_admin'])) {
            $score += self::SCORE_CRITICAL_CAP_NON_ADMIN *
                min(count($capability_findings['critical_caps_non_admin']), 3);
        }
        if (!empty($capability_findings['suspicious_overrides'])) {
            $score += self::SCORE_SUSPICIOUS_OVERRIDE *
                min(count($capability_findings['suspicious_overrides']), 3);
        }

        // Cap at 65.
        return min($score, 65);
    }

    /**
     * Calculates the total plugin risk score.
     * PluginRisk = PrivacyScore + DependencyScore + CapabilityScore
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
     * Legacy compatibility — calculates risk from old-style API/DB data.
     *
     * @param array $plugin_scan_data
     * @param array $db_findings
     * @return int
     */
    public function calculate_risk_score(array $plugin_scan_data, array $db_findings): int {
        return $this->calculate_privacy_score($plugin_scan_data, $db_findings);
    }

    /**
     * Categorizes the score into a risk level.
     *
     * @param int $score
     * @return string 'low', 'medium', 'high', 'critical'
     */
    public function get_risk_level(int $score): string {
        if ($score >= 81) {
            return 'critical';
        } else if ($score >= 61) {
            return 'high';
        } else if ($score >= 31) {
            return 'medium';
        } else {
            return 'low';
        }
    }
}
