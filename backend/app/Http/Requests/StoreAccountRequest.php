<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for creating a new account.
 */
/**
 * @OA\Schema(
 *      title="Store Account Request",
 *      description="Store Account request body data",
 *      type="object",
 *      required={"login"}
 * )
 */
class StoreAccountRequest extends FormRequest
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
     * @OA\Property(property="login", type="integer", example=123456)
     * @OA\Property(property="status", type="string", enum={"enable", "disable"}, example="enable")
     * @OA\Property(property="trading_status", type="string", enum={"enable", "disable"}, example="enable")
     */
    public function rules(): array
    {
        return [
            'login' => 'required|integer|unique:accounts,login',
            'status' => 'in:enable,disable',
            'trading_status' => 'in:enable,disable',
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
            'login.required' => 'A login ID is required.',
            'login.integer' => 'Login must be a numeric ID.',
            'login.unique' => 'This login ID already exists.',
            'status.in' => 'Status must be either enable or disable.',
            'trading_status.in' => 'Trading status must be either enable or disable.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->status ?? 'enable',
            'trading_status' => $this->trading_status ?? 'enable',
        ]);
    }
}
