<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('api')->can('create', Task::class);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->userId ?? $this->user_id,
            'milestone_id' => $this->milestoneId ?? $this->milestone_id,
            'due_date' => $this->dueDate ?? $this->due_date,
            'status' => $this->status ?? 'pending', // Set default status if not provided
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'sometimes|required|in:low,medium,high',
            'status' => 'sometimes|required|in:pending,in_progress,completed',
            'due_date' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'milestone_id' => 'required|exists:milestones,id',
        ];
    }
}
