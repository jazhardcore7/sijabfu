<?php

/*
|--------------------------------------------------------------------------
| Direktori data FIKTIF untuk driver "fake" SSO dan SIMPEG/SIASN (FR-USR-3)
|--------------------------------------------------------------------------
| Bukan data pegawai asli. Dipakai agar alur SSO login, Sinkronisasi Data SSO,
| dan Sinkron data SIMPEG/SIASN dapat dijalankan secara lokal.
| NIP demo dari DatabaseSeeder disertakan agar akun demo dapat login lewat SSO.
*/

return [
    'people' => [
        ['nip' => '198501012010011001', 'sso_id' => 'fake-sso-0001', 'name' => 'Admin Tim Kerja (PIC)', 'email' => 'admin.jf@bps.go.id', 'jabatan' => 'PIC Pembinaan Jabatan Fungsional', 'unit_kerja' => 'Biro SDM - BPS RI'],
        ['nip' => '198001012005011001', 'sso_id' => 'fake-sso-0002', 'name' => 'Super Admin SIGMA JF', 'email' => 'superadmin.jf@bps.go.id', 'jabatan' => 'Super Admin Pembinaan Jabatan Fungsional', 'unit_kerja' => 'Biro SDM - BPS RI'],
        ['nip' => '199505152020121002', 'sso_id' => 'fake-sso-0003', 'name' => 'Budi Pratama, S.Tr.Stat.', 'email' => 'budi.pratama@bps.go.id', 'jabatan' => 'Statistisi Ahli Pertama', 'unit_kerja' => 'BPS RI Pusat'],
        ['nip' => '199808182022032005', 'sso_id' => 'fake-sso-0004', 'name' => 'Siti Rahmawati, S.Tr.Stat.', 'email' => 'siti.rahmawati@bps.go.id', 'jabatan' => 'Statistisi Ahli Pertama', 'unit_kerja' => 'BPS Provinsi DKI Jakarta'],
        ['nip' => '200001012024011001', 'sso_id' => 'fake-sso-0005', 'name' => 'Contoh Pegawai Fiktif Satu', 'email' => 'fiktif.satu@example.test', 'jabatan' => 'Pranata Komputer Ahli Pertama', 'unit_kerja' => 'BPS Provinsi Contoh'],
        ['nip' => '200002022024012002', 'sso_id' => 'fake-sso-0006', 'name' => 'Contoh Pegawai Fiktif Dua', 'email' => 'fiktif.dua@example.test', 'jabatan' => 'Statistisi Ahli Muda', 'unit_kerja' => 'BPS Kabupaten Contoh'],
    ],
];
