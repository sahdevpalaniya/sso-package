# SSO-Package for Laravel

## Overview

This Laravel Composer package provides seamless integration for multiple Single Sign-On (SSO) providers. The supported providers in this version include:

* GitHub
* Google
* JumpCloud
* LinkedIn
* Twitter

The package is designed to be modular, scalable, and easy to configure for any Laravel application.

---

## Installation

1. **Require the Package**

   ```bash
   composer require sahdevpalaniya/laravel-multi-sso
   ```

2. **Publish the Configuration** Publish the configuration file to your Laravel project:

   ```bash
   php artisan vendor:publish --tag=sso-config
   ```

   This will create a configuration file (`sso.php`) in the `config` directory.

3. **Generate Configuration File** The configuration file will look like this:

   ```php
   <?php
   return [
       'providers' => [
           'google' => [
               'client_id' => env('SSO_GOOGLE_CLIENT_ID'),
               'client_secret' => env('SSO_GOOGLE_CLIENT_SECRET'),
               'redirect' => env('SSO_GOOGLE_REDIRECT'),
           ],
       ],
      // Add more SSO configurations keys
   ];
   ```

4. **Add Service Provider** Register the service provider in `config/app.php` (if not automatically added):

   ```php
   'providers' => [
       Sahdev\SSO\SSOServiceProvider::class,
   ],
   ```

5. **Configure Environment Variables** Add the necessary environment variables for your desired SSO providers in the `.env` file. Below are detailed instructions for each provider:

   ### GitHub

   Example:

   ```env
   GITHUB_CLIENT_ID=your-github-client-id
   GITHUB_CLIENT_SECRET=your-github-client-secret
   GITHUB_REDIRECT_URI=https://yourdomain.com/callback/github
   ```

   Generate Keys:

   1. Visit [GitHub Developer Settings](https://github.com/settings/developers).

   2. Click on "New OAuth App" and provide the following details:

      * **Application Name**: Your app name
      * **Homepage URL**: [https://yourdomain.com](https://yourdomain.com)
      * **Authorization Callback URL**: [https://yourdomain.com/callback/github](https://yourdomain.com/callback/github)

   3. Save the app to get the **Client ID** and **Client Secret**.

   ### Google

   Example:

   ```env
   GOOGLE_CLIENT_ID=your-google-client-id
   GOOGLE_CLIENT_SECRET=your-google-client-secret
   GOOGLE_REDIRECT_URI=https://yourdomain.com/callback/google
   ```

   Generate Keys:

   1. Go to the [Google Cloud Console](https://console.cloud.google.com/).
   2. Create a new project.
   3. Enable the "OAuth consent screen" and configure the required scopes.
   4. Create credentials for "OAuth 2.0 Client IDs" and provide the redirect URI: [https://yourdomain.com/callback/google](https://yourdomain.com/callback/google).
   5. Save to get the **Client ID** and **Client Secret**.

   ### JumpCloud

   Example:

   ```env
   JUMPCLOUD_ENTITY_ID=your-jumpcloud-entity-id
   JUMPCLOUD_SSO_URL=your-jumpcloud-sso-url
   JUMPCLOUD_CERTIFICATE=your-jumpcloud-certificate
   ```

   Generate Keys:

   1. Log in to [JumpCloud Admin Portal](https://console.jumpcloud.com/).
   2. Go to "SSO" and add a new application.
   3. Configure the SSO URL, Entity ID, and X.509 Certificate.
   4. Use the generated details in your `.env` file.

   ### LinkedIn

   Example:

   ```env
   LINKEDIN_CLIENT_ID=your-linkedin-client-id
   LINKEDIN_CLIENT_SECRET=your-linkedin-client-secret
   LINKEDIN_REDIRECT_URI=https://yourdomain.com/callback/linkedin
   ```

   Generate Keys:

   1. Visit [LinkedIn Developer Portal](https://www.linkedin.com/developers/).
   2. Create a new app and configure the redirect URI: [https://yourdomain.com/callback/linkedin](https://yourdomain.com/callback/linkedin).
   3. Save the app to get the **Client ID** and **Client Secret**.

   ### Twitter

   Example:

   ```env
   TWITTER_CLIENT_ID=your-twitter-client-id
   TWITTER_CLIENT_SECRET=your-twitter-client-secret
   TWITTER_REDIRECT_URI=https://yourdomain.com/callback/twitter
   ```

   Generate Keys:

   1. Visit [Twitter Developer Portal](https://developer.twitter.com/).
   2. Create a new project and app.
   3. Configure the callback URL: [https://yourdomain.com/callback/twitter](https://yourdomain.com/callback/twitter).
   4. Save to get the **API Key** (Client ID) and **API Secret Key** (Client Secret).

---

## Usage

1. **Routes** The package provides predefined routes for redirecting to the SSO providers and handling callbacks. Add these routes in your `routes/web.php` file:

   ```php
   Route::get('/auth/redirect/{provider}', [SSOManager::class, 'redirect'])->name('sso.redirect');
   Route::get('/auth/callback/{provider}', [SSOManager::class, 'callback'])->name('sso.callback');
   ```

2. **Redirect to SSO Provider** Use the following URL format to redirect users to the desired SSO provider:

   ```
   /auth/redirect/{provider}
   ```

   Replace `{provider}` with one of the supported providers (`github`, `google`, `jumpcloud`, `linkedin`, `twitter`).

3. **Handle Callback** After successful authentication, the callback URL will process the user information and provide access to authenticated user data.

4. **Example Implementation** Below is an example of handling callback data using the package's `getData` method:

   ```php
   use Illuminate\Http\Request;
   use YourVendor\SSO\Facades\SSO;

   Route::get('/auth/callback/{provider}', function (Request $request, $provider) {
       try {
           $providerData = SSO::driver($provider)->callback($request);
           $userData = $providerData->getData()->data; // Access user data

           // Example: Log the user data
           logger()->info('User Data:', (array) $userData);

           // Redirect to dashboard or other page
           return redirect('/dashboard')->with('user', $userData);
       } catch (Exception $e) {
           return redirect('/login')->withErrors(['error' => $e->getMessage()]);
       }
   });
   ```

   The `getData()` method returns the user data provided by the SSO provider. Adjust the implementation based on your application's needs.

---

## Contributing

Contributions are welcome! Feel free to fork see [CONTRIBUTING](CONTRIBUTING) and submit PR.

## Changelog

## See [CHANGELOG](CHANGELOG) for a detailed history of changes.

## License

This project is open-source and licensed under the MIT License. You are free to use, modify, and distribute this package in your own projects, whether for personal or commercial purposes, under the following conditions:

1. **Attribution**: Credit must be given to the original authors and contributors. The license text must remain intact in all copies or significant portions of the software.
2. **No Warranty**: The software is provided "as-is," without any warranty, express or implied, including but not limited to fitness for a particular purpose.

We welcome contributions to this project. Feel free to fork the repository, suggest improvements, or submit pull requests to make this package better for the community.

The full license text can be found in the [LICENSE](LICENSE) file in this repository.

---

## Support

For any questions or issues, please create an [issue](https://github.com/sahdevpalaniya/laravel-multi-sso/issues) in this repository or contact [sahdevsinhpalaniya98@gmail.com](mailto:sahdevsinhpalaniya98@gmail.com).