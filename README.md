索引与查询
    功能优势
    1. 实时：写入后可以立即被分析。
    2. 速度快：
    3. 查询：一秒内查询（5个条件）可处理10亿级数据。
    4. 分析：一秒内分析（5个维度聚合+GroupBy）可聚合亿级别数据。
    5. 灵活：可以改变任意查询和分析条件，实时获取结果。
    6. 生态：除控制台提供的报表、仪表盘、快速分析等功能外，还可以和无缝与Grafana、DataV、Jaeger等产品对接，并支持Restful API，JDBC等协议。

    数据类型
    1. text 查询实例: uri:"login*"
        区分大小写: 
        分词符: 例如查询'/url/pic/abc.gif', 不设置任何分词符: 只有通过该完整字符串, 或模糊查询'/url/pic/*'
        包含中文: 
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
    and
    or
    not 形式为query1 not query2 ，表示符合query1 并且不符合query2的结果
    ( , ): 把一个或多个子 query 合并成一个 query
    : 用于 key-value 对的查询
    “ 关键词转换成普通的查询字符
    \ 转义符
    timeslice  时间分片运算符  例如 query1 | timeslice 1h | count as count_num 表示查询 query 这个条件，并且返回以 1 小时为时间分片的总次数
    count 计数运算符, 日志条数
    source  查询某个IP的数据  例如source:127.0.0.1
    __topic__  查询某个 topic 下数据
