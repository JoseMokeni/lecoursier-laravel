<?php

namespace App\Http\Requests;

use App\Models\Milestone; // Import the Milestone model
use Illuminate\Foundation\Http\FormRequest;

class UpdateMilestoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Get the milestone ID from the route parameter.
        $milestoneId = $this->route('milestone');

        // Find the milestone within the current tenant's context.
        $milestone = Milestone::find($milestoneId);

        // If the milestone doesn't exist in this tenant's scope, deny access.
        if (!$milestone) {
            return false;
        }

        // Check if the authenticated user can update this specific milestone instance.
        return $this->user('api')->can('update', $milestone);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'longitudinal' => 'sometimes|required|string|max:255',
            'latitudinal' => 'sometimes|required|string|max:255',
            'favorite' => 'sometimes|boolean',
        ];
    }
}
