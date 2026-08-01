<?php

namespace App\Services;

class SystemCompatibility
{
    public function __construct(private readonly UserAgentParser $agents) {}

    /** @return array{supported:bool,issues:array<int,string>,browser:string,browser_version:?string,operating_system:string} */
    public function assess(?string $userAgent): array
    {
        $userAgent ??= '';
        $details = $this->agents->parse($userAgent);
        $issues = [];
        $minimumBrowser = config("system-requirements.browsers.{$details['browser']}");

        if ($minimumBrowser !== null && $details['browser_version'] !== null
            && version_compare($details['browser_version'], (string) $minimumBrowser, '<')) {
            $issues[] = "{$details['browser']} {$minimumBrowser} or newer is required; you are using {$details['browser_version']}.";
        } elseif ($details['browser'] === 'Unknown' && $userAgent !== '') {
            $issues[] = 'This browser could not be identified. Use a current version of Chrome, Edge, Firefox, or Safari.';
        }

        [$operatingSystem, $operatingSystemVersion] = $this->operatingSystem($userAgent, $details['operating_system']);
        $minimumOperatingSystem = config("system-requirements.operating_systems.{$operatingSystem}");
        if ($minimumOperatingSystem !== null && $operatingSystemVersion !== null
            && version_compare($operatingSystemVersion, (string) $minimumOperatingSystem, '<')) {
            $issues[] = "{$operatingSystem} {$minimumOperatingSystem} or newer is required; you are using {$operatingSystemVersion}.";
        }

        return [
            'supported' => $issues === [],
            'issues' => $issues,
            'browser' => $details['browser'],
            'browser_version' => $details['browser_version'],
            'operating_system' => trim($operatingSystem.' '.($operatingSystemVersion ?? '')),
        ];
    }

    /** @return array{string, ?string} */
    private function operatingSystem(string $agent, string $fallback): array
    {
        if (preg_match('/Windows NT ([\d.]+)/', $agent, $match)) {
            return ['Windows', match ($match[1]) {
                '10.0' => '10', '6.3' => '8.1', '6.2' => '8', '6.1' => '7', default => $match[1],
            }];
        }
        if (preg_match('/Android ([\d.]+)/', $agent, $match)) return ['Android', $match[1]];
        if (preg_match('/(?:iPhone|iPad).*OS (\d+(?:_\d+)*)/', $agent, $match)) return ['iOS', str_replace('_', '.', $match[1])];
        if (preg_match('/Mac OS X (\d+(?:[_\.]\d+)*)/', $agent, $match)) return ['macOS', str_replace('_', '.', $match[1])];

        return [$fallback, null];
    }
}
