<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username', 64)->nullable()->after('name');
        });

        $used = [];
        $rows = DB::table('users')->select('id', 'email', 'username')->orderBy('id')->get();

        foreach ($rows as $row) {
            if (filled($row->username)) {
                $used[(string) $row->username] = true;

                continue;
            }

            $email = (string) $row->email;
            $local = strstr($email, '@', true) ?: $email;
            $base = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '_', $local));
            $base = trim($base, '_') ?: 'user';
            $base = Str::limit($base, 64, '');

            $candidate = $base;
            $n = 0;
            while (isset($used[$candidate])) {
                $suffix = '_'.(++$n);
                $candidate = Str::limit($base, 64 - strlen($suffix), '').$suffix;
            }

            $used[$candidate] = true;

            DB::table('users')->where('id', $row->id)->update(['username' => $candidate]);
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->string('username', 64)->nullable(false)->change();
            $table->unique('username');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
