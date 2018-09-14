<?php
/**
 * Created by PhpStorm.
 * User: guoshuo
 */

require('./aliyun-sdk/Log_Autoload.php');

class DemoLog
{
    /**
     * 选择与上面步骤创建 project 所属区域匹配的 Endpoint
     */
    public $endpoint = 'cn-beijing.log.aliyuncs.com';

    /**
     * 使用你的阿里云访问秘钥 AccessKeyId
     */
    public $accessKeyId = '';

    /**
     * 使用你的阿里云访问秘钥 AccessKeySecret
     */
    public $accessKey = '';

    /**
     * 使用你创建的项目名称
     */
    public $project = '';

    /**
     * 使用你创建的日志库名称
     */
    public $logstore = '';

    public function getLogs()
    {
        $endpoint = $this->endpoint;
        $accessKeyId = $this->accessKeyId;
        $accessKey = $this->accessKey;
        $project = $this->project;
        $logstore = $this->logstore;

        $client = new \Aliyun_Log_Client($endpoint, $accessKeyId, $accessKey);

        $search = '';
        $group = "date_format(date_trunc('hour', __time__), '%Y-%m-%d')";
        $query = $search . "| select approx_distinct(__source__) as uv, count(1) as pv, ".$group." as time group by ".$group." order by time asc";
        $dataCount = NULL;
        $from = strtotime(date('Y-m-d', time()-3600*24*7));
        $to = time();
        $topic = "";

        while (is_null($dataCount) || (! $dataCount->isCompleted()))
        {
            $req = new \Aliyun_Log_Models_GetLogsRequest($project, $logstore, $from, $to, $topic, $query, 30, 0, False);
            $dataCount = $client->getLogs($req);
        }

        $logs = $dataCount->getLogs();
        foreach ($logs as $log) {

            $contents = $log->getContents();
            $data['time'][] = $contents['time'];
            $data['pv'][] = $contents['pv'];
        }

        return $data;
    }
}

$demoLog = new DemoLog();

$logs = $demoLog->getLogs();

var_dump($logs);
?>

<div id="main" style="width: 100%;height:400px;"></div>

<script src="./js/jquery-2.2.0.min.js"></script>
<script src="./js/echart.js"></script>
<script type="text/javascript">
    $(function() {
        var myChart = echarts.init(document.getElementById('main'));

        var  xTime = <?php echo json_encode($logs['time']); ?>, // ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            data = <?php echo json_encode($logs['pv']); ?>; // [10, 52, 200, 334, 390, 330, 220];

        setChart(xTime, data, myChart);
    });

    function setChart(xTime, data, myChart) {
        var option = option = {
            color: ['#3398DB'],
            tooltip : {
                trigger: 'axis',
                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                    type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                }
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis : [
                {
                    type : 'category',
                    data : xTime,
                    axisTick: {
                        alignWithLabel: true
                    }
                }
            ],
            yAxis : [
                {
                    type : 'value'
                }
            ],
            series : [
                {
                    name:'直接访问',
                    type:'bar',
                    barWidth: '60%',
                    data: data
                }
            ]
        };

        return myChart.setOption(option);
    }
</script>
