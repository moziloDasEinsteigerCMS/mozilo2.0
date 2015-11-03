var only_one_receive_page = false;
var only_one_drop_page = false;
var only_one_drop_cat = false;
var send_true = true;

var delete_handler = function (event) {
    var that = $(this);
    if(that.hasClass("ui-state-disabled"))
        return false;
    var li_cat_page = find_li_cat_page(that),
        this_table = li_cat_page.find("table").eq(0),
        dialog_text = "<div id=\"dialog-del\"><ul><li><b>" + this_table.find(".js-normal-in-name").text() + "<\/b><br \/>";
    if(!is_page(this_table.find(".js-in-cat-page").val()) && !get_target(this_table.find(".js-in-cat-page").val())) {
        if(li_cat_page.find(".js-li-page").length > 0) {
            dialog_text += "<br \/><b>" + mozilo_lang["pages"] + ":<\/b><br \/><ul>";
            li_cat_page.find(".js-li-page").each(function(index) {
                dialog_text += "<li>" + $(this).find(".js-page-name").text() + "<\/li>";
            });
            dialog_text += "<\/ul>";
        }
        if(this_table.find(".js-cat-files").val() != "false") {
            dialog_text += "<br \/><b>" + mozilo_lang["files"] + ":<\/b><br \/><ul>";
            var files = this_table.find(".js-cat-files").val().split("-#-");
            for (var i = 0; i < files.length; ++i)
                dialog_text += "<li>" + files[i] + "<\/li>";
            dialog_text += "<\/ul>";
        }
    }
    dialog_text += "<\/li><\/ul><\/div>";

    send_item_status = "cat_page_del";

    dialog_multi.data("del_object",li_cat_page);
    dialog_open("delete",dialog_text);
};

var in_name_enter_handler = function(event) {
    var that = $(this);
    if(event.which == 13) { // enter
        var li_cat_page = find_li_cat_page(that);
        if(li_cat_page) {
            li_cat_page.find("table").eq(0).find(".js-edit-rename").trigger("click");
        }
        return false;
    } else if(event.which == 27) { // esc
        var li_cat_page = find_li_cat_page(that);
        if(li_cat_page) {
            li_cat_page.find("table").eq(0).find(".js-edit-rename").data("rename-mode","false");
            change_to_normal_mode(li_cat_page.find("table").eq(0));
        }
        return false;
    }
};

var rename_handler = function (event) {
    var that = $(this),
        li_cat_page = find_li_cat_page(that);
    if(!li_cat_page)
        return false;
    var this_table = li_cat_page.find("table").eq(0);
    // zu Rename mode wechseln
    if(that.data("rename-mode") == "false") {
        that.data("rename-mode","true");
        change_to_rename_mode(this_table);
    // zu Normal mode wechseln
    } else if(that.data("rename-mode") == "true") {
        // nur wenn der name korect und nicht doppelt ist
        this_table.find(".js-in-cat-page").addClass("no-free-name");
        // wir sind bei pages
        if(is_page(this_table.find(".js-in-cat-page").attr("name"))) {
            var curent_cat = get_cat(this_table.find(".js-in-cat-page").attr("name"))+"][",
                search_free_name = that.parents(".js-li-cat").find(".js-in-page:not(.no-free-name)");
        // wir sind bei cats
        } else {
            var curent_cat = "",
                search_free_name = $(".js-ul-cats").find(".js-in-cat:not(.no-free-name)");
        }
        var free_name = rawurlencode_js(this_table.find(".js-in-name").val());
        if(free_name.length < 1) {
            dialog_multi.data("focus",this_table.find(".js-in-name"));
            dialog_open("error_messages",returnMessage(false,mozilo_lang["error_input_empty"]));
            return;
        }
        // hat sich überhaubt was geändert wenn nein gleich raus hier
        var new_name = curent_cat+free_name;
        if(typeof this_table.find(".js-in-link").val() != "undefined") {
            new_name += this_table.find(".js-in-radio:checked").val();
            new_name += rawurlencode_js(this_table.find(".js-in-link").val());
            new_name += EXT_LINK;
        } else if(typeof this_table.find(".js-in-radio:checked").val() != "undefined") {
            new_name += this_table.find(".js-in-radio:checked").val();
        }
        if(new_name == make_clean_cat_page_name(this_table.find(".js-in-cat-page").val())) {
            that.data("rename-mode","false");
            change_to_normal_mode(this_table);
            return false;
        }

        free_name = get_next_free_name(search_free_name,free_name);
        if(!free_name) {
            dialog_open("error",returnMessage(false,mozilo_lang["error_no_freename"]));
            return;
        }
        this_table.find(".js-in-cat-page").removeClass("no-free-name");
        if(free_name.match(/%23_\d+_/)) {
            this_table.find(".js-in-name").focus();
            this_table.find(".js-in-name").css("color","red");
            return false;
        }

        this_table.find(".js-in-cat-page").attr("name", "sort_array[" + new_name + "]");

        that.data("rename-mode","false");
        change_to_normal_mode(this_table);
        send_item_status = "cat_page_move";
        send_data(make_send_para_sort_array(),this_table);
    }
};

var dblclick_rename_handler = function(event) {
    var li_cat_page = find_li_cat_page($(this));
    if(li_cat_page)
        li_cat_page.find("table").eq(0).find(".js-edit-rename").trigger("click");
};

var for_page_droppable_drop = function( event, ui ) {

    if(only_one_drop_page ) {
        return;
    }
    only_one_drop_page  = true;
    if(!ui.draggable.hasClass("js-link")) {
        ui.draggable.draggable(option_page_draggable);
    }
    ui.draggable.find(".js-rename-mode-hide, .js-tools").show(0);

    ui.draggable.find(".js-edit-rename").data("rename-mode","false");

    send_item_status = "cat_page_copy";
    // nur ändern wenn es keine neue page/link ist
    if(ui.draggable.hasClass("new-page")) {
        send_item_status = "cat_page_new";
    }
    ui.draggable.removeClass("new-page");
};

var for_page_sortable_receive = function( event, ui ) {
    if(only_one_receive_page ) {
        return;
    }
    only_one_receive_page  = true;

    // for_droppable_drop wurde grad ausgeführt also brauchen wir hier nur den status ändern
    if(only_one_drop_page) {
        change_status_pages($(this));
    } else {

//!!!!!!!!! muss hier erst abgefragt werden ob send_item_status false ist??????????????
        send_item_status = "cat_page_move";

        change_status_pages(ui.item);
    }
};


var option_page_sortable = {
    scrollSensitivity: 40,
    scrollSpeed: 10,
    distance: 30,
    connectWith: ".js-ul-pages",
    handle: ".js-move-me-page",
    placeholder: "mo-in-ul-li mo-tag-height-from-icon ui-corner-all ui-state-highlight",
    receive: for_page_sortable_receive,
    start: function(event, ui) {
        send_item_status = false;
        only_one_receive_page = false;
        only_one_drop_page = false;
    },
    // in der cat aus dem die page kam den page status erneuern
    remove: function(event, ui) {
        change_status_pages($(this));
    },
    stop: function(event, ui) {
        // setzt das auf js-edit-rename
        ui.item.find(".js-in-page").addClass("no-free-name");
        var li_cat = ui.item.parents(".js-li-cat"),
            free_name = get_name(ui.item.find(".js-in-page").attr("name"));
        free_name = get_next_free_name(li_cat.find(".js-in-page:not(.no-free-name)"),free_name);
        if(!free_name) {
            dialog_open("error",returnMessage(false,mozilo_lang["error_no_freename"]));
            return;
        }
        ui.item.find(".js-in-page").removeClass("no-free-name");

        set_auto_new_name(ui.item,free_name);

        send_data(make_send_para_sort_array(),ui.item);
    }
};

var option_page_droppable = {
    accept: ".js-li-page:not(.ui-sortable-helper)",
    addClasses: false,
    drop: for_page_droppable_drop
};

var option_page_draggable = {
    connectToSortable: ".js-ul-pages",
    addClasses: false,
    helper: "clone",
    handle: ".js-copy-me-page",
    appendTo: ".catpage",
//    appendTo: "body",
    start: function(event, ui) {
        if($(this).find(".ui-state-disabled").length > 0)
            return false;
    }
};

var edit_handler = function() {
    if($(this).hasClass("ui-state-disabled"))
        return false;

    var cat_page = $(this).parents(".js-li-page").find(".js-in-page").val(),
        link_text = rawurldecode_js(get_cat(cat_page)) + "/ " + rawurldecode_js(get_page(cat_page));

    if(modrewrite == "true")
        var link_href = URL_BASE + get_cat(cat_page) + "/" + get_page(cat_page) + ".html?draft=true";
    else
        var link_href = URL_BASE + "index.php?cat=" + get_cat(cat_page) + "&page=" + get_page(cat_page) + "&draft=true";

    dialog_editor.data("send_object",false)
        .dialog({
            title: mozilo_lang["page_edit"] + " → " + "<a href=\"" + link_href.replace(/%2F/g,"/") + "\" target=\"_blank\">" + link_text + "<\/a>",
            width: $(".mo-td-content-width").eq(0).width(),
            height: (parseInt($(window).height()) - dialogMaxheightOffset)});
    editor_file = "editpage="+make_clean_cat_page_name(cat_page).replace(/\]\[/, ":");
    send_editor_data(editor_file,false);
};

$(function() {

    // Tools
    $(".js-ul-cats").on("click", ".js-edit-delete", delete_handler);
    $(".js-ul-cats").on("click", ".js-edit-rename", rename_handler);
    $(".js-ul-cats").on("dblclick", ".js-cat-name, .js-page-name",dblclick_rename_handler);

    $(".js-ul-cats").on("click", ".js-edit-page",edit_handler);

    $(".js-ul-cats").on("keydown", ".js-in-name, .js-in-link", in_name_enter_handler);

    $(".js-ul-cats .js-edit-rename").data("rename-mode","false");
    // Inhaltseiten
    // copy page
    $(".js-ul-cats .js-li-page:not(.js-link)").draggable(option_page_draggable);
    // empfangen und sortieren new, copy und move page
    $(".js-ul-cats .js-ul-pages").droppable(option_page_droppable).sortable(option_page_sortable);

    // new page
    $(".js-new-ul .js-li-page").draggable(option_page_draggable).draggable("option","handle","");

    // Kategorien
    $(".js-new-ul .js-tools, .js-new-ul div.js-rename-mode-hide, .js-new-ul .js-move-cat, .js-new-ul .js-edit-in-name").hide(0);
    $(".js-new-ul .js-li-cat").draggable({
        connectToSortable: ".js-ul-cats",
        addClasses: false,
        helper: "clone",
        appendTo: ".catpage"
//        appendTo: "body"
    });

    $(".js-ul-cats").droppable({
        addClasses: false,
        accept: ".js-li-cat:not(.ui-sortable-helper)",
        // drop = Neue Kategorie
        drop: function( event, ui ) {
// ist in jquery-ui-1.8.22 doch nicht gefixt
//$('#out').html($('#out').html()+"drop<br>");
            if(only_one_drop_cat) {
                return;
            }
            send_true = true;
            only_one_drop_cat = true;
            // no-free-name hinzufügen damit wir das beim doppelten namen suchen übergehen können
            ui.draggable.find(".js-in-cat").addClass("no-free-name");
            var free_name = get_name(ui.draggable.find(".js-in-cat").attr("name"));
            // Achtung wir übergeben alle .js-in-cat auser dem mit curent_item
            free_name = get_next_free_name($(".js-ul-cats").find(".js-in-cat:not(.no-free-name)"),free_name);
            if(!free_name) {
                send_true = false;
                ui.draggable.remove();
                dialog_open("error",returnMessage(false,mozilo_lang["error_no_freename"]));
                return;
            }

            send_item_status = "cat_page_new";
            ui.draggable.find(".no-free-name").removeClass("no-free-name");

            set_auto_new_name(ui.draggable,free_name);

            ui.draggable.find(".js-tools, .js-rename-mode-hide, .js-move-cat").show(0);
            // nur wenn es kein Link ist
            if(!get_target(ui.draggable.find(".js-in-cat").attr("name"))) {
                ui.draggable.find(".mo-li-head-tag").removeClass("mo-li-head-tag-no-ul");
                var new_ul = $("<ul class=\"js-ul-pages  mo-in-ul-ul\"><\/ul>")
                        .appendTo(ui.draggable);
                new_ul.droppable(option_page_droppable).sortable(option_page_sortable);
            } else {
                ui.draggable.find(".mo-li-head-tag").addClass("mo-li-head-tag-no-ul");
            }

            ui.draggable.find(".js-edit-rename").data("rename-mode","false");
        }
    }).sortable({
        scrollSensitivity: 40,
        scrollSpeed: 10,
        distance: 30,
        connectWith: ".js-ul-cats",
        handle: ".js-move-me-cat:not(.js-deact-filter)",
        placeholder: "mo-placeholder mo-li mo-li-head-tag mo-li-head-tag-no-ul mo-tag-height-from-icon ui-corner-all ui-state-highlight",
        start: function(event, ui) {
            only_one_drop_cat = false;
        },
        stop: function(event, ui) {
            if(send_true === false) {
                send_true = true;
                return;
            }
            // wenn false ist das nur cat sortieren
            if(!only_one_drop_cat)
                send_item_status = false;
            // hier erst das senden da erst hier die reinfolge da ist
            send_data(make_send_para_sort_array(),ui.item);
        }
    });
    $(".js-ul-cats, .js-new-ul").disableSelection();
});

