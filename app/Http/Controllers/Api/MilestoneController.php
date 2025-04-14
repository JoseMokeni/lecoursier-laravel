<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMilestoneRequest;
use App\Http\Requests\UpdateMilestoneRequest;
use App\Models\Milestone;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class MilestoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $authorized = $request->user('api')->can('viewAny', Milestone::class);
        if ($authorized) {
            // Check if favorite parameter exists in the request
            if ($request->has('favorite')) {
                $milestones = Milestone::where('favorite', true)->get();
            } else {
                $milestones = Milestone::all();
            }
            return response()->json($milestones);
        }
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMilestoneRequest $request)
    {
        $milestone = Milestone::create($request->validated());
        return response()->json($milestone, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMilestoneRequest $request, $milestone)
    {
        // Manually find the milestone using the ID from the route ($milestone).
        $milestoneInstance = Milestone::findOrFail($milestone);

        $milestoneInstance->update($request->validated());
        return response()->json($milestoneInstance);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($milestone)
    {
        $milestoneInstance = Milestone::findOrFail($milestone);

        // Check if the user is authorized to delete the milestone.
        if (request()->user('api')->can('delete', $milestoneInstance)) {
            $milestoneInstance->delete();
            return response()->json(['message' => 'Milestone deleted successfully']);
        } else {
            throw new AccessDeniedHttpException();
        }
    }
}
