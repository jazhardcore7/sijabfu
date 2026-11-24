# SIJABFU — Technical Snapshot (as of commit `78977c1`)

## 1. Repo identity

- **Remote:** `origin https://github.com/jazhardcore7/sijabfu.git` (fetch/push), from `git remote -v`
- **Current branch:** `main`
- **Working tree:** clean (`git status --short` printed nothing)
- **Extra worktree:** `.kilo/worktrees/whispering-plutonium` is checked out at `fb50b9d` (detached HEAD), from `git worktree list`
- **Commits:** the repo has only 5 commits, fewer than the 10 requested.

| Date | Message |
|---|---|
| 2026-11-17 | feat: add PortalController, views, and web routes for portal module |
| 2026-11-14 | feat: add SIJABFU portal controllers, models, migrations, seeders, views, and documentation |
| 2026-10-12 | feat: add progress berkas tracking feature and modules |
| 2026-10-12 | feat: add bps favicon to public directory |
| 2026-10-08 | feat: inisialisasi Portal Pembinaan Jabatan Fungsional BPS RI (SIJABFU) |

## 2. Tech stack

- **Language / framework:** PHP `^8.2`, Laravel `^12.0`, laravel/tinker `^2.10.1` (`composer.json`)
- **Database:** SQLite. `DB_CONNECTION=sqlite` is set in `.env` and `.env.example`, and the file is `database/database.sqlite`. The README mentions SQLite/MySQL as options.
- **ORM:** Eloquent (`app/Models/*.php`)
- **UI:** Blade templates. Nearly every view has its own large inline `<style>` block and loads Google Fonts (`resources/views/layouts/app.blade.php:8-10`). There is no component library.
- **CSS framework:** Tailwind `^4.3.3` and `@tailwindcss/vite` are installed (`package.json`, `vite.config.js`). Only `resources/views/welcome.blade.php` calls `@vite`. The app views use hand-written inline CSS, not Tailwind.
- **JS:** Vite `^7.0.7`, laravel-vite-plugin `^2.0.0`, axios `^1.11.0` (`package.json`). `resources/js/app.js` is 1 line long. Pages use small inline scripts.
- **Auth library:** Laravel's built-in session guard with the Eloquent provider (`config/auth.php:42,66`). There is no Breeze, Fortify, Sanctum, or Socialite.
- **File storage:** none in use. `FILESYSTEM_DISK=local` is in `.env.example`, but no code calls `Storage::` or handles uploads (grep of `app` and `routes`). Documents are submitted as external links (Laci BPS / Google Drive).
- **Tests:** PHPUnit `^11.5.50`, Mockery, Collision (`composer.json`), with in-memory SQLite for tests (`phpunit.xml`).
- **How to run** (README): `cp .env.example .env` → `composer install` → `php artisan key:generate` → `php artisan migrate --seed` → `php artisan serve`. The `composer.json` scripts are `setup`, `dev` (serve + queue + pail + vite via concurrently), and `test`.

## 3. Folder structure

```
sijabfu/
├── app/
│   ├── Http/Controllers/   # AuthController, AdminController, PortalController
│   ├── Http/Middleware/    # RoleMiddleware (role:admin check)
│   ├── Models/             # User, FormUsulan, ProgressBerkas
│   └── Providers/          # AppServiceProvider (default)
├── bootstrap/              # app.php: routing and the 'role' middleware alias
├── config/                 # Laravel default configs
├── database/
│   ├── migrations/         # users/cache/jobs + nip/role, progress_berkas, form_usulan
│   ├── seeders/            # DatabaseSeeder: 3 demo users + 1 proposal
│   └── factories/          # UserFactory (default)
├── docs/                   # Business-process document (.md) and slide deck (.pptx)
├── public/images/          # BPS logo, emblem, staff photo
├── resources/views/
│   ├── admin/              # dashboard, monitoring
│   ├── auth/               # login
│   ├── layouts/            # app.blade.php (layout for the employee portal)
│   └── modules/            # alur-layanan, form-usulan, progress-berkas, regulasi, tim-kerja
├── routes/                 # web.php, console.php
├── tests/                  # Feature and Unit example tests only
└── .kilo/worktrees/        # separate git worktree (tool artifact)
```

## 4. Authentication and roles

**Login is local: NIP + password.**
- `AuthController::login` validates `nip` and `password`, then calls `Auth::attempt($credentials, $remember)` (`app/Http/Controllers/AuthController.php:28-57`). The form posts `name="nip"` (`resources/views/auth/login.blade.php:550`). The controller accepts a `remember` flag, but the login view has no remember checkbox (grep found no match).

**SSO is not real.** The "LOGIN SSO" button runs JavaScript that fills in the demo pegawai's credentials and submits the normal form (`resources/views/auth/login.blade.php:640-647`):

```js
function handleSSO() { ...
    setTimeout(() => {
        fillCredentials('199505152020121002', 'password');
        document.getElementById('loginForm').submit();
    }, 350);
```

**Role storage.** Roles live in the `users.role` column: `string(20)`, default `'pegawai'` (`database/migrations/2026_10_08_025416_add_nip_and_role_to_users_table.php:16`).

**Roles that exist.** There are only two: `'admin'` and `'pegawai'` (`app/Models/User.php:31-39`). The seeder sets admin's `jabatan` to "PIC Pembinaan Jabatan Fungsional". There is no separate Tim Pembinaan JF role and no Super Admin role.

```php
public function isAdmin(): bool { return $this->role === 'admin'; }
public function isPegawai(): bool { return $this->role === 'pegawai'; }
```

**Enforcement is server-side.**
- The `role` alias points to `RoleMiddleware` (`bootstrap/app.php:14-16`).
- The middleware checks only `$role === 'admin' && !isAdmin()`. A non-admin is redirected to `home` with an error (`app/Http/Middleware/RoleMiddleware.php:26-28`).
- `/admin/*` routes use `['auth','role:admin']` (`routes/web.php:18`).
- Portal routes use only `auth`, so admins can open them too (`routes/web.php:25`).
- There are no policies or gates.

**After login:**
- An admin goes to `admin.dashboard`.
- A pegawai goes to `home` (`AuthController.php:45-51`).

## 5. Database schema

**`users`** (`database/migrations/0001_01_01_000000_create_users_table.php`, plus `2026_10_08_025416_...`)
- Fields: `id`, `nip` (string 25, nullable, unique), `name`, `email` (unique), `email_verified_at`, `password`, `role` (default `pegawai`), `jabatan`, `unit_kerja`, `remember_token`, timestamps.
- That base migration also creates `password_reset_tokens` and `sessions`.
- Relationships: `FormUsulan` and `ProgressBerkas` have `belongsTo User`. `User` defines no inverse relationships (`app/Models/User.php`).

**`form_usulan`**: the active proposal table (`database/migrations/2026_11_13_100119_create_form_usulan_table.php`; model `app/Models/FormUsulan.php`)
- Fields: `id`, `user_id` (FK, cascade), `email`, `jabatan_fungsional`, `jenis_usulan`, `nomor_surat`, `hal_surat`, `nama_lengkap`, `nip` (30), `satuan_kerja`, `link_berkas` (text), `link_usulan` (text, nullable), `status` (tinyInteger, default 1), `catatan` (text, nullable), timestamps.
- Status values from `FormUsulan::statusLabel()` (`app/Models/FormUsulan.php:105-111`):

```php
1 => 'Diusulkan',
2 => 'Sedang Diverifikasi',
3 => 'Jadwal Ditetapkan',
4 => 'Undangan Terkirim',
5 => 'Selesai',
```

- Stage labels from `FormUsulan::tahapan()` (`FormUsulan.php:26-70`):
  1. "Mengusulkan Peserta"
  2. "Verifikasi Kelayakan"
  3. "Penetapan Jadwal"
  4. "Undangan & Pemberitahuan"
  5. "Konfirmasi & Unggah Dokumen"
- Per-step display labels: `'Selesai' | 'Sedang Diproses' | 'Belum Mulai'` (`FormUsulan.php:83-88`).
- `jenis_usulan` options: 'Uji Kompetensi Kenaikan Jenjang', 'Uji Kompetensi Perpindahan Jabatan Lain', 'Konsultasi JF', 'Inpassing' (`FormUsulan.php:154-161`).
- `jabatan_fungsional`: a hardcoded list of 33 JF names (`FormUsulan.php:115-152`).

**`progress_berkas`**: legacy, unused (`database/migrations/2026_10_12_023512_create_progress_berkas_table.php`; model `app/Models/ProgressBerkas.php`)
- Fields: `user_id` (FK), `nomor_usulan` (unique), `jenis_jabatan`, `jenjang`, six tinyInteger stage columns (`status_verifikasi`, `status_surat_usulan`, `status_pengelola`, `status_ukom`, `status_instansi_pembina`, `status_pengumuman`), `catatan`, `tanggal_usulan`.
- Stage values: `0 => 'Belum Mulai', 1 => 'Sedang Diproses', 2 => 'Selesai', 3 => 'Ditolak'` (`ProgressBerkas.php:67-75`).

**Framework tables:** `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, from the Laravel default migrations `0001_01_01_000001/000002`, which were not opened in detail.

There are no tables for regulasi, chat, notifications, sertifikat, or surat usulan files.

## 6. Routes, pages, and API endpoints

All routes are in `routes/web.php`. There are no API routes (`routes/api.php` does not exist).

| Route | Method | Role access | File | Purpose |
|---|---|---|---|---|
| `/login` | GET | guest | AuthController@showLoginForm | Login page (`auth/login.blade.php`) |
| `/login` | POST | guest | AuthController@login | NIP/password auth, role redirect |
| `/logout` | POST | auth | AuthController@logout | Logout and invalidate the session |
| `/admin` | GET | auth + admin | AdminController@index | Admin dashboard: link cards and status table |
| `/admin/monitoring` | GET | auth + admin | AdminController@monitoring | Filter/search/paginate proposals, stage counts |
| `/admin/usulan/{id}/status` | POST | auth + admin | AdminController@updateStatus | Set status 1–5 and catatan |
| `/` | GET | auth (any role) | PortalController@index | Landing page (`landing.blade.php`) |
| `/alur-layanan` | GET | auth | PortalController@alurLayanan | Static 6-step flow description |
| `/form-usulan` | GET | auth | PortalController@formUsulan | Proposal form, or the existing proposal |
| `/form-usulan` | POST | auth | PortalController@formUsulanStore | Create one proposal per user (status 1) |
| `/progress-berkas` | GET | auth | PortalController@progressBerkas | The user's latest proposal and its 5-stage tracker |
| `/regulasi` | GET | auth | PortalController@regulasi | Hardcoded regulation list |
| `/tim-kerja` | GET | auth | PortalController@timKerja | Hardcoded team list and contact info |
| `/uji-kompetensi` | GET | auth | routes/web.php:33 | Redirects to `/progress-berkas` |
| `/up` | GET | public | bootstrap/app.php:11 | Laravel health check |

## 7. Feature inventory per role

There are only two roles in code. "Tim Pembinaan JF" and "Super Admin" both map to the single `admin` role. Super Admin has no distinct features.

| Role | Menu / page | What it does (file) |
|---|---|---|
| Pegawai | Landing `/` | Hero section with hardcoded stats (500+, 33, 5, 2026), 5 module cards, live clock (`resources/views/landing.blade.php`) |
| Pegawai | Alur Layanan | Static 6 steps from Verifikasi to Pengumuman (`PortalController.php:17-65`, `modules/alur-layanan.blade.php`) |
| Pegawai | Form Usulan | Text form with name/NIP/email/satker prefilled from the user, JF and jenis dropdowns, and link fields; one submission per user (`modules/form-usulan.blade.php`, `PortalController.php:78-141`) |
| Pegawai | Progress Berkas | Progress % (status/5), Tim catatan, 5-stage tracker, links to the submitted berkas (`modules/progress-berkas.blade.php`) |
| Pegawai | Regulasi | Grouped table with client-side search and category filter; "Unduh" links are `#` (`modules/regulasi.blade.php`, `PortalController.php:143-212`) |
| Pegawai | Tim Kerja | 6 hardcoded member cards and a static email/phone/address/hours block (`modules/tim-kerja.blade.php`, `PortalController.php:214-268`) |
| Pegawai | Header | Breadcrumb, date/time, user name, Logout; an "Admin" badge link shows only for admins (`layouts/app.blade.php:495-528`) |
| Admin (Tim/PIC) | Dashboard `/admin` | 3 cards of hardcoded links (Internal, Eksternal, Lainnya), status table with an inline status/catatan form, Akses Cepat bar (`admin/dashboard.blade.php`, `AdminController.php:14-147`) |
| Admin (Tim/PIC) | Monitoring `/admin/monitoring` | Count per stage (clickable filters), search, paginated cards with tracker, status and catatan form (`admin/monitoring.blade.php`, `AdminController.php:152-187`) |
| Admin (Tim/PIC) | Portal Pegawai | Admin can open every pegawai page (`routes/web.php:25`) |
| Super Admin | — | NOT FOUND (no role or pages) |

## 8. Proposal workflow as implemented

**Creation.** A pegawai submits `/form-usulan`. The controller rejects the request if the user already has any `form_usulan` row, then creates one with `status = 1` (`PortalController.php:97-138`).

**Transitions.** Only the admin changes status, through `POST /admin/usulan/{id}/status` with validation `'status' => 'required|integer|min:1|max:5'` and an optional `catatan` of up to 1000 characters (`AdminController.php:192-207`).
- Any value from 1 to 5 can be set in any order. There is no state machine, guard, or history log.
- The UI for this is a `<select>` in both `admin/dashboard.blade.php` and `admin/monitoring.blade.php`.

**Perbaikan.** There is no Perbaikan status. The only mechanism is the free-text `catatan`. The monitoring textarea placeholder mentions "perbaikan berkas" (`admin/monitoring.blade.php:~992`), and the pegawai sees the catatan on Progress Berkas (`modules/progress-berkas.blade.php:283-288`). The pegawai cannot edit or resubmit, because the one-submission rule applies (`PortalController.php:97`).

**MS/TMS.** NOT FOUND. There is no such field or status anywhere.

**Surat usulan.** No upload or download exists. The pegawai pastes a text link (`link_usulan`, optional) and a berkas link (`link_berkas`, required). Validation is `string|max:500`, not a URL check, although the HTML input is `type="url"` (`PortalController.php:110-111`, `modules/form-usulan.blade.php:619-626`). The admin opens these as external links (`admin/dashboard.blade.php`, `admin/monitoring.blade.php`).

**Sertifikat UKOM.** NOT FOUND.

## 9. Integrations

- **SIMPEG/SIASN sync:** NOT FOUND. These appear only as hardcoded outbound links on the admin dashboard (`AdminController.php:24-28, 58-62`).
- **SSO data sync:** NOT FOUND. The SSO button is a client-side demo shortcut (`auth/login.blade.php:640-647`).
- **Notifications:** NOT FOUND. Only session flash messages exist (`success`/`error`/`info`). `User` uses the `Notifiable` trait, but nothing sends notifications (`app/Models/User.php:14`).
- **Chat/messenger:** NOT FOUND. There is no polling, websocket, or WhatsApp link (grep of `resources/views`). The only `setInterval` calls update the clock (`layouts/app.blade.php:565`, `landing.blade.php:696`).
- **Regulation upload/preview:** NOT FOUND. The list is a hardcoded PHP array with `'file' => '#'` (`PortalController.php:143-212`).
- **Dark/light theme:** NOT FOUND in the app views. Only the unused Laravel `welcome.blade.php` has `prefers-color-scheme`. The many "dark" matches are CSS variable names such as `--bps-dark`.
- **Dashboard charts:** NOT FOUND (no canvas or chart library). Monitoring shows count tiles and CSS progress bars (`admin/monitoring.blade.php`).
- **External links:** E-TTD, SIMPEG, SDM, Kinerja, Intranet, BPS, SIASN, BKN, MyASN, KemenPAN-RB, JDIHN, Pusbin, plus several dummy URLs (`AdminController.php:17-140`).

## 10. PDF feature checklist

| Area | Item | Status | File | Note |
|---|---|---|---|---|
| Pegawai | Dashboard | DIFFERENT | resources/views/landing.blade.php | Marketing-style landing with module cards and static numbers |
| Pegawai | Sync SIMPEG/SIASN | MISSING | app/Http/Controllers/PortalController.php | No sync; fields prefilled from the local `users` row |
| Pegawai | Entri data | IMPLEMENTED | resources/views/modules/form-usulan.blade.php | Text form, validated in `PortalController.php:101-123` |
| Pegawai | Upload berkas persyaratan | DIFFERENT | resources/views/modules/form-usulan.blade.php:613-626 | Pastes a Drive/Laci link; no file upload |
| Pegawai | Kirim usulan | IMPLEMENTED | app/Http/Controllers/PortalController.php:92-141 | One submission per user, status 1 |
| Pegawai | Status Verifikasi/Perbaikan | PARTIAL | resources/views/modules/progress-berkas.blade.php | Stage 2 "Verifikasi Kelayakan" exists; Perbaikan only via catatan |
| Pegawai | Status Disetujui MS/TMS | MISSING | app/Models/FormUsulan.php | No MS/TMS value |
| Pegawai | Ditetapkan + surat usulan preview/download | MISSING | app/Models/FormUsulan.php | No document storage; stage 3 is "Penetapan Jadwal" |
| Pegawai | Selesai + sertifikat preview/download | PARTIAL | app/Models/FormUsulan.php:110 | Status 5 'Selesai' exists; no sertifikat |
| Pegawai | Regulasi list | IMPLEMENTED | resources/views/modules/regulasi.blade.php | Hardcoded array, client-side search |
| Pegawai | Regulasi download | PARTIAL | app/Http/Controllers/PortalController.php:153 | Button exists; link is `#` |
| Pegawai | Regulasi preview | MISSING | resources/views/modules/regulasi.blade.php | No preview |
| Pegawai | Tim Kerja list | IMPLEMENTED | resources/views/modules/tim-kerja.blade.php | 6 hardcoded members |
| Pegawai | Tim Kerja profile | PARTIAL | resources/views/modules/tim-kerja.blade.php | Card only (name, NIP, jabatan, bidang); no profile page |
| Pegawai | Tim Kerja chat | MISSING | resources/views/modules/tim-kerja.blade.php | Static contact block only |
| Pegawai | Chat Box | MISSING | — | NOT FOUND |
| Tim JF | Dashboard | DIFFERENT | resources/views/admin/dashboard.blade.php | Link-portal cards and status table |
| Tim JF | Daftar pegawai (verifikasi) | PARTIAL | resources/views/admin/monitoring.blade.php | Lists proposals, not pegawai |
| Tim JF | Berkas | PARTIAL | resources/views/admin/monitoring.blade.php | External link to berkas |
| Tim JF | Pemeriksaan | MISSING | — | No checklist or per-document review |
| Tim JF | Perbaikan | PARTIAL | app/Http/Controllers/AdminController.php:192-207 | Free-text catatan only |
| Tim JF | MS/TMS | MISSING | — | NOT FOUND |
| Tim JF | Monitoring UKOM: update status | IMPLEMENTED | app/Http/Controllers/AdminController.php:192-207 | Any of 1–5, unrestricted |
| Tim JF | Upload surat usulan/sertifikat | MISSING | — | No upload code |
| Tim JF | Regulasi CRUD and preview | MISSING | app/Http/Controllers/PortalController.php:143 | Read-only hardcoded list; no admin route |
| Tim JF | Messenger | MISSING | — | NOT FOUND |
| Super Admin | Dashboard | MISSING | app/Models/User.php | No super-admin role |
| Super Admin | Sinkronisasi Data SSO | MISSING | — | NOT FOUND |
| Super Admin | Monitoring UKOM | DIFFERENT | resources/views/admin/monitoring.blade.php | Shared with the single admin role |
| Super Admin | Regulasi | MISSING | — | NOT FOUND |
| Super Admin | Daftar User | MISSING | — | No user-management route |
| All | Navbar | IMPLEMENTED | resources/views/layouts/app.blade.php; admin/*.blade.php | Separate navbars for portal and admin |
| All | Notifications | MISSING | — | Flash messages only |
| All | Profile with user level | PARTIAL | admin/dashboard.blade.php; layouts/app.blade.php | Name shown; "ADMIN" badge on admin pages; no profile page |
| All | Charts | MISSING | admin/monitoring.blade.php | Count tiles only |
| All | Logout | IMPLEMENTED | app/Http/Controllers/AuthController.php:62-70 | POST /logout |
| All | Dark/light theme | MISSING | — | NOT FOUND in app views |

## 11. Code that exists but is not in the checklist

- **Alur Layanan page:** static 6-step flow (`PortalController.php:17-65`).
- **Admin link portal:** Link Internal, Eksternal, Lainnya, plus an Akses Cepat bar with a "RESPONSE" Google Sheets dummy link (`AdminController.php:17-140`).
- **Demo-account buttons on login:** Admin PIC, "Sudah Usul" Budi, "Belum Usul" Siti (`auth/login.blade.php:600-614`).
- **Legacy table:** the `progress_berkas` table and model use a 6-stage, 0–3 status scheme, including 'Ditolak'. They are unused: the model is imported in `PortalController.php:7` but never called.
- **`/uji-kompetensi` redirect** (`routes/web.php:33`).
- **Unused default `welcome.blade.php`:** it references `route('register')`, which does not exist.
- **`docs/`:** `DOKUMENTASI_PROSES_BISNIS_SIJABFU.md`, which covers RACI, process levels, and the 8 required documents, and `Dokumentasi_SIJABFU_BPS_RI.pptx`.
- **"Live Monitoring Active" badge:** a static label (`admin/monitoring.blade.php`).

## 12. Known problems

- **TODO/FIXME:** none found. One placeholder, `ext. 3xxxx`, is in `modules/tim-kerja.blade.php:266`.
- **Dead code:** the `ProgressBerkas` model and migration, the unused import in `PortalController.php:7`, and `welcome.blade.php`.
- **Hardcoded data:**
  - regulations: `PortalController.php:145-209`
  - team members with names and NIPs: `PortalController.php:216-265`
  - dummy URLs (`docs.google.com/.../dummy-...`, `sijabfu.bps.go.id/dummy-...`): `AdminController.php:100-139`
  - landing stats: `landing.blade.php`
  - the 33-item JF list: `FormUsulan.php:115-152`
- **Demo credentials are public:** they appear in `README.md`, in `DatabaseSeeder.php`, and in login-page JavaScript (`auth/login.blade.php:600-614, 643`). The fake SSO logs anyone in as a seeded pegawai.
- **Missing validation:**
  - `link_berkas`/`link_usulan` are validated as `string`, not `url` (`PortalController.php:110-111`). The admin views render them as `href` values, so a `javascript:` URL is possible because Blade escaping does not sanitize URL schemes.
  - `nip`/`nama_lengkap` on the form can differ from the logged-in user (`PortalController.php:107-108`).
- **No workflow guard:** status can jump or go backwards, and there is no audit trail (`AdminController.php:192-207`).
- **No authorization granularity:** any admin can update any proposal, there is no policy, and admins can submit proposals on the portal.
- **No rate limiting on login:** no throttle middleware (`routes/web.php:9-12`).
- **File-type/size checks:** not applicable, because there is no upload.
- **Tests:**
  - Only the example tests exist (`tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`).
  - The Feature test expects `GET /` to return 200, but `/` requires auth and will redirect. It is likely to fail (not executed).

## 13. Environment

**Variable names** (`.env.example`; `.env` has the same set minus `APP_NAME`; values omitted): APP_NAME, APP_ENV, APP_KEY, APP_DEBUG, APP_URL, APP_LOCALE, APP_FALLBACK_LOCALE, APP_FAKER_LOCALE, APP_MAINTENANCE_DRIVER, BCRYPT_ROUNDS, LOG_CHANNEL, LOG_STACK, LOG_DEPRECATIONS_CHANNEL, LOG_LEVEL, DB_CONNECTION, SESSION_DRIVER, SESSION_LIFETIME, SESSION_ENCRYPT, SESSION_PATH, SESSION_DOMAIN, BROADCAST_CONNECTION, FILESYSTEM_DISK, QUEUE_CONNECTION, CACHE_STORE, MEMCACHED_HOST, REDIS_CLIENT, REDIS_HOST, REDIS_PASSWORD, REDIS_PORT, MAIL_MAILER, MAIL_SCHEME, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS, MAIL_FROM_NAME, AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION, AWS_BUCKET, AWS_USE_PATH_STYLE_ENDPOINT, VITE_APP_NAME.

There are no SSO, SIMPEG, or SIASN variables.

**External services:**
- Required: none. The app runs on SQLite with database-backed sessions and queue (`.env.example`).
- Runtime: Google Fonts CDN (`layouts/app.blade.php:8-10`), plus outbound links only (BPS, BKN, Google Docs/Drive, Laci BPS).

---

**Verification:** every row above cites a file that was opened during inspection. No secret values are included; the only credentials referenced are the public demo ones already in `README.md`. At the time of inspection, `git status --short` printed nothing (working tree clean, no files modified).
