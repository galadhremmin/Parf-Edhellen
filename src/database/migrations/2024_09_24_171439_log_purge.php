<?php

use App\Models\AuthorizationProvider;
use App\Models\SystemError;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $identifiers = AuthorizationProvider::select('name_identifier')->get()->map(function ($row) {
            return $row->name_identifier.'-auth-redirect';
        });

        SystemError::where('category', 'like', '%-auth-redirect')
            ->whereNotIn('category', $identifiers)
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore from backup
    }
};
