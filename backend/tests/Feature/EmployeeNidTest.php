<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeNidTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $over = []): array
    {
        $dept = Department::first() ?? Department::create(['name' => 'Design', 'code' => 'DSN']);

        return array_merge([
            'department_id' => $dept->id, 'name' => 'Nid Person', 'email' => 'nid@example.com',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
            'designation' => 'Designer', 'basic_salary' => 1000, 'joining_date' => '2026-11-01',
        ], $over);
    }

    public function test_nid_number_and_private_copy_are_saved_and_only_super_admin_can_view_copy(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin);

        $this->post('/employees', $this->payload([
            'nid_number' => '1234567890123',
            'nid_file' => UploadedFile::fake()->image('nid.jpg'),
        ]))->assertRedirect(route('employees.index'));

        $emp = Employee::where('email', 'nid@example.com')->firstOrFail();
        $this->assertSame('1234567890123', $emp->nid_number);
        Storage::disk('local')->assertExists($emp->nid_file);

        $this->get("/employees/{$emp->id}/nid-copy")->assertOk();
        $this->get("/employees/{$emp->id}")->assertSee('1234567890123');

        $this->actingAs(User::factory()->create(['role' => 'employee']))
            ->get("/employees/{$emp->id}/nid-copy")->assertRedirect();
    }

    public function test_invalid_or_duplicate_nid_is_rejected_and_nid_is_optional(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin']));

        $this->post('/employees', $this->payload(['nid_number' => '12ab']))
            ->assertSessionHasErrors('nid_number');

        $this->post('/employees', $this->payload())->assertSessionDoesntHaveErrors();
        $this->post('/employees', $this->payload(['email' => 'b@example.com', 'nid_number' => '1234567890']))
            ->assertSessionDoesntHaveErrors();
        $this->post('/employees', $this->payload(['email' => 'c@example.com', 'nid_number' => '1234567890']))
            ->assertSessionHasErrors('nid_number');
    }
}
