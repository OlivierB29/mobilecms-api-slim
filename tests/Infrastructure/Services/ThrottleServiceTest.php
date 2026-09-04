<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Services;

use App\Infrastructure\Services\ThrottleService;
use PHPUnit\Framework\TestCase;

final class ThrottleServiceTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/mobilecms-throttle-'.uniqid('', true);
        mkdir($this->directory.'/history', 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/history/*.json') as $file) {
            unlink($file);
        }
        rmdir($this->directory.'/history');
        rmdir($this->directory);
    }

    public function testProgressiveLockoutDurations(): void
    {
        $throttle = new ThrottleService($this->directory);
        $user = 'user@example.com';
        $ip = '203.0.113.10';

        $this->recordFailures($throttle, $user, $ip, 5);
        $this->assertGreaterThanOrEqual(55, $throttle->getRetryAfter($user, $ip));

        $this->recordFailures($throttle, $user, $ip, 5);
        $this->assertGreaterThanOrEqual(295, $throttle->getRetryAfter($user, $ip));

        $this->recordFailures($throttle, $user, $ip, 10);
        $this->assertGreaterThanOrEqual(1795, $throttle->getRetryAfter($user, $ip));
    }

    public function testThrottleIsScopedToIpAndUsername(): void
    {
        $throttle = new ThrottleService($this->directory);

        for ($index = 0; $index < 5; ++$index) {
            $throttle->recordFailedLogin('user@example.com', '203.0.113.10');
        }

        $this->assertGreaterThan(0, $throttle->getRetryAfter('user@example.com', '203.0.113.10'));
        $this->assertSame(0, $throttle->getRetryAfter('other@example.com', '203.0.113.10'));
        $this->assertSame(0, $throttle->getRetryAfter('user@example.com', '203.0.113.11'));
    }

    private function recordFailures(ThrottleService $throttle, string $user, string $ip, int $count): void
    {
        for ($index = 0; $index < $count; ++$index) {
            $throttle->recordFailedLogin($user, $ip);
        }
    }
}