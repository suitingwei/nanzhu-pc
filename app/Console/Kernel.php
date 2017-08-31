<?php

namespace App\Console;

use App\Models\Sms;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Mail;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Inspire::class,
        Commands\FulfillGroupUserSparePhones::class,
        Commands\FulfillHxUserAndGroup::class,
        Commands\ClearHxUsersAndGroups::class,
        Commands\MakeDirectorDepartmentEssential::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {

            $errorGroupData = \DB::select('SELECT * FROM t_biz_progressdailygrdata GROUP BY  FDAILYDATAID HAVING  count(FID) > 5;');
	    
            if ($errorGroupData) {
                $data = array('name'=>"Virat Gandhi");

                Mail::send('daily_data_error', $data, function($message) {
                    $message->to('suitingwei@nanzhuxinyu.com', '南竹错误日志')->subject('出现了每日数据重复数据');
                    $message->from('postmaster@nanzhuxinyu.com','nanzhuxinyu');
                });
            }

        })->everyTenMinutes();
    }
}
