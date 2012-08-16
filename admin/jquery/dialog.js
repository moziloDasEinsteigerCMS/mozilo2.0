var dialog_open_function = false;
var send_object = false;

var retSuccess = false;
var retError = false;

var pagecontent = false;
var replace_item = false;
var send_item_status = false;

var dialog_multi = false;
var offset_width = false;
var offset_height = false;

function dialog_open(func,content) {
   dialog_set_offset();
   if(typeof content == "object") {
        $(dialog_multi).append(content);
        // damit die höhe des login dialogs geht
        if(content.find('.mo-login').length > 0)
            content.find('.mo-login').addClass('mo-login-dialog');
        if($(dialog_multi).find("iframe").length > 0) {
            $(dialog_multi).dialog("option", "width", $(".mo-td-content-width").eq(0).width());
            $(dialog_multi).dialog("option", "height", (parseInt($(window).height()) - dialogMaxheightOffset));
            $(dialog_multi).dialog("option", "resizable", false);
        } else if($(dialog_multi).children().length > 0) {
            // um die breite zu ermitel setzen wir es testhalber mal ein
            $("#dialog-test-w").append(content);
            dialog_set_max_from_test(dialog_multi);
            $(dialog_multi).append(content);
        }
    } else if(typeof content != "undefined") {
        $(dialog_multi).append(content);
    }

    var dialogopen = false;
    if(typeof func != "undefined") {
        dialogopen = true;
        if(func == "send_cancel")
            dialog_send_cancel();
        else if(func == "error_messages")
            dialog_error_messages();
        else if(func == "error")
            dialog_error();
        else if(func == "messages")
            dialog_messages();
        else if(func == "delete")
            dialog_delete();
        else if(func == "gallery_delete")
            dialog_gallery_delete();
        else if(func == "editor_send_cancel")
            dialog_editor_send_cancel()
        else if(func == "docu")
            dialog_docu();
        else if(func == "editor_save_beforclose")
            dialog_editor_save_beforclose();
        else if(func == "delete_files")
            dialog_delete_files();
        else if(func == "delete_file")
            dialog_delete_file();
        else if(func == "messages_lastbackup")
            dialog_messages_lastbackup();
        else if(func == "from_php")
            dialog_from_php();
        else {
            fn = window[func];
            if(typeof fn === 'function') {
                fn();
            } else
                dialogopen = false;
        }
    }
    if(dialogopen) {
        $(dialog_multi).dialog("open");
    }
}

function dialog_set_offset() {
    if(offset_width !== false && offset_height !== false)
        return;
    $(dialog_multi).dialog("option", "width", 100);
    $(dialog_multi).dialog("option", "height", 100);
    $(dialog_multi).dialog("open");
    offset_width = $(dialog_multi).closest(".ui-dialog").outerWidth() - $(dialog_multi).width();
    offset_height = $(dialog_multi).closest(".ui-dialog").outerHeight() - $(dialog_multi).height();
    $(dialog_multi).dialog("close");
}

function dialog_set_max_from_test(dialog) {
    if($("#dialog-test-w").height() > (parseInt($(window).height()) - dialogMaxheightOffset - offset_height))
        $(dialog).dialog("option", "height", (parseInt($(window).height()) - dialogMaxheightOffset));
    // wenn wir im iframe sind gibts kein mo-td-content-width
    var win_width = $(".mo-td-content-width").eq(0).width();
    if($(".mo-td-content-width").length == 0)
        win_width = (parseInt($(window).width()));
    if($("#dialog-test-w").width() > (win_width - offset_width))
        $(dialog).dialog("option", "width", win_width);
}

function dialog_from_php() {
    var settings = $(dialog_multi).find('.js-dialog-content');
    var buttons = new Array();
    if(typeof settings.attr('title') != "undefined") {
        $(dialog_multi).dialog( "option", "title", settings.attr('title'));
        settings.removeAttr('title');
    } else
        $(dialog_multi).dialog( "option", "title", mozilo_lang["dialog_title_messages"]);
    if(settings.hasClass('js-dialog-close')) {
        var button_close = {text: mozilo_lang["close"],
                    click: function() { $(this).dialog("close"); }};
        buttons.push(button_close);
    }
    if(settings.hasClass('js-dialog-reload')) {
        var button_reload = { text: mozilo_lang["page_reload"],
                    click: function() {
                        var actives_tab = "";
                        if(window.location.search != "")
                            actives_tab = window.location.search;
                        window.location.href = "index.php" + actives_tab;
                }};
        buttons.push(button_reload);
    }
    $(dialog_multi).dialog( "option", "buttons", buttons);
    // user login mit ajax
    if(settings.find('form[name="loginform"]').length > 0) {
        $(dialog_multi).dialog( "option", "title", mozilo_lang["login_titel_dialog"]);
        $('form[name="loginform"]').bind('submit',function(event) {
            event.preventDefault();
            para = $(this).serialize()+"&login=Login&ajaxlogin=true";
            $(dialog_multi).dialog("close");
            $('form[name="loginform"]').unbind();
            send_data(para);
        });
    }
}

function dialog_send_cancel() {
    $(dialog_multi).css("background", "url(" + icons_src + "ajax-loader.gif) center center no-repeat");
    $(dialog_multi).dialog( "option", "title", mozilo_lang["dialog_title_send"]);
    $(dialog_multi).dialog( "option", "buttons", [{
        text: mozilo_lang["page_cancel_reload"],
        click: function() {
            send_object.abort();
            send_object = false;
            var actives_tab = "";
            if(window.location.search != "")
                actives_tab = window.location.search;
            window.location.href = "index.php"+actives_tab;
        }
    }]);
}

function dialog_error_messages() {
    $(dialog_multi).dialog( "option", "title", mozilo_lang["dialog_title_error"]);
    $(dialog_multi).dialog( "option", "buttons", [{
        text: mozilo_lang["close"],
        click: function() { $(this).dialog("close"); }
    }]);
}

function dialog_messages() {
    $(dialog_multi).dialog( "option", "title", mozilo_lang["dialog_title_messages"]);
    $(dialog_multi).dialog( "option", "buttons", [{
        text: mozilo_lang["close"],
        click: function() { $(this).dialog("close"); }
    }]);
}

function dialog_error() {
    $(dialog_multi).dialog( "option", "title", mozilo_lang["dialog_title_error"]);
    $(dialog_multi).dialog( "option", "buttons", [{
        text: mozilo_lang["page_reload"],
        click: function() {
            var actives_tab = "";
            if(window.location.search != "")
                actives_tab = window.location.search;
            window.location.href = "index.php" + actives_tab;
        }
    }]);
}

function dialog_messages_lastbackup() {
    $(dialog_multi).dialog( "option", "title", mozilo_lang["dialog_title_lastbackup"]);
    $(dialog_multi).dialog( "option", "buttons", [{
        text: mozilo_lang["yes"],
        click: function() { send_data($("#lastbackup_yes").text()); }
    }]);
}

function dialog_docu() {
    $(dialog_multi).dialog( "option", "title", mozilo_lang["dialog_title_docu"]);
    $(dialog_multi).dialog( "option", "buttons", []);
}

function dialog_delete() {
    $(dialog_multi).dialog( "option", "title", mozilo_lang["dialog_title_delete"]);
    $(dialog_multi).dialog( "option", "buttons", [{
        text: mozilo_lang["yes"],
        click: function() { send_data(make_send_para_sort_array(),$(this).data("del_object")); }
    },{
        text: mozilo_lang["no"],
        click: function() { $(this).dialog("close"); }
    }]);
    // Achtung nicht alle data entfernen sonst geht der dialog nicht mehr auf
    $(this).removeData("del_object");
}

function dialog_delete_files() {
    $(dialog_multi).dialog( "option", "title", mozilo_lang["dialog_title_delete"]);
    $(dialog_multi).dialog( "option", "buttons", [{
        text: mozilo_lang["yes"],
        click: function() {
            $(this).data("del_object")[0].find('.delete input:checked').siblings('button').addClass('js-nodialog').click();
            $(this).data("del_object")[1].find('.toggle').prop('checked', false);
            $(this).dialog("close");
        }
    },{
        text: mozilo_lang["no"],
        click: function() { $(this).dialog("close"); }
    }]);
    // Achtung nicht alle data entfernen sonst geht der dialog nicht mehr auf
    $(this).removeData("del_object");
}

function dialog_delete_file() {
    $(dialog_multi).dialog( "option", "title", mozilo_lang["dialog_title_delete"]);
    $(dialog_multi).dialog( "option", "buttons", [{
        text: mozilo_lang["yes"],
        click: function() {
            $(this).data("del_object").addClass('js-nodialog').click();
            $(this).dialog("close");
        }
    },{
        text: mozilo_lang["no"],
        click: function() { $(this).dialog("close"); }
    }]);
    // Achtung nicht alle data entfernen sonst geht der dialog nicht mehr auf
    $(this).removeData("del_object");
}

function dialog_gallery_delete() {
    $(dialog_multi).dialog( "option", "title", mozilo_lang["dialog_title_delete"]);
    $(dialog_multi).dialog( "option", "buttons", [{
        text: mozilo_lang["yes"],
        click: function() { send_data(user_para,$(this).data("del_object")); }
    },{
        text: mozilo_lang["no"],
        click: function() { $(this).dialog("close"); }
    }]);
    // Achtung nicht alle data entfernen sonst geht der dialog nicht mehr auf
    $(this).removeData("del_object");
}

function clean_data(data) {
    retSuccess = false;
    retError = false;
    replace_item = false;
    pagecontent = false;
    data = $("<span>"+data+"</span>");
    // vom Server kamm ein tag mit der id replace-item
    if(data.find("#replace-item").length > 0) {
        replace_item = data.find("#replace-item");
        data.find("#replace-item").remove();
    }
    // vom Server kamm ein tag mit der id page-content
    if(data.find("#page-content").length > 0) {
        pagecontent = data.find("#page-content").text();
        data.find("#page-content").remove();
    }
    // vom Server kamm ein tag mit der class success
    if(data.find(".success").length > 0) {
        retSuccess = data.find(".success");
        data.find(".success").remove();
    }
    if(data.find(".error").length > 0) {
        retError = data.find(".error");
        data.find(".error").remove();
    }
    // test ob vom Server noch was anderes kamm auser error oder success
    data.find("*").each(function(i,tag) {
        data.find(tag).remove();
    });
    // wenn ja dann ist das eine php error meldung
    if(data.html().length > 10) {
        retError = $("<span class=\"error js-dialog-reload js-dialog-content\" title=\""+mozilo_lang["dialog_title_error"]+"\">"+data.html()+"</span>");
    }
    return data;
}

function repalce_tags(replace_item) {
    $(replace_item).children().each(function(i,tag) {
        if(tag.tagName == "SELECT") {
            var search_tag = $("[name="+$(tag).attr("name")+"]");
            search_tag.contents().remove();
            search_tag.append($(tag).contents());
            search_tag.multiselect('refresh');
        } else if(typeof $(tag).attr("id") != "undefined") {
            $("#"+$(tag).attr("id")).replaceWith($(tag));
        } else if(typeof $(tag).attr("class") != "undefined") {
            $("."+$(tag).attr("class")).replaceWith($(tag));
        }
    });
}

function send_data(para,change_item) {
    send_object = false;
    if(para.substring(0, 1) != "&")
        para = "&"+para;
    para = "action="+action_activ+para;
    // catpage und gallery sachen
    if(typeof change_item != "undefined") {
        if(send_item_status) {
            para += "&changeart=" + send_item_status;
            if(send_item_status == "gallery_new" || send_item_status == "gallery_del") {
                para += "&galleryname="+rawurlencode_js($(change_item).find('.js-gallery-name').text());
            } else if(send_item_status == "gallery_rename") {
                para += "&galleryname="+rawurlencode_js($(change_item).find('.js-gallery-name').text());
                para += "&gallerynewname="+rawurlencode_js($(change_item).find('.in-gallery-new-name').val());
            } else if(send_item_status == "gallery_size") {
                para += "&"+$(change_item).serialize();
            } else if(send_item_status == "gallery_subtitle") {
                para += "";
            } else if(send_item_status == "cat_page_del") {
                para += make_send_para_change($(change_item).find("table").eq(0));
            } else if(send_item_status == "file_rename") {
                para += "";
            } else
                para += make_send_para_change(change_item);
        }
    }

    $.ajax({
        global: true,
        cache: false,
        type: "POST",
        url: "index.php",
        async: true,
        dataType: "html",// html
        // timeout geht nur bei async: true und ist in error und complete verfügbar
        timeout:20000,
        data: para,
        beforeSend: function(jqXHR) {
            if($(dialog_multi).dialog("isOpen")) {
                $(dialog_multi).dialog("close");
            }
            send_object = jqXHR;
            dialog_open("send_cancel");
        },
        success: function(getdata, textStatus, jqXHR){
            if($(dialog_multi).dialog("isOpen")) {
                send_object = false;
                $(dialog_multi).dialog("close");
            }

            // Achtung vom server muss immer ein tag zurückkommen
            getdata = clean_data(getdata);
            if(retError !== false) {
                dialog_open("from_php",retError);
            } else if(retSuccess !== false) {
                // nur öffnen wenn es auch einen inhalt gibt
                if(retSuccess.text().length > 5) {
                    dialog_open("from_php",retSuccess);
                }
//$('#new-event').append("<br />" + "change select = "+retSuccess.text());
                // catpage und gallery sachen
                if(typeof change_item != "undefined") {
                    if(send_item_status == "gallery_del") {
                        $(change_item).remove();
                    } else if(send_item_status == "gallery_rename") {
                        make_rename_changes(change_item);
                    } else if(send_item_status == "gallery_new") {
                        var new_fileupload = $(change_item).find('.fileupload');
                        new_fileupload.fileupload({
                            dropZone: new_fileupload
                        });
                    } else if(send_item_status == "gallery_size") {
                        cange_size(change_item);
                    } else if(send_item_status == "gallery_subtitle") {
                        change_item.siblings('.fu-subtitle').text(change_item.val()).show(0);
                        change_item.remove();
                    } else if(send_item_status == "file_rename") {
                        file_rename(change_item);
                    } else if(send_item_status == "cat_page_del") {
                        var item_status = $(change_item).parents(".js-li-cat").find("table").eq(0);
                        $(change_item).remove();
                        change_status_pages(item_status);
                    // nur wenns auch ein status gibt beim move cat gibts keinen
                    } else if(send_item_status)
                        set_send_success_changes(change_item);
                }
                // beim catpage wird nee select mit geschickt die müssen wir mit dem original ersetzen
                if(replace_item !== false) {
                    repalce_tags(replace_item);
                }
            } else {
                dialog_open("error","unbekanter fehler");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            if($(dialog_multi).dialog("isOpen")) {
                send_object = false;
                $(dialog_multi).dialog("close");
            }
            dialog_open("error_messages","status= " + textStatus + "\nerror:\n" + errorThrown);
            send_item_status = false;
        },
    });
}

$(function() {
    $('body').append('<div id="dialog-multi"></div>');
    $('body').append('<div style="height:1px;overflow:hidden;"><div id="dialog-test-w" style="float:left;"></div><div class="mo-clear"></div></div>');
    $("#dialog-multi").dialog({
        autoOpen: false,
        resizable: true,
        height: "auto",
        width: "auto",
        modal: true,
        title: "Fehler Meldungen",
        buttons: [],
        create: function(event, ui) {
            $(this).data("focus",false)
            $(this).data("is_open",false)
            dialog_multi = this;
        },
        close: function(event, ui) {
            $("#menu-fix-close-dialog").show(0).attr("id","menu-fix");
            $(this).dialog( "option", "buttons", []);
            // bei den Plugins adminplugins einstelungen wird die width und height geändert
            $(this).dialog("option", "width", "auto");
            $(this).dialog("option", "height", "auto");
            $(this).dialog("option", "resizable", false);
            $(this).data("is_open",false)
            $(this).html("");
            $(this).css("background","transparent");
            if($(this).data("focus")) {
                $(this).data("focus").focus();
                $(this).data("focus",false)
            }
        },
        open: function(event, ui) {
            $("#menu-fix").hide(0).attr("id","menu-fix-close-dialog");
            if($(this).data("is_open")) {
                return false;
            }
            $(this).data("is_open",true)
            if($(this).dialog( "option", "buttons").length > 0)
                $(this).parents(".ui-dialog").find(".ui-dialog-titlebar-close").hide(0);
            else
                $(this).parents(".ui-dialog").find(".ui-dialog-titlebar-close").show(0);
        },
    });
});
