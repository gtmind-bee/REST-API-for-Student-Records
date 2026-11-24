<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentApiTest extends TestCase
{
    use RefreshDatabase;

    private function authenticate(User $user): void
    {
        Sanctum::actingAs($user, ['*']);
    }

    private function validStudentPayload(array $overrides = []): array
    {
        return array_merge([
            'admission_number' => 'STU-2026-001',
            'first_name' => 'Grace',
            'last_name' => 'Hopper',
            'email' => 'grace@example.com',
            'date_of_birth' => '2002-12-09',
            'gender' => 'female',
            'department' => 'Computer Science',
            'level' => '300',
            'status' => 'active',
        ], $overrides);
    }

    public function test_student_routes_require_authentication(): void
    {
        $this->getJson('/api/students')->assertUnauthorized();
        $this->postJson('/api/students', $this->validStudentPayload())->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_a_student(): void
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $this->postJson('/api/students', $this->validStudentPayload())
            ->assertCreated()
            ->assertJsonPath('data.admission_number', 'STU-2026-001')
            ->assertJsonPath('data.full_name', 'Grace Hopper');

        $this->assertDatabaseHas('students', [
            'user_id' => $user->id,
            'admission_number' => 'STU-2026-001',
        ]);
    }

    public function test_student_creation_validates_input(): void
    {
        $this->authenticate(User::factory()->create());

        $this->postJson('/api/students', ['first_name' => 'Only a first name'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['admission_number', 'last_name', 'gender', 'department', 'level']);
    }

    public function test_student_list_is_paginated_and_can_be_filtered(): void
    {
        $user = User::factory()->create();
        $this->authenticate($user);
        Student::factory()->count(3)->for($user)->create(['department' => 'Computer Science']);
        Student::factory()->for($user)->create(['department' => 'Biology']);

        $this->getJson('/api/students?per_page=2&department=Computer%20Science')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3);
    }

    public function test_user_can_update_and_delete_their_student(): void
    {
        $user = User::factory()->create();
        $this->authenticate($user);
        $student = Student::factory()->for($user)->create();

        $this->patchJson('/api/students/'.$student->id, ['level' => '400'])
            ->assertOk()
            ->assertJsonPath('data.level', '400');

        $this->deleteJson('/api/students/'.$student->id)->assertNoContent();
        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_user_cannot_access_another_users_student(): void
    {
        $this->authenticate(User::factory()->create());
        $otherUserStudent = Student::factory()->create();

        $this->getJson('/api/students/'.$otherUserStudent->id)->assertNotFound();
        $this->patchJson('/api/students/'.$otherUserStudent->id, ['level' => '400'])->assertNotFound();
        $this->deleteJson('/api/students/'.$otherUserStudent->id)->assertNotFound();
    }

    public function test_admission_number_must_be_unique_per_account(): void
    {
        $user = User::factory()->create();
        $this->authenticate($user);
        Student::factory()->for($user)->create(['admission_number' => 'STU-DUPLICATE']);

        $this->postJson('/api/students', $this->validStudentPayload([
            'admission_number' => 'STU-DUPLICATE',
            'email' => 'another@example.com',
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['admission_number']);
    }
}
