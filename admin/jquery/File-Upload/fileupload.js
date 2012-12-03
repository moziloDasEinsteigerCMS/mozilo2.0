function file_rename(change_item) {
    var curent_name = new RegExp('file='+change_item.siblings('.fu-rename-file').text()),
        curent_newname = 'file='+change_item.val(),

        curent_name_srv = new RegExp('/'+change_item.siblings('.fu-rename-file').text()),
        curent_newname_srv = '/'+change_item.val(),
        template = change_item.closest('.template-download');

    change_item.siblings('.fu-rename-file').removeClass('fu-nosearch').text(change_item.val()).show(0);

    template.find('.delete button').attr('data-url',template.find('.delete button').attr('data-url').replace(curent_name,curent_newname));

    if(template.find('.preview').length > 0) {
        template.find('.preview a').prop('href',template.find('.preview a').prop('href').replace(curent_name_srv,curent_newname_srv)).prop('title',change_item.val())

        template.find('.preview img').prop('src',template.find('.preview img').prop('src').replace(curent_name_srv,curent_newname_srv))
    }

    change_item.remove();
}

function is_filename_allowed(name) {
// /[^a-zA-Z0-9._-]/g.test(name);
    if(name.search(/[^a-zA-Z0-9._-]/) != -1) {
//$("#out").html($("#out").html()+"<br />nicht erlaubt="+name);
        return false;
    }
    return true;

}

$(function () {
//    'use strict';

    $('input[type="file"]').prop('multiple','multiple');

    $('.fileupload').each(function () {
        if($(this).parents('#menu-fix').length > 0) {
            // wie continue
            return true;
        }
        var that = this;
        $(this).fileupload({
            dropZone: $(this)
        });
//$("#out").html($("#out").html()+"<br />get="+URL_BASE+ADMIN_DIR_NAME+"/index.php?"+$(this).serialize());
        $.get(URL_BASE+ADMIN_DIR_NAME+"/index.php?"+$(this).serialize(), function (result) {
            var tmpdata = $("<span>"+result+"</span>");
//$("#out").html($("#out").html()+"<br />tmpdata="+tmpdata.find("#json-data").text());
            if(tmpdata.find("#json-data").length > 0) {
                result = jQuery.parseJSON(tmpdata.find("#json-data").text());
            } else
                result = "";
            if (result && result.length) {
                $(that).fileupload('option', 'done')
                    .call(that, null, {result: result});
            }
        });
    });

    $('.fileupload .preview a:not([target^=_blank])').live('click', function (e) {
        e.preventDefault();
        if(is_img(this.href))
            dialog_img_preview(this.href);
        else
            dialog_iframe_preview(this.href);
    });

    $('.fu-rename-file').live('dblclick', function (e) {
        e.preventDefault();
        $(this).addClass('fu-nosearch').hide(0).after("<input class=\"fu-rename-in-file\" type=\"text\" value=\""+$(this).text()+"\">");
        $(this).siblings('.fu-rename-in-file').focus();
    });

    $('.fu-rename-in-file').live('keydown', function (e) {
        if(e.which == 13) { // enter
            e.preventDefault();
            var new_name = $(this).val();
            var name_twice = false;
            $(this).closest('.fileupload').find('.fu-rename-file:not(.fu-nosearch)').each(function(){
                if(new_name == $(this).text())
                    name_twice = true;
            });
            if(name_twice) {
                dialog_open("error_messages","<b>Namen gibts schonn</b>");
            } else {
                if(new_name == $(this).siblings('.fu-rename-file').text()) {
                    $(this).siblings('.fu-rename-file').removeClass('fu-nosearch').show(0);
                    $(this).remove();
//$("#out").html($("#out").html()+"<br />nichts geändert raus hier");
                    return false;
                }
                if(!is_filename_allowed(new_name)) {
                    dialog_open("error_messages","<b>Namen hat unerlaubte Sonderzeichen</b>");
                    return false;
                }
//$("#out").html($("#out").html()+"<br />erlaubt="+new_name);
                send_item_status = "file_rename";
                var para = "newfile="+new_name+"&orgfile="+$(this).siblings('.fu-rename-file').text()+"&curent_dir="+rawurlencode_js($(this).closest('.fileupload').find('input[name="curent_dir"]').val());
                send_data(para,$(this));
//$("#out").html($("#out").html()+"<br />curent_dir="+$(this).closest('.fileupload').find('input[name="curent_dir"]').val());
            }
        } else if(e.which == 27) { // esc
            e.preventDefault();
            $(this).siblings('.fu-rename-file').removeClass('fu-nosearch').show(0);
            $(this).remove();
        }
    });

    $('.fu-subtitle').live('dblclick', function (e) {
        e.preventDefault();
        $(this).hide(0).after("<input class=\"fu-subtitle-in\" type=\"text\">");
        $(this).siblings('.fu-subtitle-in').val($(this).text()).focus();
    });

    $('.fu-subtitle-in').live('keydown', function (e) {
        if(e.which == 13) { // enter
            e.preventDefault();
            send_item_status = "gallery_subtitle";
//$("#out").html($("#out").html()+"<br>"+rawurlencode_js($(this).val()));
            var para = "subtitle="+$(this).val()+
                "&curent_dir="+rawurlencode_js($(this).closest('.fileupload').find('input[name="curent_dir"]').val())+
                "&file="+$(this).closest('.template-download').find('.fu-rename-file').text();
            send_data(para,$(this));
        } else if(e.which == 27) { // esc
            e.preventDefault();
            $(this).siblings('.fu-subtitle').show(0);
            $(this).remove();
        }
    });

});
