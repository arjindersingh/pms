<?php

namespace App\Services;

class UserAgentParser
{
    /** @return array{browser:string,browser_version:?string,operating_system:string,device_type:string,device_name:?string} */
    public function parse(?string $agent): array
    {
        $agent ??= '';
        [$browser, $version] = $this->browser($agent);
        [$deviceType, $deviceName] = $this->device($agent);

        return [
            'browser' => $browser,
            'browser_version' => $version,
            'operating_system' => $this->operatingSystem($agent),
            'device_type' => $deviceType,
            'device_name' => $deviceName,
        ];
    }

    /** @return array{string, ?string} */
    private function browser(string $agent): array
    {
        foreach ([
            'Edge' => '/Edg(?:A|iOS)?\/([\d.]+)/', 'Opera' => '/(?:OPR|Opera)\/([\d.]+)/',
            'Samsung Internet' => '/SamsungBrowser\/([\d.]+)/', 'Firefox' => '/(?:Firefox|FxiOS)\/([\d.]+)/',
            'Chrome' => '/(?:Chrome|CriOS)\/([\d.]+)/', 'Safari' => '/Version\/([\d.]+).*Safari\//',
        ] as $name => $pattern) {
            if (preg_match($pattern, $agent, $match)) return [$name, $match[1]];
        }

        return [str_contains($agent, 'bot') || str_contains(strtolower($agent), 'crawler') ? 'Bot' : 'Unknown', null];
    }

    private function operatingSystem(string $agent): string
    {
        foreach ([
            'Windows 11/10' => '/Windows NT 10\.0/', 'Windows 8.1' => '/Windows NT 6\.3/',
            'Windows 7' => '/Windows NT 6\.1/', 'Android' => '/Android/',
            'iOS' => '/(?:iPhone|iPad).*OS [\d_]+/', 'macOS' => '/Mac OS X/',
            'ChromeOS' => '/CrOS/', 'Linux' => '/Linux/',
        ] as $name => $pattern) {
            if (preg_match($pattern, $agent)) return $name;
        }

        return 'Unknown';
    }

    /** @return array{string, ?string} */
    private function device(string $agent): array
    {
        if (preg_match('/iPad/', $agent)) return ['tablet', 'iPad'];
        if (preg_match('/iPhone/', $agent)) return ['mobile', 'iPhone'];
        if (preg_match('/Android.*Mobile/i', $agent)) return ['mobile', 'Android phone'];
        if (preg_match('/Android/i', $agent)) return ['tablet', 'Android tablet'];
        if (preg_match('/bot|crawler|spider/i', $agent)) return ['bot', 'Automated client'];

        return ['desktop', null];
    }
}
