<?php
/**
 * Created by PhpStorm.
 * User: mooyoung
 * Date: 2020-03-16
 * Time: 오후 4:47
 * 용도 : 셀렉트박스 가져오기
 */

include "../../lib/lib.php";

$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : "";

switch ($type) {
    case "select_domain":
        $search = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : "";

        $db = new DBSQL();
        $db->DBconnect();

        $all = isset($_REQUEST['all']) ? trim($_REQUEST['all']) : "";

        $Query = "select * from dm_common_code where dm_code_group='1001' order by dm_code_asc";

        $db->ExecSql($Query, "S" );

        $arData = array();

        if($search == "1")
        {
            $arALL = array( "dm_id" => "","dm_domain_nm" => "전체","selected" => "true");
            array_push( $arData, $arALL );
        }
        $index = 0;

        if ( $db->Num > 0 )
        {
            while ( $arItem = $db->Fetch() )
            {
                foreach( $arItem AS $key => $val )
                {
                    if ( !is_string( $key ) ) continue;
                    $arFields[ $key ] = $val;
                }

                if($index == 0)
                {
                    $arFields[ "selected" ] = "true";
                }else
                {
                    $arFields[ "selected" ] = "";
                }

                if($search == "1")
                {
                    $arFields[ "selected" ] = "";
                }
                $index +=1;
                array_push( $arData, $arFields );
            }
        }

    break;

    case "select_code":
        $group =  isset($_REQUEST['group']) ? $_REQUEST['group'] : "";
        $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : "";
        $selected = isset($_REQUEST['selected']) ? $_REQUEST['selected'] : "";
        $counsel_type =  isset($_REQUEST['counsel_type']) ? $_REQUEST['counsel_type'] : "";

        $db = new DBSQL();
        $db -> DBconnect();

        $all = isset($_REQUEST['all']) ? $_REQUEST['all'] : "";

        $search_Sql = "";

        $Query = "select * from dm_common_code where dm_code_group='".$group."' ".$search_Sql." order by dm_code_asc";

        $db->ExecSql($Query, "S" );

        $arData = array();

        if($search == "1" || $search == "2")
        {
            $arALL = array( "dm_code_value" => "","dm_code_name" => "전체","selected" => "true");
            array_push( $arData, $arALL );
        }
        $index = 0;

        if ( $db->Num > 0 )
        {

            while ( $arItem = $db->Fetch() )
            {
                if($index == 0)
                {
                    if($selected == "")
                    {
                        $arItem[ "selected" ] ="true";
                    }else
                    {
                        if($arItem[ 'dm_code_value' ] == $selected)
                        {
                            $arItem[ "selected" ] ="true";
                        }
                    }
                    if($search == "1")
                    {
                        $arItem[ "selected" ] = "";
                    }

                    foreach( $arItem AS $key => $val )
                    {
                        if ( !is_string( $key ) ) continue;
                        $arFields[ $key ] = $val;
                    }

                    array_push( $arData, $arFields );
                } else {
                    if($selected == "")
                    {
                        $arItem[ "selected" ] ="";
                    }else
                    {
                        if($arItem[ 'dm_code_value' ] == $selected)
                        {
                            $arItem[ "selected" ] ="true";
                        }else
                        {
                            $arItem[ "selected" ] ="";
                        }
                    }
                    if($search == "1")
                    {
                        $arItem[ "selected" ] = "";
                    }

                    foreach( $arItem AS $key => $val )
                    {
                        if ( !is_string( $key ) ) continue;
                        $arFields[ $key ] = $val;
                    }

                    array_push( $arData, $arFields );
                }
                $index +=1;
            }
        } else
        {

        }

    break;

    case "select_menu_parent":
        $search = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : "";
        $parent_id = isset($_REQUEST['parent_id']) ? trim($_REQUEST['parent_id']) : "";
        $selected = isset($_REQUEST['selected']) ? trim($_REQUEST['selected']) : "";
        $arFields = array();

        $db = new DBSQL();
        $db->DBconnect();

        $all = isset($_REQUEST['all']) ? trim($_REQUEST['all']) : "";

        $Query = "select * from dm_menus";

        $db->ExecSql($Query, "S" );

        $arData = array();

        if($search == "1")
        {
            $arALL = array( "dm_id" => "","dm_domain_nm" => "전체","selected" => "true");
            array_push( $arData, $arALL );
        }
        $index = 0;

        if ( $db->Num > 0 )
        {
            while ( $arItem = $db->Fetch() )
            {
                foreach( $arItem AS $key => $val )
                {
                    if ( !is_string( $key ) ) continue;
                    $arFields[ $key ] = $val;
                }

                if($index == 0)
                {
                    $arFields[ "selected" ] = "true";
                }else
                {
                    if($selected == "")
                    {
                        $arFields[ "selected" ] ="";
                    }else
                    {
                        if ($arFields[ 'dm_id' ] == $selected)
                        {
                            $arFields[ "selected" ] ="true";
                        }else
                        {
                            $arFields[ "selected" ] ="";
                        }
                    }
                }

                if($search == "1")
                {
                    $arFields[ "selected" ] = "";
                }
                $index +=1;
                array_push( $arData, $arFields );
            }
        }
    break;

    case "select_menu_thema":
        $arData = array();

        $arALL = array( "dm_id" => "","dm_domain_nm" => "전체","selected" => "true");
        array_push( $arData, $arALL );

        $dir = $_SERVER['DOCUMENT_ROOT']."/diam/web/menu/";


        $dir_handle=opendir($dir); // 디렉토리 열기
        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                $cnt = 0;
                while (($file = readdir($handle)) !== false){
                    if ($file == "." || $file == "..") continue;
                    if (is_dir($dir.$file)) {
                        $arData[$cnt]['thema_text'] = $file;
                        if ($cnt == 0) {
                            $arData[$cnt]['selected'] = true;
                        }
                    }
                    $cnt++;
                }
                closedir($handle);
            }
        }
        sort($arData);

        break;

    case "select_content_type":
        $group =  isset($_REQUEST['group']) ? $_REQUEST['group'] : "";
        $content_type =  isset($_REQUEST['content_type']) ? $_REQUEST['content_type'] : "";
        $selected = isset($_REQUEST['selected']) ? $_REQUEST['selected'] : "";
        $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : "";
        $search_sql = "";

        switch ($content_type) {
            default:
                $search_sql = "";
            break;

            case "1":
                $search_sql .= " AND dm_code_value = 'TOP' OR dm_code_value = 'BOTTOM' ";
            break;

            case "2":
                $search_sql .= " AND dm_code_value = 'LEFT' OR dm_code_value = 'RIGHT' ";
            break;

            case "3":
                $search_sql .= " AND dm_code_value <> 'BOTTOM' ";
            break;

            case "4":
                $search_sql .= " AND dm_code_value <> 'TOP' ";
            break;

            case "5":
                $search_sql .= " AND dm_code_value <> 'LEFT' ";
            break;

        }

        $db = new DBSQL();
        $db -> DBconnect();

        $all = isset($_REQUEST['all']) ? $_REQUEST['all'] : "";

        $Query = "select * from dm_common_code where dm_code_group='".$group."' ".$search_sql." order by dm_code_asc";

        $db->ExecSql($Query, "S" );

        $arData = array();
        if($content_type) {
            if ($search == 1) {
                $arALL = array( "dm_code_value" => "","dm_code_name" => "선택", "selected" => "true");
            } else {
                $arALL = array( "dm_code_value" => "","dm_code_name" => "선택");
            }
        } else {
            $arALL = array( "dm_code_value" => "","dm_code_name" => "선택", "selected" => "true");
        }
        array_push( $arData, $arALL );

        $index = 0;

        if ( $db->Num > 0 )
        {

            while ( $arItem = $db->Fetch() )
            {
                if($index == 0)
                {
                    if($selected == "")
                    {
                        $arItem[ "selected" ] ="true";
                    }else
                    {
                        if($arItem[ 'dm_code_value' ] == $selected)
                        {
                            $arItem[ "selected" ] ="true";
                        }
                    }
                    if($search == "1")
                    {
                        $arItem[ "selected" ] = "";
                    }

                    foreach( $arItem AS $key => $val )
                    {
                        if ( !is_string( $key ) ) continue;
                        $arFields[ $key ] = $val;
                    }

                    array_push( $arData, $arFields );
                } else {
                    if($selected == "")
                    {
                        $arItem[ "selected" ] ="";
                    }else
                    {
                        if($arItem[ 'dm_code_value' ] == $selected)
                        {
                            $arItem[ "selected" ] ="true";
                        }else
                        {
                            $arItem[ "selected" ] ="";
                        }
                    }
                    if($search == "1")
                    {
                        $arItem[ "selected" ] = "";
                    }

                    foreach( $arItem AS $key => $val )
                    {
                        if ( !is_string( $key ) ) continue;
                        $arFields[ $key ] = $val;
                    }

                    array_push( $arData, $arFields );
                }
                $index +=1;
            }
        } else
        {

        }

        break;

    case "select_board":
        $selected = isset($_REQUEST['selected']) ? $_REQUEST['selected'] : "";
        $arData = array();
        $arReturn = array();

        $db = new DBSQL();
        $db -> DBconnect();


        $query = "SELECT `dm_id`, `dm_subject` FROM `dm_board`";
        $db->ExecSql($query, "S");

        if ($db -> Num > 0) {
            while ($arReturn = $db->Fetch()) {
                if($selected == "")
                {
                    $arReturn[ "selected" ] ="true";
                }else
                {
                    if($arReturn[ 'dm_id' ] == $selected)
                    {
                        $arReturn[ "selected" ] ="true";
                    }
                }
                $arData[] = $arReturn;
            }
        }

    break;

    case "select_board_list_count":
        $arData = array();
        $selected = isset($_REQUEST['selected']) ? $_REQUEST['selected'] : "";

        for ($i=1; $i<16; $i++) {
            if($selected == "")
            {
                if ($i == 1) {
                    $temp = array("dm_code_value" => $i, "dm_code_text" => $i, "selected" => true);
                } else {
                    $temp = array("dm_code_value" => $i, "dm_code_text" => $i);
                }
            }else
            {
                if($i == $selected)
                {
                    $temp = array("dm_code_value" => $i, "dm_code_text" => $i, "selected" => true);
                } else {
                    $temp = array("dm_code_value" => $i, "dm_code_text" => $i);
                }
            }
            array_push($arData, $temp);
        }
    break;

    case "select_layout_thema":
        $arData = array();
        $db = new DBSQL();
        $db -> DBconnect();
        $selected = isset($_REQUEST['selected']) ? $_REQUEST['selected'] : "";

        $dir = $_SERVER['DOCUMENT_ROOT']."/diam/web/thema/";

        $dir_handle=opendir($dir); // 디렉토리 열기
        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                $cnt = 0;
                while (($file = readdir($handle)) !== false){
                    if ($file == "." || $file == "..") continue;
                    if (is_dir($dir.$file)) {
                        $arData[$cnt]['thema_text'] = $file;

                        if ($selected) {
                            if ($arData[$cnt]['thema_text'] == $selected) {
                                $arData[$cnt]['selected'] = true;
                            } else {
                                $arData[$cnt]['selected'] = "";
                            }
                        } else {
                            if ($cnt == 0) {
                                $arData[$cnt]['selected'] = true;
                            }
                        }
                    }
                    $cnt++;
                }
                closedir($handle);
            }
        }
        sort($arData);

        break;

    case "select_page":
        $arData = array();
        $db = new DBSQL();
        $db -> DBconnect();

        $dir = $_SERVER['DOCUMENT_ROOT']."/diam/web/temp/";

        $dir_handle=opendir($dir); // 디렉토리 열기
        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                $cnt = 0;
                $arData[0]['id'] = "Temp";
                $arData[0]['text'] = "Temp";
                while (($file = readdir($handle)) !== false){
                    if ($file == "." || $file == "..") continue;
                    if (is_file($dir.$file)) {
                        $arData[0]['children'][$cnt]['id'] = $file;
                        $arData[0]['children'][$cnt]['text'] = $file;
                        $arData[0]['children'][$cnt]['file_src'] = $dir.$file;
                    }
                    $cnt++;
                }
                closedir($handle);
            }
        }
        sort($arData);

        break;

    case "select_page_table":
        $arData = array();
        $db = new DBSQL();
        $db -> DBconnect();

        $dir = $_SERVER['DOCUMENT_ROOT']."/diam/web/temp/";

        $dir_handle=opendir($dir); // 디렉토리 열기
        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                $cnt = 0;
                $arData[$cnt]['id'] = "Temp";
                $arData[$cnt]['text'] = "Temp";
                while (($file = readdir($handle)) !== false){
                    if ($file == "." || $file == "..") continue;
                    if (is_file($dir.$file)) {
                        $arData[$cnt]['id'] = $file;
                        $arData[$cnt]['text'] = $file;
                        $arData[$cnt]['dm_file_src'] = $dir.$file;
                    }
                    $cnt++;
                }
                closedir($handle);
            }
        }
        sort($arData);

        break;

    case "select_domain_id":
        $arData = array();
        $arReturn = array();
        $db = new DBSQL();
        $db -> DBconnect();

        $query = "SELECT * FROM `dm_domain_list` WHERE `dm_domain_status` = 1";

        $db->ExecSql($query, "S");

        if ($db->Num >0) {
            while($arReturn = $db->Fetch()) {
                $arData[] = $arReturn;
            }
        }
        break;

    case "select_page_type":
        $arData = array();
        $db = new DBSQL();
        $db -> DBconnect();
        $page_type = isset($_REQUEST['page_type']) ? $_REQUEST['page_type'] : "";

        $dir = $_SERVER['DOCUMENT_ROOT']."/diam/web/base/".$page_type."/";

        $dir_handle=opendir($dir); // 디렉토리 열기
        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                $cnt = 0;
                while (($file = readdir($handle)) !== false){
                    if ($file == "." || $file == "..") continue;
                    if (is_file($dir.$file)) {
                        $arData[$cnt]['dm_select_value'] = $file;
                        if ($cnt == 0) {
                            $arData[$cnt]['selected'] = true;
                        }
                    }
                    $cnt++;
                }
                closedir($handle);
            }
        }
        sort($arData);

        break;


}
echo json_encode($arData);
?>