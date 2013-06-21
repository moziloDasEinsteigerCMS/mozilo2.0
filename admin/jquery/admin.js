function make_para(item,user_para) {
    var para = item.serialize();
    if(item.is('[multiple]') && para.length < 1) {
        para = item.attr("name").replace(/\[/g, '%5B').replace(/\]/g, '%5D')+"=null";
    }
    if(item.attr("type") == "checkbox" && para.length < 1) {
        para = item.attr("name")+"=false";
    }
    send_data(user_para+para);
}

function test_newname(name) {
    if(name.length >= 5)
        return true;
    else
        return false;
}

function test_newpw(pw) {
    if(pw.length >= 6) {
        var nr_search = /\d/;
        // das password hat keine zahl und kein grossen buchstaben
        if(!nr_search.test(pw) || pw.toLowerCase() == pw || pw.toUpperCase() == pw) {
            return false;
        } else
            return true;
    } else
        return false;
}

var in_enter_handler = function(event) {
    if(event.which == 13) { // enter
        if($(this).hasClass("maximage"))
            make_para($(".maximage"),"chanceadmin=true&");
        else
            make_para($(this),"chanceadmin=true&");
        return false;
    }
};

// js-in-pwroot js-in-pwuser
var in_pw_handler = function(event) {
    if(event.which == 13) { // enter
        event.preventDefault();
        var in_name = "newname",
            in_pw = "newpw",
            in_pwre = "newpwrepeat",
            in_class = "js-in-pwroot";
        if($(this).hasClass("js-in-pwuser")) {
            in_name = "newusername";
            in_pw = "newuserpw";
            in_pwre = "newuserpwrepeat";
            in_class = "js-in-pwuser";
        }
        $('.'+in_class).css("background-color","transparent");
        var valide = true;
        $('.'+in_class).each(function(i){
            var that = $(this);
            // ist der neue name valide
            if(that.attr("name") == in_name) {
                if(!test_newname(that.val())) {
                    that.css("background-color","#00f");
                    valide = false;
                    return false;
                }
            }
            // ist ein feld nicht ausgefühlt setze den focus darauf
            if(that.val().length < 1) {
                that.focus();
                valide = false;
                return false;
            }
            // ist das neue password oder die password wiederholung valide
            if(that.attr("name") == in_pw || that.attr("name") == in_pwre) {
                if(!test_newpw(that.val())) {
                    that.css("background-color","#00f");
                    valide = false;
                    return false;
                }
            }
        });
        if(!valide) {
            return false;
        }
        // ist das password und die password wiederholung sind nicht gleich
        if($("input[name=\""+in_pw+"\"]").val() != $("input[name=\""+in_pwre+"\"]").val()) {
            $("input[name=\""+in_pwre+"\"]").css("background-color","#0f0");
            $("input[name=\""+in_pwre+"\"]").val("");
            $("input[name=\""+in_pwre+"\"]").focus();
            return false;
        }
        // wir sind biss hier gekommen dann schein alles gut zu sein
        make_para($('.'+in_class),"");
        $("input[name=\""+in_pw+"\"], input[name=\""+in_pwre+"\"]").val("");
        return false;
    }
};

var in_change_handler = function(event) {
    make_para($(this),"chanceadmin=true&");
};

var in_chmod_handler = function(event) {
    var chmod = $('input[name="chmodnewfilesatts"]').val();
    if(chmod.length == 3 && chmod >= "000" && chmod <= "777") {
        var para = "chmodnewfilesatts="+chmod+"&chmodupdate=true";
        send_data(para);
    } else {
        dialog_multi.data("focus",$('input[name="chmodnewfilesatts"]'));
        dialog_open("error_messages","Es sind keine datei rechte angegeben worden oder Fehlerhaft");
    }
};

$(function() {

    $('input[type="text"]:not(.js-in-pwroot, .js-in-pwuser)').bind("keydown", in_enter_handler);

    $('.js-in-pwroot , .js-in-pwuser' ).bind("keydown", in_pw_handler);

    $('input[name="chmodupdate"]').bind("click", in_chmod_handler);

    $('input[name="deluser"]').bind("click", function(event) {
        send_data("deluser=true");
        $('input[name="newusername"]').val("");
    });

    $('.js-language, .js-noroot-tabs, .js-noroot-config, .js-noroot-admin, .js-noroot-plugins, .js-noroot-template').bind("change", in_change_handler);

    $('.js-language').multiselect({
        multiple: false,
        showClose: false,
        showSelectAll:false,
        noneSelectedText: false,
        selectedList: 1
   }).multiselectfilter();

    $('.js-noroot-tabs, .js-noroot-config, .js-noroot-admin, .js-noroot-plugins, .js-noroot-template').multiselect({
        showSelectAll:true,
        showClose: false,
        multiple: true,
        selectedList: 0,
        selectedText: function(numChecked, numTotal, checkedItems) {
            return $(this.element).attr("title");
        },
        checkAll: function(){
            if($(this).hasClass('js-noroot-tabs'))
                $('.js-noroot-config, .js-noroot-admin').multiselect('enable');
            make_para($(this),"chanceadmin=true&");
        },
        uncheckAll: function(){
            if($(this).hasClass('js-noroot-tabs'))
                $('.js-noroot-config, .js-noroot-admin').multiselect('disable');
            make_para($(this),"chanceadmin=true&");
        },
        click: function(event, ui){
            if($(this).hasClass('js-noroot-tabs') && (ui.value == "config" || ui.value == "admin" || ui.value == "plugins" || ui.value == "template")) {
                $('.js-noroot-'+ui.value).multiselect((ui.checked ? 'enable' : 'disable'));
            }
        }
    }).multiselectfilter();
/*
    if($('.js-noroot-plugins option').length < 1)
        $('.js-noroot-plugins').multiselect('disable');*/

    $('.js-noroot-tabs').multiselect("option","noneSelectedText",$('.js-noroot-tabs').attr('title'));
    $('.js-noroot-config').multiselect("option","noneSelectedText",$('.js-noroot-config').attr('title'));
    $('.js-noroot-admin').multiselect("option","noneSelectedText",$('.js-noroot-admin').attr('title'));
    $('.js-noroot-plugins').multiselect("option","noneSelectedText",$('.js-noroot-plugins').attr('title'));
    $('.js-noroot-template').multiselect("option","noneSelectedText",$('.js-noroot-template').attr('title'));

    var backup_items = $('#backup_include_cms, #backup_include_catpage, #backup_include_gallery, #backup_include_layouts, #backup_include_plugins, #backup_include_docu');
    backup_items.bind("change", function() {
        var bytes = 0;
        backup_items.filter(':checked').each(function () {
            var tmp_label_bytes = $('label[for="'+this.id+'"] .js-file-size').text();
            var tmp_bytes = parseFloat(tmp_label_bytes);
            if(tmp_label_bytes.substr(-2) == "KB")
                tmp_bytes = parseFloat(tmp_label_bytes) * 1000;
            if(tmp_label_bytes.substr(-2) == "MB")
                tmp_bytes = parseFloat(tmp_label_bytes) * 1000000;
            bytes += tmp_bytes;
        });
        var bytes_string = bytes + " B";
        if (bytes >= 1000000000)
            bytes_string = (bytes / 1000000000).toFixed(2) + ' GB';
        else if (bytes >= 1000000)
            bytes_string = (bytes / 1000000).toFixed(2) + ' MB';
        else if (bytes >= 1000)
            bytes_string = (bytes / 1000).toFixed(2) + ' KB';
        $('.js-file-size-summe').text(bytes_string);
    });

});