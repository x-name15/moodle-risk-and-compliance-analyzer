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
 * Dashboard data preparation class.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\reporting;

use renderable;
use templatable;
use renderer_base;

/**
 * Dashboard class.
 *
 * Serves all data for the MRCA dashboard: site risk index, risk trend,
 * top-5 risky plugins/roles, dependency audit, and more.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dashboard implements renderable, templatable {
    /**
     * Exports data for the Mustache template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $DB;

        $data = [
            'has_scans' => false,
            'sesskey' => sesskey(),
            'scan_url' => (new \moodle_url('/local/mrca/index.php'))->out(false),
            'site_risk_index' => 0,
            'site_risk_class' => 'healthy',
            'site_risk_label' => get_string('risk_healthy', 'local_mrca'),
            'total_score' => 0,
            'plugins_scanned' => 0,
            'roles_scanned' => 0,
            'scan_date' => '',
            'chart_data' => '{}',
            'trend_data' => '{}',
            'has_trend' => false,
            'plugin_risks' => [],
            'top_plugins' => [],
            'has_top_plugins' => false,
            'top_roles' => [],
            'has_top_roles' => false,
            'dep_audit' => [],
            'has_dep_audit' => false,
            'role_heatmap' => [],
            'has_heatmap' => false,
            'alerts' => [],
            'has_alerts' => false,
            'whitelist_items' => [],
            'has_integration' => false,
        ];

        // Get the latest scan.
        $latestscan = $DB->get_records('local_mrca_scans', ['status' => 1], 'timecreated DESC', '*', 0, 1);
        if (empty($latestscan)) {
            return $data;
        }

        $scan = reset($latestscan);
        $data['has_scans'] = true;
        $data['scanid'] = $scan->id;
        $data['total_score'] = $scan->total_score;
        $data['site_risk_index'] = round($scan->site_risk_index, 1);
        $data['plugins_scanned'] = $scan->plugins_scanned;
        $data['roles_scanned'] = $scan->roles_scanned;
        $data['scan_date'] = userdate($scan->timecreated);

        // Site risk classification.
        $model = new \local_mrca\engine\scoring_model();
        $classification = $model->classify_site_risk($scan->site_risk_index);
        $data['site_risk_class'] = $classification;
        $data['site_risk_label'] = get_string('risk_' . $classification, 'local_mrca');

        // Badge color mapping.
        $classmap = [
            'healthy' => 'success',
            'low' => 'info',
            'moderate' => 'warning',
            'high' => 'danger',
            'critical' => 'danger',
        ];
        $data['site_risk_badge'] = $classmap[$classification] ?? 'secondary';

        // Add risk trend (historical).
        $data = $this->add_risk_trend($data, $DB);

        // Add plugin risks with multi-score.
        $data = $this->add_plugin_risks($data, $DB, $scan);

        // Add top 5 risky plugins.
        $data = $this->add_top_plugins($data, $DB, $scan);

        // Add top 5 risky roles.
        $data = $this->add_top_roles($data, $DB, $scan);

        // Add dependency audit data.
        $data = $this->add_dependency_audit($data, $DB, $scan);

        // Add role heatmap data.
        $data = $this->add_role_heatmap($data, $DB, $scan);

        // Add correlation alerts.
        $data = $this->add_alerts($data, $DB, $scan);

        // Add whitelist items.
        $data = $this->add_whitelist($data);

        // Integration check.
        $method = get_config('local_mrca', 'integration_method');
        $data['has_integration'] = !empty($method) && $method !== 'disabled';

        return $data;
    }

    /**
     * Adds risk trend data from last 10 scans.
     *
     * @param array $data Template data.
     * @param \moodle_database $db Database object.
     * @return array Updated data.
     */
    private function add_risk_trend(array $data, $db): array {
        $scans = $db->get_records('local_mrca_scans', ['status' => 1], 'timecreated ASC', '*', 0, 10);

        if (count($scans) >= 2) {
            $labels = [];
            $values = [];

            foreach ($scans as $s) {
                $labels[] = userdate($s->timecreated, '%d/%m %H:%M');
                $values[] = round($s->site_risk_index, 1);
            }

            $data['trend_data'] = json_encode([
                'labels' => $labels,
                'values' => $values,
            ]);
            $data['has_trend'] = true;
        }

        return $data;
    }

    /**
     * Adds plugin risks with multi-score breakdown.
     *
     * @param array $data Template data.
     * @param \moodle_database $db Database object.
     * @param \stdClass $scan Current scan record.
     * @return array Updated data.
     */
    private function add_plugin_risks(array $data, $db, \stdClass $scan): array {
        $multirisks = $db->get_records('local_mrca_plugin_risks', ['scanid' => $scan->id], 'total_score DESC');
        $results = $db->get_records('local_mrca_scan_results', ['scanid' => $scan->id], 'risk_score DESC');

        $engine = new \local_mrca\engine\risk_engine();
        $risklevels = ['low' => 0, 'medium' => 0, 'high' => 0, 'critical' => 0];

        $multiindex = [];
        foreach ($multirisks as $mr) {
            $multiindex[$mr->component] = $mr;
        }

        foreach ($results as $result) {
            $level = $engine->get_risk_level($result->risk_score);
            $risklevels[$level]++;

            $badgeclass = 'success';
            if ($level === 'critical' || $level === 'high') {
                $badgeclass = 'danger';
            } else if ($level === 'medium') {
                $badgeclass = 'warning';
            }

            $mr = $multiindex[$result->plugin] ?? null;

            $piifields = [];
            $details = json_decode($result->details, true);
            $wm = new \local_mrca\manager\whitelist_manager();
            if (!empty($details['db_findings'])) {
                foreach ($details['db_findings'] as $finding) {
                    $tbl = $finding['table'] ?? '';
                    $fld = $finding['field'] ?? '';
                    if (!empty($tbl) && !empty($fld) && !$wm->is_whitelisted($result->plugin, $tbl, $fld)) {
                        $piifields[] = [
                            'table' => $tbl,
                            'field' => $fld,
                            'component' => $result->plugin,
                        ];
                    }
                }
            }

            $data['plugin_risks'][] = [
                'id' => $result->id,
                'plugin' => $result->plugin,
                'risk_score' => $result->risk_score,
                'risk_level' => get_string('risk_' . $level, 'local_mrca'),
                'badge_class' => $badgeclass,
                'has_privacy_provider' => $result->has_privacy_provider,
                'privacy_score' => $mr->privacy_score ?? 0,
                'dependency_score' => $mr->dependency_score ?? 0,
                'capability_score' => $mr->capability_score ?? 0,
                'pii_fields' => $piifields,
                'has_pii_fields' => !empty($piifields),
            ];
        }

        $data['chart_data'] = json_encode([
            'labels' => [
                get_string('risk_low', 'local_mrca'),
                get_string('risk_medium', 'local_mrca'),
                get_string('risk_high', 'local_mrca'),
                get_string('risk_critical', 'local_mrca'),
            ],
            'values' => [
                $risklevels['low'],
                $risklevels['medium'],
                $risklevels['high'],
                $risklevels['critical'],
            ],
            'colors' => ['#28a745', '#ffc107', '#fd7e14', '#dc3545'],
        ]);

        return $data;
    }

    /**
     * Adds top 5 risky plugins.
     *
     * @param array $data Template data.
     * @param \moodle_database $db Database object.
     * @param \stdClass $scan Current scan record.
     * @return array Updated data.
     */
    private function add_top_plugins(array $data, $db, \stdClass $scan): array {
        $top = $db->get_records(
            'local_mrca_plugin_risks',
            ['scanid' => $scan->id],
            'total_score DESC',
            '*',
            0,
            5
        );

        $engine = new \local_mrca\engine\risk_engine();

        foreach ($top as $pr) {
            if ($pr->total_score <= 0) {
                continue;
            }
            $level = $engine->get_risk_level($pr->total_score);
            $badge = ($level === 'critical' || $level === 'high') ? 'danger' :
                     ($level === 'medium' ? 'warning' : 'success');

            $data['top_plugins'][] = [
                'component' => $pr->component,
                'total_score' => $pr->total_score,
                'privacy_score' => $pr->privacy_score,
                'dependency_score' => $pr->dependency_score,
                'capability_score' => $pr->capability_score,
                'risk_level' => get_string('risk_' . $level, 'local_mrca'),
                'badge_class' => $badge,
            ];
        }

        $data['has_top_plugins'] = !empty($data['top_plugins']);
        return $data;
    }

    /**
     * Adds top 5 risky roles.
     *
     * @param array $data Template data.
     * @param \moodle_database $db Database object.
     * @param \stdClass $scan Current scan record.
     * @return array Updated data.
     */
    private function add_top_roles(array $data, $db, \stdClass $scan): array {
        $top = $db->get_records(
            'local_mrca_role_risks',
            ['scanid' => $scan->id],
            'risk_score DESC',
            '*',
            0,
            5
        );

        foreach ($top as $rr) {
            if ($rr->risk_score <= 0) {
                continue;
            }
            $role = $db->get_record('role', ['id' => $rr->roleid]);
            if (!$role) {
                continue;
            }

            $badge = 'success';
            if ($rr->critical_cap_count >= 8) {
                $badge = 'danger';
            } else if ($rr->critical_cap_count >= 3) {
                $badge = 'warning';
            } else if ($rr->critical_cap_count >= 1) {
                $badge = 'info';
            }

            $data['top_roles'][] = [
                'role_shortname' => $role->shortname,
                'risk_score' => $rr->risk_score,
                'critical_cap_count' => $rr->critical_cap_count,
                'badge_class' => $badge,
            ];
        }

        $data['has_top_roles'] = !empty($data['top_roles']);
        return $data;
    }

    /**
     * Adds dependency audit panel data.
     *
     * @param array $data Template data.
     * @param \moodle_database $db Database object.
     * @param \stdClass $scan Current scan record.
     * @return array Updated data.
     */
    private function add_dependency_audit(array $data, $db, \stdClass $scan): array {
        $results = $db->get_records('local_mrca_scan_results', ['scanid' => $scan->id]);

        foreach ($results as $result) {
            $details = json_decode($result->details, true);
            if (empty($details['dep_findings'])) {
                continue;
            }

            $dep = $details['dep_findings'];
            $issues = [];

            if (!empty($dep['core_mismatch'])) {
                $issues[] = [
                    'severity' => 'HIGH',
                    'severity_badge' => 'danger',
                    'message' => get_string('dep_core_mismatch', 'local_mrca'),
                ];
            }

            if (!empty($dep['missing_dependencies'])) {
                foreach ($dep['missing_dependencies'] as $missing) {
                    $issues[] = [
                        'severity' => 'HIGH',
                        'severity_badge' => 'danger',
                        'message' => get_string('dep_missing', 'local_mrca', $missing),
                    ];
                }
            }

            if (!empty($dep['outdated'])) {
                $issues[] = [
                    'severity' => 'MED',
                    'severity_badge' => 'warning',
                    'message' => get_string('dep_outdated', 'local_mrca'),
                ];
            }

            if (!empty($dep['deprecated_apis'])) {
                $count = count($dep['deprecated_apis']);
                $issues[] = [
                    'severity' => 'MED',
                    'severity_badge' => 'warning',
                    'message' => get_string('dep_deprecated_apis', 'local_mrca', $count),
                ];
            }

            if (!empty($issues)) {
                $data['dep_audit'][] = [
                    'plugin' => $result->plugin,
                    'issues' => $issues,
                ];
            }
        }

        $data['has_dep_audit'] = !empty($data['dep_audit']);
        return $data;
    }

    /**
     * Adds role heatmap data.
     *
     * @param array $data Template data.
     * @param \moodle_database $db Database object.
     * @param \stdClass $scan Current scan record.
     * @return array Updated data.
     */
    private function add_role_heatmap(array $data, $db, \stdClass $scan): array {
        $rolerisks = $db->get_records('local_mrca_role_risks', ['scanid' => $scan->id], 'risk_score DESC');

        foreach ($rolerisks as $rr) {
            $role = $db->get_record('role', ['id' => $rr->roleid]);
            if (!$role) {
                continue;
            }

            $heatmapclass = 'success';
            $emoji = '🟢';
            if ($rr->critical_cap_count >= 8) {
                $heatmapclass = 'danger';
                $emoji = '🔴';
            } else if ($rr->critical_cap_count >= 3) {
                $heatmapclass = 'warning';
                $emoji = '🟠';
            } else if ($rr->critical_cap_count >= 1) {
                $heatmapclass = 'info';
                $emoji = '🟡';
            }

            $data['role_heatmap'][] = [
                'role_shortname' => $role->shortname,
                'critical_cap_count' => $rr->critical_cap_count,
                'risk_score' => $rr->risk_score,
                'heatmap_class' => $heatmapclass,
                'emoji' => $emoji,
            ];
        }

        $data['has_heatmap'] = !empty($data['role_heatmap']);
        return $data;
    }

    /**
     * Adds correlation alerts.
     *
     * @param array $data Template data.
     * @param \moodle_database $db Database object.
     * @param \stdClass $scan Current scan record.
     * @return array Updated data.
     */
    private function add_alerts(array $data, $db, \stdClass $scan): array {
        $alerts = $db->get_records('local_mrca_alerts', ['scanid' => $scan->id], 'severity DESC', '*', 0, 20);

        foreach ($alerts as $alert) {
            $alertbadge = 'info';
            if ($alert->severity === 'critical') {
                $alertbadge = 'danger';
            } else if ($alert->severity === 'high') {
                $alertbadge = 'warning';
            }

            $data['alerts'][] = [
                'type' => $alert->type,
                'severity' => $alert->severity,
                'severity_badge' => $alertbadge,
                'component' => $alert->component,
                'description' => $alert->description,
            ];
        }

        $data['has_alerts'] = !empty($data['alerts']);
        return $data;
    }

    /**
     * Adds whitelist items.
     *
     * @param array $data Template data.
     * @return array Updated data.
     */
    private function add_whitelist(array $data): array {
        $wm = new \local_mrca\manager\whitelist_manager();
        $whitelist = $wm->get_all();

        foreach ($whitelist as $item) {
            $data['whitelist_items'][] = [
                'id' => $item->id,
                'component' => $item->component,
                'table_name' => $item->table_name,
                'field_name' => $item->field_name,
            ];
        }

        return $data;
    }
}
