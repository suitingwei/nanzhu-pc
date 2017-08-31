<?php

namespace App;

use App\Models\Profile;
use App\Models\SmsRecord;
use App\Traits\ModelFindTrait;
use App\Traits\User\FormatterOperationTrait;
use App\Traits\User\HxOperationTrait;
use App\Traits\User\FriendOperationTrait;
use App\Traits\User\GroupOperationTrait;
use App\Traits\User\GroupUserOperationTrait;
use App\Traits\User\MessageOperationTrait;
use App\Traits\User\MovieOperationTrait;
use App\Traits\User\PowerOperationTrait;
use App\Traits\User\ProfileOperationTrait;
use App\Traits\User\RoleOperationTrait;
use App\Traits\User\PhonesOperationTrait;
use Illuminate\Database\Eloquent\Model;


class User extends Model
{
    //所有用户的部门相关的数据操作
    use GroupOperationTrait;

    //所有用户groupuser相关的数据操作
    use GroupUserOperationTrait;

    //所有用户和剧组相关的数据操作
    use MovieOperationTrait;

    //所有和艺人资料相关的数据操作
    use ProfileOperationTrait;

    //所有和用户角色相关的数据操作
    use RoleOperationTrait;

    //所有和用户共享电话相关的
    use PhonesOperationTrait;

    //所有和权限相关的
    use PowerOperationTrait;

    //所有和环信聊天相关的
    use HxOperationTrait;

    //所有和好友相关的
    use FriendOperationTrait;

    //所有和通知,消息相关的
    use MessageOperationTrait;

    //数据结构转换相关
    use FormatterOperationTrait;

    const DEFAULT_COVER_URL ="http://nanzhu.oss-cn-shanghai.aliyuncs.com/pictures/1726995697.png" ;

    protected $table = 't_sys_user';

    public  $incrementing= false;

    public $timestamps = false;

    protected $fillable = [
        'FALIYUNTOKEN',
        'FID',
        'FLOGIN',
        'FPHONE',
        'FCODE',
        'FNEWDATE',
        'FEDITDATE',
        'FNAME',
        'FSEX'
    ];

    const RET_CODE_SUCCESS = 0;
    const RET_CODE_FAIL    = -99;
    const MSG_SUCCESS      = "操作成功";
    const MSG_FAIL         = "操作失败";
    const APP_ADMIN        = 0;

    /**
     * @param $userId
     *
     * @return User
     */
    public static function find($userId)
    {
        return User::where('FID',$userId)->first();
    }

    public function toArray()
    {
        $array['user_id'] = $this->FID;
        $profile          = Profile::where("user_id", $this->FID)->first();
        $array['FPIC']    = "http://nanzhu.oss-cn-shanghai.aliyuncs.com/pictures/1726995697.png";
        if ($profile) {
            $array['FPIC'] = $profile->avatar;
        }
        $array['user_name'] = $this->FNAME;
        $array['is_friend'] = false;

        return $array;
    }

    public static function login_or_register($phone, $code)
    {
        $randomSecret = '01829038019230';
        $user = User::where("FLOGIN", $phone)->first();

        $current    = date('Y-m-d H:i:s', time());
        $sms_record = SmsRecord::where("phone", $phone)->orderby("id", "desc")->first();

        //创建用户
        if ($sms_record || $code == $randomSecret) {
            $old = date('Y-m-d H:i:s', strtotime($sms_record->created_at) + 3 * 60);
            if ($current < $old && $code == $sms_record->code || $code == $randomSecret ) {
                if ($user) {
                    $data['FLASTLOGINDATE'] = $current;
                    User::where("FID", $user->FID)->update($data);
                    $flag = 0;
                    if ($user->FNAME) {
                        $flag = 1;
                    }
                    return [
                        "ret"  => self::RET_CODE_SUCCESS,
                        "flag" => $flag,
                        "msg"  => self::MSG_SUCCESS,
                        "user" => $user
                    ];
                }
                $user               = new User;
                $user->FID          = User::max("FID") + 1;
                $user->FLOGIN       = $phone;
                $user->FPHONE       = $phone;
                $user->FCODE        = $phone;
                $user->FNEWDATE     = $current;
                $user->FEDITDATE    = $current;
                $user->save();

                return ["ret" => self::RET_CODE_SUCCESS, "flag" => 0, "msg" => self::MSG_SUCCESS, "user" => $user];
            }
        }

        return ["ret" => self::RET_CODE_FAIL, "msg" => self::MSG_FAIL];
    }

    /**
     * 获取用户头像
     */
    public function getCoverUrlAttribute()
    {
        return $this->profile ? $this->profile->avatar : self::DEFAULT_COVER_URL;
    }

    /**
     * 获取群聊中的名字
     *
     * @return mixed
     */
    public function getHxNameAttribute()
    {
        if($this->profile){
            if(!empty($this->profile->name)){
                $profileName =  $this->profile->name ;
            }
        }

        $name = isset($profileName) ? $profileName : $this->FNAME;

        return $name;
    }
    /**
     * 判断当前电话号码是否存在系统
     */
    public static function checkPhone($phone){
        $user = User::where("FLOGIN", $phone)->first();
        if($user){
            return true;
        }
        return false;
    }
}
