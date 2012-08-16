function refresh_session() {
    if(isPageHidden()) {
//        $('#out').html($('#out').html()+"<br />unsichtbar");
        return;
    }
//    $('#out').html($('#out').html()+"<br />refrech");
    url = "index.php?refresh_session=true&action="+action_activ;
    jQuery.get(url, function(data) {
        if(data.length > 1) {
            $('.js-multi-user').each( function(index, tag) {
                var test_name = $(tag).find('a').attr('name');
                // nur die die nicht activ sind
                if($(tag).hasClass('js-hover-default')) {
                    // ist in der liste also hidden
                    if(data.search(test_name) != -1) {
                        if(!$(tag).hasClass('ui-state-disabled'))
                            $(tag).addClass('ui-state-disabled js-no-click');
                    } else {
                        // ist nicht in der liste also aktivieren
                        if($(tag).hasClass('ui-state-disabled'))
                            $(tag).removeClass('ui-state-disabled js-no-click');
                    }
                }
            });
        }
    });
}

function isPageHidden() {
     return document.hidden || document.mozHidden || document.msHidden || document.webkitHidden;
}
/*
var hidden, visibilityChange; 
if (typeof document.hidden !== "undefined") {
    hidden = "hidden";
    visibilityChange = "visibilitychange";
} else if (typeof document.mozHidden !== "undefined") {
    hidden = "mozHidden";
    visibilityChange = "mozvisibilitychange";
} else if (typeof document.msHidden !== "undefined") {
    hidden = "msHidden";
    visibilityChange = "msvisibilitychange";
} else if (typeof document.webkitHidden !== "undefined") {
    hidden = "webkitHidden";
    visibilityChange = "webkitvisibilitychange";
}
*/
$(function() {
    var set_session = window.setInterval("refresh_session()", multi_user_time);
});
