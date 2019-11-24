<?php

use App\Models\Language;
use Illuminate\Database\Migrations\Migration;

class GlaemscribeLanguageTengwarModes extends Migration
{
    private const TengwarModeMapping = [
        'adunaic' => 'adunaic-tengwar-glaemscrafu',
        'blackspeech' => 'blackspeech-tengwar-general_use',
        'quenya' => 'quenya-tengwar-classical',
        'sindarin' => 'sindarin-tengwar-general_use',
        'sindarin-beleriand' => 'sindarin-tengwar-beleriand',
        'telerin' => 'telerin-tengwar-glaemscrafu',
        'westron' => 'westron-tengwar-glaemscrafu',
        'valarin-sarati' => 'valarin-sarati',
        'khuzdul-cirth' => 'khuzdul-cirth-moria',
        'quenya-sarati' => 'quenya-sarati'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::TengwarModeMapping as $from => $to) {
            Language::where('tengwar_mode', $from)
                ->update(['tengwar_mode' => $to]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (self::TengwarModeMapping as $to => $from) {
            Language::where('tengwar_mode', $from)
                ->update(['tengwar_mode' => $to]);
        }
    }
}
