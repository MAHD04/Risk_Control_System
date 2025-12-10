<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for attaching actions to a risk rule.
 */
/**
 * @OA\Schema(
 *      title="Attach Actions Request",
 *      description="Attach Actions request body data",
 *      type="object",
 *      required={"action_ids"}
 * )
 */
class AttachActionsRequest extends FormRequest
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
     * @OA\Property(
     *     property="action_ids",
     *     type="array",
     *     @OA\Items(type="integer"),
     *     example={1, 2}
     * )
     */
    public function rules(): array
    {
        return [
            'action_ids' => 'required|array',
            'action_ids.*' => 'exists:configured_actions,id',
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
            'action_ids.required' => 'At least one action must be specified.',
            'action_ids.array' => 'Action IDs must be provided as an array.',
            'action_ids.*.exists' => 'One or more selected actions do not exist.',
        ];
    }
}
