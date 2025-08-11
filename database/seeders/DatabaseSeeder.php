<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\ControlRoom;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        Role::create(['name' => 'foreman']);
        Role::create(['name' => 'operator']);

        // Create countries
        $rwanda = Country::create(['name' => 'Rwanda', 'code' => 'RW', 'timezone' => 'Africa/Kigali']);
        $kenya = Country::create(['name' => 'Kenya', 'code' => 'KE', 'timezone' => 'Africa/Nairobi']);

        // Create control rooms
        ControlRoom::create(['country_id' => $rwanda->id, 'name' => 'Rwanda Control Room', 'notification_interval_minutes' => 10]);
        ControlRoom::create(['country_id' => $kenya->id, 'name' => 'Kenya Control Room', 'notification_interval_minutes' => 10]);

        // Create users
        $operator = User::create([
            'name' => 'Operator',
            'email' => 'operator@victorfarmers.com',
            'password' => bcrypt(env('OPERATOR_PASSWORD', 'securepassword123')),
            'role' => 'operator',
        ]);
        $operator->assignRole('operator');

        $foreman = User::create([
            'name' => 'Foreman',
            'email' => 'foreman@victorfarmers.com',
            'password' => bcrypt(env('FOREMAN_PASSWORD', 'securepassword123')),
            'role' => 'foreman',
        ]);
        $foreman->assignRole('foreman');
    }
}