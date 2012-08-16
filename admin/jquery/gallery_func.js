function change_to_rename_mode(this_item) {
//    var tmpl = $(this).parents('.js-file-dir-header');
    if($(this_item).find('.ui-state-disabled').length > 0)
        return false;
//    event.preventDefault();

    var name = $(this_item).find('.js-gallery-name');
    var in_name = $('<input type="text" />');
    in_name.prop('value', $(name).text());
    in_name.addClass('in-gallery-new-name');
// js-gallery-name
    $(name).hide(0);
    $(this_item).find('.js-toggle, .js-edit-delete').addClass('ui-state-disabled');
    $(in_name).insertAfter(name).focus();
//            $(this).closest('.fileupload').find('.js-toggle, .js-edit-delete').removeClass('ui-state-disabled');
}

function is_name_twice(in_cat_page,name) {
    var name_test = false;
    $(in_cat_page).each(function(index) {
//$("#out").html($("#out").html()+"<br>"+name+" = "+rawurlencode_js($(this).text()));
        if(name == rawurlencode_js($(this).text())) {
            name_test = true;
        }
    });
    return name_test;
}

function get_next_number_name(name) {
    var is_next_copy = name.match(/%23_\d+_/);
//alert(is_next_copy)
    if(is_next_copy) {
        var next_copy_nummber = is_next_copy[is_next_copy.length - 1].substring(3).match(/\d+/);
//alert(is_next_copy[is_next_copy.length - 1].substring(3).match(/\d+/))
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
//$("#out").html($("#out").html()+"<br>get_next_free_name="+name);
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
//$("#out").html($("#out").html()+"<br>is_copy_name");
            return is_copy_name;
        }
    }
    // namen gibts nicht dann könne wir den benutzen
    if(!is_name_twice(in_cat_page,name)) {
//$("#out").html($("#out").html()+"<br>!is_name_twice = "+name);
        return name;
    // namen gibts also copy syntax solange mit laufender nummer ersetzen bis es in nicht gibt
    } else {
        while(is_name_twice(in_cat_page,name)) {
//$("#out").html($("#out").html()+"<br>while = "+name);
            name = get_next_number_name(name);
            if(name == false) {
                return false;
            }
        }
//$("#out").html($("#out").html()+"<br>is_name_twice = "+name);
        return name;
    }
    return false;
}

function make_rename_changes(change_item) {
    var new_name_item = $(change_item).find('.in-gallery-new-name'),
        name_item = $(change_item).find('.js-gallery-name')
        curent_name = new RegExp('curent_dir='+rawurlencode_js(rawurlencode_js($(name_item).text()))),
        curent_newname = 'curent_dir='+rawurlencode_js(rawurlencode_js($(new_name_item).val())),

        curent_name_srv = new RegExp('/galerien/'+rawurlencode_js(rawurlencode_js($(name_item).text()))+'/'),
        curent_newname_srv = '/galerien/'+rawurlencode_js(rawurlencode_js($(new_name_item).val()))+'/';

    $(name_item).text($(new_name_item).val());
    $(change_item).find('input[name="curent_dir"]').val(rawurlencode_js($(new_name_item).val()));

    $(new_name_item).remove()
    $(change_item).find('.js-toggle, .js-edit-delete').removeClass('ui-state-disabled');
    $(name_item).show(0);

    $(change_item).find('.template-download').each(function() {
        var prev_a = $(this).find('.preview a'),
            prev_img = $(this).find('.preview img'),
            del = $(this).find('.delete button');
        if(prev_a.length > 0)
            prev_a.prop('href',prev_a.prop('href').replace(curent_name_srv,curent_newname_srv));
        if(prev_img.length > 0)
            prev_img.prop('src',prev_img.prop('src').replace(curent_name_srv,curent_newname_srv));
        if(del.length > 0)
            del.attr('data-url',del.attr('data-url').replace(curent_name,curent_newname));
    });
}

// bei änderung der default greift das hier
function cange_size(change_item) {

    var max_w = $(change_item).eq(0);
    var max_h = $(change_item).eq(1);

    if(max_w.val() != "auto")
        $("input[name=\""+max_w.attr('name').replace(/_global/,"")+"\"]").val(max_w.val());
    if(max_h.val() != "auto")
        $("input[name=\""+max_h.attr('name').replace(/_global/,"")+"\"]").val(max_h.val());
    if(max_w.val() == "auto" || max_h.val() == "auto") {
        var this_name_w = max_w.attr('name').replace(/_global/,"");
        var this_name_h = max_h.attr('name').replace(/_global/,"");
        $('.fileupload').each( function() {
            var this_in_w = $(this).find("input[name=\""+this_name_w+"\"]");
            var this_in_h = $(this).find("input[name=\""+this_name_h+"\"]");
            if(max_w.val() == "auto")
                this_in_w.val("");
            if(max_h.val() == "auto")
                this_in_h.val("");
            $(this).find('.pixelsize span').each( function() {
                var w_h = $(this).text().split("x");
                if(max_w.val() == "auto") {
                    if(this_name_w.match(/width/) && this_in_w.val() < parseInt(w_h[0]))
                        this_in_w.val(parseInt(w_h[0]))
                }
                if(max_h.val() == "auto") {
                    if(this_name_h.match(/height/) && this_in_h.val() < parseInt(w_h[1]))
                        this_in_h.val(parseInt(w_h[1]))
                }
            });
        });
    }
}

