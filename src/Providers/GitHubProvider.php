<?php
namespace Sahdev\SSO\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GitHubProvider
{
    public function redirect()
    {
        $query = http_build_query([
            'client_id' => config('sso.providers.github.client_id'),
            'redirect_uri' => config('sso.providers.github.redirect'),
            'scope' => 'read:user user:email',
            'allow_signup' => 'true',
        ]);

        return redirect("https://github.com/login/oauth/authorize?$query");
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
                    'Accept' => 'application/json',
                ])
                ->post('https://github.com/login/oauth/access_token', [
                    'client_id' => config('sso.providers.github.client_id'),
                    'client_secret' => config('sso.providers.github.client_secret'),
                    'redirect_uri' => config('sso.providers.github.redirect'),
                    'code' => $code,
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
                ->get('https://api.github.com/user');

            if (!$userResponse->successful()) {
                return [
                    'status' => false,
                    'message' => 'Failed to retrieve user profile.',
                    'user' => null
                ];
            }

            $emailsResponse = Http::withToken($accessToken)
                ->acceptJson()
                ->get('https://api.github.com/user/emails');

            $primaryEmail = collect($emailsResponse->json())->firstWhere('primary', true)['email'] ?? null;

            $user = $userResponse->json();

            return [
                'status' => true,
                'message' => 'GitHub login successful.',
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'] ?? $user['login'],
                    'email' => $primaryEmail,
                    'avatar' => $user['avatar_url'] ?? null,
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