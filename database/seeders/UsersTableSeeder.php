<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'Administrator')->first();
        $brigadierRole = Role::where('name', 'Brigadier')->first();
        $finalUserRole = Role::where('name', 'Final User')->first();

        User::create([
            'name' => 'John',
            'last_name' => 'Doe',
            'email' => 'johndoe@autonoma.edu.co',
            'password' => Hash::make('password123'),
            'id_card' => 123456789,
            'rhgb' => 'O+',
            'social_security' => 'SS123456789',
            'phone_number' => '1234567890',
            'is_active' => true,
            'role_id' => $adminRole->id,
        ]);

        User::create([
            'name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'janesmith@autonoma.edu.co',
            'password' => Hash::make('password123'),
            'id_card' => 987654321,
            'rhgb' => 'A-',
            'social_security' => 'SS987654321',
            'phone_number' => '0987654321',
            'is_active' => true,
            'role_id' => $brigadierRole->id,
        ]);

        User::create([
            'name' => 'Final',
            'last_name' => 'User',
            'email' => 'finaluser@autonoma.edu.co',
            'password' => Hash::make('password123'),
            'id_card' => 123123123,
            'rhgb' => 'B+',
            'social_security' => 'SS123123123',
            'phone_number' => '1231231234',
            'is_active' => true,
            'role_id' => $finalUserRole->id,
        ]);
    }
}
