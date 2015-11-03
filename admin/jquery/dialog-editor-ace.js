var editor_file = ""; // das muss gesetzt werden beim öfnen des editors
var dialog_editor = false;
var editor;
var editor_session;
var dialog_prev_pageedit_box = false;

if(typeof moziloSyntax == "undefined")
    var moziloSyntax = false;
if(typeof moziloUserSyntax != "undefined") {
    if(!moziloSyntax)
        moziloSyntax = moziloUserSyntax;
    else
        moziloSyntax += "|"+moziloUserSyntax;
}
//var ace_width_test_string = '<pre id="ace_width_test_string" class="ace_editor">WWWWWWWWWW</pre>';

function send_editor_data(para,savepage) {
    if(para.substring(0, 1) != "&")
        para = "&"+para;
    para = "action="+action_activ+para;
    $.ajax({
        global: true,
        cache: false,
        type: "POST",
        url: "index.php",
        data: para,
        async: true,
        dataType: "html",
        // timeout geht nur bei async: true und ist in error und complete verfügbar
        timeout:30000,
        beforeSend: function(jqXHR) {
            dialog_editor.data("send_object",jqXHR);
            // das dient dazu das der error dialog nich aufgeht
            dialog_editor.data("send_abort",false);
            dialog_open("editor_send_cancel");
        },
        success: function(getdata, textStatus, jqXHR){
            if(dialog_multi.dialog("isOpen")) {
                dialog_multi.dialog("close");
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
                if(!savepage && pagecontent !== false) {
                    dialog_editor.dialog("open");
                    editor_session.setValue(pagecontent);
                    init_ace_editor();
                    dialog_editor.data("diffcontent",pagecontent);
                }

                if(savepage) {
                    dialog_editor.data("diffcontent",editor_session.getValue());
                    if(dialog_editor.data("close_after_save") === true) {
                        dialog_editor.dialog("close");
                        return;
                    }
                }
                // beim config wird nee select mit geschickt die müssen wir mit dem original ersetzen
                if(replace_item !== false) {
                    replace_tags(replace_item);
                }
            } else {
               dialog_open("error","unbekanter fehler");
            }
            dialog_editor.data("send_object",false);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            if(dialog_multi.dialog("isOpen")) {
                dialog_multi.dialog("close");
            }
            dialog_editor.data("send_object",false);
            if(!dialog_editor.data("send_abort")) {
                dialog_open("error_messages","status= " + textStatus + "\nerror:\n" + errorThrown);
            }
        }
    });
}


function send_editor_preview() {
    var prev_url = $('.ui-dialog-title a',dialog_editor.parents('.ui-dialog')).attr("href");
    $.ajax({
        global: true,
        cache: false,
        type: "POST",
        url: prev_url,
        data: "prevcontentadmin="+ rawurlencode_js(editor_session.getValue()),
        async: true,
        dataType: "html",// html
        // timeout geht nur bei async: true und ist in error und complete verfügbar
        timeout:20000,
        beforeSend: function(jqXHR) {
            dialog_editor.data("send_object",jqXHR);
            // das dient dazu das der error dialog nich aufgeht
            dialog_editor.data("send_abort",false);
            dialog_open("editor_send_cancel");
        },
        success: function(getdata, textStatus, jqXHR){
            if(dialog_multi.dialog("isOpen")) {
                dialog_multi.dialog("close");
            }
            if(getdata == "true") {
                var prev_iframe = $('<iframe frameborder="0" width="100%" height="100%" align="left" style="overflow:visible;"><\/iframe>').prop('src', prev_url);
                dialog_prev_pageedit_box.dialog({
                    width: (parseInt($(window).width()) - dialogMaxheightOffset),
                    height: (parseInt($(window).height()) - dialogMaxheightOffset)
                }).append(prev_iframe).dialog("open");
            } else {
                dialog_open("error_messages","unbekanter fehler");
            }
            dialog_editor.data("send_object",false);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            if(dialog_multi.dialog("isOpen")) {
                dialog_multi.dialog("close");
            }
            dialog_editor.data("send_object",false);
            if(!dialog_editor.data("send_abort")) {
                dialog_open("error_messages","status= " + textStatus + "\nerror:\n" + errorThrown);
            }
        }
    });
}

function dialog_editor_send_cancel() {
    dialog_multi.css("background", "url(" + ICON_URL + "ajax-loader.gif) center center no-repeat")
        .dialog({
            title: mozilo_lang["dialog_title_send"],
            buttons: [{
                text: mozilo_lang["button_cancel"],
                click: function() {
                    dialog_editor.data("send_abort",true);
                    dialog_editor.data("send_object").abort();
                    dialog_multi.dialog("close");
                }
        }]});
}

function dialog_editor_save_beforclose() {
    dialog_multi.dialog({
        title: mozilo_lang["dialog_title_save_beforeclose"],
        buttons: [{
            text: mozilo_lang["button_save"],
            click: function() {
                dialog_editor.data("close_after_save",true);
                send_editor_data(editor_file+"&content="+rawurlencode_js(editor_session.getValue()),true);
                dialog_multi.dialog("close");
            }
        },{
            text: mozilo_lang["button_cancel"],
            click: function() { dialog_multi.dialog("close"); }
        },{
            text: mozilo_lang["page_edit_discard"],
            click: function() {
                dialog_editor.data("diffcontent",false);
                dialog_editor.dialog("close");
                dialog_multi.dialog("close");
        }
    }]}).html(returnMessage(false, mozilo_lang["error_save_beforeclose"]));
}

function isSyntaxSelect(range_ace,aTag) {
    var tmp_test;
    range_ace.start.column -= 1;
    range_ace.end.column += 1;
    var test_string = editor_session.doc.getTextRange(range_ace);
    range_ace.start.column += 1;
    range_ace.end.column -= 1;

    if(moziloSyntax) {
        tmp_test = new RegExp("^\\[(("+moziloSyntax+"){1,1}[\\|\\]]{1,1}|("+moziloSyntax+"){1,1}=(.|\\s)*\\|)$");
        if(tmp_test.test(test_string) && tmp_test.test(aTag))
            return aTag.replace(/[\[\|\]]/g, "");
    }
    // nur mozilo Eigen Platzhalter Tauschen
    if(typeof moziloPlace != "undefined") {
        tmp_test = new RegExp("^\\{("+moziloPlace+")\\}$");
        if(tmp_test.test(test_string) && tmp_test.test(aTag))
            return aTag.replace(/[\{\}]/g, "");
    }
    if(typeof moziloSmileys != "undefined") {
        var tmp_test = new RegExp("^\\:("+moziloSmileys+")\\:$");
        var tmp_aTag = aTag.substr(1, (aTag.length -2));
        if(tmp_test.test(test_string) && tmp_test.test(tmp_aTag)) {
            aTag = aTag.substr(2, (aTag.length -4));
            return aTag;
        }
    }
    return false;
}

function insert_ace(aTag, eTag, select) {
    var test_range = editor_session.selection.rangeList,
        range_ace = [],
        tmp_start = 0,
        tmp_end = 0,
        // das ist für die farbe
        tmp_aTag,
        tmp_eTag,
        syntax_color = "[farbe=";

    // im css mode gibts nur #hex
    if($('#select-mode').val() == "css") {
        aTag = "#"+aTag.substr(syntax_color.length,6);
        eTag = false;
    }

    if(test_range.ranges.length == 0) {
        range_ace[0] = editor.getSelectionRange();
        if(false !== (tmp = isSyntaxSelect(range_ace[0],aTag))) {
            aTag = tmp;
            eTag = false;
            select = false;
        }
    } else {
        for (var i = 0; i < test_range.ranges.length; i++) {
            range_ace[i] = test_range.ranges[i];
            if(false !== (tmp = isSyntaxSelect(range_ace[i],aTag))) {
                aTag = tmp;
                eTag = false;
                select = false;
            }
        }
    }
    tmp_aTag = aTag;
    tmp_eTag = eTag;

    editor.exitMultiSelectMode();
    var row_column_offset = 0;
    for(var i = 0; i < range_ace.length; i++) {
        // wenn in der farbe nur der hex ersetzt wurde a/eTag wider herstellen
        aTag = tmp_aTag;
        eTag = tmp_eTag;
        // wenn was selectet ist überschreiben wir select
        if(range_ace[i].start.column < range_ace[i].end.column)
            select = true;
        var selectet = editor_session.doc.getTextRange(range_ace[i]);
        // anscheinend wurde der farbcode ohne die # selectiert
        if(aTag.length == 7 && aTag.substr(0, 1) == "#" && selectet.substr(0, 1) != "#")
            aTag = aTag.substr(1,6);
        // ist aTag das farbe syntaxelement
        if(aTag.substr(0, syntax_color.length) == syntax_color && range_ace[i].start.column >= syntax_color.length) {
            range_ace[i].start.column -= syntax_color.length;
            range_ace[i].end.column -= selectet.length;
            // sind wir in der farb syntax dann ersetzen wir nur den hex wert
            if(editor_session.doc.getTextRange(range_ace[i]) == syntax_color) {
                aTag = aTag.substr(syntax_color.length,6);
                eTag = false;
            }
            range_ace[i].start.column += syntax_color.length;
            range_ace[i].end.column += selectet.length;
        }

        tmp_start = range_ace[i].start.column;
        tmp_end = range_ace[i].end.column;

        if(eTag) {
            editor_session.doc.replace(range_ace[i], aTag+selectet+eTag);
            var end_column = aTag.length;
            if(range_ace[i].start.row < range_ace[i].end.row)
                end_column = 0;
            range_ace[i].start.column += aTag.length;
            range_ace[i].end.column += end_column;
        } else {
            editor_session.doc.replace(range_ace[i], aTag);
            range_ace[i].end.column = range_ace[i].start.column + aTag.length;
            range_ace[i].end.row = range_ace[i].start.row;
        }
        if(select) {
            if(i == 0)
                // das selectierte wieder selectioeren, setzt auch den cursor ins element
                editor_session.selection.setSelectionRange(range_ace[i],false);
            editor_session.selection.addRange(range_ace[i], false);
            // offset der nächsten selection setzen
            if(typeof range_ace[(i + 1)] != "undefined") {
                var e_tag = 0;
                if(eTag)
                    e_tag = eTag.length;
                if(range_ace[i].start.row == range_ace[(i + 1)].start.row) {
                    row_column_offset += (range_ace[i].start.column - tmp_start)
                        + ((range_ace[i].end.column - range_ace[i].start.column) - (tmp_end - tmp_start))
                        + e_tag;
                    range_ace[(i + 1)].start.column = range_ace[(i + 1)].start.column + row_column_offset;
                    range_ace[(i + 1)].end.column = range_ace[(i + 1)].end.column + row_column_offset;
                } else
                    row_column_offset = 0;
            }
        }
    }
    editor.focus();
}

function init_ace_editor() {
    var box_height = $('#pageedit-box-inhalt').height(),
        new_width = $('#pagecontent-border').width(),
        mo_syntax_box = 0;
    if($('#js-editor-toolbar').css('display') != "none")
        mo_syntax_box = $('#js-editor-toolbar').outerHeight(true);

    var new_height = (box_height - mo_syntax_box - $('#ace-menu-box').outerHeight(true) - 2);

    $('#pagecontent-border').height(new_height);

    $('#'+meditorID).height(new_height).width((new_width - 2));
    editor.resize();
    editor.focus();
}

function set_editor_settings() {
    if(navigator.cookieEnabled == true) {
        var cookieablauftage = 50,
            ablauf = new Date(),
            cookielifetime = ablauf.getTime() + (cookieablauftage * 24 * 60 * 60 * 1000);
        ablauf.setTime(cookielifetime);

        var settings = set_icon_checked($('#show_gutter'),false)+",";
        settings += set_icon_checked($('#show_hidden'),false)+",";
        if($('#select-mode').val() == "text")
            settings += $('#select-mode').val()+",";
        else
            settings += "mozilo,";
        settings += $('#select-fontsize').val();
        document.cookie = "mozilo_editor_settings=" + settings + "; expires=" + ablauf.toGMTString();
    }
}

function get_editor_settings() {
    // set default
    $('#select-mode option[value="mozilo"]').attr('selected',true);
    $('#select-fontsize option[value="12px"]').attr('selected',true);
    $('#show_gutter').addClass('ed-ace-icon-active');
    if(navigator.cookieEnabled == true) {
        if(document.cookie && document.cookie.match(/mozilo_editor_settings=[^;]+/i)) {
            var settings = document.cookie.match(/mozilo_editor_settings=[^;]+/i)[0].split("=")[1].split(",");

            if(settings[0] == "true")
                $('#show_gutter').addClass('ed-ace-icon-active');
            if(settings[1] == "true")
                $('#show_hidden').addClass('ed-ace-icon-active');
            if(settings[2] == "text") {
                $('#select-mode option:selected').attr('selected',false);
                $('#select-mode option[value="'+settings[2]+'"]').attr('selected',true);
            }
            $('#select-fontsize option:selected').attr('selected',false);
            $('#select-fontsize option[value="'+settings[3]+'"]').attr('selected',true);
        } else {
            set_editor_settings();
        }
    }
};

function set_icon_checked(item,setcss) {
    if(setcss && !item.hasClass('ed-ace-icon-active'))
        item.addClass('ed-ace-icon-active');
    else if(setcss && item.hasClass('ed-ace-icon-active'))
        item.removeClass('ed-ace-icon-active');

    var return_set = false;
    if(item.hasClass('ed-ace-icon-active'))
        return_set = true;
    return return_set;
}

$(function() {

    get_editor_settings();
    editor = ace.edit(meditorID);
    editor_session = editor.getSession();
    editor.setTheme("ace/theme/mozilo");
    editor.setFontSize($('#select-fontsize').val());
    editor.setSelectionStyle("line"); // "line" "text"
    editor_session.setFoldStyle("markbegin");
    editor.setShowFoldWidgets(true);
    if(usecmssyntax != "true") {
        $('#select-mode option:selected').attr('selected',false);
        $('#select-mode option[value="html"]').attr('selected',true)
    }
    editor_session.setMode("ace/mode/"+$('#select-mode').val());
    editor_session.setUseWrapMode(true);
// Actung nicht setzen da sond der Foldmode nicht richtig geht auser setUseWrapMode(false) ist gesetzt
//    editor_session.setWrapLimitRange(80, 80);
    editor_session.setTabSize(4);

    editor.renderer.setShowPrintMargin(false);
    // Achtung in die admin.css muss div.ace_scroller { overflow-x: hidden; } sonst gehts nicht
    editor.renderer.setHScrollBarAlwaysVisible(false);

    editor.renderer.setShowGutter(set_icon_checked($('#show_gutter'),false));
    editor.setShowInvisibles(set_icon_checked($('#show_hidden'),false));
    editor.destroy();

    // der opera hat probleme mit den bold sachen im ace editor
    // älterer firefox auch
//     if(navigator.appName.toLowerCase() == "opera") {
       // $('.ace_editor').css('font-family','monospace');
//     }

    $('#show_gutter').bind('click', function() {
        editor.renderer.setShowGutter(set_icon_checked($(this),true));
        editor.focus();
        set_editor_settings();
    });

    $('#show_hidden').bind('click', function() {
        editor.setShowInvisibles(set_icon_checked($(this),true));
        editor.focus();
        set_editor_settings();
    });

    $('#select-mode').bind('change', function() {
        editor_session.setMode("ace/mode/"+$(this).val());
        editor.focus();
        set_editor_settings();
    });

    $('#select-fontsize').bind('change', function() {
        editor.setFontSize($(this).val());
        editor.focus();
        set_editor_settings();
    });

    $('#toggle_fold').bind('click', function() {
        if($(this).hasClass('foldet')) {
            editor_session.unfold();
            $(this).removeClass('foldet')
        } else {
            editor_session.foldAll();
            $(this).addClass('foldet')
        }
        editor.focus();
    });

    $('#search').bind('click', function() {
        if($('#search-text').val() == "")
            return;
        editor.exitMultiSelectMode();
        if($('#search-all').prop("checked")) {
            editor.findAll($('#search-text').val());
        } else {
            editor.find($('#search-text').val());
        }
        editor.centerSelection();
        editor.focus();
    });

    $('#replace').bind('click', function() {
        if($('#replace-text').val() != "" && !editor_session.selection.isEmpty())
            insert_ace($('#replace-text').val(), false, true);
        else
            editor.focus();
    });

    $('#undo').bind('click', function(event) {
        editor_session.getUndoManager().undo(true);
        editor.exitMultiSelectMode();
        editor.focus();
    });
    $('#redo').bind('click', function(event) {
        editor_session.getUndoManager().redo(true);
        editor.exitMultiSelectMode();
        editor.focus();
    });


    $("body").append('<div id="prev-pageedit-box"><\/div>');

    $("#pageedit-box").dialog({
        autoOpen: false,
        height: "auto",
        width: "auto",
        modal: true,
        position: "center",
        resizable: true,
        dialogClass: "mo-shadow",
        create: function(event, ui) {
            dialog_editor = $(this);
            dialog_editor.data("send_object",false);

            dialog_editor.parents('.ui-dialog').find('.ui-dialog-titlebar').prepend(dialog_editor.find('.js-docu-link'));
        },
        beforeClose: function(event, ui) {
            // hat sich die Inhaltseite geändert
            if(dialog_editor.data("diffcontent") !== false && dialog_editor.data("diffcontent") != editor_session.getValue()) {
                dialog_open("editor_save_beforclose");
                    event.preventDefault();
            }

        },
        buttons: [{
            text: mozilo_lang["button_save"],
            click: function() {
                send_editor_data(editor_file+"&content="+rawurlencode_js(editor_session.getValue()),true);
            }
        }],
        close: function(event, ui) {
            $("#menu-fix-close-editor").show(0).attr("id","menu-fix");
            if(dialog_editor.data("send_object"))
                dialog_editor.data("send_object").abort();
            dialog_editor.data("send_object",false);
            editor_file = "";
            editor_session.setValue("dummy");
            editor.destroy();
            dialog_editor.data("diffcontent",false);
            dialog_editor.data("close_after_save",false);
            if($('.js-coloreditor-button').length > 0) {
                $('#ce-colorchange').dialog("close");
            }
        },
        open: function(event, ui) {
            if($('.js-coloreditor-button').length > 0)
                $('#ce-colorchange').dialog("close");
            $("#menu-fix").hide(0).attr("id","menu-fix-close-editor");
            $('.overviewselect, .usersyntaxselectbox').multiselect( "option", "maxHeight", $("#"+meditorID).closest('td').outerHeight() + dialog_editor.next('.ui-dialog-buttonpane').height());
            // ein hack das die select grösse stimt
            $('select[name="select-mode"]').closest('div').width($('select[name="select-mode"]').outerWidth());
            $('select[name="select-fontsize"]').closest('div').width($('select[name="select-fontsize"]').outerWidth());
            // das ist wichtig da erst dann die button breite bekant ist
            $('.overviewselect, .usersyntaxselectbox, .js-ace-select').multiselect("refresh");
        },//Stop
        resize: function(event, ui) {
            init_ace_editor();
        }
    });

    if(action_activ == "catpage") {
        dialog_editor.dialog({buttons: [{
            text: mozilo_lang["button_save"],
            click: function() {
                send_editor_data(editor_file+"&content="+rawurlencode_js(editor_session.getValue()),true);
            }
        },{
            text: mozilo_lang["button_preview"],
            click: function() {
                send_editor_preview();
            }
        }]});

        $("#prev-pageedit-box").dialog({
            autoOpen: false,
            width: "auto",
            height: "auto",
            modal: true,
            position: "center",
            resizable: true,
            create: function(event, ui) {
                dialog_prev_pageedit_box = $(this);
            },
            close: function(event, ui) {
                if(dialog_editor.data("send_object"))
                    dialog_editor.data("send_object").abort();
                dialog_editor.data("send_object",false);
                var file_href = $('.ui-dialog-title a',dialog_editor.parents('.ui-dialog')).attr("href");
                $.post( file_href,{ prevcontentadmin: "prevcontentadminthisclear"});
                $(this).html("");
                editor.focus();
            }
        });
    }

    $('.overviewselect, .usersyntaxselectbox').multiselect({
        multiple: false,
        showClose: false,
        showSelectAll:false,
        closeOptgrouptoggle: false,
        noneSelectedText: false,
        selectedList: 0,
        selectedText: function(numChecked, numTotal, checkedItems) {
            $(this.labels).removeClass("ui-state-highlight ui-state-hover");
            return $(this.element).attr("title");
        }
    }).multiselectfilter();

    $('select[name="template_css"], select[name="platzhalter"]').multiselect({
        click: function(event, ui){
            insert_ace(ui.value,false,false);
        }
    });

    $('select[name="files"], select[name="gals"]').multiselect({
        click: function(event, ui){
            insert_ace(FILE_START+ui.value+FILE_END,false,false);
        }
    });

    $('select[name="pages"]').multiselect({
        closeOptgrouptoggle: true,
        click: function(event, ui){
            insert_ace(FILE_START+ui.value+FILE_END,false,false);
        },
        optgrouptoggle: function(event, ui){
            insert_ace(FILE_START+ui.label+FILE_END,false,false);
        }
    });

    $('select[name="usersyntax"]').multiselect({
        click: function(event, ui){
            // [user syntax|...] und [user syntax=|...] nur {VALUE} wird ersetzt
            if (ui.value.search(/\|\.\.\.\]/) != -1) {
                insert_ace(ui.value.substring(0, ui.value.length-4), ']',true);
            }
            // [user syntax=|] nur {DESCRIPTION} wird ersetzt da keine {VALUE}
            else if (ui.value.search(/\=\.\.\.\|\]/) != -1) {
                insert_ace(ui.value.substring(0, ui.value.length-5), '|]',true);
            }
            // [user syntax] kein {DESCRIPTION} und {VALUE} [user syntax] wird eingesetzt
            else {
                insert_ace(ui.value,false,false);
            }
       }
    });

    $('select[name="plugins"]').multiselect({
        click: function(event, ui){
            // {PLUGIN|}
            if (ui.value.search(/\|\}/) != -1) {
                insert_ace(ui.value.substring(0, ui.value.length-1), '}',true);
            }
            // {PLUGIN|...}
            else if (ui.value.search(/\.\.\.\}/) != -1) {
                insert_ace(ui.value.substring(0, ui.value.length-4), '}',true);
            }
            // {PLUGIN|wert}
            else if (ui.value.search(/\|/) != -1) {
                insert_ace(ui.value,false,true);
            } 
            // {PLUGIN}
            else {
                insert_ace(ui.value,false,false);
            }
       }
    });

    $('.js-ace-select').multiselect({
        multiple: false,
        showClose: false,
        showSelectAll:false,
        noneSelectedText: false,
        minWidth: 20,
        selectedList: 1
    });


    $("body").on("click",".ed-syntax-user", function(event){
        event.preventDefault();
        // [user syntax|...] und [user syntax=|...] nur {VALUE} wird ersetzt
        if (this.value.search(/\|\.\.\.\]/) != -1) {
            insert_ace(this.value.substring(0, this.value.length-4), ']',true);
        }
        // [user syntax=|] nur {DESCRIPTION} wird ersetzt da keine {VALUE}
        else if (this.value.search(/\=\.\.\.\|\]/) != -1) {
            insert_ace(this.value.substring(0, this.value.length-5), '|]',true);
        }
        // [user syntax] kein {DESCRIPTION} und {VALUE} [user syntax] wird eingesetzt
        else {
            insert_ace(this.value,false,false);
        }
    });

/*
    $('#pageedit-box').on({
        mouseenter: function() { 
            $(this).addClass("ui-state-hover").removeClass("ui-state-active");
        },
        mouseleave: function () {
            $(this).removeClass("ui-state-hover").addClass("ui-state-active");
        }
    },".ed-syntax-hover");//ui-state-hover ed-syntax-icon
*/
//$(".ui-dialog").show(0);
});
