<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkspaceController extends Controller
{
    /**
     * GET /api/v1/workspaces - List existing workspaces
     */
    public function index(): JsonResponse
    {
        $workspaces = Workspace::all();
        return response()->json($workspaces);
    }

    /**
     * POST /api/v1/workspaces - Creates new workspace (Administrator only)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|unique:workspaces,name',
            'display_name' => 'required|string',
            'description' => 'nullable|string'
        ]);

        $workspace = Workspace::create($request->all());
        return response()->json($workspace, 201);
    }

    /**
     * GET /api/v1/workspaces/{name} - Get single workspace details
     */
    public function show(string $name): JsonResponse
    {
        $workspace = Workspace::where('name', $name)->firstOrFail();
        return response()->json($workspace);
    }

    /**
     * PATCH /api/v1/workspaces/{name} - Updates workspace
     */
    public function update(Request $request, string $name): JsonResponse
    {
        $workspace = Workspace::where('name', $name)->firstOrFail();
        
        $request->validate([
            'display_name' => 'sometimes|string',
            'description' => 'sometimes|nullable|string'
        ]);

        $workspace->update($request->all());
        return response()->json($workspace);
    }

    /**
     * DELETE /api/v1/workspaces/{name} - Delete workspace
     */
    public function destroy(string $name): JsonResponse
    {
        $workspace = Workspace::where('name', $name)->firstOrFail();
        $workspace->delete();
        return response()->json(['message' => 'Workspace deleted successfully']);
    }

    /**
     * GET /api/v1/workspaces/status/{name} - Get workspace status
     */
    public function status(string $name): JsonResponse
    {
        $workspace = Workspace::where('name', $name)->firstOrFail();
        $canDelete = $workspace->assignments()->count() === 0 && $workspace->interviews()->count() === 0;
        
        return response()->json([
            'name' => $workspace->name,
            'can_delete' => $canDelete,
            'disabled' => $workspace->disabled
        ]);
    }

    /**
     * POST /api/v1/workspaces/{name}/disable - Disable workspace
     */
    public function disable(string $name): JsonResponse
    {
        $workspace = Workspace::where('name', $name)->firstOrFail();
        $workspace->update(['disabled' => true]);
        return response()->json(['message' => 'Workspace disabled successfully']);
    }

    /**
     * POST /api/v1/workspaces/{name}/enable - Enable workspace
     */
    public function enable(string $name): JsonResponse
    {
        $workspace = Workspace::where('name', $name)->firstOrFail();
        $workspace->update(['disabled' => false]);
        return response()->json(['message' => 'Workspace enabled successfully']);
    }
}
