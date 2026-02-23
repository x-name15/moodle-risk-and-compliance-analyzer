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
 * CLI script — Run MRCA scan from the command line.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');

require_once($CFG->libdir . '/clilib.php');

[$options, $unrecognised] = cli_get_params(['help' => false], ['h' => 'help']);

if ($options['help']) {
    echo "MRCA — Moodle Risk & Compliance Analyzer CLI Scan

Usage:
    php run_scan_cli.php [--help]

Options:
    --help, -h    Show this help message.

Description:
    Runs a complete risk and compliance scan across all installed plugins
    and system roles. Results are saved to the database and can be viewed
    on the MRCA dashboard.
";
    exit(0);
}

mtrace("===================================================");
mtrace(" MRCA — Moodle Risk & Compliance Analyzer");
mtrace(" Starting CLI Scan...");
mtrace("===================================================");

$task = new \local_mrca\task\run_scan();
$task->execute();

mtrace("===================================================");
mtrace(" MRCA CLI Scan completed. Check the dashboard.");
mtrace("===================================================");
