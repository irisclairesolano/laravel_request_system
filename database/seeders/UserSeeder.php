<?php

namespace Database\Seeders;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Trusted setup: creates fictional students + one administrator.
     * Admin role is assigned here only (not via public registration).
     */
    public function run(): void
    {
        User::where('email', 'test@example.com')->delete();

        $student1 = User::updateOrCreate(
            ['email' => 'student1@example.com'],
            [
                'name' => 'Student 1',
                'password' => 'password',
                'role' => 'student',
            ]
        );

        $student2 = User::updateOrCreate(
            ['email' => 'student2@example.com'],
            [
                'name' => 'Student 2',
                'password' => 'password',
                'role' => 'student',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

        // Preserve Laboratory 2 rows; only attach ownership via user_id.
        ServiceRequest::where('id', 1)->update(['user_id' => $student1->id]);
        ServiceRequest::where('id', 2)->update(['user_id' => $student2->id]);
        ServiceRequest::where('id', 3)->update(['user_id' => $student1->id]);
    }
}
