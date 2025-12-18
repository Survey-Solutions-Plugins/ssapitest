<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\HeadquartersApiClient;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ManageController extends Controller
{
    private function clientFromSession(): HeadquartersApiClient
    {
        $hqBaseUrl = session('hq_base_url', session('headquarters_url'));
        $workspace = session('hq_workspace');
        $username = data_get(session('api_credentials'), 'username');
        $password = data_get(session('api_credentials'), 'password');

        $authType = data_get(session('hq_auth'), 'type', 'basic');
        $token = data_get(session('hq_auth'), 'token');

        return new HeadquartersApiClient($hqBaseUrl, $workspace, $username, $password, $authType === 'bearer' ? $token : null);
    }

    public function createWorkspace(Request $request): RedirectResponse
    {
        if (!session('headquarters_url') || !session('api_credentials')) {
            return redirect()->route('home')->with('error', 'Please login to headquarters first');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:12', 'regex:/^[0-9a-z]+$/'],
            'display_name' => ['required', 'string', 'max:300'],
        ]);

        try {
            $client = $this->clientFromSession();
            $client->createWorkspace($validated['name'], $validated['display_name']);

            return redirect()->back()->with('success', 'Workspace created successfully');
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $status = optional($e->response)->status();
            $body = optional($e->response)->body();
            $body = is_string($body) ? mb_substr($body, 0, 500) : '';

            Log::warning('HQ create workspace failed', [
                'status' => $status,
                'body' => $body,
                'url' => session('headquarters_url'),
            ]);

            return redirect()->back()->with('error', "HQ rejected workspace create (HTTP {$status}). {$body}");
        } catch (\Throwable $e) {
            Log::warning('HQ create workspace failed', [
                'error' => $e->getMessage(),
                'url' => session('headquarters_url'),
            ]);

            return redirect()->back()->with('error', 'Unable to create workspace (network/timeout)');
        }
    }

    public function createUser(Request $request): RedirectResponse
    {
        if (!session('headquarters_url') || !session('api_credentials')) {
            return redirect()->route('home')->with('error', 'Please login to headquarters first');
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in(['Supervisor', 'Interviewer', 'Headquarter', 'Observer', 'ApiUser'])],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:1', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'supervisor' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validated['role'] === 'Interviewer' && empty($validated['supervisor'])) {
            return redirect()->back()->with('error', 'Supervisor is required when creating an Interviewer');
        }

        try {
            $client = $this->clientFromSession();
            $createdId = $client->createUser([
                'Role' => $validated['role'],
                'UserName' => $validated['username'],
                'Password' => $validated['password'],
                'FullName' => $validated['full_name'] ?? null,
                'PhoneNumber' => $validated['phone_number'] ?? null,
                'Email' => $validated['email'] ?? null,
                'Supervisor' => $validated['supervisor'] ?? null,
            ]);

            $suffix = $createdId ? " (id: {$createdId})" : '';

            return redirect()->back()->with('success', 'User created successfully' . $suffix);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $status = optional($e->response)->status();
            $body = optional($e->response)->body();
            $body = is_string($body) ? mb_substr($body, 0, 500) : '';

            Log::warning('HQ create user failed', [
                'status' => $status,
                'body' => $body,
                'url' => session('headquarters_url'),
            ]);

            return redirect()->back()->with('error', "HQ rejected user create (HTTP {$status}). {$body}");
        } catch (\Throwable $e) {
            Log::warning('HQ create user failed', [
                'error' => $e->getMessage(),
                'url' => session('headquarters_url'),
            ]);

            return redirect()->back()->with('error', 'Unable to create user (network/timeout)');
        }
    }

    public function updateWorkspace(Request $request, string $name): RedirectResponse
    {
        if (!session('headquarters_url') || !session('api_credentials')) {
            return redirect()->route('home')->with('error', 'Please login to headquarters first');
        }

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:300'],
        ]);

        try {
            $client = $this->clientFromSession();
            $client->updateWorkspace($name, $validated['display_name']);
            return redirect()->back()->with('success', "Workspace '{$name}' updated");
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $status = optional($e->response)->status();
            $body = optional($e->response)->body();
            $body = is_string($body) ? mb_substr($body, 0, 500) : '';
            Log::warning('HQ update workspace failed', [
                'status' => $status,
                'body' => $body,
                'url' => session('headquarters_url'),
            ]);
            return redirect()->back()->with('error', "HQ rejected workspace update (HTTP {$status}). {$body}");
        } catch (\Throwable $e) {
            Log::warning('HQ update workspace failed', [
                'error' => $e->getMessage(),
                'url' => session('headquarters_url'),
            ]);
            return redirect()->back()->with('error', 'Unable to update workspace (network/timeout)');
        }
    }

    public function disableWorkspace(Request $request, string $name): RedirectResponse
    {
        if (!session('headquarters_url') || !session('api_credentials')) {
            return redirect()->route('home')->with('error', 'Please login to headquarters first');
        }
        try {
            $client = $this->clientFromSession();
            $client->disableWorkspace($name);
            return redirect()->back()->with('success', "Workspace '{$name}' disabled");
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $status = optional($e->response)->status();
            $body = optional($e->response)->body();
            $body = is_string($body) ? mb_substr($body, 0, 500) : '';
            Log::warning('HQ disable workspace failed', [
                'status' => $status,
                'body' => $body,
                'url' => session('headquarters_url'),
            ]);
            return redirect()->back()->with('error', "HQ rejected workspace disable (HTTP {$status}). {$body}");
        } catch (\Throwable $e) {
            Log::warning('HQ disable workspace failed', [
                'error' => $e->getMessage(),
                'url' => session('headquarters_url'),
            ]);
            return redirect()->back()->with('error', 'Unable to disable workspace (network/timeout)');
        }
    }

    public function enableWorkspace(Request $request, string $name): RedirectResponse
    {
        if (!session('headquarters_url') || !session('api_credentials')) {
            return redirect()->route('home')->with('error', 'Please login to headquarters first');
        }
        try {
            $client = $this->clientFromSession();
            $client->enableWorkspace($name);
            return redirect()->back()->with('success', "Workspace '{$name}' enabled");
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $status = optional($e->response)->status();
            $body = optional($e->response)->body();
            $body = is_string($body) ? mb_substr($body, 0, 500) : '';
            Log::warning('HQ enable workspace failed', [
                'status' => $status,
                'body' => $body,
                'url' => session('headquarters_url'),
            ]);
            return redirect()->back()->with('error', "HQ rejected workspace enable (HTTP {$status}). {$body}");
        } catch (\Throwable $e) {
            Log::warning('HQ enable workspace failed', [
                'error' => $e->getMessage(),
                'url' => session('headquarters_url'),
            ]);
            return redirect()->back()->with('error', 'Unable to enable workspace (network/timeout)');
        }
    }
}
