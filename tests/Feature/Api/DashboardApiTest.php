<?php

namespace Tests\Feature\Api;

class DashboardApiTest extends ApiTestCase
{
    public function test_dashboard_returns_summary_structure(): void
    {
        $this->getJson('/api/dashboard', $this->asAdmin())
            ->assertOk()
            ->assertJsonStructure([
                'summary' => ['hospital', 'pharmacy', 'lab', 'finance'],
                'revenue' => ['labels', 'opd', 'pharmacy', 'lab'],
                'growth'  => ['labels', 'counts'],
                'activity',
            ]);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->getJson('/api/dashboard')->assertUnauthorized();
    }

    public function test_dashboard_hospital_summary_has_expected_keys(): void
    {
        $response = $this->getJson('/api/dashboard', $this->asAdmin())->assertOk();

        $hospital = $response->json('summary.hospital');

        $this->assertArrayHasKey('total_patients', $hospital);
        $this->assertArrayHasKey('today_opd', $hospital);
        $this->assertArrayHasKey('current_ipd', $hospital);
        $this->assertArrayHasKey('total_doctors', $hospital);
    }
}
