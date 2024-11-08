<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\RiskSituation;
use App\Models\Institution;
use Illuminate\Http\Request;
use App\Models\UserReport;
use App\Http\Requests\StoreUserReportRequest;
use App\Http\Requests\UpdateUserReportRequest;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class UserReportController extends Controller
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
    public function index(Request $request, Institution $institution, RiskSituation $riskSituation, Incident $incident)
    {
        $reports = $incident->userReports();
        $query = $request->query();

        if (isset($query['state'])) {
            $reports->where('state', $query['state']);
        }

        $perPage = isset($query['per_page'])  && $query['per_page'] > 0 ? $query['per_page'] : $reports->count();
        $reports = $reports->orderBy('updated_at', 'desc')->paginate($perPage)->withQueryString();

        return response()->json([
            'data' => $reports->load(['user']),
            'pagination' => [
                'total' => $reports->total(),
                'per_page' => $reports->perPage(),
                'current_page' => $reports->currentPage(),
                'total_pages' => $reports->lastPage(),
                'last_page' => $reports->lastPage(),
                'next_page_url' => $reports->nextPageUrl(),
                'prev_page_url' => $reports->previousPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserReportRequest $request, Institution $institution, RiskSituation $riskSituation, Incident $incident)
    {
        // Revisar que el usuario autenticato no tega ya un reporte en el incidente
        $userReport = UserReport::where('user_id', Auth::id())->where('incident_id', $incident->id)->first();

        if ($userReport) {
            return response()->json(['message' => __('messages.already_reported')], 400);
        }

        $request['user_id'] = Auth::id();
        $request['incident_id'] = $incident->id;

        $report = UserReport::create($request->all());
        return response()->json(['data' => $report->load(['user'])], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Institution $institution, RiskSituation $riskSituation, Incident $incident, UserReport $userReport)
    {
        return response()->json(['data' => $userReport->load(['user'])]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserReportRequest $request, Institution $institution, RiskSituation $riskSituation, Incident $incident, UserReport $userReport)
    {
        $userReport->update($request->all());
        return response()->json(['data' => $userReport->load(['user'])]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Institution $institution, RiskSituation $riskSituation, Incident $incident, UserReport $userReport)
    {
        try {
            $userReport->delete();
            return response()->json(['message' => __('messages.deleted', ['Model' => __('incident')])]);
        } catch (\Exception $e) {

            $resources = [];

            if ($userReport->userReportResolutions->count() > 0) {
                $resources[] = __('user report resolutions');
            }

            $resourceList = implode(', ', $resources);

            return response()->json(['message' => __('messages.cannot_delete', ['Model' => __('incident'), 'resources' => $resourceList])], 400);
        }
    }
}
