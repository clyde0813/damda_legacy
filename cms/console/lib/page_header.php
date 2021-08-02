<!DOCTYPE html>
<html>
<head>
    <?
    include "../../lib/lib.php";

    if (!getSession("chk_dm_id")) {
        alert ("로그인 후 이용해주세요");
        echo "<script> top.location= '".$_VAR_PATH_CMS."index.html'; </script>";
    }
    ?>
    <title><?=$_TITLE?></title>

    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>jquery/jquery-1.4.3.min.js"></script>
    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>jquery/jquery-1.10.2.js"></script>

    <link rel="stylesheet" type="text/css" href="<?=$_VAR_PATH_JS?>easyui/themes/bootstrap/easyui.css">
    <link rel="stylesheet" type="text/css" href="<?=$_VAR_PATH_JS?>easyui/themes/icon.css">
    <link rel="stylesheet" type="text/css" href="<?=$_VAR_PATH_JS?>easyui/jquery-easyui-texteditor/texteditor.css">
    <link rel="stylesheet" type="text/css" href="<?=$_VAR_PATH_JS?>colorpicker/colorpicker.css">
    <link rel="stylesheet" type="text/css" href="<?=$_VAR_PATH_JS?>tui.chart/dist/tui-chart.min.css">
    <link rel="stylesheet" type="text/css" href="<?=$_VAR_PATH_CSS?>page.css">
    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>easyui/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>easyui/jquery-easyui-texteditor/jquery.texteditor.js"></script>
    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>colorpicker/colorpicker.js"></script>
    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>tui.chart/dist/tui-chart-all.min.js"></script>
    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>jquery.nicescroll.min.js"></script>
    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>common.js"></script>
    <script type="text/javascript" src="<?=$_VAR_PATH_JS?>base.js"></script>

    <meta http-equiv="Content-Type" charset="UTF-8">

    <SCRIPT LANGUAGE="JavaScript">
        var lib_url = '<?=$_VAR_PATH_LIB?>';

        document.onkeydown = trapRefresh;
        function trapRefresh()
        {
            if (event.keyCode == 116)
            {
                event.keyCode = 0;
                event.cancelBubble = true;
                event.returnValue = false;
                document.location.reload();
            }
        }
    </SCRIPT>


</head>
<body>
<?
//print_r2($_SERVER);
$db2 = new DBSQL();
$db2->DBconnect();

$query = "SELECT * FROM dm_access_admin_menu WHERE dm_link_url like '%".$_SERVER['SCRIPT_NAME']."%' ";

$db2->ExecSql($query, "S");

$current_page_info = $db2->Fetch();


?>