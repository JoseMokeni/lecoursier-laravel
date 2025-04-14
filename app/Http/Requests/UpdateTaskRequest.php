<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $taskId = $this->route('task');

        // Find the task within the current tenant's context.
        $task = Task::find($taskId);

        // If the task doesn't exist in this tenant's scope, deny access.
        if (!$task) {
            return false;
        }
        // Check if the authenticated user can update this specific task instance.
        return $this->user('api')->can('update', $task);
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('completedAt')) {
            $data['completed_at'] = $this->completedAt;
        }

        // Convert camelCase fields to snake_case for database storage
        if ($this->has('dueDate')) {
            $data['due_date'] = $this->dueDate;
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => 'sometimes|required|string',
            'priority' => 'sometimes|required|in:low,medium,high',
            'status' => 'sometimes|required|in:pending,in_progress,completed',
            'completed_at' => 'sometimes|required|date',
            'due_date' => 'sometimes|date',
        ];
    }
}
