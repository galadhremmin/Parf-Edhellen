<?php

namespace Tests\Unit\Aws;

use Aws\Comprehend\ComprehendClient;

use Tests\TestCase;
use App\Aws\{
    ComprehendFacade,
    ComprehendFactory
};

class ComprehendFacadeTest extends TestCase
{
    public function testUniqueKeywordsOnly()
    {
        $clientMock = $this->getMockBuilder(ComprehendClient::class) //
            ->disableOriginalConstructor() //
            ->onlyMethods(['detectKeyPhrases']) //
            ->getMock();

        $factoryMock = $this->createMock(ComprehendFactory::class);

        $response = [
            'KeyPhrases' => [
                [
                    'Text' => 'A'
                ],
                [
                    'Text' => 'A'
                ],
                [
                    'Text' => 'B'
                ],
                [
                    'Text' => 'C'
                ]
            ]
        ];

        $clientMock->method('detectKeyPhrases')->willReturn($response);
        $factoryMock->method('create')->willReturn($clientMock);

        $facade = new ComprehendFacade($factoryMock);
        $phrases = $facade->detectKeyPhrases('1234');

        $this->assertTrue(is_array($phrases));
        $this->assertEquals($phrases, ['A', 'B', 'C']);
    }
}
