<?php

namespace App\Services;

use App\Http\Requests\Tag\CreateTagRequest;
use App\Http\Requests\Tag\ListTagRequest;
use App\Tag;
use Illuminate\Database\Eloquent\Collection;

class TagService extends BaseService
{

    /**
     * @param ListTagRequest $request
     * @return Tag[]|Collection
     */
    public static function index(ListTagRequest $request)
    {
        // for one way ===============
//        return Tag::select('id', 'title')->get();
        // for two way ===============
//        $tags = Tag::all('id', 'title');
//        $result = [];
//        $tags->each(function ($tag) use (&$result) {
//            $result[$tag->id] = $tag->title;
//        });
//        return $result;
        // for three way ===============
//        return Tag::pluck('id', 'title');
        // for four way ===============
        // return Tag::all('id', 'title');
        return Tag::all();
    }

    public static function create(CreateTagRequest $request)
    {
        $data = $request->validated();
        /** @var Tag $tag */
        $tag = Tag::create($data);
//        return [
//            'id' => $tag->id,
//            'title' => $tag->title
//        ];
        return $tag;
    }

}
