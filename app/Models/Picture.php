<?php

namespace App\Models;

use Common;
use Illuminate\Database\Eloquent\Model;
use Log;

require_once base_path() . '/vendor/aliyuncs/oss-sdk-php/autoload.php';
require_once base_path() . '/vendor/aliyuncs/oss-sdk-php/samples/Common.php';


class Picture extends Model
{
    protected $fillable = ["is_startup","url", "blog_id", "profile_id","jump_url"];

    public function toArray()
    {
        $array['url'] = $this->url;
        $array['jump_url']= $this->jump_url;
        return $array;
    }

    public static function upload($folder, $file)
    {
        $bucket    = Common::getBucketName();
        $ossClient = Common::getOssClient();
        list($filename, $ext) = explode(".", $file->getClientOriginalName());
        $filename = mt_rand() . "." . $ext;
        $ossClient->uploadFile($bucket, $folder . "/" . $filename, $file->getRealPath());
        $url = $ossClient->signUrl($bucket, $folder . "/" . $filename, 3600);
        Log::info($url);
        return explode("?", $url)[0];
    }

    public static function is_from_ios($url)
    {
        $url = explode("/", $url);

        $last = count($url);

        if (strpos($url[$last - 1], 'jpg') === false) {
            return true;
        }
        return false;
    }

    public static function convert_pic($url)
    {
        $old = $url;
        $url = explode("/", $url);

        $last = count($url);

        if (strpos($url[$last - 1], 'jpg') === false) {
            return $old;
        }

        $rep = "nanzhu.img-cn-shanghai.aliyuncs.com";

        $new_url = $url[0] . "//" . $rep;

        foreach ($url as $key => $u) {
            if ($key > 2) {
                $new_url .= "/" . $u;
            }
        }

        return $new_url;
    }
}
