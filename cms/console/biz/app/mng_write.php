<?php
/**
 * Created by PhpStorm.
 * User: user21
 * Date: 2020-05-27
 * Time: 오후 4:38
 */

include "../../lib/lib.php";

$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";
$search_board =  isset($_REQUEST['search_board']) ? $_REQUEST['search_board'] : "";
$search_nm = isset($_REQUEST['search_nm']) ? trim($_REQUEST['search_nm']) : "";
$search_subject = isset($_REQUEST['search_subject']) ? trim($_REQUEST['search_subject']) : "";
$search_start_date = isset($_REQUEST['search_start_date']) ? trim($_REQUEST['search_start_date']) : "";
$search_end_date = isset($_REQUEST['search_end_date']) ? trim($_REQUEST['search_end_date']) : "";
$wr_id = isset($_REQUEST['wr_id']) ? trim($_REQUEST['wr_id']) : "";
$parent_id = isset($_REQUEST['parent_wr_id']) ? trim($_REQUEST['parent_wr_id']) : "";
$dm_table = isset($_REQUEST['dm_table']) ? trim($_REQUEST['dm_table']) : "";
$all = isset($_REQUEST['all']) ? trim($_REQUEST['all']) : "";
$com_wr_name = isset($_POST['com_wr_name']) ? trim($_POST['com_wr_name']) : "";
$com_wr_content = isset($_POST['com_wr_content']) ? trim($_POST['com_wr_content']) : "";
$chk_mb_id = getSession('chk_dm_id');
$wr_1 = isset($_REQUEST['wr_1']) ? trim($_REQUEST['wr_1']) : "";
$wr_2 = isset($_REQUEST['wr_2']) ? trim($_REQUEST['wr_2']) : "";
$wr_3 = isset($_REQUEST['wr_3']) ? trim($_REQUEST['wr_3']) : "";
$wr_4 = isset($_REQUEST['wr_4']) ? trim($_REQUEST['wr_4']) : "";
$wr_5 = isset($_REQUEST['wr_5']) ? trim($_REQUEST['wr_5']) : "";
$wr_6 = isset($_REQUEST['wr_6']) ? trim($_REQUEST['wr_6']) : "";
$wr_7 = isset($_REQUEST['wr_7']) ? trim($_REQUEST['wr_7']) : "";
$wr_8 = isset($_REQUEST['wr_8']) ? trim($_REQUEST['wr_8']) : "";
$wr_9 = isset($_REQUEST['wr_9']) ? trim($_REQUEST['wr_9']) : "";
$wr_10 = isset($_REQUEST['wr_10']) ? trim($_REQUEST['wr_10']) : "";
$ca_name = isset($_REQUEST['dm_category']) ? trim($_REQUEST['dm_category']) : "";
$wr_name = isset($_REQUEST['wr_name']) ? trim($_REQUEST['wr_name']) : "";
$wr_datetime = isset($_REQUEST['wr_datetime']) ? trim($_REQUEST['wr_datetime']) : date("Y-m-d H:i:s");

$db = new DBSQL();
$db2 = new DBSQL();
$db->DBconnect();
$db2->DBconnect();

$arData = array();
$arReturn = array();

$comment_list = array();

function getComment($wr_id, $table)
{
    $db = new DBSQL();
    $db->DBconnect();
    $row = array();
    global $comment_list;

    $query = "SELECT * FROM `{$table}` WHERE wr_parent = '".$wr_id."' and wr_is_comment = 1 order by wr_comment, wr_comment_reply ";

    $db->ExecSql($query, "S");

    if ($db->Num > 0)
    {
        while($row = $db->Fetch())
        {
            if ($row['wr_comment_reply'])
            {
                $row['wr_content'] = str_repeat("-", 4*strlen($row['wr_comment_reply']))."→".$row['wr_content'];
            }

            $row['wr_content'] .= "<div style='display:inline-block; float:right;'><a href='javascript:fnCommentReply({$row['wr_id']});' class='easyui-linkbutton reply' plain='true' iconCls='icon-add'>댓글</a>";
            $row['wr_content'] .= "<a href='javascript:modifyComment({$row['wr_id']});' class='easyui-linkbutton reply_modify' plain='true' iconCls='icon-save'>수정</a>";
            $row['wr_content'] .= "<a href='javascript:deleteComment({$row['wr_id']});' class='easyui-linkbutton reply_delete' plain='true' iconCls='icon-cut'>삭제</a></div>";
            
            $comment_list[] = $row;

            getComment($row['wr_id'], $table);
        }
    }

    return $comment_list;
}

if ($type == "select")
{

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? intval($_REQUEST['rows']) : 50;

    $search_type = isset($_REQUEST['search_type']) ? urldecode(trim($_REQUEST['search_type'])) : "";
    $search_value = isset($_REQUEST['search_value']) ? urldecode(trim($_REQUEST['search_value'])): "";
    $search_start_date = isset($_REQUEST['search_start_date']) ? urldecode(trim($_REQUEST['search_start_date'])): "";
    $search_end_date = isset($_REQUEST['search_end_date']) ? urldecode(trim($_REQUEST['search_end_date'])): "";

    $where = " WHERE (dm_table <> 'as' AND dm_table <> 'survey' AND dm_table <> 'operation_faq')";

    if ($search_board)
    {
        $where .= " AND dm_table = '".$search_board."'";
    }

    $query = "SELECT * FROM dm_board $where";

    $db->ExecSql($query, "S");

    $total_count = 0;

    $arTable = array();

    $dm_subject = "";

    $i = 0;

    while ($arData = $db->Fetch())
    {
        $arTable[$i]['dm_table'] = $arData['dm_table'];
        $arTable[$i]['dm_board_id'] = $arData['dm_id'];
        $i++;
    }

    $where1 = ' WHERE wr_is_comment = 0 ';

    if ($search_type != "") {
        if ($search_type == "all") {
            $where1 .= " AND (wr_subject LIKE '%".$search_value."%' OR wr_content LIKE '%".$search_value."%' OR wr_name LIKE '%".$search_value."%' OR wr_datetime LIKE '%".$search_value."%')";
        } else {
            $where1 .= " AND $search_type LIKE '%".$search_value."%'";
        }
    }

    if ($search_start_date) {
        $search_start_date = date("Y-m-d", strtotime($search_start_date));
        $where1 .= " AND `wr_datetime` >= '{$search_start_date}'";
    }

    if ($search_end_date) {
        $where1 .= " AND wr_datetime <= '".date("Y-m-d", strtotime($search_end_date))." 23:59:59'";
    }

    $i = 0;
    foreach ($arTable as $key => $value) {

        if ($i == 0)
        {
            $query = "SELECT 
            a.wr_subject as wr_subject, 
            a.wr_content as wr_content, 
            a.wr_name as wr_name, 
            a.wr_id as wr_id, 
            a.wr_datetime as wr_datetime, 
            a.wr_hit as wr_hit, 
            (SELECT dm_table FROM dm_board WHERE dm_table = '".$value['dm_table']."') as dm_table, 
            (SELECT dm_subject FROM dm_board WHERE dm_table = '".$value['dm_table']."') as dm_subject, 
            (SELECT dm_id FROM dm_board WHERE dm_table = '".$value['dm_table']."') as dm_board_id,
            (SELECT distinct(dm_uid) FROM dm_pages WHERE dm_board_id = '".$value['dm_board_id']."' AND dm_status = '1') as dm_page_uid
            FROM dm_write_".$value['dm_table']." as `a` ". $where1;
        }
        else
        {
            $query .= " UNION ( SELECT 
            a.wr_subject as wr_subject, 
            a.wr_content as wr_content,
            a.wr_name as wr_name,
            a.wr_id as wr_id, 
            a.wr_datetime as wr_datetime, 
            a.wr_hit as wr_hit, 
            (SELECT dm_table FROM dm_board WHERE dm_table = '".$value['dm_table']."') as dm_table, (SELECT dm_subject FROM dm_board WHERE dm_table = '".$value['dm_table']."') as dm_subject, 
            (SELECT dm_id FROM dm_board WHERE dm_table = '".$value['dm_table']."') as dm_board_id,
            (SELECT distinct(dm_uid) FROM dm_pages WHERE dm_board_id = '".$value['dm_board_id']."' AND dm_status = '1') as dm_page_uid
            FROM dm_write_".$value['dm_table'] . " as `a` ". $where1." )";
        }
        $countQuery = " SELECT count(*) as `count` FROM dm_write_".$value['dm_table'] . $where1;
        $db->ExecSql($countQuery, "S");
        $count = $db->Fetch();
        $total_count += $count['count'];
        $i++;
    }

    $limit = "limit ".$rows*($page-1).", ".$rows;

    $order = " ORDER BY b.wr_datetime desc";

    $query = "SELECT * FROM (" . $query .") as b ". $order ." ". $limit;

    $db2->ExecSql($query, "S");

    while ($rowData = $db2->Fetch())
    {
        $query = "SELECT * FROM dm_pages WHERE dm_board_id = '".$rowData['dm_board_id']."'";
        $db->ExecSql($query, "S");
        $pageInfo = $db->Fetch();
        if ($pageInfo) {
            $rowData['dm_url'] = $_VAR_WEB_URL."?contentId=".$pageInfo['dm_uid']."&command=view&wr_id=".$rowData['wr_id'];
        } else {
            $rowData['dm_url'] = "";
        }

        $arReturn[] = $rowData;
    }

    $arResult = array("result" => "success", "_return" => "", "total" => $total_count, "rows" => $arReturn);

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type == "select_board")
{

    $where = " WHERE (dm_table <> 'as' AND dm_table <> 'survey' AND dm_table <> 'operation_faq')";

    $query = "SELECT * FROM dm_board $where";

    $db->ExecSql($query, "S");

    if ($all) {
        $arALL = array( "dm_table" => "","dm_subject" => "전체","selected" => "true");
        array_push( $arReturn, $arALL );
    }

    $index = 0;
    while ($arData = $db->Fetch())
    {
//        if($index == 0)
//        {
//            $arData[ "selected" ] = "true";
//        }else
//        {
//            $arData[ "selected" ] = "";
//        }
        array_push( $arReturn, $arData );
    }
    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);
}

else if ($type == 'select_write')
{
    $mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : "";

    $query = "SELECT * FROM dm_board WHERE dm_table = '$dm_table'";
    $db->ExecSql($query, "S");

    if ($db->Num > 0)
    {
        $boardConfig = $db->Fetch();
    }

    $query = "SELECT * FROM dm_write_" . $dm_table . " WHERE wr_id = '".$wr_id."'";

    $db->ExecSql($query, "S");

    if ($db->Num > 0) {
        $row = $db->Fetch();
        $row['dm_table'] = $dm_table;
        $row['dm_subject'] = $boardConfig['dm_subject'];
        $row['wr_content'] = htmlspecialchars_decode($row['wr_content']);

        if ($row['wr_file']) {
            $file_array = array_filter(explode("|", $row['wr_file']));
            $file_ori_array = array_filter(explode("|", $row['wr_ori_file_name']));
        } else {
            $file_array = array();
            $file_ori_array = array();
        }

        $row['wr_file_array'] = $file_array;
        $row['wr_file_ori_array'] = $file_ori_array;

        $row['wr_comment_list'] = getComment($row['wr_id'], "dm_write_" . $dm_table);

        if ($mode == 'reply') {
            $row['wr_subject'] = "re: ".$row['wr_subject'];
        }

        $arResult = array("result" => "success", "_return" => "", "total" => "", "rows" => $row);
    } else {
        $arResult = array( "result" => "fail", "_return" => "","total" => "", "rows" => $arData);
    }

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);

}

else if ($type ==  'get_board_config')
{
    $query = "SELECT * FROM dm_board WHERE dm_table = '$dm_table'";
    $db->ExecSql($query, "S");

    if ($db->Num > 0)
    {
        $row = $db->Fetch();
        $row['wr_content'] = nl2br($row['wr_content']);
        $arResult = array("result" => "success", "_return" => "", "total" => "", "rows" => $row);
    }
    else
    {
        $arResult = array( "result" => "fail", "_return" => "","total" => "", "rows" => $arData);
    }

    echo json_encode($arResult, JSON_UNESCAPED_UNICODE);
}

else if ($type ==  'get_category_select')
{

    $query = "SELECT dm_category_list FROM dm_board WHERE dm_table = '$dm_table'";

    $db->ExecSql($query, "S");

    if ($all)
    {
        $arALL = array( "dm_code_value" => "","dm_code_text" => "전체","selected" => "true");
        array_push( $arReturn, $arALL );
    }

    $row = $db->Fetch();

    $category_list = $row['dm_category_list'];

    $category_list = explode(",", $category_list);

    if (count($category_list) > 0)
    {
        for ($i=0; $i<count($category_list); $i++)
        {
            $tempArray = array( "dm_code_value" => $category_list[$i],"dm_code_text" => $category_list[$i]);
            array_push( $arReturn, $tempArray );
        }

    }

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);
}

else if ($type == 'comment_update')
{
    $dm_table = "dm_write_".$dm_table;

    $query = "UPDATE {$dm_table} SET `wr_content` = '$com_wr_content', `wr_name` = '$com_wr_name' WHERE `wr_id` = '$wr_id'";

    $db->ExecSql($query, "I");

    $arReturn = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "댓글을 수정했습니다.", "objName" => $_POST['objName'] );

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);

}

else if ($type == 'comment_delete')
{
    $dm_table = "dm_write_".$dm_table;

    $query = "DELETE FROM {$dm_table} WHERE `wr_id` = '$wr_id'";

    $db->ExecSql($query, "I");

    $arReturn = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "댓글을 삭제했습니다.", "objName" => $_POST['objName'] );

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);
}

else if ($type == 'comment_insert' || $type == 'comment_reply')
{
    $query = "SELECT * FROM `dm_board` WHERE dm_table = '{$dm_table}'";

    $db->ExecSql($query, "S");

    $BBS_VAL = $db->Fetch();

    $dm_table = "dm_write_".$dm_table;

    $query = "SELECT * FROM {$dm_table} WHERE `wr_id` = '$parent_id' ";
    $db->ExecSql($query, "S");
    $is_secret = false;
    $bbsData = $db->Fetch();

    $option_array = $bbsData['wr_option'];

    $option_array = explode(",", $option_array);

    foreach ($option_array as $val)
    {
        if ($val == 'secret')
        {
            $is_secret = true;
        }
    }

    $wr_password = sql_password($wr_password);

    if ($is_secret)
    {
        if (!$wr_password)
        {
            $wr_password = $bbsData['wr_password'];
        }
    }

    if ($type == 'comment_reply')
    {
        $reply_len = strlen($bbsData['wr_comment_reply']) + 1;
        $tmp_comment = $bbsData['wr_comment'];

        if ($BBS_VAL['dm_reply_order']) {
            $begin_reply_char = 'A';
            $end_reply_char = 'Z';
            $reply_number = +1;
            $query = " select MAX(SUBSTRING(wr_comment_reply, $reply_len, 1)) as reply
                        from {$dm_table}
                        where wr_parent = '$wr_id'
                        and wr_comment = '$tmp_comment'
                        and SUBSTRING(wr_comment_reply, $reply_len, 1) <> '' ";
        }
        else
        {
            $begin_reply_char = 'Z';
            $end_reply_char = 'A';
            $reply_number = -1;
            $query = " select MIN(SUBSTRING(wr_comment_reply, $reply_len, 1)) as reply
                        from {$dm_table}
                        where wr_parent = '$wr_id'
                        and wr_comment = '$tmp_comment'
                        and SUBSTRING(wr_comment_reply, $reply_len, 1) <> '' ";
        }
        if ($bbsData['wr_comment_reply'])
            $query .= " and wr_comment_reply like '{$bbsData['wr_comment_reply']}%' ";

        $db->ExecSql($query, "S");

        $row = $db->Fetch();

        if (!$row['reply'])
            $reply_char = $begin_reply_char;
        else if ($row['reply'] == $end_reply_char) // A~Z은 26 입니다.
            $arReturn = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => '더 이상 답변하실 수 없습니다.\\n\\n답변은 26개 까지만 가능합니다.', "objName" => $_POST['objName'] );
        else
            $reply_char = chr(ord($row['reply']) + $reply_number);

        $tmp_comment_reply = $bbsData['wr_comment_reply'] . $reply_char;
    }


    $query = " insert into {$dm_table} (`ca_name`, `wr_num`, `wr_parent`, `wr_is_comment`, `wr_content`, `mb_id`, `wr_name`, `wr_password`, `wr_datetime`, `wr_ip`, `wr_1`, `wr_2`, `wr_3`, `wr_4`, `wr_5`, `wr_6`, `wr_7`, `wr_8`, `wr_9`, `wr_10`, `wr_comment_reply`, `wr_is_notice`, `wr_option`)
                VALUE ('".$bbsData['ca_name']."', '".$bbsData['wr_num']."', '".$bbsData['wr_id']."', 1, '".$com_wr_content."', '".$chk_mb_id."', '".$com_wr_name."', '".$wr_password."', now(), '".$_SERVER['REMOTE_ADDR']."', '".$wr_1."', '".$wr_2."', '".$wr_3."', '".$wr_4."',
                '".$wr_5."', '".$wr_6."', '".$wr_7."', '".$wr_8."', '".$wr_9."', '".$wr_10."', '".$tmp_comment_reply."', 0, '".$bbsData['wr_option']."')";

    $db->ExecSql($query, "I");

    $arReturn = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "댓글을 작성했습니다.", "objName" => $_POST['objName'] );

    echo json_encode($arReturn, JSON_UNESCAPED_UNICODE);


}

else if ($type == 'write' || $type == 'update' || $type == 'reply')
{
    $query = "SELECT * FROM `dm_board` WHERE dm_table = '{$dm_table}'";

    $db->ExecSql($query, "S");

    $BBS_VAL = $db->Fetch();

    $dm_table = "dm_write_".$dm_table;

    $wr_subject = '';
    if (isset($_POST['wr_subject'])) {
        $wr_subject = substr(trim($_POST['wr_subject']),0,255);
        $wr_subject = preg_replace("#[\\\]+$#", "", $wr_subject);
        $wr_subject = htmlspecialchars($wr_subject, ENT_QUOTES );
        $wr_subject = addslashes($wr_subject);
    }

    if ($wr_subject == '') {
        $arResult = array( "result" => "fail","_return" => "","total" => 0 , "rows" => 0, "notice" => "제목을 입력하세요", "objName" => $_POST['objName'] );
        echo json_encode( $arResult );
        exit;
    }

    $wr_content = '';

    if (isset($_POST['wr_content'])) {
        $wr_content = substr(trim($_POST['wr_content']),0,65536);
        $wr_content = preg_replace("#[\\\]+$#", "", $wr_content);
        $wr_content = htmlspecialchars($wr_content, ENT_QUOTES);
        $wr_content = addslashes($wr_content);

    }

    if ($wr_content == '') {
        $arResult = array( "result" => "fail","_return" => "","total" => 0 , "rows" => 0, "notice" => "내용을 입력하세요", "objName" => $_POST['objName'] );
        echo json_encode( $arResult );
        exit;
    }

    $wr_link1 = '';
    if (isset($_POST['wr_link1'])) {
        $wr_link1 = substr($_POST['wr_link1'],0,1000);
        $wr_link1 = trim(strip_tags($wr_link1));
        $wr_link1 = preg_replace("#[\\\]+$#", "", $wr_link1);
    }

    $wr_link2 = '';
    if (isset($_POST['wr_link2'])) {
        $wr_link2 = substr($_POST['wr_link2'],0,1000);
        $wr_link2 = trim(strip_tags($wr_link2));
        $wr_link2 = preg_replace("#[\\\]+$#", "", $wr_link2);
    }

    @mkdir($_VAR_WEB_DATA_PATH.'file/'.$dm_table, 0707);
    @chmod($_VAR_WEB_DATA_PATH.'file/'.$dm_table, 0707);

    $file_path = $_VAR_WEB_DATA_PATH.'file/'.$dm_table.'/';

    $upload_file_name = "";
    $upload_ori_file_name = "";

    $query = "SELECT * FROM {$dm_table} WHERE `wr_id` = '$wr_id'";
    $db->ExecSql($query, "S");
    $currentInfo = $db->Fetch();

    if ($currentInfo['wr_file']) {
        $file_array = explode("|", $currentInfo['wr_file']);
        $file_ori_array = explode("|", $currentInfo['wr_ori_file_name']);
    } else {
        $file_array = array();
        $file_ori_array = array();
    }

    $j = 0;
    for ($i=0; $i<$BBS_VAL['dm_upload_count']; $i++) {
        if(isset($_REQUEST['del_file'][$i]) && $_REQUEST['del_file'][$i]) {
            if (is_file($_VAR_WEB_DATA_PATH.'file/'.$file_array[$j])) {
                unlink($_VAR_WEB_DATA_PATH.'file/'.$file_array[$j]);
                $file_array[$j] = '';
                $file_ori_array[$j] = '';
            }
        }

        if ($_FILES['file']['name'][$i]) {
            if ($file_array[$j]) {
                if (is_file($_VAR_WEB_DATA_PATH.'file/'.$file_array[$j])) {
                    unlink($_VAR_WEB_DATA_PATH.'file/'.$file_array[$j]);
                    $file_array[$j] = '';
                    $file_ori_array[$j] = '';
                }
            }
            $uploadName = explode('.', $_FILES['file']['name'][$i]);
            $new_file_name = date("YmdHis")."_".hash("md5", $uploadName[0]).".".$uploadName[1];
            $ori_file_name = $_FILES['file']['name'][$i];

            if(!move_uploaded_file($_FILES['file']['tmp_name'][$i], $file_path.$new_file_name)) {
                $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "파일업로드에 실패했습니다.", "objName" => $_POST['objName'] );
                echo json_encode( $arResult );
                exit;
            } else {
                $file_array[$j] = $dm_table."/".$new_file_name;
                $file_ori_array[$j] = $ori_file_name;
            }
        }
        $j++;
    }

    $upload_file_name = implode("|", $file_array);
    $upload_ori_file_name = implode("|", $file_ori_array);

    $secret = '';
    if (isset($_POST['secret']) && $_POST['secret']) {
        if(preg_match('#secret#', strtolower($_POST['secret']), $matches))
            $secret = $matches[0];
    }

    // 외부에서 글을 등록할 수 있는 버그가 존재하므로 비밀글 무조건 사용일때는 관리자를 제외(공지)하고 무조건 비밀글로 등록
    if (!$is_admin && $BBS_VAL['dm_use_secret'] == 2) {
        $secret = 'secret';
    }

    $html = '';
    if (isset($_POST['html']) && $_POST['html']) {
        if(preg_match('#html(1|2)#', strtolower($_POST['html']), $matches))
            $html = $matches[0];
    }

    $mail = '';
    if (isset($_POST['mail']) && $_POST['mail']) {
        if(preg_match('#mail#', strtolower($_POST['mail']), $matches))
            $mail = $matches[0];
    }

    $notice = '0';
    if (isset($_POST['notice']) && $_POST['notice']) {
        $notice = $_POST['notice'];
    }

    if ($type == 'write' || $type == "reply") {
        // 가장 작은 번호를 얻어
        $query = " SELECT min(wr_num) as min_wr_num FROM `{$dm_table}` ";
        $db->ExecSql($query, "S");
        $row = $db->Fetch();
        // 가장 작은 번호에 1을 빼서 넘겨줌
        $wr_num = (int)($row['min_wr_num'] - 1);
        $wr_reply = '';

        $wr_password = sql_password($wr_password);
        $wr_option = $html.",".$secret.",".$mail;
        if ($type == 'reply')
        {
            $query = "SELECT * FROM {$dm_table} WHERE `wr_id` = '$wr_id'";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            $wr_num = $row['wr_num'];
            if (getSession('chk_dm_id')) {
                $wr_password = $row['wr_password'];
            }

            $wr_option = $row['wr_option'];

            // 게시글 배열 참조
            $reply_array = $row;

            // 최대 답변은 테이블에 잡아놓은 wr_reply 사이즈만큼만 가능합니다.
            if (strlen($reply_array['wr_reply']) == 10) {
                $arResult = array( "result" => "fail", "_return" => "","total" => $total_count , "rows" => $arData, "notice" => "게시물에 더이상 답변할 수 없습니다..", "objName" => $_POST['objName'] );
            }

            $reply_len = strlen($reply_array['wr_reply']) + 1;
            if ($BBS_VAL['dm_reply_order']) {
                $begin_reply_char = 'A';
                $end_reply_char = 'Z';
                $reply_number = +1;
                $sql = " select MAX(SUBSTRING(wr_reply, $reply_len, 1)) as reply from {$dm_table} where wr_num = '{$reply_array['wr_num']}' and SUBSTRING(wr_reply, {$reply_len}, 1) <> '' ";
            } else {
                $begin_reply_char = 'Z';
                $end_reply_char = 'A';
                $reply_number = -1;
                $sql = " select MIN(SUBSTRING(wr_reply, {$reply_len}, 1)) as reply from {$dm_table} where wr_num = '{$reply_array['wr_num']}' and SUBSTRING(wr_reply, {$reply_len}, 1) <> '' ";
            }

            if ($reply_array['wr_reply']) $sql .= " and wr_reply like '{$reply_array['wr_reply']}%' ";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();

            if (!$row['wr_reply']) {
                $reply_char = $begin_reply_char;
            } else if ($row['wr_reply'] == $end_reply_char) { // A~Z은 26 입니다.
//                    alert("더 이상 답변하실 수 없습니다.\\n답변은 26개 까지만 가능합니다.");
            } else {
                $reply_char = chr(ord($row['wr_reply']) + $reply_number);
            }

            $reply = $reply_array['wr_reply'] . $reply_char;
            $wr_reply = $reply;
        }

        $Query = "insert into ".$dm_table."(";
        $Query .= "wr_num, wr_reply, wr_parent, wr_is_comment, wr_comment, wr_comment_reply, ca_name, wr_option, wr_subject, wr_content, wr_seo_title, wr_link1, wr_link2, wr_link1_hit, wr_link2_hit, wr_hit, wr_good, wr_nogood, wr_name, wr_email, wr_homepage, wr_datetime, wr_file, wr_last, wr_ip, wr_facebook_user, wr_twitter_user, wr_1, wr_2, wr_3, wr_4, wr_5, wr_6, wr_7, wr_8, wr_9, wr_10, wr_is_notice, wr_ori_file_name, mb_id, wr_password) values(";
        $Query .= "'$wr_num', '$wr_reply', '$wr_parent', '$wr_is_comment', '0', '$wr_comment_reply', '$ca_name', '$wr_option', '$wr_subject', '$wr_content', '$wr_seo_title', '$wr_link1', '$wr_link2', '$wr_link1_hit', '$wr_link2_hit', '$wr_hit', '$wr_good', '$wr_nogood', '$wr_name', '$wr_email', '$wr_homepage', '".$wr_datetime."', '$upload_file_name', '$wr_last', '".$_SERVER['REMOTE_ADDR']."', '$wr_facebook_user', '$wr_twitter_user', '$wr_1', '$wr_2', '$wr_3', '$wr_4', '$wr_5', '$wr_6', '$wr_7', '$wr_8', '$wr_9', '$wr_10', '$notice' , '$upload_ori_file_name', '$chk_mb_id', '$wr_password')";

        $db->ExecSql($Query, "I");

        $wr_id = $db->insertId();

        // 자신 아이디에 UPDATE
        $query = " update {$dm_table} set wr_parent = '$wr_id' where wr_id = '$wr_id' ";
        $db->ExecSql($query, "I");

        $arResult = array( "result" => "success","_return" => $wr_id,"total" => $total_count , "rows" => $arData, "notice" => "글을 등록했습니다.", "objName" => $_POST['objName'] );

        echo json_encode( $arResult );
    }
    else if ($type == 'update')
    {
        $query = "UPDATE `{$dm_table}` SET
            `wr_is_notice`='".$notice."',`ca_name` = '$ca_name', `wr_file` = '$upload_file_name', `wr_ori_file_name` = '$upload_ori_file_name', `wr_option` = '$html,$secret,$mail',`wr_subject` = '$wr_subject',`wr_content` = '$wr_content',`wr_link1` = '$wr_link1',`wr_link2` = '$wr_link2',`wr_name` = '$wr_name', `wr_1` = '$wr_1', `wr_2` = '$wr_2', `wr_3` = '$wr_3', `wr_4` = '$wr_4', `wr_5` = '$wr_5', `wr_6` = '$wr_6', `wr_7` = '$wr_7', `wr_8` = '$wr_8', `wr_9` = '$wr_9', `wr_10` = '$wr_10', `wr_name` = '".$wr_name."'
            ,`wr_datetime` = '".$wr_datetime."' WHERE `wr_id` = '$wr_id'";

        $db->ExecSql($query, "I");

        $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "글을 수정했습니다.", "objName" => $_POST['objName'] );

        echo json_encode( $arResult, JSON_UNESCAPED_UNICODE );
    }
}

else if ($type == 'delete')
{
    $dm_table = 'dm_write_'.$dm_table;

    $query = "DELETE FROM `{$dm_table}` WHERE wr_id = '$wr_id'";

    $db->ExecSql($query, "I");

    $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "글을 삭제했습니다.", "objName" => $_POST['objName'] );

    echo json_encode( $arResult, JSON_UNESCAPED_UNICODE );
}

else if ($type == 'move_write') {
    $target_table = isset($_REQUEST['target_table']) ? $_REQUEST['target_table'] : "";
    $result = array();

    if (!$target_table) {
        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "타겟테이블이 지정되지 않았습니다.", "objName" => $_POST['objName'] );
        echo json_encode( $arResult, JSON_UNESCAPED_UNICODE );
        exit;
    }

    $dm_table = 'dm_write_'.$dm_table;
    $target_table = 'dm_write_'.$target_table;

    $query = " SELECT min(wr_num) as min_wr_num FROM `{$target_table}` ";
    $db->ExecSql($query, "S");
    $row = $db->Fetch();
    // 가장 작은 번호에 1을 빼서 넘겨줌
    $wr_num = (int)($row['min_wr_num'] - 1);

    $query = "SELECT * FROM {$dm_table} WHERE wr_id = '".$wr_id."'";
    $db->ExecSql($query, "S");
    $currentInfo = $db->Fetch();

    $query = "DESC $target_table";

    $db->ExecSql($query, "S");

    while($row = $db->Fetch()) {
        $result[] = $row;
    }

    $row_count = count($result);

    $query = "INSERT INTO $target_table ( ";

    foreach ($result as $key => $value) {
        if ($value['Field'] != 'wr_id') {
            if ($row_count == ($key+1)) {
                $query .= $value['Field'].")";
            } else {
                $query .= $value['Field'].",";
            }
        }
    }

    $query .= " VALUE (";

    foreach ($result as $key => $value) {
        if ($value['Field'] != 'wr_id') {
            if ($value['Field'] == 'wr_num') {
                $query .= "'".$wr_num."', ";
            } else {
                if ($row_count == ($key + 1)) {
                    $query .= "'" . $currentInfo[$value['Field']] . "')";
                } else {
                    $query .= "'" . $currentInfo[$value['Field']] . "',";
                }
            }
        }
    }

    $db->ExecSql($query, "I");

    $query = "DELETE FROM $dm_table WHERE wr_id = '".$currentInfo['wr_id']."'";

    $db->ExecSql($query, "I");

    $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "이동되었습니다.", "objName" => $_POST['objName'] );
    echo json_encode( $arResult, JSON_UNESCAPED_UNICODE );
    exit;

}