<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add username column as nullable initially to allow backfill
            $table->string('username')->nullable()->unique()->after('name');
        });

        // Backfill usernames from email local-part if missing
        $users = DB::table('users')->select('id', 'email', 'username')->get();
        foreach ($users as $user) {
            if (empty($user->username) && !empty($user->email)) {
                $local = $user->email;
                $atPos = strpos($local, '@');
                if ($atPos !== false) {
                    $local = substr($local, 0, $atPos);
                }
                // Ensure uniqueness: append id if necessary
                $candidate = $local ?: ('user'.$user->id);
                $exists = DB::table('users')->where('username', $candidate)->exists();
                if ($exists) {
                    $candidate = $candidate.'_'.$user->id;
                }
                DB::table('users')->where('id', $user->id)->update(['username' => $candidate]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
