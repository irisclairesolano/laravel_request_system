<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $student1 = User::create([
            'name' => 'Student 1',
            'email' => 'iriscaliresom@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        $student2 = User::create([
            'name' => 'Student 2',
            'email' => 'klylachua.com',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        ServiceRequest::where('user_id', 1)->update(['user_id' => $student1->id]);
        ServiceRequest::where('user_id', 2)->update(['user_id' => $student2->id]);
        ServiceRequest::where('user_id', 3)->update(['user_id' => $admin->id]);
    }
}
