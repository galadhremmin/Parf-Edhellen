<?php

namespace Tests\Unit;

use App\Helpers\ExternalGlossGroupToInternalUrlResolver;
use App\Models\GlossGroup;
use App\Security\AccountManager;
use Tests\TestCase;

class ExternalGlossGroupToInternalUrlResolverTest extends TestCase
{
    /**
     * @var ExternalGlossGroupToInternalUrlResolver
     */
    private $_resolver;

    public function setUp(): void
    {
        parent::setUp();

        $groups = collect([
            GlossGroup::firstOrNew([
                'name' => 'Group 1',
                'external_link_format' => 'https://www.test1.com/page/{ExternalID}'
            ]),
            GlossGroup::firstOrNew([
                'name' => 'Group 2',
                'external_link_format' => 'https://www.test2.com/pages/{ExternalID}'
            ]),
        ]);

        $groups->each(function ($group, $i) {
            $group->id = $i + 1;
        });

        $this->_resolver = new ExternalGlossGroupToInternalUrlResolver($groups);
    }

    public function testHasCorrectSources()
    {
        $this->assertEquals(
            $this->_resolver->getSources(),
            [
                'www.test1.com' => [
                    'regex' => '/\/page\/([0-9]+)/',
                    'group_id' => 1,
                    'group_name' => 'group_1'
                ],
                'www.test2.com' => [
                    'regex' => '/\/pages\/([0-9]+)/',
                    'group_id' => 2,
                    'group_name' => 'group_2'
                ],
            ]
        );
    }

    public function testDoesNotMatchLink()
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

    public function testMatches()
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
