<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeModulesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(string $role = 'super_admin'): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_pages_open_for_admin(): void
    {
        $this->actingAs($this->admin());

        foreach (['/assets', '/assets/create', '/inventory-items', '/inventory-items/create',
                  '/reports', '/reports/finance', '/reports/attendance', '/reports/payroll', '/settings'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_settings_are_super_admin_only(): void
    {
        $this->actingAs($this->admin('admin'));

        $this->get('/settings')->assertForbidden();
        $this->put('/settings', ['company_name' => 'X'])->assertForbidden();
    }

    public function test_company_name_is_saved_and_shown(): void
    {
        $this->actingAs($this->admin());

        $this->put('/settings', ['company_name' => 'Acme Ltd'])->assertRedirect();
        $this->get('/dashboard')->assertSee('Acme Ltd');
    }

    public function test_stock_cannot_go_negative(): void
    {
        $this->actingAs($this->admin());
        $item = InventoryItem::create(['sku' => 'A1', 'name' => 'Pen', 'unit' => 'pcs', 'quantity' => 5, 'reorder_level' => 2]);

        $this->post(route('inventory-items.move', $item), ['type' => 'Out', 'quantity' => 9])
            ->assertSessionHasErrors('error');
        $this->assertSame(5, $item->fresh()->quantity);

        $this->post(route('inventory-items.move', $item), ['type' => 'Out', 'quantity' => 3]);
        $this->post(route('inventory-items.move', $item), ['type' => 'In', 'quantity' => 10]);

        $this->assertSame(12, $item->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 2);
    }

    public function test_assigned_asset_needs_an_employee(): void
    {
        $this->actingAs($this->admin());

        $this->post('/assets', [
            'asset_code' => 'LP-1', 'name' => 'Laptop', 'status' => 'Assigned',
        ])->assertSessionHasErrors('employee_id');

        $this->post('/assets', [
            'asset_code' => 'LP-1', 'name' => 'Laptop', 'status' => 'Available',
        ])->assertRedirect(route('assets.index'));
    }

    public function test_employee_cannot_open_office_modules(): void
    {
        $this->actingAs($this->admin('employee'));

        $this->get('/assets')->assertRedirect(route('portal.dashboard'));
        $this->get('/reports')->assertRedirect(route('portal.dashboard'));
        $this->get('/settings')->assertRedirect(route('portal.dashboard'));
    }
}
