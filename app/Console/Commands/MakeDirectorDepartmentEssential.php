<?php

namespace App\Console\Commands;

use App\Models\Group;
use Illuminate\Console\Command;

/**
 * Class MakeDirectorDepartmentEssential
 * @package App\Console\Commands
 * ---------------------------------------
 * 1.本脚本会进行两次修改
 *          1.修改已有的所有导演Group的FGROUPTYPE=40
 *          2.修改创建剧组时候的Group Templates的FGROUPTYPE=40
 */
class MakeDirectorDepartmentEssential extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:make_director_essential';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '上线操作,修改导演部门为剧组必须部门;';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('修改已有的所有导演Group的FGROUPTYPE=40'.PHP_EOL);

        \DB::update("UPDATE t_biz_group SET FGROUPTYPE = 40 WHERE FNAME like '导演%'");

        $this->info('修改完成...'.PHP_EOL);

        $this->info('向剧组模板池添加"南竹客服"');

        Group::create([
            'FMOVIE'=>0,
            'FPOS' =>'',
            'FREMARK'=>'',
            'FCONTACT'=>'',
            'FPHONE'=>'',
            'FUSER'=>'',
            'FID'=> Group::max('FID') +1,
            'FCODE'=>'',
            'FNAME'=>'南竹客服',
            'FNEWUSER'=>'',
            'FEDITUSER'=>'',
            'FNEWDATE'=>time(),
            'FEDITDATE'=>time(),
            'FLEADERID'=>'',
            'FGROUPTYPE'=>Group::TYPE_NANZHU_ASSIT,
            'hx_group_id'=>''
        ]);

        $this->info('添加完成...'.PHP_EOL);
    }
}
