<?php

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    protected function setUp(): void
    {
        \Brain\Monkey\setUp();
    }

    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
    }

    public function testGetWbitlyShortUrlReturnsFalseIfInvalid()
    {
        $this->assertFalse(get_wbitly_short_url(0));
    }

    public function testGetWbitlyShortUrlReturnsUrlIfValid()
    {
        \Brain\Monkey\Functions\when('Codehaveli\Wbitly\Manager::get_short_url')->justReturn('https://bit.ly/abc');
        $this->assertEquals('https://bit.ly/abc', get_wbitly_short_url(123));
    }
}
