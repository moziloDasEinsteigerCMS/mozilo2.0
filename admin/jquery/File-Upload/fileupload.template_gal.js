
var parentWidget = ($.blueimpIP || $.blueimp).fileupload;
$.widget('blueimpUI.fileupload', $.blueimpUI.fileupload, {

        options: {

//$(function () {
//    'use strict';

//$('.fileupload').fileupload({
            prev_img: true,
    uploadTemplate: function (o) {
//$("#out").html($("#out").html()+"<br>files=");
        var rows = $();
        $.each(o.files, function (index, file) {
//$("#out").html($("#out").html()+"<br>files=0");

            var row = $('<li class="template-upload fade file-item ui-widget ui-widget-content ui-corner-all">' +
                '<table width="100%" cellspacing="0" border="0" cellpadding="0">' +
                '<tbody>' +
                    '<tr>' +
                        '<td colspan="4" class="error mo-pading-l-r"><\/td>' +
                    '<\/tr>' +
                    '<tr>' +
                        '<td rowspan="2" width="1%" class="preview"><span class="fade"><\/span><\/td>' +
                        '<td class="size mo-pading-l-r" width="1%"><\/td>' +
                        '<td class="mo-pading-l-r" width="93%">'+
                            '<div class="progress progress-success progress-striped active"><div class="bar" style="width:0%;"><\/div><\/div>'+
                        '<\/td>' +
                        '<td rowspan="2" width="1%" class="mo-nowrap">' +
                            '<span class="start">'+
                                '<button class="fu-img-button mo-icons-icon mo-icons-save">&nbsp;<\/button>'+
                            '<\/span>'+
                            '<span class="cancel">'+
                                '<button class="fu-img-button mo-icons-icon mo-icons-stop">&nbsp;<\/button>'+
                            '<\/span>'+
                        '<\/td>' +
                    '<\/tr>' +
                    '<tr>' +
                        '<td colspan="2" class="name mo-pading-l-r"><\/td>' +
                    '<\/tr>' +
                '<\/tbody>' +
                '<\/table>' +
            '<\/li>');
//$("#out").html($("#out").html()+"<br>files=1");
            row.find('.name').text(file.name);
            row.find('.size').text(o.formatFileSize(file.size));
            if (file.error) {
                row.addClass('ui-state-error');
                row.find('.error').html('Error: '+(locale.fileupload.errors[file.error] || file.error));
            }
//$("#out").html($("#out").html()+"<br>files=2");
            rows = rows.add(row);
        });
//$("#out").html($("#out").html()+"<br>files=3");
        return rows;
    },
    downloadTemplate: function (o) {
        var rows = $();
        var new_width = $(o.options.filesContainer).siblings('.fileupload-buttonbar').find('input[name="new_width"]');
        var new_height = $(o.options.filesContainer).siblings('.fileupload-buttonbar').find('input[name="new_height"]');

        $.each(o.files, function (index, file) {

            var row = $('<li class="template-download fade file-item ui-widget ui-widget-content ui-corner-all">' +
                '<table width="100%" cellspacing="0" border="0" cellpadding="0">' +
                '<tbody>' +
                    '<tr>' +
                        '<td colspan="4" class="error"><\/td>' +
                    '<\/tr>' +
                    '<tr>' +
                        '<td rowspan="3" width="1%" class="preview"><\/td>' +
                        '<td class="size mo-pading-l-r" width="1%"><\/td>' +
                        '<td class="pixelsize mo-pading-l-r" width="93%"><span><\/span> '+mozilo_lang["pixels"]+'<\/td>' +
                        '<td rowspan="3" class="delete" width="1%">' +
                        '<button class="fu-img-button mo-icons-icon mo-icons-delete">&nbsp;<\/button>'+
                       '<img class="fu-img-button resize mo-icons-icon mo-icons-img-scale" src="'+ICON_URL_SLICE+'" alt="img-scale" \/>'+
                        '<input type="checkbox" name="delete" value="1" \/><\/td>'+
                    '<\/tr>' +
                    '<tr>' +
                        '<td class="subtitle-lang mo-pading-l-r">'+mozilo_lang["gallery_text_subtitle"]+'<\/td>' +
                        '<td class="subtitle mo-pading-l-r"><span class="fu-subtitle"><\/span><\/td>' +// style="border:1px solid #ff0000;"
                    '<\/tr>' +
                    '<tr>' +
                        '<td colspan="2" class="name mo-pading-l-r"><span class="fu-rename-file"><\/span><\/td>' +
                    '<\/tr>' +
                '<\/tbody>' +
                '<\/table>' +
            '<\/li>');


            if(file.pixel_w && file.pixel_h) {
                row.find('.pixelsize span').text(file.pixel_w+" x "+file.pixel_h);
                if($('input[name="new_global_width"]').val() == "auto" && new_width.val() < file.pixel_w)
                    new_width.val(file.pixel_w);
                if($('input[name="new_global_height"]').val() == "auto" && new_height.val() < file.pixel_h)
                    new_height.val(file.pixel_h);
           } else
                row.find('.pixelsize').html("");
            if(file.subtitle)
                row.find('.subtitle span').text(rawurldecode_js(file.subtitle));
            else  {
                row.find('.subtitle span').addClass('fu-empty');
                if(typeof file.subtitle == "undefined")
                    row.find('.subtitle-lang').text("");
            }

            row.find('.size').text(o.formatFileSize(file.size));
            if (file.error) {
                row.find('.name').text(file.name);
                row.addClass('ui-state-error');
                row.find('.error').html('Error: '+(locale.fileupload.errors[file.error] || file.error));
                row.find('.delete button').addClass('js-nodialog');
            } else {
                row.find('.name span').text(file.name);
                if (file.thumbnail_url) {
                    row.find('.preview').append('<a><img><\/a>')
                        .find('img').prop('src', file.thumbnail_url+"?"+(new Date()).getTime());
                    row.find('a').prop('title', file.name);
                }
                row.find('a').prop('href', file.url);
                row.find('.delete button')
                    .attr('data-type', file.delete_type)
                    .attr('data-url', file.delete_url);
            }

/*
            row.find('.size').text(o.formatFileSize(file.size));
            if (file.error) {
                row.addClass('ui-state-error');
                row.find('.error').html('Error: '+(locale.fileupload.errors[file.error] || file.error));
            } else {
                row.find('.name span').text(file.name);

                if(!o.options.prev_img) {
                    row.find('.preview').append('<a><img></a>')
                        .find('img').prop('src', o.mimeType(file));
                    row.find('a').prop('title', file.name);
                }
                row.find('a').prop('href', file.url);
                row.find('.delete button')
                    .attr('data-type', file.delete_type)
                    .attr('data-url', file.delete_url);
            }*/
            rows = rows.add(row);
        });
        return rows;
    }
}
});


//});
