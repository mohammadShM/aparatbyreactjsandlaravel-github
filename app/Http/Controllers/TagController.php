<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\ListCategoryRequest;
use App\Http\Requests\Tag\CreateTagRequest;
use App\Http\Requests\Tag\ListTagRequest;
use App\Services\TagService;
use Illuminate\Routing\Controller as BaseController;

class TagController extends BaseController
{
    public function index(ListTagRequest $request)
    {
      return TagService::index($request);
    }

    public function create(CreateTagRequest $request)
    {
        return TagService::create($request);
    }

}
