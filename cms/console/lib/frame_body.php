<script>
    $(function () {
        $.ajax({
            url : "<?=$_VAR_PATH_BIZ?>app/mng_statistics.php?type=dash_board",
            type:"post",
            dataType : "json",
            success : function (data) {
                $("#today_visitor").text(data.today).counterUp();
                $("#expert_count").text(data.expert_count).counterUp();
                $("#business_count").text(data.business_count).counterUp();
                $("#member_count").text(data.member_count).counterUp();

                $("#today_compare").text(data.today_compare+"%");
                $("#expert_compare").text(data.expert_compare+"%");
                $("#business_compare").text(data.business_compare+"%");
                $("#member_compare").text(data.member_compare+"%");
            }
        });

        $("#header_site").combobox({
            onClickIcon: function(index){
                var dm_site_id = $(this).combobox('getValue');
                var dm_site_array = $(this).combobox('getData');

                if (index == '0')
                {
                    if (dm_site_id != "")
                    {
                        for(var i = 0; i<dm_site_array.length; i++)
                        {
                            if (dm_site_array[i].dm_id == dm_site_id)
                            {
                                window.open("http://"+dm_site_array[i].dm_domain_url, "_blank");
                            }
                        }
                    }
                }

                if (index == '1')
                {
                    $.messager.confirm('경고', '사이트를 변경하시겠습니까?', function(r){
                        if (r){
                            setSite();
                        }
                    });

                }
            }
        });
    });

    function setSite()
    {
        var site_id = $("#header_site").combobox("getValue");

        $.ajax({
           url : "<?=$_VAR_PATH_BIZ ?>app/mng_site.php",
            data : "type=set_site&site_id="+site_id,
           dataType : "json",
           type : "post",
           success : function (data)
           {
               if (data.result == 'success')
               {
                   <? if (getSession('site_id')) { ?>
                   var site_id = '<?=getSession("site_id")?>';
                   $("#header_site").combobox("setValue", site_id);
                   <? } ?>
               }
           }
        });
    }

</script>
<div data-options="region:'center',title:'',iconCls:'icon-ok'">
    <div id="m_frame" fit="true" border="false" class="easyui-tabs tab-main" style="margin:0px;height:auto" data-options="tools:'#tab-tools'">
        <div class="easyui-layout" fit="true">
            <div class="contents">
                <div class="row">
                    <div class="col-md-6 col-xl-3">
                        <div class="cardbox tilebox">
                            <i class="icon01"></i>
                            <h6>방문자</h6>
                            <h3 id="today_visitor">0</h3>
                            <span class="badge red mr-1" id="today_compare"> +11% </span> <span>전날 대비</span>
                        </div>
                    </div><!-- end col-->

                    <div class="col-md-6 col-xl-3">
                        <div class="cardbox tilebox">
                            <i class="icon02"></i>
                            <h6>전문가회원 신청수</h6>
                            <h3 id="expert_count">0</h3>
                            <span class="badge blue mr-1" id="expert_compare"> -29% </span> <span>전날 대비</span>
                        </div>
                    </div><!-- end col-->

                    <div class="col-md-6 col-xl-3">
                        <div class="cardbox tilebox">
                            <i class="icon02"></i>
                            <h6>기업회원 신청수</h6>
                            <h3 id="business_count">0</h3>
                            <span class="badge gray mr-1" id="business_compare"> 0% </span> <span>전날 대비</span>
                        </div>
                    </div><!-- end col-->

                    <div class="col-md-6 col-xl-3">
                        <div class="cardbox tilebox">
                            <i class="icon04"></i>
                            <h6>전체회원</h6>
                            <h3 id="member_count"></h3>
                            <span class="badge yellow mr-1" id="member_compare"> +89% </span> <span>전달 대비</span>
                        </div>
                    </div><!-- end col-->
                </div>

                <div class="row">
                    <div class="col-lg-6 col-xl-4">
                        <div class="cardbox">
                            <h6 class="mb10">방문자 추이</h6>

                            <div class="btn-group">
                                <button type="button" class="btn" id="today_visitor_graph">Today</button>
                                <button type="button" class="btn" id="weekend_visitor_graph">이번주</button>
                                <button type="button" class="btn" id="lastweek_visitor_graph">지난주</button>
                            </div>

                            <div id="chart-area2"></div>
                        </div>
                    </div><!-- end col-->
                    <div class="col-lg-6 col-xl-8">
                        <div class="cardbox">
                            <h6>Weekly's 신규회원</h6>
                            <div id="chart-area"></div>
                        </div>
                    </div><!-- end col-->
                </div>

                <div class="row">
                    <div class="col-lg-12 col-xl-12">
                        <div class="cardbox cardbox2">
                            <h6>게시판 현황</h6>
                            <table>
                                <thead>
                                <tr>
                                    <th>게시판명</th>
                                    <th>총 게시글</th>
                                    <th>오늘 게시글</th>
                                    <th>답변</th>
                                </tr>
                                </thead>
                                <tbody id="board_total">
                                <tr>
                                    <td>게시판명1</td>
                                    <td>8</td>
                                    <td>0</td>
                                    <td>1</td>
                                </tr>
                                <tr>
                                    <td>게시판명2</td>
                                    <td>8</td>
                                    <td>0</td>
                                    <td>1</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div><!-- end col-->
                </div>
            </div>

            <script type="text/javascript" src="<?=$_VAR_PATH_JS?>plugin.js"></script>
            <script type="text/javascript" src="<?=$_VAR_PATH_JS?>main.js"></script>
        </div>
    </div>
</div>

<script>
    $(function () {
        var widthSize = $('#chart-area2').width();
        var chart1_widthSize = $('#chart-area').width();
        var container = document.getElementById('chart-area2');
        var container1 = document.getElementById('chart-area');

        getVisitorWeekly('today');
        weeklyNewMember();

        function getVisitorWeekly(x) {
            $.ajax({
                url : "<?=$_VAR_PATH_BIZ ?>app/mng_statistics.php",
                data : "type=visitor_weekly&search_date="+x,
                dataType:"json",
                success : function (data) {
                    $('#chart-area2').empty();
                    var chart_data = {
                        categories: [data.search_date],
                        series: [
                            {
                                name: '신규방문자',
                                data: data.dm_new_visit
                            },
                            {
                                name: '재방문자',
                                data: data.dm_re_visit
                            }
                        ]
                    };
                    var options = {
                        chart: {
                            width: widthSize - 50, height: 296 /*,title: ''*/
                        },
                        format: function(value, chartType, areaType, valuetype, legendName) {
                            if (areaType === 'makingSeriesLabel') { // formatting at series area
                                value = value + '%';
                            }
                            return value;
                        },
                        series: {
                            radiusRange: ['60%', '100%'],
                            showLabel: false
                        },
                        tooltip: {
                            suffix: '%'
                        },
                        legend: {
                            align: 'right'
                        }
                    };
                    tui.chart.pieChart(container, chart_data, options);
                }
            });
        }

        function weeklyNewMember() {
            $.ajax({
                url : "<?=$_VAR_PATH_BIZ ?>app/mng_statistics.php",
                data : "type=weekly_new_member",
                dataType:"json",
                success : function (data) {
                    console.log(data);
                    var join_date = [];
                    var pc_count = [];
                    var mobile_count = [];
                    var all_count = [];
                    $.each(data, function (key, value) {
                        join_date.push(value.date);
                        pc_count.push(value.pc_count);
                        mobile_count.push(value.mobile_count);
                        total = value.pc_count * 1 + value.mobile_count * 1;
                        all_count.push(total);
                    }) ;
                    $('#chart-area').empty();
                    var chart1_data = {
                        categories: join_date,
                        series: [
                            {
                                name: '전체 신규회원',
                                data: all_count
                            },
                            {
                                name: 'PC 신규회원',
                                data: pc_count
                            },
                            {
                                name: '모바일 신규회원',
                                data: mobile_count
                            }
                        ]
                    };
                    var chart1_options = {
                        chart: {
                            width: chart1_widthSize - 110, height: 320 /*,title: ''*/
                        },
                        yAxis: {
                            title: ''
                        },
                        xAxis: {
                            title: '',
                            min: 0
                        },
                        tooltip: {
                            suffix: ''/* ,grouped: true*/
                        },
                        series: {
                            showLabel: false, showDot: false
                        },
                        legend: {
                            showCheckbox: true, align: 'bottom'
                        }
                    };
                    tui.chart.columnChart(container1, chart1_data, chart1_options);
                }
            });

        }

        $("#today_visitor_graph").off().on('click', function () {
            getVisitorWeekly('today');
        });

        $("#weekend_visitor_graph").off().on('click', function () {
            getVisitorWeekly('weekend');
        });

        $("#lastweek_visitor_graph").off().on('click', function () {
            getVisitorWeekly('lastweek');
        });

        $.ajax({
            url : "<?=$_VAR_PATH_BIZ?>app/mng_statistics.php?type=board_total",
            dataType:"json",
            success: function (data) {
                $("#board_total").empty();
                $.each (data, function (key, value) {
                    $("#board_total").append(
                        '<tr>'+
                        '<td>'+value.dm_table+'</td>'+
                        '<td>'+value.write_count+'</td>'+
                        '<td>'+value.day_count+'</td>'+
                        '<td>'+value.day_comment_count+'</td>'+
                        '</tr>'
                    )

                });
            }
        });
    });
</script>