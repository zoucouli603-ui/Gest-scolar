<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SyncNotesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'notes' => ['required', 'array', 'min:1', 'max:100'],
            'notes.*' => [
                'required',
                'array:class_room_id,student_id,subject_id,evaluation_type,score,trimester,evaluation_month,note_date,coefficient',
            ],
            'notes.*.class_room_id' => ['required', 'integer', 'min:1'],
            'notes.*.student_id' => ['required', 'integer', 'min:1'],
            'notes.*.subject_id' => ['required', 'integer', 'min:1'],
            'notes.*.evaluation_type' => [
                'required',
                Rule::in(['composition', 'devoir', 'controle', 'examen_trimestriel']),
            ],
            'notes.*.score' => ['required', 'numeric', 'min:0'],
            'notes.*.trimester' => ['nullable', 'integer', 'between:1,3'],
            'notes.*.evaluation_month' => ['nullable', 'date_format:Y-m'],
            'notes.*.note_date' => ['nullable', 'date'],
            'notes.*.coefficient' => ['nullable', 'numeric', 'min:0.5', 'max:10'],
        ];
    }
}
