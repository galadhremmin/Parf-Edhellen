<?php

namespace App\Http\Controllers;

use App\Models\{Sentence, Gloss};
use App\Helpers\{LinkHelper, StringHelper};

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    private $_linkHelper;
    private $_domain;

    public function __construct(LinkHelper $linkHelper)
    {
        $this->_linkHelper = $linkHelper;
        $this->_domain = config('app.url');
    }

    public function index(Request $request, string $context)
    {
        if (! $request->has('key') || $request->input('key') !== config('ed.sitemap_key')) {
            return response(null, 401);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";

        switch ($context) {
            case 'pages':
                $this->addPages($xml);
                break;
            case 'translations': 
                {
                    $this->validate($request, [
                        'from'    => 'required|numeric|min:0',
                        'to'      => 'required|numeric|min:0'
                    ]);

                    $from    = $request->input('from');
                    $to      = $request->input('to');

                    $this->addGlosses($xml, $from, $to);
                }
                break;
            case 'sentences':
                $this->addSentences($xml);
                break;
        }

        $xml .= '</urlset>'."\n";

        return response($xml)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }

    private function addPages(string& $xml)
    {
        $routeNames = ['home', 'about', 'about.donations'];

        foreach ($routeNames as $routeName) {
            $xml .= '<url>'.
                '<loc>'.route($routeName).'</loc>'.
                '<changefreq>weekly</changefreq>'.
                '</url>';
        }
    }

    private function addGlosses(string& $xml, int $from, int $to) 
    {
        if ($from > $to || $to - $from > 50000) {
            return;
        }

        $glosses = Gloss::active()
            ->join('words', 'words.id', 'glosses.word_id')
            ->select('words.normalized_word', 'glosses.updated_at', 'glosses.created_at')
            ->distinct()
            ->skip($from)
            ->take($to - $from)
            ->get();

        foreach ($glosses as $gloss) {
            $this->addNode($xml,
                $this->_domain.'/w/'.urlencode($gloss->normalized_word),
                'monthly',
                $gloss->updated_at ?: $gloss->created_at
            );
        }
    }

    private function addSentences(string& $xml)
    {
        $this->addNode($xml, route('sentence.public'));

        $sentences = Sentence::with('language')
            ->orderBy('language_id')
            ->get();

        $languages = [];

        foreach ($sentences as $sentence) {
            if (! array_key_exists($sentence->language_id, $languages)) {
                $languages[$sentence->language_id] = true;

                $this->addNode($xml,
                    $this->_linkHelper->sentencesByLanguage($sentence->language_id, $sentence->language->name)
                );
            }

            $this->addNode($xml, 
                $this->_linkHelper->sentence($sentence->language_id, $sentence->language->name, $sentence->id, $sentence->name),
                'monthly',
                $sentence->updated_at ?: $sentence->created_at
            );
        }
    }

    private function addNode(string& $xml, string $location, string $changeFrequency = 'monthly', \Carbon\Carbon $lastModified = null) {
        $xml .= '<url>'.
            '<loc>'.$location.'</loc>'.
            ($lastModified !== null ? '<lastmod>'.$lastModified->format('Y-m-d').'</lastmod>' : '').
            '<changefreq>'.$changeFrequency.'</changefreq>'.
            '</url>';
    }
}
