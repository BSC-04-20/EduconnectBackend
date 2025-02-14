<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LectureRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            "fullname" => ["bail", "required"],
            "phonenumber" => ['bail', "required", "min:10", "unique:lectures,phonenumber"],
            "email" => ['bail', 'required','email:rfc,dns', 'unique:lectures,email'],
            "password" => ['bail', 'required']
        ];
    }

    /**
     * Custom error messages for validation.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            "fullname.required" => "Full name is required. Please provide your full name.",
            "phonenumber.required" => "Phone number is required. Please enter a valid phone number.",
            "phonenumber.min" => "Phone number must be at least 10 digits long.",
            "phonenumber.unique" => "This phone number is already registered. Please use a different one.",
            "email.required" => "Email address is required. Please enter your email.",
            "email.unique" => "This email is already taken. Please try another one.",
            "password.required" => "Password is required. Please enter a strong password.",
        ];
    }

    /**
     * Customize the failed validation response to return JSON.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422));
    }
}
