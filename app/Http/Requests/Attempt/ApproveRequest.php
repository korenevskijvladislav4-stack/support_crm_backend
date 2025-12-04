<?php

namespace App\Http\Requests\Attempt;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Team;

class ApproveRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'name' => 'string|required',
            'surname' => 'string|required',
            'email' => 'string|required',
            'phone' => 'nullable|string|max:20',
            'team_id' => 'required|integer|exists:teams,id',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'group_id' => 'required|integer|exists:groups,id',
            'schedule_type_id' => 'required|integer|exists:schedule_types,id',
            'start_date' => 'required|date|after_or_equal:today',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $teamId = $this->input('team_id');
            $roles = $this->input('roles', []);

            if ($teamId && !empty($roles)) {
                $team = Team::with('roles')->find($teamId);
                if ($team) {
                    $teamRoleIds = $team->roles->pluck('id')->toArray();
                    $invalidRoles = array_diff($roles, $teamRoleIds);
                    
                    if (!empty($invalidRoles)) {
                        $validator->errors()->add(
                            'roles',
                            'Выбранные роли не принадлежат выбранному отделу.'
                        );
                    }
                }
            }
        });
    }
}
