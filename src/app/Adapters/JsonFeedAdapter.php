<?php

namespace App\Adapters;

use Illuminate\Support\Collection;
use Carbon\Carbon;

class JsonFeedAdapter
{
    private const REQUIRED_PROPERTIES = ['id', 'content_text', 'url', 'date_published', 'date_modified'];
    private $_metadata;

    public function __construct(string $title, string $homePageUrl, string $feedUrl)
    {
        $this->_metadata = [
            'title' => $title,
            'home_page_url' => $homePageUrl,
            'feed_url' => $feedUrl
        ];
    }

    public function adapt(Collection $data, callable $itemFormatter)
    {
        $payload = array_merge($this->_metadata, [
            '_build_date' => [
                't' => Carbon::now()->toRfc3339String()
            ],
            'version' => 'https://jsonfeed.org/version/1',
            'items'   => $data->map(function ($d) use ($itemFormatter) {
                $fd = $itemFormatter($d);
                $this->validateFormattedItem($fd);
                return $fd;
            })
        ]);

        return json_encode($payload);
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
