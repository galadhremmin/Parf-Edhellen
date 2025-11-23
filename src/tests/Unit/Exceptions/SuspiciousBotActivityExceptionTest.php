<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\SuspiciousBotActivityException;
use Exception;
use Illuminate\Http\Request;
use Tests\TestCase;

class SuspiciousBotActivityExceptionTest extends TestCase
{
    public function test_initializes_with_request_and_component()
    {
        $request = Request::create('https://example.com/test', 'POST');
        $component = 'user login';

        $exception = new SuspiciousBotActivityException($request, $component);

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertStringContainsString('Suspicious bot activity from', $exception->getMessage());
        $this->assertStringContainsString($request->ip(), $exception->getMessage());
        $this->assertStringContainsString('affecting component '.$component, $exception->getMessage());
    }

    public function test_initializes_with_null_assessment_result()
    {
        $request = Request::create('https://example.com/test', 'POST');
        $component = 'user registration';

        $exception = new SuspiciousBotActivityException($request, $component, null);

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertStringContainsString('Suspicious bot activity from', $exception->getMessage());
        $this->assertStringContainsString($request->ip(), $exception->getMessage());
        $this->assertStringContainsString('affecting component '.$component, $exception->getMessage());
        $this->assertStringNotContainsString('with assessment result:', $exception->getMessage());
    }

    public function test_initializes_with_empty_assessment_result()
    {
        $request = Request::create('https://example.com/test', 'POST');
        $component = 'user registration';
        $assessmentResult = [];

        $exception = new SuspiciousBotActivityException($request, $component, $assessmentResult);

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertStringContainsString('Suspicious bot activity from', $exception->getMessage());
        $this->assertStringContainsString($request->ip(), $exception->getMessage());
        $this->assertStringContainsString('affecting component '.$component, $exception->getMessage());
        $this->assertStringNotContainsString('with assessment result:', $exception->getMessage());
    }

    public function test_initializes_with_assessment_result()
    {
        $request = Request::create('https://example.com/test', 'POST');
        $component = 'user login';
        $assessmentResult = [
            'riskAnalysis' => [
                'score' => 0.3,
                'reasons' => ['AUTOMATION'],
            ],
            'tokenProperties' => [
                'valid' => false,
                'action' => 'LOGIN',
            ],
        ];

        $exception = new SuspiciousBotActivityException($request, $component, $assessmentResult);

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertStringContainsString('Suspicious bot activity from', $exception->getMessage());
        $this->assertStringContainsString($request->ip(), $exception->getMessage());
        $this->assertStringContainsString('affecting component '.$component, $exception->getMessage());
        $this->assertStringContainsString('with assessment result:', $exception->getMessage());
        $this->assertStringContainsString('riskAnalysis', $exception->getMessage());
        $this->assertStringContainsString('tokenProperties', $exception->getMessage());
    }

    public function test_message_format_with_assessment_result()
    {
        $request = Request::create('https://example.com/test', 'POST');
        $component = 'test component';
        $assessmentResult = [
            'score' => 0.5,
            'valid' => true,
        ];

        $exception = new SuspiciousBotActivityException($request, $component, $assessmentResult);

        $message = $exception->getMessage();
        
        // Verify the message structure
        $this->assertStringStartsWith('Suspicious bot activity from', $message);
        $this->assertStringContainsString($request->ip(), $message);
        $this->assertStringContainsString('affecting component '.$component, $message);
        $this->assertStringContainsString('with assessment result:', $message);
        
        // Verify JSON is properly formatted (should contain the keys from assessment result)
        $this->assertStringContainsString('"score"', $message);
        $this->assertStringContainsString('"valid"', $message);
    }

    public function test_extends_exception()
    {
        $request = Request::create('https://example.com/test', 'POST');
        $exception = new SuspiciousBotActivityException($request, 'test');

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
    }
}

