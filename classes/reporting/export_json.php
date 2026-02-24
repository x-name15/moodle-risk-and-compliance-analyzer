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
 * JSON export generator.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\reporting;

use core_plugin_manager;

/**
 * JSON export generator class.
 *
 * Processes scan results and generates a downloadable JSON report.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class export_json {
    /**
     * Generates a JSON report for a scan.
     *
     * @param int $scanid The ID of the scan to export.
     * @return void
     */
    public function generate_report(int $scanid) {
        global $DB;

        $scan = $DB->get_record('local_mrca_scans', ['id' => $scanid], '*', MUST_EXIST);
        $results = $DB->get_records('local_mrca_scan_results', ['scanid' => $scanid]);
        $pluginrisks = $DB->get_records('local_mrca_plugin_risks', ['scanid' => $scanid]);
        $rolerisks = $DB->get_records('local_mrca_role_risks', ['scanid' => $scanid]);
        $alerts = $DB->get_records('local_mrca_alerts', ['scanid' => $scanid]);

        // Get dynamic version from version.php via plugin manager.
        $pluginman = core_plugin_manager::instance();
        $plugininfo = $pluginman->get_plugin_info('local_mrca');
        $release = $plugininfo->release ?? 'unknown';

        $engine = new \local_mrca\engine\risk_engine();

        $report = [
            'generator' => 'MRCA',
            'version' => $release,
            'scan' => [
                'id' => $scan->id,
                'timestamp' => $scan->timecreated,
                'total_score' => $scan->total_score,
                'site_risk_index' => round($scan->site_risk_index, 2),
                'plugins_scanned' => $scan->plugins_scanned,
                'roles_scanned' => $scan->roles_scanned,
            ],
            'plugin_results' => [],
            'plugin_risks' => [],
            'role_risks' => [],
            'alerts' => [],
        ];

        foreach ($results as $r) {
            $report['plugin_results'][] = [
                'plugin' => $r->plugin,
                'risk_score' => $r->risk_score,
                'risk_level' => $engine->get_risk_level($r->risk_score),
                'has_privacy_provider' => (bool)$r->has_privacy_provider,
                'details' => json_decode($r->details),
            ];
        }

        foreach ($pluginrisks as $pr) {
            $report['plugin_risks'][] = [
                'component' => $pr->component,
                'privacy_score' => $pr->privacy_score,
                'dependency_score' => $pr->dependency_score,
                'capability_score' => $pr->capability_score,
                'total_score' => $pr->total_score,
            ];
        }

        foreach ($rolerisks as $rr) {
            $report['role_risks'][] = [
                'roleid' => $rr->roleid,
                'risk_score' => $rr->risk_score,
                'critical_cap_count' => $rr->critical_cap_count,
            ];
        }

        foreach ($alerts as $a) {
            $report['alerts'][] = [
                'type' => $a->type,
                'severity' => $a->severity,
                'component' => $a->component,
                'description' => $a->description,
            ];
        }

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename=MRCA_Report_' . $scan->id . '.json');
        echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
