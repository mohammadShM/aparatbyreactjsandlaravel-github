<?php

namespace App\Http\Requests\Video;

use App\Video;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

/**
 * @property mixed video
 * @property mixed like
 * @property mixed user
 * @property mixed from_date
 * @property mixed last_n_days
 */
class FavouritesVideoListRequest extends FormRequest
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
        ];
    }
}

