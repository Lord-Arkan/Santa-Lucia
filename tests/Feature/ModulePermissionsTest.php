<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_only_assigned_modules(): void
    {
        $user = User::factory()->create([
            'module_permissions' => ['patients'],
        ]);

        $this->actingAs($user)
            ->get(route('patients.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('services.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_configuration_permission_allows_managing_users_regardless_of_role(): void
    {
        $manager = User::factory()->create([
            'rol' => 'asistente',
            'module_permissions' => ['configuration'],
        ]);

        $this->actingAs($manager)
            ->post(route('users.store'), [
                'name' => 'Usuario limitado',
                'email' => 'limitado@santalucia.test',
                'password' => 'password',
                'password_confirmation' => 'password',
                'rol' => 'contador',
                'module_permissions' => ['reports'],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'limitado@santalucia.test',
            'rol' => 'contador',
        ]);

        $created = User::query()->where('email', 'limitado@santalucia.test')->firstOrFail();

        $this->assertSame(['reports'], $created->module_permissions);
    }

    public function test_user_without_configuration_permission_cannot_manage_users(): void
    {
        $user = User::factory()->create([
            'module_permissions' => ['dashboard'],
        ]);

        $this->actingAs($user)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_user_can_be_saved_without_any_module_permissions(): void
    {
        $manager = User::factory()->create([
            'module_permissions' => ['configuration'],
        ]);

        $target = User::factory()->create();

        $this->actingAs($manager)
            ->put(route('users.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'rol' => $target->rol,
                'module_permissions' => [],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame([], $target->fresh()->module_permissions);
    }

    public function test_history_module_can_be_accessed_without_patients_module(): void
    {
        $user = User::factory()->create([
            'module_permissions' => ['history'],
        ]);

        $this->actingAs($user)
            ->get(route('history.index'))
            ->assertOk();
    }
}
