# 🏛️ SIJABFU — SIGMA JF BPS RI

Portal Layanan Terpadu Pembinaan Jabatan Fungsional (Statistisi, Pranata Komputer, dan Fungsional lainnya) di lingkungan **Badan Pusat Statistik Republik Indonesia (BPS RI)**.

---

## 🚀 Fitur Utama

### 1. 👥 Multi-Role Authentication (Login Berbasis NIP)
- **Role Admin (Tim Kerja / PIC)**: 
  - Dialihkan ke **Dashboard Admin Pembinaan JF** dengan 3 panel kartu link terpadu:
    - 🌐 **Link Internal** (E-TTD BPS, SIMPEG, Portal SDM, dll.)
    - 🌐 **Link Eksternal** (SIASN BKN, MyASN, KemenPAN-RB, dll.)
    - 🌐 **Lainnya** (Template Surat, Peraturan JF, Penetapan JF, Rekomendasi, ABK)
    - 📢 **Bar Akses Cepat**: Tombol langsung menuju `FORM USULAN` dan `RESPONSE` sheet.
- **Role Pegawai (Pengusul / Akses Portal)**:
  - Dialihkan ke **Portal Layanan JF**, mengakses seluruh modul pembinaan karir.

### 2. 🏛️ 5 Modul Layanan Portal Pegawai
1. **🔄 Alur Layanan**: Panduan interaktif 4 jalur pengangkatan JF (Pengangkatan Pertama, Perpindahan, Penyesuaian/Inpassing, dan Promosi/Kenaikan Jenjang).
2. **📝 Form Usulan**: Checklist 8 dokumen persyaratan wajib dan tombol pengisian Google Forms resmi BPS RI.
3. **🎓 Uji Kompetensi**: Informasi tahapan UKOM (Pendaftaran, CAT, Wawancara, Hasil) dan integrasi portal kompetensi BPS.
4. **📋 Regulasi**: Repositori peraturan (PermenPAN-RB, Perka BPS, Surat Edaran) dengan pencarian dokumen *real-time*.
5. **👥 Tim Kerja**: Direktori profil tim kerja pembina JF beserta kontak WhatsApp Helpdesk dan FAQ.

---

## 🔑 Akun Demo Bawaan

| Role | NIP | Password | Halaman Tujuan |
|---|---|---|---|
| **Admin / PIC** | `198501012010011001` | `password` | `/admin` (Dashboard Admin) |
| **Pegawai BPS** | `199505152020121002` | `password` | `/` (Portal Utama Pegawai) |

---

## 🛠️ Persyaratan Sistem & Instalasi

- **PHP** >= 8.2 (ekstensi OpenSSL, PDO, SQLite / MySQL aktif)
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

# 5. Jalankan Migrasi & Database Seeder (Membuat akun demo)
php artisan migrate --seed

# 6. Jalankan Server Development
php artisan serve
```

Akses aplikasi melalui browser:
👉 **http://127.0.0.1:8000**

---

## 📁 Struktur Direktori Penting

```
sijabfu/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php      # Autentikasi NIP & role redirect
│   │   │   ├── AdminController.php     # Dashboard Admin & link manager
│   │   │   └── PortalController.php    # 5 modul layanan portal pegawai
│   │   └── Middleware/
│   │       └── RoleMiddleware.php      # Pembatasan akses berbasis role
│   └── Models/
│       └── User.php                    # Model User dengan atribut NIP & Role
├── database/
│   ├── migrations/                     # Skema tabel users, sessions, dll.
│   └── seeders/DatabaseSeeder.php      # Akun demo Admin & Pegawai
├── docs/                               # File dokumentasi & slide presentasi PPTX
├── public/
│   └── images/                         # Aset logo vektor BPS & ilustrasi 3D
├── resources/
│   └── views/
│       ├── admin/                      # Template Dashboard Admin
│       ├── auth/                       # Template Login Form (NIP & Password)
│       ├── layouts/                    # Layout Master Portal
│       └── modules/                    # Halaman 5 modul layanan JF
└── routes/
    └── web.php                         # Rute aplikasi & proteksi middleware
```

---

## 📄 Lisensi & Hak Cipta
Hak Cipta © 2026 Tim Pembinaan JF — Biro SDM — Badan Pusat Statistik Republik Indonesia.
