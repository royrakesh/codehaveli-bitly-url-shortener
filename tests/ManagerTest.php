<?php

use PHPUnit\Framework\TestCase;
use Codehaveli\Wbitly\Manager;

class ManagerTest extends TestCase
{
    protected function setUp(): void
    {
        \Brain\Monkey\setUp();
    }

    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
    }

    public function testGetShortUrlReturnsNullIfNoPostId()
    {
        $this->assertNull(Manager::get_short_url(0));
    }

    public function testGetShortUrlReturnsNullIfMetaMissing()
    {
        \Brain\Monkey\Functions\when('get_post_meta')->justReturn('');
        $this->assertNull(Manager::get_short_url(123));
    }

    public function testGetShortUrlReturnsUrlIfValid()
    {
        \Brain\Monkey\Functions\when('get_post_meta')->justReturn('https://bit.ly/abc');
        $this->assertEquals('https://bit.ly/abc', Manager::get_short_url(123));
    }

    public function testUpdateShortUrlReturnsFalseIfNoPostId()
    {
        $this->assertFalse(Manager::update_short_url(0, 'https://bit.ly/abc'));
    }

    public function testUpdateShortUrlReturnsTrueIfValid()
    {
        \Brain\Monkey\Functions\when('update_post_meta')->justReturn(true);
        $this->assertTrue(Manager::update_short_url(123, 'https://bit.ly/abc'));
    }

    public function testDeleteShortUrlReturnsFalseIfNoPostId()
    {
        $this->assertFalse(Manager::delete_short_url(0));
    }

    public function testDeleteShortUrlReturnsTrueIfValid()
    {
        \Brain\Monkey\Functions\when('delete_post_meta')->justReturn(true);
        $this->assertTrue(Manager::delete_short_url(123));
    }
}
