var parentWidget = ($.blueimpIP || $.blueimp).fileupload;
$.widget('blueimpUI.fileupload', $.blueimpUI.fileupload, {
//$.widget('blueimpUI.fileupload', parentWidget, {
        options: {

            acceptFileTypes:  mo_acceptFileTypes,

            add: function (e, data) {
//$("#out").html($("#out").html()+"<br>add");
                var that = $(this).data('fileupload'),
                    options = that.options,
                    files = data.files;
//$("#out").html($("#out").html()+"<br>add="+options.uploadTemplate.toSource());
                $(this).fileupload('resize', data).done(data, function () {
                    that._adjustMaxNumberOfFiles(-files.length);
                    data.isAdjusted = true;
                    data.files.valid = data.isValidated = that._validate(files);
                    data.context = that._renderUpload(files)
                        .appendTo(options.filesContainer)
                        .data('data', data);
//$("#out").html($("#out").html()+"<br>files="+$(data.context).html());
if(options.prev_img === true) {
    var new_width = $(options.filesContainer).siblings('.fileupload-buttonbar').find('input[name="thumbnail_max_width"]').val();
    var new_height = $(options.filesContainer).siblings('.fileupload-buttonbar').find('input[name="thumbnail_max_height"]').val();

    if(new_width.length > 0 || new_height.length > 0) {
        options.previewMaxWidth = new_width;
        options.previewMaxHeight = new_height;
    }
                    that._renderPreviews(files, data.context);
}
                    that._forceReflow(data.context);
                    that._transition(data.context).done(
                        function () {
//$("#out").html($("#out").html()+"<br>name="+$(this).find('.name').text());

var current_appendTo = $(this);
$.each($(options.filesContainer).find('.template-download'), function (index) {
    if($(this).find('.name').text() == current_appendTo.find('.name').text()) {
        var time = (new Date()).getTime();
        $(this).hide(0).attr('id','oldfile'+index+time);
        current_appendTo.addClass('ui-state-error name-twice').attr('id','newfile'+index+time);
        current_appendTo.find('.progress').prepend('<b>Überschreibe Datei<\/b>');
    }
});
//$("#out").html($("#out").html()+"<br>files=");
                            if ((that._trigger('added', e, data) !== false) &&
                                    (options.autoUpload || data.autoUpload) &&
                                    data.autoUpload !== false && data.isValidated) {
                                data.submit();
                            }
                        }
                    );
                });
            },
            // Callback for successful uploads:
            done: function (e, data) {
                var that = $(this).data('fileupload'),
                    template,
                    preview;
                if (data.context) {
                    data.context.each(function (index) {
                        var file = ($.isArray(data.result) &&
                                data.result[index]) || {error: 'emptyResult'};
                        if (file.error) {
                            that._adjustMaxNumberOfFiles(1);
                        }
                        that._transition($(this)).done(
                            function () {
                                var node = $(this);
if(node.hasClass('name-twice')) {
    $('#oldfile'+node.attr('id').substring(7)).remove();
}
                                template = that._renderDownload([file])
//                                    .css('height', node.height())
                                    .replaceAll(node);
                                that._forceReflow(template);
                                that._transition(template).done(
                                    function () {
that._changeFilesCount();
                                        data.context = $(this);
                                        that._trigger('completed', e, data);
                                    }
                                );
                            }
                        );
                    });
                } else {
                    template = that._renderDownload(data.result)
                        .appendTo(that.options.filesContainer);
                    that._forceReflow(template);
                    that._transition(template).done(
                        function () {
that._changeFilesCount();
                            data.context = $(this);
                            that._trigger('completed', e, data);
                        }
                    );
                }
            },
            // Callback for file deletion:
            destroy: function (e, data) {
                var that = $(this).data('fileupload');
                if (data.url) {
//var context = data.context;
                    $.ajax(data)
                        .success(function (result) {
                            var tmpdata = $("<span>"+result+"<\/span>");
                            if(tmpdata.find(".error").length > 0) {
                                window.location.href = "index.php?fileupload="+action_activ;
                            }
                    });
                }
                that._adjustMaxNumberOfFiles(1);
                that._transition(data.context).done(
                    function () {
                        $(this).remove();
that._changeFilesCount();
//$("#out").html($("#out").html()+"<br />destroy");
                        that._trigger('destroyed', e, data);
                    }
                );
            },
            // Callback for file rename:
            resize_all_new: function (e, data) {
                if (data.url) {
                var that = $(this).data('fileupload'),
                    context = data.context;
                    $.ajax(data)
                        .success(function (file) {
                            var tmpdata = $("<span>"+file+"<\/span>");
                            if(tmpdata.find(".error").length > 0) {
                                window.location.href = "index.php?fileupload="+action_activ;
                            } else if(tmpdata.find("#json-data").length > 0) {
                                file = jQuery.parseJSON(tmpdata.find("#json-data").text());
                            } else
                                file = "";
                            if(file.length < 1 || file.error) {
                                context.addClass('ui-state-error');
                                if(file.error)
                                    context.find('.error').html('Error: '+file.error);
                                else
                                    context.find('.error').html('Error: Unbekant keine json');
                            } else {
//$("#out").html($("#out").html() + "<br>file.pixel_w="+ file.pixel_w);
//$("#out").html($("#out").html() + "<br>context="+ context.attr('class'));
                                if(file.pixel_w && file.pixel_h) {
                                    context.find('.pixelsize span').text(file.pixel_w+" x "+file.pixel_h);
                                }
                                context.find('.size').text(that._formatFileSize(file.size));
                                if(file.thumbnail_url) {
                                    if(context.find('.preview img').length > 0)
                                        context.find('.preview img').prop('src', file.thumbnail_url+"?"+(new Date()).getTime());
                                    else {
                                        context.find('.preview').append('<a><img><\/a>')
                                        .find('img').prop('src', file.thumbnail_url+"?"+(new Date()).getTime());
                                        context.find('a').prop('title', file.name);
                                    }
                                }
                            }
                        })
                        .error(function (jqXHR, textStatus, errorThrown) {
//    dialog_open("error",errorThrown);
                            context.addClass('ui-state-error');
                            context.find('.error').html('Error: '+textStatus+', '+errorThrown);
                        })
                        .complete(function() {
//$("#out").html($("#out").html() + "<br>complete=");
                            context.addClass('in').removeClass('ui-state-disabled');
                            context.find('.delete input').removeClass('disabled').prop('checked', false);
                        });
                }
            }
    },

/**/
        _renderDownload: function (files) {
            return this._renderTemplate(
                this.options.downloadTemplate,
                files
            ).find('a[download]').each(this._enableDragToDesktop).end();
        },

        _renderTemplate: function (func, files) {
            if (!func) {
                return $();
            }
            var result = func({
                files: files,
                formatFileSize: this._formatFileSize,
mimeType: this._mimeType,
                options: this.options
            });
            if (result instanceof $) {
                return result;
            }
            return $(this.options.templatesContainer).html(result).children();
        },

        _hasError: function (file) {
            if (file.error) {
                return file.error;
            }
            // The number of added files is subtracted from
            // maxNumberOfFiles before validation, so we check if
            // maxNumberOfFiles is below 0 (instead of below 1):
            if (this.options.maxNumberOfFiles < 0) {
                return 'maxNumberOfFiles';
            }
            // Files are accepted if either the file type or the file name
            // matches against the acceptFileTypes regular expression, as
            // only browsers with support for the File API report the type:
            if(action_activ != "files") {
                if (!this.options.previewSourceFileTypes.test(file.type))
                    return 'acceptFileTypes';
            } else {
                if (this.options.acceptFileTypes.test(file.name))
                    return 'acceptFileTypes';
            }
            if (this.options.maxFileSize &&
                    file.size > this.options.maxFileSize) {
                return 'maxFileSize';
            }
            if (typeof file.size === 'number' &&
                    file.size < this.options.minFileSize) {
                return 'minFileSize';
            }
            return null;
        },

        _mimeType: function (file) {
            var ext = "none";
            if(file.name)
                ext = file.name.substring(file.name.lastIndexOf(".")+1).toLowerCase();
            var img = "none";
            if(typeof ext == "string") {
                if(ext == "png" || ext == "gif" || ext == "jpg" || ext == "jpeg" || ext == "ico") {
                    img = "img";
                } else if(ext == "doc" || ext == "odf") {
                    img = "doc";
                } else if(ext == "mpg" || ext == "mov" || ext == "flv") {
                    img = "mov";
                } else if(ext == "pdf") {
                    img = "pdf";
                } else if(ext == "txt") {
                    img = "txt";
                } else if(ext == "mp3" || ext == "mp4" || ext == "wav") {
                    img = "wav";
                } else if(ext == "zip" || ext == "gzip" || ext == "gz") {
                    img = "zip";
                } else if(ext == "iso") {
                    img = "iso";
                }
            }
            return img;
        },

        _changeFilesCount: function () {
//$("#out").html($("#out").html()+"<br />"+this.element.find('.'+this.options.downloadTemplateId).length);
            this.element.find('.files-count').text(this.element.find('.'+this.options.downloadTemplateId).length);
        },

        _deleteHandler: function (e) {
//$("#out").html($("#out").html()+"<br />class="+$(this).attr('class'));
//$("#out").html($("#out").html()+"<br />_deleteHandler");
            e.preventDefault();
            if($(this).hasClass('js-nodialog')) {
                var button = $(this);
                e.data.fileupload._trigger('destroy', e, {
                    context: button.closest('.template-download'),
                    url: button.attr('data-url'),
                    type: button.attr('data-type') || 'DELETE',
                    dataType: e.data.fileupload.options.dataType
                });
            } else {
//$("#out").html($("#out").html()+"<br />dilaog öffnen");
                dialog_multi.data("del_object",$(this));
                dialog_open("delete_file","<b>"+$(this).closest('.template-download').find('.name').text()+"<\/b>");
            }
        },

        _cancelHandler: function (e) {
//$("#out").html($("#out").html()+"<br />_cancelHandler");
            e.preventDefault();
            var template = $(this).closest('.template-upload'),
                data = template.data('data') || {};
if(template.hasClass('name-twice')) {
    $('#oldfile'+template.attr('id').substring(7)).show(0).removeAttr('id').removeClass('name-twice');
}
            if (!data.jqXHR) {
                data.errorThrown = 'abort';
                e.data.fileupload._trigger('fail', e, data);
            } else {
                data.jqXHR.abort();
            }
        },

        _resizeImgHandler: function (e) {
            e.preventDefault();
            var button = $(this),
                template = button.closest('.template-download');
            if(template.hasClass('ui-state-disabled')) return;
//$("#out").html($("#out").html()+"<br />_resizeImgHandler");
                template.addClass('ui-state-disabled').removeClass('in');
                template.find('.delete input').addClass('disabled');

            e.data.fileupload._trigger('resize_all_new', e, {
                context: template,
                url: 'index.php?file='+template.find('.name').text()+'&'+button.closest('.fileupload').serialize()+'&resize=true',
                type: 'POST',
                isValidated: true,
                dataType: e.data.fileupload.options.dataType
            });


//  .siblings('.resize').click();
        },

        _initButtonBarEventHandlers: function () {
//$("#out").html($("#out").html()+"<br />_initButtonBarEventHandlers");
            var fileUploadButtonBar = this.element.find('.fileupload-buttonbar'),
                filesList = this.options.filesContainer,
                ns = this.options.namespace;
            fileUploadButtonBar.find('.start')
                .bind('click.' + ns, function (e) {
                    e.preventDefault();
//$("#out").html($("#out").html()+"<br />start");
                    filesList.find('.start button').click();
                });
            fileUploadButtonBar.find('.cancel')
                .bind('click.' + ns, function (e) {
                    e.preventDefault();
//$("#out").html($("#out").html()+"<br />cancel");
                    filesList.find('.cancel button').click();
                });
            fileUploadButtonBar.find('.delete')
                .bind('click.' + ns, function (e) {
                    e.preventDefault();
//$("#out").html($("#out").html()+"<br />delete="+e.which);
                    var dialog = false;
                    dialog_multi.data("del_object",[filesList,fileUploadButtonBar]);
                    var dialog_text = "<div id=\"dialog-del\"><ul>";
                    filesList.find('.delete input:checked').each(function() {//.del.delete ete 
//$("#out").html($("#out").html()+"<br />file="+$(this).closest('.template-download').find('.name').text());
                        dialog = true;
                        dialog_text += "<li><b>"+$(this).closest('.template-download').find('.name').text()+"<\/b><\/li>";
                    });
                    dialog_text += "<\/ul><\/div>";
                    if(dialog)
                        dialog_open("delete_files",dialog_text);
//return false;
                });
            fileUploadButtonBar.find('.resize')
                .bind('click.' + ns, function (e) {
//$("#out").html($("#out").html()+"<br />resize");
                    e.preventDefault();
//$("#out").html($("#out").html()+"<br />resize");
                    filesList.find('.delete input:checked')
                        .siblings('.resize').click();
                    fileUploadButtonBar.find('.toggle')
                        .prop('checked', false);
                });
            fileUploadButtonBar.find('.toggle')
                .bind('change.' + ns, function (e) {
//$("#out").html($("#out").html()+"<br />toggle");
                    filesList.find('.delete input:not(.disabled)').prop(
                        'checked',
                        $(this).is(':checked')
                    );
                });
        },

        _initEventHandlers: function () {
            parentWidget.prototype._initEventHandlers.call(this);
            var eventData = {fileupload: this};
            this.options.filesContainer
                .delegate(
                    '.start button',
                    'click.' + this.options.namespace,
                    eventData,
                    this._startHandler
                )
                .delegate(
                    '.cancel button',
                    'click.' + this.options.namespace,
                    eventData,
                    this._cancelHandler
                )
                .delegate(
                    '.delete button',
                    'click.' + this.options.namespace,
                    eventData,
                    this._deleteHandler
                )
                .delegate(
                    '.resize',
                    'click.' + this.options.namespace,
                    eventData,
                    this._resizeImgHandler
                );
            this._initButtonBarEventHandlers();
        }

});