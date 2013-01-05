
var edit_handler = function(event) {
    event.preventDefault();
    var that = $(this);
    if(that.hasClass("ui-state-disabled"))
        return false;

    $('#js-editor-toolbar').css("display","block");
    if(that.hasClass("js-css")) {
        $('#colordiv-editor').append($('#js-color-menu'));
        if($('#select-mode').val() != "text") {
            $('#select-mode option:selected').attr('selected',false)
            $('#select-mode option[value="css"]').attr('selected',true)
        }
        $('#js-editor-toolbar').css("display","none");
    }
    if(that.hasClass("js-html")) {
        $('#colordiv-mozilo').append($('#js-color-menu'));
        if($('#select-mode').val() != "text") {
            $('#select-mode option:selected').attr('selected',false)
            $('#select-mode option[value="html"]').attr('selected',true)
        }
    }

    editor_session.setMode("ace/mode/"+$('#select-mode').val());

    // wenn sich im FileUpload was geändert hat deshalb hollen wir immer die selectbox template
    send_data("templateselectbox=true",$('select[name="template_css"]'));

    dialog_editor.data("send_object",false)
        .dialog({
            title: "Berabeiten "+that.closest(".js-tools-show-hide").find(".js-filename").text(),
            width: $(".mo-td-content-width").eq(0).width(),
            height: (parseInt($(window).height()) - dialogMaxheightOffset)});

    editor_file = "configtemplate=true&templatefile="+that.next('.js-edit-file-pfad').text();
    send_editor_data(editor_file,false);
}
$(function() {
    $("body").on("click",".js-edit-template", edit_handler);
});
