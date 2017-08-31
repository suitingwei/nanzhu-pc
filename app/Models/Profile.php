<?php

namespace App\Models;

use App\User;
use App\Utils\OssUtil;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Profile
 * @package App\Models
 */
class Profile extends Model
{
    //自我介绍视频站位图
    private $selfVideoDefaultCoverImgUrl = 'http://nanzhu.oss-cn-shanghai.aliyuncs.com/pictures/self_video_cover.jpg';

    //集锦视频站位图
    private $collectionVideoDefaultCoverImgUrl = 'http://nanzhu.oss-cn-shanghai.aliyuncs.com/pictures/collection_video_cover.jpg';

    /**
     * 星座
     * @var array
     */
    public static $constellation_arr = [
        "水瓶座",
        "双鱼座",
        "白羊座",
        "金牛座",
        "双子座",
        "巨蟹座",
        "狮子座",
        "天秤座",
        "天蝎座",
        "射手座",
        "魔蝎座"
    ];

    /**
     * 血型
     * @var array
     */
    public static $bloodTypes = ["A", "AB", "B", "O"];

    /**
     * 幕后职位
     * @var array
     */
    public static $behindScenePositions = [
        '制片人/制作人',
        '监制',
        '导演',
	'策划',
        '编剧',
        '文学统筹/剧本统筹',
        '统筹',
        '场记',
        '执行制片人',
        '制片主任',
        '现场制片',
        '外联制片',
        '生活制片',
        '执行导演',
        '演员副导/艺人统筹',
        '摄影指导/摄影师',
        '美术指导/美术',
        '录音指导/录音',
        '服装师',
        '化妆指导/化妆师',
        '道具',
        '照明/灯光',
        '动作指导/武行',
        '特效化妆',
        '剪辑/后期',
        '剧照',
        '经纪人',
        '特效',
        '司机',
        '场务',
        '茶水',
        '艺人助理',
        'EPK',
        '宣传',
        '发行'
    ];

    /**
     * 台前职位
     * @var array
     */
    public static $beforeScenePositions = [
        '演员',
        '歌手',
        '主持人',
        '配音演员',
        '戏曲演员',
        '平面模特',
        'T台模特',
        '网红',
        '特型演员'
    ];

    /**
     * 可填写字段,这个地方不能替换成guard,
     * 改了的话Api\UsersController有地方更新profile的时候使用了不属于fillable的字段,
     * 如果使用fillable会导致报错
     * @var array
     */
    protected $fillable = [
        "is_show",
        "sort",
        "name",
        "type",
        "avatar",
        "gender",
        "height",
        "weight",
        "mobile",
        "constellation",
        "blood_type",
        "work_ex",
        "prize_ex",
        "user_id",
        "speciality",
        "introduction",
        "college",
        "language",
        "birthday",
        "email",
        "hometown",
        'before_position',
        'behind_position',
        'self_video_url',
        'collection_video_url',
        'schedule',
        'self_video_cover_url',
        'collection_video_cover_url'
    ];

    /**
     * 协助编辑的用户
     */
    public function shareEditors()
    {
        return $this->hasMany(ProfileShare::class, 'profile_id', 'id');
    }

    /**
     *
     */
    public function user()
    {
        return $this->hasOne(User::class, 'FID', 'user_id');
    }

    /**
     * 旧版本,艺人类型,新版本使用幕后身份,台前身份
     * @return array
     */
    public static function types()
    {
        $types = ["全部", "演员", "歌手", "主持人", "网红", "其他"];
        return $types;
    }


    /**
     * @return mixed
     */
    public function pic_urls()
    {
        return Album::where("profile_id", $this->id)->get();
    }


    /**
     * 幕后大咖
     *
     * @param $query
     *
     * @return
     */
    public function scopeBehindScene($query)
    {
        return $query->where('behind_position', '!=', '')->whereNotNull('behind_position');
    }

    /**
     * 台前艺人
     *
     * @param $query
     *
     * @return
     */
    public function scopeBeforeScene($query)
    {
        return $query->where('before_position', '!=', '')->whereNotNull('before_position');
    }

    /**
     * 不是幕后身份
     *
     * @param $query
     *
     * @return mixed
     */
    public function scopeNotBehindScene($query)
    {
        return $query->where('behind_position', '')->whereNotNull('behind_position');
    }

    /**
     * 艺人资料是否展示
     *
     * @param $query
     *
     * @return mixed
     */
    public function scopeShown($query)
    {
        return $query->where('is_show', 1);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id'                         => $this->id,
            'name'                       => $this->name,
            'user_id'                    => $this->user_id,
            'avatar'                     => $this->avatar,
            'birthday'                   => $this->birthday,
            'email'                      => $this->email,
            'hometown'                   => $this->hometown,
            'height'                     => $this->height,
            'weight'                     => $this->weight,
            'mobile'                     => $this->mobile,
            'gender'                     => $this->gender,
            'language'                   => $this->language,
            'college'                    => $this->college,
            'speciality'                 => $this->speciality,
            'introduction'               => $this->introduction,
            'constellation'              => $this->constellation,
            'blood_type'                 => $this->blood_type,
            'type'                       => $this->type,
            'work_ex'                    => $this->work_ex,
            'prize_ex'                   => $this->prize_ex,
            'pic_urls'                   => $this->pic_urls(),
            'self_video_url'             => $this->self_video_url,
            'collection_video_url'       => $this->collection_video_url == ',' ? '' : $this->collection_video_url,
            'schedule'                   => $this->schedule,
            'before_position'            => $this->before_position,
            'behind_position'            => $this->behind_position,
            'is_liked'                   => $this->is_liked,
            'is_share'                   => $this->is_share,
            'like_count'                 => $this->likeCount(),
            'is_favorite'                => $this->is_favorite,
            'self_video_cover_url'       => $this->self_video_obj,
            'collection_video_cover_url' => $this->collection_video_obj,
            'phone'                      => $this->user->FPHONE ? $this->user->FPHONE : 0
        ];
    }


    /**
     * 资料是否开启协助编辑
     *
     * @param $userId
     *
     * @return bool
     */
    public function sharedWithUser($userId)
    {
        return (boolean)ProfileShare::where(['profile_id' => $this->id, 'user_id' => $userId])->count() > 0;
    }

    /**
     * 被喜欢的数量
     * @return int
     */
    public function likeCount()
    {
        return Like::where('like_id', $this->id)->count();
    }


    /**
     * 自我介绍视频的封面,给安卓用的
     * 他们获取不了oss视频的第一帧
     * @return array
     */
    public function getSelfVideoObjAttribute()
    {
        $result     = [];
        $coverArray = explode(',', $this->self_video_cover_url);
        $videoArray = explode(',', $this->self_video_url);
        for ($i = 0; $i < count($videoArray); $i++) {
            $coverUrl = $this->selfVideoDefaultCoverImgUrl;
            if (isset($coverArray[$i]) && $coverArray[$i] != '') {
                $coverUrl = $coverArray[$i];
            }
            $result [] = $this->buildVideoObj($coverUrl, $videoArray[$i]);
        }
        return $result;
    }

    /**
     * 集锦介绍视频的封面,给安卓用的
     * 他们获取不了oss视频的第一帧
     * @return array
     */
    public function getCollectionVideoObjAttribute()
    {
        $result     = [];
        $coverArray = explode(',', $this->collection_video_cover_url);
        $videoArray = explode(',', $this->collection_video_url);
        for ($i = 0; $i < count($videoArray); $i++) {
            $coverUrl = $this->collectionVideoDefaultCoverImgUrl;
            if (isset($coverArray[$i]) && $coverArray[$i] != '') {
                $coverUrl = $coverArray[$i];
            }
            $result [] = $this->buildVideoObj($coverUrl, $videoArray[$i]);
        }
        return $result;
    }

    /**
     * 组件视频对象
     *
     * @param $coverUrl
     * @param $videoUrl
     *
     * @return \stdClass
     */
    public function buildVideoObj($coverUrl, $videoUrl)
    {
        $videoObj            = new \stdClass();
        $videoObj->cover_url = $coverUrl;
        $videoObj->video_url = $videoUrl;

        return $videoObj;
    }

    /**
     * 获取视频截图
     */
    public function snapSelfVideoShoot()
    {
        \Log::info('snap self video shoot');
        //截取自我介绍视频
        $ossUtil = new OssUtil;

        $snapShootUrl = $ossUtil->snapNanzhuVideosBucket($this->self_video_url);

        if (empty($snapShootUrl)) {
            $snapShootUrl = $this->selfVideoDefaultCoverImgUrl;
        }

        $this->update(['self_video_cover_url' => $snapShootUrl]);
    }

    /**
     * 对集锦视频截图
     */
    public function snapCollectionVideoShoot()
    {
        //截取自我介绍视频
        $ossUtil = new OssUtil;

        $snapShootUrlArray = array_map(function ($collectionVideoUrl) use ($ossUtil) {
            $snapShootUrl = $ossUtil->snapNanzhuVideosBucket($collectionVideoUrl);

            $snapShootUrl = $snapShootUrl ? $snapShootUrl : $this->collectionVideoDefaultCoverImgUrl;

            return $snapShootUrl;

        }, explode(',', $this->collection_video_url));

        $this->update(['collection_video_cover_url' => implode(',', $snapShootUrlArray)]);
    }

    /**
     * 对视频进行截图
     */
    public function snapVideoShoot()
    {
        $this->snapSelfVideoShoot();

        $this->snapCollectionVideoShoot();
    }


}
