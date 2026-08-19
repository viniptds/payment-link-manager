<?php

namespace Tests\Feature;

use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsCompanyData;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase, BuildsCompanyData;

    private $company;
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = $this->company();
        $this->admin = $this->user(['is_admin' => true, 'company_id' => $this->company->id]);
    }

    private function dashboardData($user): array
    {
        return $this->actingAs($user)->get('/dashboard')->assertOk()->viewData('data');
    }

    public function test_guests_are_sent_to_the_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_totals_are_grouped_by_status(): void
    {
        $this->payment(['company_id' => $this->company->id, 'value' => 100, 'expire_at' => now()->addDay()]);
        $this->payment(['company_id' => $this->company->id, 'value' => 50, 'expire_at' => now()->subDay()]);
        $this->payment(['company_id' => $this->company->id, 'value' => 200, 'status' => Payment::STATUS_PAID]);
        $this->payment(['company_id' => $this->company->id, 'value' => 30, 'status' => Payment::STATUS_CANCELLED]);
        $this->payment(['company_id' => $this->company->id, 'value' => 20, 'status' => Payment::STATUS_INACTIVE]);

        $data = $this->dashboardData($this->admin);

        $this->assertSame(5, $data['numbers']['payments']);
        $this->assertEquals(400, $data['payments']);

        $this->assertSame(1, $data['numbers']['active']);
        $this->assertEquals(100, $data['values']['active']);

        $this->assertSame(1, $data['numbers']['expired']);
        $this->assertEquals(50, $data['values']['expired']);

        $this->assertSame(1, $data['numbers']['paid']);
        $this->assertEquals(200, $data['values']['paid']);

        $this->assertSame(1, $data['numbers']['canceled']);
        $this->assertEquals(30, $data['values']['canceled']);

        $this->assertSame(1, $data['numbers']['inactive']);
        $this->assertEquals(20, $data['values']['inactive']);
    }

    public function test_totals_are_empty_without_payments(): void
    {
        $data = $this->dashboardData($this->admin);

        $this->assertSame(0, $data['numbers']['payments']);
        $this->assertNull($data['payments']);
        $this->assertNull($data['values']['paid']);
    }

    public function test_totals_only_count_the_payments_of_the_company(): void
    {
        $other = $this->company();
        $otherAdmin = $this->user(['is_admin' => true, 'company_id' => $other->id]);

        $this->payment(['company_id' => $this->company->id, 'value' => 100]);
        $this->payment(['company_id' => $other->id, 'value' => 70]);
        $this->payment(['company_id' => $other->id, 'value' => 30]);

        $this->assertSame(1, $this->dashboardData($this->admin)['numbers']['payments']);
        $this->assertEquals(100, $this->dashboardData($this->admin)['payments']);

        $this->assertSame(2, $this->dashboardData($otherAdmin)['numbers']['payments']);
        $this->assertEquals(100, $this->dashboardData($otherAdmin)['payments']);
    }

    public function test_super_admin_and_main_user_see_the_totals_of_every_company(): void
    {
        $other = $this->company();

        $this->payment(['company_id' => $this->company->id, 'value' => 100]);
        $this->payment(['company_id' => $other->id, 'value' => 100]);

        $this->assertSame(2, $this->dashboardData($this->superAdmin())['numbers']['payments']);
        $this->assertSame(2, $this->dashboardData($this->mainUser($this->company))['numbers']['payments']);
    }

    public function test_operator_without_company_only_counts_payments_without_company(): void
    {
        $loose = $this->user(['is_admin' => true, 'company_id' => null]);

        $this->payment(['company_id' => null, 'value' => 10]);
        $this->payment(['company_id' => $this->company->id, 'value' => 100]);

        $this->assertSame(1, $this->dashboardData($loose)['numbers']['payments']);
        $this->assertEquals(10, $this->dashboardData($loose)['payments']);
    }

    public function test_payments_without_expiration_are_not_counted_as_active(): void
    {
        // the active bucket of the dashboard requires a future expiration date
        $this->payment(['company_id' => $this->company->id, 'value' => 100, 'expire_at' => null]);

        $data = $this->dashboardData($this->admin);

        $this->assertSame(1, $data['numbers']['payments']);
        $this->assertSame(0, $data['numbers']['active']);
    }
}
