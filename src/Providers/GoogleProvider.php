<?php
namespace Sahdev\SSO\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GoogleProvider
{
    public function redirect()
    {
        $query = http_build_query([
            'client_id' => config('sso.providers.google.client_id'),
            'redirect_uri' => config('sso.providers.google.redirect'),
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'access_type' => 'offline',
            'prompt' => 'consent'
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function callback(Request $request)
    {
        try {
            $code = request('code');
            if (!$code) {
                return response()->json(['status' => false, 'message' => 'Authorization code not found.'], 400);
            }

            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('sso.providers.google.client_id'),
                'client_secret' => config('sso.providers.google.client_secret'),
                'redirect_uri' => config('sso.providers.google.redirect'),
                'grant_type' => 'authorization_code',
                'code' => $code,
            ]);

            if (!$tokenResponse->successful()) {
                return response()->json(['status' => false, 'message' => 'Failed to get access token.'], 500);
            }

            $accessToken = $tokenResponse->json()['access_token'];

            $userResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if (!$userResponse->successful()) {
                return response()->json(['status' => false, 'message' => 'Failed to fetch user info.'], 500);
            }
            return response()->json([
                'status' => true,
                'message' => 'Google login successful.',
                'data' => [
                    "sub" => $userResponse['sub'],
                    "name" => $userResponse['name'],
                    "given_name" => $userResponse['given_name'],
                    "family_name" => $userResponse['family_name'],
                    "picture" => $userResponse['picture'],
                    "email" => $userResponse['email'],
                    "email_verified" => $userResponse['email_verified'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ], 500);
        }
    }
}
