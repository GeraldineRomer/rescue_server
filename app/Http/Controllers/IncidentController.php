<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\RiskSituation;
use App\Models\Institution;
use Illuminate\Http\Request;
use App\Http\Requests\StoreIncidentRequest;
use App\Http\Requests\UpdateIncidentRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class IncidentController extends Controller
{
    /**
     * Create a new controller instance.
     * It will be used to define the middleware that will be applied to the controller methods.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only(['store']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Institution $institution, RiskSituation $riskSituation)
    {
        $incidents = $riskSituation->incidents();
        $query = $request->query();

        if (isset($query['initial_date']) && isset($query['final_date'])) {
            $incidents->whereBetween('created_at', [$query['initial_date'], $query['final_date']]);
        }

        if (isset($query['initial_date'])) {
            $incidents->where('created_at', '>=', $query['initial_date']);
        }

        if (isset($query['final_date'])) {
            $incidents->where('created_at', '<=', $query['final_date']);
        }

        $perPage = isset($query['per_page'])  && $query['per_page'] > 0 ? $query['per_page'] : $incidents->count();
        $incidents = $incidents->orderBy('updated_at', 'desc')->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $incidents->load(['riskSituation', 'informer', 'userReports', 'userReports.user', 'brigadiers', 'brigadiers.brigadier', 'brigadiers.meetPoint']),
            'pagination' => [
                'total' => $incidents->total(),
                'per_page' => $incidents->perPage(),
                'current_page' => $incidents->currentPage(),
                'total_pages' => $incidents->lastPage(),
                'last_page' => $incidents->lastPage(),
                'next_page_url' => $incidents->nextPageUrl(),
                'prev_page_url' => $incidents->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIncidentRequest $request, Institution $institution, RiskSituation $riskSituation)
    {
        $activeIncident = $institution->activeIncidents()->first();

        if ($activeIncident) {
            return response()->json(['message' => __('messages.active_incident')], 400);
        }

        $request['initial_date'] = now();
        $request['risk_situation_id'] = $riskSituation->id;
        $request['informer_id'] = Auth::id();
        $incident = Incident::create($request->all());

        return response()->json(['data' => $incident->load(['riskSituation', 'informer']), 'message' =>  __('messages.created', ['Model' => __('incident')])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Institution $institution, RiskSituation $riskSituation, Incident $incident)
    {
        return response()->json(['data' => $incident->load(['riskSituation', 'informer', 'userReports', 'userReports.user', 'brigadiers', 'brigadiers.brigadier', 'brigadiers.meetPoint'])]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIncidentRequest $request, Institution $institution, RiskSituation $riskSituation, Incident $incident)
    {
        if ($incident->risk_situation_id !== $riskSituation->id) {
            return response()->json(['message' => __('messages.not_found_in_risk_situation', ['Model' => __('incident')])], 404);
        }

        if (!$incident->final_date) {
            $request['final_date'] = now();
        }

        $incident->update($request->all());
        return response()->json(['data' => $incident->load(['riskSituation', 'informer']), 'message' => __('messages.updated', ['Model' => __('incident')])]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Institution $institution, RiskSituation $riskSituation, Incident $incident)
    {
        try {
            $incident->delete();
            return response()->json(['message' => __('messages.deleted', ['Model' => __('incident')])]);
        } catch (\Exception $e) {

            $resources = [];

            if ($incident->userReports->count() > 0) {
                $resources[] = __('user reports');
            }

            if ($incident->brigadiers->count() > 0) {
                $resources[] = __('brigadiers');
            }

            if ($incident->meetPoints->count() > 0) {
                $resources[] = __('meet points');
            }

            $resourceList = implode(', ', $resources);
            return response()->json(['message' => __('messages.cannot_delete', ['Model' => __('incident'), 'resources' => $resourceList])], 400);
        }
    }
}
