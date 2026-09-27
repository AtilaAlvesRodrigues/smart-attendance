<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\UsuarioMaster;

class UsuarioMasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UsuarioMaster::firstOrCreate(
            ['email_search' => UsuarioMaster::generateBlindIndex('master@admin.com')],
            [
                'nome' => 'Master Admin',
                'email' => 'master@admin.com',
                'password' => Hash::make('senha123'),
                'role' => 'master',
            ]
        );
    }
}
