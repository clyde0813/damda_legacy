/********************************************
 //  pop open
 *********************************************/
function openPopup(link, param, id, x, y, width, height, type, fit, content_type, popup_page, expired, href, link_type)
{
    cookiedata = document.cookie;

    if(cookiedata.indexOf("popup_" + id + "=done") < 0)
    {
        if(type == "1")
        {
            openPopupWindows(link, param, id, x, y, width, height, fit);
        }else
        {
            openPopupLayer(link, param, id, x, y, width, height, fit, content_type, popup_page, expired, href, link_type);
        }
    }
}
function openPopupWindows(link, param, id, x, y, width, height, fit)
{

    var windows_width = width; //��â�� �ʺ�
    var windows_height = height; //��â�� ����
    var screen_width = screen.availWidth; //ȭ�� �ʺ� (�ػ�)
    var screen_height = screen.availHeight; //ȭ�� ���� (�ػ�)
    //var open_x = (screen_width - 500) / 2;
    //var open_y = (screen_height - 320) / 2;
    var open_x = x;
    var open_y = y;


    window.open(link + param, id, "status=yes, left=" + open_x + ",top=" + open_y + ",width=" + windows_width + ",height=" + windows_height);
}
function openPopupLayer(link, param, id, x, y, width, height, fit, content_type, popup_page, expired, href, link_type)
{
    var Content = '<div class="layer_popup" id="layer_popup_' + id + '" name="layer_popup_' + id + '" style="position:fixed;width:' + width + 'px;height:' + ((height*1)+40) + 'px;overflow-x:hidden; overflow-y:hidden;left:' + x + 'px;top:' + y + 'px;z-index:9999;background-color: #ffffff; border: 0px solid #90C;">';
    if (content_type == 1) {
        Content += '<iframe src="' + link + '" width="100%" height="100%"  scrolling="no" border="no" maginwidth="0" marginheight="0" frameborder="0" ></iframe>';
    } else {
        if (href) {
            Content += '<a href="'+href+'" target="'+link_type+'"><img src="'+popup_page+'" /></a>';
        } else {
            Content += '<img src="'+popup_page+'" />';
        }
    }
    Content += '<div class="layer_btns_area">\n' +
        '\t\t<div class="layer_"><input type="checkbox" id="chk_expired'+id+'" name="chk_expired" class="pop_chk"><label for="chk_expired'+id+'"><b>'+expired+'</b>시간 동안 창 열지 않음</label></div>\n' +
        '<a onclick="onClose1(\''+id+'\', \''+expired+'\' );" class="close_pop" style="cursor:hand;">닫기</a>' +
        '</div></div>';

    var newContent = $(Content);
    newContent.appendTo("body");
}
function setCookie(name, value, expirehours)
{
    var todayDate = new Date();
    expirehours = expirehours * 1;
    todayDate.setHours(todayDate.getHours() + expirehours);
    document.cookie = name + "=" + escape(value) + "; path=/; expires=" + todayDate.toUTCString() + ";"
}
function closeCooKie(popId, expired) {
    setCookie( "popup_"+popId , "done" , expired );
}
function parentRemove(type, dm_id)
{
    $('#layer_popup_' + dm_id).remove();
}
function onClose1(dm_id, expired)
{
    var checked = $("input:checkbox[id='chk_expired"+dm_id+"']").is(":checked");

    if(expired > -1 && checked)
    {
        closeCooKie(dm_id, expired);
    }
    parentRemove("", dm_id)
}