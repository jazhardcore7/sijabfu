<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin (Tim Kerja atau PIC)
        User::updateOrCreate(
            ['nip' => '198501012010011001'],
            [
                'name' => 'Admin Tim Kerja (PIC)',
                'email' => 'admin.jf@bps.go.id',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'admin',
                'jabatan' => 'PIC Pembinaan Jabatan Fungsional',
                'unit_kerja' => 'Biro SDM - BPS RI',
            ]
        );

        // 2. Akun Pegawai (Pengusul / Akses Portal)
        User::updateOrCreate(
            ['nip' => '199505152020121002'],
            [
                'name' => 'Budi Pratama, S.Tr.Stat.',
                'email' => 'pegawai@bps.go.id',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'pegawai',
                'jabatan' => 'Statistisi Ahli Pertama',
                'unit_kerja' => 'BPS RI',
            ]
        );
    }
}
