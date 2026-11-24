# 🏛️ SIJABFU — SIGMA JF BPS RI

Portal Layanan Terpadu Pembinaan Jabatan Fungsional (Statistisi, Pranata Komputer, dan Fungsional lainnya) di lingkungan **Badan Pusat Statistik Republik Indonesia (BPS RI)**.

Aplikasi ini mengikuti *Modul Website Pembinaan JF* (Tim Pembinaan JF, 2026) dan PRD Revisi SIJABFU. Rangkuman revisi ada di bagian [Ringkasan Revisi](#-ringkasan-revisi-2026).

---

## 👥 Role dan Menu

Role disimpan di kolom `users.role` dan dibatasi di server (middleware `role:` dan policy).

| Role | Kunci | Menu |
|---|---|---|
| **Pegawai** | `pegawai` | Dashboard, Pengajuan Usulan, Pelacakan Status, Regulasi, Tim Kerja, Chat Box |
| **Tim Pembinaan JF** | `tim_jf` | Dashboard, Verifikasi Berkas Usulan Pegawai, Monitoring Progress UKOM, Regulasi, Chat Box (Messenger) |
| **Super Admin** | `super_admin` | Dashboard, Sinkronisasi Data SSO, Monitoring Progress UKOM, Regulasi, Daftar User |

Semua halaman memakai satu layout dengan navbar, notifikasi, profil dengan tingkatan user, logout, dan tema dark/light.

## 🔄 Alur Usulan

Status disimpan di `form_usulan.status_key` dan hanya berubah lewat state machine (`App\Services\UsulanStateMachine`); setiap perubahan dicatat di `usulan_status_logs`.

```
(kirim) → verifikasi ──MS──→ disetujui ──surat usulan──→ ditetapkan ──sertifikat UKOM──→ selesai
               │  ↑
              TMS └── kirim ulang
               ↓
           perbaikan
```

Super Admin dapat mengembalikan status ke tahap sebelumnya (rollback) dengan alasan wajib.

## 🧩 Modul

- **Pengajuan Usulan** (`/pengajuan`): sinkron data SIMPEG/SIASN, entri data, upload 8 berkas persyaratan (PDF, maks 5 MB), kirim / kirim ulang.
- **Pelacakan Status** (`/pelacakan-status`): tracker, riwayat status, hasil MS/TMS, preview dan unduh surat usulan serta sertifikat UKOM.
- **Verifikasi Berkas** (`/verifikasi`): daftar pegawai, berkas, pemeriksaan per berkas, keputusan MS/TMS.
- **Monitoring Progress UKOM** (`/monitoring`): progres per pegawai, upload surat usulan dan sertifikat, rollback (Super Admin).
- **Regulasi** (`/regulasi`, kelola di `/admin/regulasi`): daftar per kategori, preview dan unduh; upload, edit, hapus untuk Tim JF dan Super Admin.
- **Tim Kerja** (`/tim-kerja`) dan **Chat Box / Messenger** (`/chat`, `/messenger`): profil Tim Pembinaan JF dan chat dua arah (polling 5 detik).
- **Daftar User** (`/admin/users`) dan **Sinkronisasi Data SSO** (`/admin/sso-sync`): khusus Super Admin.
- **Dashboard** (`/dashboard`): grafik per role dan notifikasi.

File usulan dan regulasi disimpan di disk privat (`storage/app/private`) dan hanya dilayani lewat route yang diotorisasi.

---

## 🔐 Login dan Akun Demo

- **Login SSO** melalui adapter `App\Contracts\SsoProvider`. Saat ini hanya tersedia driver **`fake`**: halaman simulasi lokal tempat NIP diketik. Driver fake **ditolak di production**. Driver SSO asli menunggu detail protokol dan endpoint.
- **Login NIP/password** aktif bila `AUTH_LOCAL_ENABLED=true` (default: aktif kecuali `APP_ENV=production`).
- **Akun demo** hanya dibuat oleh seeder di environment `local` atau `testing`, dengan password acak yang tidak ditampilkan. Masuklah lewat tombol **LOGIN SSO** dan ketik NIP akun demo yang ada di `config/sijabfu_fake.php` (data fiktif).

## ⚙️ Variabel Environment Tambahan

| Variabel | Default | Keterangan |
|---|---|---|
| `SSO_DRIVER` | `fake` | Driver SSO. Nilai selain `fake` menampilkan "driver belum dikonfigurasi". |
| `SIMPEG_DRIVER` | `fake` | Driver SIMPEG/SIASN untuk Sinkron data. |
| `AUTH_LOCAL_ENABLED` | aktif kecuali production | Login NIP/password. |

---

## 🛠️ Persyaratan Sistem & Instalasi

- **PHP** >= 8.2 (ekstensi OpenSSL, PDO, SQLite aktif)
- **Composer** >= 2.x
- **Node.js & NPM** (opsional untuk build aset)

### Langkah Menjalankan:

```bash
# 1. Masuk ke folder proyek
cd sijabfu

# 2. Salin environment configuration
cp .env.example .env

# 3. Install dependensi
composer install

# 4. Generate Application Key
php artisan key:generate

# 5. Jalankan migrasi dan seeder (akun demo hanya di local/testing)
php artisan migrate --seed

# 6. Jalankan server development
php artisan serve
```

Akses aplikasi melalui browser: 👉 **http://127.0.0.1:8000**

### Menjalankan Test

```bash
php artisan test
```

---

## 📁 Struktur Direktori Penting

```
sijabfu/
├── app/
│   ├── Contracts/           # SsoProvider, SimpegDirectory (adapter)
│   ├── Exceptions/          # InvalidStatusTransition
│   ├── Http/Controllers/    # Pengajuan, Pelacakan, Verifikasi, Monitoring, Regulasi, Chat, Messenger, Dashboard, SSO, Admin/*
│   ├── Http/Middleware/     # RoleMiddleware (multi-role), EnsureUserIsActive
│   ├── Models/              # User, FormUsulan, UsulanBerkas, UsulanStatusLog, Regulasi, ChatThread, ChatMessage, SsoSyncLog
│   ├── Notifications/       # Notifikasi database
│   ├── Policies/            # Policy usulan, berkas, regulasi, chat
│   ├── Services/            # State machine, chat, notifikasi, dashboard, sinkronisasi SSO/SIMPEG
│   ├── Simpeg/ dan Sso/     # Driver fake
├── config/sijabfu.php       # Berkas persyaratan, batas file, driver SSO/SIMPEG
├── config/sijabfu_fake.php  # Direktori data fiktif untuk driver fake
├── database/migrations/     # Migrasi aditif (SQLite)
├── docs/                    # Dokumentasi
├── resources/views/         # Layout bersama, partial, dan halaman per modul
├── routes/web.php           # Route dan proteksi role
└── tests/Feature/Phase1..9  # Test per fase revisi
```

---

## 📝 Ringkasan Revisi 2026

Revisi dikerjakan bertahap di branch `revision/pdf-plan-2026` (Fase 1 sampai 9, satu commit per fase). Asumsi berikut dipakai sebagai default dan **masih menunggu konfirmasi pemilik** (PRD bagian 11):

| # | Pertanyaan terbuka | Default yang dipakai |
|---|---|---|
| 11.1 | Protokol dan endpoint SSO serta SIMPEG/SIASN | Hanya adapter dan driver `fake`; driver asli belum dibuat |
| 11.2 | Daftar dokumen persyaratan | 8 dokumen dari `docs/DOKUMENTASI_PROSES_BISNIS_SIJABFU.md` bagian 4.3 |
| 11.3 | Cakupan Super Admin | Semua akses Tim JF, ditambah user, sinkronisasi, rollback; menu sidebar mengikuti PDF |
| 11.4 | Pemetaan status lama | 1, 2 → verifikasi; 3 → disetujui; 4 → ditetapkan; 5 → selesai |
| 11.5 | NIP Super Admin pertama | Placeholder `198001012005011001` (hanya di local/testing) |
| 11.6 | Pemeriksaan per berkas dan `hasil_ukom` | Ada; `hasil_ukom` opsional |
| 11.7 | Usulan per pegawai | Satu usulan aktif; usulan baru setelah `selesai` |
| 11.8 | Batas file | PDF saja; berkas, surat, sertifikat 5 MB; regulasi 10 MB |
| 11.9 | Model chat | Satu thread per pegawai, inbox bersama Tim JF, polling 5 detik |
| 11.10 | Grafik dashboard | Per status, per jenis usulan, per bulan (12 bulan); user per role untuk Super Admin; tanpa dependensi baru |
| 11.11 | Item di luar PDF | Alur Layanan, kartu tautan admin, `/uji-kompetensi` dipertahankan |

Keputusan lain selama revisi:
- Alasan TMS: catatan **atau** minimal satu berkas bertanda "Perlu perbaikan" (mengikuti FR-VB-4).
- Kolom `form_usulan.status` (angka) dan tabel `progress_berkas` tetap ada; penghapusannya memerlukan migrasi destruktif yang disetujui.
- Satu notifikasi per pesan chat untuk setiap anggota Tim JF aktif.

---

## 📄 Lisensi & Hak Cipta
Hak Cipta © 2026 Tim Pembinaan JF — Biro SDM — Badan Pusat Statistik Republik Indonesia.
