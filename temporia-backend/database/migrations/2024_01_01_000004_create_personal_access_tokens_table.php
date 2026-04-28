<?php

// This migration is intentionally left empty.
// Laravel Sanctum publishes its own personal_access_tokens migration
// via `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
// Running our own copy causes a "table already exists" conflict.

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void {}
    public function down(): void {}
};
