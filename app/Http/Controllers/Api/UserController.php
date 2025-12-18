<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Supervisor;
use App\Models\Interviewer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * POST /api/v1/users - Creates new user with specified role
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:administrator,headquarters,supervisor,interviewer',
            'phone' => 'nullable|string',
            'workspace_id' => 'required_if:role,supervisor,interviewer|exists:workspaces,id',
            'supervisor_id' => 'required_if:role,interviewer|exists:supervisors,id'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone
        ]);

        // Create role-specific records
        if ($request->role === 'supervisor') {
            Supervisor::create([
                'user_id' => $user->id,
                'workspace_id' => $request->workspace_id
            ]);
        } elseif ($request->role === 'interviewer') {
            Interviewer::create([
                'user_id' => $user->id,
                'supervisor_id' => $request->supervisor_id,
                'workspace_id' => $request->workspace_id
            ]);
        }

        return response()->json($user->load(['supervisor', 'interviewer']), 201);
    }

    /**
     * GET /api/v1/users/{id} - Gets detailed info about single user
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with(['supervisor', 'interviewer'])->findOrFail($id);
        return response()->json($user);
    }

    /**
     * GET /api/v1/supervisors - Gets list of supervisors
     */
    public function supervisors(Request $request): JsonResponse
    {
        $query = User::where('role', 'supervisor')->with(['supervisor.workspace']);
        
        if ($request->has('workspace_id')) {
            $query->whereHas('supervisor', function($q) use ($request) {
                $q->where('workspace_id', $request->workspace_id);
            });
        }
        
        $supervisors = $query->get();
        return response()->json($supervisors);
    }

    /**
     * GET /api/v1/supervisors/{id} - Gets detailed info about single supervisor
     */
    public function supervisor(string $id): JsonResponse
    {
        $supervisor = User::where('role', 'supervisor')
            ->with(['supervisor.workspace', 'supervisor.interviewers.user'])
            ->findOrFail($id);
        return response()->json($supervisor);
    }

    /**
     * GET /api/v1/supervisors/{supervisorId}/interviewers - Gets interviewers in supervisor team
     */
    public function supervisorInterviewers(string $supervisorId): JsonResponse
    {
        $supervisor = Supervisor::with(['interviewers.user'])->findOrFail($supervisorId);
        return response()->json($supervisor->interviewers);
    }

    /**
     * GET /api/v1/interviewers - Gets list of interviewers
     */
    public function interviewers(Request $request): JsonResponse
    {
        $query = User::where('role', 'interviewer')->with(['interviewer.supervisor.user', 'interviewer.workspace']);
        
        if ($request->has('supervisor_id')) {
            $query->whereHas('interviewer', function($q) use ($request) {
                $q->where('supervisor_id', $request->supervisor_id);
            });
        }
        
        if ($request->has('workspace_id')) {
            $query->whereHas('interviewer', function($q) use ($request) {
                $q->where('workspace_id', $request->workspace_id);
            });
        }
        
        $interviewers = $query->get();
        return response()->json($interviewers);
    }

    /**
     * GET /api/v1/interviewers/{id} - Gets detailed info about single interviewer
     */
    public function interviewer(string $id): JsonResponse
    {
        $interviewer = User::where('role', 'interviewer')
            ->with(['interviewer.supervisor.user', 'interviewer.workspace'])
            ->findOrFail($id);
        return response()->json($interviewer);
    }

    /**
     * PATCH /api/v1/users/{id}/archive - Archives user
     */
    public function archive(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['is_archived' => true]);
        return response()->json(['message' => 'User archived successfully']);
    }

    /**
     * PATCH /api/v1/users/{id}/unarchive - Unarchives user
     */
    public function unarchive(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['is_archived' => false]);
        return response()->json(['message' => 'User unarchived successfully']);
    }
}
