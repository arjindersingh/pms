<?php

namespace Tests\Unit;

use App\Services\SystemCompatibility;
use Tests\TestCase;

class SystemCompatibilityTest extends TestCase
{
    public function test_it_accepts_a_supported_browser_and_operating_system(): void
    {
        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124.0.0.0 Safari/537.36';

        $result = app(SystemCompatibility::class)->assess($agent);

        $this->assertTrue($result['supported']);
        $this->assertSame('Chrome', $result['browser']);
        $this->assertSame('Windows 10', $result['operating_system']);
    }

    public function test_it_reports_outdated_browser_and_operating_system_versions(): void
    {
        $agent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_7 like Mac OS X) AppleWebKit/605.1.15 Version/15.6 Mobile/15E148 Safari/604.1';

        $result = app(SystemCompatibility::class)->assess($agent);

        $this->assertFalse($result['supported']);
        $this->assertCount(2, $result['issues']);
        $this->assertStringContainsString('Safari 16.4', $result['issues'][0]);
        $this->assertStringContainsString('iOS 16', $result['issues'][1]);
    }
}
