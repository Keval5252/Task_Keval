<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class CustomEmailVerificationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'required|exists:users,id',
            'hash' => 'required|string',
        ];
    }

    public function validateResolved()
    {
        $user = User::find($this->route('id'));
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['This email verification link is invalid.'],
            ]);
        }

        $this->user = $user;
    }

    public function fulfill()
    {
        $this->user->markEmailAsVerified();
    }
}
