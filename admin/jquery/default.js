var anim_speed = '200';
var dialogMaxheightOffset = 40;
var max_menu_tab = false;

function sleep(milliSeconds) {
    var startTime = new Date().getTime();
    while (new Date().getTime() < startTime + milliSeconds);
}

function rawurlencode_js(str) {
    return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
    replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/\~/g, '%7E').replace(/#/g,'%23');
}

function rawurldecode_js(str) {
    return decodeURIComponent(str).replace(/%21/g, '!').replace(/%27/g, "'").replace(/%28/g, '(').
    replace(/%29/g, ')').replace(/%2A/g, '*').replace(/%7E/g, '~').replace(/%23/g,'#');
}

// das ist die gleiche function wie in der index.php
function returnMessage(success, message) {
    var padding_left = parseInt(ICON_SIZE.substring(0,2)) +10;
    if (success === true) {
        return "<span class=\"mo-message-erfolg\" style=\"background-image:url("+ADMIN_ICONS+"information.png);padding-left:"+padding_left+"px;\">"+message+"</span>";
    } else {
        return "<span class=\"mo-message-fehler\" style=\"background-image:url("+ADMIN_ICONS+"error.png);padding-left:"+padding_left+"px;\">"+message+"</span>";
    }
}

function set_dialog_max_width(timeItem) {
    if($(timeItem).length > 0) {
        $(timeItem).dialog("option", "width", $(".mo-td-content-width").eq(0).width());
        $(timeItem).dialog("option", "height", (parseInt($(window).height()) - dialogMaxheightOffset));
    } else
        window.setTimeout("set_dialog_max_width(\""+timeItem+"\")", 100);
}

$(function() {

    $("body").on("click",".js-no-click", function(event) { event.preventDefault() });

    /* damit der show sauber arbeitet setzen wir den width vor den hide() oder display:none */
    $(".js-width-show-helper").width($(".js-width-show-helper").width());

    /* bei allen input's mit dieser class nur zahlen zulassen */
    $("body").on("keyup",".js-in-digit", function(event) {
        if($(this).val().search( /[^0-9]/g) != -1)
            $(this).val($(this).val().replace( /[^0-9]/g, "" ));
    });

    /* bei allen input's mit dieser class nur zahlen und auto zulassen */
    $("body").on("keyup",".js-in-digit-auto", function(event) {
        if(event.which == 8) // del left
            return;
        if($(this).val().search( /[^0-9auto]/g) != -1)
            $(this).val($(this).val().replace( /[^0-9auto]/g, "" ));

        if($(this).val().search( /[auto]/g) != -1)
            $(this).val("auto");
    });

    /* bei allen input's mit dieser class nur zahlen 0-7 zulassen */
    $("body").on("keyup", ".js-in-chmod",function(event) {
        if($(this).val().search( /[^0-7]/g) != -1)
            $(this).val($(this).val().replace( /[^0-7]/g, "" ));
    });

    /* toggle für die tools icons */
    $(".js-tools-icon-show-hide").css("opacity", 0);
    $("body").on({
        mouseenter: function() { 
            $(this).find(".js-tools-icon-show-hide:not(.mo-icon-blank)").css("opacity", 1);
        },
        mouseleave: function () {
            $(this).find(".js-tools-icon-show-hide").css("opacity", 0);
        }
    },".js-tools-show-hide");

    $("body").on({
        mouseenter: function() { 
            $(this).addClass("ui-state-hover").removeClass("ui-state-default");
        },
        mouseleave: function () {
            $(this).removeClass("ui-state-hover").addClass("ui-state-default");
        }
    },".js-hover-default");

    var menu_fix_top = parseInt($("#menu-fix").css("top"));
    $("#menu-fix").css({"width":parseInt($("#menu-fix").css("min-width")),
                        "top":($(window).scrollTop() + menu_fix_top),
                        });
    $(window).scroll(function () {
        $("#menu-fix").css("top",($(window).scrollTop() + menu_fix_top));
    });

    $("#menu-fix-content .js-li-cat .mo-li-head-tag").addClass("mo-li-head-tag-no-ul");

    $("#menu-fix").on({
        mouseenter: function() {
            $(this).addClass("ui-corner-all").css("border-left-width",1).animate(
                {width: parseInt($(this).css("max-width"))},
                {duration:anim_speed,queue:false});
        },
        mouseleave: function () {
            $(this).animate({width:parseInt($(this).css("min-width")) },
                        {duration:anim_speed,queue:false,
                        complete: function() {
                            $(this).removeClass("ui-corner-all");
                        }}).css("border-left-width",0);
        }
    });

    /* toggle für die get_template_truss() php function */
    $(".js-toggle-content").hide(0);
    $("body").on("click",".js-toggle", function(event) {
        if($(this).hasClass('ui-state-disabled')) return;
        var mo_li = $(this).closest(".mo-li");
        if(mo_li.find(".js-toggle-content").is(":visible")) {
            $(this).siblings('.js-rename-file, .js-edit-delete').removeClass("ui-state-disabled")
            mo_li.find(".mo-li-head-tag").removeClass("ui-corner-top").addClass("ui-corner-all");
            mo_li.find(".js-toggle-content").hide(anim_speed);
            return;
        } else if(!mo_li.find(".js-toggle-content").is(":visible")) {
            $(this).siblings('.js-rename-file, .js-edit-delete').addClass("ui-state-disabled")
            mo_li.find(".mo-li-head-tag").removeClass("ui-corner-all").addClass("ui-corner-top");
            mo_li.find(".js-toggle-content").show(anim_speed);
            return;
        }
    });

    $("body").on("click",".js-docu-link", function(event) {
        event.preventDefault();
        var iframe = $('<iframe frameborder="0" width="100%" height="100%" align="left" style="overflow:visible;" />');
        iframe.attr("src",$(this).attr("href"));
        dialog_open("docu",iframe);
    });

    if($("#dialog-auto").length > 0) {
        if($("#lastbackup").length > 0)
            dialog_open("messages_lastbackup",$("#lastbackup"));
        else
            dialog_open("from_php",$("#dialog-auto").contents());
    }

});