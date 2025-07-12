<?php

use PHPUnit\Framework\TestCase;
use Codehaveli\Wbitly\OptionManager;

class OptionManagerTest extends TestCase
{
    protected function setUp(): void
    {
        \Brain\Monkey\setUp();
    }

    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
    }

    public function testGetReturnsDefaultIfNotSet()
    {
        \Brain\Monkey\Functions\when('get_option')->justReturn([]);
        $this->assertEquals('default', OptionManager::get('not_set', 'default'));
    }

    public function testGetReturnsValueIfSet()
    {
        \Brain\Monkey\Functions\when('get_option')->justReturn(['foo' => 'bar']);
        $this->assertEquals('bar', OptionManager::get('foo'));
    }

    public function testSetUpdatesOption()
    {
        \Brain\Monkey\Functions\when('get_option')->justReturn([]);
        \Brain\Monkey\Functions\expect('update_option')->once()->andReturn(true);
        OptionManager::set('foo', 'bar');
    }

    public function testAllReturnsOptions()
    {
        \Brain\Monkey\Functions\when('get_option')->justReturn(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], OptionManager::all());
    }

    public function testGetAccessTokenAndGuid()
    {
        \Brain\Monkey\Functions\when('get_option')->justReturn(['access_token' => 'tok', 'group_guid' => 'guid']);
        $this->assertEquals('tok', OptionManager::get_access_token());
        $this->assertEquals('guid', OptionManager::get_guid());
    }

    public function testMigrateOption()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default) {
            if ($key === 'wbitly_url_option_name') return ['foo' => 'bar'];
            if ($key === 'ch_wbitly_url_option') return ['baz' => 'qux'];
            return $default;
        });
        \Brain\Monkey\Functions\expect('update_option')->once();
        \Brain\Monkey\Functions\expect('delete_option')->once();
        OptionManager::migrate_option();
    }
}
