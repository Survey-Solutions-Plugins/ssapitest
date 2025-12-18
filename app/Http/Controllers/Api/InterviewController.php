<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Interview;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InterviewController extends Controller
{
    /**
     * GET /api/v1/interviews - Gets list of interviews existing in the system
     */
    public function index(Request $request): JsonResponse
    {
        $query = Interview::with(['assignment', 'questionnaire', 'workspace', 'interviewer', 'supervisor']);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('assignment_id')) {
            $query->where('assignment_id', $request->assignment_id);
        }
        
        if ($request->has('questionnaire_id')) {
            $query->where('questionnaire_id', $request->questionnaire_id);
        }
        
        if ($request->has('workspace_id')) {
            $query->where('workspace_id', $request->workspace_id);
        }
        
        if ($request->has('interviewer_id')) {
            $query->where('interviewer_id', $request->interviewer_id);
        }
        
        $interviews = $query->paginate($request->get('limit', 20));
        return response()->json($interviews);
    }

    /**
     * GET /api/v1/interviews/{id} - Gets all answers for given interview
     */
    public function show(string $id): JsonResponse
    {
        $interview = Interview::with(['assignment', 'questionnaire', 'workspace', 'interviewer', 'supervisor', 'interviewAnswers'])
            ->where('interview_id', $id)
            ->firstOrFail();
        return response()->json($interview);
    }

    /**
     * DELETE /api/v1/interviews/{id} - Deletes interview
     */
    public function destroy(string $id): JsonResponse
    {
        $interview = Interview::where('interview_id', $id)->firstOrFail();
        $interview->delete();
        return response()->json(['message' => 'Interview deleted successfully']);
    }

    /**
     * PATCH /api/v1/interviews/{id}/approve - Approves interview (supervisor)
     */
    public function approve(string $id): JsonResponse
    {
        $interview = Interview::where('interview_id', $id)->firstOrFail();
        $interview->update(['status' => 'approved_by_supervisor']);
        return response()->json(['message' => 'Interview approved by supervisor']);
    }

    /**
     * PATCH /api/v1/interviews/{id}/assign - Assigns interview to interviewer
     */
    public function assign(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'interviewer_id' => 'required|exists:users,id'
        ]);
        
        $interview = Interview::where('interview_id', $id)->firstOrFail();
        $interview->update(['interviewer_id' => $request->interviewer_id]);
        
        return response()->json(['message' => 'Interview assigned to interviewer']);
    }

    /**
     * PATCH /api/v1/interviews/{id}/assignsupervisor - Assigns supervisor
     */
    public function assignSupervisor(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'supervisor_id' => 'required|exists:users,id'
        ]);
        
        $interview = Interview::where('interview_id', $id)->firstOrFail();
        $interview->update([
            'supervisor_id' => $request->supervisor_id,
            'status' => 'supervisor_assigned'
        ]);
        
        return response()->json(['message' => 'Supervisor assigned to interview']);
    }

    /**
     * PATCH /api/v1/interviews/{id}/hqapprove - Approves interview as headquarters
     */
    public function hqApprove(string $id): JsonResponse
    {
        $interview = Interview::where('interview_id', $id)->firstOrFail();
        $interview->update(['status' => 'approved_by_headquarters']);
        return response()->json(['message' => 'Interview approved by headquarters']);
    }

    /**
     * PATCH /api/v1/interviews/{id}/hqreject - Rejects interview as headquarters
     */
    public function hqReject(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'comment' => 'required|string'
        ]);
        
        $interview = Interview::where('interview_id', $id)->firstOrFail();
        $interview->update(['status' => 'rejected_by_headquarters']);
        
        return response()->json(['message' => 'Interview rejected by headquarters']);
    }

    /**
     * PATCH /api/v1/interviews/{id}/reject - Rejects interview (supervisor)
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'comment' => 'required|string'
        ]);
        
        $interview = Interview::where('interview_id', $id)->firstOrFail();
        $interview->update(['status' => 'rejected_by_supervisor']);
        
        return response()->json(['message' => 'Interview rejected by supervisor']);
    }

    /**
     * GET /api/v1/interviews/{id}/stats - Get statistics by interview
     */
    public function stats(string $id): JsonResponse
    {
        $interview = Interview::where('interview_id', $id)->firstOrFail();
        
        $stats = [
            'interview_id' => $interview->interview_id,
            'status' => $interview->status,
            'answers_count' => $interview->interviewAnswers()->count(),
            'has_errors' => $interview->has_errors,
            'errors_count' => $interview->errors_count,
            'created_at' => $interview->created_at,
            'updated_at' => $interview->updated_at
        ];
        
        return response()->json($stats);
    }
}
