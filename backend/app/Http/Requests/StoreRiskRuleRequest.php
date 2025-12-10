<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for creating a new risk rule.
 */
/**
 * @OA\Schema(
 *      title="Store Risk Rule Request",
 *      description="Store Risk Rule request body data",
 *      type="object",
 *      required={"name", "rule_type", "severity", "incident_limit"}
 * )
 */
class StoreRiskRuleRequest extends FormRequest
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
     * @OA\Property(property="name", type="string", example="Max Daily Loss")
     * @OA\Property(property="description", type="string", example="Limits daily loss to $500")
     * @OA\Property(property="rule_type", type="string", enum={"min_duration", "volume_consistency", "trade_frequency"}, example="min_duration")
     * @OA\Property(property="severity", type="string", enum={"HARD", "SOFT"}, example="HARD")
     * @OA\Property(property="incident_limit", type="integer", example=3)
     * @OA\Property(property="is_active", type="boolean", example=true)
     * @OA\Property(property="parameters", type="object", example={"min_duration_seconds": 60})
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'rule_type' => 'required|string|in:min_duration,volume_consistency,trade_frequency',
            'severity' => 'required|string|in:HARD,SOFT',
            'incident_limit' => 'required|integer|min:1',
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
            'name.required' => 'A name is required for the risk rule.',
            'name.max' => 'Rule name cannot exceed 255 characters.',
            'rule_type.required' => 'A rule type is required.',
            'rule_type.in' => 'Invalid rule type. Must be: min_duration, volume_consistency, or trade_frequency.',
            'severity.required' => 'Severity level is required.',
            'severity.in' => 'Severity must be either HARD or SOFT.',
            'incident_limit.min' => 'Incident limit must be at least 1.',
        ];
    }
}
