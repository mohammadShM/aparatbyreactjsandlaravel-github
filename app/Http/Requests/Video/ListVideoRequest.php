<?php

namespace App\Http\Requests\Video;

use App\Rules\CanChangeVideoState;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed slug
 * @property mixed video
 * @property mixed state
 * @property mixed republished
 */
class ListVideoRequest extends FormRequest
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
            'republished' => 'nullable|boolean'
        ];
    }
}
