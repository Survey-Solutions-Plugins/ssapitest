<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AssignmentController extends Controller
{
    /**
     * GET /api/v1/assignments - List all assignments with filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = Assignment::with(['questionnaire', 'workspace', 'responsible']);
        
        if ($request->has('workspace_id')) {
            $query->where('workspace_id', $request->workspace_id);
        }
        
        if ($request->has('questionnaire_id')) {
            $query->where('questionnaire_id', $request->questionnaire_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('responsible_id')) {
            $query->where('responsible_id', $request->responsible_id);
        }
        
        $assignments = $query->paginate($request->get('limit', 20));
        return response()->json($assignments);
    }

    /**
     * POST /api/v1/assignments - Create new assignment
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'questionnaire_id' => 'required|exists:questionnaires,id',
            'workspace_id' => 'required|exists:workspaces,id',
            'responsible_id' => 'nullable|exists:users,id',
            'identifying_data' => 'nullable|array',
            'quantity' => 'nullable|integer|min:1',
            'audio_recording' => 'boolean'
        ]);

        $assignment = Assignment::create($request->all());
        return response()->json($assignment->load(['questionnaire', 'workspace', 'responsible']), 201);
    }

    /**
     * GET /api/v1/assignments/{id} - Single assignment details
     */
    public function show(string $id): JsonResponse
    {
        $assignment = Assignment::with(['questionnaire', 'workspace', 'responsible', 'interviews'])->findOrFail($id);
        return response()->json($assignment);
    }

    /**
     * PATCH /api/v1/assignments/{id}/archive - Archive assignment
     */
    public function archive(string $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);
        $assignment->update(['archived' => true, 'status' => 'archived']);
        return response()->json(['message' => 'Assignment archived successfully']);
    }

    /**
     * PATCH /api/v1/assignments/{id}/assign - Assign new responsible person
     */
    public function assign(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'responsible_id' => 'required|exists:users,id'
        ]);
        
        $assignment = Assignment::findOrFail($id);
        $assignment->update([
            'responsible_id' => $request->responsible_id,
            'status' => 'assigned'
        ]);
        
        return response()->json(['message' => 'Assignment responsibility changed successfully']);
    }

    /**
     * PATCH /api/v1/assignments/{id}/changeQuantity - Change assignment quantity
     */
    public function changeQuantity(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);
        
        $assignment = Assignment::findOrFail($id);
        
        if ($assignment->interviews_count > $request->quantity) {
            return response()->json([
                'error' => 'Cannot set quantity lower than existing interviews count'
            ], 400);
        }
        
        $assignment->update(['quantity' => $request->quantity]);
        return response()->json(['message' => 'Assignment quantity updated successfully']);
    }

    /**
     * PATCH /api/v1/assignments/{id}/close - Close assignment
     */
    public function close(string $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);
        $assignment->update([
            'quantity' => $assignment->interviews_count,
            'status' => 'completed'
        ]);
        
        return response()->json(['message' => 'Assignment closed successfully']);
    }

    /**
     * GET /api/v1/assignments/{id}/recordAudio - Get audio recording status
     */
    public function getRecordAudio(string $id): JsonResponse
    {
        $assignment = Assignment::findOrFail($id);
        return response()->json(['audio_recording' => $assignment->audio_recording]);
    }

    /**
     * PATCH /api/v1/assignments/{id}/recordAudio - Set audio recording
     */
    public function setRecordAudio(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'audio_recording' => 'required|boolean'
        ]);
        
        $assignment = Assignment::findOrFail($id);
        $assignment->update(['audio_recording' => $request->audio_recording]);
        
        return response()->json(['message' => 'Audio recording setting updated successfully']);
    }
}
