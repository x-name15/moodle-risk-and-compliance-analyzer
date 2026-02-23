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
 * Unit tests for the risk engine.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mrca;

defined('MOODLE_INTERNAL') || die();

use local_mrca\engine\risk_engine;

/**
 * Tests for risk_engine scoring logic.
 *
 * @covers \local_mrca\engine\risk_engine
 */
class risk_engine_test extends \advanced_testcase
{
    /** @var risk_engine */
    private $engine;

    protected function setUp(): void {
        parent::setUp();
        $this->engine = new risk_engine();
    }

    // ==================== Privacy Score Tests ====================

    public function test_privacy_score_no_provider_no_findings(): void {
        $score = $this->engine->calculate_privacy_score(
            ['has_privacy_provider' => false],
            []
        );
        $this->assertEquals(risk_engine::SCORE_NO_PRIVACY_API, $score);
    }

    public function test_privacy_score_with_provider(): void {
        $score = $this->engine->calculate_privacy_score(
            ['has_privacy_provider' => true],
            []
        );
        $this->assertEquals(0, $score);
    }

    public function test_privacy_score_critical_pii_fields(): void {
        $findings = [
            ['field' => 'user_password', 'is_encrypted' => false],
        ];
        $score = $this->engine->calculate_privacy_score(
            ['has_privacy_provider' => true],
            $findings
        );
        // password field = 35 points (critical).
        $this->assertEquals(35, $score);
    }

    public function test_privacy_score_encrypted_field_reduction(): void {
        $findings = [
            ['field' => 'user_password', 'is_encrypted' => true],
        ];
        $score = $this->engine->calculate_privacy_score(
            ['has_privacy_provider' => true],
            $findings
        );
        // password field (35) * 0.2 = 7.
        $this->assertEquals(7, $score);
    }

    public function test_privacy_score_high_pii_fields(): void {
        $findings = [
            ['field' => 'email_address', 'is_encrypted' => false],
        ];
        $score = $this->engine->calculate_privacy_score(
            ['has_privacy_provider' => true],
            $findings
        );
        // email field = 25 points (high).
        $this->assertEquals(25, $score);
    }

    public function test_privacy_score_medium_pii_fields(): void {
        $findings = [
            ['field' => 'user_ip', 'is_encrypted' => false],
        ];
        $score = $this->engine->calculate_privacy_score(
            ['has_privacy_provider' => true],
            $findings
        );
        // ip field = 15 points (medium).
        $this->assertEquals(15, $score);
    }

    public function test_privacy_score_capped_at_65(): void {
        $findings = [
            ['field' => 'password1', 'is_encrypted' => false],
            ['field' => 'password2', 'is_encrypted' => false],
        ];
        $score = $this->engine->calculate_privacy_score(
            ['has_privacy_provider' => true],
            $findings
        );
        // 35 + 35 = 70, capped at 65.
        $this->assertEquals(65, $score);
    }

    // ==================== Dependency Score Tests ====================

    public function test_dependency_score_clean(): void {
        $findings = [
            'core_mismatch' => false,
            'missing_dependencies' => [],
            'outdated' => false,
            'deprecated_apis' => [],
            'no_recent_update' => false,
        ];
        $score = $this->engine->calculate_dependency_score($findings);
        $this->assertEquals(0, $score);
    }

    public function test_dependency_score_core_mismatch(): void {
        $findings = [
            'core_mismatch' => true,
            'missing_dependencies' => [],
            'outdated' => false,
            'deprecated_apis' => [],
            'no_recent_update' => false,
        ];
        $score = $this->engine->calculate_dependency_score($findings);
        $this->assertEquals(risk_engine::SCORE_CORE_VERSION_MISMATCH, $score);
    }

    public function test_dependency_score_missing_deps_multiplied(): void {
        $findings = [
            'core_mismatch' => false,
            'missing_dependencies' => ['mod_foo', 'mod_bar'],
            'outdated' => false,
            'deprecated_apis' => [],
            'no_recent_update' => false,
        ];
        $score = $this->engine->calculate_dependency_score($findings);
        $this->assertEquals(risk_engine::SCORE_MISSING_DEPENDENCY * 2, $score);
    }

    public function test_dependency_score_deprecated_apis_capped_at_3(): void {
        $findings = [
            'core_mismatch' => false,
            'missing_dependencies' => [],
            'outdated' => false,
            'deprecated_apis' => [1, 2, 3, 4, 5], // 5 findings, capped at 3.
            'no_recent_update' => false,
        ];
        $score = $this->engine->calculate_dependency_score($findings);
        $this->assertEquals(risk_engine::SCORE_DEPRECATED_API * 3, $score);
    }

    public function test_dependency_score_capped_at_65(): void {
        $findings = [
            'core_mismatch' => true, // 25
            'missing_dependencies' => ['a', 'b', 'c'], // 60
            'outdated' => true, // 15
            'deprecated_apis' => [1, 2, 3], // 30
            'no_recent_update' => true, // 10
        ];
        $score = $this->engine->calculate_dependency_score($findings);
        $this->assertEquals(65, $score);
    }

    // ==================== Capability Score Tests ====================

    public function test_capability_score_clean(): void {
        $findings = [
            'critical_caps_non_admin' => [],
            'suspicious_overrides' => [],
        ];
        $score = $this->engine->calculate_capability_score($findings);
        $this->assertEquals(0, $score);
    }

    public function test_capability_score_critical_caps(): void {
        $findings = [
            'critical_caps_non_admin' => [
                ['capability' => 'moodle/site:config'],
                ['capability' => 'moodle/user:delete'],
            ],
            'suspicious_overrides' => [],
        ];
        $score = $this->engine->calculate_capability_score($findings);
        $this->assertEquals(risk_engine::SCORE_CRITICAL_CAP_NON_ADMIN * 2, $score);
    }

    public function test_capability_score_critical_caps_capped_at_3(): void {
        $findings = [
            'critical_caps_non_admin' => [
                ['capability' => 'a'],
                ['capability' => 'b'],
                ['capability' => 'c'],
                ['capability' => 'd'],
                ['capability' => 'e'],
            ],
            'suspicious_overrides' => [],
        ];
        $score = $this->engine->calculate_capability_score($findings);
        // Capped at 3 × 25 = 75, then capped at 65.
        $this->assertEquals(65, $score);
    }

    // ==================== Risk Level Tests ====================

    public function test_risk_level_low(): void {
        $this->assertEquals('low', $this->engine->get_risk_level(0));
        $this->assertEquals('low', $this->engine->get_risk_level(30));
    }

    public function test_risk_level_medium(): void {
        $this->assertEquals('medium', $this->engine->get_risk_level(31));
        $this->assertEquals('medium', $this->engine->get_risk_level(60));
    }

    public function test_risk_level_high(): void {
        $this->assertEquals('high', $this->engine->get_risk_level(61));
        $this->assertEquals('high', $this->engine->get_risk_level(80));
    }

    public function test_risk_level_critical(): void {
        $this->assertEquals('critical', $this->engine->get_risk_level(81));
        $this->assertEquals('critical', $this->engine->get_risk_level(200));
    }

    // ==================== Plugin Risk Total ====================

    public function test_plugin_risk_total(): void {
        $total = $this->engine->calculate_plugin_risk(30, 20, 10);
        $this->assertEquals(60, $total);
    }
}
