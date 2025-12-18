<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\HeadquartersApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * Show the Survey Solutions login page
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Handle the headquarters login
     */
    public function login(Request $request)
    {
        $request->validate([
            'headquarters_url' => 'required|url',
            'username' => 'required|string',
            'password' => 'required',
            'workspace' => 'nullable|string'
        ]);

        $hqBaseUrl = rtrim((string) $request->headquarters_url, '/');
        $workspace = trim((string) $request->workspace);
        $headquartersUrl = $workspace !== '' ? ($hqBaseUrl . '/' . ltrim($workspace, '/')) : $hqBaseUrl;
        $username = $request->username;
        $password = $request->password;

        // Validate credentials against the HQ.
        // This system is restricted to API User accounts only.
        // Survey Solutions: Admin users have web UI access but API users have API access.
        // Real Survey Solutions HQ: HTTP Basic auth.
        // Local API: Bearer token login fallback.
        
        \Log::info('API User login attempt', ['username' => $username, 'url' => $headquartersUrl]);
        
        try {
            $client = new HeadquartersApiClient($hqBaseUrl, $workspace ?: null, $username, $password);

            try {
                $client->getWorkspaces();

                // Detect and store the connected user's role for UI permissions
                $detectedRole = $client->getCurrentUserRole();

                session([
                    'headquarters_url' => $headquartersUrl,
                    'hq_base_url' => $hqBaseUrl,
                    'hq_workspace' => $workspace ?: null,
                    'api_credentials' => [
                        'username' => $username,
                        'password' => $password,
                    ],
                    'hq_auth' => [
                        'type' => 'basic',
                    ],
                    'hq_user_role' => $detectedRole,
                ]);

                return redirect()->route('dashboard');
            } catch (\Illuminate\Http\Client\RequestException $e) {
                $status = optional($e->response)->status();

                // 403 usually means credentials are valid but the user is not allowed to access this endpoint.
                // For API users, some endpoints might be forbidden but they can still access others
                if ($status === 403) {
                    \Log::info('API user login with 403 for workspaces', ['username' => $username, 'url' => $headquartersUrl]);
                    
                    // For API users, 403 on workspaces might be normal - try to proceed
                    $detectedRole = $client->getCurrentUserRole();

                    session([
                        'headquarters_url' => $headquartersUrl,
                        'hq_base_url' => $hqBaseUrl,
                        'hq_workspace' => $workspace ?: null,
                        'api_credentials' => [
                            'username' => $username,
                            'password' => $password,
                        ],
                        'hq_auth' => [
                            'type' => 'basic',
                        ],
                        'hq_user_role' => $detectedRole,
                    ]);

                    return redirect()->route('dashboard')->with('warning', "Authenticated with API User account at {$headquartersUrl}, but access to /api/v1/workspaces is forbidden (HTTP 403). This is normal for some API users - other endpoints may still be accessible.");
                }

                // If basic auth is rejected, don't blindly claim the username/password is wrong.
                // Try bearer only if the HQ exposes our local auth endpoint.
                if ($status === 401) {
                    $bearer = $client->tryBearerLogin();

                    // Real Survey Solutions HQ doesn't have /api/v1/auth/login
                    if (in_array($bearer['status'] ?? null, [404, 405], true)) {
                        return back()->withInput()->with('error', "Headquarters rejected Basic authentication (HTTP {$status}) at {$headquartersUrl}. This HQ does not support token login. Please verify the API user exists in Headquarters (role: API user) and that the password is correct. Tip: if your HQ runs locally, try using http://127.0.0.1:9700 instead of http://localhost:9700.");
                    }

                    if (!($bearer['ok'] ?? false) || empty($bearer['token'])) {
                        $bearerStatus = $bearer['status'] ?? 'unknown';
                        return back()->withInput()->with('error', "Unable to authenticate with the headquarters server at {$headquartersUrl} (Basic HTTP {$status}; token login HTTP {$bearerStatus}). Please verify username/password and API permissions.");
                    }

                    $client->setBearerToken($bearer['token']);
                    $client->getWorkspaces();
                    $detectedRole = $client->getCurrentUserRole();

                    session([
                        'headquarters_url' => $headquartersUrl,
                        'hq_base_url' => $hqBaseUrl,
                        'hq_workspace' => $workspace ?: null,
                        'api_credentials' => [
                            'username' => $username,
                            'password' => $password,
                        ],
                        'hq_auth' => [
                            'type' => 'bearer',
                            'token' => $bearer['token'],
                        ],
                        'hq_user_role' => $detectedRole,
                    ]);

                    return redirect()->route('dashboard');
                }

                throw $e;
            }
        } catch (\Throwable $e) {
            Log::warning('HQ connection/login failed', [
                'headquarters_url' => $headquartersUrl,
                'username' => $username,
                'error' => $e->getMessage(),
                'class' => get_class($e),
            ]);

            $message = 'Unable to connect to the headquarters server.';
            if (Str::contains($e->getMessage(), ['cURL error', 'Connection', 'Could not resolve'])) {
                $message = 'Unable to reach the headquarters URL. Please verify the URL and network connectivity.';
            }
            return back()->withInput()->with('error', $message);
        }
    }

    /**
     * Logout from headquarters
     */
    public function logout()
    {
        session()->flush();
        return redirect()->route('home');
    }
}
