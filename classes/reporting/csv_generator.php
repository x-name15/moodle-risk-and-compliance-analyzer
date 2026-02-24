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
 * CSV report generator.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\reporting;

/**
 * CSV generator class.
 *
 * Processes scan data and streams it as a CSV file for download.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class csv_generator {
    /**
     * Generates a CSV report for a scan and sends it to output.
     *
     * @param int $scanid The ID of the scan to export.
     * @return void
     */
    public function generate_report(int $scanid): void {
        global $DB;

        $scan = $DB->get_record('local_mrca_scans', ['id' => $scanid], '*', MUST_EXIST);
        $results = $DB->get_records('local_mrca_scan_results', ['scanid' => $scanid], 'risk_score DESC');

        $engine = new \local_mrca\engine\risk_engine();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=MRCA_Report_' . $scan->id . '.csv');

        $output = fopen('php://output', 'w');

        // Info header.
        fputcsv($output, ['MRCA Scan Report']);
        fputcsv($output, ['Scan Date', userdate($scan->timecreated)]);
        fputcsv($output, ['Total Score', $scan->total_score]);
        fputcsv($output, ['Site Risk Index', round($scan->site_risk_index, 1) . '/100']);
        fputcsv($output, ['Plugins Scanned', $scan->plugins_scanned]);
        fputcsv($output, ['Roles Scanned', $scan->roles_scanned]);
        fputcsv($output, []);

        // Results.
        fputcsv($output, ['Plugin', 'Risk Score', 'Risk Level', 'Privacy API']);

        foreach ($results as $result) {
            $level = $engine->get_risk_level($result->risk_score);
            fputcsv($output, [
                $result->plugin,
                $result->risk_score,
                ucfirst($level),
                $result->has_privacy_provider ? 'Yes' : 'No',
            ]);
        }

        fclose($output);
    }
}
