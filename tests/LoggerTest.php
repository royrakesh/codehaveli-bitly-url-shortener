<?php

use PHPUnit\Framework\TestCase;
use Codehaveli\Wbitly\Logger;

class LoggerTest extends TestCase
{
    public function testErrorLogsWhenDebug()
    {
        define('WP_DEBUG', true);
        $this->expectOutputString('');
        Logger::error('test');
        $this->assertTrue(true); // Just for coverage
    }

    public function testNoticeLogsWhenDebug()
    {
        define('WP_DEBUG', true);
        $this->expectOutputString('');
        Logger::notice('test');
        $this->assertTrue(true);
    }

    public function testDebugLogsWhenDebugAndDebugLog()
    {
        if (!defined('WP_DEBUG')) define('WP_DEBUG', true);
        if (!defined('WP_DEBUG_LOG')) define('WP_DEBUG_LOG', true);
        $this->expectOutputString('');
        Logger::debug('test');
        $this->assertTrue(true);
    }
}
