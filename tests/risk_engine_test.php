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

use local_mrca\engine\risk_engine;

/**
 * Tests for risk_engine scoring logic.
 *
 * @package    local_mrca
 * @copyright  2026 Mr Jacket
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_mrca\engine\risk_engine
 */
final class risk_engine_test extends \advanced_testcase
{
    /** @var risk_engine The engine instance. */
    private $engine;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->engine = new risk_engine();
    }

    /**
     * Test privacy score when no provider and no findings exist.
     *
     * @return void
     */
    public function test_privacy_score_no_provider_no_findings(): void {
        $score = $this->engine->calculate_privacy_score(
            ['has_privacy_provider' => false],
            []
        );
        $this->assertEquals(risk_engine::SCORE_NO_PRIVACY_API, $score);
    }

    /**
     * Test privacy score with provider and no findings.
     *
     * @return void
     */
    public function test_privacy_score_with_provider(): void {
        $score = $this->engine->calculate_privacy_score(
            ['has_privacy_provider' => true],
            []
        );
        $this->assertEquals(0, $score);
    }

    /**
     * Test privacy score for critical PII fields.
     *
     * @return void
     */
    public function test_privacy_score_critical_pii_fields(): void {
        $findings = [
            ['field' => 'user_password', 'is_encrypted' => false],
        ];
        $score = $this->engine->calculate_privacy_score(
            ['has_privacy_provider' => true],
            $findings
        );
        $this->assertEquals(35, $score);
    }

    /**
     * Test privacy score reduction for encrypted fields.
     *
     * @return void
     */
    public function test_privacy_score_encrypted_field_reduction(): void {
        $findings = [
            ['field' => 'user_password', 'is_encrypted' => true],
        ];
        $score = $this->engine->calculate_privacy_score(
            ['has_privacy_provider' => true],
            $findings
        );
        $this->assertEquals(7, $score);
    }

    /**
     * Test dependency score when clean.
     *
     * @return void
     */
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

    /**
     * Test dependency score for core version mismatch.
     *
     * @return void
     */
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

    /**
     * Test that risk level is low for score 0.
     *
     * @return void
     */
    public function test_risk_level_low(): void {
        $this->assertEquals('low', $this->engine->get_risk_level(0));
        $this->assertEquals('low', $this->engine->get_risk_level(30));
    }

    /**
     * Test that risk level is medium for score 31.
     *
     * @return void
     */
    public function test_risk_level_medium(): void {
        $this->assertEquals('medium', $this->engine->get_risk_level(31));
        $this->assertEquals('medium', $this->engine->get_risk_level(60));
    }

    /**
     * Test that risk level is high for score 61.
     *
     * @return void
     */
    public function test_risk_level_high(): void {
        $this->assertEquals('high', $this->engine->get_risk_level(61));
        $this->assertEquals('high', $this->engine->get_risk_level(80));
    }

    /**
     * Test that risk level is critical for score 81.
     *
     * @return void
     */
    public function test_risk_level_critical(): void {
        $this->assertEquals('critical', $this->engine->get_risk_level(81));
        $this->assertEquals('critical', $this->engine->get_risk_level(200));
    }
}
