<?php
namespace Sahdev\SSO\Services;

use InvalidArgumentException;
use Sahdev\SSO\Providers\GitHubProvider;
use Sahdev\SSO\Providers\GoogleProvider;
use Sahdev\SSO\Providers\TwitterProvider;

class SSOManager
{
    public function driver(string $provider)
    {
        return match (strtolower($provider)) {
            'google' => new GoogleProvider(),
            'github' => new GitHubProvider(),
            'twitter' => new TwitterProvider(),
            default => throw new InvalidArgumentException("SSO provider [$provider] is not supported."),
        };
    }
}
