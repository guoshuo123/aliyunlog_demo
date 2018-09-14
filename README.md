aliyun-log 简单应用

    JS/Web Tracking 采集日志
        引入文件 js/aliLog/loghub-tracking.js
        <script>
            var logger = new window.Tracker('host', 'project', 'logstore');
            logger.push({
                aaaa: '657578568',
                mobile: '13344564852',
                name: '测试测试测试',
                href: 'www.demo.com',
                data: {
                    'user': 'guoshuo1',
                    'data1': 234234,
                    'msg': {
                        'code': '0',
                        'content': 'success',
                    }
                }
            });
        
            logger.logger();
        </script>
    注: LogHub支持客户端、网页、协议、SDK/API等多种日志无损采集方式，所有采集方式均基于Restful API实现，除此之外您也可以通过API/SDK实现新的采集方式。
        详见: https://help.aliyun.com/document_detail/28981.html?spm=a2c4g.11186623.6.574.119777a88tjGuB
        
    PHP SDK
        下载地址: https://github.com/aliyun/aliyun-log-php-sdk
        
        本文提供PHP简单的日志操作 例见: index.php    
    
    功能优势
        1. 实时：写入后可以立即被分析。
        2. 速度快：
        3. 查询：一秒内查询（5个条件）可处理10亿级数据。
        4. 分析：一秒内分析（5个维度聚合+GroupBy）可聚合亿级别数据。
        5. 灵活：可以改变任意查询和分析条件，实时获取结果。
        6. 生态：除控制台提供的报表、仪表盘、快速分析等功能外，还可以和无缝与Grafana、DataV、Jaeger等产品对接，并支持Restful API，JDBC等协议。

    数据类型
        1. text 查询实例: uri:"login*"
            区分大小写 
            分词符: 例如查询'/url/pic/abc.gif', 不设置任何分词符: 只有通过该完整字符串, 或模糊查询'/url/pic/*'
            包含中文
            全文索引
            
        数值类型 (2＆3)
        2. long 数值类型, 例: status>200, status in [200, 500]
        3. double 带浮点数数值类型, 例: price>28.95, t in [20.0, 37]
            查询只能通过数值范围
            
        4. json JSON字段, 例: level0.key>29.95, level0.key2:"action"
            通过jsonkey.key1:"text_value", jsonkey.key2:true 等条件进行查询
    
        5. 文本 整条日志当做文本进行查询。 例: error and "login fail"

    查询分析语法
        由（Search、Analytics）两个部分组成，中间通过|进行分割
        查询（Search）：查询条件，可以由关键词、模糊、数值等、区间范围和组合条件等产生。如果为空或”*”，则代表所有数据。
        分析（Analytics）：对查询结果或全量数据进行计算和统计。
        例: " | select approx_distinct(__source__) as uv, 
                count(1) as pv, 
                date_format(date_trunc('hour', __time__), '%Y-%m-%d') as time 
                group by date_format(date_trunc('hour', __time__), '%Y-%m-%d') 
                order by time asc"

    语法关键词
        **and**
        **or**
        **not**         形式为query1 not query2 ，表示符合query1 并且不符合query2的结果
        **( , )**       把一个或多个子 query 合并成一个 query
        **:**           用于 key-value 对的查询
        **“**           关键词转换成普通的查询字符
        **\**           转义符
        **timeslice**   时间分片运算符  例如 query1 | timeslice 1h | count as count_num 表示查询 query 这个条件，并且返回以 1 小时为时间分片的总次数
        **count**       计数运算符, 日志条数
        **source**      查询某个IP的数据  例如source:127.0.0.1
        **__topic__**   查询某个 topic 下数据
        
    查询示例
        同时包含 a 和 b 的日志： a and b 或者 a b
        包含 a 或者包含 b 的日志：a or b
        包含 a 但是不包含 b 的日志：a not b
        所有日志中不包含 a 的日志：not a
        查询包含 a 而且包含 b，但是不包括 c 的日志：a and b not c
        包含 a 或者包含 b，而且一定包含 c 的日志：(a or b ) and c
        包含 a 或者包含 b，但不包括 c 的日志：(a or b ) not c
        包含 a 而且包含 b，可能包含 c 的日志：a and b or c
        FILE 字段包含 apsara的日志： FILE:apsara
        FILE 字段包含 apsara 和 shennong 的日志：FILE:"apsara shennong" 或者 FILE:apsara FILE: shennong 或者 FILE:apsara and FILE:shennong
        包含 and 的日志：and
        FILE 字段包含 apsara 或者 shennong 的日志：FILE:apsara or FILE:shennong
        file info 字段包含 apsara 的日志："file info":apsara
        包括引号的日志：\"
        查询以 shen 开头的所有日志：shen*
        查询 FILE 字段下，以 shen 开头的所有日志：FILE:shen*
        查询以 shen 开头，以 ong 结尾，中间还有一个字符的日志：shen?ong
        查询包括以 shen 开头，并且包括以 aps 开头的日志：shen* and aps*
        查询以 shen 开头的日志的分布，时间片为 20 分钟：shen*| timeslice 20m | count
        查询 topic1 和 topic2 下的所有数据： __topic__:topic1 or __topic__ : topic2
        查询 tagkey1 下 tagvalue2 的所有数据：__tag__ : tagkey1 : tagvalue2
        查询latency大于等于100，并且小于200的所有数据，有两种写法：latency >=100 and latency < 200或latency in [100 200)。
        查询latency 大于100的所有请求，只有一种写法： latency > 100。
        查询不包含爬虫的日志，并且http_referer中不包含opx的日志： not spider not bot not http_referer:opx。
        查询cdnIP字段为空的日志： not cdnIP:""。
        查询cdnIP字段不存在的日志：not cdnIP:*。
        查询存在cdnIP字段的日志：cdnIP:*。
        
    实时分析简介
        基本语法：
            [search query] | [sql query]
            
        前提条件
        要使用分析功能，必须在查询分析设置中点击SQL涉及的字段下开启分析开关。
        如果不开启统计，默认只提供每个shard最多1万行数据的计算功能，而且延时比较高。
        开启后可以提供秒级别快速分析。
        开启后只对新数据生效。
        开启统计后不会产生额外费用。
        
        内置字段
            __time__    日志的时间。
            __source__  日志来源IP。在搜索时，该字段是source，在SQL中才会带上前后各两个下划线。
            __topic__   日志的Topic。
            
        SELECT聚合计算函数：
            https://help.aliyun.com/document_detail/63445.html?spm=a2c4g.11186623.6.675.419c3642pmxMxp
        
            
