<?
	include "lib.php";

	$type =  isset($_REQUEST['type']) ? $_REQUEST['type'] : "select";

	$db = new DBSQL();
	$db -> DBconnect();

	if($type == "selectcode")
	{
		$group =  isset($_REQUEST['group']) ? $_REQUEST['group'] : "0000";
		$value = "";
		$Query = "select * from dm_common_code where dm_code_group='".$group."' order by dm_code_asc asc"; 
		$db->ExecSql($Query, "S" );

		$arData = array();

		$index = 0;
	
		while ( $arItem = $db->Fetch() )
		{
			foreach( $arItem AS $key => $val )
			{
				if ( !is_string( $key ) ) continue;
				$arFields[ $key ] = trim( $val );

			}
			if($index == 0) 
			{
				$arFields[ "selected" ] = "true";
			}else
			{
				$arFields[ "selected" ] = "";
			}
			
			array_push( $arData, $arFields );
			$index +=1;
		}
		echo json_encode( $arData );	
	}
	elseif($type == "selectboardgroup")
	{
		$group =  isset($_REQUEST['group']) ? $_REQUEST['group'] : "0000";

		
		$Query = "select * from dm_board_group order by dm_id desc"; 
		$db->ExecSql($Query, "S" );

		$arData = array();

		$index = 0;
	
		while ( $arItem = $db->Fetch() )
		{
			foreach( $arItem AS $key => $val )
			{
				if ( !is_string( $key ) ) continue;
				//$arFields[ $key ] = trim(iconv("euc-kr", "utf-8", $val));
				$arFields[ $key ] = trim( $val );
			}
			if($index == 0) 
			{
				$arFields[ "selected" ] = "true";
			}else
			{
				$arFields[ "selected" ] = "";
			}

			array_push( $arData, $arFields );
			$index +=1;
		}
		echo json_encode( $arData );	
	}
	elseif($type == "selectboardskin")
	{
		$dir    = $_VAR_BOARD_SKIN;

		$results = scandir($dir);

		$arData = array();
		$index = 0;

		foreach ($results as $result) {
			if ($result === '.' or $result === '..') continue;
			if (is_dir($dir . '/' . $result)) {
				$arFields[ "dm_code_value" ] = trim( $result );
				$arFields[ "dm_code_name" ] = trim( $result );
				if($index == 0) 
				{
					$arFields[ "selected" ] = "true";
				}else
				{
					$arFields[ "selected" ] = "";
				}
				array_push( $arData, $arFields );
				$index +=1;
			}
		}
		echo json_encode( $arData );
	}
?>