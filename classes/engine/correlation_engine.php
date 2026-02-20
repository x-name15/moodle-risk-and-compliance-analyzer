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
 * Correlates plugin risk, privacy exposure, capability definitions,
 * role assignments, and dependency instability to detect systemic risk.
 *
 * Rules evaluate cross-layer combinations to identify risks that
 * individual scanners cannot detect in isolation.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\engine;

defined('MOODLE_INTERNAL') || die();

class correlation_engine {

    /** @var scoring_model */
    private $scoring_model;

    /** @var int Threshold for triggering systemic risk alerts. */
    const RISK_THRESHOLD = 40;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->scoring_model = new scoring_model();
    }

    /**
     * Evaluates all correlation rules and generates alerts.
     *
     * @param array $plugin_risks Array of plugin risk data indexed by component.
     * @param array $role_risks Array of role risk data indexed by roleid.
     * @param int $scanid Current scan ID.
     * @return array List of alert records to insert.
     */
    public function evaluate(array $plugin_risks, array $role_risks, int $scanid): array {
        $alerts = [];

        // Run all correlation rules.
        $alerts = array_merge($alerts, $this->rule_privacy_capability($plugin_risks, $scanid));
        $alerts = array_merge($alerts, $this->rule_high_risk_unstable_deps($plugin_risks, $scanid));
        $alerts = array_merge($alerts, $this->rule_systemic_failure($plugin_risks, $role_risks, $scanid));
        $alerts = array_merge($alerts, $this->rule_outdated_with_pii($plugin_risks, $scanid));
        $alerts = array_merge($alerts, $this->rule_structural_privacy_gap($plugin_risks, $scanid));
        $alerts = array_merge($alerts, $this->rule_multi_role_escalation($role_risks, $scanid));
        $alerts = array_merge($alerts, $this->rule_deprecated_with_exposure($plugin_risks, $scanid));

        return $alerts;
    }

    /**
     * Rule 1: High privacy risk + no Privacy API + defines capabilities.
     * A plugin that stores PII without proper privacy controls AND defines
     * capabilities is a data exposure risk through permission inheritance.
     *
     * @param array $plugin_risks Plugin risk data.
     * @param int $scanid Scan ID.
     * @return array
     */
    private function rule_privacy_capability(array $plugin_risks, int $scanid): array {
        $alerts = [];

        foreach ($plugin_risks as $component => $data) {
            if (($data['privacy_score'] ?? 0) >= 30 &&
                empty($data['has_privacy_provider']) &&
                ($data['capability_score'] ?? 0) > 0) {

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
     * High-risk plugins with dependency problems are likely to cause
     * breakage during Moodle upgrades.
     *
     * @param array $plugin_risks Plugin risk data.
     * @param int $scanid Scan ID.
     * @return array
     */
    private function rule_high_risk_unstable_deps(array $plugin_risks, int $scanid): array {
        $alerts = [];

        foreach ($plugin_risks as $component => $data) {
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
     * When both a risky plugin and a risky role exist simultaneously,
     * the probability of a security incident is multiplicatively higher.
     *
     * @param array $plugin_risks Plugin risk data.
     * @param array $role_risks Role risk data.
     * @param int $scanid Scan ID.
     * @return array
     */
    private function rule_systemic_failure(array $plugin_risks, array $role_risks, int $scanid): array {
        $alerts = [];

        foreach ($role_risks as $roleid => $role_data) {
            if (($role_data['risk_score'] ?? 0) >= self::RISK_THRESHOLD) {
                foreach ($plugin_risks as $component => $plugin_data) {
                    $plugin_score = ($plugin_data['privacy_score'] ?? 0) +
                                    ($plugin_data['dependency_score'] ?? 0) +
                                    ($plugin_data['capability_score'] ?? 0);

                    if ($plugin_score >= self::RISK_THRESHOLD) {
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
     * Plugins with PII that haven't been updated are likely to have
     * unpatched security vulnerabilities affecting personal data.
     *
     * @param array $plugin_risks Plugin risk data.
     * @param int $scanid Scan ID.
     * @return array
     */
    private function rule_outdated_with_pii(array $plugin_risks, int $scanid): array {
        $alerts = [];

        foreach ($plugin_risks as $component => $data) {
            $dep_findings = $data['dep_findings'] ?? [];
            $is_outdated = !empty($dep_findings['outdated']) || !empty($dep_findings['no_recent_update']);
            $has_pii = ($data['privacy_score'] ?? 0) >= 20;

            if ($is_outdated && $has_pii) {
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
     * Plugins with poor code structure (no tests, missing maturity)
     * AND privacy gaps indicate a compliance risk — the plugin
     * is likely unmaintained and non-compliant.
     *
     * @param array $plugin_risks Plugin risk data.
     * @param int $scanid Scan ID.
     * @return array
     */
    private function rule_structural_privacy_gap(array $plugin_risks, int $scanid): array {
        $alerts = [];

        foreach ($plugin_risks as $component => $data) {
            $structural_score = $data['structural_score'] ?? 0;
            $privacy_score = $data['privacy_score'] ?? 0;

            if ($structural_score >= 15 && $privacy_score >= 25 && empty($data['has_privacy_provider'])) {
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
     * If 3 or more non-admin roles have critical capabilities,
     * it suggests a systemic misconfiguration of the permission model.
     *
     * @param array $role_risks Role risk data.
     * @param int $scanid Scan ID.
     * @return array
     */
    private function rule_multi_role_escalation(array $role_risks, int $scanid): array {
        $alerts = [];

        $dangerous_roles = 0;
        foreach ($role_risks as $roleid => $data) {
            if (($data['critical_cap_count'] ?? 0) >= 3 &&
                !in_array($data['role_shortname'] ?? '', ['manager', 'admin'])) {
                $dangerous_roles++;
            }
        }

        if ($dangerous_roles >= 3) {
            $alerts[] = $this->create_alert(
                $scanid,
                'capability',
                'critical',
                '',
                get_string('alert_multi_role_escalation', 'local_mrca', $dangerous_roles)
            );
        }

        return $alerts;
    }

    /**
     * Rule 7: Deprecated code usage + data exposure.
     * Plugins using deprecated functions AND having PII exposure
     * suggest an unmaintained plugin handling sensitive data.
     *
     * @param array $plugin_risks Plugin risk data.
     * @param int $scanid Scan ID.
     * @return array
     */
    private function rule_deprecated_with_exposure(array $plugin_risks, int $scanid): array {
        $alerts = [];

        foreach ($plugin_risks as $component => $data) {
            $has_deprecated = ($data['deprecated_calls'] ?? 0) > 0;
            $has_exposure = ($data['privacy_score'] ?? 0) >= 20 && empty($data['has_privacy_provider']);

            if ($has_deprecated && $has_exposure) {
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
    private function create_alert(int $scanid, string $type, string $severity,
                                   string $component, string $description): \stdClass {
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
