<?php

namespace App\Console\Commands;

use App\Models\GroupUser;
use App\Models\SparePhone;
use Illuminate\Console\Command;

class FulfillGroupUserSparePhones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fulfill:sharephones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fulfill groupuser\'s missing share phones,every groupuser should have 3 sharephones';

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
        foreach (GroupUser::all() as $index => $groupUser) {
            $this->info('----------------------['.$index.']-------------------------'.PHP_EOL);
            $sharePhones       = $groupUser->sharePhones;
            $createdPhoneCount = $sharePhones->count();

            $this->warn('正在检测组员' . $groupUser->FID . '...' . PHP_EOL);


            if ($createdPhoneCount === 3) {
                $this->warn("组员{$groupUser->FID}共有{$createdPhoneCount}个共享电话,不需要补全\n");
            }else{
                $needCount = 3-$createdPhoneCount;
                $this->warn("组员{$groupUser->FID}共有{$createdPhoneCount}个共享电话,需要补全{$needCount}个\n");

                for ($i = $createdPhoneCount; $i < 3; $i++) {
                    $newSparePhone = SparePhone::createNormalPhone($groupUser,$i);

                    $number =$i +1;

                    $this->info("第{$number}个补全电话创建完毕:{$newSparePhone}\n");
                }
            }
            $this->info(PHP_EOL.PHP_EOL);
        }
    }
}
