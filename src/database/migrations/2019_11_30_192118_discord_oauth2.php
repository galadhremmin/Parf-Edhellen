<?php

use App\Models\AuthorizationProvider;

use Illuminate\Database\Migrations\Migration;

class DiscordOauth2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $auth = new AuthorizationProvider;
        $auth->name = 'Discord';
        $auth->logo_file_name = 'discord.png';
        $auth->name_identifier = 'discord';
        $auth->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        AuthorizationProvider::where('name_identifier', 'discord')
            ->delete();
    }
}
