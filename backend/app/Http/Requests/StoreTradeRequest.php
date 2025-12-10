<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for creating a new trade.
 */
/**
 * @OA\Schema(
 *      title="Store Trade Request",
 *      description="Store Trade request body data",
 *      type="object",
 *      required={"account_id", "type", "volume", "open_time", "open_price", "status"}
 * )
 */
class StoreTradeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    /**
     * @OA\Property(property="account_id", type="integer", example=1)
     * @OA\Property(property="type", type="string", enum={"BUY", "SELL"}, example="BUY")
     * @OA\Property(property="volume", type="number", format="float", example=1.5)
     * @OA\Property(property="open_time", type="string", format="date-time", example="2023-10-27T10:00:00Z")
     * @OA\Property(property="close_time", type="string", format="date-time", example="2023-10-27T12:00:00Z", nullable=true)
     * @OA\Property(property="open_price", type="number", format="float", example=150.00)
     * @OA\Property(property="close_price", type="number", format="float", example=155.00, nullable=true)
     * @OA\Property(property="status", type="string", enum={"OPEN", "CLOSED"}, example="OPEN")
     */
    public function rules(): array
    {
        return [
            'account_id' => 'required|exists:accounts,id',
            'type' => 'required|in:BUY,SELL',
            'volume' => 'required|numeric|min:0.01',
            'open_time' => 'required|date',
            'close_time' => 'nullable|date|after:open_time',
            'open_price' => 'required|numeric|min:0',
            'close_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:OPEN,CLOSED',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'account_id.required' => 'An account is required for the trade.',
            'account_id.exists' => 'The selected account does not exist.',
            'type.in' => 'Trade type must be either BUY or SELL.',
            'volume.min' => 'Volume must be at least 0.01.',
            'close_time.after' => 'Close time must be after open time.',
            'status.in' => 'Trade status must be either OPEN or CLOSED.',
        ];
    }
}
