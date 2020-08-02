<?php

namespace App\Http\Requests\Video;

use App\Rules\CategoryId;
use App\Rules\OwnPlaylistId;
use App\Rules\UploadedVideoBannerId;
use App\Rules\UploadedVideoId;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

/**
 * @property mixed video_id
 * @property mixed title
 * @property mixed category
 * @property mixed chanel_category
 * @property mixed info
 * @property mixed banner
 * @property mixed publish_at
 * @property mixed playlist
 * @property mixed enable_comments
 * @property mixed enable_watermark
 * @property mixed video
 * @property mixed tags
 * @property mixed channel_category
 */
class UpdateVideoRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('update', $this->video);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'info' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'category' => ['required', new CategoryId(CategoryId::PUBLIC_CATEGORIES)],
            'chanel_category' => ['nullable', new CategoryId(CategoryId::PRIVATE_CATEGORIES)],
            'enable_comments' => 'required|boolean',
            'banner' => ['nullable', new UploadedVideoBannerId()],
        ];
    }
}
