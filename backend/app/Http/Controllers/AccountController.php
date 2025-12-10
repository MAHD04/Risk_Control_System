<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/accounts",
     *      operationId="getAccountsList",
     *      tags={"Accounts"},
     *      summary="List all accounts",
     *      description="Returns list of accounts",
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
        $perPage = $request->input('per_page', 10);
        $accounts = Account::withCount(['trades', 'incidents'])->paginate($perPage);

        return response()->json($accounts);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/accounts/{id}",
     *      operationId="getAccountById",
     *      tags={"Accounts"},
     *      summary="Get account information",
     *      description="Returns account data with risk status",
     *      @OA\Parameter(
     *          name="id",
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
    public function show(Account $account): JsonResponse
    {
        $account->loadCount(['trades', 'incidents']);

        return response()->json([
            'success' => true,
            'data' => [
                'account' => $account,
                'risk_status' => [
                    'is_enabled' => $account->isEnabled(),
                    'is_trading_enabled' => $account->isTradingEnabled(),
                    'total_incidents' => $account->incidents_count,
                ],
            ],
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/accounts",
     *      operationId="storeAccount",
     *      tags={"Accounts"},
     *      summary="Create new account",
     *      description="Returns account data",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/StoreAccountRequest")
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = Account::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => $account,
        ], 201);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/accounts/{id}/restore",
     *      operationId="restoreAccount",
     *      tags={"Accounts"},
     *      summary="Restore disabled account",
     *      description="Re-enables a disabled account",
     *      @OA\Parameter(
     *          name="id",
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
    public function restore(Account $account): JsonResponse
    {
        $account->update([
            'status' => 'enable',
            'trading_status' => 'enable',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Account restored successfully',
            'data' => $account->fresh(),
        ]);
    }
}
