<?php

namespace App\Http\Controllers;

use App\Helpers\LinkHelper;
use App\Http\Controllers\Abstracts\Controller;
use App\Models\ForumGroup;
use App\Models\LexicalEntry;
use App\Models\Sentence;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    private LinkHelper $_linkHelper;

    private ?string $_domain;

    private ?string $_accessKey;

    public function __construct(LinkHelper $linkHelper)
    {
        $this->_linkHelper = $linkHelper;
        $this->_domain = config('app.url');
        $this->_accessKey = config('ed.sitemap_key');
    }

    public function index(Request $request, string $context)
    {
        if (! empty($this->_accessKey)) {
            if (! $request->has('key') || $request->input('key') !== $this->_accessKey) {
                return abort(401, 'Incorrect access token.');
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";

        switch ($context) {
            case 'pages':
                $this->addPages($xml);
                break;
            case 'translations':

                $this->validate($request, [
                    'from' => 'required|numeric|min:0',
                    'to' => 'required|numeric|min:0',
                ]);

                $from = intval($request->input('from'));
                $to = intval($request->input('to'));

                $this->addGlosses($xml, $from, $to);

                break;
            case 'sentences':
                $this->addSentences($xml);
                break;
            case 'discuss':
                $this->addDiscuss($xml);
                break;
            default:
                abort(400, sprintf('"%s" is an unrecognised context.', $context));
        }

        $xml .= '</urlset>'."\n";

        return response($xml)
            ->header('Content-Type', 'text/xml; charset=utf-8');
    }

    private function addPages(string &$xml)
    {
        $routeNames = ['home', 'about', 'about.cookies', 'about.privacy', 'flashcard', 'sentence.public', 'discuss.index'];

        foreach ($routeNames as $routeName) {
            $xml .= '<url>'.
                '<loc>'.route($routeName).'</loc>'.
                '<changefreq>weekly</changefreq>'.
                '</url>';
        }
    }

    private function addGlosses(string &$xml, int $from, int $to)
    {
        if ($from > $to || $to - $from > 50000) {
            return;
        }

        $glosses = LexicalEntry::active()
            ->join('words', 'words.id', 'lexical_entries.word_id')
            ->select('words.normalized_word', 'lexical_entries.updated_at', 'lexical_entries.created_at')
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

    private function addSentences(string &$xml)
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

    private function addDiscuss(string &$xml)
    {
        $this->addNode($xml, route('discuss.index'));

        $groups = ForumGroup::all();
        foreach ($groups as $group) {
            $this->addNode($xml, $this->_linkHelper->forumGroup($group->id, $group->name));
            foreach ($group->forum_threads as $thread) {
                $this->addNode($xml, $this->_linkHelper->forumThread($group->id, $group->name, $thread->id, $thread->normalized_subject));
            }
        }
    }

    private function addNode(string &$xml, string $location, string $changeFrequency = 'monthly', ?Carbon $lastModified = null)
    {
        $xml .= '<url>'.
            '<loc>'.$location.'</loc>'.
            ($lastModified !== null ? '<lastmod>'.$lastModified->format('Y-m-d').'</lastmod>' : '').
            '<changefreq>'.$changeFrequency.'</changefreq>'.
            '</url>';
    }
}
