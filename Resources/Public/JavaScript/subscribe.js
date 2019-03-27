
$('.tx-vibis-ajaxmailsubscription').on('click','#vibisAjaxmailsubscription_btn',function(){
    var ajaxUrl = $( this ).attr( "data-ajaxUrl" );
    //console.log('ajaxUrl = ' + ajaxUrl);
    var formData = $( "#vibisAjaxmailsubscription_form" ).serializeArray();
    $('#vibisAjaxmailsubscription_ajaxResponse').html('Loading...');
    $('#vibisAjaxmailsubscription_ajaxResponse')
        .load( 
            ajaxUrl,
            formData,
            function( response, status, xhr ){       
                if ( status == "error" ) {
                    var msg = vibis_ajaxmailsubscription_errors['ajaxError'];
                    $( "#vibisAjaxmailsubscription_errors" ).html( '<div  class="alert alert-danger">' + msg + xhr.status + " " + xhr.statusText + '</div>' );
                }else{
                    vibis_createCookie('vibisAjaxmailsubscription', 1, 1);
                }
    });
});

// popup code
var intervalID;

function vibis_createCookie(cookieName,cookieValue,daysToExpire){
    var date = new Date();
    date.setTime(date.getTime()+(daysToExpire*86400000)); //24*60*60*1000
    document.cookie = cookieName + "=" + cookieValue + "; expires=" + date.toGMTString() + "; path=/";
}
function vibis_accessCookie(cookieName){
    var name = cookieName + "=";
    var allCookieArray = document.cookie.split(';');
    for(var i=0; i<allCookieArray.length; i++)
    {
    var temp = allCookieArray[i].trim();
    if (temp.indexOf(name)==0)
    return temp.substring(name.length,temp.length);
    }
    return "";
}
function callPopUp(){
    var subscription = vibis_accessCookie('vibisAjaxmailsubscription');
    if (subscription){
        clearInterval(intervalID);
    }else{ // show popup
        clearInterval(intervalID);
        $('#vibis_popup').css( "display", "block" );
    }
}
// call popup
$(document).ready(function() { 
    var vibisAjaxmailclose = vibis_accessCookie('vibisAjaxmailclose');
    if (vibisAjaxmailclose != 1){
        intervalID = setInterval(callPopUp, popUpTimeInterval);  
    }    
});

$('.tx-vibis-ajaxmailsubscription').on('click','#vibisPopUpCloseBtn',function(){
    vibis_createCookie('vibisAjaxmailclose', 1, 30);
    $('#vibis_popup').css( "display", "none" );
});