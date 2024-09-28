<?php

use App\Models\{
    Account,
    GlossGroup
};
use App\Models\Versioning\GlossVersion;
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
        $eldamoAccount = Account::where('nickname', 'Eldamo Import')
            ->select('id')
            ->first();
        
        $eldamoGroups = ['Eldamo', 'Eldamo - neologism/reconstructions', 'Eldamo - neologism/adaptations'];
        foreach ($eldamoGroups as $groupName) {
            $eldamo = GlossGroup::where('name', $groupName)
                ->select('id')
                ->first();

            GlossVersion::where('gloss_group_id', $eldamo->id)
                ->where('account_id', '<>', $eldamoAccount->id)
                ->update(['account_id' => $eldamoAccount->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
