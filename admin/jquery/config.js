
var in_enter_handler = function(event) {
    if(event.which == 13) { // enter
        make_para(this);
        return false;
    }
}

var in_pw_handler = function(event) {
    if(event.which == 13) { // enter
        event.preventDefault();
        $('input[name="newname"],input[name="newpw"],input[name="newpwrepeat"]').css("background-color","transparent");
        var valide = true;
        $('input[name="newname"],input[name="newpw"],input[name="newpwrepeat"]').each(function(i){
            /* ist der neue name valide */
            if($(this).attr("name") == "newname") {
                if(!test_newname($(this).val())) {
                    $(this).css("background-color","#00f");
                    valide = false;
                    return false;
                }
            }
            /* ist ein feld nicht ausgefühlt setze den focus darauf */
            if($(this).val().length < 1) {
                $(this).focus();
                valide = false;
                return false;
            }
            /* ist das neue password oder die password wiederholung valide */
            if($(this).attr("name") == "newpw" || $(this).attr("name") == "newpwrepeat") {
                if(!test_newpw($(this).val())) {
                    $(this).css("background-color","#00f");
                    valide = false;
                    return false;
                }
            }
        });
        if(!valide) {
            return false;
        }
        /* ist das password und die password wiederholung sind nicht gleich */
        if($('input[name="newpw"]').val() != $('input[name="newpwrepeat"]').val()) {
            $('input[name="newpwrepeat"]').css("background-color","#0f0");
            $('input[name="newpwrepeat"]').val("");
            $('input[name="newpwrepeat"]').focus();
            return false;
        }
        /* wir sind biss hier gekommen dann schein alles gut zu sein */
        make_para($('input[name="newname"],input[name="newpw"],input[name="newpwrepeat"]'));
        $('input[name="newname"],input[name="newpw"],input[name="newpwrepeat"]').val("");
        return false;
    }
}

var in_change_handler = function(event) {
    make_para(this);
}

var in_change_usecmssyntax_handler = function(event) {
// $("#out").html($("#out").html()+"<br>expert = "+$(this).prop("checked"));
    if($(this).prop("checked")) {
        $('#js-editor-toolbar').css("display","block");
        $('#select-mode option:selected').attr('selected',false);
        $('#select-mode option[value="mozilo"]').attr('selected',true)
        $('.js-usecmssyntax').parents('.mo-in-ul-li').show(anim_speed);
    } else {
        $('#js-editor-toolbar').css("display","none");
        $('#select-mode option:selected').attr('selected',false);
        $('#select-mode option[value="html"]').attr('selected',true)
        $('.js-usecmssyntax').parents('.mo-in-ul-li').hide(anim_speed);
    }
    editor_session.setMode("ace/mode/"+$('#select-mode').val());
    make_para(this);
}

var in_chmod_handler = function(event) {
    var chmod = $('input[name="chmodnewfilesatts"]').val();
    if(chmod.length == 3 && chmod >= "000" && chmod <= "777") {
        var para = "chmodnewfilesatts="+chmod+"&chmodupdate=true";
        send_data(para);
    } else {
        $(dialog_multi).data("focus",$('input[name="chmodnewfilesatts"]'))
        dialog_open("error_messages","Es sind keine datei rechte angegeben worden oder Fehlerhaft");
    }
}

var edit_handler = function() {
    if($(this).hasClass("ui-state-disabled"))
        return false;
    $(dialog_editor).dialog("option", "width", $(".mo-td-content-width").eq(0).width());
    $(dialog_editor).dialog("option", "height", (parseInt($(window).height()) - dialogMaxheightOffset));

    $(dialog_editor).dialog("option", "title", "Benutzerdefinierte Syntaxelemente");
    $(dialog_editor).data("send_object",false);

    $(dialog_editor).dialog("open");
    editor_file = "savesyntax=true";
    send_editor_data(editor_file,false);
}


$(function() {

    if($('input[name="usecmssyntax"]').length > 0 && $('input[name="usecmssyntax"]:checked').length == 0) {
        $('.js-usecmssyntax').parents('.mo-in-ul-li').hide(0);
    }

    $('input[type="text"]:not(input[name="newname"],input[name="newpw"],input[name="newpwrepeat"])').bind("keydown", in_enter_handler);
    $('input[type="radio"]').bind("change", in_change_handler);
    $('input[type="checkbox"]:not(.js-ace-in)').bind("change", in_change_handler);

    $('input[name="usecmssyntax"]').bind("change", in_change_usecmssyntax_handler);

    $('input[name="newname"],input[name="newpw"],input[name="newpwrepeat"]').bind("keydown", in_pw_handler);

    $('input[name="chmodupdate"]').bind("click", in_chmod_handler);

    $('.js-editsyntax').bind("click", edit_handler);

    $('select:not(.overviewselect, .usersyntaxselectbox, .js-ace-select)').bind("change", in_change_handler);

    $('select:not(.overviewselect, .usersyntaxselectbox, .js-ace-select)').multiselect({
        multiple: false,
        showClose: false,
        showSelectAll:false,
        noneSelectedText: false,
        selectedList: 1
    }).multiselectfilter();

    if($('#js-config-default-color-box').length > 0) {
        var $default_color_drag = $('#js-config-default-color-box');
        var $new_default_color_drag = $('.js-new-config-default-color');
        var $js_del_config_default_color = $('#js-del-config-default-color');
        $default_color_drag.sortable({
            connectWith: "#js-config-default-color-box",
            placeholder: "ce-default-color-img ui-corner-all ui-state-highlight",
            stop: function( event, ui ) {
                $('.js-new-config-default-color' ,$default_color_drag).removeClass('js-new-config-default-color ce-bg-color-change ui-draggable').attr('title',$('#js-new-default-color-value').val());
                var in_value = '';
                $('img' ,$default_color_drag).each(function(index) {
                    var new_value = $(this).attr('title').toUpperCase();
                    // wenn es nicht hex konform ist
                    if(new_value.search(/[^A-F0-9]/) != -1) {
                        $(this).remove();
                    } else
                        in_value += ","+new_value;
                });
                $('input[name="defaultcolors"]').val(in_value.substr(1));
//$('#out').html($('#out').html()+"<br>stop="+in_value.substr(1))
            }
        });

        $new_default_color_drag.draggable({
            connectToSortable: "#js-config-default-color-box",
            revert: "invalid",
            containment: "document",
            helper: "clone",
            cursor: "move",
            start: function( event, ui ) {
                $('#js-new-default-color-value:focus').blur();
            }
        });

        $js_del_config_default_color.droppable({
            accept: "#js-config-default-color-box > img",
            hoverClass: "ui-state-default",
            tolerance: "touch",
            out: function( event, ui ) {
                $(this).attr('src',URL_BASE+ADMIN_DIR_NAME+'/gfx/icons/24x24/delete.png');
            },
            over: function( event, ui ) {
                $(this).attr('src',URL_BASE+ADMIN_DIR_NAME+'/gfx/icons/24x24/delete_full.png');
            },
            drop: function( event, ui ) {
                $(this).attr('src',URL_BASE+ADMIN_DIR_NAME+'/gfx/icons/24x24/delete.png');
                ui.draggable.remove();
            }
        });


        $('.js-save-default-color').bind("click", function(){
            make_para('input[name="defaultcolors"]');
            if($default_color_drag.html().length > 10)
                $('#ce-colorchange .ce-default-color-box').show();
            else
                $('#ce-colorchange .ce-default-color-box').hide();
            $('#ce-colorchange .ce-default-color-box').html($default_color_drag.html());
        });
    };
});

