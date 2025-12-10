<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for updating an existing trade.
 */
/**
 * @OA\Schema(
 *      title="Update Trade Request",
 *      description="Update Trade request body data",
 *      type="object"
 * )
 */
class UpdateTradeRequest extends FormRequest
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
     * @OA\Property(property="close_time", type="string", format="date-time", example="2023-10-27T12:00:00Z")
     * @OA\Property(property="close_price", type="number", format="float", example=155.00)
     * @OA\Property(property="status", type="string", enum={"OPEN", "CLOSED"}, example="CLOSED")
     */
    public function rules(): array
    {
        return [
            'close_time' => 'sometimes|required|date|after:open_time',
            'close_price' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:OPEN,CLOSED',
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
            'close_price.min' => 'Close price cannot be negative.',
            'status.in' => 'Trade status must be either OPEN or CLOSED.',
        ];
    }
}
