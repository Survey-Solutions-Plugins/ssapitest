<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\HeadquartersApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Show the Survey Solutions dashboard
     */
    public function index(?string $module = null)
    {
        // Check if user is authenticated with headquarters
        if (!session('headquarters_url') || !session('api_credentials')) {
            return redirect()->route('home')->with('error', 'Please login to headquarters first');
        }

        $activeModule = $module ?: 'overview';

        $headquartersUrl = session('headquarters_url');
        $hqBaseUrl = session('hq_base_url', $headquartersUrl);
        $workspace = session('hq_workspace');
        $username = data_get(session('api_credentials'), 'username');
        $password = data_get(session('api_credentials'), 'password');

        $authType = data_get(session('hq_auth'), 'type', 'basic');
        $token = data_get(session('hq_auth'), 'token');

        \Log::info('DashboardController creating client', [
            'session_hq_base_url' => $hqBaseUrl,
            'session_workspace' => $workspace,
            'username' => $username,
            'auth_type' => $authType
        ]);

        $client = new HeadquartersApiClient($hqBaseUrl, $workspace, $username, $password, $authType === 'bearer' ? $token : null);

        $apiWarnings = [];

        $workspaces = collect();
        $assignments = collect();
        $interviews = collect();
        $users = collect();

        $usersDebug = null;
        $workspaceStatus = null;

        $usersAccessDenied = false;
        $usersAccessError = false;

        // Always attempt to refresh role (fixes stale/incorrect session values)
        $userRole = session('hq_user_role');
        try {
            $detected = $client->getCurrentUserRole();
            if (!empty($detected) && $detected !== $userRole) {
                $userRole = $detected;
                session(['hq_user_role' => $detected]);
            }
        } catch (\Throwable $e) {
            // ignore role detection failures
        }

        // Always fetch workspaces so header counts are accurate across modules
        try {
            $workspaces = $client->getWorkspaces();
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $status = optional($e->response)->status();
            $apiWarnings[] = "Workspaces: HQ returned HTTP {$status} for GET /api/v1/workspaces";
            Log::warning('HQ API error', ['endpoint' => 'workspaces', 'status' => $status, 'url' => $headquartersUrl]);
        } catch (\Throwable $e) {
            $apiWarnings[] = 'Workspaces: unable to fetch (network/timeout)';
            Log::warning('HQ API error', ['endpoint' => 'workspaces', 'error' => $e->getMessage(), 'url' => $headquartersUrl]);
        }

        if (in_array($activeModule, ['overview', 'assignments'], true)) {
            try {
                $assignments = $client->getAssignments(10);
            } catch (\Illuminate\Http\Client\RequestException $e) {
                $status = optional($e->response)->status();
                $apiWarnings[] = "Assignments: HQ returned HTTP {$status} for GET /api/v1/assignments";
                Log::warning('HQ API error', ['endpoint' => 'assignments', 'status' => $status, 'url' => $headquartersUrl]);
            } catch (\Throwable $e) {
                $apiWarnings[] = 'Assignments: unable to fetch (network/timeout)';
                Log::warning('HQ API error', ['endpoint' => 'assignments', 'error' => $e->getMessage(), 'url' => $headquartersUrl]);
            }
        }

        if (in_array($activeModule, ['overview', 'interviews'], true)) {
            try {
                $interviews = $client->getInterviews(10);
            } catch (\Illuminate\Http\Client\RequestException $e) {
                $status = optional($e->response)->status();
                $apiWarnings[] = "Interviews: HQ returned HTTP {$status} for GET /api/v1/interviews";
                Log::warning('HQ API error', ['endpoint' => 'interviews', 'status' => $status, 'url' => $headquartersUrl]);
            } catch (\Throwable $e) {
                $apiWarnings[] = 'Interviews: unable to fetch (network/timeout)';
                Log::warning('HQ API error', ['endpoint' => 'interviews', 'error' => $e->getMessage(), 'url' => $headquartersUrl]);
            }
        }

        if (in_array($activeModule, ['overview', 'users'], true)) {
            try {
                $result = $client->getUsersUnified(50);
                $users = collect(data_get($result, 'users', []));
                $usersDebug = data_get($result, 'debug');
                $apiWarnings = array_values(array_unique(array_merge($apiWarnings, (array) data_get($result, 'warnings', []))));

                $usersAccessDenied = collect($apiWarnings)->contains(fn ($w) => is_string($w) && str_contains($w, 'Users: HQ returned HTTP 403'));
            } catch (\Throwable $e) {
                $apiWarnings[] = 'Users: unable to fetch (network/timeout)';
                $usersAccessError = true;
                Log::warning('HQ API error', ['endpoint' => 'users', 'error' => $e->getMessage(), 'url' => $headquartersUrl]);
            }

            // Fallback counts via workspace status when user listing is empty
            if ($workspace && ($users->isEmpty() || $usersAccessDenied)) {
                try {
                    $workspaceStatus = $client->getWorkspaceStatus($workspace);
                } catch (\Throwable $e) {
                    Log::debug('Workspace status fetch failed', ['workspace' => $workspace, 'error' => $e->getMessage()]);
                }
            }
        }

        // Get data for dashboard
        $dashboardData = [
            'headquarters_url' => session('headquarters_url'),
            'username' => session('api_credentials.username'),
            'workspaces' => $workspaces,
            'assignments' => $assignments,
            'interviews' => $interviews,
            'users' => $users,
            'apiWarnings' => $apiWarnings,
            'activeModule' => $activeModule,
            'usersAccessDenied' => $usersAccessDenied,
            'usersAccessError' => $usersAccessError,
            'usersDebug' => $usersDebug,
            'workspaceStatus' => $workspaceStatus,
            'userRole' => $userRole,
        ];

        return view('dashboard', $dashboardData);
    }

    /**
     * Get workspaces from API
     */
}
