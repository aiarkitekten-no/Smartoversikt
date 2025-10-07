<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('widgets')->updateOrInsert(
            ['key' => 'mail.imap'],
            [
                'key' => 'mail.imap',
                'name' => 'IMAP Mailbox',
                'description' => 'E-postboks statistikk via IMAP',
                'category' => 'mail',
                'default_refresh_interval' => 300,
                'is_active' => true,
                'order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('widgets')->where('key', 'mail.imap')->delete();
    }
};
