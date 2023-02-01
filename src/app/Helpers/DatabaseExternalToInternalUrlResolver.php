<?php

namespace App\Helpers;

use App\Helpers\StringHelper;
use App\Interfaces\IExternalToInternalUrlResolver;
use App\Models\GlossGroup;
use Illuminate\Support\Facades\Cache;

class DatabaseExternalToInternalUrlResolver implements IExternalToInternalUrlResolver
{
    private $_sources;

    public function __construct()
    {
        $this->_sources = Cache::remember('ed.DatabaseExternalToInternalUrlResolver.sources', 60 * 60 /* seconds */, function () {
            $externalLinks = GlossGroup::whereNotNull('external_link_format')
                ->orderBy('id')
                ->get();
            $sources = [];

            foreach ($externalLinks as $group) {
                $url = $group->external_link_format;
                $host = self::extractHost($url);
                $path = parse_url($url, PHP_URL_PATH);

                if ($host === false || $path === false) {
                    continue;
                }

                if (! array_key_exists($host, $sources)) {
                    $sources[$host] = [
                        'regex'      => '/'.str_replace('\{ExternalID\}', '([0-9]+)', preg_quote($path, '/')).'/',
                        'group_id'   => $group->id,
                        'group_name' => StringHelper::normalizeForUrl($group->name)
                    ];
                }
            }

            return $sources;
        });
    }

    public function getInternalUrl(string $url): ?string
    {
        $host = self::extractHost($url);
        if (! $this->isHostQualified($host)) {
            return null;
        }

        $regex = $this->getRegularExpressionForHostUnsafe($host);
        $path = parse_url($url, PHP_URL_PATH);
        $matches = null;
        if (! preg_match($regex, $path, $matches)) {
            return null;
        }

        $groupId = $this->getGroupIdForHostUnsafe($host);
        $groupName = $this->getGroupNameForHostUnsafe($host);
        return sprintf('/wg/%d-%s/%s', $groupId, $groupName, $matches[1]);
    }

    public function isHostQualified(string $host): bool
    {
        return array_key_exists($host, $this->_sources);
    }

    private static function extractHost(string $url)
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST));
        return $host;
    }

    private function getRegularExpressionForHostUnsafe(string $host)
    {
        return $this->_sources[$host]['regex'];
    }

    private function getGroupIdForHostUnsafe(string $host)
    {
        return $this->_sources[$host]['group_id'];
    }

    private function getGroupNameForHostUnsafe(string $host)
    {
        return $this->_sources[$host]['group_name'];
    }
}
