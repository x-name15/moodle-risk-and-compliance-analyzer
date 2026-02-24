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
 * Upgrade code for the MRCA plugin.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Upgrade the local_mrca plugin.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool Always returns true.
 */
function xmldb_local_mrca_upgrade($oldversion) {
    global $DB; // Moodle pide que esté aquí aunque no se use en este bloque.

    if ($oldversion < 2026022400) {
        // MRCA Golden Release 1.1.5 upgrade step.
        // Aquí podrías poner lógica de base de datos, pero el savepoint es OBLIGATORIO.
        upgrade_plugin_savepoint(true, 2026022400, 'local', 'mrca');
    }

    return true;
}
