$(function () {
    $("#confirm_id").off().on('click', function () {
        if ($.trim($("#dm_id").val()) == "")
        {
            alert("아이디를 입력해주세요");
            return false;
        }
        $.ajax({
            url : member_url+"command.php",
            data:"dm_id="+$("#dm_id").val()+"&command=check_id",
            type : 'POST',
            cache : false,
            async : false,
            dataType : 'json',
            success:function(data)
            {
                if(data.result == "success")
                {
                    alert('사용할 수 있는 아이디입니다.');
                    $("#dm_id").attr("readonly", true);
                    $("#confirm_txt").text('사용할 수 있는 아이디입니다.');
                    $("#confirm_txt").css("color", "blue");
                    $("#confirm_id").hide();
                    $("input[name='chk_id_flag']").val(1);
                }else if (data.result == 'dup')
                {
                    alert("사용할 수 없는 아이디입니다.");
                    $("input[name='chk_id_flag']").val(0);
                } else
                {
                    alert("아래와 같은 이류로 확인에 실패하였습니다.\r\n -" + data.notice + "\r\n","아이디 중복검사 실패");
                    $("input[name='chk_id_flag']").val(0);
                }
            },
            error:function(data)
            {
            }
        });
    });

    $("#confirm_nick").off().on('click', function () {
        if ($.trim($("#dm_nick").val()) == "")
        {
            alert("닉네임을 입력해주세요");
            return false;
        }
        $.ajax({
            url : member_url+"command.php",
            data:"dm_nick="+$("#dm_nick").val()+"&command=check_nick",
            type : 'POST',
            cache : false,
            async : false,
            dataType : 'json',
            success:function(data)
            {
                if(data.result == "success")
                {
                    alert('사용할 수 있는 닉네임입니다.');
                    $("#dm_nick").attr("readonly", true);
                    $("#confirm_txt").text('사용할 수 있는 닉네임입니다.');
                    $("#confirm_txt").css("color", "blue");
                    $("#confirm_nick").hide();
                    $("input[name='chk_nick_flag']").val(1);
                }else if (data.result == 'dup')
                {
                    alert("사용할 수 없는 닉네임입니다.");
                    $("input[name='chk_nick_flag']").val(0);
                } else
                {
                    alert("아래와 같은 이류로 사용자 닉네임 중복확인에 실패하였습니다.\r\n -" + data.notice + "\r\n관리자에게 문의하세요.","닉네임 중복확인 실패");
                    $("input[name='chk_nick_flag']").val(0);
                }
            },
            error:function(data)
            {
            }
        });
    });

    $("#dm_password_confirm").keyup(function(){
        var pwd1=$("#dm_password").val();
        var pwd2=$("#dm_password_confirm").val();
        $("input[name='chk_pw_flag']").val(0);

        if(pwd1 != "" || pwd2 != ""){
            if(pwd1 == pwd2){
                $("#password_notice").text("비밀번호가 일치합니다.");
                $("#password_notice").css("color", "blue");
                $("input[name='chk_pw_flag']").val(1);
            }else {
                $("#password_notice").text("비밀번호가 일치하지 않습니다.");
                $("#password_notice").css("color", "red");
                $("input[name='chk_pw_flag']").val(0);
            }
        }
    });

    $(".member_confirm").off().on('click', function () {
        if (!$("#dm_agree1").is(":checked")) {
            alert ("이용약관에 동의해주세요");
            return false;
        }

        if (!$("#dm_agree2").is(":checked")) {
            alert ("개인정보 처리방침에 동의해주세요");
            return false;
        }

        var idx = $(".member_confirm").index($(this));
        idx += 1;

        $.ajax({
            url : member_url+"member.php",
            data:"command=set_member_type&member_type="+idx,
            dataType:"json",
            type:"post",
            success:function (data) {
                if (data.result == 'success') {
                    location.href='?contentId=3bc897ab4f256c63d48e80c2f4d530ae';
                } else {
                    alert (data.notice);
                }
            }
        });
    });

    $("#search_btn").off().on('click', function () {
        var search_value = $("#search_value").val();
        if (search_value != "") {
            searchAll(search_value);
        }
    });

    $("#search_value").keypress(function(e){
        var search_value = $("#search_value").val();
        if (search_value != "") {
            if(e.keyCode === 13){
                e.preventDefault();
                searchAll(search_value);
            }
        }
    });

    $("#search_btn1").off().on('click', function () {
        var search_value = $("#search_value1").val();
        if (search_value != "") {
            searchAll(search_value);
        }
    });

    $("#search_value1").keypress(function(e){
        var search_value = $("#search_value1").val();
        if (search_value != "") {
            if(e.keyCode === 13){
                e.preventDefault();
                searchAll(search_value);
            }
        }
    });


    function searchAll(search_value) {
        $.ajax({
            url :prgm_url+"search.php",
            data:"type=get_search_all&search_value="+encodeURI(search_value),
            dataType:"json",
            success : function (data) {
                if (data.result == 'success') {
                    location.href="?contentId=9039ca26934275e62483422d11e21db4";
                }
            }
        });
    }

    function getMainSurvey() {
        $.ajax({
            url :prgm_url+"survey.php",
            data:"type=get_main_survey",
            dataType:"json",
            success : function (data) {
                if (data) {
                    $(".question p.txt").text(data.dm_survey_question);
                } else {
                    $(".question p.txt").text("진행중인 설문조사가 없습니다.");
                }
            }
        });
    }

    function getMainVote() {
        $.ajax({
            url :prgm_url+"vote.php",
            data:"type=get_main_vote_list",
            dataType:"json",
            success : function (data) {
                if (data.cate1.length > 0) {
                    $("#tabb1 ul").empty();
                    $(data.cate1).each(function (key, value) {
                        $("#tabb1 ul").append(
                            '<li>'+
                                '<div class="titbox">'+
                                    '<h4>'+value.dm_vote_name+'</h4>'+
                                    '<em>총득표수 '+value.total_count+'명</em>'+
                                '</div>'+
                                '<div class="graph">'+
                                    '<span style="width:'+value.dm_vote1_percent+'%" class="down"></span>'+
                                '</div>'+
                                '<div class="data">'+
                                    '<p class="blue">'+value.dm_vote1+' <em>'+value.dm_vote1_percent+'%</em></p>'+
                                    '<p class="red"><em>'+value.dm_vote2_percent+'%</em> '+value.dm_vote2+'</p>'+
                                '</div>'+
                            '</li>'
                        );
                    });
                } else {
                    $("#tabb1 ul").empty();
                    $("#tabb1 ul").append('<li>진행중인 공감지표가 없습니다.</li>');
                }

                if (data.cate2.length > 0) {
                    $("#tabb2 ul").empty();
                    $(data.cate2).each(function (key, value) {
                        $("#tabb2 ul").append(
                            '<li>'+
                            '<div class="titbox">'+
                            '<h4>'+value.dm_vote_name+'</h4>'+
                            '<em>총득표수 '+value.total_count+'명</em>'+
                            '</div>'+
                            '<div class="graph">'+
                            '<span style="width:'+value.dm_vote1_percent+'%" class="down"></span>'+
                            '</div>'+
                            '<div class="data">'+
                            '<p class="blue">'+value.dm_vote1+' <em>'+value.dm_vote1_percent+'%</em></p>'+
                            '<p class="red"><em>'+value.dm_vote2_percent+'%</em> '+value.dm_vote2+'</p>'+
                            '</div>'+
                            '</li>'
                        );
                    });
                } else {
                    $("#tabb2 ul").empty();
                    $("#tabb2 ul").append('<li>진행중인 이 종목 어때가 없습니다.</li>');
                }
            }
        });
    }

    getMainVote();
    getMainSurvey();

    $("#bank_confirm").off().on('click', function () {
        $.ajax({
            url :prgm_url+"charge.php",
            data:"type=set_bank",
            dataType: "json",
            success:function (data) {
                if (data.result == 'success') {
                    alert ("무통장입금 신청을 완료했습니다. \n\n입금자명은 회원명과 일치시켜주시기 바랍니다.");
                    location.href='?contentId=5a8ad35373cb7325b06904ce235faee9';
                } else if (data.nomember) {
                    alert (data.notice);
                    location.href=data.url;
                }else {
                    alert (data.notice);
                }
            }
        });
    });

    $("#bank_cancel").off().on('click', function () {
        $("#bank").hide();
    });

    $.ajax({
        url:prgm_url+"charge.php",
        data:"type=get_mypoint",
        dataType:"json",
        success:function (data) {
            $("#remain_point").text(data.remain_point);
            $("#use_point").text(data.use_point);
            $("#charge_point").text(data.charge_point);
            if (data.last_point_date) {
                $("#last_date").text(data.last_point_date);
            } else {
                $("#last_date").text("-");
            }
        }
    });

    $(".levelup_pop").off().on('click', function () {
        $.ajax({
            url:member_url+"command.php",
            type:"post",
            data:"command=setClosePop",
            success: function () {
                $("#levelLayer").hide();
            }
        });
    });
});

function showPassword(wr_id, mode) {
    $("div.modal").show();
    $("#com_wr_id").val(wr_id);
    $("input[name='mode']").val(mode);
    $("#modal_back").show();
}

function chk_password () {
    $.ajax({
        url : board_url+'command.php',
        dataType : 'json',
        type : 'post',
        data : '&command=chk_password&chk_pass='+$("#chk_pass").val()+'&contentId='+content_id+'&wr_id='+$("#com_wr_id").val(),
        success : function (data) {
            alert(data.notice);

            if (data.result == 'success')
            {
                var mode = $("input[name='mode']").val();

                if (mode == 'modify')
                {
                    showComment($("#com_wr_id").val());
                    chk_cancel();
                }
                else if (mode == 'delete')
                {
                    delComment($("#com_wr_id").val());
                }
                else if (mode == 'view')
                {
                    location.href='?command=view&contentId='+content_id+'&wr_id='+$("#com_wr_id").val();
                }
                else if (mode == 'modify_write') {
                    location.href='?command=modify_form&contentId='+content_id+'&wr_id='+$("#com_wr_id").val();
                }
                else if (mode == "delete_write") {
                    removeBoard();
                }
            }

            $("#chk_pass").val('');
        }
    });
}

$(document).on("click", "#modal_back", function () {
    chk_cancel();
});

function chk_cancel() {
    $("div.modal").hide();
    $("#modal_back").hide();
    $("#com_wr_id").val('');
    $("input[name='mode']").val('');
}


function showComment(wr_id) {
    var comment_content = $("#content_"+wr_id).text();
    comment_content = $.trim(comment_content);
    var comment_wr_name = $("#wr_name"+wr_id+" strong span").text();
    comment_wr_name = $.trim(comment_wr_name);

    $("#comment_content").text(comment_content);
    $("#comment_content").focus();
    $("#comment_name").val(comment_wr_name);
    $("input[name='wr_id']").val(wr_id);
}

function delComment(wr_id) {
    $.ajax({
        url : board_url+'command.php',
        dataType : 'json',
        type : 'post',
        data : '&command=delete_comment&contentId='+content_id+'&wr_id='+wr_id,
        success : function (data) {
            alert(data.notice);
            if (data.result == 'success') {
                location.reload();
            }
        }
    });
}

$('.print').click(function(){
    reportPrint($("#print_pop").html());
});

function reportPrint(param){
    const setting = "width=890, height=841";
    const objWin = window.open('', 'print', setting);
    objWin.document.open();
    objWin.document.write('<html><head><title>분석 레포트 </title>');
    objWin.document.write('<link rel="stylesheet" type="text/css" href="'+layout_path+'css/gallery.css"/>');
    objWin.document.write('</head><body>');
    objWin.document.write(param);
    objWin.document.write('</body></html>');
    objWin.focus();
    objWin.document.close();

    objWin.print();
    objWin.close();
}

function facebooxShare(url) {
    var encodeUrl = encodeURIComponent(url);
    var faceboox = "https://www.facebook.com/sharer/sharer.php?u=";
    var link = faceboox + encodeUrl;
    window.open(link, 'facebook', 'width=625,height=436');
}

function naverShare(url, title) {
    var encodeUrl = encodeURI(encodeURIComponent(url));
    var encodeTitle = encodeURI(title);
    var shareURL = "https://share.naver.com/web/shareView.nhn?url=" + encodeUrl + "&title=" + encodeTitle;
    window.open(shareURL);
}

function twitterShare(url, title) {
    var encodeUrl = encodeURI(encodeURIComponent(url));
    var encodeTitle = encodeURI(title);
    var shareURL = "http://twiter.com/share?url=" + encodeUrl + "&text=" + encodeTitle;
    window.open(shareURL, 'twitter', 'width=625,height=436');
}

$(function () {
    $("#dm_mb_id").keypress(function(e){
        if(e.keyCode === 13){
            e.preventDefault();
            fnLogin();
        }
    });
    $("#dm_mb_password").keypress(function(e){
        if(e.keyCode === 13){
            e.preventDefault();
            fnLogin();
        }
    });
});

function fnLogin() {

    if($.trim($("#dm_mb_id").val()) == ""){
        alert('아이디를 입력해 주십시오.');
        $("#dm_mb_id").focus();
        return false;
    }

    if($.trim($("#dm_mb_password").val()) == ""){
        alert('패스워드를 입력해 주십시오.');
        $("#dm_mb_password").focus();
        return false;
    }

    var form = $("#fm")[0];
    var formData = new FormData(form);

    $.ajax({
        url : login_url+"login.php",
        data:formData,
        type : 'POST',
        cache : false,
        async : false,
        contentType: false,
        processData: false,
        dataType : 'json',
        success:function(data)
        {
            if(data.result == "success")
            {
                if (data.url) {
                    location.href = web_url+"?"+data.url;
                } else {
                    location.href = web_url;
                }
            }else
            {
                alert("아래와 같은 이류로 사용자 로그인에 실패하였습니다.\r\n -" + data.notice + "\r\n관리자에게 문의하세요.","로그인 실패");
            }
        },
        error:function(data)
        {
        }
    });
    //return true;
}

function logout() {
    if(confirm("로그아웃 하시겠습니까?")) {
        $.ajax({
            url : login_url+"login.php",
            data:'command=logout',
            type : 'POST',
            cache : false,
            async : false,
            dataType : 'json',
            success:function(data)
            {
                console.log(data);
                if(data.result == "success")
                {
                    alert("로그아웃했습니다.");
                    location.href = web_url;
                }else
                {
                    alert("아래와 같은 이류로 사용자 로그인에 실패하였습니다.\r\n -" + data.notice + "\r\n관리자에게 문의하세요.","로그인 실패");
                }
            },
            error:function(data)
            {
            }
        });
    }
}

function charge(id) {
    if (confirm ("충전하시겠습니까?")) {
        $.ajax({
            url :prgm_url+"charge.php",
            data:"type=get_goods&dm_id="+id,
            type:"post",
            dataType:"json",
            success : function (data) {
                $("#amount").text(data.dm_goods_price+"원");
            }
        });
        $("#bank").show();
    }
}

function find_info(comm)
{
    if (comm == 'lost_id')
    {
        if($.trim($("#dm_name").val()) == ""){
            alert('이름을 입력해주세요.');
            $("#dm_name").focus();
            return false;
        }

        if($.trim($("#dm_email").val()) == ""){
            alert('이메일을 입력해주세요.');
            $("#dm_email").focus();
            return false;
        }
    }
    else
    {
        if($.trim($("#dm_id").val()) == ""){
            alert('아이디를 입력해주세요.');
            $("#dm_id").focus();
            return false;
        }

        if($.trim($("#dm_email1").val()) == ""){
            alert('이메일을 입력해주세요.');
            $("#dm_email1").focus();
            return false;
        }
    }

    $("input[name='command']").val(comm);

    var form = $("#fm")[0];
    var formData = new FormData(form);

    $.ajax({
        url : login_url+"login.php",
        data:formData,
        type : 'POST',
        cache : false,
        async : false,
        contentType: false,
        processData: false,
        dataType : 'json',
        success:function(data)
        {
            if(data.result == "success")
            {
                if (comm == 'lost_id')
                {
                    alert('당신의 아이디는 ['+data.notice+'] 입니다.');
                    $("#dm_name").val('');
                    $("#dm_email").val('');
                }
                else
                {
                    alert('요청하신 이메일로 임시비밀번호를 보내드렸습니다.');
                    $("#dm_id").val('');
                    $("#dm_email1").val('');
                }

            }else
            {
                alert("아래와 같은 이류로 사용자 정보찾기에 실패하였습니다.\r\n -" + data.notice + "\r\n관리자에게 문의하세요.","");
            }
        },
        error:function(data)
        {
        }
    });
}

function fnLeave() {
    if(confirm("정말 탈퇴하시겠습니까? 모든 정보와 포인트가 삭제됩니다.")) {
        location.href=member_url+"command.php?command=leave_member";
    }
}