<?php

namespace Tests\Feature;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceRequestAccessInputMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_t01_guest_cannot_open_request_list_or_detail(): void
    {
        $owner = $this->makeUser('student');
        $serviceRequest = ServiceRequest::query()->create($this->requestData($owner));

        $this->get('/requests')->assertRedirect(route('login'));
        $this->get("/requests/{$serviceRequest->id}")->assertRedirect(route('login'));
    }

    public function test_t02_each_student_sees_and_opens_only_their_own_requests(): void
    {
        $studentA = $this->makeUser('student');
        $studentB = $this->makeUser('student');
        $requestA = ServiceRequest::query()->create($this->requestData($studentA, 'A private purpose'));
        $requestB = ServiceRequest::query()->create($this->requestData($studentB, 'B private purpose'));

        $this->actingAs($studentA)
            ->get('/requests')
            ->assertOk()
            ->assertSee($requestA->item_name)
            ->assertDontSee($requestB->item_name);

        $this->get("/requests/{$requestA->id}")
            ->assertOk()
            ->assertSee('A private purpose');

        auth()->logout();

        $this->actingAs($studentB)
            ->get('/requests')
            ->assertOk()
            ->assertSee($requestB->item_name)
            ->assertDontSee($requestA->item_name);

        $this->get("/requests/{$requestB->id}")
            ->assertOk()
            ->assertSee('B private purpose');
    }

    public function test_t03_each_student_gets_404_for_the_other_students_record(): void
    {
        $studentA = $this->makeUser('student');
        $studentB = $this->makeUser('student');
        $requestA = ServiceRequest::query()->create($this->requestData($studentA, 'A secret'));
        $requestB = ServiceRequest::query()->create($this->requestData($studentB, 'B secret'));

        $this->actingAs($studentA)
            ->get("/requests/{$requestB->id}")
            ->assertNotFound()
            ->assertDontSee('B secret');

        auth()->logout();

        $this->actingAs($studentB)
            ->get("/requests/{$requestA->id}")
            ->assertNotFound()
            ->assertDontSee('A secret');
    }

    public function test_t04_student_status_patch_is_denied_and_does_not_change_database_status(): void
    {
        $student = $this->makeUser('student');
        $serviceRequest = ServiceRequest::query()->create($this->requestData($student));
        $csrfToken = str_repeat('a', 40);

        $this->actingAs($student)
            ->withSession(['_token' => $csrfToken])
            ->withHeader('X-CSRF-TOKEN', $csrfToken)
            ->patchJson("/requests/{$serviceRequest->id}/status", ['status' => 'approved'])
            ->assertForbidden();

        $this->assertDatabaseHas('requests', [
            'id' => $serviceRequest->id,
            'status' => 'pending',
        ]);
    }

    public function test_t05_administrator_can_list_view_and_update_student_request(): void
    {
        $admin = $this->makeUser('admin');
        $studentA = $this->makeUser('student');
        $studentB = $this->makeUser('student');
        $requestA = ServiceRequest::query()->create($this->requestData($studentA));
        $requestB = ServiceRequest::query()->create($this->requestData($studentB));

        $this->actingAs($admin)
            ->get('/requests')
            ->assertOk()
            ->assertSee($requestA->item_name)
            ->assertSee($requestB->item_name);

        $this->get("/requests/{$requestA->id}")
            ->assertOk()
            ->assertSee($requestA->requester_email);

        $this->patch("/requests/{$requestA->id}/status", ['status' => 'approved'])
            ->assertRedirect(route('requests.show', $requestA));

        $this->assertDatabaseHas('requests', [
            'id' => $requestA->id,
            'status' => 'approved',
        ]);
    }

    public function test_t06_invalid_item_or_quantity_is_rejected_without_saving_a_row(): void
    {
        $student = $this->makeUser('student');
        $validInput = [
            'item_name' => 'Laptop',
            'quantity' => 1,
            'purpose' => 'Coursework',
        ];
        $invalidInputs = [
            ['quantity' => 0],
            ['quantity' => -1],
            ['quantity' => 'not-an-integer'],
            ['item_name' => ''],
        ];

        foreach ($invalidInputs as $invalidInput) {
            $response = $this->actingAs($student)
                ->postJson('/requests', array_replace($validInput, $invalidInput))
                ->assertUnprocessable();

            $response->assertJsonValidationErrors(array_key_first($invalidInput));
        }

        $this->assertDatabaseCount('requests', 0);
    }

    public function test_t07_student_cannot_supply_owner_status_or_role_fields(): void
    {
        $student = $this->makeUser('student');
        $validInput = [
            'item_name' => 'Laptop',
            'quantity' => 1,
            'purpose' => 'Coursework',
        ];

        foreach ([
            'user_id' => 999,
            'status' => 'approved',
            'is_admin' => true,
            'role' => 'admin',
        ] as $field => $value) {
            $this->actingAs($student)
                ->postJson('/requests', [...$validInput, $field => $value])
                ->assertUnprocessable()
                ->assertJsonValidationErrors($field);
        }

        $this->assertDatabaseCount('requests', 0);
    }

    public function test_t08_html_markup_is_escaped_and_apostrophe_is_stored_safely(): void
    {
        $student = $this->makeUser('student');
        $purpose = "<b>LAB3</b> student's request";

        $this->actingAs($student)
            ->postJson('/requests', [
                'item_name' => 'Student\'s laptop',
                'quantity' => 1,
                'purpose' => $purpose,
            ])
            ->assertCreated()
            ->assertJsonPath('purpose', $purpose)
            ->assertJsonPath('item_name', 'Student\'s laptop');

        $serviceRequest = ServiceRequest::query()->firstOrFail();

        $this->assertSame($purpose, $serviceRequest->purpose);
        $this->get("/requests/{$serviceRequest->id}")
            ->assertOk()
            ->assertSee('&lt;b&gt;LAB3&lt;/b&gt; student&#039;s request', false)
            ->assertDontSee('<b>LAB3</b>', false);
    }

    public function test_t10_administrator_invalid_status_is_rejected_without_changing_status(): void
    {
        $admin = $this->makeUser('admin');
        $student = $this->makeUser('student');
        $serviceRequest = ServiceRequest::query()->create($this->requestData($student));

        $this->actingAs($admin)
            ->patchJson("/requests/{$serviceRequest->id}/status", ['status' => 'processing'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        $this->assertDatabaseHas('requests', [
            'id' => $serviceRequest->id,
            'status' => 'pending',
        ]);
    }

    private function makeUser(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    /**
     * @return array<string, mixed>
     */
    private function requestData(User $user, string $purpose = 'Coursework'): array
    {
        return [
            'user_id' => $user->id,
            'requester_name' => $user->name,
            'requester_email' => $user->email,
            'item_name' => 'Request '.$user->id,
            'quantity' => 1,
            'purpose' => $purpose,
            'status' => 'pending',
        ];
    }
}
