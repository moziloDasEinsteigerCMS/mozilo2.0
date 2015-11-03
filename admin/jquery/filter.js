$(function() {
    $.widget('custom.mofilterplugin', {
        options: {
            search_item: false,
            search_name: false,
            filter_text: mozilo_lang["filter_text_"+action_activ]+' '+mozilo_lang["filter_text"],
            filter_action: action_activ
        },
        _create: function() {
            if(!this.options.search_item || !this.options.search_name)
                return false;

            var that = this;
            this.rows = [];
            this.search_rows = [];

            this.search_fild = $('<div class="js-mofilterplugin mo-margin-top ui-state-default ui-corner-all ui-helper-clearfix mo-li-head-tag-no-ul mo-li-head-tag mo-tag-height-from-icon mo-middle"><\/div>');
            this.search_fild.append('<span class="mo-bold mo-padding-right mo-padding-left">'+this.options.filter_text+'<\/span>');
            this.search_fild.append('<input class="mo-plugin-input" value="" type="search" style="width:15em" \/>');

            this.search_fild.insertBefore(this.element);

            this.search_fild.on({
                    keydown: function(e) {
                        if(e.which === 13) e.preventDefault();
                        if(e.which === 27) $(this).val("");
                    },
                    keyup: function(e) {that._filter();},
                    click: function(e) {that._filter();},
                    focus: function(e) {that._makeRows();}
                },'input[type="search"]');

            if(this.options.filter_action == "catpage") {
                this.search_fild.append('<input type="button" class="js-filter-page-hide mo-checkbox-del mo-td-middle" value="'+mozilo_lang["filter_button_all_hide"]+'" \/>');
                this.search_fild.on({
                        click: function(e) {
                            e.preventDefault();
                            if($(this).hasClass('js-filter-page-hide')) {
                                $('.js-li-page').css("display","none");
                                $(this).val(mozilo_lang["filter_button_all_show"]).removeClass('js-filter-page-hide');
                            } else {
                                $('.js-li-page').css("display","block");
                                $(this).val(mozilo_lang["filter_button_all_hide"]).addClass('js-filter-page-hide');
                            }
                        }
                    },'input[type="button"]');
            }
        },
        _makeRows: function() {
            var that = this;
            this.rows = $(this.element).find(this.options.search_item);
            this.search_rows = $.map(this.rows, function(v,i) {
                    return that.rows.eq(i).find(that.options.search_name).text().toLowerCase();
                });
        },
        _filter: function() {
            var search_str = $.trim(this.search_fild.find('input[type="search"]').val().toLowerCase()),
                rows = this.rows,
                search_rows = this.search_rows;
            if(!search_str) {
                rows.css("display","block");
                if(this.options.filter_action == "catpage") {
                    $('.js-move-me-cat').css({opacity:1,cursor:"move"}).removeClass('js-deact-filter');
                    $('.js-new-ul .js-li-cat').css("display","block");
                }
            } else {
                rows.css("display","none");
                if(this.options.filter_action == "catpage") {
                    $('.js-move-me-cat').css({opacity:0.3,cursor:"default"}).addClass('js-deact-filter');
                    $('.js-new-ul .js-li-cat').css("display","none");
                }
                if(this.options.filter_action == "plugins")
                    $(".js-plugin-del:checked").prop("checked",false);

                // 1. sonderzeichen escapen auser lehrzeichen und +
                // 2. + und oder lehrzeichen+ ist gleich oder suche also ein |
                // 3. die restlichen lehrzeichen escapen
                search_str = search_str.replace(/[\-\[\]{}()\*?.,\\\^$|#]/g,"\\$&")
                    .replace(/[\s]*[+]/g,"|")
                    .replace(/[\s]/g,"\\$&");

                search_str = new RegExp(search_str,'gi');

                $.map(search_rows, function(v,i) {
                    if(v.search(search_str) !== -1)
                        rows.eq(i).css("display","block");
                    return null;
                });
            }
        }
    });

    if(action_activ == "gallery")
        $('.js-gallery').mofilterplugin({search_item:'.js-file-dir',search_name:'.js-gallery-name'});

    if(action_activ == "plugins")
        $('.js-plugins').mofilterplugin({search_item:'.js-plugin',search_name:'.js-plugin-name'});

    if(action_activ == "files")
        $('.js-files').mofilterplugin({search_item:'.js-file-dir',search_name:'.js-gallery-name'});

    if(action_activ == "catpage")
        $('.js-ul-cats').mofilterplugin({search_item:'.js-li-cat',search_name:'.js-cat-name'});
});

