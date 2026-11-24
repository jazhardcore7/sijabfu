<?php

namespace Tests\Feature\Phase2;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * PRD Revisi bagian 6: kolom dan tabel baru tersedia di SQLite.
 */
class SchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_has_revision_columns(): void
    {
        $this->assertTrue(Schema::hasColumns('users', ['is_active', 'sso_id', 'bidang', 'foto_path', 'kontak']));
    }

    public function test_form_usulan_has_lifecycle_columns_and_keeps_legacy_status(): void
    {
        $this->assertTrue(Schema::hasColumns('form_usulan', [
            'status', 'status_key', 'hasil_verifikasi', 'verified_by', 'verified_at',
            'surat_usulan_path', 'sertifikat_path', 'hasil_ukom', 'submitted_at',
            'link_berkas', 'link_usulan', 'catatan',
        ]));
    }

    public function test_new_tables_exist_with_their_columns(): void
    {
        $tables = [
            'usulan_berkas' => ['id', 'form_usulan_id', 'jenis_persyaratan', 'file_path', 'original_name', 'mime', 'size', 'pemeriksaan', 'catatan', 'created_at'],
            'usulan_status_logs' => ['id', 'form_usulan_id', 'actor_id', 'from_status', 'to_status', 'catatan', 'created_at'],
            'regulasi' => ['id', 'judul', 'kategori', 'nomor', 'tahun', 'file_path', 'uploaded_by', 'deleted_at', 'created_at'],
            'chat_threads' => ['id', 'pegawai_id', 'last_message_at'],
            'chat_messages' => ['id', 'thread_id', 'sender_id', 'body', 'read_at', 'created_at'],
            'notifications' => ['id', 'type', 'notifiable_type', 'notifiable_id', 'data', 'read_at'],
            'sso_sync_logs' => ['id', 'triggered_by', 'started_at', 'finished_at', 'created_count', 'updated_count', 'failed_count', 'error'],
        ];

        foreach ($tables as $table => $columns) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table}");
            $this->assertTrue(Schema::hasColumns($table, $columns), "Kolom {$table}");
        }
    }

    public function test_legacy_progress_berkas_table_is_kept(): void
    {
        $this->assertTrue(Schema::hasTable('progress_berkas'));
    }
}
