<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_directory_shows_role_counts_filters_and_search_results(): void
    {
        $admin = User::factory()->create(['name' => 'Site Admin', 'role' => 'admin']);
        User::factory()->create([
            'name' => 'Ana Docente',
            'email' => 'ana.docente@example.test',
            'role' => 'docente',
        ]);
        User::factory()->create([
            'name' => 'Luis Alumno',
            'email' => 'luis.alumno@example.test',
            'role' => 'alumno',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => 'docente', 'q' => 'ana']))
            ->assertOk()
            ->assertSee('Gestión de usuarios')
            ->assertSee('Directorio institucional')
            ->assertSee('Crear usuario')
            ->assertSee('Ana Docente')
            ->assertDontSee('Luis Alumno')
            ->assertSee('name="role" value="docente"', false)
            ->assertSee('name="q" value="ana"', false);
    }

    public function test_admin_can_create_users_with_the_selected_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Marta Docente',
            'email' => 'marta.docente@example.test',
            'password' => 'password123',
            'role' => 'docente',
        ])->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success');

        $createdUser = User::where('email', 'marta.docente@example.test')->firstOrFail();
        $this->assertSame('docente', $createdUser->role);
        $this->assertTrue(password_verify('password123', $createdUser->password));
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        $admin = User::factory()->create(['name' => 'Site Admin', 'role' => 'admin']);
        User::factory()->create(['name' => 'Ana Docente', 'role' => 'docente']);
        User::factory()->create(['name' => 'Luis Alumno', 'role' => 'alumno']);

        $this->actingAs($admin)
            ->get(route('admin.users.index', ['role' => 'alumno']))
            ->assertOk()
            ->assertSee('Luis Alumno')
            ->assertDontSee('Ana Docente');
    }

    public function test_linked_student_account_cannot_be_changed_to_another_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $studentAccount = User::factory()->create([
            'name' => 'Ana Alumna',
            'email' => 'ana.alumna@example.test',
            'role' => 'alumno',
        ]);
        Student::create([
            'user_id' => $studentAccount->id,
            'dni' => '12345678',
            'first_name' => 'Ana',
            'last_name' => 'García',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.users.update', $studentAccount->id), [
                'name' => $studentAccount->name,
                'email' => $studentAccount->email,
                'role' => 'docente',
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', [
            'id' => $studentAccount->id,
            'role' => 'alumno',
        ]);
    }
}
