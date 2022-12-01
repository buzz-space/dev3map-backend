<?php

namespace Botble\Setting\Http\Controllers\API;

use Assets;
use BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Setting\Models\Setting;
use Botble\Base\Supports\Core;
use Botble\Base\Supports\Language;
use Botble\Media\Repositories\Interfaces\MediaFileInterface;
use Botble\Setting\Http\Requests\EmailTemplateRequest;
use Botble\Setting\Http\Requests\LicenseSettingRequest;
use Botble\Setting\Http\Requests\MediaSettingRequest;
use Botble\Setting\Http\Requests\ResetEmailTemplateRequest;
use Botble\Setting\Http\Requests\SendTestEmailRequest;
use Botble\Setting\Http\Requests\SettingRequest;
use Botble\Setting\Repositories\Interfaces\SettingInterface;
use Carbon\Carbon;
use EmailHandler;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use RvMedia;
use Throwable;



class SettingController extends BaseController
{
    public function getSetting(Request $request, BaseHttpResponse $response)
    {
        // $value = !empty(setting($key)) ? setting($key) : null;
        // if ($key == "popup_banner")
        //     $value = !empty(setting($key)) ? RvMedia::url(setting($key)) : null;

        $arr = explode(",",$request->input('key'));
        $data= new \stdClass();
        foreach($arr as $key){
            $data->$key = !empty(setting($key)) ? setting($key) : null;
            if ($key == "popup_banner")
                $data->$key = !empty(setting($key)) ? RvMedia::url(setting($key)) : null;
        }

        return $response->setData($data);
    }

}
