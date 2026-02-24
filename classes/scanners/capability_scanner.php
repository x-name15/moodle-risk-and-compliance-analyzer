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
 * Capability scanner — permission and role risk analysis.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca\scanners;

/**
 * Capability scanner class.
 *
 * Analyzes role capabilities, identifies critical permissions, and detects privilege escalation risks.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class capability_scanner {
    /** @var array Critical core capabilities. */
    private const CRITICAL_CAPABILITIES = [
        'moodle/site:config',
        'moodle/user:delete',
        'moodle/role:assign',
        'moodle/course:manageactivities',
        'moodle/course:update',
        'moodle/user:update',
        'moodle/role:manage',
        'moodle/role:override',
        'moodle/site:doanything',
        'moodle/user:create',
        'moodle/backup:backupcourse',
        'moodle/restore:restorecourse',
    ];

    /** @var array Non-admin archetypes that shouldn't have critical caps. */
    private const NON_ADMIN_ARCHETYPES = [
        'student', 'teacher', 'editingteacher', 'coursecreator', 'user', 'guest',
    ];

    /**
     * Scans all roles for permission risks.
     *
     * @return array Role risk data indexed by roleid.
     */
    public function scan_roles(): array {
        global $DB;

        $roles = $DB->get_records('role');
        $results = [];

        foreach ($roles as $role) {
            $results[$role->id] = $this->scan_single_role($role);
        }

        return $results;
    }

    /**
     * Scans a single role for permission risks.
     *
     * @param \stdClass $role The role record.
     * @return array Findings with critical_caps, overrides, risk_score.
     */
    public function scan_single_role(\stdClass $role): array {
        global $DB;

        $finding = [
            'roleid' => $role->id,
            'role_shortname' => $role->shortname,
            'critical_caps' => [],
            'critical_cap_count' => 0,
            'suspicious_overrides' => [],
            'risk_score' => 0,
        ];

        // Get all capabilities for this role in the system context.
        $context = \context_system::instance();
        $rolecaps = $DB->get_records('role_capabilities', [
            'roleid' => $role->id,
            'contextid' => $context->id,
        ]);

        foreach ($rolecaps as $rc) {
            // Check for critical capabilities.
            if (in_array($rc->capability, self::CRITICAL_CAPABILITIES)) {
                if ($rc->permission == CAP_ALLOW) {
                    $finding['critical_caps'][] = $rc->capability;
                    $finding['critical_cap_count']++;
                }
            }

            // Check for suspicious overrides (prohibit -> allow on dangerous caps).
            if ($rc->permission == CAP_ALLOW && $this->is_critical_capability($rc->capability)) {
                // Check if this is non-admin archetype with critical cap.
                if (in_array($role->archetype, self::NON_ADMIN_ARCHETYPES)) {
                    $finding['suspicious_overrides'][] = [
                        'capability' => $rc->capability,
                        'role' => $role->shortname,
                    ];
                }
            }
        }

        // Calculate score.
        $engine = new \local_mrca\engine\risk_engine();
        $finding['risk_score'] = $engine->calculate_capability_score([
            'critical_caps_non_admin' => $finding['suspicious_overrides'],
            'suspicious_overrides' => $finding['suspicious_overrides'],
        ]);

        return $finding;
    }

    /**
     * Checks if a capability is in the critical list.
     *
     * @param string $capability
     * @return bool
     */
    private function is_critical_capability(string $capability): bool {
        return in_array($capability, self::CRITICAL_CAPABILITIES);
    }

    /**
     * Generates heatmap data for the dashboard.
     *
     * @param array $rolerisks Role risk data from scan_roles().
     * @return array Heatmap data array for template.
     */
    public function generate_heatmap(array $rolerisks): array {
        $heatmap = [];

        foreach ($rolerisks as $roleid => $data) {
            $riskclass = 'success';
            $riskemoji = '🟢';
            if ($data['critical_cap_count'] >= 8) {
                $riskclass = 'danger';
                $riskemoji = '🔴';
            } else if ($data['critical_cap_count'] >= 3) {
                $riskclass = 'warning';
                $riskemoji = '🟠';
            } else if ($data['critical_cap_count'] >= 1) {
                $riskclass = 'info';
                $riskemoji = '🟡';
            }

            $heatmap[] = [
                'roleid' => $roleid,
                'role_shortname' => $data['role_shortname'],
                'critical_cap_count' => $data['critical_cap_count'],
                'risk_score' => $data['risk_score'],
                'risk_class' => $riskclass,
                'risk_emoji' => $riskemoji,
            ];
        }

        // Sort by risk score descending.
        usort($heatmap, function ($a, $b) {
            return $b['risk_score'] - $a['risk_score'];
        });

        return $heatmap;
    }
}
