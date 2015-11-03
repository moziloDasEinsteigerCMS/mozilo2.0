
var edit_handler = function(event) {
    event.preventDefault();
    var that = $(this);
    if(that.hasClass("ui-state-disabled"))
        return false;

    $('#js-editor-toolbar').css("display","block");
    if(that.hasClass("js-css")) {
        $('#colordiv-editor').append($('#js-color-menu'));
        if($('#select-mode').val() != "text") {
            $('#select-mode option:selected').attr('selected',false);
            $('#select-mode option[value="css"]').attr('selected',true);
        }
        $('#js-editor-toolbar').css("display","none");
    }
    if(that.hasClass("js-html")) {
        $('#colordiv-mozilo').append($('#js-color-menu'));
        if($('#select-mode').val() != "text") {
            $('#select-mode option:selected').attr('selected',false);
            $('#select-mode option[value="html"]').attr('selected',true);
        }
    }

    editor_session.setMode("ace/mode/"+$('#select-mode').val());

    // wenn sich im FileUpload was geändert hat deshalb hollen wir immer die selectbox template
    send_data("templateselectbox=true",$('select[name="template_css"]'));

    dialog_editor.data("send_object",false)
        .dialog({
            title: mozilo_lang["template_title_editor"]+" "+that.closest(".js-tools-show-hide").find(".js-filename").text(),
            width: $(".mo-td-content-width").eq(0).width(),
            height: (parseInt($(window).height()) - dialogMaxheightOffset)});

    editor_file = "configtemplate=true&templatefile="+that.next('.js-edit-file-pfad').text();
    send_editor_data(editor_file,false);
};

$(function() {
    $("body").on("click",".js-edit-template", edit_handler);

    $("body").on("click","#js-template-manage [type=radio]", function() {
        $("#js-template-install-file").val("");
        $("#js-template-manage").submit();
    });

    $("body").on("change","#js-template-install-file", function(event) {
        if(!checkIsZipFile($("#js-template-install-file")))
            dialog_open("error_messages",returnMessage(false, mozilo_lang["error_zip_nozip"]));
    });

    $("body").on("click","#js-template-install-submit", function(event) {
        if(checkIsZipFile($("#js-template-install-file")))
            return true;
        if($('select[name="template-install-select"] option:selected').val() != "") {
            return true;
        }
        event.preventDefault();
        dialog_open("error_messages",returnMessage(false, mozilo_lang["error_zip_nozip"]));
    });

    $("body").on("click","#js-template-del-submit", function(event) {
        event.preventDefault();
        if($("#js-template-manage [type=checkbox]:checked").length > 0) {
            $("#js-template-manage [type=checkbox]:checked").each(function(){
                $("<span class=\"mo-bold\">"+rawurldecode_js($(this).val())+"<\/span><br \/>").appendTo(dialog_multi);
            });
            dialog_multi.dialog({
                title: mozilo_lang["dialog_title_delete"],
                buttons: [{
                    text: mozilo_lang["yes"],
                    click: function() {
                        var form_manage = $("#js-template-manage");
                        $("<input type=\"hidden\" name=\"template-all-del\" value=\"true\" \/>").appendTo(form_manage);
                        form_manage.submit();
                    }
                },{
                    text: mozilo_lang["no"],
                    click: function() {
                        dialog_multi.dialog("close");
                    }
                }]});
            dialog_multi.dialog("open");
        }
    });

});
