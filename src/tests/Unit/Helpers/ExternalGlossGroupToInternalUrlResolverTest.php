<?php

namespace Tests\Unit;

use App\Helpers\ExternalGlossGroupToInternalUrlResolver;
use App\Models\LexicalEntryGroup;
use Tests\TestCase;

class ExternalGlossGroupToInternalUrlResolverTest extends TestCase
{
    /**
     * @var ExternalGlossGroupToInternalUrlResolver
     */
    private $_resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $groups = collect([
            LexicalEntryGroup::firstOrNew([
                'name' => 'Group 1',
                'external_link_format' => 'https://www.test1.com/page/{ExternalID}',
            ]),
            LexicalEntryGroup::firstOrNew([
                'name' => 'Group 2',
                'external_link_format' => 'https://www.test2.com/pages/{ExternalID}',
            ]),
        ]);

        $groups->each(function ($group, $i) {
            $group->id = $i + 1;
        });

        $this->_resolver = new ExternalGlossGroupToInternalUrlResolver($groups);
    }

    public function test_has_correct_sources()
    {
        $this->assertEquals(
            $this->_resolver->getSources(),
            [
                'www.test1.com' => [
                    'regex' => '/\/page\/([0-9]+)/',
                    'group_id' => 1,
                    'group_name' => 'group_1',
                ],
                'www.test2.com' => [
                    'regex' => '/\/pages\/([0-9]+)/',
                    'group_id' => 2,
                    'group_name' => 'group_2',
                ],
            ]
        );
    }

    public function test_does_not_match_link()
    {
        $url = 'https://www.doesnotexist.com';
        $host = parse_url($url)['host'];

        $this->assertFalse(
            $this->_resolver->isHostQualified($host)
        );

        $this->assertNull(
            $this->_resolver->getInternalUrl($host)
        );
    }

    public function test_matches()
    {
        $id = 13256124;
        $url = 'https://www.test1.com/page/'.$id.'/something-else?something=else';
        $host = parse_url($url)['host'];

        $this->assertTrue(
            $this->_resolver->isHostQualified($host)
        );

        $this->assertEquals(
            '/wg/1-group_1/'.$id,
            $this->_resolver->getInternalUrl($url)
        );
    }
}
