<?php

namespace Tests\Feature;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_request_list_contains_only_their_requests(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $ownRequest = ServiceRequest::query()->create($this->requestData($student));
        ServiceRequest::query()->create($this->requestData($otherStudent));

        $this->actingAs($student)
            ->getJson('/requests')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownRequest->id);
    }

    public function test_student_cannot_view_another_students_request(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $serviceRequest = ServiceRequest::query()->create($this->requestData($otherStudent));

        $this->actingAs($student)
            ->getJson("/requests/{$serviceRequest->id}")
            ->assertNotFound();
    }

    public function test_student_can_create_a_request_but_cannot_choose_its_owner_or_initial_status(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->postJson('/requests', [
                'requester_name' => 'Student',
                'requester_email' => 'student@example.com',
                'item_name' => 'Laptop',
                'quantity' => 1,
                'purpose' => 'Coursework',
                'user_id' => $otherStudent->id,
                'status' => 'approved',
            ])
            ->assertCreated()
            ->assertJsonPath('user_id', $student->id)
            ->assertJsonPath('status', 'pending');

        $this->assertDatabaseHas('requests', [
            'user_id' => $student->id,
            'status' => 'pending',
        ]);
    }

    public function test_administrator_cannot_create_a_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->postJson('/requests', [
                'requester_name' => 'Administrator',
                'requester_email' => 'admin@example.com',
                'item_name' => 'Laptop',
                'quantity' => 1,
                'purpose' => 'Coursework',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('requests', 0);
    }

    public function test_only_administrators_can_update_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $serviceRequest = ServiceRequest::query()->create($this->requestData($student));

        $this->actingAs($admin)
            ->patchJson("/requests/{$serviceRequest->id}/status", ['status' => 'approved'])
            ->assertOk()
            ->assertJsonPath('status', 'approved');

        $this->assertDatabaseHas('requests', [
            'id' => $serviceRequest->id,
            'status' => 'approved',
        ]);
    }

    public function test_student_cannot_update_status_and_other_students_records_remain_hidden(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $ownRequest = ServiceRequest::query()->create($this->requestData($student));
        $otherRequest = ServiceRequest::query()->create($this->requestData($otherStudent));

        $this->actingAs($student)
            ->patchJson("/requests/{$ownRequest->id}/status", ['status' => 'approved'])
            ->assertForbidden();

        $this->patchJson("/requests/{$otherRequest->id}/status", ['status' => 'approved'])
            ->assertNotFound();

        $this->assertDatabaseHas('requests', [
            'id' => $ownRequest->id,
            'status' => 'pending',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function requestData(User $user): array
    {
        return [
            'user_id' => $user->id,
            'requester_name' => 'Student',
            'requester_email' => 'student@example.com',
            'item_name' => 'Laptop',
            'quantity' => 1,
            'purpose' => 'Coursework',
        ];
    }
}
