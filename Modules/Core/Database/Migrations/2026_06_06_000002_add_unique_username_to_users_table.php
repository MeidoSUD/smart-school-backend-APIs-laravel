<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users ADD UNIQUE INDEX users_username_unique (username)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP INDEX users_username_unique');
    }
};
