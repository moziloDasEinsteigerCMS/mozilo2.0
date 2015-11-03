var only_one_drop_cat = false;
var send_true = true;
var user_para = "&chancegallery=true";

var delete_handler = function (event) {
    var that = $(this);
    if(that.hasClass("ui-state-disabled"))
        return false;
    var this_li = that.parents(".js-file-dir");

    var dialog_text = "<b>" + this_li.find(".js-gallery-name").text() + "<\/b><br \/>" + mozilo_lang["gallery_delete_confirm"];

    send_item_status = "gallery_del";
    dialog_multi.data("del_object",this_li);
    dialog_open("gallery_delete",dialog_text);
};

var rename_handler = function (event) {
    if($(this).hasClass("ui-state-disabled"))
        return false;
    var tmpl = $(this).closest('.fileupload');
    if(tmpl.find('.in-gallery-new-name').length > 0) {
        var e = jQuery.Event("keydown", { which: 13 });
        $(tmpl.find('.in-gallery-new-name')).trigger(e);
    } else
        change_to_rename_mode(tmpl);
};

var in_size_enter_handler = function(event) {
    if(event.which == 13) { // enter
        event.preventDefault();
        send_item_status = "gallery_size";
        send_data(user_para,$(this).closest('li').find('input'));
    }
};

var in_name_enter_handler = function(event) {
    var that = $(this);
    if(event.which == 13) { // enter
        event.preventDefault();
        // name hat sich nicht geändert
        if(that.val() == that.siblings('.js-gallery-name').text()) {
            var name_item = that.siblings('.js-gallery-name');
            that.siblings('.js-gallery-name').show(0);
            that.closest('.fileupload').find('.js-toggle, .js-edit-delete').removeClass('ui-state-disabled');
            that.remove();
            return;
        }
        if(is_name_twice($('.js-gallery .js-gallery-name:visible'),rawurlencode_js(that.val()))) {
            dialog_multi.data("focus",that);
            dialog_open("error_messages",returnMessage(false,mozilo_lang["error_exists_file_dir"]));
        } else {
            send_item_status = "gallery_rename";
            send_data(user_para,that.closest('.fileupload'));
        }
    } else if(event.which == 27) { // esc
        event.preventDefault();
        that.closest('.fileupload').find('.js-toggle, .js-edit-delete').removeClass('ui-state-disabled');
        that.siblings('.js-gallery-name').show(0);
        that.remove();
    }
};


$(function () {
    'use strict';

    $(".js-gallery").on("click",".js-edit-delete",delete_handler);
    $(".js-gallery").on("dblclick",".js-gallery-name",rename_handler);//dblclick_rename_handler
    $(".js-gallery").on("click",".js-rename-file",rename_handler);
    $(".js-gallery").on("keydown",".in-gallery-new-name", in_name_enter_handler);

    $('input[name="new_global_width"], input[name="new_global_height"], input[name="thumbnail_global_max_width"], input[name="thumbnail_global_max_height"]').on("keydown", in_size_enter_handler);

    $("#menu-fix .js-file-dir").draggable({
        connectToSortable: ".js-gallery",
        addClasses: false,
        helper: "clone",
        appendTo: "body"
    });

    $(".js-gallery").droppable({
        addClasses: false,
        accept: ".js-file-dir:not(.ui-sortable-helper)",
        // drop = Neue Kategorie
        drop: function( event, ui ) {
            if(only_one_drop_cat) {
                return;
            }
            send_true = true;
            only_one_drop_cat = true;
            ui.draggable.find(".mo-hidden").removeClass("mo-hidden");
            // no-free-name hinzufügen damit wir das beim doppelten namen suchen übergehen können
            ui.draggable.find('.js-gallery-name').addClass("no-free-name");
            var free_name = ui.draggable.find('.js-gallery-name').text();
            // Achtung wir übergeben alle .js-gallery auser dem mit curent_item
            free_name = get_next_free_name($(".js-gallery").find('.js-gallery-name:not(.no-free-name)'),rawurlencode_js(free_name));
            if(!free_name) {
                send_true = false;
                ui.draggable.remove();
                dialog_open("error",returnMessage(false,mozilo_lang["error_no_freename"]));
                return;
            }
            ui.draggable.find(".no-free-name").text(rawurldecode_js(free_name)).removeClass("no-free-name");
            ui.draggable.find('input[name="curent_dir"]').val(free_name);
        }
    }).sortable({
        scrollSensitivity: 40,
        scrollSpeed: 10,
        distance: 30,
        cancel: ".js-file-dir",
        placeholder: "mo-placeholder mo-li mo-li-head-tag mo-li-head-tag-no-ul mo-tag-height-from-icon ui-corner-all ui-state-highlight",
        start: function(event, ui) { 
            only_one_drop_cat = false;
        },
        stop: function(event, ui) {
            if(send_true === false) {
                send_true = true;
                return;
            }
            send_item_status = "gallery_new";
            // hier erst das senden da erst hier die reinfolge da ist
            send_data(user_para,ui.item);
        }
    });
});

