<?php

use Botble\Base\Facades\DashboardMenuFacade;
use Botble\Base\Facades\PageTitleFacade;
use Botble\Base\Supports\DashboardMenu;
use Botble\Base\Supports\Editor;
use Botble\Base\Supports\PageTitle;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;

if (!function_exists('anchor_link')) {
    /**
     * @param string|null $link
     * @param string|null $name
     * @param array $options
     * @return string
     * @deprecated
     */
    function anchor_link(?string $link, ?string $name, array $options = []): string
    {
        return Html::link($link, $name, $options);
    }
}

if (!function_exists('language_flag')) {
    /**
     * @param string $flag
     * @param string|null $name
     * @return string
     */
    function language_flag(string $flag, ?string $name = null): string
    {
        return Html::image(asset(BASE_LANGUAGE_FLAG_PATH . $flag . '.svg'), $name, ['title' => $name, 'width' => 16]);
    }
}

if (!function_exists('render_editor')) {
    /**
     * @param string $name
     * @param string|null $value
     * @param bool $withShortCode
     * @param array $attributes
     * @return string
     * @throws Throwable
     */
    function render_editor(string $name, ?string $value = null, bool $withShortCode = false, array $attributes = []): string
    {
        return (new Editor())->render($name, $value, $withShortCode, $attributes);
    }
}

if (!function_exists('is_in_admin')) {
    /**
     * @param bool $force
     * @return bool
     */
    function is_in_admin(bool $force = false): bool
    {
        $prefix = BaseHelper::getAdminPrefix();

        $segments = array_slice(request()->segments(), 0, count(explode('/', $prefix)));

        $isInAdmin = implode('/', $segments) === $prefix;

        return $force ? $isInAdmin : apply_filters(IS_IN_ADMIN_FILTER, $isInAdmin);
    }
}

if (!function_exists('page_title')) {
    /**
     * @return PageTitle
     */
    function page_title(): PageTitle
    {
        return PageTitleFacade::getFacadeRoot();
    }
}

if (!function_exists('dashboard_menu')) {
    /**
     * @return DashboardMenu
     */
    function dashboard_menu(): DashboardMenu
    {
        return DashboardMenuFacade::getFacadeRoot();
    }
}

if (!function_exists('get_cms_version')) {
    /**
     * @return string
     */
    function get_cms_version(): string
    {
        $version = '...';

        try {
            $core = BaseHelper::getFileData(core_path('core.json'));

            return Arr::get($core, 'version', $version);
        } catch (Exception $exception) {
            return $version;
        }
    }
}

if (!function_exists('platform_path')) {
    /**
     * @param string|null $path
     * @return string
     */
    function platform_path(?string $path = null): string
    {
        return base_path('platform/' . $path);
    }
}

if (!function_exists('core_path')) {
    /**
     * @param string|null $path
     * @return string
     */
    function core_path(?string $path = null): string
    {
        return platform_path('core/' . $path);
    }
}

if (!function_exists('package_path')) {
    /**
     * @param string|null $path
     * @return string
     */
    function package_path(?string $path = null): string
    {
        return platform_path('packages/' . $path);
    }
}

function generateUrl($url = "/")
{
    if (env("APP_ENV") == "local")
        return url($url);

    return secure_url($url);
}

function generateAsset($asset = "/")
{
    if (env("APP_ENV") == "local")
        return asset($asset);

    return secure_asset($asset);
}

function processValidators($validationArray = [], $type = 'string')
{
    $arrMsg = [];

    foreach ($validationArray as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $item) {
                $arrMsg[] = $item;
            }
        }
    }

    if ($type == 'string')
        $arrMsg = implode(' ', $arrMsg);

    return $arrMsg;
}

if (!function_exists('getDBSizeInMB')) {

    function getDBSizeInMB()
    {
        $result = DB::select(DB::raw('SELECT table_name AS "Table",
                ((data_length + index_length) / 1024 / 1024) AS "Size"
                FROM information_schema.TABLES
                WHERE table_schema ="' . env("DB_DATABASE") . '"'));
        //    $db_size = number_format((float)$size, 2, '.', '');
        return array_sum(array_column($result, 'Size'));

    }
}

function utf8convert($str)
{

    if (!$str) return "";

    $utf8 = array(

        'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ|Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',

        'd' => 'đ|Đ',

        'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ|É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',

        'i' => 'í|ì|ỉ|ĩ|ị|Í|Ì|Ỉ|Ĩ|Ị',

        'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ|Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',

        'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự|Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',

        'y' => 'ý|ỳ|ỷ|ỹ|ỵ|Ý|Ỳ|Ỷ|Ỹ|Ỵ',

    );

    foreach ($utf8 as $ascii => $uni) $str = preg_replace("/($uni)/i", $ascii, $str);
    $str = str_replace(" ", "-", $str);
    $str = str_replace("----", "-", $str);
    $str = str_replace("---", "-", $str);
    $str = str_replace("--", "-", $str);
    $str = str_replace("-", "-", $str);
    $str = strtolower($str);

    return $str;

}

function limitText($text, $char = 15){
    $length = strlen($text);

    $arr = explode(" ", $text);
    $res = [];
    for ($i = 0; $i < count($arr); $i++){
        if (isset($arr[$i]) && strlen($arr[$i]) <= 10)
            $res[] = $arr[$i];
        if (strlen(implode(" ", $res)) == $length)
            break;
        if (strlen(implode(" ", $res)) > $char){
            $res[] = " ...";
            break;
        }
    }

    return implode(" ", $res);
}

function selectPage($page){
    Paginator::currentPageResolver(function () use ($page) {
        return $page;
    });
}
function bodyRequestToQueryString($data)
{
    $queryString = [];
    foreach ($data as $key => $value) {
        if (is_array($value))
            $queryString[] = $key . "=" . implode(",", $value);
        else
            $queryString[] = $key . "=" . $value;
    }

    return implode("&", $queryString);
}
function getIp(){
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
        if (array_key_exists($key, $_SERVER) === true){
            foreach (explode(',', $_SERVER[$key]) as $ip){
                $ip = trim($ip); // just to be safe
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                    return $ip;
                }
            }
        }
    }
    return request()->ip(); // it will return server ip when no client ip found
}
//function saveImageFromUrl($url){
//    $img = Image::make($url)->resize(320, 480);
//
//    $path = "storage/user/image_". now()->format('H_i_s') . ".png";
//
//    fopen(public_path($path), "w+");
//
//    $img->save(public_path($path));
//
//    return $path;
//}
