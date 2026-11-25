<?php

namespace Tests\Feature\Phase9;

use App\Models\FormUsulan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use Tests\TestCase;

/**
 * PRD Revisi bagian 10 (Definition of done):
 * - setiap FR di bagian 5 punya minimal satu test;
 * - alur lengkap end to end diuji;
 * - menu tiap role sesuai bagian 3 dan server memblokir sisanya;
 * - data lama tetap ada dengan status terpetakan (diuji di Fase 2).
 */
class DefinitionOfDoneTest extends TestCase
{
    use RefreshDatabase;

    private const NS = 'Tests\\Feature\\';

    /** FR-ID => [kelas test (relatif ke Tests\Feature), method] */
    private const FR_TESTS = [
        'FR-AUTH-1' => ['Phase8\\SsoLoginTest', 'test_known_nip_logs_in_and_is_redirected_by_role'],
        'FR-AUTH-2' => ['Phase8\\SsoLoginTest', 'test_views_contain_no_hardcoded_credentials_and_sso_button_uses_adapter'],
        'FR-AUTH-3' => ['Phase8\\SsoLoginTest', 'test_local_login_disabled_by_flag'],
        'FR-AUTH-4' => ['Phase1\\LoginThrottleTest', 'test_sixth_attempt_within_a_minute_returns_429'],
        'FR-RBAC-1' => ['Phase1\\RoleAccessMatrixTest', 'test_each_role_against_each_route_group'],
        'FR-RBAC-2' => ['Phase3\\FileAccessTest', 'test_other_pegawai_gets_403'],
        'FR-RBAC-3' => ['Phase1\\PengajuanRoleTest', 'test_staff_post_to_pengajuan_returns_403'],
        'FR-DASH-1' => ['Phase1\\SharedLayoutTest', 'test_page_renders_the_five_layout_elements'],
        'FR-DASH-2' => ['Phase1\\SidebarMenuTest', 'test_each_role_sees_exactly_its_menu_items'],
        'FR-DASH-3' => ['Phase7\\DashboardTest', 'test_pegawai_dashboard_shows_correct_values_at_each_status'],
        'FR-DASH-4' => ['Phase7\\DashboardTest', 'test_staff_chart_data_equals_database_counts'],
        'FR-DASH-5' => ['Phase7\\DashboardTest', 'test_super_admin_sees_sso_card_with_latest_log_and_users_per_role'],
        'FR-DASH-6' => ['Phase1\\ThemeTest', 'test_authenticated_page_has_theme_script_tokens_and_toggle'],
        'FR-NOT-1' => ['Phase7\\NotificationTest', 'test_status_changes_notify_the_owner_once_each'],
        'FR-PU-1' => ['Phase8\\SimpegSyncTest', 'test_sync_success_fills_nama_nip_jabatan_and_unit_kerja'],
        'FR-PU-2' => ['Phase3\\PengajuanTest', 'test_identity_is_taken_from_logged_in_user_and_posted_values_are_ignored'],
        'FR-PU-3' => ['Phase3\\PengajuanTest', 'test_file_is_stored_privately_with_random_name'],
        'FR-PU-4' => ['Phase3\\PengajuanTest', 'test_kirim_is_refused_when_a_slot_is_empty'],
        'FR-PU-5' => ['Phase3\\PengajuanTest', 'test_perbaikan_allows_edit_replace_and_kirim_ulang'],
        'FR-PU-6' => ['Phase3\\PengajuanTest', 'test_new_usulan_is_only_allowed_after_previous_is_selesai'],
        'FR-PU-7' => ['Phase3\\PelacakanStatusTest', 'test_legacy_links_render_only_for_http_and_https'],
        'FR-PS-1' => ['Phase3\\PelacakanStatusTest', 'test_timeline_lists_each_transition_with_date_and_actor_role'],
        'FR-PS-2' => ['Phase3\\PelacakanStatusTest', 'test_perbaikan_banner_lists_flagged_files'],
        'FR-PS-3' => ['Phase3\\PelacakanStatusTest', 'test_ms_or_tms_label_matches_stored_value'],
        'FR-PS-4' => ['Phase3\\PelacakanStatusTest', 'test_page_renders_correctly_at_each_status'],
        'FR-PS-5' => ['Phase3\\PelacakanStatusTest', 'test_selesai_shows_hasil_ukom_when_present'],
        'FR-PS-6' => ['Phase3\\FileAccessTest', 'test_owner_gets_200_with_inline_preview_and_attachment_download'],
        'FR-VB-1' => ['Phase4\\VerifikasiTest', 'test_list_filters_by_status_and_search_combine_and_paginate_by_15'],
        'FR-VB-2' => ['Phase4\\VerifikasiTest', 'test_detail_lists_every_berkas_and_each_opens'],
        'FR-VB-3' => ['Phase4\\VerifikasiTest', 'test_pemeriksaan_marks_persist_per_berkas'],
        'FR-VB-4' => ['Phase4\\VerifikasiTest', 'test_tms_without_reason_returns_422_and_changes_nothing'],
        'FR-VB-5' => ['Phase4\\VerifikasiTest', 'test_decision_outside_verifikasi_returns_422'],
        'FR-MON-1' => ['Phase4\\MonitoringTest', 'test_counts_equal_database_and_filters_work'],
        'FR-MON-2' => ['Phase4\\MonitoringTest', 'test_disallowed_transition_returns_422_and_changes_nothing'],
        'FR-MON-3' => ['Phase2\\UsulanStateMachineTest', 'test_full_lifecycle_writes_one_log_per_change'],
        'FR-MON-4' => ['Phase4\\MonitoringTest', 'test_transition_without_file_is_rejected'],
        'FR-MON-5' => ['Phase4\\MonitoringTest', 'test_tim_jf_cannot_rollback'],
        'FR-REG-1' => ['Phase5\\RegulasiTest', 'test_preview_and_download_work_for_record_with_file'],
        'FR-REG-2' => ['Phase5\\RegulasiTest', 'test_pegawai_gets_403_on_admin_actions'],
        'FR-REG-3' => ['Phase5\\RegulasiTest', 'test_seeded_rows_exist_without_file_and_are_disabled'],
        'FR-TK-1' => ['Phase6\\TimKerjaTest', 'test_adding_a_tim_jf_user_makes_it_appear'],
        'FR-CHAT-1' => ['Phase6\\ChatTest', 'test_pegawai_message_creates_thread_and_appears_in_tim_jf_inbox_on_next_poll'],
        'FR-CHAT-2' => ['Phase6\\ChatTest', 'test_staff_reply_appears_for_pegawai_within_one_poll'],
        'FR-CHAT-3' => ['Phase6\\ChatTest', 'test_super_admin_can_view_all_threads_and_reply'],
        'FR-CHAT-4' => ['Phase6\\ChatTest', 'test_html_is_shown_as_text'],
        'FR-USR-1' => ['Phase8\\UserManagementTest', 'test_last_active_super_admin_is_protected'],
        'FR-USR-2' => ['Phase8\\SsoSyncTest', 'test_run_creates_and_updates_users_and_never_changes_role'],
        'FR-USR-3' => ['Phase8\\SsoLoginTest', 'test_full_local_flow_with_fake_driver'],
    ];

    /** semua FR di PRD bagian 5 */
    private const PRD_FRS = [
        'FR-AUTH-1', 'FR-AUTH-2', 'FR-AUTH-3', 'FR-AUTH-4', 'FR-RBAC-1', 'FR-RBAC-2', 'FR-RBAC-3',
        'FR-DASH-1', 'FR-DASH-2', 'FR-DASH-3', 'FR-DASH-4', 'FR-DASH-5', 'FR-DASH-6', 'FR-NOT-1',
        'FR-PU-1', 'FR-PU-2', 'FR-PU-3', 'FR-PU-4', 'FR-PU-5', 'FR-PU-6', 'FR-PU-7',
        'FR-PS-1', 'FR-PS-2', 'FR-PS-3', 'FR-PS-4', 'FR-PS-5', 'FR-PS-6',
        'FR-VB-1', 'FR-VB-2', 'FR-VB-3', 'FR-VB-4', 'FR-VB-5',
        'FR-MON-1', 'FR-MON-2', 'FR-MON-3', 'FR-MON-4', 'FR-MON-5',
        'FR-REG-1', 'FR-REG-2', 'FR-REG-3', 'FR-TK-1',
        'FR-CHAT-1', 'FR-CHAT-2', 'FR-CHAT-3', 'FR-CHAT-4',
        'FR-USR-1', 'FR-USR-2', 'FR-USR-3',
    ];

    public function test_every_prd_fr_has_an_existing_test(): void
    {
        $this->assertSame([], array_values(array_diff(self::PRD_FRS, array_keys(self::FR_TESTS))), 'FR tanpa test');

        foreach (self::FR_TESTS as $fr => [$class, $method]) {
            $fqcn = self::NS.$class;
            $this->assertTrue(class_exists($fqcn), "{$fr}: kelas {$fqcn} tidak ada");
            $this->assertTrue((new ReflectionClass($fqcn))->hasMethod($method), "{$fr}: method {$method} tidak ada");
        }
    }

    public function test_end_to_end_flow_test_exists(): void
    {
        $this->assertTrue((new ReflectionClass(self::NS.'Phase4\\FullFlowTest'))->hasMethod('test_full_flow_end_to_end'));
    }

    public function test_each_role_menu_matches_prd_section_3_and_other_routes_are_blocked(): void
    {
        $expected = [
            User::ROLE_PEGAWAI => ['Dashboard', 'Pengajuan Usulan', 'Pelacakan Status', 'Regulasi', 'Tim Kerja', 'Chat Box'],
            User::ROLE_TIM_JF => ['Dashboard', 'Verifikasi Berkas Usulan Pegawai', 'Monitoring Progress UKOM', 'Regulasi', 'Chat Box (Messenger)'],
            User::ROLE_SUPER_ADMIN => ['Dashboard', 'Sinkronisasi Data SSO', 'Monitoring Progress UKOM', 'Regulasi', 'Daftar User'],
        ];

        foreach ($expected as $role => $labels) {
            $html = $this->actingAs(User::factory()->create(['role' => $role]))->get('/dashboard')->assertOk()->getContent();
            preg_match('/<nav class="sidebar-nav".*?<\/nav>/s', $html, $nav);
            preg_match_all('/<div class="nav-icon">.*?<\/div>\s*([^<]+?)\s*(?:<span|<\/a>)/s', $nav[0], $m);
            $this->assertSame($labels, array_map('trim', $m[1]), "Menu {$role}");
        }

        // server memblokir route role lain
        $pegawai = User::factory()->pegawai()->create();
        foreach (['/verifikasi', '/monitoring', '/messenger', '/admin/regulasi', '/admin/users', '/admin/sso-sync'] as $uri) {
            $this->actingAs($pegawai)->get($uri)->assertForbidden();
        }
        $timJf = User::factory()->timJf()->create();
        foreach (['/pengajuan', '/pelacakan-status', '/chat', '/admin/users', '/admin/sso-sync'] as $uri) {
            $this->actingAs($timJf)->get($uri)->assertForbidden();
        }
    }

    public function test_dead_code_removed_and_legacy_data_kept(): void
    {
        $this->assertFileDoesNotExist(resource_path('views/welcome.blade.php'));
        $this->assertFileDoesNotExist(app_path('Models/ProgressBerkas.php'));
        $this->assertFalse(method_exists(FormUsulan::class, 'tahapan'));
        $this->assertFalse(Route::has('register'));

        // tabel dan kolom lama tetap (penghapusan perlu migrasi destruktif yang disetujui)
        $this->assertTrue(Schema::hasTable('progress_berkas'));
        $this->assertTrue(Schema::hasColumn('form_usulan', 'status'));
    }

    public function test_seeder_demo_accounts_have_no_known_password(): void
    {
        $this->seed();

        // 4 akun awal + 4 akun satker daerah (FR-SATKER-1, S1)
        $this->assertSame(8, User::count());
        foreach (User::all() as $user) {
            $this->assertFalse(Hash::check('password', $user->password), $user->nip);
        }
    }

    public function test_seeder_creates_no_demo_accounts_in_production(): void
    {
        $this->app['env'] = 'production';
        try {
            // panggil seeder langsung (artisan db:seed di production meminta konfirmasi interaktif)
            (new DatabaseSeeder)->setContainer($this->app)->run();
        } finally {
            $this->app['env'] = 'testing';
        }

        $this->assertSame(0, User::count());
    }
}
