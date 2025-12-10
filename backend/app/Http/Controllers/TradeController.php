<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTradeRequest;
use App\Http\Requests\UpdateTradeRequest;
use App\Models\Trade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/trades",
     *      operationId="getTradesList",
     *      tags={"Trades"},
     *      summary="Get list of trades",
     *      description="Returns list of trades with optional filtering",
     *      @OA\Parameter(
     *          name="account_id",
     *          description="Filter by Account ID",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="Filter by Status (OPEN/CLOSED)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(type="string")
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
        $query = Trade::with('account');

        // Filter by account
        if ($request->has('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', strtoupper($request->status));
        }

        $perPage = $request->input('per_page', 10);
        $trades = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($trades);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/trades/{id}",
     *      operationId="getTradeById",
     *      tags={"Trades"},
     *      summary="Get trade information",
     *      description="Returns trade data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Trade ID",
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
    public function show(Trade $trade): JsonResponse
    {
        $trade->load(['account', 'incidents']);

        return response()->json([
            'success' => true,
            'data' => $trade,
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/trades",
     *      operationId="storeTrade",
     *      tags={"Trades"},
     *      summary="Store new trade",
     *      description="Returns trade data",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreTradeRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(StoreTradeRequest $request): JsonResponse
    {
        $trade = Trade::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Trade created successfully. Risk evaluation triggered.',
            'data' => $trade->load('account'),
        ], 201);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/trades/{id}",
     *      operationId="updateTrade",
     *      tags={"Trades"},
     *      summary="Update existing trade",
     *      description="Returns updated trade data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Trade ID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/UpdateTradeRequest")
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
    public function update(UpdateTradeRequest $request, Trade $trade): JsonResponse
    {
        $trade->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Trade updated successfully. Risk evaluation triggered.',
            'data' => $trade->fresh(['account']),
        ]);
    }
}
