<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for updating an existing risk rule.
 */
/**
 * @OA\Schema(
 *      title="Update Risk Rule Request",
 *      description="Update Risk Rule request body data",
 *      type="object"
 * )
 */
class UpdateRiskRuleRequest extends FormRequest
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
     * @OA\Property(property="name", type="string", example="Max Daily Loss Updated")
     * @OA\Property(property="description", type="string", example="Updated description")
     * @OA\Property(property="severity", type="string", enum={"HARD", "SOFT"}, example="SOFT")
     * @OA\Property(property="incident_limit", type="integer", example=5)
     * @OA\Property(property="is_active", type="boolean", example=false)
     * @OA\Property(property="parameters", type="object", example={"min_duration_seconds": 120})
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'severity' => 'sometimes|required|string|in:HARD,SOFT',
            'incident_limit' => 'sometimes|required|integer|min:1',
            'is_active' => 'boolean',
            'parameters' => 'nullable|array',
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
            'name.max' => 'Rule name cannot exceed 255 characters.',
            'rule_type.in' => 'Invalid rule type. Must be: min_duration, volume_consistency, or trade_frequency.',
            'severity.in' => 'Severity must be either HARD or SOFT.',
            'incident_limit.min' => 'Incident limit must be at least 1.',
        ];
    }
}
