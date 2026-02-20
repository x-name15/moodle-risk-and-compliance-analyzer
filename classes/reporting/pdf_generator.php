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
 * PDF report generator.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\reporting;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/pdflib.php');

class pdf_generator {

    /**
     * Generates a PDF report for a scan.
     *
     * @param int $scanid
     */
    public function generate_report(int $scanid) {
        global $DB;

        $scan = $DB->get_record('local_mrca_scans', ['id' => $scanid], '*', MUST_EXIST);
        $results = $DB->get_records('local_mrca_scan_results', ['scanid' => $scanid], 'risk_score DESC');

        $pdf = new \pdf();
        $pdf->SetTitle(get_string('report_title', 'local_mrca'));
        $pdf->SetAuthor('MRCA');
        $pdf->AddPage();
        $pdf->SetFont('', 'B', 16);
        $pdf->Cell(0, 10, get_string('report_title', 'local_mrca'), 0, 1, 'C');

        $pdf->SetFont('', '', 10);
        $pdf->Cell(0, 8, get_string('scan_date', 'local_mrca') . ': ' . userdate($scan->timecreated), 0, 1);
        $pdf->Cell(0, 8, get_string('total_score', 'local_mrca') . ': ' . $scan->total_score, 0, 1);
        $pdf->Cell(0, 8, get_string('site_risk_index', 'local_mrca') . ': ' . round($scan->site_risk_index, 1) . '/100', 0, 1);
        $pdf->Cell(0, 8, get_string('plugins_scanned', 'local_mrca') . ': ' . $scan->plugins_scanned, 0, 1);
        $pdf->Cell(0, 8, get_string('roles_scanned', 'local_mrca') . ': ' . $scan->roles_scanned, 0, 1);
        $pdf->Ln(5);

        // Results table.
        $pdf->SetFont('', 'B', 10);
        $pdf->Cell(90, 7, get_string('plugin', 'local_mrca'), 1);
        $pdf->Cell(30, 7, get_string('risk_score', 'local_mrca'), 1);
        $pdf->Cell(40, 7, get_string('risk_level', 'local_mrca'), 1);
        $pdf->Cell(30, 7, get_string('privacy_api', 'local_mrca'), 1);
        $pdf->Ln();

        $engine = new \local_mrca\engine\risk_engine();
        $pdf->SetFont('', '', 9);
        foreach ($results as $result) {
            $level = $engine->get_risk_level($result->risk_score);
            $provider = $result->has_privacy_provider ? '✓' : '✗';

            $pdf->Cell(90, 6, $result->plugin, 1);
            $pdf->Cell(30, 6, $result->risk_score, 1, 0, 'C');
            $pdf->Cell(40, 6, ucfirst($level), 1, 0, 'C');
            $pdf->Cell(30, 6, $provider, 1, 0, 'C');
            $pdf->Ln();
        }

        $pdf->Output('MRCA_Report_' . $scan->id . '.pdf', 'D');
    }
}
