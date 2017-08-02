<?php

namespace App\Http\Controllers;

use App\Models\{Sentence, Translation};
use App\Helpers\{LinkHelper, StringHelper};

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function __construct(LinkHelper $linkHelper)
    {
        $this->_linkHelper = $linkHelper;
    }

    public function index(Request $request)
    {
        if (! $request->has('key') || $request->input('key') !== config('ed.sitemap-key')) {
            return response(null, 401);
        }

        $domain = config('app.url');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'.
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'.
            '<url>'.
            '<loc>'.$domain.'</loc>'.
            '<changefreq>weekly</changefreq>'.
            '</url>';

        $translations = Translation::join('words', 'words.id', 'translations.word_id')
            ->select('words.normalized_word', 'translations.updated_at', 'translations.created_at')
            ->distinct()
            ->get();

        $sentences = Sentence::with('language')->get();

        foreach ($translations as $translation) {
            $xml .= '<url>'.
            '<loc>'.$domain.'/w/'.$translation->normalized_word.'</loc>'.
            '<lastmod>'.($translation->updated_at ?: $translation->created_at).'</lastmod>'.
            '<changefreq>monthly</changefreq>'.
            '</url>';
        }

        foreach ($sentences as $sentence) {
            $xml .= '<url>'.
            '<loc>'.$this->_linkHelper->sentence($sentence->language_id, $sentence->language->name, $sentence->id, $sentence->name).'</loc>'.
            '<lastmod>'.($sentence->updated_at ?: $sentence->created_at).'</lastmod>'.
            '<changefreq>monthly</changefreq>'.
            '</url>';
        }

        $xml .= '</urlset>';

        return response($xml)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }
}
