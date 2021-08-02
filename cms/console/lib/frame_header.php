<!DOCTYPE html>
<html>
<head>
    <?
    include "../lib/lib.php";

    if (!getSession("chk_dm_id")) {
        alert('로그인 후 이용해주세요', $_VAR_PATH_CMS."index.html");
    } else if (!getSession("is_admin")) {
		alert('접근권한이 없는 아이디 입니다.', $_VAR_PATH_CMS."index.html");
	}

    ?>
    <title><?=$_TITLE?></title>

    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>jquery/jquery-1.4.3.min.js"></script>
    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>jquery/jquery-1.10.2.js"></script>
    <link rel="stylesheet" type="text/css" href="<?=$_VAR_PATH_JS?>easyui/themes/bootstrap/easyui.css">
    <link rel="stylesheet" type="text/css" href="<?=$_VAR_PATH_JS?>easyui/themes/icon.css">
    <link rel="stylesheet" type="text/css" href="<?=$_VAR_PATH_JS?>easyui/jquery-easyui-texteditor/texteditor.css">
    <link rel="stylesheet" type="text/css" href="<?=$_VAR_PATH_JS?>tui.chart/dist/tui-chart.min.css">
    <link rel="stylesheet" type="text/css" href="<?=$_VAR_PATH_CSS?>admin.css">
    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>easyui/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>easyui/jquery-easyui-texteditor/jquery.texteditor.js"></script>
    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>jquery.nicescroll.min.js"></script>
    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>tui.chart/dist/tui-chart-all.min.js"></script>
    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>common.js"></script>
    <meta http-equiv="Content-Type" charset="UTF-8">

    <SCRIPT LANGUAGE="JavaScript">
        <!--
        var index = 0;
        function addTab(nm, src, close, icon, page)
        {
            index+=1;

            var nDocumentWidth = $(window).width();
            var nDocumentHeight = $(window).height()-100;
            var nScrollWidth = 0, nScrollHeight = 0;

            $('#m_frame').tabs('add',{
                id: index,
                title: nm,
                content: '<div data-options="closable:true,fit:true" class="wrap-tabs-content"><iframe id="tab_'+ index +'" name="tab_'+ index +'" scrolling="auto" frameborder="0"  src="' + src + '" style="width:100%;height:' + nDocumentHeight +'px"></iframe></div>',
                closable: close,
                iconCls: icon,
                fit:true
            });

        }

        function closeTab(idx) {
            var tab = $('#m_frame').tabs('getSelected');

            if (tab) {
                if (!idx) idx = $("#m_frame").tabs('getTabIndex', tab);
                $("#m_frame").tabs('close', idx);
            }
        }
        function fnLogout()
        {
            if(confirm("로그아웃 하시겠습니까?")) {
                location.href='<?=$_VAR_PATH_BIZ?>app/mng_login.php?type=logout';
            }
        }
        $(document).ready(function(){

        });

    </SCRIPT>
    <!-- <?
    if($ck_user_id == "")
    {
        echo "<script>$(window.top.location).attr('href','/jseng/ui/login.html')</script>";
        //exit();
    }
    ?> -->
    <script>
        document.onkeydown = trapRefresh;
        function trapRefresh()
        {
            if (event.keyCode == 116)
            {
                event.keyCode = 0;
                event.cancelBubble = true;
                event.returnValue = false;
                var p = $('#m_frame').tabs('getTab', 0);
                p.panel('refresh');
                // document.location.reload();
            }
        }
    </script>
</head>
<?

$member_level = getSession('chk_dm_level');

function getAdminMenu($parent_id)
{
    global $member_level;
    $db2 = new DBSQL();
    $db2->DBconnect();
    $menuArr = array();

    $query = "select * from dm_access_admin_menu WHERE dm_parent_id = '$parent_id' AND dm_access_level <= ".$member_level." AND dm_status = 1 order by dm_view_order asc";

    $db2->ExecSql($query, "S");

    while ($row = $db2->Fetch())
    {
        $menuArr[] = $row;
    }

    return $menuArr;
}

$menuArr = getAdminMenu(1);
?>
<body>

<div class="easyui-layout" fit="true">
    <div class="sidebar" data-options="region:'west',split:false,border:true,hideCollapsedContent:false,collapsed:false" title="사용자메뉴">
        <div class="easyui-accordion" data-options="multiple:false,border:false">

            <? foreach ($menuArr as $value) { ?>
                <div title="<?=$value['dm_view_title']?>" data-options="collapsed:true" >
                    <?
                    $childMenu = getAdminMenu($value['dm_id']);
                    foreach ($childMenu as $val) {
                        if ($val['dm_link_url'])
                        {
                            ?>
                            <p class="child" onclick="javascript:addTab('<?=$val['dm_view_title']?>','<?=$val['dm_link_url']?>',true,'<?=$val['dm_icon']?>','');"> - <?=$val['dm_nm']?></p>
                            <?
                        } else { ?>
                            <p> - <?=$val['dm_nm']?></p>
                        <? }
                        ?>
                    <? } ?>
                </div>
            <? } ?>

        </div>
    </div>
    <div class="header" data-options="region:'north'">
        <div class="logo">
            <a href="<?=$_VAR_PATH?>"><img src="../images/wlogo.png" /></a>
        </div>
        <div class="header_site">
            <script>
                $(function () {
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
            <select id="header_site" class="easyui-combobox" style="width:200px; margin-top:1px;" data-options="
                 url : '<?=$_VAR_PATH_BIZ ?>app/mng_site.php?type=select&mode=frame',
                 method:'get',
                 valueField:'dm_id',
                 textField:'dm_domain_nm',
                 panelHeight:'auto',
                 iconWidth:22,
                    icons:[{
                        iconCls:'icon-redo'
                    },{
                        iconCls:'icon-add'
                    }]
                 ">
            </select>
        </div>
        <div class="mnb">
            <div class="user">
                <a href="javascript:fnLogout();"><span><?=getSession('chk_dm_name')?></span>님  (<span><?=getSession('chk_dm_id')?></span>) <em>로그아웃</em></a>
            </div>
            <div class="site">
                <a class="homp" href="/diam/web/" target="_blank">내사이트</a>
                <a class="css" href="http://diam.kr/bbs/board.php?bo_table=maintenance" target="_blank">유지보수신청</a>
            </div>
        </div>
    </div>