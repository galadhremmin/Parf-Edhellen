<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('keywords', function () 
{    
    $affected = DB::table('keywords as k')
        ->leftJoin('translation as t', 'k.TranslationID', '=', 't.TranslationID')
        ->leftJoin('word as w', 'k.WordID', '=', 'w.KeyID')
        ->whereNotNull('k.TranslationID')
        ->where(function ($query) {
            $query->whereNull('t.TranslationID') // translation doesn't exist
                ->orWhereNull('w.KeyID') // word doesn't exist
                ->orWhere('t.Deleted', '=', DB::raw(1))
                ->orWhere('t.Latest', '=', DB::raw(0))
                ->orWhere('t.WordID', '<>', DB::raw('k.WordID'));
        })
        ->delete();

    $this->info('Deleted '.$affected.' deprecated translation keywords.');
    
    $affected = DB::table('keywords as k')
        ->leftJoin('namespace as n', 'k.NamespaceID', '=', 'n.NamespaceID')
        ->leftJoin('word as w', 'k.WordID', '=', 'w.KeyID')
        ->whereNotNull('k.NamespaceID')
        ->where(function ($query) {
            $query->whereNull('w.KeyID') // word doesn't exist
                ->orWhere('n.IdentifierID', '<>', DB::raw('k.WordID'));
        })
        ->delete();

    $this->info('Deleted '.$affected.' deprecated sense keywords.');

    $missingKeywords = DB::table('translation as t')
        ->join('word as w', 't.WordID', '=', 'w.KeyID')
        ->where([
            ['t.Latest', '=', DB::raw(1)],
            ['t.Deleted', '=', DB::raw(0)]
        ])
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('keywords as k')
                ->where([
                    ['k.TranslationID', '=', DB::raw('t.TranslationID')],
                    ['k.WordID', '=', DB::raw('t.WordID')]
                ]);
        })
        ->select('t.TranslationID', 'w.KeyID', 'w.Key', 'w.NormalizedKey', 'w.ReversedNormalizedKey')
        ->get();

    $addedCount = 1;
    foreach ($missingKeywords as $keyword) {
        DB::table('keywords')->insert([
            'Keyword'                   => $keyword->Key,
            'NormalizedKeyword'         => $keyword->NormalizedKey,
            'ReversedNormalizedKeyword' => $keyword->ReversedNormalizedKey,
            'TranslationID'             => $keyword->TranslationID,
            'WordID'                    => $keyword->KeyID
        ]);

        $this->info($addedCount.': "'.$keyword->Key.'" for translation '.$keyword->TranslationID);
        $addedCount += 1;
    }

    $missingKeywords = DB::table('namespace as n')
        ->join('word as w', 'n.IdentifierID', '=', 'w.KeyID')
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('keywords as k')
                ->where('n.NamespaceID', '=', DB::raw('k.NamespaceID'));
        })
        ->select('n.NamespaceID', 'w.KeyID', 'w.Key', 'w.NormalizedKey', 'w.ReversedNormalizedKey')
        ->get();
        
    foreach ($missingKeywords as $keyword) {
        DB::table('keywords')->insert([
            'Keyword'                   => $keyword->Key,
            'NormalizedKeyword'         => $keyword->NormalizedKey,
            'ReversedNormalizedKeyword' => $keyword->ReversedNormalizedKey,
            'NamespaceID'               => $keyword->NamespaceID,
            'WordID'                    => $keyword->KeyID
        ]);

        $this->info($addedCount.': "'.$keyword->Key.'" for namespace '.$keyword->NamespaceID);
        $addedCount += 1;
    }

})->describe('Updates keywords');
