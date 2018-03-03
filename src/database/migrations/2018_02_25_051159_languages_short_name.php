<?php

use Illuminate\Support\Facades\{
    DB,
    Schema
};
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LanguagesShortName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->string('short_name', 4);
        });

        $mapping = [
            'Sindarin' => 's',
            'Quenya' => 'q',
            'English' => 'eng',
            'Noldorin' => 'n',
            'Telerin' => 't',
            'Quendya' => 'van',
            'Nandorin' => 'nan',
            'Undetermined' => 'unk',
            'Black Speech' => 'bs',
            'Adûnaic' => 'ad',
            'Khuzdûl' => 'kh',
            'Westron' => 'wes',
            'Primitive elvish' => 'p',
            'Ancient quenya' => 'aq',
            'Ancient telerin' => 'at',
            'Old sindarin' => 'os',
            'North sindarin' => 'ns',
            'Avarin' => 'av', 
            'Edain' => 'ed',
            'Primitive adûnaic' => 'pad',
            'Rohirric' => 'roh',
            'Wose' => 'wos',
            'Easterling' => 'eas',
            'Dunlending' => 'dun',
            'Valarin' => 'val',
            'Entish' => 'ent',
            'Qenya' => 'mq',
            'Doriathrin' => 'ilk',
            'Gnomish' => 'g',
            'Ossriandric' => 'dan',
            'Early Ilkorin' => 'eilk',
            'Early Noldorin' => 'en',
            'Old Noldorin' => 'on',
            'Middle Primitive Elvish' => 'mp',
            'Taliska' => 'tal',
            'Lemberin' => 'lem',
            'Middle Telerin' => 'mt',
            'Solosimpi' => 'et',
            'Early Primitive Elvish' => 'ep'
        ];

        if (count(array_unique(array_values($mapping))) !== count($mapping)) {
            throw new \Exception('Language mapping contains at least one duplicate key.');
        }

        foreach ($mapping as $language => $shortName) {
            DB::table('languages')->where('name', $language)
                ->update(['short_name' => $shortName]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('short_name');
        });
    }
}
