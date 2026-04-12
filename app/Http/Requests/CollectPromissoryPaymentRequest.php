<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class CollectPromissoryPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization will be handled in controller based on college/org context
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'student_id' => 'required|integer|exists:students,id',
            'promissory_note_id' => 'required|integer|exists:promissory_notes,id',
            'selected_fees' => 'required|array|min:1',
            'selected_fees.*' => 'integer|exists:fees,id',
            'cash_received' => 'required|numeric|min:0.01',
            'note' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'student_id.required' => 'Student ID is required.',
            'student_id.exists' => 'Selected student does not exist.',
            'promissory_note_id.required' => 'Promissory note ID is required.',
            'promissory_note_id.exists' => 'Selected promissory note does not exist.',
            'selected_fees.required' => 'At least one fee must be selected for settlement.',
            'selected_fees.min' => 'At least one fee must be selected.',
            'selected_fees.*.exists' => 'One or more selected fees do not exist.',
            'cash_received.required' => 'Cash received amount is required.',
            'cash_received.numeric' => 'Cash received must be a valid amount.',
            'cash_received.min' => 'Cash received must be greater than zero.',
            'note.max' => 'Notes cannot exceed 500 characters.',
        ];
    }
}
