var anim_speed = '200';
var dialogMaxheightOffset = 40;
var max_menu_tab = false;

function sleep(milliSeconds) {
    var startTime = new Date().getTime(),
        curTime = null;
    do { curTime = new Date().getTime(); }
    while(curTime - startTime < milliSeconds);
}

function getCaretPos(item) {
    var pos = 0;
    if(!$(item).is(':focus'))
        item.focus();
    if(document.selection) {
        var sel = document.selection.createRange().duplicate();
        sel.moveStart('character',-item.value.length);
        pos = sel.text.length;
    } else if(item.selectionStart)
        pos = item.selectionStart;
    return pos;
}

function setCaretPos(item,pos) {
    if(!$(item).is(':focus'))
        item.focus();
    if(document.selection) {
        var range = item.createTextRange();
        range.move("character", pos);
        range.select();
    } else if(item.selectionStart) {
        item.selectionStart = pos;
        item.selectionEnd = pos;
    }
}

function checkHexValue(event) {
    if(event.which == 8) // del left
        return;
    var ele = $(event.target),
        caret_pos = getCaretPos(event.target),
        new_value = ele.val().toUpperCase();
    if(new_value.search(/[^A-F0-9]/g) != -1) {
        caret_pos = new_value.search(/[^A-F0-9]/g);
        new_value = new_value.replace(/[^A-F0-9]/g,"");
    }
    ele.val(new_value);
    setCaretPos(event.target,caret_pos);
}

function checkDezValue(event) {
    if(event.which == 8) // del left
        return;
    var ele = $(event.target),
        caret_pos = getCaretPos(event.target),
        new_value = ele.val();
    if(new_value.search(/[^0-9]/g) != -1) {
        caret_pos = new_value.search(/[^0-9]/g);
        new_value = new_value.replace(/[^0-9]/g,"");
        ele.val(new_value);
        setCaretPos(event.target,caret_pos);
    }
}

function checkChmodValue(event) {
    if(event.which == 8) // del left
        return;
    var ele = $(event.target),
        caret_pos = getCaretPos(event.target),
        new_value = ele.val();
    if(new_value.search(/[^0-7]/g) != -1) {
        caret_pos = new_value.search(/[^0-7]/g);
        new_value = new_value.replace(/[^0-7]/g,"");
        ele.val(new_value);
        setCaretPos(event.target,caret_pos);
    }
}

function checkDezAutoValue(event) {
    if(event.which == 8) // del left
        return;
    var ele = $(event.target),
        caret_pos = getCaretPos(event.target),
        new_value = ele.val();
    if(new_value.search(/[auto]/g) != -1) {
        ele.val("auto");
        setCaretPos(event.target,caret_pos);
    } else if(new_value.search(/[^0-9auto]/g) != -1) {
        caret_pos = new_value.search(/[^0-9auto]/g);
        new_value = new_value.replace(/[^0-9auto]/g,"");
        ele.val(new_value);
        setCaretPos(event.target,caret_pos);
    }
}

function checkIsZipFile(file_obj) {
    var file = file_obj.val();
    if(file.length > 5 && file.substring(file.lastIndexOf(".")).toLowerCase() == ".zip") {
        return true;
    }
    file_obj.val("");
    return false;
}

function rawurlencode_js(str) {
    return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/\~/g, '%7E').replace(/#/g,'%23');
}

function rawurldecode_js(str) {
    return decodeURIComponent(str).replace(/%21/g, '!').replace(/%27/g, "'").replace(/%28/g, '(').replace(/%29/g, ')').replace(/%2A/g, '*').replace(/%7E/g, '~').replace(/%23/g,'#');
}

// das ist die gleiche function wie in der index.php
function returnMessage(success, message) {
    if (success === true) {
        return "<span class=\"mo-message-erfolg\"><img class=\"mo-message-icon mo-icons-icon mo-icons-information\" src=\""+ICON_URL_SLICE+"\" alt=\"information\" \/>"+message+"<\/span>";
    } else {
        return "<span class=\"mo-message-fehler\"><img class=\"mo-message-icon mo-icons-icon mo-icons-error\" src=\""+ICON_URL_SLICE+"\" alt=\"error\" \/>"+message+"<\/span>";
    }
}

function test_modrewrite(that) {
    $.ajax({
        global: true,
        cache: false,
        type: "POST",
        url: "mod_rewrite_t_e_s_t.html",
        async: true,
        dataType: "html",
        timeout:20000,
        beforeSend: function(jqXHR) {
            if(dialog_mod_rewrite.dialog("isOpen")) {
                dialog_mod_rewrite.dialog("close");
            }
            send_object_mod_rewrite = jqXHR;
            dialog_mod_rewrite.dialog("open");
        },
        success: function(data, textStatus, jqXHR) {
            send_object_mod_rewrite = false;
            dialog_mod_rewrite.dialog("close");
            var tmp = $("<span>"+data+"<\/span>");
            if(tmp.find("#mod-rewrite-true").length > 0) {
                // in Info die li austauschen
                if($("#mod-rewrite-false").length > 0)
                    $("#mod-rewrite-false").parents(".mo-in-ul-li").replaceWith(tmp.find(".mo-in-ul-li"));
                // in Einstellungen die checkbox activ setzen
                if($("#modrewrite").length > 0) {
                    $("#modrewrite").prop('checked', true);
                    make_para($(that));
                }
            } else {
                if($("#modrewrite").length > 0) {
                    // in Einstellungen die checkbox nicht activ setzen
                    $("#modrewrite").prop('checked', false);
                    // und fehlermeldung ausgeben
                    dialog_open("error_messages",returnMessage(false, mozilo_lang["config_error_modrewrite"]));
                }
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            send_object_mod_rewrite = false;
            dialog_mod_rewrite.dialog("close");
            dialog_open("error_messages",returnMessage(false, mozilo_lang["config_error_modrewrite"]));
            $("#modrewrite").prop('checked', false);
        }
    });
}

$(function() {

    $("body").on("click",".js-no-click", function(event) { event.preventDefault(); });

    /* bei allen input's mit dieser class nur zahlen zulassen */
    $("body").on("keyup",".js-in-digit", function(event) {
        checkDezValue(event);
    });

    /* bei allen input's mit dieser class nur zahlen zulassen */
    $("body").on({
        keyup: function(event) {checkHexValue(event);},
        focusout: function() {
            var v = $(event.target).val().toUpperCase().replace(/[^A-F0-9]/g,"");
            $(event.target).val(v+("000000".substr((Math.min(v.length,6)))));
        }
    },".js-in-hex");

    /* bei allen input's mit dieser class nur zahlen und auto zulassen */
    $("body").on("keyup",".js-in-digit-auto", function(event) {
        checkDezAutoValue(event);
    });

    /* bei allen input's mit dieser class nur zahlen 0-7 zulassen */
    $("body").on("keyup", ".js-in-chmod",function(event) {
        checkChmodValue(event);
    });

    /* toggle für die tools icons */
    $("body").on({
        mouseenter: function() { 
            $(this).find(".js-tools-icon-show-hide:not(.mo-icon-blank)").css("opacity", 1);
        },
        mouseleave: function () {
            $(this).find(".js-tools-icon-show-hide").css("opacity", 0);
        }
    },".js-tools-show-hide");

    var menu_fix_top = parseInt($("#menu-fix").css("top"));
    $("#menu-fix").css({"width":parseInt($("#menu-fix").css("min-width")),
                        "top":($(window).scrollTop() + menu_fix_top)
    });
    $(window).scroll(function() {
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
    $("body").on("click",".js-toggle", function(event) {
        if($(this).hasClass('ui-state-disabled')) return;
        var mo_li = $(this).closest(".mo-li");
        if(mo_li.find(".js-toggle-content").is(":visible")) {
            $(this).siblings('.js-rename-file, .js-edit-delete').removeClass("ui-state-disabled");
            mo_li.find(".mo-li-head-tag").removeClass("ui-corner-top").addClass("ui-corner-all");
            mo_li.find(".js-toggle-content").hide(anim_speed);
            return;
        } else if(!mo_li.find(".js-toggle-content").is(":visible")) {
            $(this).siblings('.js-rename-file, .js-edit-delete').addClass("ui-state-disabled");
            mo_li.find(".mo-li-head-tag").removeClass("ui-corner-all").addClass("ui-corner-top");
            mo_li.find(".js-toggle-content").show(anim_speed);
            return;
        }
    });

    $("body").on("click",".js-docu-link", function(event) {
        event.preventDefault();
        var iframe = $('<iframe frameborder="0" width="100%" height="100%" align="left" style="overflow:visible;" \/>');
        iframe.attr("src",$(this).attr("href"));
        dialog_open("docu",iframe);
    });

    if($("#dialog-auto").length > 0) {
        if($("#lastbackup").length > 0)
            dialog_open("messages_lastbackup",$("#lastbackup"));
        else
            dialog_open("from_php",$("#dialog-auto").contents());
    }

    $('input[name="username"]').focus();
});