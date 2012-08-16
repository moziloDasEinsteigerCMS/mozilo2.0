
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
        $('.js-usecmssyntax').parents('.mo-in-ul-li').show(anim_speed);
    } else {
        $('.js-usecmssyntax').parents('.mo-in-ul-li').hide(anim_speed);
    }
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
        selectedList: 1,
    }).multiselectfilter();

});

