<?php

namespace App\Http\Requests\Channel;

use App\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed banner
 */
class UpdateSocialsRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'cloob' => 'nullable|url',
            'lenzor' => 'nullable|url',
            'twitter' => 'nullable|url',
            'facebook' => 'nullable|url',
            'telegram' => 'nullable|url',
            'instagram' => 'nullable|url'
        ];
    }
}
