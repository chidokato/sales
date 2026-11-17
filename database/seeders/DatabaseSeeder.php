<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Default Admin
        User::updateOrCreate(
            ['email' => 'admin@idc.vn'],
            [
                'name' => 'Quản trị viên IDC',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // Sample users for demonstration
        $sampleUsers = [
            [
                'name' => 'Nguyễn Văn An',
                'email' => 'an.nguyen@example.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'active',
            ],
            [
                'name' => 'Trần Thị Bích',
                'email' => 'bich.tran@example.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'active',
            ],
            [
                'name' => 'Lê Hoàng Nam',
                'email' => 'nam.le@example.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'inactive',
            ],
            [
                'name' => 'Phạm Minh Đức',
                'email' => 'duc.pham@example.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'status' => 'active',
            ],
        ];

        foreach ($sampleUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['email_verified_at' => now()])
            );
        }
    }
}
