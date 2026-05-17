<?php

namespace App\Modules\Projects\Http\Requests;

use App\Modules\Projects\Domain\Models\Task;
use App\Modules\Shared\Http\Requests\AuthenticatedFormRequest;
use Closure;

class UpdateTimeEntryRequest extends AuthenticatedFormRequest
{
    public function rules(): array
    {
        return [
            'worked_on' => ['sometimes', 'required', 'date'],
            'minutes' => ['sometimes', 'required', 'integer', 'min:1', function (string $attr, mixed $value, Closure $fail) {
                if ((int) $value % 15 !== 0) {
                    $fail('Els minuts han de ser múltiples de 15.');
                }
            }],
            'description' => ['nullable', 'string', 'max:500'],
            'task_id' => ['nullable', 'integer', function (string $attr, mixed $value, Closure $fail) {
                $projectId = (int) $this->route('project')->id;
                $belongs = Task::query()->whereKey($value)->where('project_id', $projectId)->exists();
                if (! $belongs) {
                    $fail('La tasca no pertany a aquest projecte.');
                }
            }],
        ];
    }
}
