<?php

namespace Sahdev\SSO\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TwitterProvider
{
    public function redirect()
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => config('sso.providers.twitter.client_id'),
            'redirect_uri' => config('sso.providers.twitter.redirect'),
            'scope' => 'tweet.read users.read offline.access',
            'state' => csrf_token(),
            'code_challenge' => '', // Optional if PKCE is used
            'code_challenge_method' => '' // Optional
        ]);

        return redirect("https://twitter.com/i/oauth2/authorize?$query");
    }

    public function callback(Request $request)
    {
        try {
            $code = request('code');

            if (!$code) {
                return [
                    'status' => false,
                    'message' => 'Authorization code not found.',
                    'user' => null
                ];
            }

            $tokenResponse = Http::asForm()
                ->withHeaders([
                    'Authorization' => 'Basic ' . base64_encode(
                        config('sso.providers.twitter.client_id') . ':' . config('sso.providers.twitter.client_secret')
                    ),
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])
                ->post('https://api.twitter.com/2/oauth2/token', [
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => config('sso.providers.twitter.redirect'),
                    'client_id' => config('sso.providers.twitter.client_id'),
                ]);

            if (!$tokenResponse->successful()) {
                return [
                    'status' => false,
                    'message' => 'Failed to retrieve access token.',
                    'user' => null
                ];
            }

            $accessToken = $tokenResponse->json()['access_token'];

            $userResponse = Http::withToken($accessToken)
                ->acceptJson()
                ->get('https://api.twitter.com/2/users/me');

            if (!$userResponse->successful()) {
                return [
                    'status' => false,
                    'message' => 'Failed to fetch user info.',
                    'user' => null
                ];
            }

            $user = $userResponse->json()['data'] ?? [];

            return [
                'status' => true,
                'message' => 'Twitter login successful.',
                'user' => [
                    'id' => $user['id'] ?? null,
                    'name' => $user['name'] ?? null,
                    'email' => null, // Not available unless elevated access
                    'avatar' => null, // Not available via this API
                    'raw' => $user,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'user' => null
            ];
        }
    }
}
