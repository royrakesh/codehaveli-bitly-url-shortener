<?php
namespace Codehaveli\Wbitly;

use PHPUnit\Framework\TestCase;
use Codehaveli\Wbitly\BitlyAPI;

class BitlyAPITest extends TestCase
{
    protected function setUp(): void
    {
        \Brain\Monkey\setUp();
    }

    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
    }

    public function testShortenUrlReturnsFalseIfInvalid()
    {
        $api = new BitlyAPI();
        $this->assertFalse($api->shorten_url('not-a-url'));
    }

    public function testShortenUrlReturnsShortUrl()
    {
        \Brain\Monkey\Functions\when('apply_filters')->justReturn('https://example.com');
        \Brain\Monkey\Functions\when('esc_url_raw')->justReturn('https://example.com');
        \Brain\Monkey\Functions\when('filter_var')->justReturn(true);
        \Brain\Monkey\Functions\when('sanitize_text_field')->justReturn('tok');
        \Brain\Monkey\Functions\when('get_option')->justReturn(['access_token' => 'tok', 'group_guid' => 'guid', 'bitly_domain' => 'bit.ly']);
        \Brain\Monkey\Functions\when('wp_json_encode')->justReturn('{}');
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn(['body' => '{"link":"https://bit.ly/abc"}']);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn('{"link":"https://bit.ly/abc"}');
        $api = $this->getMockBuilder(BitlyAPI::class)->onlyMethods(['send_request'])->getMock();
        $api->method('send_request')->willReturn(['link' => 'https://bit.ly/abc']);
        $this->assertEquals('https://bit.ly/abc', $api->shorten_url('https://example.com'));
    }

    public function testGetGroupGuidReturnsGuid()
    {
        \Brain\Monkey\Functions\when('sanitize_text_field')->justReturn('tok');
        \Brain\Monkey\Functions\when('get_option')->justReturn(['access_token' => 'tok']);
        $api = $this->getMockBuilder(BitlyAPI::class)->onlyMethods(['send_request'])->getMock();
        $api->method('send_request')->willReturn(['groups' => [['guid' => 'guid']]]);
        $this->assertEquals('guid', $api->get_group_guid());
    }

    public function testGetRateLimitReturnsLimits()
    {
        \Brain\Monkey\Functions\when('sanitize_text_field')->justReturn('tok');
        \Brain\Monkey\Functions\when('get_option')->justReturn(['access_token' => 'tok']);
        $api = $this->getMockBuilder(BitlyAPI::class)->onlyMethods(['send_request'])->getMock();
        $api->method('send_request')->willReturn(['platform_limits' => ['foo' => 'bar']]);
        $this->assertEquals(['foo' => 'bar'], $api->get_rate_limit());
    }
}
