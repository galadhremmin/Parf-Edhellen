<?php

namespace Tests\Unit\Aws;

use App\Aws\ComprehendFacade;
use App\Aws\ComprehendFactory;
use Aws\Comprehend\ComprehendClient;
use Tests\TestCase;

class ComprehendFacadeTest extends TestCase
{
    public function test_unique_keywords_only()
    {
        $clientMock = $this->getMockBuilder(ComprehendClient::class) //
            ->disableOriginalConstructor() //
            ->onlyMethods(['__call']) //
            ->getMock();

        $factoryMock = $this->createMock(ComprehendFactory::class);

        $response = [
            'KeyPhrases' => [
                [
                    'Text' => 'A',
                ],
                [
                    'Text' => 'A',
                ],
                [
                    'Text' => 'B',
                ],
                [
                    'Text' => 'C',
                ],
            ],
        ];

        $clientMock->expects($this->once())
            ->method('__call')
            ->with('detectKeyPhrases')
            ->willReturn($response);
        $factoryMock->method('create')->willReturn($clientMock);

        $facade = new ComprehendFacade($factoryMock);
        $phrases = $facade->detectKeyPhrases('1234');

        $this->assertTrue(is_array($phrases));
        $this->assertEquals($phrases, ['A', 'B', 'C']);
    }
}
