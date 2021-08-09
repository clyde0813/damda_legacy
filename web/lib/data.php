<?
$contentId = isset($_REQUEST['contentId']) ? $_REQUEST['contentId'] : "";

if ($contentId == "") {
    $Query = "select * from dm_pages where dm_main_content = '1' and dm_status = '1'";
} else {
    $Query = "select * from dm_pages where dm_uid='" . $contentId . "' and dm_status = '1'";
}


$db = new DBSQL();
$db->DBconnect();

$db->ExecSql($Query, "S");
$PAGE_VAL;
$LAYOUT_VAL;

if ($db->Num > 0) {
    $row = $db->Fetch();
    $PAGE_VAL = $row;
    //echo "타이틀:".$row["dm_title"]."<BR>";
    $page = $PAGE_VAL["dm_file_src"];

    $Query = "select * from dm_layout where dm_id=" . $row["dm_layout"];
    $db->ExecSql($Query, "S");
    if ($db->Num > 0) {
        $row = $db->Fetch();
        $LAYOUT_VAL = $row;
        //echo "레이아웃:".$LAYOUT_VAL["dm_layout_nm"]."<BR>";
    } else {
        echo "레이아웃 정보가 존재하지 않습니다.";
        exit;
    }

    //require($page);

} else {
    echo "컨텐츠가 존재하지 않습니다.";
    exit;
}

$domain = $_SERVER['SERVER_NAME'];
if (preg_match('/www/', $domain) == true) { // www 없을때
    $domain = str_replace("www.", "", $domain);
}

$query = "SELECT * FROM dm_config WHERE dm_url LIKE '%" . $domain . "%'";
$db->ExecSql($query, "S");
$CONFIG = $db->Fetch();

if ($CONFIG['dm_personal_image']) {
    $ogg_file = "http://" . $domain . "/diam/web/thema/" . $LAYOUT_VAL['dm_top_content'] . '/images/' . $CONFIG['dm_personal_image'];
}

$CONFIG['dm_private_text'] = htmlspecialchars_decode($CONFIG['dm_private_text']);
$CONFIG['dm_information_text'] = htmlspecialchars_decode($CONFIG['dm_information_text']);
$CONFIG['dm_policy_text'] = htmlspecialchars_decode($CONFIG['dm_policy_text']);

$query = "SELECT * FROM dm_member_config";
$db->ExecSql($query, "S");
$MEMBER_CONFIG = $db->Fetch();

$CONFIG['point_text'] = "포인트";

if (getSession("chk_dm_id")) {
    $query = "SELECT * FROM dm_member WHERE dm_id = '" . getSession("chk_dm_id") . "'";
    $db->ExecSql($query, "S");
    $MEMBER = $db->Fetch();
    $selectCode = selectCommonCode('1002');
    $MEMBER['level_text'] = $selectCode[$MEMBER['dm_level']];
    $query = "SELECT * FROM dm_group WHERE dm_group_id = '" . $MEMBER['dm_group_id'] . "'";
    $db->ExecSql($query, "S");
    $GROUP = $db->Fetch();
}

?>