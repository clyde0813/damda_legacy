/********************************************
// menu
*********************************************/
function MM_swapImgRestore() { //v3.0
    var i, x, a = document.MM_sr; for (i = 0; a && i < a.length && (x = a[i]) && x.oSrc; i++) x.src = x.oSrc;
}

function MM_preloadImages() { //v3.0
    var d = document; if (d.images) {
        if (!d.MM_p) d.MM_p = new Array();
        var i, j = d.MM_p.length, a = MM_preloadImages.arguments; for (i = 0; i < a.length; i++)
            if (a[i].indexOf("#") != 0) { d.MM_p[j] = new Image; d.MM_p[j++].src = a[i]; } 
    }
}

function MM_findObj(n, d) { //v4.01
    var p, i, x; if (!d) d = document; if ((p = n.indexOf("?")) > 0 && parent.frames.length) {
        d = parent.frames[n.substring(p + 1)].document; n = n.substring(0, p);
    }
    if (!(x = d[n]) && d.all) x = d.all[n]; for (i = 0; !x && i < d.forms.length; i++) x = d.forms[i][n];
    for (i = 0; !x && d.layers && i < d.layers.length; i++) x = MM_findObj(n, d.layers[i].document);
    if (!x && d.getElementById) x = d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
    var i, j = 0, x, a = MM_swapImage.arguments; document.MM_sr = new Array; for (i = 0; i < (a.length - 2); i += 3)
        if ((x = MM_findObj(a[i])) != null) { document.MM_sr[j++] = x; if (!x.oSrc) x.oSrc = x.src; x.src = a[i + 2]; }
}

function Chtab(layername,xid,len){
	try{
		for(i=1;i <= len; i++){
			if (i==xid)	document.getElementById(layername+i).style.display="block";
			else	document.getElementById(layername+i).style.display="none";
		}
	} catch (e)	{}
}

function Ch_Class(layername,xid,len,class_name){
	try{
		for(i=1;i <= len; i++){
			if (i==xid)	document.getElementById(layername+i).className=class_name;
			else	document.getElementById(layername+i).className="";
		}
	} catch (e)	{}
}

/********************************************
//  pop open
*********************************************/
function openPop(link, param) {

    var windows_width = 500; //��â�� �ʺ� 
    var windows_height = 320; //��â�� ���� 
    var screen_width = screen.availWidth; //ȭ�� �ʺ� (�ػ�) 
    var screen_height = screen.availHeight; //ȭ�� ���� (�ػ�)
    var open_x = (screen_width - 500) / 2;
    var open_y = (screen_height - 320) / 2;


    window.open(link + param, "CAR", "status=yes, left=" + open_x + ",top=" + open_y + ",width=500,height=320");
}

/********************************************
// jquery ajax failed method
*********************************************/
function CallFailed(error) {
    alert(error.get_message());
}
/********************************************
//   �ڹٽ�ũ��Ʈ�� replace �Լ��� ���ڿ� �߿� �Ȱ��� �ܾ �־ �� �ѹ� �ۿ� �ٲ��� �ʱ� 
//   ������ ������ ������ �Ȱ��� �ܾ ��� �ٲ���� �Ѵ�.
*********************************************/
function js_replace(str, oldStr, newStr)
{
    for(var i=0;i<str.length;i++)
    {
        if(str.substring(i, i+oldStr.length) == oldStr)
        {
            str = str.substring(0, i) + newStr + str.substring(i+oldStr.length, str.length)
        }
    }

    return str
}
/********************************************
// ���ڿ��� �յ� ���� ����		str: ���� ���Ŵ�� ���ڿ�
*********************************************/
function Trim(str)
{
	var sw1 = true;
	var sw2 = true;

	while (sw1)
	{
		if (str.indexOf(' ') == 0)
		{
			str = str.substr(1, str.length - 1);
		}
		else
		{
			if (sw2)
			{
				sw2 = false;
				str = str.split('').reverse().join('');
			}
			else sw1 = false;
		}
	}
	return str.split('').reverse().join('');
}
/********************************************
//���� ���ĺ��� �������� �˻��Ѵ�.
*********************************************/
function isAlphaNumber(sValue) { 
	if(sValue.length == 0)
		return false;

	sValue = sValue.toUpperCase();
	for(var i=0; i < sValue.length; i++) {
		if(!(('A' <= sValue.charAt(i) && sValue.charAt(i) <= 'Z') || ('0' <= sValue.charAt(i) && sValue.charAt(i) <= '9')))
			return false;
	}
	return true;
}

/********************************************
// ����: ����Ű �Է½� true ��ȯ
// ���̵� �Է�.. Ư����ȣ -_ ���
*********************************************/
function FilterID() {
    if (event.keyCode == 13)	// ����Ű
        return true;

    if (((event.keyCode > 31 && event.keyCode < 48) || (event.keyCode > 57 && event.keyCode < 65) ||
				 (event.keyCode > 90 && event.keyCode < 97) || (event.keyCode > 122 && event.keyCode < 127))
			 && event.keyCode != 45 && event.keyCode != 95) {
        event.returnValue = false;
        event.cancelBubble = true;
    }

    return false;
}
/********************************************
// �н����� �Է�.. Ư����ȣ -_!@#$ ���
*********************************************/
function FilterPwd() {
    if (event.keyCode == 13)	// ����Ű
        return true;

    if (((event.keyCode > 31 && event.keyCode < 48) || (event.keyCode > 57 && event.keyCode < 64) ||
				 (event.keyCode > 90 && event.keyCode < 97) || (event.keyCode > 122 && event.keyCode < 127))
			  && event.keyCode != 33 && event.keyCode != 35 && event.keyCode != 36 && event.keyCode != 45 && event.keyCode != 95) {
        event.returnValue = false;
        event.cancelBubble = true;
    }

    return false;
}
/********************************************
// �ܾ� �Է�.. Ư����ȣ ����/����()[],@/_-���
*********************************************/
function FilterWords() {
    if (event.keyCode == 13)	// ����Ű
        return true;

    if (((event.keyCode > 32 && event.keyCode < 48) || (event.keyCode > 57 && event.keyCode < 65) ||
					(event.keyCode > 90 && event.keyCode < 97) || (event.keyCode > 122 && event.keyCode < 127))
				 && (event.keyCode != 40 && event.keyCode != 41 && event.keyCode != 44 && event.keyCode != 45
				 			&& event.keyCode != 91 && event.keyCode != 93 && event.keyCode != 95 && event.keyCode != 47 && event.keyCode != 64)
			) {
        event.returnValue = false;
        event.cancelBubble = true;
    }

    return false;
}
/********************************************
// �ܾ� �Է�.. Ư����ȣ ����/���鹫��/[],@/_-���
*********************************************/
function FilterWord() {
    if (event.keyCode == 13)	// ����Ű
        return true;

    if (((event.keyCode > 31 && event.keyCode < 48) || (event.keyCode > 57 && event.keyCode < 65) ||
					(event.keyCode > 90 && event.keyCode < 97) || (event.keyCode > 122 && event.keyCode < 127))
				 && (event.keyCode != 40 && event.keyCode != 41 && event.keyCode != 44 && event.keyCode != 45
				 			&& event.keyCode != 91 && event.keyCode != 93 && event.keyCode != 95 && event.keyCode != 47 && event.keyCode != 64)
			) {
        event.returnValue = false;
        event.cancelBubble = true;
    }

    return false;
}
/********************************************
// ��¥ �Է�.. ����, - �� ��� / �ٸ� ���� ����/���鹫��
*********************************************/
function FilterDate() {
    if (event.keyCode == 13)	// ����Ű
        return true;

    if (((event.keyCode < 48) || (event.keyCode > 57)) && (event.keyCode != 45)) {
        event.returnValue = false;
        event.cancelBubble = true;
    }

    return false;
}
/********************************************
// ���� �Է�.. �ٸ� ���� ����/���鹫��
*********************************************/
function FilterNum() {
    if (event.keyCode == 13)	// ����Ű
        return true;

    if ((event.keyCode < 48) || (event.keyCode > 57)) {
        event.returnValue = false;
        event.cancelBubble = true;
    }

    return false;
}
/********************************************
// �����Է�.. ���ڿ� ','�� �Է�/���鹫��
*********************************************/
function FilterNums() {
    if (event.keyCode == 13)	// ����Ű
        return true;

    if (((event.keyCode < 48) || (event.keyCode > 57)) && (event.keyCode != 44)) {
        event.returnValue = false;
        event.cancelBubble = true;
    }

    return false;
}

/********************************************
// ��ȣ �Է�.. ���ڿ� '-'�� �Է�/���鹫��
*********************************************/
function FilterPhone() {
    if (event.keyCode == 13)	// ����Ű
        return true;

    if (((event.keyCode < 48) || (event.keyCode > 57)) && (event.keyCode != 45)) {
        event.returnValue = false;
        event.cancelBubble = true;
    }

    return false;
}
/********************************************
// ��ȣ �Է�.. ���ڿ� '*#'�� �Է�/���鹫��
*********************************************/
function FilterPhonePwd() {
    if (event.keyCode == 13)	// ����Ű
        return true;

    if (((event.keyCode < 48) || (event.keyCode > 57)) && (event.keyCode != 35) && (event.keyCode != 42)) {
        event.returnValue = false;
        event.cancelBubble = true;
    }

    return false;
}
/********************************************
// �ؽ�Ʈ �Է�.. Ư����ȣ ����/����()[],@/_-���
*********************************************/
function FilterContents() {
    if (event.keyCode == 13)	// ����Ű
        return true;

    if (((event.keyCode > 32 && event.keyCode < 48) || (event.keyCode > 57 && event.keyCode < 65) ||
					(event.keyCode > 90 && event.keyCode < 97) || (event.keyCode > 122 && event.keyCode < 127))
				 && (event.keyCode != 40 && event.keyCode != 41 && event.keyCode != 44 && event.keyCode != 45 && event.keyCode != 58
				 			&& event.keyCode != 91 && event.keyCode != 93 && event.keyCode != 95 && event.keyCode != 47 && event.keyCode != 64)
			) {
        event.returnValue = false;
        event.cancelBubble = true;
    }

    return false;
}

/********************************************
// QueryString ��������
*********************************************/
function getQuerystring(key, default_)
{
    if (default_==null) default_=""; 
    
    key = key.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
    
    var regex = new RegExp("[\\?&]" + key + "=([^&#]*)");

    var qs = regex.exec(window.location.href);
    if(qs == null)
        return default_;
    else
        return qs[1];
}
/********************************************
// easyUI �޷� ��������
*********************************************/
function myformatter(date){
	var y = date.getFullYear();
	var m = date.getMonth()+1;
	var d = date.getDate();
	return y+'-'+(m<10?('0'+m):m)+'-'+(d<10?('0'+d):d);
}
function myparser(s){
	if (!s) return new Date();
	var ss = (s.split('-'));
	var y = parseInt(ss[0],10);
	var m = parseInt(ss[1],10);
	var d = parseInt(ss[2],10);
	if (!isNaN(y) && !isNaN(m) && !isNaN(d)){
		return new Date(y,m-1,d);
	} else {
		return new Date();
	}
}
/********************************************
// ����� ����
*********************************************/
function fnUser(){
	location.href = "/acWeb/ui/user/user.html";
}
/********************************************
// �ݹ� ����
*********************************************/
function fnCallback(){
	location.href = "/acWeb/ui/callback/callback.html";
}

/********************************************
// �α׾ƿ�
*********************************************/
function fnLogout(){
	location.href = "/acWeb/biz/logout.php";
}
/********************************************
// ����Ȯ��
*********************************************/
function isNumber(s) {

  s += ''; // ���ڿ��� ��ȯ

  s = s.replace(/^\s*|\s*$/g, ''); // �¿� ���� ����

  if (s == '' || isNaN(s)) return false;

  return true;

}
function Hashtable()
{
	this.length = 0;
	this.items = new Array();
	for (var i = 0; i < arguments.length; i += 2) {
		if (typeof(arguments[i + 1]) != 'undefined') {
			this.items[arguments[i]] = arguments[i + 1];
			this.length++;
		}
	}
   
	this.removeItem = function(in_key)
	{
		var tmp_value;
		if (typeof(this.items[in_key]) != 'undefined') {
			this.length--;
			var tmp_value = this.items[in_key];
			delete this.items[in_key];
		}
	   
		return tmp_value;
	}
 
	this.getLength = function() {
		return this.length;
	}
 
	this.getItem = function(in_key) {
		return this.items[in_key];
	}
 
	this.setItem = function(in_key, in_value)
	{
		if (typeof(in_value) != 'undefined') {
			if (typeof(this.items[in_key]) == 'undefined') {
				this.length++;
			}
			this.items[in_key] = in_value;
		}
		return in_value;
	}
	this.hasItem = function(in_key)
	{
		return typeof(this.items[in_key]) != 'undefined';
	}
}
function colorTable(index)
{     
	  var ColorValue = "#4682B4";
	  if (index == '1')       ColorValue = "#F0F8FF";
      if (index == '1')       ColorValue = "#F0F8FF";
      if (index == '2')  ColorValue = "#FAEBD7";
      if (index == '3')       ColorValue = "#00FFFF";
      if (index == '4')  ColorValue = "#7FFFD4";
      if (index == '5')  ColorValue = "#F0FFFF";
      if (index == '6')  ColorValue = "#F5F5DC";
      if (index == '7')  ColorValue = "#FFE4C4";
      if (index == '8')  ColorValue = "#FFFF00";
      if (index == '9')  ColorValue = "#FFEBCD";
      if (index == '10')  ColorValue = "#0000FF";
      if (index == '11')  ColorValue = "#8A2BE2";
      if (index == '12')  ColorValue = "#A52A2A";
      if (index == '13')  ColorValue = "#DEB887";
      if (index == '14')  ColorValue = "#5F9EA0";
      if (index == '15')  ColorValue = "#7FFF00";
      if (index == '16')  ColorValue = "#D2691E";
      if (index == '17')  ColorValue = "#FF7F50";
      if (index == '28')  ColorValue = "#6495ED";
      if (index == '19')  ColorValue = "#FFF8DC";
      if (index == '20')  ColorValue = "#DC143C";
      if (index == '21')  ColorValue = "#00FFFF";
      if (index == '22')  ColorValue = "#00008B";
      if (index == '23')  ColorValue = "#008B8B";
      if (index == '24')  ColorValue = "#B8860B";
      if (index == '25')  ColorValue = "#A9A9A9";
      if (index == '26')  ColorValue = "#006400";
      if (index == '27')  ColorValue = "#BDB76B";
      if (index == '28')  ColorValue = "#8B008B";
      if (index == '29')  ColorValue = "#556B2F";
      if (index == '30')  ColorValue = "#FF8C00";
      if (index == '31')  ColorValue = "#9932CC";
      if (index == '32')  ColorValue = "#8B0000";
      if (index == '33')  ColorValue = "#E9967A";
      if (index == '34')  ColorValue = "#8FBC8F";
      if (index == '35')  ColorValue = "#483D8B";
      if (index == '36')  ColorValue = "#2F4F4F";
      if (index == '37')  ColorValue = "#00CED1";
      if (index == '38')  ColorValue = "#9400D3";
      if (index == '39')  ColorValue = "#FF1493";
      if (index == '40')  ColorValue = "#00BFFF";
      if (index == '41')  ColorValue = "#696969";
      if (index == '42')  ColorValue = "#1E90FF";
      if (index == '43')  ColorValue = "#B22222";
      if (index == '44')  ColorValue = "#FFFAF0";
      if (index == '45')  ColorValue = "#228B22";
      if (index == '46')  ColorValue = "#FF00FF";
      if (index == '47')  ColorValue = "#DCDCDC";
      if (index == '48')  ColorValue = "#F8F8FF";
      if (index == '49')  ColorValue = "#FFD700";
      if (index == '50')  ColorValue = "#DAA520";
      if (index == '51')  ColorValue = "#808080";
      if (index == '52')  ColorValue = "#008000";
      if (index == '53')  ColorValue = "#ADFF2F";
      if (index == '54')  ColorValue = "#F0FFF0";
      if (index == '55')  ColorValue = "#FF69B4";
      if (index == '56')  ColorValue = "#CD5C5C";
      if (index == '57')  ColorValue = "#4B0082";
      if (index == '58')  ColorValue = "#FFFFF0";
      if (index == '59')  ColorValue = "#F0E68C";
      if (index == '60')  ColorValue = "#E6E6FA";
      if (index == '61')  ColorValue = "#FFF0F5";
      if (index == '62')  ColorValue = "#7CFC00";
      if (index == '63')  ColorValue = "#FFFACD";
      if (index == '64')  ColorValue = "#ADD8E6";
      if (index == '65')  ColorValue = "#F08080";
      if (index == '66')  ColorValue = "#E0FFFF";
      if (index == '67')  ColorValue = "#FAFAD2";
      if (index == '68')  ColorValue = "#90EE90";
      if (index == '69')  ColorValue = "#D3D3D3";
      if (index == '70')  ColorValue = "#FFB6C1";
      if (index == '71')  ColorValue = "#FFA07A";
      if (index == '72')  ColorValue = "#20B2AA";
      if (index == '73')  ColorValue = "#87CEFA";
      if (index == '74')  ColorValue = "#778899";
      if (index == '75')  ColorValue = "#B0C4DE";
      if (index == '76')  ColorValue = "#FFFFE0";
      if (index == '77')  ColorValue = "#00FF00";
      if (index == '78')  ColorValue = "#32CD32";
      if (index == '79')  ColorValue = "#FAF0E6";
      if (index == '80')  ColorValue = "#FF00FF";
      if (index == '81')  ColorValue = "#800000";
      if (index == '82')  ColorValue = "#66CDAA";
      if (index == '83')  ColorValue = "#0000CD";
      if (index == '84')  ColorValue = "#BA55D3";
      if (index == '85')  ColorValue = "#9370DB";
      if (index == '86')  ColorValue = "#3CB371";
      if (index == '87')  ColorValue = "#7B68EE";
      if (index == '88')  ColorValue = "#00FA9A";
      if (index == '89')  ColorValue = "#48D1CC";
      if (index == '90')  ColorValue = "#C71585";
      if (index == '91')  ColorValue = "#191970";
      if (index == '92')  ColorValue = "#F5FFFA";
      if (index == '93')  ColorValue = "#FFE4E1";
      if (index == '94')  ColorValue = "#FFE4B5";
      if (index == '95')  ColorValue = "#FFDEAD";
      if (index == '96')  ColorValue = "#000080";
      if (index == '97')  ColorValue = "#FDF5E6";
      if (index == '98')  ColorValue = "#808000";
      if (index == '99')  ColorValue = "#6B8E23";
      if (index == '100')  ColorValue = "#FFA500";
      if (index == '101')  ColorValue = "#FF4500";
      if (index == '102')  ColorValue = "#DA70D6";
      if (index == '103')  ColorValue = "#EEE8AA";
      if (index == '104')  ColorValue = "#98FB98";
      if (index == '105')  ColorValue = "#AFEEEE";
      if (index == '106')  ColorValue = "#DB7093";
      if (index == '107')  ColorValue = "#FFEFD5";
      if (index == '108')  ColorValue = "#FFDAB9";
      if (index == '109')  ColorValue = "#CD853F";
      if (index == '110')  ColorValue = "#FFC0CB";
      if (index == '111')  ColorValue = "#DDA0DD";
      if (index == '112')  ColorValue = "#B0E0E6";
      if (index == '113')  ColorValue = "#800080";
      if (index == '114')  ColorValue = "#FF0000";
      if (index == '115')  ColorValue = "#BC8F8F";
      if (index == '116')  ColorValue = "#4169E1";
      if (index == '118')  ColorValue = "#8B4513";
      if (index == '119')  ColorValue = "#FA8072";
      if (index == '120')  ColorValue = "#F4A460";
      if (index == '121')  ColorValue = "#2E8B57";
      if (index == '122')  ColorValue = "#FFF5EE";
      if (index == '123')  ColorValue = "#A0522D";
      if (index == '124')  ColorValue = "#C0C0C0";
      if (index == '125')  ColorValue = "#87CEEB";
      if (index == '126')  ColorValue = "#6A5ACD";
      if (index == '127')  ColorValue = "#708090";
      if (index == '128')  ColorValue = "#FFFAFA";
      if (index == '129')  ColorValue = "#00FF7F";
      if (index == '130')  ColorValue = "#4682B4";
      if (index == '131')  ColorValue = "#D2B48C";
      if (index == '132')  ColorValue = "#008080";
      if (index == '133')  ColorValue = "#D8BFD8";
      if (index == '134')  ColorValue = "#FF6347";
      if (index == '135')  ColorValue = "#40E0D0";
      if (index == '136')  ColorValue = "#EE82EE";
      if (index == '137')  ColorValue = "#F5DEB3";
      if (index == '138')  ColorValue = "#FFFFFF";
      if (index == '139')  ColorValue = "#F5F5F5";
      if (index == '140')  ColorValue = "#000000";
      if (index == '141')  ColorValue = "#9ACD32";

	  return ColorValue;
    
}
function leftPad(str, fillChar, length) 
{
	if (str.length > length) return str;
	var returnStr = "";
	var i;
	for (i = str.length; i < length; i++) 
	{
		returnStr = returnStr + fillChar;
	}
	returnStr = returnStr + str;

	return returnStr;
}
function str_pad(input, length, string, type) {
	if (input.length >= length) return input;	
	
	var string = string || "0", 
		input = input + "",
		type = type || "STR_PAD_LEFT";
		inputLength = input.length;
		pad = Array(length - inputLength + 1).join(string);
	switch (type) {
		case "STR_PAD_LEFT": 
			result = pad + input;
			break;
		case "STR_PAD_RIGHT": 
			result = input + pad;
			break;
		case "STR_PAD_BOTH": 
			var i = parseInt((length - inputLength) / 2);
			result = pad.substring(0,i) + input + pad.substring(i, length - i + 1);			
			break;
	}
    return result;
 
}
function formatTime(data)
{
	nHour = parseInt(data/3600);
	nMin = parseInt((data%3600)/60);
	nSec = data%60;
	return ( (nHour < 10) ? "0" : "" ) + nHour + ":" + ( (nMin < 10) ? "0" : "" ) + nMin + ":" + ( (nSec < 10) ? "0" : "" ) + nSec;
}
function replaceAll(str, searchStr, replaceStr) 
{
    return str.split(searchStr).join(replaceStr);
}

function AddComma(num)
{
    var regexp = /\B(?=(\d{3})+(?!\d))/g;
    return num.toString().replace(regexp, ',');
}

DateSearch = function() {
    // DateSearch.form = document.getElementById('shop_list');
    DateSearch.date = new Date();
    //올해
    DateSearch.date.curYear = DateSearch.date.getFullYear();
    //이번달
    DateSearch.date.curMonth = DateSearch.date.getMonth() ;
    //오늘
    DateSearch.date.curDate = DateSearch.date.getDate();
    //요일
    DateSearch.date.curDay = DateSearch.date.getDay();
    //오늘 YYYY-mm-dd
    DateSearch.getToday = function() {
        var fullDate = DateSearch.makeFullDate(DateSearch.date.curYear, DateSearch.date.curMonth, DateSearch.date.curDate);
        $("#search_start_date").datebox('setValue', fullDate);
        $("#search_end_date").datebox('setValue', fullDate);
    };
    // 7일전 YYYY-mm-dd
    DateSearch.getNextSevenDays = function() {
        var sevenDaysLater = new Date(DateSearch.date.curYear, DateSearch.date.curMonth, DateSearch.date.curDate - 6) ;
        var nextSevenYear = sevenDaysLater.getFullYear();
        var nextSevenMonth = sevenDaysLater.getMonth();
        var nextSevenDate = sevenDaysLater.getDate();
        //오늘
        $("#search_start_date").datebox('setValue', DateSearch.makeFullDate(nextSevenYear, nextSevenMonth, nextSevenDate));
        //7일뒤
        $("#search_end_date").datebox('setValue', DateSearch.makeFullDate(DateSearch.date.curYear, DateSearch.date.curMonth, DateSearch.date.curDate));
    };
    //15일전 YYYY-mm-dd
    DateSearch.getNextFiftheenDays = function() {
        var fifteenDaysLater = new Date(DateSearch.date.curYear, DateSearch.date.curMonth, DateSearch.date.curDate - 14) ;
        var nextFifteenYear = fifteenDaysLater.getFullYear();
        var nextFifteenMonth = fifteenDaysLater.getMonth();
        var nextFifteenDate = fifteenDaysLater.getDate();
        //오늘
        $("#search_start_date").datebox('setValue', DateSearch.makeFullDate(nextFifteenYear, nextFifteenMonth, nextFifteenDate));
        //15일뒤
        $("#search_end_date").datebox('setValue', DateSearch.makeFullDate(DateSearch.date.curYear, DateSearch.date.curMonth, DateSearch.date.curDate));
    };
    //이번주 YYYY-mm-dd
    DateSearch.getThisWeek = function() {
        var startOfWeek = new Date(DateSearch.date.curYear, DateSearch.date.curMonth, DateSearch.date.curDate - DateSearch.date.curDay);
        var startYear = startOfWeek.getFullYear();
        var startMonth = startOfWeek.getMonth();
        var startDate = startOfWeek.getDate();
        var endOfWeek = new Date(DateSearch.date.curYear, DateSearch.date.curMonth, DateSearch.date.curDate + (6- DateSearch.date.curDay)) ;
        var endYear = endOfWeek.getFullYear();
        var endMonth = endOfWeek.getMonth();
        var endDate = endOfWeek.getDate();
        //이번주 월요일
        $("#search_start_date").datebox('setValue', DateSearch.makeFullDate(startYear, startMonth, startDate));
        //이번주 일요일
        $("#search_end_date").datebox('setValue',  DateSearch.makeFullDate(endYear, endMonth, endDate));
    };
    //이번달 YYYY-mm-dd
    DateSearch.getThisMonth = function() {
        //달의 첫째 날
        DateSearch.date.startOfMonth = new Date(DateSearch.date.curYear, DateSearch.date.curMonth, 1).getDate();
        //달의 마지막 날
        DateSearch.date.endOfMonth = new Date(DateSearch.date.curYear, DateSearch.date.curMonth, 0).getDate();
        //이번주 월요일
        $("#search_start_date").datebox('setValue', DateSearch.makeFullDate(DateSearch.date.curYear, DateSearch.date.curMonth, DateSearch.date.startOfMonth));
        //이번주 일요일
        $("#search_end_date").datebox('setValue',  DateSearch.makeFullDate(DateSearch.date.curYear, DateSearch.date.curMonth, DateSearch.date.endOfMonth));
    };
    //1개월 전 YYYY-mm-dd
    DateSearch.getMonthAgo = function() {
        var fullDate = DateSearch.makeFullDate(DateSearch.date.curYear, DateSearch.date.curMonth, DateSearch.date.curDate);
        var month_ago = DateSearch.makeFullDate(DateSearch.date.curYear, DateSearch.date.curMonth-1, DateSearch.date.curDate);
        $("#search_start_date").datebox('setValue', month_ago);
        $("#search_end_date").datebox('setValue', fullDate);
    };
    //3개월 전 YYYY-mm-dd
    DateSearch.getThreeMonthAgo = function() {
        var fullDate = DateSearch.makeFullDate(DateSearch.date.curYear, DateSearch.date.curMonth, DateSearch.date.curDate);
        var month_ago = DateSearch.makeFullDate(DateSearch.date.curYear, DateSearch.date.curMonth-3, DateSearch.date.curDate);
        $("#search_start_date").datebox('setValue', month_ago);
        $("#search_end_date").datebox('setValue', fullDate);
    };
    //6개월 전 YYYY-mm-dd
    DateSearch.getSixMonthAgo = function() {
        var fullDate = DateSearch.makeFullDate(DateSearch.date.curYear, DateSearch.date.curMonth, DateSearch.date.curDate);
        var month_ago = DateSearch.makeFullDate(DateSearch.date.curYear, DateSearch.date.curMonth-6, DateSearch.date.curDate);
        $("#search_start_date").datebox('setValue', month_ago);
        $("#search_end_date").datebox('setValue', fullDate);
    };
    //12개월 전 YYYY-mm-dd
    DateSearch.getTwelveMonthAgo = function() {
        var fullDate = DateSearch.makeFullDate(DateSearch.date.curYear, DateSearch.date.curMonth, DateSearch.date.curDate);
        var month_ago = DateSearch.makeFullDate(DateSearch.date.curYear-1, DateSearch.date.curMonth, DateSearch.date.curDate);
        $("#search_start_date").datebox('setValue', month_ago);
        $("#search_end_date").datebox('setValue', fullDate);
    };
    //전체
    DateSearch.resetDate = function() {
        $("#search_start_date").datebox('setValue', '');
        $("#search_end_date").datebox('setValue', '');
    };
    // YYYY-mm-dd형식 변환
    DateSearch.makeFullDate = function(requestYear, requestMonth, requestDate) {
        requestMonth = requestMonth+1;
        if (requestMonth < 10) {
            requestMonth = '0' + requestMonth;
        }
        if (requestDate < 10) {
            requestDate = '0' + requestDate;
        }
        DateSearch.date.fullDate = requestYear + '-' + requestMonth + '-' + requestDate;

        return DateSearch.date.fullDate;
    }
}
DateSearch();
