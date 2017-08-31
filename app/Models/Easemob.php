<?php
namespace App\Models;

use Curl\Curl;

class Easemob
{
    const URL          = 'https://a1.easemob.com';
    const GROUP_PREFIX = 'nanzhu_group_';

    const REQUEST_METHOD_GET    = 'GET';
    const REQUEST_METHOD_POST   = 'POST';
    const REQUEST_METHOD_PUT    = 'PUT';
    const REQUEST_METHOD_DELETE = 'DELETE';

    const MSG_TYPE_PASS_THROUGH = 'cmd'; //透传消息类型

    const SEND_SCOPE_USERS  = 'users';   //发送聊天范围
    const SEND_SCOPE_GROUPS = 'chatgroups'; //发给群组
    const SEND_SCOPE_ROOMS  = 'chatrooms'; //发给聊天室

    private $client_id;
    private $client_secret;
    private $org_name;
    private $app_name;
    private $url;

    /**
     * 初始化环形参数
     *
     * @param array $options
     * @param       $options ['client_id']
     * @param       $options ['client_secret']
     * @param       $options ['org_name']
     * @param       $options ['app_name']
     */
    public function __construct($options)
    {
        $paramsMap = array(
            'client_id',
            'client_secret',
            'org_name',
            'app_name'
        );
        foreach ($paramsMap as $paramsName) {
            if (!isset($options[$paramsName])) {
                throw new \InvalidArgumentException("初始化未设置[{$paramsName}]");
            } else {
                $this->$paramsName = $options[$paramsName];
            }
        }
        $this->url = self::URL . '/' . $this->org_name . '/' . $this->app_name;
    }

    /**
     * 创建新用户[授权模式]
     *
     * @param $username
     * @param $password
     *
     * @return mixed
     * @throws \ErrorException
     */
    public function userAuthorizedRegister($username, $password)
    {
        $url = $this->url . '/users';
        return $this->contact($url, array(
            'username' => $username,
            'password' => $password
        ));
    }

    /**
     * 查看用户是否在线
     *
     * @param $username
     *
     * @return bool
     * @throws \ErrorException
     */
    public function userOnline($username)
    {
        $url = $this->url . '/users/' . $username . '/status';
        $res = $this->contact($url, '', 'GET');
        if (isset($res['data'])) {
            if (isset($res['data'][$username])) {
                return ($res['data'][$username] === 'online');
            }
        }
        return false;
    }

    /**
     * 强制已经登陆的用户退出登录
     *
     * @param $userName
     *
     * @return mixed
     */
    public function forceUserDisconnect($userName)
    {
        $url  = $this->url . '/users/'.$userName.'/disconnect';

        return $this->contact($url,'',self::REQUEST_METHOD_GET);
    }

    /**
     * 用户添加好友
     *
     * @param $ownerUserName
     * @param $friendUserName
     *
     * @return mixed
     */
    public function userAddFriend($ownerUserName, $friendUserName)
    {
        $url = $this->url . '/users/' . $ownerUserName . '/contacts/users/' . $friendUserName;

        return $this->contact($url, '', self::REQUEST_METHOD_POST);
    }

    /**
     * 用户删除好友
     *
     * @param $ownerUserName
     * @param $friendUserName
     *
     * @return mixed
     */
    public function userDeleteFriend($ownerUserName, $friendUserName)
    {
        $url = $this->url . '/users/' . $ownerUserName . '/contacts/users/' . $friendUserName;

        return $this->contact($url, '', self::REQUEST_METHOD_DELETE);
    }

    /**
     * 获取环信用户信息
     *
     * @param $userName
     *
     * @return mixed
     */
    public function getUserInfo($userName)
    {
        $url = $this->url . '/users/' . $userName;

        return $this->contact($url, '', self::REQUEST_METHOD_GET);
    }

    /**
     * 向群组中加一个人
     *
     * @param $groupId
     * @param $username
     *
     * @return mixed
     * @throws \ErrorException
     */
    public function groupAddUser($groupId, $username)
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/users/' . $username;
        return $this->contact($url);
    }

    /**
     * 向群组中添加多个用户
     *
     * @param       $groupId
     * @param array $userNames
     *
     * @return mixed
     */
    public function groupAddUsers($groupId, $userNames = [])
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/users';

        return $this->contact($url, ['usernames' => $userNames]);
    }

    /**
     * 从群组中删除用户
     *
     * @param int    $groupId
     * @param string $userName
     *
     * @return mixed
     */
    public function groupRemoveUser($groupId, $userName)
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/users/' . $userName;

        return $this->contact($url, '', self::REQUEST_METHOD_DELETE);
    }

    /**
     * 从群组中移除多个用户
     *
     * @param       $groupId
     * @param array $userNames
     *
     * @return mixed
     */
    public function groupRemoveUsers($groupId, $userNames = [])
    {
        $needToRemoveUserNames = implode(',', $userNames);

        $url = $this->url . '/chatgroups/' . $groupId . '/users/' . $needToRemoveUserNames;

        return $this->contact($url, '', self::REQUEST_METHOD_DELETE);
    }

    /**
     * 删除一个用户
     *
     * @param $username
     *
     * @return mixed
     * @throws \ErrorException
     */
    public function userDelete($username)
    {
        $url = $this->url . '/users/' . $username;

        return $this->contact($url, '', self::REQUEST_METHOD_DELETE);
    }

    /**
     * @param string|array $groupId 发给群ID
     * @param string       $from    谁发的
     * @param array        $options
     * @param              $options ['mixed'] 是否需要将ext的内容同时发送到txt里 环信的webim不支持接受ext 故加入此功能
     * @param              $options ['msg'] 消息内容
     * @param              $options ['ext'] 扩展消息内容
     *
     * @return mixed
     */
    public function sendToGroups($groupId, $from, $options)
    {
        return $this->sendMessage($from, $groupId, $options, 'chatgroups');
    }

    /**
     * @param string|array $username 发给谁
     * @param string       $from     谁发的
     * @param array        $options
     * @param              $options  ['mixed'] 是否需要将ext的内容同时发送到txt里 环信的webim不支持接受ext 故加入此功能
     * @param              $options  ['msg'] 消息内容
     * @param              $options  ['ext'] 扩展消息内容
     *
     * @return mixed
     */
    public function sendToUsers($username, $from, $options)
    {
        return $this->sendMessage($from, $username, $options);
    }

    /**
     * @param string       $from        谁发的
     * @param string|array $to          发给谁,人或群
     * @param array        $options
     * @param              $options     ['mixed'] 是否需要将ext的内容同时发送到txt里 环信的webim不支持接受ext 故加入此功能
     * @param              $options     ['msg'] 消息内容
     * @param              $options     ['ext'] 扩展消息内容
     * @param string       $target_type 群还是人
     *
     * @return mixed
     * @throws \ErrorException
     */
    private function sendMessage($from, $to, $options, $target_type = 'users')
    {
        $data = array(
            'target_type' => $target_type,
            'target'      => is_array($to) ? $to : array($to),
            'from'        => $from,
        );
        if (isset($options['mixed'])) {
            $data['msg'] = array(
                'type' => 'txt',
                'msg'  => json_encode($options['ext'])
            );
        }
        if (isset($options['msg'])) {
            $data['msg'] = array(
                'type' => 'txt',
                'msg'  => json_encode($options['msg'])
            );
        }
        if (isset($options['ext'])) {
            $data['ext'] = $options['ext'];
        }
        $url = $this->url . '/messages';
        return $this->contact($url, $data);
    }

    /**
     * 发送透传消息
     *
     * @param $from
     * @param $to
     * @param $msg
     *
     * @return mixed
     *
     */
    public function sendPassthroughMsgToGroup($from, $to, $msg)
    {
        $data = [
            "target_type" => self::SEND_SCOPE_GROUPS,
            'target'      => is_array($to) ? $to : [$to],
            'msg'         => [
                'type'   => self::MSG_TYPE_PASS_THROUGH,
                'action' => $msg
            ],
            'from' => $from
        ];

        $url  = $this->url . '/messages';

        return $this->contact($url,$data);
    }

    /**
     * 获取token
     * @return bool
     * @throws \ErrorException
     */
    private function getToken()
    {
        $token = $this->cacheToken();
        if ($token) {
            return $token;
        } else {
            $option ['grant_type']    = "client_credentials";
            $option ['client_id']     = $this->client_id;
            $option ['client_secret'] = $this->client_secret;
            $token                    = $this->contact($this->url . '/token', $option);
            if (isset($token['access_token'])) {
                $this->cacheToken($token);
                return $token['access_token'];
            } else {
                return false;
            }
        }
    }

    /**
     * 持久化token
     *
     * @param bool $saveToken
     *
     * @return bool
     */
    private function cacheToken($saveToken = false)
    {
        $cacheFilePath = base_path('/storage/data');
        if ($saveToken) {
            $saveToken['expires_in'] = $saveToken['expires_in'] + time();
            $fp                      = @fopen($cacheFilePath, 'w');
            @fwrite($fp, serialize($saveToken));
            //fclose($fp);
        } else {
            $fp = @fopen($cacheFilePath, 'r');
            if ($fp) {
                $data = unserialize(fgets($fp));
                fclose($fp);
                if (!isset($data['expires_in']) || !isset($data['access_token'])) {
                    return false;
                }
                if ($data['expires_in'] < time()) {
                    return false;
                } else {
                    return $data['access_token'];
                }
            }
            return false;
        }
    }

    /**
     * 创建一个群组
     *
     *
     * @param int    $ownerId   //群组的管理员，此属性为必须的
     * @param string $groupName /群组名称，此属性为必须的
     * @param string $desc
     * @param int    $maxUsers  /群组成员最大数（包括群主），值为数值类型，默认值200，最大值2000，此属性为可选的
     * @param bool   $isPublic  /是否是公开群，此属性为必须的
     * @param bool   $approval  /加入公开群是否需要批准，默认值是false（加入公开群不需要群主批准），此属性为必选的，私有群必须为true
     *
     * @return mixed
     */
    public function createGroup(
        $ownerId,
        $groupName,
        $desc = 'nanzhu chat group desc',
        $maxUsers = 500,
        $isPublic = true,
        $approval = true
    ) {
        $url = $this->url . '/chatgroups';

        $params = [
            'groupname' => $groupName,
            'desc'      => $desc,
            'public'    => $isPublic,
            'maxusers'  => $maxUsers,
            'approval'  => $approval,
            'owner'     => 'nanzhu_' . $ownerId
        ];

        return $this->contact($url, $params);
    }

    /**
     * 删除群组
     *
     * @param $groupId
     *
     * @return mixed
     */
    public function deleteGroup($groupId)
    {
        $url = $this->url . '/chatgroups/' . $groupId;

        return $this->contact($url, '', self::REQUEST_METHOD_DELETE);
    }

    /**
     * 获取群组信息
     *
     * @param $groupId
     *
     * @return mixed
     */
    public function getGroupInfo($groupId)
    {
        $url = $this->url . '/chatgroups/' . $groupId;

        return $this->contact($url, '', self::REQUEST_METHOD_GET);
    }

    /**
     * 更新群组信息
     *
     * @param      $groupId
     * @param null $groupName
     * @param null $description
     * @param null $maxMembers
     *
     * @return mixed
     */
    public function updateGroupInfo($groupId, $groupName = null, $description = null, $maxMembers = null)
    {
        $url = $this->url . '/chatgroups/' . $groupId;

        $params = [];

        if ($groupName) {
            $params ['groupname'] = $groupName;
        }

        if ($description) {
            $params ['description'] = $description;
        }

        if ($maxMembers) {
            $params ['maxusers'] = $maxMembers;
        }

        \Log::info('updateGroupInfo.' . $this->url . '  params:' . json_encode($params));
        return $this->contact($url, $params, self::REQUEST_METHOD_PUT);
    }

    /**
     * 获取群组成员
     *
     * @param int $groupId
     *
     * @return mixed
     */
    public function groupMembers($groupId)
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/users';

        return $this->contact($url, '', self::REQUEST_METHOD_GET);
    }

    /**
     * 用户参与的所有群组
     *
     * @param string $userName
     *
     * @return mixed
     */
    public function userJoinedGroups($userName)
    {
        $url = $this->url . '/users/' . $userName . '/joined_chatgroups';

        return $this->contact($url, '', self::REQUEST_METHOD_GET);
    }

    /**
     * 转让群组
     *
     * @param $groupId
     * @param $userName
     *
     * @return mixed
     */
    public function transforGroupOwnerToUser($groupId, $userName)
    {
        $url = $this->url . '/chatgroups/' . $groupId;

        return $this->contact($url, ['newowner' => $userName], self::REQUEST_METHOD_PUT);
    }

    /**
     * 获取群组的黑名单
     *
     * @param $groupId
     *
     * @return mixed
     */
    public function groupBlackLists($groupId)
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/blocks/users';

        return $this->contact($url, '', self::REQUEST_METHOD_GET);
    }

    /**
     * 群组屏蔽一个用户
     *
     * @param $groupId
     * @param $userName
     *
     * @return mixed
     */
    public function groupBlockUser($groupId, $userName)
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/blocks/users/' . $userName;

        return $this->contact($url);
    }

    /**
     * 群组屏蔽多个用户
     *
     * @param       $groupId
     * @param array $userNames
     *
     * @return mixed
     */
    public function groupBlockUsers($groupId, $userNames = [])
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/blocks/users/';

        return $this->contact($url, ['usernames' => $userNames]);
    }

    /**
     * 从群组黑名单移除某一个用户
     *
     * @param $groupId
     * @param $userName
     *
     * @return mixed
     */
    public function groupUnBlockUser($groupId, $userName)
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/blocks/users/' . $userName;

        return $this->contact($url, '', self::REQUEST_METHOD_DELETE);
    }

    /**
     * 从群组黑名单删除多个用户
     *
     * @param       $groupId
     * @param array $userNames
     *
     * @return mixed
     */
    public function groupUnblockUsers($groupId, $userNames = [])
    {
        $needToRemoveUserNames = implode(',', $userNames);

        $url = $this->url . '/chatgroups/' . $groupId . '/blocks/users/' . $needToRemoveUserNames;

        return $this->contact($url, '', self::REQUEST_METHOD_DELETE);
    }

    /**
     * 用户加入黑名单
     *
     * @param $ownerName
     * @param $friendName
     *
     * @return mixed
     */
    public function userBlockUser($ownerName, $friendName)
    {
        return $this->userBlockUsers($ownerName, [$friendName]);
    }

    /**
     * 用户把用户加入黑名单
     *
     * @param string $ownerName
     * @param array  $friendNames
     *
     * @return mixed
     */
    public function userBlockUsers($ownerName, $friendNames)
    {
        $url = $this->url . '/users/' . $ownerName . '/blocks/users';

        $params = ['usernames' => $friendNames];

        return $this->contact($url, $params);
    }

    /**
     * 用户把人移除黑名单
     *
     * @param $ownerName
     * @param $friendName
     *
     * @return mixed
     */
    public function userUnBlockUser($ownerName, $friendName)
    {
        $url = $this->url . '/users/' . $ownerName . '/blocks/users/' . $friendName;

        return $this->contact($url, '', self::REQUEST_METHOD_DELETE);
    }

    /**
     * 用户把人从黑名单移除
     *
     * @param string $ownerName
     * @param array  $friendNames
     */
    public function userUnBlockUsers($ownerName, $friendNames)
    {
        foreach ($friendNames as $friendName) {
            try {
                $this->userUnBlockUser($ownerName, $friendName);
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    /**
     * 用户的黑名单
     *
     * @param $ownerName
     *
     * @return mixed
     */
    public function userBlackLists($ownerName)
    {
        $url = $this->url . '/users/' . $ownerName . '/blocks/users';

        return $this->contact($url, '', self::REQUEST_METHOD_GET);
    }

    /**
     * 向环信请求
     *
     * @param        $url
     * @param string $params
     * @param string $type POST|GET
     *
     * @return mixed
     * @throws \ErrorException
     */
    private function contact($url, $params = '', $type = self::REQUEST_METHOD_POST)
    {
        $postData = '';
        if (is_array($params)) {
            $postData = json_encode($params);
        }
        $curl = new Curl();
        $curl->setUserAgent('Jasonwx/Easemob SDK; Jason Wang<jasonwx@163.com>');
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);
        $curl->setHeader('Content-Type', 'application/json');
        if ($url !== $this->url . '/token') {
            $token = $this->getToken();
            $curl->setHeader('Authorization', 'Bearer ' . $token);
        }
        switch ($type) {
            case self::REQUEST_METHOD_POST: {
                $curl->post($url, $postData);
                break;
            }
            case self::REQUEST_METHOD_GET: {
                $curl->get($url);
                break;
            }
            case self::REQUEST_METHOD_DELETE: {
                $curl->delete($url);
                break;
            }
            case self::REQUEST_METHOD_PUT: {
                $curl->put($url, $postData);
                break;
            }
        }
        $curl->close();
        if ($curl->error) {
            throw new \ErrorException('CURL Error: ' . $curl->error_message . $curl->raw_response, $curl->error_code);
        }
        return json_decode($curl->raw_response, true);
    }


}
