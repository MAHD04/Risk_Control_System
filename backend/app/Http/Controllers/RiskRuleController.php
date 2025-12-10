<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachActionsRequest;
use App\Http\Requests\StoreRiskRuleRequest;
use App\Http\Requests\UpdateRiskRuleRequest;
use App\Models\RiskRule;
use App\Models\ConfiguredAction;
use Illuminate\Http\JsonResponse;

class RiskRuleController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/risk-rules",
     *      operationId="getRiskRulesList",
     *      tags={"Risk Rules"},
     *      summary="Get list of risk rules",
     *      description="Returns list of risk rules",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(): JsonResponse
    {
        $rules = RiskRule::with('actions')->get();

        return response()->json([
            'success' => true,
            'data' => $rules,
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/risk-rules/{id}",
     *      operationId="getRiskRuleById",
     *      tags={"Risk Rules"},
     *      summary="Get risk rule information",
     *      description="Returns risk rule data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Risk Rule ID",
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
    public function show(RiskRule $riskRule): JsonResponse
    {
        $riskRule->load('actions');

        return response()->json([
            'success' => true,
            'data' => $riskRule,
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/risk-rules",
     *      operationId="storeRiskRule",
     *      tags={"Risk Rules"},
     *      summary="Store new risk rule",
     *      description="Returns risk rule data",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreRiskRuleRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(StoreRiskRuleRequest $request): JsonResponse
    {
        $rule = RiskRule::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Risk rule created successfully',
            'data' => $rule,
        ], 201);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/risk-rules/{id}",
     *      operationId="updateRiskRule",
     *      tags={"Risk Rules"},
     *      summary="Update existing risk rule",
     *      description="Returns updated risk rule data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Risk Rule ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/UpdateRiskRuleRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function update(UpdateRiskRuleRequest $request, RiskRule $riskRule): JsonResponse
    {
        $riskRule->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Risk rule updated successfully',
            'data' => $riskRule->fresh(),
        ]);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/risk-rules/{id}",
     *      operationId="deleteRiskRule",
     *      tags={"Risk Rules"},
     *      summary="Delete existing risk rule",
     *      description="Deletes a record and returns no content",
     *      @OA\Parameter(
     *          name="id",
     *          description="Risk Rule ID",
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
    public function destroy(RiskRule $riskRule): JsonResponse
    {
        $riskRule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Risk rule deleted successfully',
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/risk-rules/{id}/actions",
     *      operationId="attachActionsToRiskRule",
     *      tags={"Risk Rules"},
     *      summary="Attach actions to risk rule",
     *      description="Returns risk rule data with attached actions",
     *      @OA\Parameter(
     *          name="id",
     *          description="Risk Rule ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/AttachActionsRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function attachActions(AttachActionsRequest $request, RiskRule $riskRule): JsonResponse
    {
        $riskRule->actions()->sync($request->validated()['action_ids']);

        return response()->json([
            'success' => true,
            'message' => 'Actions attached successfully',
            'data' => $riskRule->load('actions'),
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/risk-rules/actions",
     *      operationId="getAvailableActions",
     *      tags={"Risk Rules"},
     *      summary="Get list of available actions",
     *      description="Returns list of configured actions",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function listActions(): JsonResponse
    {
        $actions = ConfiguredAction::all();

        return response()->json([
            'success' => true,
            'data' => $actions,
        ]);
    }
}
