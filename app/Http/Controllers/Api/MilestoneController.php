<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMilestoneRequest;
use App\Http\Requests\UpdateMilestoneRequest;
use App\Http\Resources\MilestoneResource;
use App\Models\Milestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
            $cacheKey = 'milestones';
            if ($request->has('favorite')) {
                $cacheKey .= '.favorite';

                $milestones = Cache::remember($cacheKey, 3600, function () {
                    return Milestone::where('favorite', true)->get();
                });

                return MilestoneResource::collection($milestones);
            } else {
                $milestones = Cache::remember($cacheKey, 3600, function () {
                    return Milestone::all();
                });

                return MilestoneResource::collection($milestones);
            }
        }
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    /**
     * Display the specified resource.
     */
    public function show($milestone)
    {
        // Manually find the milestone using the ID from the route ($milestone).
        $milestoneInstance = Cache::remember('milestone.'.$milestone, 3600, function () use ($milestone) {
            return Milestone::findOrFail($milestone);
        });

        // Check if the user is authorized to view the milestone.
        if (request()->user('api')->can('view', $milestoneInstance)) {
            return new MilestoneResource($milestoneInstance);
        } else {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMilestoneRequest $request)
    {
        $milestone = Milestone::create($request->validated());

        // Update the cache with the new milestone
        $this->updateMilestoneInCache($milestone);

        return new MilestoneResource($milestone);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMilestoneRequest $request, $milestone)
    {
        // Manually find the milestone using the ID from the route ($milestone).
        $milestoneInstance = Milestone::findOrFail($milestone);

        $milestoneInstance->update($request->validated());
        $milestoneInstance = $milestoneInstance->fresh();

        // Update the milestone in cache
        $this->updateMilestoneInCache($milestoneInstance);

        return new MilestoneResource($milestoneInstance);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($milestone)
    {
        $milestoneInstance = Milestone::findOrFail($milestone);

        // Check if the user is authorized to delete the milestone.
        if (request()->user('api')->can('delete', $milestoneInstance)) {
            // Remove from cache before deleting
            $this->removeMilestoneFromCache($milestoneInstance);

            $milestoneInstance->delete();

            return response()->json(['message' => 'Milestone deleted successfully']);
        } else {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * Update a milestone in all relevant caches
     */
    private function updateMilestoneInCache(Milestone $milestone)
    {
        // Update single milestone cache
        Cache::put('milestone.' . $milestone->id, $milestone, 3600);

        // Update milestone in collection caches
        $this->updateMilestoneInCollectionCache($milestone);
    }

    /**
     * Remove a milestone from all relevant caches
     */
    private function removeMilestoneFromCache(Milestone $milestone)
    {
        // Remove from individual milestone cache
        Cache::forget('milestone.' . $milestone->id);

        // Update collection caches to remove this milestone
        $this->updateMilestoneInCollectionCache($milestone, true);
    }

    /**
     * Update milestone in collection caches (or remove it if $remove is true)
     */
    private function updateMilestoneInCollectionCache(Milestone $milestone, bool $remove = false)
    {
        $cacheKeys = ['milestones'];

        // Add favorite key if the milestone is or was a favorite
        if ($milestone->favorite) {
            $cacheKeys[] = 'milestones.favorite';
        }

        foreach ($cacheKeys as $cacheKey) {
            if (Cache::has($cacheKey)) {
                $cachedMilestones = Cache::get($cacheKey);

                if ($remove) {
                    // Remove milestone from collection
                    $updatedMilestones = $cachedMilestones->filter(function ($cachedMilestone) use ($milestone) {
                        return $cachedMilestone->id !== $milestone->id;
                    })->values();
                } else {
                    // Update or add milestone to collection
                    $milestoneExists = false;

                    $updatedMilestones = $cachedMilestones->map(function ($cachedMilestone) use ($milestone, &$milestoneExists) {
                        if ($cachedMilestone->id === $milestone->id) {
                            $milestoneExists = true;
                            return $milestone;
                        }
                        return $cachedMilestone;
                    });

                    // If milestone wasn't in the collection, add it (if applicable to the cache key)
                    if (!$milestoneExists && $this->milestoneBelongsInCache($milestone, $cacheKey)) {
                        $updatedMilestones->push($milestone);
                    }
                }

                // Put back the updated collection in cache
                Cache::put($cacheKey, $updatedMilestones, 3600);
            }
        }
    }

    /**
     * Determine if a milestone should be included in a particular cache
     */
    private function milestoneBelongsInCache(Milestone $milestone, string $cacheKey): bool
    {
        // All milestones belong in the main milestones cache
        if ($cacheKey === 'milestones') {
            return true;
        }

        // Only favorite milestones belong in the favorites cache
        if ($cacheKey === 'milestones.favorite') {
            return $milestone->favorite;
        }

        return false;
    }
}
