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
 * Correlation engine — systemic risk layer per REBRAND.MD §4.4.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\engine;

/**
 * Correlates plugin risk, privacy exposure, capability definitions,
 * role assignments, and dependency instability to detect systemic risk.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class correlation_engine {
    /** @var scoring_model The scoring model instance. */
    private $scoringmodel;

    /** @var int Threshold for triggering systemic risk alerts. */
    const RISK_THRESHOLD = 40;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->scoringmodel = new scoring_model();
    }

    /**
     * Evaluates all correlation rules and generates alerts.
     *
     * @param array $pluginrisks Array of plugin risk data indexed by component.
     * @param array $rolerisks Array of role risk data indexed by roleid.
     * @param int $scanid Current scan ID.
     * @return array List of alert records to insert.
     */
    public function evaluate(array $pluginrisks, array $rolerisks, int $scanid): array {
        $alerts = [];

        // Run all correlation rules.
        $alerts = array_merge($alerts, $this->rule_privacy_capability($pluginrisks, $scanid));
        $alerts = array_merge($alerts, $this->rule_high_risk_unstable_deps($pluginrisks, $scanid));
        $alerts = array_merge($alerts, $this->rule_systemic_failure($pluginrisks, $rolerisks, $scanid));
        $alerts = array_merge($alerts, $this->rule_outdated_with_pii($pluginrisks, $scanid));
        $alerts = array_merge($alerts, $this->rule_structural_privacy_gap($pluginrisks, $scanid));
        $alerts = array_merge($alerts, $this->rule_multi_role_escalation($rolerisks, $scanid));
        $alerts = array_merge($alerts, $this->rule_deprecated_with_exposure($pluginrisks, $scanid));

        return $alerts;
    }

    /**
     * Rule 1: High privacy risk + no Privacy API + defines capabilities.
     *
     * @param array $pluginrisks Plugin risk data.
     * @param int $scanid Scan ID.
     * @return array
     */
    private function rule_privacy_capability(array $pluginrisks, int $scanid): array {
        $alerts = [];

        foreach ($pluginrisks as $component => $data) {
            if (
                ($data['privacy_score'] ?? 0) >= 30 &&
                empty($data['has_privacy_provider']) &&
                ($data['capability_score'] ?? 0) > 0
            ) {
                $alerts[] = $this->create_alert(
                    $scanid,
                    'correlation',
                    'critical',
                    $component,
                    get_string('alert_privacy_capability_correlation', 'local_mrca', $component)
                );
            }
        }

        return $alerts;
    }

    /**
     * Rule 2: Plugin has high total risk AND unstable dependencies.
     *
     * @param array $pluginrisks Plugin risk data.
     * @param int $scanid Scan ID.
     * @return array
     */
    private function rule_high_risk_unstable_deps(array $pluginrisks, int $scanid): array {
        $alerts = [];

        foreach ($pluginrisks as $component => $data) {
            $total = ($data['privacy_score'] ?? 0) +
                     ($data['dependency_score'] ?? 0) +
                     ($data['capability_score'] ?? 0);

            if ($total >= 60 && ($data['dependency_score'] ?? 0) >= 20) {
                $alerts[] = $this->create_alert(
                    $scanid,
                    'correlation',
                    'high',
                    $component,
                    get_string('alert_high_risk_unstable_deps', 'local_mrca', $component)
                );
            }
        }

        return $alerts;
    }

    /**
     * Rule 3: Roles with high risk + plugins with high risk → systemic risk.
     *
     * @param array $pluginrisks Plugin risk data.
     * @param array $rolerisks Role risk data.
     * @param int $scanid Scan ID.
     * @return array
     */
    private function rule_systemic_failure(array $pluginrisks, array $rolerisks, int $scanid): array {
        $alerts = [];

        foreach ($rolerisks as $roleid => $roledata) {
            if (($roledata['risk_score'] ?? 0) >= self::RISK_THRESHOLD) {
                foreach ($pluginrisks as $component => $plugindata) {
                    $pluginscore = ($plugindata['privacy_score'] ?? 0) +
                                    ($plugindata['dependency_score'] ?? 0) +
                                    ($plugindata['capability_score'] ?? 0);

                    if ($pluginscore >= self::RISK_THRESHOLD) {
                        $alerts[] = $this->create_alert(
                            $scanid,
                            'correlation',
                            'critical',
                            $component,
                            get_string('alert_systemic_risk', 'local_mrca', [
                                'plugin' => $component,
                                'roleid' => $roleid,
                            ])
                        );
                        break; // One alert per role is enough.
                    }
                }
            }
        }

        return $alerts;
    }

    /**
     * Rule 4: Outdated dependencies + PII fields → amplified risk.
     *
     * @param array $pluginrisks Plugin risk data.
     * @param int $scanid Scan ID.
     * @return array
     */
    private function rule_outdated_with_pii(array $pluginrisks, int $scanid): array {
        $alerts = [];

        foreach ($pluginrisks as $component => $data) {
            $depfindings = $data['dep_findings'] ?? [];
            $isoutdated = !empty($depfindings['outdated']) || !empty($depfindings['no_recent_update']);
            $haspii = ($data['privacy_score'] ?? 0) >= 20;

            if ($isoutdated && $haspii) {
                $alerts[] = $this->create_alert(
                    $scanid,
                    'correlation',
                    'high',
                    $component,
                    get_string('alert_outdated_pii', 'local_mrca', $component)
                );
            }
        }

        return $alerts;
    }

    /**
     * Rule 5: Structural issues + privacy gaps → compliance alert.
     *
     * @param array $pluginrisks Plugin risk data.
     * @param int $scanid Scan ID.
     * @return array
     */
    private function rule_structural_privacy_gap(array $pluginrisks, int $scanid): array {
        $alerts = [];

        foreach ($pluginrisks as $component => $data) {
            $structuralscore = $data['structural_score'] ?? 0;
            $privacyscore = $data['privacy_score'] ?? 0;

            if ($structuralscore >= 15 && $privacyscore >= 25 && empty($data['has_privacy_provider'])) {
                $alerts[] = $this->create_alert(
                    $scanid,
                    'correlation',
                    'high',
                    $component,
                    get_string('alert_structural_privacy', 'local_mrca', $component)
                );
            }
        }

        return $alerts;
    }

    /**
     * Rule 6: Multi-role escalation detection.
     *
     * @param array $rolerisks Role risk data.
     * @param int $scanid Scan ID.
     * @return array
     */
    private function rule_multi_role_escalation(array $rolerisks, int $scanid): array {
        $alerts = [];

        $dangerousroles = 0;
        foreach ($rolerisks as $roleid => $data) {
            if (
                ($data['critical_cap_count'] ?? 0) >= 3 &&
                !in_array($data['role_shortname'] ?? '', ['manager', 'admin'])
            ) {
                $dangerousroles++;
            }
        }

        if ($dangerousroles >= 3) {
            $alerts[] = $this->create_alert(
                $scanid,
                'capability',
                'critical',
                '',
                get_string('alert_multi_role_escalation', 'local_mrca', $dangerousroles)
            );
        }

        return $alerts;
    }

    /**
     * Rule 7: Deprecated code usage + data exposure.
     *
     * @param array $pluginrisks Plugin risk data.
     * @param int $scanid Scan ID.
     * @return array
     */
    private function rule_deprecated_with_exposure(array $pluginrisks, int $scanid): array {
        $alerts = [];

        foreach ($pluginrisks as $component => $data) {
            $hasdeprecated = ($data['deprecated_calls'] ?? 0) > 0;
            $hasexposure = ($data['privacy_score'] ?? 0) >= 20 && empty($data['has_privacy_provider']);

            if ($hasdeprecated && $hasexposure) {
                $alerts[] = $this->create_alert(
                    $scanid,
                    'correlation',
                    'high',
                    $component,
                    get_string('alert_deprecated_exposure', 'local_mrca', $component)
                );
            }
        }

        return $alerts;
    }

    /**
     * Creates an alert record object.
     *
     * @param int $scanid
     * @param string $type
     * @param string $severity
     * @param string $component
     * @param string $description
     * @return \stdClass
     */
    private function create_alert(
        int $scanid,
        string $type,
        string $severity,
        string $component,
        string $description
    ): \stdClass {
        $alert = new \stdClass();
        $alert->scanid = $scanid;
        $alert->type = $type;
        $alert->severity = $severity;
        $alert->component = $component;
        $alert->description = $description;
        $alert->timecreated = time();
        return $alert;
    }
}
