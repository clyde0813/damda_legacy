<?
    include('../../lib/lib.php');

	$contentId =  isset($_REQUEST['contentId']) ? $_REQUEST['contentId'] : "";
	$page_no = isset($_REQUEST['page_no']) ? intval($_REQUEST['page_no']) : 1;
    $page_num = isset($_REQUEST['page_num']) ? intval($_REQUEST['page_num']) : 1;
	$command =  isset($_REQUEST['command']) ? $_REQUEST['command'] : "list";
	$wr_id =  isset($_REQUEST['wr_id']) ? $_REQUEST['wr_id'] : "";
	$cate =  isset($_REQUEST['cate']) ? $_REQUEST['cate'] : "";
	$sType =isset($_REQUEST['sType']) ? $_REQUEST['sType'] : "";
	$sValue =isset($_REQUEST['sValue']) ? $_REQUEST['sValue'] : "";
	$orderKind =isset($_REQUEST['orderKind']) ? $_REQUEST['orderKind'] : "";
	$sOrder =isset($_REQUEST['sOrder']) ? $_REQUEST['sOrder'] : "";
	$sPhoto =isset($_REQUEST['sPhoto']) ? $_REQUEST['sPhoto'] : "";

    $is_board_admin = false;

	$Query = "select * from dm_pages where dm_uid='".$contentId."' and dm_status = '1'";

	$db->ExecSql($Query, "S");
	$BBS_VAL;
	$BBS_PAGING;
	$BBS_DATAS = array();

    if ($db->Num > 0)
	{
		$row = $db->Fetch();
		$Query = "select * from dm_board where dm_id=".$row["dm_board_id"];

		$db->ExecSql($Query, "S");
		if ($db->Num > 0) 
		{
			$row = $db->Fetch();
			$BBS_VAL = $row;

            $query = "SELECT count(*) FROM `dm_write_{$BBS_VAL["dm_table"]}` WHERE `wr_is_notice` = 1";

            $db->ExecSql($query, "S");

            $notice_count = 0;

            if ($db->Num > 0) {
                $notice_count = $db -> GetPosition(0);
                $notice_count = $notice_count[0];
            }

			$countQuery = "SELECT count(*) FROM `dm_write_{$BBS_VAL["dm_table"]}`";
			$selectQuery = "SELECT *, (select count(*) from `dm_write_{$BBS_VAL['dm_table']}` WHERE wr_is_comment = 1 AND wr_num = a.wr_num) as `com_count`, (SELECT dm_nick FROM dm_member WHERE dm_id = a.mb_id ) AS `mb_nick` FROM `dm_write_{$BBS_VAL["dm_table"]}` as a";

			$whereQuery = "WHERE 1 = 1 AND wr_is_comment <> 1";
			$orderQuery = "ORDER BY wr_is_notice DESC";

			$pageQuery = "limit ".$BBS_VAL["dm_page_rows"]*($page_no-1).", ".$BBS_VAL["dm_page_rows"];

			if ($command == "view" || $command == 'modify_form' || $command == 'reply_form') {
                $whereQuery .= " AND wr_id = '$wr_id'";
            }
            
            if ($cate && $cate != "전체") {
                $whereQuery .= " AND `ca_name` LIKE '%".$cate."%'" ;
            }

            if ($sType && $sType != "전체") {
                if ($sType == "both") {
                    $whereQuery .= " AND (wr_subject LIKE '%$sValue%' OR wr_content LIKE '%$sValue%')";
                } else {
                    $whereQuery .= " AND `$sType` LIKE '%".$sValue."%'" ;
                }
            }

            if ($sPhoto) {
                $whereQuery .= " AND wr_content LIKE '%<img%' ";
            }

            if ($orderKind)
            {
                $orderQuery .= ", $orderKind $sOrder";
            }

            else
            {
                if($BBS_VAL["dm_sort_field"] == 1)
                {
                    $orderQuery .= ', wr_num , wr_reply';
                }
                else if ($BBS_VAL["dm_sort_field"] == 2)
                {
                    $orderQuery .= ', wr_num asc , wr_reply';
                }
                else if ($BBS_VAL["dm_sort_field"] == 'wr_2')
                {
                    $orderQuery .= ', cast(wr_2 as unsigned) asc ';
                }
                else if ($BBS_VAL["dm_sort_field"] == 'wr_3')
                {
                    $orderQuery .= ', wr_3 desc ';
                }
                else if ($BBS_VAL["dm_sort_field"] == 'wr_4')
                {
                    $orderQuery .= ', wr_4 desc ';
                }
                else if ($BBS_VAL["dm_sort_field"] == 4)
                {
                    $orderQuery = ' order by abs(DATEDIFF(DATE_FORMAT(now(), "%Y-%m-%d %H"), DATE_FORMAT(STR_TO_DATE(CONCAT(dm_append_1, dm_append_2), "%Y-%m-%d%H"), "%Y-%m-%d %H"))), dm_append_1 desc ';
                }
            }

			$cQuery = $countQuery." ".$whereQuery."";
			$Query = $selectQuery." ".$whereQuery." ".$orderQuery." ".$pageQuery;

			$db->ExecSql($cQuery, "S");
			$row = $db -> GetPosition(0);

            $total_page  = ceil($row[0] / $BBS_VAL["dm_page_rows"]);  // 전체 페이지 계산
			
			$BBS_PAGING["TOTAL_ROW_COUNT"] = $row[0];
			$BBS_PAGING["PAGE_INDEX"] = $page_no;
			$BBS_PAGING["ROWS"] = $BBS_VAL["dm_page_rows"];
			$BBS_PAGING["total_page"] = $total_page;

			$db->ExecSql($Query, "S");

            if (getSession('chk_dm_id')) {
                if ($BBS_VAL['dm_admin'] == getSession('chk_dm_id')) {
                    $is_board_admin = true;
                }
            }

            // 분류 사용
            $is_category = false;
            $category_name = '';
            if ($BBS_VAL['dm_use_category'])
            {
                $is_category = true;
                $category_name = $BBS_VAL['dm_category_list'];
                $category_array = explode(",", $category_name);
            }

            if ($BBS_VAL['dm_use_list_category'])
            {
                $category_name = $BBS_VAL['dm_category_list'];
                $category_array = explode(",", $category_name);
                if (count($category_array) > 1) {
                    $is_list_category = true;
                }
            }

            $is_html = "";
            $is_secret = "";
            $is_mail = "";

            $is_dhtml_editor = false;
            $is_dhtml_editor_use = false;
            $editor_content_js = '';

            $is_read = false; // 읽기 권한 체크
            $is_list_read = false; // 목록 권한 체크
            $is_write = false; // 쓰기 권한 체크
            $is_reply = false; // 답변 권한 체크
            $is_comment = false; // 댓글 권한 체크

            $list_group = explode("|", $BBS_VAL['dm_list_group']);
            $view_group = explode("|", $BBS_VAL['dm_read_group']);
            $write_group = explode("|", $BBS_VAL['dm_write_group']);
            $reply_group = explode("|", $BBS_VAL['dm_reply_group']);
            $comment_group = explode("|", $BBS_VAL['dm_comment_group']);
            $link_group = explode("|", $BBS_VAL['dm_link_group']);
            $upload_group = explode("|", $BBS_VAL['dm_upload_group']);

            $is_list_read = getAuth1($BBS_VAL['dm_auth_type'], $member_level, $BBS_VAL['dm_list_level'], $MEMBER['dm_group_id'], $list_group, $is_admin, $is_board_admin);
			$is_read = getAuth1($BBS_VAL['dm_auth_type'], $member_level, $BBS_VAL['dm_read_level'], $MEMBER['dm_group_id'], $view_group, $is_admin, $is_board_admin);
            $is_write = getAuth1($BBS_VAL['dm_auth_type'], $member_level, $BBS_VAL['dm_write_level'], $MEMBER['dm_group_id'], $write_group, $is_admin, $is_board_admin);
            $is_reply = getAuth1($BBS_VAL['dm_auth_type'], $member_level, $BBS_VAL['dm_reply_level'], $MEMBER['dm_group_id'], $reply_group, $is_admin, $is_board_admin);
            $is_comment_write = getAuth1($BBS_VAL['dm_auth_type'], $member_level, $BBS_VAL['dm_comment_level'], $MEMBER['dm_group_id'], $comment_group, $is_admin, $is_board_admin);

            //댓글 사용
            if ($BBS_VAL['dm_is_comment'])
            {
                $use_comment = true;
            }

            //답변 사용
            if ($BBS_VAL['dm_is_reply'])
            {
                $use_reply = true;
            }

            if ($command == "list")
			{
                //회원
                if (!$is_list_read)
                {
                    alert("글목록 권한이 없습니다. 회원이시라면 로그인해보세요", "?contentId=c13406bf526e9fee2bed34ab6f2125f6");
                }

                //쓰기 권한 체크
                $write_href = "";

                if ($is_admin || $is_board_admin)
                {
                    $write_href = "?contentId=".$contentId."&command=write_form";
                }
                else
                {
                    if ($is_write)
                    {
                        $write_href = "?contentId=".$contentId."&command=write_form";
                    }
                }

                $k = 0;
                while ($arItem = $db->Fetch() )
                {
                    $mb = getMember($arItem['mb_id']);

                    foreach( $arItem AS $key => $val )
                    {
                        if ( !is_string( $key ) ) continue;
                        $sDatas[ $key ] = $val;
                    }

                    $comment = getCommentCount($arItem['wr_num'], 'dm_write_'.$BBS_VAL["dm_table"]);
                    $comment_count = count($comment);
                    $sDatas['comment_count'] = $comment_count;

                    $reply = getReply($arItem['wr_num'], 'dm_write_'.$BBS_VAL['dm_table']);
                    $sDatas['reply_count'] = count($reply);

                    $list_num = $BBS_PAGING["TOTAL_ROW_COUNT"] - ($page_no - 1) * $BBS_VAL["dm_page_rows"];

                    $sDatas['num'] = $list_num - $k;

                    $sDatas['is_secret'] = false;

                    $option_array = explode(",", $sDatas['wr_option']);

//                    $sDatas['wr_content'] = htmlspecialchars_decode($sDatas['wr_content']);

                    foreach ($option_array as $v)
                    {
                        if ($v == 'secret')
                        {
                            $sDatas['wr_subject_secret'] = "비밀글입니다.";

                            if ($sDatas['wr_reply'])
                            {
                                $sDatas['wr_subject_secret'] = "re: 비밀글입니다.";
                            }

                            $sDatas['wr_content'] = "비밀글입니다.";

                            $sDatas['is_secret'] = true;
                        }
                    }

                    //회원
                    if ($is_read)
                    {
                        //뷰 링크
                        $sDatas['view_href'] = "?command=view&contentId=".$contentId.'&wr_id='.$arItem['wr_id']."&cate=".$cate."&page_num=".$page_no."&sType=".$sType."&sValue=".$sValue."&sPhoto=".$sPhoto;

                        $current_point = getMemberPoint();

                        if ((!$is_admin && !$is_board_admin)) {
                            if ($BBS_VAL['dm_read_point'] && ($arItem['mb_id'] != getSession("chk_dm_id"))) {
                                $db2 = new DBSQL();
                                $db2->DBconnect();
                                $is_use = false;
                                $query = "SELECT * FROM dm_point_log WHERE dm_id = '".getSession("chk_dm_id")."' AND dm_table = '".$BBS_VAL['dm_table']."' AND wr_id = '".$arItem['wr_id']."' AND dm_type = 1 order by dm_datetime desc";
                                $db2->ExecSql($query, "S");
                                $is_buy = $db2->Fetch();

                                if ($is_buy) {
                                    $expired = $is_buy['dm_expired'];
                                    if ($is_buy['dm_expired']) {
                                        $expired_date = date("Y-m-d H:i:s", strtotime($is_buy['dm_datetime'] ."+". $expired . " hour"));
                                        $now = date("Y-m-d H:i:s");
                                        if ($now >= $expired_date) {
                                            $is_use = false;
                                        } else {
                                            $is_use = true;
                                        }
                                    } else {
                                        $is_use = true;
                                    }
                                }

                                if (!$is_use) {
                                    if ($BBS_VAL['dm_read_point_type'] == 1) {
                                        if ($BBS_VAL['dm_read_point'] > $current_point) {
                                            if (!getSession("chk_dm_id")) {
                                                $sDatas['view_href'] = "javascript:alert('로그인 후 이용해주세요'); location.href='?contentId=c13406bf526e9fee2bed34ab6f2125f6'";
                                            } else {
                                                $sDatas['view_href'] = "javascript:alert('글을 읽을 포인트가 부족합니다.')";
                                            }
                                            
                                        } else {
                                            $text = "차감되는 포인트는 [".$BBS_VAL['dm_read_point']."] 입니다. 글을 읽으시겠습니까?";
                                            $sDatas['view_href'] = "javascript:if (confirm('".$text."')) { location.href='".$sDatas['view_href']."' }";
                                        }
                                    }
                                }
                            }
                        }

                        if($sDatas['is_secret'] && (!$is_admin && !$is_board_admin))
                        {
                            $sDatas['view_href'] = "javascript:showPassword('".$arItem['wr_id']."', 'view');";
                        }
                    }
                    else
                    {
                        $sDatas['view_href'] = "javascript:alert('글을 읽을 권한이 없습니다. 회원이시면 로그인해보세요');location.href='/diam/web/?contentId=c13406bf526e9fee2bed34ab6f2125f6';";
                    }

                    $sDatas['wr_subject'] = cut_str($sDatas['wr_subject'], $BBS_VAL['dm_subject_len'], '…');

                    $sDatas['dm_hit_url'] = "";
                    $sDatas['dm_new_url'] = "";

                    if ($BBS_VAL['dm_is_hit']) {
                        if ($sDatas['wr_hit'] >= $BBS_VAL['dm_hit_max']) {
                            if ($BBS_VAL['dm_hit_icon']) {
                                if (is_file($_VAR_PATH_WEB_BOARD.$BBS_VAL['dm_skin']."/images/".$BBS_VAL['dm_hit_icon'])) {
                                    $sDatas['dm_hit_url'] = $_VAR_URL_WEB_BOARD.$BBS_VAL['dm_skin']."/images/".$BBS_VAL['dm_hit_icon'];
                                } else {
                                    $sDatas['dm_hit_url'] = $_VAR_URL_WEB_BOARD.$BBS_VAL['dm_skin']."/images/ico_hit.png";
                                }
                            } else {
                                $sDatas['dm_hit_url'] = $_VAR_URL_WEB_BOARD.$BBS_VAL['dm_skin']."/images/ico_hit.png";
                            }
                        }
                    }

                    if ($BBS_VAL['dm_is_new']) {
                        if (date("Y-m-d H:i:s") <= date("Y-m-d H:i:s", strtotime($sDatas['wr_datetime']."+ ".$BBS_VAL['dm_new_time']. " hour"))) {
                            if ($BBS_VAL['dm_new_icon']) {
                                if (is_file($_VAR_PATH_WEB_BOARD . $BBS_VAL['dm_skin'] . "/images/" . $BBS_VAL['dm_new_icon'])) {
                                    $sDatas['dm_new_url'] = $_VAR_URL_WEB_BOARD . $BBS_VAL['dm_skin'] . "/images/" . $BBS_VAL['dm_new_icon'];
                                } else {
                                    $sDatas['dm_new_url'] = $_VAR_URL_WEB_BOARD.$BBS_VAL['dm_skin']."/images/ico_new.png";
                                }
                            } else {
                                $sDatas['dm_new_url'] = $_VAR_URL_WEB_BOARD.$BBS_VAL['dm_skin']."/images/ico_new.png";
                            }
                        }
                    }

                    $is_file_icon = false;
                    if ($BBS_VAL['dm_use_file_icon']) {
                        $is_file_icon = true;
                        if ($sDatas['wr_file']) {
                            $sDatas['file_icon'] = $_VAR_URL_WEB_BOARD.$BBS_VAL['dm_skin']."/images/file.png";
                        }
                    }

                    $is_good = false;
                    $is_nogood = false;

                    $good_url = "";
                    $nogood_url = "";
                    if ($BBS_VAL['dm_use_good']) {
                        $db2 = new DBSQL();
                        $db2->DBconnect();
                        $is_good = true;
                        $good_url = $_VAR_URL_WEB_BOARD.'command.php?command=sympathize&kind=good&contentId='.$contentId;
                        $query = "SELECT count(*) as a FROM dm_board_sympathize WHERE dm_table = 'dm_write_".$BBS_VAL['dm_table']."' AND wr_id = '".$sDatas['wr_id']."' AND dm_type = 'good'";
                        $db2->ExecSql($query, "S");
                        $good_count = $db2->Fetch();
                        $sDatas['good_count'] = $good_count['a'];
                    }

                    if ($BBS_VAL['dm_use_nogood']) {
                        $db2 = new DBSQL();
                        $db2->DBconnect();
                        $is_nogood = true;
                        $nogood_url = $_VAR_URL_WEB_BOARD.'command.php?command=sympathize&kind=nogood&contentId='.$contentId;
                        $query = "SELECT count(*) as a FROM dm_board_sympathize WHERE dm_table = 'dm_write_".$BBS_VAL['dm_table']."' AND wr_id = '".$sDatas['wr_id']."' AND dm_type = 'nogood'";
                        $db2->ExecSql($query, "S");
                        $good_count = $db2->Fetch();
                        $sDatas['nogood_count'] = $good_count['a'];
                    }

                    $selectCode = selectCommonCode('1002');
                    $sDatas['level_text'] = $selectCode[$mb['dm_level']];

                    array_push( $BBS_DATAS, $sDatas );
                    $k++;
                }

                $is_list_good = false;
                if ($BBS_VAL['dm_list_good']) {
                    $is_list_good = true;
                }
            }
            else if ($command == "view")
            {
                //회원
                if (!$is_read)
                {
                    alert("글을 볼 권한이 없습니다. 회원이시라면 로그인해보세요");
                    goLink("?contentId=c13406bf526e9fee2bed34ab6f2125f6");
                }

                $BBS_DATA = $db->Fetch();

                // 포인트 체크 후 소모 210118 //
                if ($BBS_VAL['dm_read_point'] && (!$is_admin || $is_board_admin) && ($BBS_DATA['mb_id'] != getSession("chk_dm_id"))) {
                    $is_use = false;
                    $query = "SELECT * FROM dm_point_log WHERE dm_id = '".getSession("chk_dm_id")."' AND dm_table = '".$BBS_VAL['dm_table']."' AND wr_id = '".$BBS_DATA['wr_id']."'  AND dm_type = 1  order by dm_datetime desc";
                    $db->ExecSql($query, "S");
                    $is_buy = $db->Fetch();

                    if ($is_buy) {
                        $expired = $is_buy['dm_expired'];
                        if ($is_buy['dm_expired']) {
                            $expired_date = date("Y-m-d H:i:s", strtotime($is_buy['dm_datetime'] ."+". $expired . " hour"));
                            $now = date("Y-m-d H:i:s");
                            if ($now >= $expired_date) {
                                $is_use = false;
                            } else {
                                $is_use = true;
                            }
                        } else {
                            $is_use = true;
                        }
                    }

                    if (!$is_use) {
                        $current_point = getMemberPoint();

                        if ($BBS_VAL['dm_read_point'] > $current_point) {
                            alert ("글을 읽을 포인트가 부족합니다. 포인트를 충전해주세요");
                        } else {
                            if ($BBS_VAL['dm_read_point_type'] == 1) {
                                $point = $current_point - $BBS_VAL['dm_read_point'];
                                $kind = 0;
                            } else {
                                $point = $current_point + $BBS_VAL['dm_read_point'];
                                $kind = 1;
                            }

                            insert_point(1, $BBS_VAL['dm_read_point'], $BBS_VAL['dm_table'], $BBS_DATA['wr_id'], $point, $BBS_VAL['dm_read_point_expired'], $kind);
                        }
                    }
                }

                // 포인트 소모 끝 //

                $option_array = $BBS_DATA['wr_option'];

                $option_array = explode(",", $option_array);

                foreach ($option_array as $v)
                {
                    if ($v == 'secret')
                    {
                        $is_secret = true;
                    }
                }

                // 비밀글 세션 검사
                $ss_key = getSession("ss_view_".$wr_id);

                $compare_key = md5($BBS_DATA['wr_datetime']."dm");

                if ((!$is_admin && !$is_board_admin) && $is_secret)
                {
                    if ($ss_key != $compare_key)
                    {
                        alert("잘못된 접근입니다.");
                    }
//                    else
//                    {
//                        setSession("ss_view_".$wr_id, "");
//                        sessionUnset("ss_view_".$wr_id);
//                    }
                }
                $hit_count = $BBS_VAL['dm_hit_count'];

                $query = "UPDATE `dm_write_{$BBS_VAL['dm_table']}` SET `wr_hit` = (`wr_hit` + {$hit_count}) WHERE `wr_id` = '".$wr_id."'";
                $db->ExecSql($query, "I");

                $is_prev = false;

                if ($BBS_VAL['dm_use_prev_write']) {
                    $is_prev = true;
                    // 윗글을 얻음
                    $query = "SELECT `wr_id`, `wr_subject`, `wr_datetime`, `wr_option`, `wr_reply` FROM `dm_write_{$BBS_VAL['dm_table']}` WHERE wr_is_comment = 0 AND wr_num = '{$BBS_DATA['wr_num']}' AND wr_reply < '{$BBS_DATA['wr_reply']}' ORDER BY wr_num DESC, wr_reply DESC LIMIT 1 ";
                    $db->ExecSql($query, "S");
                    $prev = $db->Fetch();

                    // 위의 쿼리문으로 값을 얻지 못했다면
                    if (!$prev['wr_id'])     {
                        $query = "SELECT `wr_id`, `wr_subject`, `wr_datetime`, `wr_option`, `wr_reply` FROM `dm_write_{$BBS_VAL['dm_table']}` WHERE `wr_is_comment` = 0 AND `wr_num` < '{$BBS_DATA['wr_num']}' ORDER BY `wr_num` DESC, `wr_reply` DESC LIMIT 1 ";
                        $db->ExecSql($query, "S");
                        $prev = $db->Fetch();
                    }

                    // 아래글을 얻음
                    $query = "SELECT `wr_id`, `wr_subject`, `wr_datetime`, `wr_option`, `wr_reply` FROM `dm_write_{$BBS_VAL['dm_table']}` WHERE `wr_is_comment` = 0 AND `wr_num` = '{$BBS_DATA['wr_num']}' AND `wr_reply` > '{$BBS_DATA['wr_reply']}'  ORDER BY `wr_num`, `wr_reply` LIMIT 1 ";
                    $db->ExecSql($query, "S");
                    $next = $db->Fetch();
                    // 위의 쿼리문으로 값을 얻지 못했다면
                    if (!$next['wr_id']) {
                        $query = "SELECT `wr_id`, `wr_subject`, `wr_datetime`, `wr_option`, `wr_reply` FROM `dm_write_{$BBS_VAL['dm_table']}` WHERE `wr_is_comment` = 0 AND `wr_num` > '{$BBS_DATA['wr_num']}' ORDER BY `wr_num`, `wr_reply` LIMIT 1 ";
                        $db->ExecSql($query, "S");
                        $next = $db->Fetch();
                    }

                    // 이전글 링크
                    $prev_href = '';
                    if (isset($prev['wr_id']) && $prev['wr_id']) {
                        $prev_option = $prev['wr_option'];

                        $prev_option = explode(",", $prev_option);

                        $prev_wr_subject = $prev['wr_subject'];

                        $prev_href = "?command=view&contentId=".$contentId."&wr_id=".$prev['wr_id']."";

                        $current_point = getMemberPoint();

                        if ($BBS_VAL['dm_read_point'] && (!$is_admin || !$is_board_admin) && getSession("chk_dm_id") != $prev['mb_id']) {
                            $db2 = new DBSQL();
                            $db2->DBconnect();
                            $is_use = false;
                            $query = "SELECT * FROM dm_point_log WHERE dm_id = '".getSession("chk_dm_id")."' AND dm_table = '".$BBS_VAL['dm_table']."' AND wr_id = '".$prev['wr_id']."' AND dm_type = 1  order by dm_datetime desc";
                            $db2->ExecSql($query, "S");
                            $is_buy = $db2->Fetch();

                            if ($is_buy) {
                                $expired = $is_buy['dm_expired'];
                                if ($is_buy['dm_expired']) {
                                    $expired_date = date("Y-m-d H:i:s", strtotime($is_buy['dm_datetime'] ."+". $expired . " hour"));
                                    $now = date("Y-m-d H:i:s");
                                    if ($now >= $expired_date) {
                                        $is_use = false;
                                    } else {
                                        $is_use = true;
                                    }
                                } else {
                                    $is_use = true;
                                }
                            }

                            if (!$is_use) {
                                if ($BBS_VAL['dm_read_point_type'] == 1) {
                                    if ($BBS_VAL['dm_read_point'] > $current_point) {
                                        $prev_href = "javascript:alert('글을 읽을 포인트가 부족합니다.')";
                                    } else {
                                        $text = "차감되는 포인트는 [".$BBS_VAL['dm_read_point']."] 입니다. 글을 읽으시겠습니까?";
                                        $prev_href = "javascript:if (confirm('".$text."')) { location.href='".$prev_href."' }";
                                    }
                                }
                            }
                        }

                        foreach ($prev_option as $v)
                        {
                            if ($v == 'secret')
                            {
                                $prev_wr_subject_secret = "비밀글입니다.";

                                if ($prev['wr_reply'])
                                {
                                    $prev_wr_subject_secret = "re: 비밀글입니다.";
                                }
                                if(!$is_admin && !$is_board_admin)
                                {
                                    $prev_href = "javascript:showPassword('".$prev['wr_id']."', 'view');";
                                }
                            }
                        }

                        $prev_wr_date = date("Y-m-d", strtotime($prev['wr_datetime']));
                    }

                    // 다음글 링크
                    $next_href = '';
                    if (isset($next['wr_id']) && $next['wr_id']) {
                        $next_option = $next['wr_option'];

                        $next_option = explode(",", $next_option);

                        $next_wr_subject = $next['wr_subject'];

                        $next_href = "?command=view&contentId=".$contentId."&wr_id=".$next['wr_id']."";

                        $current_point = getMemberPoint();

                        if ($BBS_VAL['dm_read_point'] && (!$is_admin || !$is_board_admin) && getSession("chk_dm_id") != $next['mb_id']) {
                            $db2 = new DBSQL();
                            $db2->DBconnect();
                            $is_use = false;
                            $query = "SELECT * FROM dm_point_log WHERE dm_id = '".getSession("chk_dm_id")."' AND dm_table = '".$BBS_VAL['dm_table']."' AND wr_id = '".$next['wr_id']."' AND dm_type = 1  order by dm_datetime desc";
                            $db2->ExecSql($query, "S");
                            $is_buy = $db2->Fetch();

                            if ($is_buy) {
                                $expired = $is_buy['dm_expired'];
                                if ($is_buy['dm_expired']) {
                                    $expired_date = date("Y-m-d H:i:s", strtotime($is_buy['dm_datetime'] ."+". $expired . " hour"));
                                    $now = date("Y-m-d H:i:s");
                                    if ($now >= $expired_date) {
                                        $is_use = false;
                                    } else {
                                        $is_use = true;
                                    }
                                } else {
                                    $is_use = true;
                                }
                            }

                            if (!$is_use) {
                                if ($BBS_VAL['dm_read_point_type'] == 1) {
                                    if ($BBS_VAL['dm_read_point'] > $current_point) {
                                        $next_href = "javascript:alert('글을 읽을 포인트가 부족합니다.')";
                                    } else {
                                        $text = "차감되는 포인트는 [".$BBS_VAL['dm_read_point']."] 입니다. 글을 읽으시겠습니까?";
                                        $next_href = "javascript:if (confirm('".$text."')) { location.href='".$next_href."' }";
                                    }
                                }
                            }
                        }

                        foreach ($next_option as $v)
                        {
                            if ($v == 'secret')
                            {
                                $next_wr_subject_secret = "비밀글입니다.";

                                if ($next['wr_reply'])
                                {
                                    $next_wr_subject_secret = "re: 비밀글입니다.";
                                }
                                if(!$is_admin && !$is_board_admin) {
                                    $next_href = "javascript:showPassword('" . $next['wr_id'] . "', 'view');";
                                }

                            }
                        }

                        $next_wr_date = date("Y-m-d", strtotime($next['wr_datetime']));
                    }
                }

                // 쓰기 링크
                $write_href = '';
                if ($is_write || ($is_admin || $is_board_admin))
                    $write_href = '?command=write_form&contentId='.$contentId;

                // 글 수정 링크
                $modify_href = "";
                if (getSession('chk_dm_id')) {
                    if (($is_admin || $is_board_admin) || $BBS_DATA['mb_id'] == getSession('chk_dm_id')) {
                        $modify_href="?command=modify_form&contentId=".$contentId.'&wr_id='.$wr_id;
                    }
                } else {
                    $modify_href="javascript:showPassword('" . $wr_id . "', 'modify_write');";
                }

                // 삭제 링크
                $delete_href = '';
                if (getSession('chk_dm_id')) {
                    if (($is_admin || $is_board_admin) || $BBS_DATA['mb_id'] == getSession('chk_dm_id')) {
                        $delete_href = $_VAR_URL_WEB_BOARD.'command.php?command=delete&contentId='.$contentId.'&wr_id='.$wr_id;
                        $delete_script = "javascript:removeBoard();";
                    }
                } else {
                    $delete_href = $_VAR_URL_WEB_BOARD.'command.php?command=delete&contentId='.$contentId.'&wr_id='.$wr_id;
                    $delete_script="javascript:showPassword('" . $wr_id . "', 'delete_write');";
                }

                // 글 답변 링크
                $reply_href = "";
                if (!$BBS_DATA['wr_is_notice']) {
                    if ($is_reply){
                        $reply_href="?command=reply_form&contentId=".$contentId.'&wr_id='.$wr_id;
                    }
                }

                //파일 배열
                if ($BBS_DATA['wr_file']) {
                    $file_array = explode("|", $BBS_DATA['wr_file']);
                    $file_ori_array = explode("|", $BBS_DATA['wr_ori_file_name']);
                } else {
                    $file_array = array();
                    $file_ori_array = array();
                }

                $is_link = false;
                if ($BBS_VAL['dm_use_link']) {
                    if ($member_level >= $BBS_VAL['dm_link_level']) {
                        for ($i=0; $i<2; $i++) {
                            if ($BBS_DATA['wr_link'.($i+1)]) {
                                $is_link = true;
                                break;
                            }
                        }
                    }
                }

                $is_file = false;
                if ($BBS_VAL['dm_use_file']) {
                    for ($i=0; $i<$BBS_VAL['dm_upload_count']; $i++) {
                        if (is_file($_VAR_PATH_WEB_DATA.'file/'.$file_array[$i])) {
                            $is_file = true;
                        }
                    }
                }

                $BBS_DATA['wr_content'] = htmlspecialchars_decode($BBS_DATA['wr_content'], ENT_QUOTES);

                if ($BBS_VAL['dm_writer_type'] == 'id') {
                    $BBS_DATA['wr_name'] = $BBS_DATA['mb_id'];
                } else if ($BBS_VAL['dm_writer_type'] == 'nick') {
                    $query = "SELECT * FROM dm_member WHERE dm_id = '".$BBS_DATA['mb_id']."'";
                    $db->ExecSql($query, "S");
                    $mb = $db->Fetch();
                    $BBS_DATA['wr_name'] = $mb['dm_nick'];
                }

                $wr_name_leng = mb_strlen($BBS_DATAS['wr_name'], "UTF-8");
                if ($BBS_VAL['dm_writer_secret'] == 2) {
                    $BBS_DATA['wr_name'] = mb_substr($BBS_DATA['wr_name'], 0, 2).'*';
                } else if ($BBS_VAL['dm_writer_secret'] == 3) {
                    $BBS_DATA['wr_name'] = mb_substr($BBS_DATA['wr_name'], 0, 1).str_repeat('*', $wr_name_leng-1);
                } else if ($BBS_VAL['dm_writer_secret'] == 4) {
                    $BBS_DATA['wr_name'] = mb_substr($BBS_DATA['wr_name'], 0, -2).str_repeat('*', 2);
                }

                //댓글
                $list = array();

                setSession("view_token", $BBS_DATA['wr_id']); //댓글에 hidden 으로 wr_id를 넘길수 없어 세션처리

//                $row = getComment($wr_id, 'dm_write_'.$BBS_VAL['dm_table']);

                $comment_list = getCommentReply($wr_id, 'dm_write_'.$BBS_VAL['dm_table']);

                if (count($comment_list) > 0) {
                    $i = 0;
                    foreach ($comment_list as $value) {
                        $list[$i] = $value;
                        $list[$i]['name'] = '<span class="'.($value['mb_id']?'member':'guest').'">'.$value['wr_name'].'</span>';

                        $list[$i]['content'] = $list[$i]['content1']= '비밀글 입니다.';

                        if (!strstr($value['wr_option'], 'secret') ||
                            $is_admin || $is_board_admin ||
                            ($BBS_DATA['mb_id']===$member_id && $member_id) ||
                            ($value['mb_id']===$member_id && $member_id)) {
                            $list[$i]['content1'] = $value['wr_content'];
                            $list[$i]['content'] = $value['wr_content'];
                        }

                        $is_parent = false;
                        //댓글 작성자의 부모글이라면
                        $query = "SELECT * FROM dm_write_".$BBS_VAL['dm_table']. " WHERE wr_id = '".$value['wr_parent']."' AND wr_is_comment = 1 AND mb_id = '".$member_id."'";
                        $db->ExecSql($query, "S");
                        $temp = $db->Fetch();
                        if ($temp) {
                            $list[$i]['content1'] = $value['wr_content'];
                            $list[$i]['content'] = $value['wr_content'];
                            $is_parent = true;
                        }

                        $list[$i]['is_comment_secret'] = false;
                        $option_array = explode(",", $value['wr_option']);
                        if (in_array("secret", $option_array)) {
                            $list[$i]['is_comment_secret'] = true;
                        }

                        $list[$i]['datetime'] = substr($value['wr_datetime'],2,14);

                        $list[$i]['ip'] = $value['wr_ip'];

                        $list[$i]['is_reply'] = false;
                        $list[$i]['is_edit'] = false;
                        $list[$i]['is_del']  = false;

                        if ($is_comment_write || $is_admin || $is_board_admin)
                        {
                            $token = '';

                            if ($member_id)
                            {
                                if ($value['mb_id'] === $member_id || $is_admin)
                                {
                                    setSession('ss_delete_'.$value['wr_id'].'_token', $value['wr_id']);
                                    $list[$i]['del_link']  = $_VAR_URL_WEB_BOARD."command.php?command=delete_comment&contentId=".$contentId;
                                    $list[$i]['is_edit']   = true;
                                    $list[$i]['is_del']    = true;
                                }
                            }
                            else
                            {
                                if (!$value['mb_id']) {
//                                    $list[$i]['del_link'] = G5_BBS_URL.'/password.php?w=x&amp;bo_table='.$bo_table.'&amp;comment_id='.$row['wr_id'].'&amp;page='.$page.$qstr;
//                                    $list[$i]['is_del']   = true;
                                }
                            }

                            if (strlen($value['wr_comment_reply']) < 5)
                                $list[$i]['is_reply'] = true;
                        }

                        if ($i > 0 && !$is_admin || !$is_board_admin)
                        {
                            if ($list[$i]['wr_comment_reply'])
                            {
                                $tmp_comment_reply = substr($value['wr_comment_reply'], 0, strlen($value['wr_comment_reply']) - 1);
                                if ($tmp_comment_reply == $list[$i-1]['wr_comment_reply'])
                                {
                                    $list[$i-1]['is_edit'] = false;
                                    $list[$i-1]['is_del'] = false;
                                }
                            }
                        }

                        $list[$i]['depth'] =  $cmt_depth = strlen($list[$i]['wr_comment_reply']) * 50;

                        $i++;
                    }

                }

                $comment_count = count($list);

                $dm_use_comment_secret = false;
                if ($BBS_VAL['dm_use_comment_secret'] == 1) {
                    $dm_use_comment_secret = true;
                    $comment_secret = '<div class="comment_secret"><input type="checkbox" name="comment_secret" id="comment_secret" checked value="secret"/> <label for="comment_secret">비밀댓글</label></div>';
                } else if ($BBS_VAL['dm_use_comment_secret'] == 2) {
                    $dm_use_comment_secret = true;
                    $comment_secret = '<div class="comment_secret">작성 시 비밀댓글로 작성됩니다. <input type="hidden" name="comment_secret" id="comment_secret" value="secret"/></div>';
                }

                $is_good = false;

                $is_nogood = false;

                $good_url = "";
                $nogood_url = "";
                if ($BBS_VAL['dm_use_good']) {
                    $is_good = true;
                    $good_url = $_VAR_URL_WEB_BOARD.'command.php?command=sympathize&kind=good&contentId='.$contentId;
                    $query = "SELECT count(*) as a FROM dm_board_sympathize WHERE dm_table = 'dm_write_".$BBS_VAL['dm_table']."' AND wr_id = '".$wr_id."' AND dm_type = 'good'";
                    $db->ExecSql($query, "S");
                    $good_count = $db->Fetch();
                    $BBS_DATA['good_count'] = $good_count['a'];
                }

                if ($BBS_VAL['dm_use_nogood']) {
                    $is_nogood = true;
                    $nogood_url = $_VAR_URL_WEB_BOARD.'command.php?command=sympathize&kind=nogood&contentId='.$contentId;
                    $query = "SELECT count(*) as a FROM dm_board_sympathize WHERE dm_table = 'dm_write_".$BBS_VAL['dm_table']."' AND wr_id = '".$wr_id."' AND dm_type = 'nogood'";
                    $db->ExecSql($query, "S");
                    $good_count = $db->Fetch();
                    $BBS_DATA['nogood_count'] = $good_count['a'];
                }

                $is_ip_view = false;

                if ($BBS_VAL['dm_use_ip_view'] == 1) {
                    $is_ip_view = true;

                } else if ($BBS_VAL['dm_use_ip_view'] == 2){
                    $is_ip_view = true;
                    $ip_leng = strlen($BBS_DATA['wr_ip']);
                    $BBS_DATA['wr_ip'] = mb_substr($BBS_DATA['wr_ip'], 0, -3).'***';
                }

                $is_sns = false;
                if ($BBS_VAL['dm_use_sns']) {
                    $is_sns = true;
                    $http_host = $_SERVER['HTTP_HOST'];
                    $request_uri = $_SERVER['REQUEST_URI'];
                    $current_url = 'http://' . $http_host . $request_uri;
                }

                $is_use_list = false;
                if ($BBS_VAL['dm_use_list']) {
                    $is_use_list = true;
                }

                $is_use_class_view = false;
                if ($BBS_VAL['dm_use_class_view']) {
                    $is_use_class_view = true;
                }

                $is_use_view_level = false;

                if ($BBS_VAL['dm_use_view_level']) {
                    $is_use_view_level = true;
                    $mb = getMember($BBS_DATA['mb_id']);
                    $selectCode = selectCommonCode('1002');
                    $BBS_DATA['level_text'] = $selectCode[$mb['dm_level']];
                }

            } else if ($command == "write_form")
            {
                //회원
                if (!$is_write)
                {
                    alert("글을 작성할 권한이 없습니다. 회원이시라면 로그인해보세요");
                    goLink("?contentId=c13406bf526e9fee2bed34ab6f2125f6");
                }

                $content = "";

                if ($BBS_VAL['dm_use_dhtml_editor']) {
                    include $_VAR_PATH_WEB_LIB."smarteditor2/editor.lib.php";
                    $editor_html = editor_html('txt_content', $content, true);
                    $editor_js = '';
                    $editor_js .= get_editor_js('txt_content', true);
                    $editor_js .= chk_editor_js('txt_content', true);
                } else {
                    $editor_html = "<textarea name='txt_content' id='txt_content' rows='10' style='width:98%;'></textarea>";
                }

                $is_notice = false;
                $notice_checked = '';
                if ($is_admin || $is_board_admin) {
                    $is_notice = true;
                    if (in_array((int)$wr_id, $notice_array)) {
                        $notice_checked = 'checked';
                    }
                }

                $is_html = false;
                if ($member_level >= $BBS_VAL['dm_html_level'])
                    $is_html = true;

                $is_secret = $BBS_VAL['dm_use_secret'];

                if ($BBS_VAL['dm_use_secret'] == 1) {
                    $secret_checked = "checked";
                }

                $is_mail = false;
                if ($BBS_VAL['dm_use_email'])
                    $is_mail = true;

                $is_link = false;
                if ($BBS_VAL['dm_use_link']) {
                    $is_link = getAuth1($BBS_VAL['dm_auth_type'], $member_level, $BBS_VAL['dm_link_level'], $MEMBER['dm_group_id'], $link_group);
                }

                $is_file = false;
                if ($BBS_VAL['dm_use_file']) {
                    $is_file = getAuth1($BBS_VAL['dm_auth_type'], $member_level, $BBS_VAL['dm_upload_level'], $MEMBER['dm_group_id'], $upload_group);
                }

                $is_name = false;
                if ($BBS_VAL['dm_use_name']) {
                    $is_name = true;
                }

                $captcha_html = '';
                $captcha_js   = '';
                $is_use_captcha = ((($BBS_VAL['dm_use_captcha']) || getSession('chk_dm_level') <= 0) && !$is_admin) ? 1 : 0;

                if ($is_use_captcha) {
                    include_once($_VAR_PATH_WEB_LIB.'/recaptcha/captcha.lib.php');
                    $captcha_html = captcha_html();
                    $captcha_js   = chk_captcha_js();
                }
            }
            else if ($command == "modify_form" || $command == 'reply_form')
            {
                //회원
                if (!$wr_id)
                {
                    alert("잘못된 접근입니다");
                    goLink("?contentId=c13406bf526e9fee2bed34ab6f2125f6");
                }

                $file_array = array();
                $ori_file_array = array();
                $BBS_DATAS = $db->Fetch();

                if ($BBS_VAL['dm_use_dhtml_editor']) {
                    include $_VAR_PATH_WEB_LIB."smarteditor2/editor.lib.php";
                    $editor_html = editor_html('txt_content', $BBS_DATAS['wr_content'], true);
                    $editor_js = '';
                    $editor_js .= get_editor_js('txt_content', true);
                    $editor_js .= chk_editor_js('txt_content', true);
                }  else {
                    $editor_html = "<textarea name='txt_content' id='txt_content' rows='10' style='width:98%;'>".$BBS_DATAS['wr_content']."</textarea>";
                }

                $is_notice = false;
                $notice_checked = '';
                $is_secret = $BBS_VAL['dm_use_secret'];

                $is_html = false;
                if ($member_level >= $BBS_VAL['dm_html_level'])
                    $is_html = true;


                $is_mail = false;
                if ($BBS_VAL['dm_use_email'])
                    $is_mail = true;

                $is_link = false;
                if ($BBS_VAL['dm_use_link']) {
                    $is_link = getAuth1($BBS_VAL['dm_auth_type'], $member_level, $BBS_VAL['dm_link_level'], $MEMBER['dm_group_id'], $link_group);
                }

                $is_file = false;
                if ($BBS_VAL['dm_use_file']) {
                    $is_file = getAuth1($BBS_VAL['dm_auth_type'], $member_level, $BBS_VAL['dm_upload_level'], $MEMBER['dm_group_id'], $upload_group);
                }

                //회원적립되면 수정해서 써야됨
                $is_name = false;
                if ($BBS_VAL['dm_use_name']) {
                    $is_name = true;
                }

                if ($command == 'modify_form')
                {

                    if (!$is_admin && !$is_board_admin) {
                        if ($BBS_DATAS['mb_id'] != $MEMBER['dm_id'])
                        {
                            alert("잘못된 접근입니다.");
                        }
                    }

                    if (!$BBS_DATAS['mb_id'] && (!$is_admin && !$is_board_admin)) {
                        $token = md5($BBS_DATAS['wr_datetime']."dm");
                        $compare = getSession("ss_view_".$BBS_DATAS['wr_id']);

                        if ($token != $compare) {
                            alert("잘못된 접근입니다");
                        } else {
                            sessionUnset("ss_view_".$BBS_DATAS['wr_id']);
                        }
                    }

                    if ($is_admin || $is_board_admin)
                    {
                        $is_notice = true;
                        if($BBS_DATAS['wr_is_notice'])
                        {
                            $notice_checked = 'checked';
                        }
                    }

                    $option_array = explode(",", $BBS_DATAS['wr_option']);

                    foreach ($option_array as $v)
                    {
                        if ($v == 'secret') {
                            $secret_checked = "checked";
                        }

                        if ($v == 'mail') {
                            $recv_email_checked = "checked";
                        }
                    }

                    $file_array = explode("|", $BBS_DATAS['wr_file']);
                    $ori_file_array = explode("|", $BBS_DATAS['wr_ori_file_name']);
                }
                else if ($command == 'reply_form')
                {
                    //회원
                    if (!$is_reply)
                    {
                        alert("글을 작성할 권한이 없습니다. 회원이시라면 로그인해보세요");
                        goLink("?contentId=c13406bf526e9fee2bed34ab6f2125f6");
                    }

                    $option_array = explode(",", $BBS_DATAS['wr_option']);
                    foreach ($option_array as $v)
                    {
                        if ($v == 'secret')
                        {
                            $secret_checked = "checked";
                        }

                        if ($v == 'mail')
                        {
                            $recv_email_checked = "checked";
                        }
                    }
                    $BBS_DATAS['wr_subject'] = "re: ".$BBS_DATAS['wr_subject'];
                    $BBS_DATAS['wr_content'] = "";
                    $BBS_DATAS['wr_name'] = getSession('chk_dm_name');

                    $captcha_html = '';
                    $captcha_js   = '';
                    $is_use_captcha = ((($BBS_VAL['dm_use_captcha']) || getSession('chk_dm_level') <= 0) && !$is_admin) ? 1 : 0;

                    if ($is_use_captcha) {
                        include_once($_VAR_PATH_WEB_LIB.'/recaptcha/captcha.lib.php');
                        $captcha_html = captcha_html();
                        $captcha_js   = chk_captcha_js();
                    }
                }
            }
		}
	}

	function getComment($wr_id, $table)
    {
        $db = new DBSQL();
        $db->DBconnect();
        $row = array();
        $res = array();

        $query = "SELECT * FROM `{$table}` WHERE wr_parent = '".$wr_id."' and wr_is_comment = 1 order by wr_comment, wr_comment_reply ";

        $db->ExecSql($query, "S");

        if ($db->Num > 0)
        {
            while($row = $db->Fetch())
            {
                $res[] = $row;
            }
        }

        return $res;
    }

    function getReply($wr_num, $table)
    {
        $db = new DBSQL();
        $db->DBconnect();
        $row = array();
        $res = array();

        $query = "SELECT * FROM `{$table}` WHERE wr_num = '".$wr_num."' and wr_is_comment = '0' and wr_reply = '' order by wr_id";

        $db->ExecSql($query, "S");

        if ($db->Num > 0)
        {
            while($row = $db->Fetch())
            {
                $res[] = $row;
            }
        }

        return $res;
    }

    function getAuth($level, $compare_level)
    {
        if (!$level) $level = 0;
        if ($compare_level <= $level) {
            return true;
        } else {
            return false;
        }
    }

    function getAuth1($type, $level="", $compare_level="", $group="", $compare_group=array(), $is_admin, $is_board_admin)
    {
        $res = false;
		if (!$is_admin && !$is_board_admin) {
			if ($type == 1) {
				if (in_array($group, $compare_group)) {
					$res = true;
				} else { 
					if (empty($compare_group)) {
						$res = true;
					}
				}
			} else if ($type == 2) {
				if (!$level) $level = 0;
				if ($compare_level <= $level) {
					$res = true;
				}
			} else if ($type == 3) {
				if (!$level) $level = 0;
				if (in_array($group, $compare_group) || ($compare_level <= $level)) {
					$res = true;
				}
			}
		} else {
			$res = true;
		}

        return $res;
	}

    $res = array();
    function getCommentReply($wr_id, $table)
    {
        $db = new DBSQL();
        $db->DBconnect();
        $row = array();
        global $res;

        $query = "SELECT * FROM `{$table}` WHERE wr_parent = '".$wr_id."' and wr_is_comment = 1 order by wr_comment, wr_comment_reply ";

        $db->ExecSql($query, "S");

        if ($db->Num > 0)
        {
            while($row = $db->Fetch())
            {
                $res[] = $row;
                getCommentReply($row['wr_id'], $table);
            }
        }

        return $res;
    }

    function getCommentCount($wr_num, $dm_table) {
        $db = new DBSQL();
        $db->DBconnect();
        $row = array();
        $res = array();

        $query = "SELECT * FROM `{$dm_table}` WHERE wr_num = '".$wr_num."' and wr_is_comment = 1 order by wr_comment, wr_comment_reply ";

        $db->ExecSql($query, "S");

        if ($db->Num > 0)
        {
            while($row = $db->Fetch())
            {
                $res[] = $row;
            }
        }

        return $res;
    }

    function get_list($type='list') {

    }

?>