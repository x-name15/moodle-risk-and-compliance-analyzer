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
 * Webhook service — sends reports to external endpoints.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\service;

defined('MOODLE_INTERNAL') || die();

class webhook_service {

    /**
     * Sends the scan report to a generic webhook.
     *
     * @param array $payload The JSON payload to send.
     * @param string $url The destination URL.
     * @param string $token Optional Bearer token.
     * @return bool True on success, false on failure.
     */
    public function send_report(array $payload, string $url, string $token = ''): bool {
        $json_payload = json_encode($payload);

        $curl = new \curl();
        $options = [
            'CURLOPT_HTTPHEADER' => [
                'Content-Type: application/json',
                'User-Agent: Moodle MRCA',
            ],
        ];

        if (!empty($token)) {
            $options['CURLOPT_HTTPHEADER'][] = 'Authorization: Bearer ' . $token;
        }

        mtrace("MRCA: Sending report to webhook: $url");

        foreach ($options['CURLOPT_HTTPHEADER'] as $header) {
            $curl->setHeader($header);
        }

        $response = $curl->post($url, $json_payload);
        $info = $curl->get_info();

        if ($info['http_code'] >= 200 && $info['http_code'] < 300) {
            mtrace("MRCA: Webhook success: HTTP " . $info['http_code']);
            return true;
        } else {
            mtrace("MRCA: Webhook failed: HTTP " . $info['http_code'] . " - Response: " . substr($response, 0, 100));
            return false;
        }
    }
}
