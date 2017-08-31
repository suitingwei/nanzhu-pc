<?php

namespace App\Console\Commands;

use App\Models\EaseUser;
use App\Models\Group;
use App\User;
use Illuminate\Console\Command;

class ClearHxUsersAndGroups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:hx';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清除环信用户以及环信群组';

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
//        $this->clearHxUsers();
//        $this->clearHxGroups();
	$this->clearUserHxGroups();
    }

    /**
     * 补全环信用户
     */
    private function clearHxUsers()
    {
        $this->info('----------------------[开始进行环信用户清除操作]-------------------------' . PHP_EOL);

        foreach (User::all() as $index => $user) {
            $this->info('----------------------[第' . ($index + 1) . '个用户]-------------------------' . PHP_EOL);

            if (!$user->easemob_uuid) {
                $this->warn("用户{$user->FID}没有环信用户,不需要清除\n");
                continue;
            }

            $this->info("用户{$user->FID}注册了环信用户:{$user->easemob_uuid}需要清除\n");
            try {
                (new EaseUser())->deleteUser($user->FID);
            } catch (\Exception $e) {
                $this->info(PHP_EOL . PHP_EOL);
                continue;
            }

            $this->info(PHP_EOL . PHP_EOL);
        }
        \DB::update("UPDATE t_sys_userSET easemob_uuid ='';");

        $this->info(PHP_EOL . PHP_EOL);
    }

    private function clearHxGroups()
    {
        $this->info('----------------------[开始进行环信群组清除操作]-------------------------' . PHP_EOL);
        $easeUser = new EaseUser();

        foreach (Group::all() as $index => $group) {
            $this->info('----------------------[第' . ($index + 1) . '个部门]-------------------------' . PHP_EOL);

            if (!$group->hx_group_id) {
                $this->warn("部门{$group->FID}[{$group->FNAME}没有注册环信群组,不需要清除\n");
                continue;
            }

            $this->info("部门{$group->FID}[{$group->FNAME}经注册了环信群组,需要清除\n");
            try {
                $easeUser->deleteGroup($group->hx_group_id);
            } catch (\Exception $e) {
                continue;
            }

            \DB::update('update t_biz_group set hx_group_id = "" ');
            $this->info(PHP_EOL . PHP_EOL);
        }
    }

    private function clearUserHxGroups()
    {
	$easeUser = new EaseUser();

	$userIds =  [28507,28411,21000,21100,28520];

	foreach(User::whereIn('FID',$userIds)->get() as $user){

		$joinedHxGroups = $user->joinedHxGroups();

		foreach ($joinedHxGroups as $chatGroup) {
		    //环信群组名为: nanzhu_group_{groupId}
		    $hxGroupId = $chatGroup['groupid'];

		    $easeUser->deleteGroup($hxGroupId);
		}
	}
    }
}
