<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\{
    GameWordFinderLanguage,
    Language
};

class WordfinderAdditions extends Migration
{
    private $_lAd;
    private $_lPe;

    public function __construct() 
    {
        $this->_lAd = Language::where(['name' => 'Adûnaic'])->first();
        $this->_lPe = Language::where(['name' => 'Primitive elvish'])->first();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($this->_lAd !== null && $this->_lPe !== null) {
            try {
                DB::beginTransaction();
                GameWordFinderLanguage::create([
                    'language_id' => $this->_lAd->id,
                    'title'       => $this->_lAd->name,
                    'description' => 'Adûnaic was the official language of the Númenoreans, the kingly denizens of Númenor.'
                ]);
                GameWordFinderLanguage::create([
                    'language_id' => $this->_lPe->id,
                    'title'       => $this->_lPe->name,
                    'description' => 'Primitive Elvish is the origin of all Elvish languages. It is believed to have been formed during the Elves\' stay by Cuiviénen, the Water of Awakening.'
                ]);
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollBack();
                throw $ex;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        GameWordFinderLanguage::whereIn('language_id', [
            $this->_lAd->id, $this->_lPe->id
        ])->delete();
    }
}
