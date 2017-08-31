<?php

namespace App\Console\Commands;

use App\Models\EaseUser;
use App\Models\Movie;
use App\User;
use Illuminate\Console\Command;

class FulfillHxUserAndGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fulfill:hx';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为用户创建环信用户;为部门创建环信群组';

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
	//        $this->fulfillHxUsers();

        $this->fulfillHxGroups();

    }

    /**
     * 补全环信用户
     */
    private function fulfillHxUsers()
    {
        $this->info('----------------------[开始进行环信用户补全操作]-------------------------' . PHP_EOL);

        foreach (User::all() as $index => $user) {
            $this->info('----------------------[第' . ($index + 1) . '个用户]-------------------------' . PHP_EOL);

            if ($user->easemob_uuid) {
                $this->warn("用户{$user->FID}已经注册了环信用户,不需要补全\n");
                continue;
            }

            $this->info("用户{$user->FID}没有注册环信用户,需要补全\n");
            try {
                (new EaseUser())->register($user->FID);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                continue;
            }
            $newUser = User::find($user->FID);
            $this->info("用户{$user->FID}环信ID为:{$newUser->easemob_uuid}\n");

            $this->info(PHP_EOL . PHP_EOL);
        }

        $this->info(PHP_EOL . PHP_EOL);
    }

    private function fulfillHxGroups()
    {
        $this->info('----------------------[开始进行环信群组补全操作]-------------------------' . PHP_EOL);

        foreach (Movie::where('shootend', 0)->get() as $index => $movie) {
            $this->info('----------------------[第' . ($index + 1) . '个剧组]-------------------------' . PHP_EOL);

            $groups = $movie->groups;

            foreach ($groups as $group) {

                if ($group->members()->count() == 0) {
                    continue;
                }

                $this->info("部门{$group->FID}[{$group->FNAME}/{$group->movie->FNAME}]开始注册环信群组\n");
                try {
		    $createdGroup = null;
                    foreach ($group->members as $key=> $member) {
                        $this->info("部门成员{$member->user->FNAME}开始加入群组\n");

                        if($key == 0){
                            $createdGroup = $group->createChatGroupWithOwner($member->user);
                        }else{
                            $member->user->joinHxGroup($createdGroup);
                        }
                    }
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                    continue;
                }
            }

            $this->info(PHP_EOL . PHP_EOL);
        }

    }
}
