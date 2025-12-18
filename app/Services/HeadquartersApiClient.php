<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class HeadquartersApiClient
{
    private string $hqBaseUrl;
    private ?string $workspace;
    private string $username;
    private string $password;
    private ?string $bearerToken;

    public function __construct(string $hqBaseUrl, ?string $workspace, string $username, string $password, ?string $bearerToken = null)
    {
        $this->hqBaseUrl = rtrim($hqBaseUrl, '/');
        $this->workspace = $workspace ? trim($workspace) : null;
        $this->username = $username;
        $this->password = $password;
        $this->bearerToken = $bearerToken;
    }

    public function setBearerToken(?string $token): void
    {
        $this->bearerToken = $token;
    }

    public function hasBearerToken(): bool
    {
        return !empty($this->bearerToken);
    }

    private function url(string $path, bool $workspaceScoped = true): string
    {
        $base = $workspaceScoped && $this->workspace ? ($this->hqBaseUrl . '/' . $this->workspace) : $this->hqBaseUrl;
        return $base . '/' . ltrim($path, '/');
    }

    /**
     * Build a URL explicitly for a given workspace name.
     * Useful when iterating over multiple workspaces while this client
     * has no single workspace set in the session.
     */
    private function urlForWorkspace(string $path, ?string $workspaceName): string
    {
        $base = $workspaceName ? ($this->hqBaseUrl . '/' . trim($workspaceName)) : $this->hqBaseUrl;
        return $base . '/' . ltrim($path, '/');
    }

    public function getUrl(string $path, bool $workspaceScoped = true): string
    {
        return $this->url($path, $workspaceScoped);
    }

    /**
     * Explicit Basic auth GET as per recommended pattern
     */
    private function basicGet(string $path, array $query = [], bool $workspaceScoped = true): \Illuminate\Http\Client\Response
    {
        return Http::withBasicAuth($this->username, $this->password)
            ->acceptJson()
            ->timeout(20)
            ->connectTimeout(10)
            ->get($this->url($path, $workspaceScoped), $query);
    }

    /**
     * Explicit Basic auth GET with a workspace override.
     */
    private function basicGetForWorkspace(string $workspaceName, string $path, array $query = []): \Illuminate\Http\Client\Response
    {
        return Http::withBasicAuth($this->username, $this->password)
            ->acceptJson()
            ->timeout(20)
            ->connectTimeout(10)
            ->get($this->urlForWorkspace($path, $workspaceName), $query);
    }

    /**
     * Explicit Basic auth POST as per recommended pattern
     */
    private function basicPost(string $path, array $data = [], bool $workspaceScoped = true): \Illuminate\Http\Client\Response
    {
        return Http::withBasicAuth($this->username, $this->password)
            ->acceptJson()
            ->timeout(20)
            ->connectTimeout(10)
            ->post($this->url($path, $workspaceScoped), $data);
    }

    /**
     * Explicit Basic auth PATCH as per recommended pattern
     */
    private function basicPatch(string $path, array $data = [], bool $workspaceScoped = true): \Illuminate\Http\Client\Response
    {
        return Http::withBasicAuth($this->username, $this->password)
            ->acceptJson()
            ->timeout(20)
            ->connectTimeout(10)
            ->patch($this->url($path, $workspaceScoped), $data);
    }

    public function getClient(?string $workspace = null): PendingRequest
    {
        return $this->client($workspace);
    }

    private function client(?string $workspace = null): PendingRequest
    {
        $request = Http::acceptJson()
            ->timeout(20)
            ->connectTimeout(10);

        // Path-based workspace scoping is used; avoid non-standard headers.

        if ($this->bearerToken) {
            return $request->withToken($this->bearerToken);
        }

        return $request->withBasicAuth($this->username, $this->password);
    }

    /**
     * Some installations (like this local Laravel API) use Bearer tokens.
     * Real Survey Solutions HQ typically uses HTTP Basic auth.
     */
    public function tryBearerLogin(): array
    {
        $payload = ['password' => $this->password];
        if (Str::contains($this->username, '@')) {
            $payload['email'] = $this->username;
        }
        $payload['username'] = $this->username;

        $response = Http::acceptJson()
            ->timeout(20)
            ->connectTimeout(10)
            ->post($this->url('/api/v1/auth/login', false), $payload);

        if (!$response->ok()) {
            return [
                'ok' => false,
                'status' => $response->status(),
                'token' => null,
            ];
        }

        $token = data_get($response->json(), 'token');
        $token = is_string($token) && $token !== '' ? $token : null;

        return [
            'ok' => $token !== null,
            'status' => $response->status(),
            'token' => $token,
        ];
    }

    public function getWorkspaces(): Collection
    {
        // Workspaces endpoint is global (not workspace-scoped)
        $response = $this->basicGet('/api/v1/workspaces', [], false);
        $response->throw();

        $json = $response->json();
        // Support both Survey Solutions casing (Workspaces) and alternatives
        $items = $this->extractList($json, ['workspaces', 'Workspaces', 'items']);

        return collect($items)->map(function ($w) {
            $name = data_get($w, 'name', data_get($w, 'Name'));
            $displayName = data_get($w, 'display_name', data_get($w, 'displayName', data_get($w, 'DisplayName')));
            $isDisabled = data_get($w, 'disabled', data_get($w, 'isDisabled'));
            // Some responses provide DisabledAtUtc instead of a boolean
            if ($isDisabled === null) {
                $disabledAt = data_get($w, 'DisabledAtUtc');
                $isDisabled = $disabledAt !== null;
            }

            return [
                'name' => is_string($name) ? $name : null,
                'display_name' => is_string($displayName) ? $displayName : null,
                'disabled' => (bool) $isDisabled,
                'description' => data_get($w, 'description', data_get($w, 'Description')),
            ];
        })->filter(fn ($w) => is_string($w['name']) && trim($w['name']) !== '')->values();
    }

    public function getAssignments(int $limit = 10): Collection
    {
        // Assignments are workspace-scoped
        $response = $this->basicGet('/api/v1/assignments', [
            'limit' => $limit,
            'offset' => 0,
        ], true);

        // Some HQ instances use different paging params; if this fails, retry without params.
        if (!$response->ok()) {
            $response = $this->basicGet('/api/v1/assignments', [], true);
        }

        $response->throw();
        $json = $response->json();
        $items = $this->extractList($json, ['assignments', 'items']);

        return collect($items)->map(function ($a) {
            return [
                'id' => data_get($a, 'id'),
                'questionnaire_title' => data_get($a, 'questionnaireTitle', data_get($a, 'questionnaire.title', data_get($a, 'questionnaireId'))),
                'workspace' => data_get($a, 'workspace', data_get($a, 'workspaceName', data_get($a, 'workspace.display_name'))),
                'responsible' => data_get($a, 'responsible', data_get($a, 'responsibleName', data_get($a, 'responsible.name'))),
                'status' => data_get($a, 'status'),
                'quantity' => (int) data_get($a, 'quantity', data_get($a, 'size', 0)),
                'interviews_count' => (int) data_get($a, 'interviewsCount', data_get($a, 'interviews_count', 0)),
            ];
        })->values();
    }

    public function getInterviews(int $limit = 10): Collection
    {
        // Interviews are workspace-scoped
        $response = $this->basicGet('/api/v1/interviews', [
            'limit' => $limit,
            'offset' => 0,
        ], true);

        if (!$response->ok()) {
            $response = $this->basicGet('/api/v1/interviews', [], true);
        }

        $response->throw();
        $json = $response->json();
        $items = $this->extractList($json, ['interviews', 'items']);

        return collect($items)->map(function ($i) {
            $createdRaw = data_get($i, 'createdAt', data_get($i, 'created_at', data_get($i, 'created')));
            $createdAt = null;
            if (is_string($createdRaw) && $createdRaw !== '') {
                try {
                    $createdAt = Carbon::parse($createdRaw)->toDateTimeString();
                } catch (\Throwable $e) {
                    $createdAt = $createdRaw;
                }
            }

            return [
                'interview_id' => data_get($i, 'id', data_get($i, 'interview_id')),
                'assignment_id' => data_get($i, 'assignmentId', data_get($i, 'assignment_id')),
                'interviewer' => data_get($i, 'interviewer', data_get($i, 'interviewerName', data_get($i, 'interviewer.name'))),
                'status' => data_get($i, 'status'),
                'created_at' => $createdAt,
            ];
        })->values();
    }

    private function firstNonEmptyString(array $source, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($source, $key);
            if (is_string($value)) {
                $value = trim($value);
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return null;
    }

    private function pickLoginName(array $u): ?string
    {
        return $this->firstNonEmptyString($u, [
            'UserName',
            'userName',
            'username',
            'LoginName',
            'loginName',
            'login_name',
            'Login',
            'login',
        ]);
    }

    public function getFieldStaff(int $limit = 50): Collection
    {
        // Survey Solutions exposes supervisors list. Interviewers are listed per supervisor.
        // Users endpoints are workspace-scoped
        $response = $this->basicGet('/api/v1/supervisors', [
            'limit' => $limit,
            'offset' => 1,
        ], true);
        if (!$response->ok()) {
            $response = $this->basicGet('/api/v1/supervisors', [], true);
        }
        $response->throw();

        $supervisors = $this->extractList($response->json(), ['Users', 'users', 'items', 'supervisors']);

        $users = collect();

        foreach (collect($supervisors)->take($limit) as $s) {
            $supervisorId = data_get($s, 'UserId', data_get($s, 'userId', data_get($s, 'Id', data_get($s, 'id'))));
            $supervisorUsername = $this->pickLoginName($s);
            $email = data_get($s, 'Email', data_get($s, 'email'));
            $isLocked = (bool) data_get($s, 'IsLocked', data_get($s, 'isLocked', false));

            $users->push([
                'id' => $supervisorId,
                'name' => data_get($s, 'FullName', data_get($s, 'fullName', $supervisorUsername)),
                'username' => $supervisorUsername ?: (is_string($supervisorId) ? $supervisorId : null),
                'email' => $email,
                'role' => 'Supervisor',
                'phone' => data_get($s, 'PhoneNumber', data_get($s, 'phoneNumber', data_get($s, 'phone'))),
                'is_archived' => (bool) data_get($s, 'IsArchived', data_get($s, 'isArchived', false)),
                'is_locked' => $isLocked,
                'creation_date' => data_get($s, 'CreationDate', data_get($s, 'creationDate')),
            ]);

            if (!$supervisorId) {
                continue;
            }

            try {
                $r = $this->basicGet("/api/v1/supervisors/{$supervisorId}/interviewers", [
                    'limit' => $limit,
                    'offset' => 1,
                ], true);
                if (!$r->ok()) {
                    $r = $this->basicGet("/api/v1/supervisors/{$supervisorId}/interviewers", [], true);
                }

                if ($r->ok()) {
                    $interviewers = $this->extractList($r->json(), ['Users', 'users', 'items', 'interviewers']);
                    foreach (collect($interviewers)->take($limit) as $iv) {
                        $interviewerId = data_get($iv, 'UserId', data_get($iv, 'userId', data_get($iv, 'Id', data_get($iv, 'id'))));
                        $interviewerUsername = $this->pickLoginName($iv);

                        $users->push([
                            'id' => $interviewerId,
                            'name' => data_get($iv, 'FullName', data_get($iv, 'fullName', $interviewerUsername)),
                            'username' => $interviewerUsername ?: (is_string($interviewerId) ? $interviewerId : null),
                            'email' => data_get($iv, 'Email', data_get($iv, 'email')),
                            'role' => 'Interviewer',
                            'supervisor' => $supervisorUsername,
                            'phone' => data_get($iv, 'PhoneNumber', data_get($iv, 'phoneNumber', data_get($iv, 'phone'))),
                            'is_archived' => (bool) data_get($iv, 'IsArchived', data_get($iv, 'isArchived', false)),
                            'is_locked' => (bool) data_get($iv, 'IsLocked', data_get($iv, 'isLocked', false)),
                            'creation_date' => data_get($iv, 'CreationDate', data_get($iv, 'creationDate')),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // ignore missing permission/endpoints
            }
        }

        return $users
            ->filter(fn ($u) => !empty($u['username']) || !empty($u['id']))
            ->unique(fn ($u) => strtolower((string) ($u['username'] ?? $u['id'] ?? '')))
            ->values();
    }

    /**
     * Attempts to fetch a broader users list (if the HQ exposes it), otherwise falls back to supervisors/interviewers.
     * Returns both users and any endpoint warnings (e.g. HTTP 403).
     */
    public function getUsersUnified(int $limit = 50): array
    {
        $warnings = [];
        $users = collect();

        $debug = [
            'supervisors_raw' => 0,
            'supervisors_added' => 0,
            'supervisor_interviewers_raw' => 0,
            'supervisor_interviewers_added' => 0,
            'interviewers_list_status' => null,
            'interviewers_list_raw' => 0,
            'interviewers_list_added' => 0,
        ];

        // Officially supported listing endpoints are role-based.
        // Best-effort: get supervisors first, then team interviewers (gives supervisor mapping), then interviewers list.

        $supervisorUsernameById = [];
        $supervisorListOk = false;

        try {
            // Survey Solutions supervisors might be workspace-specific
            $workspaces = [];
            try {
                $workspaceResponse = $this->basicGet('/api/v1/workspaces', [], false);
                if ($workspaceResponse->ok()) {
                    $workspaces = $this->extractList($workspaceResponse->json(), ['workspaces', 'items']);
                    \Log::info('HQ workspaces found', ['workspaces' => $workspaces]);
                }
            } catch (\Throwable $e) {
                \Log::warning('Could not fetch workspaces for supervisor context', ['error' => $e->getMessage()]);
            }

            // API Users have different permissions - let's discover what this API user can access
            \Log::info('API User mode: discovering accessible endpoints for this API user');
            
            // Test various endpoints to see what this API user can actually access
            $testEndpoints = [
                '/api/v1/questionnaires',
                '/api/v1/users/current',  
                '/api/v1/interviews/status',
                '/api/v1/export/status',
                '/api/v1/maps',
                '/api/v1/supervisors/current',
                '/api/v1/interviewers/current'
            ];
            
            foreach ($testEndpoints as $endpoint) {
                try {
                    $testResponse = $this->basicGet($endpoint);
                    \Log::info("API User endpoint test: {$endpoint}", ['status' => $testResponse->status(), 'accessible' => $testResponse->status() !== 403]);
                    
                    if ($testResponse->ok()) {
                        $responseData = $testResponse->json();
                        \Log::info("Successful endpoint {$endpoint}", ['response' => $responseData]);
                        
                        // If this endpoint returns user data, try to extract it
                        if (str_contains($endpoint, 'supervisor') || str_contains($endpoint, 'interviewer') || str_contains($endpoint, 'user')) {
                            $users = $this->extractList($responseData, ['Users', 'users', 'items', 'supervisors', 'interviewers']);
                            if (!empty($users)) {
                                \Log::info("Found users in endpoint {$endpoint}", ['users' => $users]);
                                foreach ($users as $user) {
                                    if (is_array($user)) {
                                        $supervisors[] = $user;
                                        $debug['supervisors_raw']++;
                                    }
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::debug("API User endpoint test failed: {$endpoint}", ['error' => $e->getMessage()]);
                }
            }

            // Admin approach: try to get all users across workspaces
            $supervisors = [];
            
            // Method 1: Try with explicit workspace context (required for Survey Solutions)
            $supervisorWorkspaceById = [];
            foreach ($workspaces as $workspace) {
                $workspaceName = data_get($workspace, 'Name', data_get($workspace, 'name'));
                if (!$workspaceName) continue;
                
                try {
                    \Log::info("Trying supervisors with workspace context: {$workspaceName}");
                    // Use explicit workspace path e.g. /{workspace}/api/v1/supervisors
                    $response = $this->basicGetForWorkspace($workspaceName, '/api/v1/supervisors', [
                        'limit' => $limit,
                        'offset' => 1,
                    ]);
                    
                    if ($response->ok()) {
                        $supervisorListOk = true;
                        $rawJson = $response->json();
                        \Log::info("HQ supervisors with workspace {$workspaceName}", ['response' => $rawJson]);
                        $wsSupervisors = $this->extractList($rawJson, ['Users', 'users', 'items', 'supervisors']);
                        if (!empty($wsSupervisors)) {
                            $supervisors = array_merge($supervisors, $wsSupervisors);
                            // Track which workspace each supervisor id belongs to for fetching team interviewers
                            foreach ($wsSupervisors as $s) {
                                $sid = data_get($s, 'UserId', data_get($s, 'userId', data_get($s, 'Id', data_get($s, 'id'))));
                                if (is_string($sid) && $sid !== '') {
                                    $supervisorWorkspaceById[$sid] = $workspaceName;
                                }
                            }
                            \Log::info("Found supervisors in workspace {$workspaceName}!", ['count' => count($wsSupervisors)]);
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::info("Workspace {$workspaceName} supervisors failed", ['error' => $e->getMessage()]);
                }
            }

            // Fallback: try without workspace context
            if (empty($supervisors)) {
                try {
                    $response = $this->basicGet('/api/v1/supervisors', [
                        'limit' => $limit,
                        'offset' => 1,
                    ], true);
                    
                    if ($response->ok()) {
                        $supervisorListOk = true;
                        $rawJson = $response->json();
                        \Log::info('HQ /api/v1/supervisors raw response (no workspace)', ['response' => $rawJson]);
                        $supervisors = $this->extractList($rawJson, ['Users', 'users', 'items', 'supervisors']);
                    }
                } catch (\Throwable $e) {
                    \Log::info('Standard supervisors endpoint failed', ['error' => $e->getMessage()]);
                }
            }

            // Method 2: If no supervisors found, try per-workspace as admin
            if (empty($supervisors) && !empty($workspaces)) {
                \Log::info('Trying admin workspace-specific supervisor queries');
                foreach ($workspaces as $workspace) {
                    $workspaceName = data_get($workspace, 'Name', data_get($workspace, 'name'));
                    if (!$workspaceName) continue;
                    
                    try {
                        // Admin might be able to query users by workspace differently
                        $wsUsersResponse = $this->basicGet('/api/v1/supervisors', [
                            'limit' => $limit,
                            'workspace' => $workspaceName,
                        ], true);
                        
                        if ($wsUsersResponse->ok()) {
                            $wsUsers = $this->extractList($wsUsersResponse->json(), ['Users', 'users', 'items', 'supervisors']);
                            \Log::info("Admin found users in workspace {$workspaceName}", ['users' => $wsUsers]);
                            if (!empty($wsUsers)) {
                                $supervisors = array_merge($supervisors, $wsUsers);
                            }
                        }
                    } catch (\Throwable $e) {
                        \Log::debug("Admin workspace query failed for {$workspaceName}", ['error' => $e->getMessage()]);
                    }
                }
            }
            
            // Update debug counters based on what we found
            $debug['supervisors_raw'] = is_array($supervisors) ? count($supervisors) : 0;
            
            \Log::info('HQ supervisors extracted', ['extracted' => $supervisors, 'count' => $debug['supervisors_raw']]);

            // The API user might not have permissions to list supervisors
            if (empty($supervisors)) {
                \Log::warning('No supervisors found - this may be due to API user permissions. API users often have more restricted access than web UI users.');
                
                // Check if we can at least get individual user details if we know IDs
                // This is a fallback for permission-restricted scenarios
                $warnings[] = "Users: No supervisors found. The API user might lack permission to list supervisors, even if they're visible in the web interface.";
            }

            foreach (collect($supervisors)->take($limit) as $s) {
                if (!is_array($s)) {
                    continue;
                }
                $normalized = $this->normalizeUserItem($s, 'Supervisor');
                $users->push($normalized);
                $debug['supervisors_added']++;

                $sid = data_get($normalized, 'id');
                $sname = data_get($normalized, 'username');
                if ($sid && $sname) {
                    $supervisorUsernameById[(string) $sid] = (string) $sname;
                }
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $status = optional($e->response)->status();
            $warnings[] = "Users: HQ returned HTTP {$status} for GET /api/v1/supervisors";
            \Log::warning('HQ API error', ['endpoint' => 'supervisors', 'status' => $status, 'url' => $this->baseUrl]);
        } catch (\Throwable $e) {
            $warnings[] = "Users: Error fetching supervisors - " . $e->getMessage();
            \Log::warning('HQ API error', ['endpoint' => 'supervisors', 'error' => $e->getMessage(), 'url' => $this->baseUrl]);
        }

        // If we have supervisors, try to fetch team interviewers per supervisor (this provides supervisor mapping).
        if ($supervisorListOk && !empty($supervisorUsernameById)) {
            foreach (array_slice(array_keys($supervisorUsernameById), 0, $limit) as $supervisorId) {
                try {
                    $wsForSupervisor = $supervisorWorkspaceById[(string) $supervisorId] ?? $this->workspace;
                    if (is_string($wsForSupervisor) && $wsForSupervisor !== '') {
                        $r = $this->basicGetForWorkspace($wsForSupervisor, "/api/v1/supervisors/{$supervisorId}/interviewers", [
                            'limit' => $limit,
                            'offset' => 1,
                        ]);
                        if (!$r->ok()) {
                            $r = $this->basicGetForWorkspace($wsForSupervisor, "/api/v1/supervisors/{$supervisorId}/interviewers");
                        }
                    } else {
                        // Fallback to current client workspace
                        $r = $this->basicGet("/api/v1/supervisors/{$supervisorId}/interviewers", [
                            'limit' => $limit,
                            'offset' => 1,
                        ], true);
                        if (!$r->ok()) {
                            $r = $this->basicGet("/api/v1/supervisors/{$supervisorId}/interviewers", [], true);
                        }
                    }

                    if ($r->ok()) {
                        $items = $this->extractList($r->json(), ['Users', 'users', 'items', 'interviewers']);
                        $debug['supervisor_interviewers_raw'] += is_array($items) ? count($items) : 0;
                        foreach (collect($items)->take($limit) as $iv) {
                            if (!is_array($iv)) {
                                continue;
                            }
                            $normalized = $this->normalizeUserItem($iv, 'Interviewer');
                            $normalized['supervisor'] = $supervisorUsernameById[(string) $supervisorId] ?? null;
                            $users->push($normalized);
                            $debug['supervisor_interviewers_added']++;
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore per-supervisor failures
                }
            }
        }

        // Also try the general interviewers list endpoint (supported) as a fallback / completeness.
        try {
            $response = $this->basicGet('/api/v1/interviewers', [
                'limit' => $limit,
                'offset' => 0,
            ], true);
            if (!$response->ok()) {
                $response = $this->basicGet('/api/v1/interviewers', [], true);
            }

            $debug['interviewers_list_status'] = $response->status();
            if ($response->ok()) {
                $items = $this->extractList($response->json(), ['Users', 'users', 'items', 'interviewers']);
                $debug['interviewers_list_raw'] = is_array($items) ? count($items) : 0;

                foreach (collect($items)->take($limit) as $iv) {
                    if (!is_array($iv)) {
                        continue;
                    }
                    $users->push($this->normalizeUserItem($iv, 'Interviewer'));
                    $debug['interviewers_list_added']++;
                }
            } else {
                // Not all HQ builds expose the interviewers list endpoint.
                if ($response->status() !== 404) {
                    $warnings[] = "Users: HQ returned HTTP {$response->status()} for GET /api/v1/interviewers";
                }
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $status = optional($e->response)->status();
            if ($status) {
                $debug['interviewers_list_status'] = $status;
                if ($status !== 404) {
                    $warnings[] = "Users: HQ returned HTTP {$status} for GET /api/v1/interviewers";
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return [
            'users' => $users
                ->filter(fn ($u) => !empty(data_get($u, 'username')) || !empty(data_get($u, 'id')))
                ->map(function ($u) {
                    $username = data_get($u, 'username');
                    if (!is_string($username) || trim($username) === '') {
                        $id = data_get($u, 'id');
                        if (is_string($id) && $id !== '') {
                            $u['username'] = $id;
                        }
                    }
                    return $u;
                })
                ->unique(fn ($u) => strtolower((string) (data_get($u, 'username') ?? data_get($u, 'id') ?? '')))
                ->values(),
            'warnings' => array_values(array_unique($warnings)),
            'debug' => $debug,
        ];
    }

    private function normalizeRole(?string $role): ?string
    {
        if (!is_string($role) || $role === '') {
            return null;
        }

        $key = strtolower(trim($role));
        return match ($key) {
            'apiuser', 'api_user', 'api user' => 'ApiUser',
            'headquarter', 'headquarters', 'hq' => 'Headquarter',
            'supervisor' => 'Supervisor',
            'interviewer' => 'Interviewer',
            'observer' => 'Observer',
            'administrator', 'admin' => 'Administrator',
            default => $role,
        };
    }

    private function normalizeUserItem(array $u, ?string $fallbackRole = null): array
    {
        $username = $this->pickLoginName($u);
        $role = data_get($u, 'Role', data_get($u, 'role', data_get($u, 'UserRole', data_get($u, 'userRole', $fallbackRole))));
        $role = $this->normalizeRole(is_string($role) ? $role : null) ?? $fallbackRole;

        return [
            'id' => data_get($u, 'UserId', data_get($u, 'userId', data_get($u, 'Id', data_get($u, 'id')))),
            'username' => $username,
            'role' => $role,
            'name' => data_get($u, 'FullName', data_get($u, 'fullName', data_get($u, 'name', $username))),
            'email' => data_get($u, 'Email', data_get($u, 'email')),
            'phone' => data_get($u, 'PhoneNumber', data_get($u, 'phoneNumber', data_get($u, 'phone'))),
            'workspace' => data_get($u, 'Workspace', data_get($u, 'workspace', data_get($u, 'WorkspaceName', data_get($u, 'workspaceName')))),
            'is_archived' => (bool) data_get($u, 'IsArchived', data_get($u, 'isArchived', false)),
            'is_locked' => (bool) data_get($u, 'IsLocked', data_get($u, 'isLocked', false)),
            'creation_date' => data_get($u, 'CreationDate', data_get($u, 'creationDate')),
        ];
    }

    public function createWorkspace(string $name, string $displayName): array
    {
        // Create workspace is global
        $response = $this->basicPost('/api/v1/workspaces', [
            'Name' => $name,
            'DisplayName' => $displayName,
        ], false);

        $response->throw();
        return is_array($response->json()) ? $response->json() : [];
    }

    /**
     * Update workspace display name.
     */
    public function updateWorkspace(string $name, string $displayName): void
    {
        $response = $this->basicPatch('/api/v1/workspaces/' . ltrim($name, '/'), [
            'DisplayName' => $displayName,
        ], false);
        $response->throw();
    }

    /**
     * Enable a workspace.
     */
    public function enableWorkspace(string $name): void
    {
        $response = $this->basicPost('/api/v1/workspaces/' . ltrim($name, '/') . '/enable', [], false);
        $response->throw();
    }

    /**
     * Disable a workspace.
     */
    public function disableWorkspace(string $name): void
    {
        $response = $this->basicPost('/api/v1/workspaces/' . ltrim($name, '/') . '/disable', [], false);
        $response->throw();
    }

    /**
     * Get workspace status info (global endpoint), useful for counts fallback.
     */
    public function getWorkspaceStatus(string $name): array
    {
        $response = $this->basicGet('/api/v1/workspaces/status/' . ltrim($name, '/'), [], false);
        $response->throw();
        $json = $response->json();
        return is_array($json) ? $json : [];
    }

    /**
     * Creates a user in HQ.
     * Expects payload keys per RegisterUserModel: Role, UserName, Password, FullName, PhoneNumber, Email, Supervisor.
     * Returns created user id if HQ provides one.
     */
    public function createUser(array $payload): ?string
    {
        // Users creation is global
        $response = $this->basicPost('/api/v1/users', $payload, false);
        $response->throw();

        // Some HQ builds return plain text, others JSON.
        $json = $response->json();
        if (is_array($json)) {
            $id = data_get($json, 'id') ?? data_get($json, 'userId') ?? data_get($json, 'UserId');
            return is_string($id) && $id !== '' ? $id : null;
        }

        $body = $response->body();
        $body = is_string($body) ? trim($body) : '';
        return $body !== '' ? $body : null;
    }

    /**
     * Extract a list from a response that might be:
     * - a plain array
     * - an object with known list keys (e.g. items, workspaces, assignments)
     */
    private function extractList(mixed $json, array $preferredKeys): array
    {
        if (is_array($json)) {
            // If associative, try known keys
            $expandedKeys = [];
            foreach ($preferredKeys as $key) {
                $expandedKeys[] = $key;
                // Common casing variants
                $expandedKeys[] = Str::camel($key);
                $expandedKeys[] = Str::studly($key);
                $expandedKeys[] = Str::snake($key);
            }
            $expandedKeys = array_values(array_unique($expandedKeys));

            foreach ($expandedKeys as $key) {
                $candidate = data_get($json, $key);
                if (is_array($candidate)) {
                    return $candidate;
                }
            }

            // If already a list
            $isList = array_keys($json) === range(0, count($json) - 1);
            if ($isList) {
                return $json;
            }
            // Some HQ builds wrap lists under Data/Result.
            foreach (['data', 'Data', 'result', 'Result'] as $wrapperKey) {
                $wrapped = data_get($json, $wrapperKey);
                if (is_array($wrapped)) {
                    if (array_is_list($wrapped)) {
                        return $wrapped;
                    }
                    foreach ($wrapped as $value) {
                        if (is_array($value) && array_is_list($value)) {
                            return $value;
                        }
                    }
                }
            }

            // Heuristic fallback: return the first top-level list we can find.
            if (array_is_list($json)) {
                return $json;
            }
            foreach ($json as $value) {
                if (is_array($value) && array_is_list($value)) {
                    return $value;
                }
            }
        }

        return [];
    }
}
