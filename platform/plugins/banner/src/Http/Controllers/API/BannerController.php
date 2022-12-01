<?php

namespace Botble\Banner\Http\Controllers\API;

use Botble\Banner\Http\Resources\BannerResource;
use Botble\Banner\Models\Banner;
use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Banner\Http\Requests\BannerRequest;
use Botble\Banner\Repositories\Interfaces\BannerInterface;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Exception;
use Botble\Banner\Tables\BannerTable;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Banner\Forms\BannerForm;
use Botble\Base\Forms\FormBuilder;
use Illuminate\Support\Facades\Validator;

class BannerController extends BaseController
{

    public function index(Request $request, BaseHttpResponse $response)
    {
        $validate = Validator::make($request->all(), [
            "position" => "required|in:az-guide,slider",
        ]);

        if ($validate->fails())
            return $response->setError()->setMessage(processValidators($validate->errors()->toArray()));

        $data = Banner::where("status", "published")
            ->where("position", $request->input("position"))
            ->orderBy("order", "DESC")
            ->take($request->input("length", 3))->get();

        return $response->setData(BannerResource::collection($data));
    }
}
