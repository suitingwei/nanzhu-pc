<?php

namespace App\Jobs;


use App\Models\Pusher;
use App\Models\PushRecord;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPush extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $aliyuntokens;
    protected $title;
    protected $body;
    protected $summary;
    protected $extra;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($aliyuntokens, $title, $body, $summary, $extra)
    {
        $this->aliyuntokens = $aliyuntokens;
        $this->title        = $title;
        $this->body         = $body;
        $this->summary      = $summary;
        $this->extra        = $extra;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $status = Pusher::send($this->aliyuntokens, $this->title, $this->body, $this->summary, $this->extra);

        $record               = new PushRecord;
        $record->aliyuntokens = $this->aliyuntokens;
        $record->title        = $this->title;
        $record->body         = $this->body;
        $record->summary      = $this->summary;
        $record->extra        = $this->extra;
        $record->status       = $status->ResponseId;
        $record->save();

    }
}
