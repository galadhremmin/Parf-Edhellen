<?php

use App\Models\LexicalEntryGroup;
use App\Models\Speech;
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
        $verb = Speech::where('is_verb', 1)->first();
        if (! $verb) {
            return;
        }

        LexicalEntryGroup::where('name', 'Quettaparma Quenyallo')->first()
            ->lexical_entries()
            ->whereHas('word', function($query) {
                $query->where('word', 'like', '%-');
            })
            ->where('comments', 'like', '%_vb._%')
            ->update(['speech_id' => $verb->id]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $verb = Speech::where('is_verb', 1)->first();
        if (! $verb) {
            return;
        }

        LexicalEntryGroup::where('name', 'Quettaparma Quenyallo')->first()
            ->lexical_entries()
            ->whereHas('word', function($query) {
                $query->where('word', 'like', '%-');
            })
            ->where('comments', 'like', '%_vb._%')
            ->update(['speech_id' => null]);
    }
};
