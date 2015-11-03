//!!!!!!!! die returns mussen geprüft werden

// ACHTUNG der .lnk.php muss als letztes stehen
var ext_array = Array(EXT_PAGE,EXT_HIDDEN,EXT_DRAFT,EXT_LINK);
var target_array = Array("-_blank-","-_self-");

var send_object = false;
var send_item_status = false;

function is_name_twice(in_cat_page,name) {
    var name_test = false;
    in_cat_page.each(function(index) {
        if(name == get_name($(this).attr("name"))) {
            name_test = true;
        }
    });
    return name_test;
}

function get_next_number_name(name) {
    var is_next_copy = name.match(/%23_\d+_/);
    if(is_next_copy) {
        var next_copy_nummber = is_next_copy[is_next_copy.length - 1].substring(3).match(/\d+/);
        next_copy_nummber = parseInt(next_copy_nummber) + 1;
        name = name.replace(is_next_copy,"%23_" + next_copy_nummber + "_");
        return name;
    } else {
        name = "%23_1_" + name;
        return name;
    }
    return false;
}

function get_next_free_name(in_cat_page,name) {
    // name ist lehr also gleich raus hier sowas gibts nicht :-)
    if(name.length < 1) {
        return false;
    }
    // wens ein name ist mit copy syntax
    var is_copy_name = name.match(/%23_\d+_/);
    if(is_copy_name) {
        // copy syntax aus name entfernen
        is_copy_name = name.substring(is_copy_name[is_copy_name.length - 1].length);
        // namen gibts nicht dann könne wir den benutzen
        if(!is_name_twice(in_cat_page,is_copy_name)) {
            return is_copy_name;
        }
    }
    // namen gibts nicht dann könne wir den benutzen
    if(!is_name_twice(in_cat_page,name)) {
        return name;
    // namen gibts also copy syntax solange mit laufender nummer ersetzen bis es in nicht gibt
    } else {
        while(is_name_twice(in_cat_page,name)) {
            name = get_next_number_name(name);
            if(name == false) {
                return false;
            }
        }
        return name;
    }
    return false;
}

function change_status_pages(this_li) {
    var item_li_cat = this_li.parents(".js-li-cat"),
        item_status = item_li_cat.find("table").eq(0).find(".js-status");
    if(item_li_cat.length == 1) {
        item_status.text(item_li_cat.find(".js-li-page").filter(":visible").length);
    }
};

function make_clean_cat_page_name(cat_page) {
    if(cat_page.substring(0,11) == "sort_array[")
        cat_page = cat_page.substring(10);
    if(cat_page.charAt(0) == "[")
        cat_page = cat_page.substring(1);
    if(cat_page.charAt(cat_page.length - 1) == "]")
        cat_page = cat_page.substring(0,cat_page.length - 1);
    return cat_page;
}

function is_page(cat_page) {
    if(cat_page.match(/\]\[/)) {
        return true;
    }
    return false;
}

function get_name(cat_page) {
    if(is_page(cat_page))
        return get_page(cat_page);
    return get_cat(cat_page);
}

function get_cat(cat_page) {
    cat_page = make_clean_cat_page_name(cat_page);
    if(cat_page.match(/\]\[/)) {
        cat_page = cat_page.split("][");
        return cat_page[0];
    }
    var target = get_target(cat_page);
    if(!target)
        return cat_page;
    if(target) {
        cat_page = cat_page.split(target);
        return cat_page[0];
    }
    return false;
}

function get_page(cat_page) {
    cat_page = make_clean_cat_page_name(cat_page);
    var ext = get_ext(cat_page);
    if(!ext)
        return false;
    cat_page = cat_page.split("][");
    if(typeof cat_page[1] == "undefined")
        return false;
    var target = get_target(cat_page[1]);
    if(!target)
        return cat_page[1].replace(ext,"");
    if(target) {
        var tmp = cat_page[1].split(target);
        if(typeof tmp[0] == "undefined")
            return false;
        return tmp[0];
    }
    return false;
}

function get_link(cat_page) {
    cat_page = make_clean_cat_page_name(cat_page);
    var target = get_target(cat_page);
    if(!target)
        return false;
    if(target) {
        var tmp = cat_page.split(target);
        if(typeof tmp[1] == "undefined")
            return false;
        var ext = get_ext(cat_page);
        if(!ext)
            return false;
        return tmp[1].replace(ext,"");
    }
    return false;
}

function get_target(cat_page) {
    for(var i = 0; i < target_array.length; i++) {
        if(cat_page.match(target_array[i])) {
            return target_array[i];
        }
    }
    return false;
}

function get_ext(cat_page) {
    cat_page = make_clean_cat_page_name(cat_page);
    for(var i = 0; i < ext_array.length; i++) {
        if(ext_array[i] == cat_page.substring(cat_page.length - EXT_LENGTH)) {
            return ext_array[i];
        }
    }
    return false;
}

function make_input_name(cat_page) {
    var name = get_page(cat_page);
    if(name) {
        return $("<input type=\"text\" class=\"js-in-name js-make-input\" \/>").val(rawurldecode_js(name));
    }
    name = get_cat(cat_page);
    if(name)
        return $("<input type=\"text\" class=\"js-in-name js-make-input\" \/>").val(rawurldecode_js(name));
    return "";
}

function make_input_link(cat_page) {
    var link = get_link(cat_page);
    if(typeof link == "boolean") {
        return "";
    }
    return mozilo_lang["url_adress"] + " <input type=\"text\" value=\""+rawurldecode_js(link)+"\" class=\"js-in-link js-make-input\" \/>";
}

function make_input_target(cat_page) {
    var curent_target = get_target(cat_page);
    if(!curent_target)
        return "";
    var inputs = "<b>" + mozilo_lang["target"] + "<\/b>" + " <form>";
    var for_id = new Date();
    for_id = for_id.getTime();
    for(var i = 0; i < target_array.length; i++) {
        inputs += "<input id=\"target" + for_id + i + "\" name=\"radio\" type=\"radio\" value=\""+target_array[i]+"\" class=\"js-in-radio js-make-input\"";
        if(target_array[i] == curent_target) {
            inputs += " checked=\"checked\"";
        }
        inputs += " \/>";
        inputs += "<label for=\"target" + for_id + i + "\">" + mozilo_lang[target_array[i].replace(/-|_/g,"")] + "<\/label>";
    }
    return inputs + "<\/form>";
}

function make_input_ext(cat_page) {
    var curent_ext = get_ext(cat_page);
    if(!curent_ext || curent_ext == EXT_LINK)
        return "";
    var inputs = "<b>" + mozilo_lang["page_status"] + "<\/b>" + ": <form>",
        for_id = new Date();
    for_id = for_id.getTime();
    for(var i = 0; i < ext_array.length - 1; i++) {
        inputs += "<input id=\"status" + for_id + i + "\" name=\"radio\" type=\"radio\" value=\""+ext_array[i]+"\" class=\"js-in-radio js-make-input\"";
        if(ext_array[i] == curent_ext) {
            inputs += " checked=\"checked\"";
        }
        inputs += " \/>";
        inputs += "<label for=\"status" + for_id + i + "\">" + mozilo_lang[ext_array[i]] + "<\/label>";
    }
    return inputs + "<\/form>";
}

// die änderungen bei new, copy und move machen aber nur den name send macht dann den rest
function set_auto_new_name(curent_item,new_name) {
    var in_cat_page = $(curent_item).find(".js-in-cat-page").attr("name"),
        cat_page_new = new_name;
    if(is_page(in_cat_page)) {
//ACHTUNG hier müssen wir die cat ermiteln in der wir sind oder übergeben
//    var cat_name = $(ui.draggable).parents(".js-li-cat").find(".in-cat").attr("name");

        cat_page_new = get_cat(curent_item.parents(".js-li-cat").find(".js-in-cat").attr("name")) + "][" + cat_page_new;
        if(get_target(in_cat_page)) {
            cat_page_new += get_target(in_cat_page);
            cat_page_new += get_link(in_cat_page);
        }
        cat_page_new += get_ext(in_cat_page);
    } else {
        if(get_target(in_cat_page)) {
            cat_page_new += get_target(in_cat_page);
            cat_page_new += get_link(in_cat_page);
            cat_page_new += get_ext(in_cat_page);
        }
    }
    curent_item.find(".js-in-cat-page").attr("name", "sort_array[" + cat_page_new + "]");
    curent_item.find(".js-normal-in-name").text(rawurldecode_js(new_name));
}

function set_send_success_changes(change_item) {
    var new_val = "[" + make_clean_cat_page_name(change_item.find(".js-in-cat-page").attr("name")) + "]";
    change_item.find(".js-in-cat-page").val(new_val);
    change_item.find(".js-normal-in-name").text(rawurldecode_js(get_name(new_val)));
    if(get_target(new_val)) {
        change_item.find(".js-status").text(mozilo_lang[get_target(new_val).replace(/-|_/g, "")]);
    } else if(get_ext(new_val)) {
        change_item.find(".js-status").text(mozilo_lang[get_ext(new_val)]);
    }
    // es wurde eine cat umbenant dann müssen wir die enthaltenen pages auch ändern
    if(!is_page(new_val)) {
        var new_cat = get_cat(new_val);
        change_item.parents(".js-li-cat").find(".js-in-page").each(function(index) {
            var that = $(this),
                in_value = that.val().split("][");
            that.val("["+new_cat+"]["+in_value[1]);
            that.attr("name","sort_array["+new_cat+"]["+in_value[1]);
        });
    }
}

function change_to_rename_mode(this_table) {
    var cat_page = this_table.find(".js-in-cat-page").val(),
        inhalt = "";
    inhalt += make_input_link(cat_page);
    inhalt += make_input_target(cat_page);
    inhalt += make_input_ext(cat_page);

    this_table.find(".js-edit-box").append(inhalt);
    this_table.find(".js-edit-in-name").append(make_input_name(cat_page));

    this_table.find(".js-rename-mode-hide").hide(0);
    this_table.find(".js-rename-mode-show").show(0);

    this_table.find(".js-tools img:not(.js-edit-rename)").addClass("ui-state-disabled");

    this_table.find(".js-link-href").hide(0);

    this_table.find(".js-in-name").focus();
    // Achtung für firefox damit input editierbar ist
    // ist bei $( ).disableSelection(); nötig
//    $(this_table).find(".js-in-name").mousedown(function(event){ event.stopPropagation(); });
    this_table.find(".js-make-input").mousedown(function(event){ event.stopPropagation(); });
}

function change_to_normal_mode(this_table) {

    var link = this_table.find(".js-in-link").val();
    if(typeof link != "undefined")
        if(link.length < 1) {
            link = "#";
            this_table.find(".js-link-href").attr({ href: link, target:"_self" });
        } else {
            if(link.search(/\:\/\//) < 1)
                link = "http://"+link;
            this_table.find(".js-link-href").attr({href: link, target:"_blank"});
        }
    this_table.find(".js-link-href").show(0);
    this_table.find(".js-edit-in-name").html("");
    this_table.find(".js-edit-box").html("");
    this_table.find(".js-rename-mode-hide").show(0);
    this_table.find(".js-rename-mode-show").hide(0);
    this_table.find(".js-tools img:not(.js-edit-rename)").removeClass("ui-state-disabled");
}

function make_send_para_sort_array() {
    var para_sort_cat_page = "&" + $(".js-ul-cats").find(".js-in-cat-page").serialize();
    para_sort_cat_page = para_sort_cat_page.replace(/&sort_array%5B/g, "&sort_array[");
    para_sort_cat_page = para_sort_cat_page.replace(/%5D%5B/g, "][");
    para_sort_cat_page = para_sort_cat_page.replace(/%5D=/g, "]=");
    return para_sort_cat_page;
}

function make_send_para_change(change_item) {
    var para_change = "&" + $(change_item).find(".js-in-cat-page").serialize();
    para_change = para_change.replace(/&sort_array%5B/g, "&cat_page_change[");
    para_change = para_change.replace(/%5D%5B/g, "][");
    para_change = para_change.replace(/%5D=/g, "]=");
    return para_change;
}

function find_li_cat_page(item) {
    if(item.parents(".js-li-page").length == 1) {
        return item.parents(".js-li-page");
    }
    if(item.parents(".js-li-cat").length == 1) {
        return item.parents(".js-li-cat");
    }
    return false;
}
