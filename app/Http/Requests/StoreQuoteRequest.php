<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'identification_number' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'date_of_birth' => [
                'required',
                'date',
                'before:today',
                'after:'.now()->subYears(120)->format('Y-m-d'),
            ],
            'destination_country' => ['required', 'string', 'max:150'],
            'destination_country_code' => ['required', 'string', 'size:2'],
            'destination_region' => ['required', 'string', 'max:100'],
            'departure_date' => [
                'required',
                'date',
                'after_or_equal:'.now()->toDateString(),
            ],
            'return_date' => ['required', 'date', 'after:departure_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required' => 'El apellido es obligatorio.',
            'identification_number.required' => 'El número de identificación es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no tiene un formato válido.',
            'date_of_birth.required' => 'La fecha de nacimiento es obligatoria.',
            'date_of_birth.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'date_of_birth.after' => 'La fecha de nacimiento no parece válida.',
            'destination_country.required' => 'El país de destino es obligatorio.',
            'destination_country_code.required' => 'El código del país es obligatorio.',
            'destination_country_code.size' => 'El código del país debe tener 2 caracteres.',
            'destination_region.required' => 'La región del destino es obligatoria.',
            'departure_date.required' => 'La fecha de salida es obligatoria.',
            'departure_date.after_or_equal' => 'La fecha de salida debe ser hoy o una fecha futura.',
            'return_date.required' => 'La fecha de regreso es obligatoria.',
            'return_date.after' => 'La fecha de regreso debe ser posterior a la fecha de salida.',
        ];
    }
}
