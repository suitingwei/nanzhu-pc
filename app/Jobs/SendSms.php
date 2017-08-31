<?php

namespace App\Jobs;

use App\Models\Sms;
use App\Models\SmsRecord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSms extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $to;
    protected $datas;
    protected $tempId;

    public function __construct($to, $datas, $tempId)
    {
        $this->to     = $to;
        $this->datas  = $datas;
        $this->tempId = $tempId;
    }

    public function handle()
    {
        Sms::send($this->to, $this->datas, $this->tempId);
        $sms_record              = new SmsRecord();
        $sms_record->phone       = $this->to;
        $sms_record->code        = $this->datas[0];
        $sms_record->valid_time  = $this->datas[1];
        $sms_record->template_id = $this->tempId;
        $sms_record->status      = "000000";
        $sms_record->save();
    }
}
