<?php

namespace App\Adapters;

use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class RssFeedAdapter
{
    private const REQUIRED_PROPERTIES = ['title', 'description', 'link', 'pubDate'];
    private $_metadata;
    private $_feedUrl;

    public function __construct(string $title, string $homePageUrl, string $feedUrl, string $description)
    {
        $this->_metadata = [
            'title' => $title,
            'link' => $homePageUrl,
            'description' => $description,
            'language' => 'en-us'
        ];
        $this->_feedUrl = $feedUrl;
    }

    public function adapt(Collection $data, callable $itemFormatter)
    {
        $payload = [
            '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">',
                '<channel>',
                    '<atom:link href="'.$this->_feedUrl.'" rel="self" type="application/rss+xml" />',
                    '<lastBuildDate>'.Carbon::now()->toRfc1123String().'</lastBuildDate>'
        ];

        foreach ($this->_metadata as $tagName => $value) {
            $payload[] = '<'.$tagName.'>'.htmlentities($value, ENT_QUOTES).'</'.$tagName.'>';
        }

        foreach ($data as $d) {
            $fd = $itemFormatter($d);
            $this->validateFormattedItem($fd);

            $payload[] = '<item>';

            foreach ($fd as $tagName => $value) {
                $payload[] = '<'.$tagName.'><![CDATA['.htmlentities($value, ENT_QUOTES).']]></'.$tagName.'>';
            }

            $payload[] = '</item>';
        }

        $payload[] = '</channel>'.
            '</rss>';

        return implode("\n", $payload);
    }

    private function validateFormattedItem($fd)
    {
        if (! is_array($fd)) {
            throw new \Exception('Formatted item must be an array.');
        }

        foreach (self::REQUIRED_PROPERTIES as $property)
        {
            if (! isset($fd[$property])) {
                throw new \Exception(sprintf('Required property "%s" is missing.', $property));
            }
        }
    }
}
