<?
include "db_config.php";

class DBSQL{

    var $HOSTNAME = "";
    var $DBNAME = "";
    var $USERID = "";
    var $PASSWD = "";

	var $DBN="My";
	var $MYSQLCON;
	var $CON_CHECK = FALSE;

	var $ErrorMSG = true;

    var $RS;

	protected $glob;

	function DBconnect() {
		global $GLOBALS;

		$this->HOSTNAME = $GLOBALS['HOSTNAME'];
		$this->DBNAME = $GLOBALS['DBNAME'];
		$this->USERID = $GLOBALS['USERID'];
		$this->PASSWD = $GLOBALS['PASSWD'];

		if ($this->CON_CHECK == FALSE) {
			if (($this->MYSQLCON=mysqli_connect($this->HOSTNAME, $this->USERID, $this->PASSWD, $this->DBNAME))==FALSE) {
				echo("MySQL Connection error! "); exit;
			}
			$this->CON_CHECK = TRUE;
		}
		mysqli_query($this->MYSQLCON,"set session character_set_connection=utf8;");
		mysqli_query($this->MYSQLCON,"set session character_set_results=utf8;");
		mysqli_query($this->MYSQLCON,"set session character_set_client=utf8;");
	}

	function ExecSql($Query, $Option) {
		if (!($this->RS=@mysqli_query($this->MYSQLCON, $Query))) {
			//print "SQL  :  $Query";
			//exit;
			if ($this->ErrorMSG == true) {
				$this->ErrorCode = mysqli_errno($this->MYSQLCON);
				$this->Error = mysqli_error($this->MYSQLCON);
				echo $this->Error;
			}
			$this->Num =0;
		}else {
			if ($Option == "S") {
				$this->Num = mysqli_num_rows($this->RS);
			}
		}
	}
		
	function Fetch() {
		++$this->FetchCount;
		return @mysqli_fetch_array($this->RS, MYSQLI_ASSOC);
	}

	function GetPosition($Count) {
		mysqli_data_seek($this->RS,$Count);
		return @mysqli_fetch_array($this->RS);
	}

	function Close() {
		if ($this->CON_CHECK) mysqli_close($this->MYSQLCON);
	}
	function GetLog($string){			
		$ytime  = mktime (0,0,0,date("m")  , date("d"), date("Y"));     
		$CurDate = date("Ymd",$ytime); 
		$ntime  = time();   
		$TodayTime = date("Y-m-d H:i:s",$ntime); 
		$fp = fopen($CurDate.".log","a");
		fwrite($fp, $TodayTime." : ".$string."\r\n"); 
		fclose($fp);
	}
	function writeLog($string){			
		$ytime  = mktime (0,0,0,date("m")  , date("d"), date("Y"));     
		$CurDate = date("Ymd",$ytime); 
		$fp = fopen("./log_web/".$CurDate.".log","a");
		fwrite($fp, $string."\r\n"); 
		fclose($fp);
	}
	function InsertId() {
	    return mysqli_insert_id($this->MYSQLCON);
    }
}
?>