function refresh_session() {
    if(isPageHidden()) return;
    url = "index.php?refresh_session=true&action="+action_activ;
    jQuery.get(url, function(data) {
        $('.js-multi-user').each( function(index, tag) {
            var tag = $(tag),
                test_name = tag.find('a').attr('name');
            // nur die die nicht activ sind
            if(tag.hasClass('js-hover-default')) {
                // ist in der liste also hidden
                if(data.search(test_name) != -1) {
                    if(!tag.hasClass('ui-state-disabled'))
                        tag.addClass('ui-state-disabled js-no-click');
                } else {
                    // ist nicht in der liste also aktivieren
                    if(tag.hasClass('ui-state-disabled'))
                        tag.removeClass('ui-state-disabled js-no-click');
                }
            }
        });
    });
}

function isPageHidden() {
     return document.hidden || document.mozHidden || document.msHidden || document.webkitHidden;
}
$(function() {
    var set_session = window.setInterval("refresh_session()", multi_user_time);
});
