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

    public function test_student_request_pagination_remains_scoped_to_their_user_id(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);

        foreach (range(1, 17) as $index) {
            ServiceRequest::query()->create($this->requestData($student));
        }

        foreach (range(1, 2) as $index) {
            ServiceRequest::query()->create($this->requestData($otherStudent));
        }

        $response = $this->actingAs($student)
            ->getJson('/requests?page=2')
            ->assertOk()
            ->assertJsonPath('total', 17)
            ->assertJsonCount(2, 'data');

        foreach ($response->json('data') as $request) {
            $this->assertSame($student->id, $request['user_id']);
        }
    }

    public function test_administrator_can_list_all_student_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);

        ServiceRequest::query()->create($this->requestData($student));
        ServiceRequest::query()->create($this->requestData($otherStudent));

        $this->actingAs($admin)
            ->getJson('/requests')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonCount(2, 'data');
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

    public function test_request_details_escape_user_provided_text_in_blade_output(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $serviceRequest = ServiceRequest::query()->create([
            ...$this->requestData($student),
            'purpose' => '<script>alert("x")</script>',
        ]);

        $this->actingAs($student)
            ->get("/requests/{$serviceRequest->id}")
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert("x")</script>', false);
    }

    public function test_request_forms_include_csrf_and_status_form_uses_patch_method_spoofing(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $serviceRequest = ServiceRequest::query()->create($this->requestData($student));
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($student)
            ->get('/requests')
            ->assertOk()
            ->assertSee('name="_token"', false);

        $this->actingAs($admin)
            ->get("/requests/{$serviceRequest->id}")
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('name="_method" value="PATCH"', false);
    }

    public function test_student_creation_uses_their_account_details_and_pending_status(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($student)
            ->postJson('/requests', [
                'requester_name' => 'Forged Name',
                'requester_email' => 'forged@example.com',
                'item_name' => 'Laptop',
                'quantity' => 1,
                'purpose' => 'Coursework',
            ])
            ->assertCreated()
            ->assertJsonPath('user_id', $student->id)
            ->assertJsonPath('requester_name', $student->name)
            ->assertJsonPath('requester_email', $student->email)
            ->assertJsonPath('status', 'pending');

        $this->assertDatabaseHas('requests', [
            'user_id' => $student->id,
            'requester_name' => $student->name,
            'requester_email' => $student->email,
            'status' => 'pending',
        ]);
    }

    public function test_student_creation_rejects_client_supplied_owner_status_and_role_fields(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        foreach ([
            'user_id' => 999,
            'status' => 'approved',
            'is_admin' => true,
            'role' => 'admin',
        ] as $field => $value) {
            $this->actingAs($student)
                ->postJson('/requests', [
                    'item_name' => 'Laptop',
                    'quantity' => 1,
                    'purpose' => 'Coursework',
                    $field => $value,
                ])
                ->assertUnprocessable()
                ->assertJsonValidationErrors($field);
        }

        $this->assertDatabaseCount('requests', 0);
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
            ->patchJson("/requests/{$serviceRequest->id}/status", [
                'status' => 'approved',
                'user_id' => 999,
                'requester_name' => 'Changed Name',
                'requester_email' => 'changed@example.com',
                'purpose' => 'Changed purpose',
            ])
            ->assertOk()
            ->assertJsonPath('status', 'approved');

        $this->assertDatabaseHas('requests', [
            'id' => $serviceRequest->id,
            'user_id' => $student->id,
            'requester_name' => 'Student',
            'requester_email' => 'student@example.com',
            'purpose' => 'Coursework',
            'status' => 'approved',
        ]);
    }

    public function test_administrator_status_update_accepts_only_pending_approved_or_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $serviceRequest = ServiceRequest::query()->create($this->requestData($student));

        foreach (['pending', 'approved', 'rejected'] as $status) {
            $this->actingAs($admin)
                ->patchJson("/requests/{$serviceRequest->id}/status", ['status' => $status])
                ->assertOk()
                ->assertJsonPath('status', $status);
        }

        foreach (['processing', 'deleted', ''] as $status) {
            $this->actingAs($admin)
                ->patchJson("/requests/{$serviceRequest->id}/status", ['status' => $status])
                ->assertUnprocessable()
                ->assertJsonValidationErrors('status');
        }

        $this->assertDatabaseHas('requests', [
            'id' => $serviceRequest->id,
            'status' => 'rejected',
        ]);
    }

    public function test_student_cannot_update_status_and_other_students_records_remain_hidden(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $otherStudent = User::factory()->create(['role' => 'student']);
        $ownRequest = ServiceRequest::query()->create($this->requestData($student));
        $otherRequest = ServiceRequest::query()->create($this->requestData($otherStudent));

        $csrfToken = str_repeat('a', 40);

        $this->actingAs($student)
            ->withSession(['_token' => $csrfToken])
            ->withHeader('X-CSRF-TOKEN', $csrfToken)
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
