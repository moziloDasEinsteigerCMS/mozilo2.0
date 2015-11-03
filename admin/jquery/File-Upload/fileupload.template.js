
$(function () {
    'use strict';

$('.fileupload').fileupload({
            prev_img: false,
    uploadTemplate: function (o) {
        var rows = $();
        $.each(o.files, function (index, file) {

            var row = $('<li class="template-upload fade ui-widget-content ui-corner-all">'+
                '<table width="100%" cellspacing="0" border="0" cellpadding="0">'+
                '<tbody>'+
                '<tr><td colspan="6" class="error"><\/td><\/tr>'+
                '<tr>'+
                '<td class="preview" width="1%"><\/td>'+
                '<td class="name mo-pading-l-r"><\/td>'+
                '<td class="size mo-pading-l-r" width="1%"><\/td>'+
                '<td class="mo-pading-l-r" width="1%">'+
                 '<div class="progress progress-success progress-striped active"><div class="bar" style="width:0%;"><\/div><\/div>'+
               '<\/td>'+
                '<td class="start" width="1%">'+
                    '<button class="fu-img-button mo-icons-icon mo-icons-save">&nbsp;<\/button>'+
                '<\/td>'+
                '<td class="cancel" width="1%">'+
                    '<button class="fu-img-button mo-icons-icon mo-icons-stop">&nbsp;<\/button>'+
                '<\/td>'+
                '<\/tr><\/tbody><\/table>'+
                '<\/li>');
                if(!o.options.prev_img) {
                    row.find('.preview').append('<a><img class="fu-ext-imgs fu-ext-'+o.mimeType(file)+'" src="'+ICON_URL_SLICE+'"><\/a>');
                    row.find('a').prop('title', file.name);
                }
            row.find('.name').text(file.name);
            row.find('.size').text(o.formatFileSize(file.size));
            if (file.error) {
                row.addClass('ui-state-error');
                row.find('.error').html('Error: '+(locale.fileupload.errors[file.error] || file.error));
            }
            rows = rows.add(row);
        });
        return rows;
    },
    downloadTemplate: function (o) {
        var rows = $();
        $.each(o.files, function (index, file) {

            var row = $('<li class="template-download fade ui-widget-content ui-corner-all">'+
                '<table width="100%" cellspacing="0" border="0" cellpadding="0">'+
                '<tbody>'+
                    '<tr><td colspan="4" class="error"><\/td><\/tr>'+
                    '<tr>'+
                    '<td class="preview" width="1%"><\/td>'+
                    '<td class="name mo-pading-l-r"><span class="fu-rename-file"><\/span><\/td>'+
                    '<td class="size mo-pading-l-r mo-nowrap" width="1%"><\/td>'+
                '<td class="delete" width="1%">'+
                    '<button class="fu-img-button mo-icons-icon mo-icons-delete">&nbsp;<\/button>'+
                '<input type="checkbox" name="delete" value="1" \/><\/td>'+
                '<\/tr><\/tbody><\/table>'+
                '<\/li>');
            row.find('.size').text(o.formatFileSize(file.size));
            if (file.error) {
                row.addClass('ui-state-error');
                row.find('.error').html('Error: '+(locale.fileupload.errors[file.error] || file.error));
                row.find('.delete button').addClass('js-nodialog');
            } else {
                row.find('.name span').text(file.name);

                if(!o.options.prev_img) {
                    row.find('.preview').append('<a><img class="fu-ext-imgs fu-ext-'+o.mimeType(file)+'" src="'+ICON_URL_SLICE+'"><\/a>');
                    row.find('a').prop('title', file.name);
                }
                row.find('a').prop('href', file.url);
                row.find('.delete button')
                    .attr('data-type', file.delete_type)
                    .attr('data-url', file.delete_url);
            }
            rows = rows.add(row);
        });
        return rows;
    }
});


});
