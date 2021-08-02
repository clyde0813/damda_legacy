<?

//    include('../../lib/lib.php');
	include("./bbs.php");

	$command =  isset($_REQUEST['command']) ? $_REQUEST['command'] : "list";
	$wr_id =  isset($_REQUEST['wr_id']) ? $_REQUEST['wr_id'] : "";
    $chk_mb_id = getSession("chk_dm_id");

	$dm_table =  "dm_write_".$BBS_VAL["dm_table"];
	$dm_file_table =  "dm_board_file";

	$wr_num = isset($_REQUEST['txt_num']) ? $_REQUEST['txt_num'] : "";
	$wr_reply = isset($_REQUEST['txt_reply']) ? $_REQUEST['txt_reply'] : "";
	$wr_parent = isset($_REQUEST['txt_parent']) ? $_REQUEST['txt_parent'] : "";
	$wr_is_comment = isset($_REQUEST['txt_is_comment']) ? $_REQUEST['txt_is_comment'] : "";
	$wr_comment = isset($_REQUEST['txt_comment']) ? $_REQUEST['txt_comment'] : "";
	$wr_password = isset($_REQUEST['txt_password']) ? $_REQUEST['txt_password'] : "";
	$wr_comment_reply = isset($_REQUEST['txt_comment_reply']) ? $_REQUEST['txt_comment_reply'] : "";
	$ca_name = isset($_REQUEST['ca_name']) ? $_REQUEST['txt_ca_name'] : "";
	$wr_option = isset($_REQUEST['txt_option']) ? $_REQUEST['txt_option'] : "";
	$wr_subject = isset($_REQUEST['txt_subject']) ? $_REQUEST['txt_subject'] : "";
	$wr_content = isset($_REQUEST['txt_content']) ? $_REQUEST['txt_content'] : "";
	$wr_seo_title = isset($_REQUEST['txt_seo_title']) ? $_REQUEST['txt_seo_title'] : "";
	$wr_link1 = isset($_REQUEST['txt_link1']) ? $_REQUEST['txt_link1'] : "";
	$wr_link2 = isset($_REQUEST['txt_link2']) ? $_REQUEST['txt_link2'] : "";
	$wr_link1_hit = isset($_REQUEST['txt_link1_hit']) ? $_REQUEST['txt_link1_hit'] : "0";
	$wr_link2_hit = isset($_REQUEST['txt_link2_hit']) ? $_REQUEST['txt_link2_hit'] : "0";
	$wr_hit = isset($_REQUEST['txt_hit']) ? $_REQUEST['txt_hit'] : "0";
	$wr_good = isset($_REQUEST['txt_good']) ? $_REQUEST['txt_good'] : "0";
	$wr_nogood = isset($_REQUEST['txt_nogood']) ? $_REQUEST['txt_nogood'] : "0";
	$wr_name = isset($_REQUEST['txt_name']) ? $_REQUEST['txt_name'] : "";
	$wr_email = isset($_REQUEST['txt_email']) ? $_REQUEST['txt_email'] : "";
	$wr_homepage = isset($_REQUEST['txt_homepage']) ? $_REQUEST['txt_homepage'] : "";
	$wr_datetime = isset($_REQUEST['txt_datetime']) ? $_REQUEST['txt_datetime'] : "";
	$wr_file = isset($_REQUEST['txt_file']) ? $_REQUEST['txt_file'] : "";
	$wr_last = isset($_REQUEST['txt_last']) ? $_REQUEST['txt_last'] : "";
	$wr_ip = isset($_REQUEST['txt_ip']) ? $_REQUEST['txt_ip'] : "";
	$wr_facebook_user = isset($_REQUEST['txt_facebook_user']) ? $_REQUEST['txt_facebook_user'] : "";
	$wr_twitter_user = isset($_REQUEST['txt_twitter_user']) ? $_REQUEST['txt_twitter_user'] : "";
	$wr_1 = isset($_REQUEST['txt_1']) ? $_REQUEST['txt_1'] : "";
	$wr_2 = isset($_REQUEST['txt_2']) ? $_REQUEST['txt_2'] : "";
	$wr_3 = isset($_REQUEST['txt_3']) ? $_REQUEST['txt_3'] : "";
	$wr_4 = isset($_REQUEST['txt_4']) ? $_REQUEST['txt_4'] : "";
	$wr_5 = isset($_REQUEST['txt_5']) ? $_REQUEST['txt_5'] : "";
	$wr_6 = isset($_REQUEST['txt_6']) ? $_REQUEST['txt_6'] : "";
	$wr_7 = isset($_REQUEST['txt_7']) ? $_REQUEST['txt_7'] : "";
	$wr_8 = isset($_REQUEST['txt_8']) ? $_REQUEST['txt_8'] : "";
	$wr_9 = isset($_REQUEST['txt_9']) ? $_REQUEST['txt_9'] : "";
	$wr_10 = isset($_REQUEST['txt_10']) ? $_REQUEST['txt_10'] : "";
    $mb_id = isset($_REQUEST['mb_id']) ? $_REQUEST['mb_id'] : "";

    $dm_append_1 = isset($_REQUEST['dm_append_1']) ? $_REQUEST['dm_append_1'] : "";
    $dm_append_2 = isset($_REQUEST['dm_append_2']) ? $_REQUEST['dm_append_2'] : "";
    $dm_append_3 = isset($_REQUEST['dm_append_3']) ? $_REQUEST['dm_append_3'] : "";
    $dm_append_4 = isset($_REQUEST['dm_append_4']) ? $_REQUEST['dm_append_4'] : "";
    $dm_append_5 = isset($_REQUEST['dm_append_5']) ? $_REQUEST['dm_append_5'] : "";
    $dm_append_6 = isset($_REQUEST['dm_append_6']) ? $_REQUEST['dm_append_6'] : "";
    $dm_append_7 = isset($_REQUEST['dm_append_7']) ? $_REQUEST['dm_append_7'] : "";
    $dm_append_8 = isset($_REQUEST['dm_append_8']) ? $_REQUEST['dm_append_8'] : "";
    $dm_append_9 = isset($_REQUEST['dm_append_9']) ? $_REQUEST['dm_append_9'] : "";
    $dm_append_10 = isset($_REQUEST['dm_append_10']) ? $_REQUEST['dm_append_10'] : "";
    $dm_append_11 = isset($_REQUEST['dm_append_11']) ? $_REQUEST['dm_append_11'] : "";
    $dm_append_12 = isset($_REQUEST['dm_append_12']) ? $_REQUEST['dm_append_12'] : "";
    $dm_append_13 = isset($_REQUEST['dm_append_13']) ? $_REQUEST['dm_append_13'] : "";
    $dm_append_14 = isset($_REQUEST['dm_append_14']) ? $_REQUEST['dm_append_14'] : "";
    $dm_append_15 = isset($_REQUEST['dm_append_15']) ? $_REQUEST['dm_append_15'] : "";
    $dm_append_16 = isset($_REQUEST['dm_append_16']) ? $_REQUEST['dm_append_16'] : "";
    $dm_append_17 = isset($_REQUEST['dm_append_17']) ? $_REQUEST['dm_append_17'] : "";
    $dm_append_18 = isset($_REQUEST['dm_append_18']) ? $_REQUEST['dm_append_18'] : "";
    $dm_append_19 = isset($_REQUEST['dm_append_19']) ? $_REQUEST['dm_append_19'] : "";

	$del_file = isset($_REQUEST['del_file']) ? $_REQUEST['del_file'] : "";

    if($command == "write" || $command == "update" || $command == "reply")
	{
	    //비회원 글쓰기일 경우
        if (!$is_admin) {
            if (!$is_board_admin) {
                if (!$BBS_VAL['dm_write_level'])
                {
                    if ($command == "update") {
                        $query = "SELECT * FROM {$dm_table} WHERE `wr_id` = '$wr_id'";
                        $db->ExecSql($query, "S");
                        $row = $db->Fetch();
                        if ($row['wr_password']){
                            if ($row['wr_password'] != sql_password($wr_password)) {
                                $arResult = array( "result" => "fail","_return" => "","total" => 0 , "rows" => 0, "notice" => "비밀번호가 맞지않습니다.", "objName" => $_POST['objName'] );
                                echo json_encode( $arResult );
                                exit;
                            }
                        }
                    }
                }
            }
        }

        if($BBS_VAL['dm_use_category']) {
            $ca_name = trim($_POST['ca_name']);
            if(!isset($ca_name) && $ca_name =="") {
                $arResult = array( "result" => "fail","_return" => "","total" => 0 , "rows" => 0, "notice" => "분류를 선택해주세요", "objName" => $_POST['objName'] );
                echo json_encode( $arResult );
                exit;
            } else {
                $categories = array_map('trim', explode(",", $BBS_VAL['dm_category_list']));
                if(!empty($categories) && !in_array($ca_name, $categories)) {
                    $arResult = array( "result" => "fail","_return" => "","total" => 0 , "rows" => 0, "notice" => "분류를 올바르게 입력하세요.", "objName" => $_POST['objName'] );
                    echo json_encode( $arResult );
                    exit;
                }
                if(empty($categories))
                    $ca_name = '';
            }
        } else {
            $ca_name = '';
        }

        $wr_subject = '';
        if (isset($_POST['txt_subject'])) {
            $wr_subject = substr(trim($_POST['txt_subject']),0,255);
            $wr_subject = preg_replace("#[\\\]+$#", "", $wr_subject);
            $wr_subject = htmlspecialchars($wr_subject, ENT_QUOTES );
        }

        if ($wr_subject == '') {
            $arResult = array( "result" => "fail","_return" => "","total" => 0 , "rows" => 0, "notice" => "제목을 입력하세요", "objName" => $_POST['objName'] );
            echo json_encode( $arResult );
            exit;
        }

        $wr_content = '';

        if (isset($_POST['txt_content'])) {
            $wr_content = substr(trim($_POST['txt_content']),0,65536);
            $wr_content = preg_replace("#[\\\]+$#", "", $wr_content);
            $wr_content = addslashes($wr_content);
//            $wr_content = htmlspecialchars($wr_content,ENT_QUOTES);
        }

        if ($wr_content == '') {
            $arResult = array( "result" => "fail","_return" => "","total" => 0 , "rows" => 0, "notice" => "내용을 입력하세요", "objName" => $_POST['objName'] );
            echo json_encode( $arResult );
            exit;
        }

        $wr_link1 = '';
        if (isset($_POST['txt_link1'])) {
            $wr_link1 = substr($_POST['txt_link1'],0,1000);
            $wr_link1 = trim(strip_tags($wr_link1));
            $wr_link1 = preg_replace("#[\\\]+$#", "", $wr_link1);
        }

        $wr_link2 = '';
        if (isset($_POST['txt_link2'])) {
            $wr_link2 = substr($_POST['txt_link2'],0,1000);
            $wr_link2 = trim(strip_tags($wr_link2));
            $wr_link2 = preg_replace("#[\\\]+$#", "", $wr_link2);
        }

        @mkdir($_VAR_PATH_WEB_DATA.'file/'.$dm_table, 0707);
        @chmod($_VAR_PATH_WEB_DATA.'file/'.$dm_table, 0707);

        $file_path = $_VAR_PATH_WEB_DATA.'file/'.$dm_table.'/';

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
        for ($i=1; $i<=$BBS_VAL['dm_upload_count']; $i++) {
            if(isset($_REQUEST['del_file'][$i]) && $_REQUEST['del_file'][$i]) {
                if (is_file($_VAR_PATH_WEB_DATA.'file/'.$file_array[$j])) {
                    unlink($_VAR_PATH_WEB_DATA.'file/'.$file_array[$j]);
                    $file_array[$j] = '';
                    $file_ori_array[$j] = '';
                }
            }

            if ($_FILES['ex_filename']['name'][$i]) {
                if ($file_array[$j]) {
                    if (is_file($_VAR_PATH_WEB_DATA.'file/'.$file_array[$j])) {
                        unlink($_VAR_PATH_WEB_DATA.'file/'.$file_array[$j]);
                        $file_array[$j] = '';
                        $file_ori_array[$j] = '';
                    }
                }
                $uploadName = explode('.', $_FILES['ex_filename']['name'][$i]);
                $new_file_name = date("YmdHis")."_".hash("md5", $uploadName[0]).".".$uploadName[1];
                $ori_file_name = $_FILES['ex_filename']['name'][$i];

                if(!move_uploaded_file($_FILES['ex_filename']['tmp_name'][$i], $file_path.$new_file_name)) {
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

        // 090710
//        if (substr_count($wr_content, '&#') > 50) {
//            alert('내용에 올바르지 않은 코드가 다수 포함되어 있습니다.');
//            exit;
//        }

        // 외부에서 글을 등록할 수 있는 버그가 존재하므로 비밀글은 사용일 경우에만 가능해야 함
//        if (!$is_admin && !$BBS_VAL['bo_use_secret'] && (stripos($_POST['html'], 'secret') !== false || stripos($_POST['secret'], 'secret') !== false || stripos($_POST['mail'], 'secret') !== false)) {
//            alert('비밀글 미사용 게시판 이므로 비밀글로 등록할 수 없습니다.');
//            exit;
//        }

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

        for ($i=1; $i<=10; $i++) {
            $var = "txt_$i";
            $$var = "";
            if (isset($_POST['txt_'.$i]) && settype($_POST['txt_'.$i], 'string')) {
                $$var = trim($_POST['txt_'.$i]);
            }
        }

        if ($command == 'write' || $command == "reply") {
            // 가장 작은 번호를 얻어
            $query = " SELECT min(wr_num) as min_wr_num FROM `{$dm_table}` ";
            $db->ExecSql($query, "S");
            $row = $db->Fetch();
            // 가장 작은 번호에 1을 빼서 넘겨줌
            $wr_num = (int)($row['min_wr_num'] - 1);
            $wr_reply = '';

            $wr_password = sql_password($wr_password);
            $wr_option = $html.",".$secret.",".$mail;
            if ($command == 'reply')
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

            $append_field_query = '';
            $append_value_query = '';

            for ($i=1; $i<=19; $i++) {
                if (!is_null(${"dm_append_".$i}) && ${"dm_append_".$i} != "") {
                    $append_field_query .= ", dm_append_".$i."";
                    $append_value_query .= ", '".addslashes(${"dm_append_".$i})."'";
                }
            }

            $Query = "insert into ".$dm_table."(";
            $Query .= "wr_num, wr_reply, wr_parent, wr_is_comment, wr_comment, wr_comment_reply, ca_name, wr_option, wr_subject, wr_content, wr_seo_title, wr_link1, wr_link2, wr_link1_hit, wr_link2_hit, wr_hit, wr_good, wr_nogood, wr_name, wr_email, wr_homepage, wr_datetime, wr_file, wr_last, wr_ip, wr_facebook_user, wr_twitter_user, wr_1, wr_2, wr_3, wr_4, wr_5, wr_6, wr_7, wr_8, wr_9, wr_10, wr_is_notice, wr_ori_file_name, mb_id, wr_password";
            $Query .= $append_field_query;
            $Query .= ") values(";
            $Query .= "'$wr_num', '$wr_reply', '$wr_parent', '$wr_is_comment', '0', '$wr_comment_reply', '$ca_name', '$wr_option', '$wr_subject', '$wr_content', '$wr_seo_title', '$wr_link1', '$wr_link2', '$wr_link1_hit', '$wr_link2_hit', '$wr_hit', '$wr_good', '$wr_nogood', '$wr_name', '$wr_email', '$wr_homepage', now(), '$upload_file_name', '$wr_last', '".$_SERVER['REMOTE_ADDR']."', '$wr_facebook_user', '$wr_twitter_user', '$wr_1', '$wr_2', '$wr_3', '$wr_4', '$wr_5', '$wr_6', '$wr_7', '$wr_8', '$wr_9', '$wr_10', '$notice' , '$upload_ori_file_name', '$chk_mb_id', '$wr_password'";
            $Query .= $append_value_query;
            $Query .= ")";

            $db->ExecSql($Query, "I");

            if($db->Num > 0)
            {
//                if(!$wr_id) $wr_id = $db->insertId();
                $wr_id = $db->insertId();

                // 자신 아이디에 UPDATE
                $query = " update {$dm_table} set wr_parent = '$wr_id' where wr_id = '$wr_id' ";
                $db->ExecSql($query, "I");

                $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "글을 등록했습니다.", "objName" => $_POST['objName'] );

                if ($BBS_VAL['dm_write_point'] && getSession("chk_dm_id")) {
                    $current_point = getMemberPoint();

                    if ($BBS_VAL['dm_write_point_type'] == 1) {
                        $point = $current_point - $BBS_VAL['dm_write_point'];
                        $kind = 0;
                    } else {
                        $point = $current_point + $BBS_VAL['dm_write_point'];
                        $kind = 1;
                        setExpCount(getSession("chk_dm_id"), 'point', $BBS_VAL['dm_write_point']);
                    }
                    insert_point("2", $BBS_VAL['dm_write_point'], $BBS_VAL['dm_table'], $wr_id, $point, "", $kind);
                }
                setExpCount(getSession("chk_dm_id"), 'write');
            }else
            {
                $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => $arNoticeData, "objName" => $_POST['objName'] );
            }
            echo json_encode( $arResult );
        }
        else if ($command == 'update')
        {

            $append_query = '';

            for ($i=1; $i<=19; $i++) {
                if (!is_null(${"dm_append_".$i}) && ${"dm_append_".$i} != "") {
                    $append_query .= ", dm_append_".$i." = '".addslashes(${"dm_append_".$i})."'";
                }
            }

            $query = "UPDATE `{$dm_table}` SET
            `wr_is_notice`='".$notice."',`ca_name` = '$ca_name', `wr_file` = '$upload_file_name', `wr_ori_file_name` = '$upload_ori_file_name', `wr_option` = '$html,$secret,$mail',
            `wr_subject` = '$wr_subject',`wr_content` = '$wr_content',`wr_link1` = '$wr_link1',`wr_link2` = '$wr_link2',`wr_name` = '$wr_name',
            `wr_homepage` = '".$wr_homepage."', 
            `wr_1` = '$wr_1', `wr_2` = '$wr_2', `wr_3` = '$wr_3', `wr_4` = '$wr_4', `wr_5` = '$wr_5', `wr_6` = '$wr_6', `wr_7` = '$wr_7', `wr_8` = '$wr_8', `wr_9` = '$wr_9', `wr_10` = '$wr_10'
            $append_query
            WHERE `wr_id` = '$wr_id'";

            $db->ExecSql($query, "I");

            if($db->Num > 0)
            {
                $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "글을 수정했습니다.", "objName" => $_POST['objName'] );
            }else
            {
                $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => $arNoticeData, "objName" => $_POST['objName'] );
            }
            echo json_encode( $arResult );
        }
	}

	else if($command == "delete")
	{

        $query = "SELECT * FROM `$dm_table` WHERE `wr_id` = '$wr_id'";

        $db->ExecSql($query, "S");
        $row = $db->Fetch();

        if ($row['mb_id']) {
            if (!$is_board_admin && !$is_admin) {
                if (getSession("chk_dm_id") != $row['mb_id']) {
                    $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "잘못된 접근입니다.", "objName" => $_POST['objName'] );
                    echo json_encode($arResult);
                    exit;
                }
            }
        } else {
            if (!$is_board_admin && !$is_admin) {
                $token = md5($row['wr_datetime']."dm");
                $compare = getSession("ss_view_".$row['wr_id']);
                if ($token != $compare) {
                    $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "잘못된 접근입니다.", "objName" => $_POST['objName'] );
                    echo json_encode($arResult);
                    exit;
                }
            }
        }

//        //파일삭제
//        $file_array = explode("|", $row['wr_file']);
//
//        foreach ($file_array as $value) {
//            if (is_file($_VAR_PATH_WEB_DATA."file/".$value)) {
//                unlink($_VAR_PATH_WEB_DATA."file/".$value);
//            }
//        }


        $query = "insert into {$dm_table}(";
        $query .= "wr_id, wr_num, wr_reply, wr_parent, wr_is_comment, wr_comment, wr_comment_reply, ca_name, wr_option, wr_subject, wr_content, wr_seo_title, wr_link1, wr_link2, wr_link1_hit, wr_link2_hit, wr_hit, wr_good, wr_nogood, wr_name, wr_email,
 wr_homepage, wr_datetime, wr_file, wr_last, wr_ip, wr_facebook_user, wr_twitter_user, wr_1, wr_2, wr_3, wr_4, wr_5, wr_6, wr_7, wr_8, wr_9, wr_10, wr_is_notice, wr_ori_file_name, mb_id, wr_password) values(";
        $query .= "'{$row['wr_id']}', '{$row['wr_num']}', '{$row['wr_reply']}', '{$row['wr_parent']}', '{$row['wr_is_comment']}', '{$row['wr_comment']}', '{$row['wr_comment_reply']}', 
'{$row['ca_name']}', '{$row['wr_option']}', '{$row['wr_subject']}', '{$row['wr_content']}', '{$row['wr_seo_title']}', '{$row['wr_link1']}', '{$row['wr_link2']}',
 '{$row['wr_link1_hit']}', '{$row['wr_link2_hit']}', '{$row['wr_hit']}', '{$row['wr_good']}', '{$row['wr_nogood']}',
 '{$row['wr_name']}', '{$row['wr_email']}', '{$row['wr_homepage']}', '{$row['wr_datetime']}', '{$row['wr_file']}', '{$row['wr_last']}', '{$row['wr_ip']}',
  '{$row['wr_facebook_user']}', '{$row['wr_twitter_user']}', '{$row['wr_1']}', '{$row['wr_2']}', '{$row['wr_3']}', '{$row['wr_4']}','{$row['wr_5']}',
  '{$row['wr_6']}', '{$row['wr_7']}', '{$row['wr_8']}', '{$row['wr_9']}', '{$row['wr_10']}', '{$row['wr_is_notice']}', '{$row['wr_ori_file_name']}', '{$row['mb_id']}' , '{$row['wr_password']}')";

        insert_board_log($command, $dm_table, $query, $row);

        if ($BBS_VAL['dm_reply_delete_type'] == "both" && $row['dm_reply'] == "") {
            $query = "DELETE FROM {$dm_table} WHERE wr_num = '".$row['wr_num']."'";
            $db->ExecSql($query, "I");
        }

        $query = "DELETE FROM `{$dm_table}` WHERE `wr_id` = '$wr_id'";

        $db->ExecSql($query, "I");

        if($db->Num > 0)
        {
            $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "글을 삭제했습니다.", "objName" => $_POST['objName'] );
        }else
        {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => $arNoticeData, "objName" => $_POST['objName'] );
        }
        echo json_encode( $arResult );
	}
    else if($command == "comment")
    {
        $is_comment_write = getAuth1($BBS_VAL['dm_auth_type'], $member_level, $BBS_VAL['dm_comment_level'], $MEMBER['dm_group_id'], $comment_group);

        if (!$is_comment_write) {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "댓글을 쓸 권한이 없습니다.", "objName" => $_POST['objName'] );
            echo json_encode($arResult);
            exit;
        }

        $view_token = getSession("view_token");
        setSession("view_token", "");

        $query = "SELECT * FROM {$dm_table} WHERE `wr_id` = '$view_token' ";
        $db->ExecSql($query, "S");

        $wr_name = isset($_POST['comment_name']) ? trim($_POST['comment_name']) : "";
        $wr_password = isset($_POST['comment_password']) ? trim($_POST['comment_password']) : "";
        $wr_comment = isset($_POST['wr_comment']) ? trim($_POST['wr_comment']) : "";
        $wr_comment = addslashes($wr_comment);
        $wr_secret = isset($_POST['comment_secret']) ? trim($_POST['comment_secret']) : "";
		
        $mode = isset($_POST['mode']) ? trim($_POST['mode']) : "";
        $is_secret = false;

        if ($db->Num > 0) {
            $bbsData = $db->Fetch();

            // 포인트 체크 후 소모 210118 //
            //관리자가 아닐때
            if ($BBS_VAL['dm_comment_point'] && (!$is_admin || $is_board_admin) && getSession("chk_dm_id")) {
                $current_point = getMemberPoint(); //현재포인트를 조회해서
                $temp = 4;
                //차감타입이라면
                if ($BBS_VAL['dm_comment_point_type'] == 1) {
                    //현재포인트보다 작은경우
                    if ($BBS_VAL['dm_comment_point'] > $current_point) {
                        $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "댓글을 쓸 포인트가 부족합니다.", "objName" => $_POST['objName'] );
                        echo json_encode($arResult);
                        exit;
                    } else {
                        //현재 포인트보다 큰경우
                        $point = $current_point - $BBS_VAL['dm_read_point'];
                        $kind = 0;
                        insert_point($temp, $BBS_VAL['dm_comment_point'], $BBS_VAL['dm_table'], $bbsData['wr_id'], $point, "", $kind);
                    }
                } else {
                    //증가 타입이라면
                    if ($bbsData['mb_id'] != $MEMBER["dm_id"]) {
                        // 내 글이 아닐 때
                        $point = $current_point + $BBS_VAL['dm_comment_point'];
                        $kind = 1;
                        setExpCount($chk_mb_id, 'point', $BBS_VAL['dm_comment_point']);
                        insert_point($temp, $BBS_VAL['dm_comment_point'], $BBS_VAL['dm_table'], $bbsData['wr_id'], $point, "", $kind);
                    }
                }
            }
			
			if ($wr_password != "") {
				$wr_password = sql_password($wr_password);
			}

            if ($mode)
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
                    alert('더 이상 답변하실 수 없습니다.\\n\\n답변은 26개 까지만 가능합니다.');
                else
                    $reply_char = chr(ord($row['reply']) + $reply_number);

                $tmp_comment_reply = $bbsData['wr_comment_reply'] . $reply_char;
            }

            if (!$wr_id) {
                $query = " insert into {$dm_table} (`ca_name`, `wr_num`, `wr_parent`, `wr_is_comment`, `wr_content`, `mb_id`, `wr_name`, `wr_password`, `wr_datetime`, `wr_ip`, `wr_1`, `wr_2`, `wr_3`, `wr_4`, `wr_5`, `wr_6`, `wr_7`, `wr_8`, `wr_9`, `wr_10`, `wr_comment_reply`, `wr_is_notice`, `wr_option`)
                VALUE ('".$bbsData['ca_name']."', '".$bbsData['wr_num']."', '".$bbsData['wr_id']."', 1, '".$wr_comment."', '".$chk_mb_id."', '".$wr_name."', '".$wr_password."', now(), '".$_SERVER['REMOTE_ADDR']."', '".$wr_1."', '".$wr_2."', '".$wr_3."', '".$wr_4."',
                '".$wr_5."', '".$wr_6."', '".$wr_7."', '".$wr_8."', '".$wr_9."', '".$wr_10."', '".$tmp_comment_reply."', 0, '".$wr_secret."')";

                setExpCount($chk_mb_id, 'comment');
            } else {
                $query = "SELECT * FROM {$dm_table} WHERE wr_id = '".$wr_id."'";
                $db->ExecSql($query, "S");
                $commentInfo = $db->Fetch();

                if ($is_admin || $is_board_admin || $commentInfo['mb_id'] == $member_id)
                {
                    if (!$is_admin && !$is_board_admin && $commentInfo['mb_id'] != $member_id) {
                        $token = md5($commentInfo['wr_datetime']."dm");
                        $compare = getSession("ss_view_".$wr_id);
                        if ($token != $compare) {
                            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "잘못된 접근입니다.", "objName" => $_POST['objName'] );
                            echo json_encode($arResult);
                            exit;
                        } else {
                            sessionUnset("ss_view_".$wr_id);
                        }
                    }
                    $query = "UPDATE {$dm_table} SET `wr_content` = '$wr_comment', `wr_name` = '$wr_name', `wr_password` = '$wr_password' WHERE `wr_id` = '$wr_id'";
                } else {
                    $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "정상적으로 이용해주세요", "objName" => $_POST['objName'] );
                    echo json_encode( $arResult );
                    exit;
                }
            }

            $db->ExecSql($query, "I");

            if ($db->Num > 0) {
                $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "댓글을 작성했습니다.", "objName" => $_POST['objName'] );
            } else {
                $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "댓글 저장 중 오류", "objName" => $_POST['objName'] );
            }

        } else {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "게시글이 없습니다.", "objName" => $_POST['objName'] );
        }

        echo json_encode( $arResult );
    }
    else if ($command == 'delete_comment')
    {
        $query = "SELECT * FROM {$dm_table} WHERE wr_id = '".$wr_id."'";
        $db->ExecSql($query, "S");
        $bbsData = $db->Fetch();
		
		if (!$is_admin && !$is_board_admin && $member_id != $bbsData['mb_id']) {
            $token = md5($bbsData['wr_datetime']."dm");
            $compare = getSession("ss_view_".$wr_id);
            if ($token != $compare) {
                $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "잘못된 접근입니다.", "objName" => $_POST['objName'] );
                echo json_encode($arResult);
                exit;
            } else {
                sessionUnset("ss_view_".$wr_id);
            }
        }

        if (($is_admin || $is_board_admin) || $member_id == $bbsData['mb_id']) {
            $query = "DELETE FROM $dm_table WHERE `wr_id` = '".$wr_id."'";

            $db->ExecSql($query, "I");

            if ($db-> Num > 0) {
                $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "댓글을 삭제했습니다.", "objName" => $_POST['objName'] );
            } else {
                $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "삭제에 실패했습니다.", "objName" => $_POST['objName'] );
            }
        } else {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "잘못된접근입니다.", "objName" => $_POST['objName'] );
        }

        echo json_encode( $arResult );
    }
    else if ($command == 'chk_password')
    {
        $chk_pass = isset($_POST['chk_pass']) ? trim($_POST['chk_pass']) : "";
        if (!$chk_pass) {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "비밀번호를 입력해주세요.", "objName" => $_POST['objName'] );
            echo json_encode( $arResult );
            exit;
        }

        $query = "SELECT * FROM {$dm_table} WHERE wr_id = '$wr_id'";
        $db->ExecSql($query, "S");
        $bbsData = $db->Fetch();

        if ($bbsData['wr_password'] == sql_password($chk_pass)) {
            $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "비밀번호를 확인했습니다.", "objName" => $_POST['objName'] );
            $token = md5($bbsData['wr_datetime']."dm");
            setSession("ss_view_".$wr_id, $token);
        } else {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "비밀번호가 틀렸습니다.", "objName" => $_POST['objName'] );
        }

        echo json_encode( $arResult );

    }
    else if ($command == 'set_view_token')
    {
        setSession("view_token", $_REQUEST['wr_id']);
        $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "", "objName" => $_POST['objName'] );
        echo json_encode( $arResult );
    }

    else if ($command == 'get_write')
    {
        $table = isset($_REQUEST['dm_table']) ? $_REQUEST['dm_table'] : "";
        $query = "SELECT * FROM dm_write_{$table} WHERE wr_id = '$wr_id'";

        $db->ExecSql($query, "S");

        $arResult = $db->Fetch();

        if ($arResult['mb_id'] != getSession("chk_dm_id")) {
            $arResult = null;
        }

//        $arResult['wr_content'] = nl2br($arResult['wr_content']);

        echo json_encode( $arResult );
    }

    else if($command == "delete_faq")
    {
        $dm_table = isset($_REQUEST['dm_table']) ? $_REQUEST['dm_table'] : "";

        $query = "SELECT * FROM `$dm_table` WHERE `wr_id` = '$wr_id'";

        $db->ExecSql($query, "S");
        $row = $db->Fetch();

        //파일삭제
        $file_array = explode("|", $row['wr_file']);

        foreach ($file_array as $value) {
            if (is_file($_VAR_PATH_WEB_DATA."file/".$value)) {
                unlink($_VAR_PATH_WEB_DATA."file/".$value);
            }
        }

        $query = "DELETE FROM `{$dm_table}` WHERE `wr_id` = '$wr_id'";

        $db->ExecSql($query, "I");

        if($db->Num > 0)
        {
            $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "글을 삭제했습니다.", "objName" => $_POST['objName'] );
        }else
        {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => $arNoticeData, "objName" => $_POST['objName'] );
        }
        echo json_encode( $arResult );
    }

    else if ($command == "sympathize") {
        $wr_id = isset($_REQUEST['wr_id']) ? $_REQUEST['wr_id'] : "";
        $kind = isset($_REQUEST['kind']) ? $_REQUEST['kind'] : "";

        if (!$wr_id) {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "잘못된 접근입니다", "objName" => $_POST['objName'] );
            echo json_encode( $arResult );
            exit;
        }

        if (!getSession("chk_dm_id")) {
            $arResult = array( "result" => "fail","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "잘못된 접근입니다", "objName" => $_POST['objName'] );
            echo json_encode( $arResult );
            exit;
        }

        $com = ($kind == "good") ? "공감" : "비공감";

        $query = "SELECT * FROM dm_board_sympathize WHERE dm_table = '".$dm_table."' AND wr_id = '".$wr_id."' AND mb_id = '".getSession("chk_dm_id")."'";
        $db->ExecSql($query, "S");
        $row = $db->Fetch();

        if ($row) {
            if ($row['dm_type'] != $kind) {
                $query = "DELETE FROM dm_board_sympathize WHERE dm_id = '".$row['dm_id']."'";
                $db->ExecSql($query, "D");
                $query = "UPDATE {$dm_table} SET wr_{$row['dm_type']} = wr_{$row['dm_type']} - 1 WHERE wr_id = '".$wr_id."'";
                $db->ExecSql($query, "U");
            }
        }

        $query = "SELECT * FROM dm_board_sympathize WHERE dm_table = '".$dm_table."' AND wr_id = '".$wr_id."' AND mb_id = '".getSession("chk_dm_id")."' AND dm_type = '".$kind."'";
        $db->ExecSql($query, "S");
        $row = $db->Fetch();

        if ($row) {
            $query = "DELETE FROM dm_board_sympathize WHERE dm_id = '".$row['dm_id']."'";
            $db->ExecSql($query, "D");
            $query = "UPDATE {$dm_table} SET wr_{$kind} = wr_{$kind} - 1 WHERE wr_id = '".$wr_id."'";
            $db->ExecSql($query, "U");
            $notice = $com."을 취소 하였습니다.";
        } else {
            $query = "INSERT INTO dm_board_sympathize (dm_table, wr_id, mb_id, dm_type, dm_datetime, dm_ip) VALUE ('".$dm_table."', '".$wr_id."','".getSession("chk_dm_id")."','".$kind."', now(), '".$_SERVER['REMOTE_ADDR']."')";
            $db->ExecSql($query, "I");
            $query = "UPDATE {$dm_table} SET wr_{$kind} = wr_{$kind} + 1 WHERE wr_id = '".$wr_id."'";
            $db->ExecSql($query, "U");
            $notice = $com."을 하였습니다.";
        }

        $arResult = array( "result" => "success","_return" => "","total" => $total_count , "rows" => $arData, "notice" => "$notice", "objName" => $_POST['objName'] );
        echo json_encode( $arResult );
        exit;
    }

?>