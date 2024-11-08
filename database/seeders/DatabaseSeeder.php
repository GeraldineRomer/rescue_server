<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\Level;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin_role = Role::create([
            'name' => 'Admiministrator',
            'description' => 'Administrator of risk situations in the institution'
        ]);

        Role::create([
            'name' => 'Brigadier',
            'description' => 'Brigadier of the institution'
        ]);

        Role::create([
            'name' => 'Final User',
            'description' => 'Final user that are part of the institution'
        ]);

        $institution = Institution::create([
            'name' => 'Universidad Autonoma de Manizales',
            'description' => 'Universidad Autonoma de Manizales',
            'is_active' => true
        ]);

        Level::create([
            'name' => 'Piso 1',
            'description' => 'Piso 1',
            'institution_id' => 1
        ]);

        Level::create([
            'name' => 'Piso 2',
            'description' => 'Piso 2',
            'institution_id' => 1
        ]);

        Level::create([
            'name' => 'Piso 3',
            'description' => 'Piso 3',
            'institution_id' => 1
        ]);

        Level::create([
            'name' => 'Piso 4',
            'description' => 'Piso 4',
            'institution_id' => 1
        ]);

        Level::create([
            'name' => 'Piso 5',
            'description' => 'Piso 5',
            'institution_id' => 1
        ]);

        $user = User::create([
            'name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin1234'),
            'id_card' => '123456789',
            'rhgb' => 'O+',
            'social_security' => 'UAM',
            'phone_number' => '3568415980',
            'is_active' => true,
        ]);

        $user->institutions()->attach($institution->id, ['code' => 'UAM', 'role_id' => $admin_role->id]);
    }
}
