<?php

namespace App\Http\Requests\Channel;

use App\Rules\ChannelName;
use App\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property mixed email
 * @property mixed info
 * @property mixed name
 * @property mixed website
 */
class UpdateChannelRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // for check Access level
        // while parameter id exist and user not admin => not authorize
        if ($this->route()->hasParameter('id') && auth()->user()->type != User::TYPE_ADMIN) {
            return false;
        }
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
            'name' => ['required', new ChannelName()],
            'website' => 'nullable|url|string|max:255',
            'info' => 'nullable|string',
        ];
    }
}
