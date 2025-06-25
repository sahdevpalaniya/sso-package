<?php

namespace Sahdev\SSO\Providers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Sahdev\SSO\Contracts\SSOProviderInterface;

class JumpCloudProvider implements SSOProviderInterface
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $authUrl = 'https://oauth.id.jumpcloud.com/oauth2/auth';
    protected $tokenUrl = 'https://oauth.id.jumpcloud.com/oauth2/token';
    protected $userInfoUrl = 'https://oauth.id.jumpcloud.com/userinfo';

    public function __construct()
    {
        $this->clientId = config('sso.providers.jumpcloud.client_id');
        $this->clientSecret = config('sso.providers.jumpcloud.client_secret');
        $this->redirectUri = config('sso.providers.jumpcloud.redirect');
    }

    /**
     * Redirect the user to the JumpCloud authentication page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        $state = Str::random(40);
        session(['jumpcloud_oauth_state' => $state]);

        $query = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => $state,
        ]);

        return redirect($this->authUrl . '?' . $query);
    }

    /**
     * Handle the callback from JumpCloud.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        $state = session('jumpcloud_oauth_state');

        if (!$request->has('state') || $request->state !== $state) {
            return response()->json(['message' => 'Invalid state parameter'], 400);
        }

        if (!$request->has('code')) {
            return response()->json(['message' => 'Authorization code not provided'], 400);
        }

        try {
            $accessToken = $this->getAccessToken($request->code);
            $userData = $this->getUserByToken($accessToken);

            $userDetails = [
                'id' => $userData['sub'] ?? null,
                'name' => $userData['name'] ?? null,
                'email' => $userData['email'] ?? null,
                'given_name' => $userData['given_name'] ?? null,
                'family_name' => $userData['family_name'] ?? null,
                'email_verified' => $userData['email_verified'] ?? false,
                "accessToken" => $accessToken
            ];

            return response()->json([
                'status' => true,
                'message' => 'JumpCloud login successful.',
                'data' => $userDetails,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Exception: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get access token using authorization code.
     *
     * @param string $code
     * @return string
     * @throws \Exception
     */
    protected function getAccessToken($code)
    {
        $response = Http::asForm()->post($this->tokenUrl, [
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to get access token: ' . $response->body());
        }

        return $response->json('access_token');
    }

    /**
     * Get user information using access token.
     *
     * @param string $token
     * @return array
     * @throws \Exception
     */
    protected function getUserByToken($token)
    {
        $response = Http::withToken($token)->get($this->userInfoUrl);

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch user information: ' . $response->body());
        }
        $userData = $response->json();

        return $userData;
    }
}
