<?php

namespace App\Models;

require app_path('Venders/aliyun-php-sdk-core/Config.php');
use DefaultAcsClient;
use DefaultProfile;
use Push\Request\V20150827 as Push;

class Pusher
{
    public static function send($aliyuntokens, $title, $body, $summary, $extra, $is_all)
    {

        // 设置你的AccessKeyId/AccessSecret/AppKey
        $accessKeyId  = "6fAjg6qCuIl4xlBy";
        $accessSecret = "9KZTLx4iwpNNdw4xGclBlrhOl6HUhm";
        $appKey       = 23358809;

        $iClientProfile = DefaultProfile::getProfile("cn-hangzhou", $accessKeyId, $accessSecret);

        $client = new DefaultAcsClient($iClientProfile);

        $request = new Push\PushRequest();


        // 推送目标
        $request->setAppKey($appKey);
        if ($is_all) {
            $request->setTarget("all"); //推送目标: device:推送给设备; account:推送给指定帐号,tag:推送给自定义标签; all: 推送给全部
            $request->setTargetValue("all"); //根据Target来设定，如Target=device, 则对应的值为 设备id1,设备id2. 多个值使用逗号分隔.(帐号与设备有一次最多100个的限制)
        } else {
            $request->setTarget("device"); //推送目标: device:推送给设备; account:推送给指定帐号,tag:推送给自定义标签; all: 推送给全部
            $request->setTargetValue($aliyuntokens); //根据Target来设定，如Target=device, 则对应的值为 设备id1,设备id2. 多个值使用逗号分隔.(帐号与设备有一次最多100个的限制)
        }

        #$request->setTargetValue("all"); //根据Target来设定，如Target=device, 则对应的值为 设备id1,设备id2. 多个值使用逗号分隔.(帐号与设备有一次最多100个的限制)

        $request->setDeviceType(3); // 设备类型deviceType 取值范围为:0~3. iOS设备: 0; Android设备: 1; 全部: 3, 这是默认值.


        // 推送配置
        $request->setType(1); // 0:表示消息(默认为0), 1:表示通知
        $request->setTitle($title); // 消息的标题
        $request->setBody($body); // 消息的内容
        $request->setSummary($summary); // 通知的摘要
        $request->setStoreOffline('true');
        // 推送配置: iOS
        //$request->setiOSBadge("5"); // iOS应用图标右上角角标
        //$request->setiOSMusic("default"); // iOS通知声音
        $request->setiOSExtParameters($extra); //自定义的kv结构,开发者扩展用 针对iOS设备
        //$request->setApnsEnv("DEV");
        $request->setRemind("true"); // 当APP不在线时候，是否通过通知提醒


        // 推送配置: Android
        //$request->setAndroidOpenType("3"); // 点击通知后动作,1:打开应用 2: 打开应用Activity 3:打开 url
        //$request->setAndroidOpenUrl("http://www.baidu.com"); // Android收到推送后打开对应的url,仅仅当androidOpenType=3有效
        $request->setAndroidExtParameters($extra); // 设定android类型设备通知的扩展属性


        $response = $client->getAcsResponse($request);
        return $response;

    }
}

