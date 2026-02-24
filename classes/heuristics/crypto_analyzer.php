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
 * Crypto analyzer — detects encrypted/hashed data via entropy analysis.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\heuristics;

/**
 * Crypto analyzer class.
 *
 * Provides heuristics and entropy calculations to identify potentially sensitive
 * data that has been hashed or encrypted.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class crypto_analyzer {
    /**
     * Estimates if a string is likely encrypted or hashed.
     *
     * @param string $data The string to analyze.
     * @return bool True if the data appears to be encrypted or hashed.
     */
    public function is_encrypted(string $data): bool {
        if (empty($data)) {
            return false;
        }

        // MD5 (32 hex chars).
        if (preg_match('/^[a-f0-9]{32}$/i', $data)) {
            return true;
        }
        // SHA1 (40 hex chars).
        if (preg_match('/^[a-f0-9]{40}$/i', $data)) {
            return true;
        }
        // SHA256 (64 hex chars).
        if (preg_match('/^[a-f0-9]{64}$/i', $data)) {
            return true;
        }
        // Bcrypt.
        if (preg_match('/^\$2[ayb]\$.{56}$/', $data)) {
            return true;
        }

        // API Keys / random tokens.
        if (preg_match('/^[a-zA-Z0-9]{32,64}$/', $data)) {
            if ($this->calculate_entropy($data) > 4.5) {
                return true;
            }
        }

        // Private Keys.
        if (
            strpos($data, 'BEGIN PRIVATE KEY') !== false ||
            strpos($data, 'BEGIN RSA PRIVATE KEY') !== false
        ) {
            return true;
        }

        // High entropy check.
        $entropy = $this->calculate_entropy($data);
        if ($entropy > 5.5) {
            return true;
        }

        return false;
    }

    /**
     * Calculates Shannon entropy of a string.
     *
     * @param string $string The input string.
     * @return float The calculated entropy value.
     */
    private function calculate_entropy(string $string): float {
        $h = 0;
        $size = strlen($string);
        $chars = count_chars($string, 1);

        foreach ($chars as $count) {
            $p = $count / $size;
            $h -= $p * log($p, 2);
        }

        return $h;
    }
}
