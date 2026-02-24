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
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\engine;

/**
 * Risk engine class.
 *
 * Handles privacy, dependency, and capability risk scoring.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class risk_engine {
    /** @var int Score for no privacy API. */
    const SCORE_NO_PRIVACY_API = 30;

    /** @var int Score for no export or delete functionality. */
    const SCORE_NO_EXPORT_DELETE = 25;

    /** @var int Score for PII field detected. */
    const SCORE_PII_FIELD = 20;

    /** @var int Score for unencrypted PII field. */
    const SCORE_PII_UNENCRYPTED = 15;

    /** @var int Score for custom table with no retention logic. */
    const SCORE_CUSTOM_TABLE_NO_RETENTION = 10;

    /** @var int Score for core version mismatch. */
    const SCORE_CORE_VERSION_MISMATCH = 25;

    /** @var int Score for missing dependency. */
    const SCORE_MISSING_DEPENDENCY = 20;

    /** @var int Score for outdated plugin version. */
    const SCORE_PLUGIN_OUTDATED = 15;

    /** @var int Score for deprecated API usage. */
    const SCORE_DEPRECATED_API = 10;

    /** @var int Score for no recent updates. */
    const SCORE_NO_RECENT_UPDATE = 10;

    /** @var int Score for critical capability in non-admin role. */
    const SCORE_CRITICAL_CAP_NON_ADMIN = 25;

    /** @var int Score for multiple critical capabilities. */
    const SCORE_MULTIPLE_CRITICAL_CAPS = 15;

    /** @var int Score for suspicious permission override. */
    const SCORE_SUSPICIOUS_OVERRIDE = 10;

    /**
     * Calculates the privacy risk score for a plugin.
     *
     * @param array $pluginscandata Plugin API check data.
     * @param array $dbfindings Suspicious DB field findings.
     * @return int
     */
    public function calculate_privacy_score(array $pluginscandata, array $dbfindings): int {
        $score = 0;

        // 1. Check Privacy API.
        if (empty($pluginscandata['has_privacy_provider'])) {
            $score += self::SCORE_NO_PRIVACY_API;
        }

        // 2. DB Findings (PII Fields).
        if (!empty($dbfindings)) {
            $piiscoreaccumulated = 0;

            foreach ($dbfindings as $finding) {
                $fieldscore = self::SCORE_PII_FIELD;
                $fieldname = strtolower($finding['field']);

                // Context Weighting.
                if (
                    strpos($fieldname, 'password') !== false ||
                    strpos($fieldname, 'secret') !== false ||
                    strpos($fieldname, 'token') !== false
                ) {
                    $fieldscore = 35; // Critical.
                } else if (
                    strpos($fieldname, 'email') !== false ||
                    strpos($fieldname, 'phone') !== false ||
                    strpos($fieldname, 'mobile') !== false ||
                    strpos($fieldname, 'dni') !== false ||
                    strpos($fieldname, 'ssn') !== false
                ) {
                    $fieldscore = 25; // High.
                } else if (
                    strpos($fieldname, 'ip') !== false ||
                    strpos($fieldname, 'address') !== false ||
                    strpos($fieldname, 'city') !== false
                ) {
                    $fieldscore = 15; // Medium.
                }

                // Encryption Reward.
                if (!empty($finding['is_encrypted'])) {
                    $fieldscore = (int)($fieldscore * 0.2);
                }

                $piiscoreaccumulated += $fieldscore;
            }

            // Cap PII score contribution at 65.
            if ($piiscoreaccumulated > 65) {
                $piiscoreaccumulated = 65;
            }

            $score += $piiscoreaccumulated;
        }

        return (int)$score;
    }

    /**
     * Calculates the dependency risk score for a plugin.
     *
     * @param array $dependencyfindings Dependency check results.
     * @return int
     */
    public function calculate_dependency_score(array $dependencyfindings): int {
        $score = 0;

        if (!empty($dependencyfindings['core_mismatch'])) {
            $score += self::SCORE_CORE_VERSION_MISMATCH;
        }
        if (!empty($dependencyfindings['missing_dependencies'])) {
            $score += self::SCORE_MISSING_DEPENDENCY * count($dependencyfindings['missing_dependencies']);
        }
        if (!empty($dependencyfindings['outdated'])) {
            $score += self::SCORE_PLUGIN_OUTDATED;
        }
        if (!empty($dependencyfindings['deprecated_apis'])) {
            $score += self::SCORE_DEPRECATED_API * min(count($dependencyfindings['deprecated_apis']), 3);
        }
        if (!empty($dependencyfindings['no_recent_update'])) {
            $score += self::SCORE_NO_RECENT_UPDATE;
        }

        // Cap at 65.
        return min($score, 65);
    }

    /**
     * Calculates the capability definition risk score for a plugin.
     *
     * @param array $capabilityfindings Capability check results.
     * @return int
     */
    public function calculate_capability_score(array $capabilityfindings): int {
        $score = 0;

        if (!empty($capabilityfindings['critical_caps_non_admin'])) {
            $score += self::SCORE_CRITICAL_CAP_NON_ADMIN *
                min(count($capabilityfindings['critical_caps_non_admin']), 3);
        }
        if (!empty($capabilityfindings['suspicious_overrides'])) {
            $score += self::SCORE_SUSPICIOUS_OVERRIDE *
                min(count($capabilityfindings['suspicious_overrides']), 3);
        }

        // Cap at 65.
        return min($score, 65);
    }

    /**
     * Calculates the total plugin risk score.
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
     * Legacy compatibility — calculates risk from old-style API/DB data.
     *
     * @param array $pluginscandata
     * @param array $dbfindings
     * @return int
     */
    public function calculate_risk_score(array $pluginscandata, array $dbfindings): int {
        return $this->calculate_privacy_score($pluginscandata, $dbfindings);
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
