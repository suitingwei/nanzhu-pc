<?php
namespace App\Utils;


require app_path('Venders/aliyun-php-sdk-core/Config.php');
use DefaultAcsClient;
use DefaultProfile;
use Mts\Request\V20140618 as Mts;
use Mts\Request\V20140618\AddTemplateRequest as AddTemplateRequest;

class OssUtil
{
    private $client = null;
    private $pipelineId = 'dcfd05f00f5b4896981ad98e0df5848a';

    public function __construct()
    {
        // 设置你的AccessKeyId/AccessSecret/AppKey
        $accessKeyId  = "6fAjg6qCuIl4xlBy";
        $accessSecret = "9KZTLx4iwpNNdw4xGclBlrhOl6HUhm";

        $iClientProfile = DefaultProfile::getProfile("cn-shanghai", $accessKeyId, $accessSecret);

        $this->client = new DefaultAcsClient($iClientProfile);

    }

    /**
     * 对nanzhuvideos这个bucket的文件进行截图
     *
     * http://nanzhuvideos.oss-cn-hangzhou.aliyuncs.com/professional/2.mp4
     * @param $videoUrl
     *
     *
     * @return string
     */
    public function snapNanzhuVideosBucket($videoUrl)
    {
        \Log::info('snap:对' . $videoUrl . '进行截图作业');
        $bucketUrl = 'http://nanzhuvideos.oss-cn-hangzhou.aliyuncs.com';
        //如果是nanzhuvideos这个bucket
        if (strrpos($videoUrl, $bucketUrl) !== false) {
            //去掉前缀/,oss的容错性不是很强,不能有前缀/
            //比如: /professional/1.mp4 ==> professional/1.mp4
            $obj = substr(str_replace($bucketUrl, '', $videoUrl), 1);
        }

        //如果不是这个bucket,或者是空
        if (!isset($obj) || empty($obj)) {
            return '';
        }

        $input = [
            'Location' => 'oss-cn-hangzhou',
            'Bucket'   => 'nanzhuvideos',
            'Object'   => urlencode($obj)
        ];

        return ($this->snapshot_job_flow($input));
    }

    private function snapshot_job_flow($input_file)
    {
        $snapshot_job = self::submit_snapshot_job($input_file);

        if ($snapshot_job->State == 'Fail') {
            return '';
        }

        return 'http://' . $snapshot_job->{'SnapshotConfig'}->{'OutputFile'}->{'Bucket'} . '.' . $snapshot_job->{'SnapshotConfig'}->{'OutputFile'}->{'Location'} . '.aliyuncs.com/' . urldecode($snapshot_job->{'SnapshotConfig'}->{'OutputFile'}->{'Object'});
    }

    /**
     * Submit the snap shot job.
     *
     * @param $input_file
     *
     * @return \SimpleXMLElement[]
     */
    private function submit_snapshot_job($input_file)
    {
        $obj = 'snapshots/' . uniqid() . '.jpg';

        $snapshot_output = array(
            'Location' => 'oss-cn-hangzhou',
            'Bucket'   => 'nanzhuvideos',
            'Object'   => urlencode($obj)
        );

        $snapshot_config = array(
            'OutputFile' => $snapshot_output,
            'Time'       => '1000'
        );

        $request = new Mts\SubmitSnapshotJobRequest();
        $request->setAcceptFormat('JSON');
        $request->setInput(json_encode($input_file));
        $request->setSnapshotConfig(json_encode($snapshot_config));

        $response = $this->client->getAcsResponse($request);

        return $response->{'SnapshotJob'};
    }


    /**
     * Transcode a video.
     *
     * @param $videoUrl
     *
     * @return string
     */
    public function transcode($videoUrl)
    {
        $bucketUrl = 'http://nanzhuvideos.oss-cn-hangzhou.aliyuncs.com';
        //如果是nanzhuvideos这个bucket
        if (strrpos($videoUrl, $bucketUrl) !== false) {
            //去掉前缀/,oss的容错性不是很强,不能有前缀/
            //比如: /professional/1.mp4 ==> professional/1.mp4
            $obj = substr(str_replace($bucketUrl, '', $videoUrl), 1);
        }

        //如果不是这个bucket,或者是空
        if (!isset($obj) || empty($obj)) {
            return '';
        }

        $input = ([
            'Location' => 'oss-cn-hangzhou',
            'Bucket'   => 'nanzhuvideos',
            'Object'   => urlencode($obj)
        ]);

        $watermark = ([
            'Location' => 'oss-cn-hangzhou',
            'Bucket'   => 'nanzhuvideos',
            'Object'   => urlencode('%E6%B0%B4%E5%8D%B0%402x.png')
        ]);

        return $this->user_custom_template_job_flow($input, $watermark);
    }


    private function system_template_job_flow($input_file, $watermark_file)
    {
        $analysis_id  = $this->submit_analysis_job($input_file, $this->pipelineId);
        $analysis_job = $this->wait_analysis_job_complete($analysis_id);
        $template_ids = $this->get_support_template_ids($analysis_job);

        # 可能会有多个系统模板，这里采用推荐的第一个系统模板进行转码
        $transcode_job_id = $this->submit_transcode_job($input_file, $watermark_file, $template_ids[0]);
        $transcode_job    = $this->wait_transcode_job_complete($transcode_job_id);

        print 'Transcode success, the target file url is http://' .
              $transcode_job->{'Output'}->{'OutputFile'}->{'Bucket'} . '.' .
              $transcode_job->{'Output'}->{'OutputFile'}->{'Location'} . '.aliyuncs.com/' .
              urldecode($transcode_job->{'Output'}->{'OutputFile'}->{'Object'}) . "\n";
    }

    private function submit_analysis_job($input_file, $pipeline_id)
    {
        $request = new Mts\SubmitAnalysisJobRequest();
        $request->setAcceptFormat('JSON');
        $request->setInput(json_encode($input_file));
        $request->setPriority(5);
        $request->setUserData('SubmitAnalysisJob userData');
        $request->setPipelineId($pipeline_id);

        $response = $this->client->getAcsResponse($request);
        return $response->{'AnalysisJob'}->{'Id'};
    }

    /**
     * Wait for the analysis job complete.
     *
     * @param $analysis_id
     *
     * @return null
     */
    private function wait_analysis_job_complete($analysis_id)
    {
        while (true) {
            $request = new Mts\QueryAnalysisJobListRequest();
            $request->setAcceptFormat('JSON');
            $request->setAnalysisJobIds($analysis_id);

            $response = $this->client->getAcsResponse($request);
            $state    = $response->{'AnalysisJobList'}->{'AnalysisJob'}[0]->{'State'};
            if ($state != 'Success') {
                if ($state == 'Submitted' or $state == 'Analyzing') {
                    sleep(5);
                } elseif ($state == 'Fail') {
                    print 'AnalysisJob is failed!';
                    return null;
                }
            } else {
                return $response->{'AnalysisJobList'}->{'AnalysisJob'}[0];
            }
        }
        return null;
    }

    /**
     * Get the support template job.
     *
     * @param $analysis_job
     *
     * @return array
     */
    private function get_support_template_ids($analysis_job)
    {
        $result = array();

        foreach ($analysis_job->{'TemplateList'}->{'Template'} as $template) {
            $result[] = $template->{'Id'};
        }

        return $result;
    }

    /**
     * Submit the video transcode job.
     *
     * @param $input_file
     * @param $watermark_file
     * @param $template_id
     *
     * @return mixed
     */
    function submit_transcode_job($input_file, $watermark_file, $template_id)
    {
        $oss_region = 'oss-cn-hangzhou';

        $output_bucket = 'nanzhuvideos';

        $watermark_config   = array();
        $watermark_config[] = array(
            'InputFile' => json_encode($watermark_file)
        );

        $obj       = 'transcoded/' . uniqid() . '.gif';
        $outputs   = array();
        $outputs[] = array(
            'OutputObject' => urlencode($obj),
            'TemplateId'   => $template_id,
            'WaterMarks'   => $watermark_config,
            'Clip'         => json_encode([
                'TimeSpan' => [
                    'Seek'     => '00:00:00.800',
                    "Duration" => "00:00:05.000"
                ]
            ])
        );

        $request = new Mts\SubmitJobsRequest();
        $request->setAcceptFormat('JSON');
        $request->setInput(json_encode($input_file));
        $request->setOutputBucket($output_bucket);
        $request->setOutputLocation($oss_region);
        $request->setOutputs(json_encode($outputs));
        $request->setPipelineId($this->pipelineId);

        $response = $this->client->getAcsResponse($request);
        return $response->{'JobResultList'}->{'JobResult'}[0]->{'Job'}->{'JobId'};
    }

    /**
     * Wait for the transcode job complete.
     *
     * @param $transcode_job_id
     *
     * @return null
     */
    function wait_transcode_job_complete($transcode_job_id)
    {
        while (true) {
            $request = new Mts\QueryJobListRequest();
            $request->setAcceptFormat('JSON');
            $request->setJobIds($transcode_job_id);

            $response = $this->client->getAcsResponse($request);
            $state    = $response->{'JobList'}->{'Job'}[0]->{'State'};
            if ($state != 'TranscodeSuccess') {
                if ($state == 'Submitted' or $state == 'Transcoding') {
                    sleep(5);
                } elseif ($state == 'TranscodeFail') {
                    print 'Transcode is failed!';
                    return null;
                }
            } else {
                return $response->{'JobList'}->{'Job'}[0];
            }
        }
        return null;
    }

    /**
     * Create a new transcode template.
     */
    public function add_transcode_template()
    {
        $request = new Mts\AddTemplateRequest();
        $request->setName('transcode_gif');
        $request->setContainer(json_encode([
            'Format' => 'gif'
        ]));
        $request->setVideo(json_encode([
            'Codec' => 'GIF'
        ]));
        $response = $this->client->getAcsResponse($request);
    }


    /**
     * Custom template transcode job.
     *
     * @param $input_file
     * @param $watermark_file
     *
     * @return string
     */
    private function user_custom_template_job_flow($input_file, $watermark_file)
    {
        $transcode_template_id = '9e70fbb3e90c4ed1c474a1fc8423b134';

        $transcode_job_id = $this->submit_transcode_job($input_file, $watermark_file, $transcode_template_id);
        $transcode_job    = $this->wait_transcode_job_complete($transcode_job_id);

        return 'http://' .
              $transcode_job->{'Output'}->{'OutputFile'}->{'Bucket'} . '.' .
              $transcode_job->{'Output'}->{'OutputFile'}->{'Location'} . '.aliyuncs.com/' .
              urldecode($transcode_job->{'Output'}->{'OutputFile'}->{'Object'}) . "\n";
    }


}

