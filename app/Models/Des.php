<?php

namespace App\Models;


class Des
{

    public static function userToken($user_id)
    {
        $url   = "http://auth.nanzhuxinyu.com/auth/TrimUserIdToUserToken?sUserId=" . $user_id;
        $token = Des::fetch_data($url);
        if ($token) {
            return $token->token;
        }
    }

    public static function tokenToUserId($token)
    {
        $url    = "http://auth.nanzhuxinyu.com/auth/CheckUserTokenServlet?sUserToken=" . $token;
        $userid = Des::fetch_data($url);
        if ($userid) {
            \Log::info($userid->userid);
            return $userid->userid;
        }
    }

    public static function fetch_data($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $result   = json_decode($response);
        return $result;
    }
}
