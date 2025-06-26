<?php

namespace Sahdev\SSO\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Sahdev\SSO\Contracts\SSOProviderInterface;

class LinkedinProvider implements SSOProviderInterface
{

    public function redirect()
    {
        $clientId = config('sso.providers.linkedin.client_id');
        $redirectUri = urlencode(config('sso.providers.linkedin.redirect'));
        $scope = 'r_liteprofile r_emailaddress';
        $state = csrf_token();
        $url = "https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id=$clientId&redirect_uri=$redirectUri&scope=$scope&state=$state";

        return redirect($url);
    }

    public function callback(Request $request)
    {
        try {
            // Get the authorization code from the request
            $code = $request->get('code');
            if (!$code) {
                return response()->json(['status' => false, 'message' => 'Authorization code not found.'], 400);
            }

            // Exchange the authorization code for an access token
            $tokenResponse = Http::asForm()->post('https://www.linkedin.com/oauth/v2/accessToken', [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => config('sso.providers.linkedin.redirect'),
                'client_id' => config('sso.providers.linkedin.client_id'),
                'client_secret' => config('sso.providers.linkedin.client_secret'),
            ]);

            if (!$tokenResponse->successful()) {
                return response()->json(['status' => false, 'message' => 'Failed to get access token.'], 500);
            }

            $accessToken = $tokenResponse->json()['access_token'];

            // Fetch user information from LinkedIn
            $userResponse = Http::withToken($accessToken)
                ->get('https://api.linkedin.com/v2/me');

            if (!$userResponse->successful()) {
                return response()->json(['status' => false, 'message' => 'Failed to fetch user info.'], 500);
            }

            // Fetch user's email address from LinkedIn
            $emailResponse = Http::withToken($accessToken)
                ->get('https://api.linkedin.com/v2/emailAddress?q=members&projection=(elements*(handle~))');

            if (!$emailResponse->successful()) {
                return response()->json(['status' => false, 'message' => 'Failed to fetch user email.'], 500);
            }

            // Prepare user data
            $userInfo = $userResponse->json();
            $emailInfo = $emailResponse->json()['elements'][0]['handle~']['emailAddress'];

            return response()->json([
                'status' => true,
                'message' => 'LinkedIn login successful.',
                'data' => [
                    "id" => $userInfo['id'],
                    "first_name" => $userInfo['localizedFirstName'],
                    "last_name" => $userInfo['localizedLastName'],
                    "email" => $emailInfo,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ], 500);
        }
    }
}
