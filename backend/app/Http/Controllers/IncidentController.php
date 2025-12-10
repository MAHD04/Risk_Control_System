<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/incidents",
     *      operationId="getIncidentsList",
     *      tags={"Incidents"},
     *      summary="Get list of incidents",
     *      description="Returns list of incidents with optional filtering",
     *      @OA\Parameter(
     *          name="account_id",
     *          description="Filter by Account ID",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="risk_rule_id",
     *          description="Filter by Risk Rule ID",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Incident::with(['account', 'riskRule', 'trade']);

        // Filter by account
        if ($request->has('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by rule
        if ($request->has('risk_rule_id')) {
            $query->where('risk_rule_id', $request->risk_rule_id);
        }

        // Order by most recent first
        $perPage = $request->input('per_page', 10);
        $incidents = $query->orderBy('triggered_at', 'desc')->paginate($perPage);

        return response()->json($incidents);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/incidents/{id}",
     *      operationId="getIncidentById",
     *      tags={"Incidents"},
     *      summary="Get incident information",
     *      description="Returns incident data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Incident ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function show(Incident $incident): JsonResponse
    {
        $incident->load(['account', 'riskRule', 'trade']);

        return response()->json([
            'success' => true,
            'data' => $incident,
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/incidents/account/{accountId}/stats",
     *      operationId="getAccountIncidentStats",
     *      tags={"Incidents"},
     *      summary="Get incident statistics for an account",
     *      description="Returns incident counts and breakdown by rule",
     *      @OA\Parameter(
     *          name="accountId",
     *          description="Account ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function accountStats(int $accountId): JsonResponse
    {
        $totalIncidents = Incident::where('account_id', $accountId)->count();

        $incidentsByRule = Incident::where('account_id', $accountId)
            ->selectRaw('risk_rule_id, count(*) as count')
            ->groupBy('risk_rule_id')
            ->with('riskRule:id,name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'account_id' => $accountId,
                'total_incidents' => $totalIncidents,
                'incidents_by_rule' => $incidentsByRule,
            ],
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/incidents/notifications/unread",
     *      operationId="getUnreadNotifications",
     *      tags={"Notifications"},
     *      summary="Get unread incidents",
     *      description="Returns list of unread incidents",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       )
     * )
     */
    public function unread(): JsonResponse
    {
        $unreadIncidents = Incident::with(['account', 'riskRule'])
            ->whereNull('read_at')
            ->orderBy('triggered_at', 'desc')
            ->limit(10)
            ->get();

        $unreadCount = Incident::whereNull('read_at')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $unreadCount,
                'notifications' => $unreadIncidents,
            ],
        ]);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/incidents/{id}/read",
     *      operationId="markNotificationAsRead",
     *      tags={"Notifications"},
     *      summary="Mark incident as read",
     *      description="Updates read_at timestamp",
     *      @OA\Parameter(
     *          name="id",
     *          description="Incident ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function markAsRead(Incident $incident): JsonResponse
    {
        $incident->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/incidents/notifications/read-all",
     *      operationId="markAllNotificationsAsRead",
     *      tags={"Notifications"},
     *      summary="Mark all incidents as read",
     *      description="Updates read_at timestamp for all unread incidents",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       )
     * )
     */
    public function markAllAsRead(): JsonResponse
    {
        Incident::whereNull('read_at')->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }
}
