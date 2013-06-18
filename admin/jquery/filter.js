var moFilterPlugin = {
    options: {
        search_item: '.js-file-dir',
        search_name: '.js-gallery-name',
        search_min: 1
    },
    _create: function() {
        var o = this.options;
        var that = this;
        this.rows = [];
        this.search_rows = [];
        if($(this.element).find(o.search_item).length > o.search_min) {
            this.search_fild = $('<div class="mo-margin-top ui-state-default ui-corner-all mo-li-head-tag-no-ul mo-li-head-tag mo-tag-height-from-icon mo-middle"><span class="mo-bold mo-padding-right mo-padding-left">'+mozilo_lang["filter_text"]+' '+mozilo_lang["filter_text_"+action_activ]+'</span><input class="mo-plugin-input" type="search" style="width:15em" /></div>');

            this.search_fild.insertBefore(this.element);

            this.search_fild.find('input[type="search"]')
                .bind({
                keydown: function( e ){
                    if( e.which === 13 )
                        e.preventDefault();
                },
                keyup: function( e ){that._filter();},
                click: function( e ){e.preventDefault(); that._filter();},
                focus: function( e ){that._makeRows();}
            });

            if(action_activ == "catpage") {
                this.search_fild.append('<input type="button" class="js-filter-page-hide mo-checkbox-del mo-td-middle" value="'+mozilo_lang["filter_button_all_hide"]+'" />');
                this.search_fild.find('input[type="button"]')
                    .bind({
                        click: function( e ){
                            e.preventDefault();
                            if($(this).hasClass('js-filter-page-hide')) {
                                $('.js-li-page').css("display","none");
                                $(this).val(mozilo_lang["filter_button_all_show"]).removeClass('js-filter-page-hide');
                            } else {
                                $('.js-li-page').css("display","block");
                                $(this).val(mozilo_lang["filter_button_all_hide"]).addClass('js-filter-page-hide');
                            }
                        },
                    });
            }
        }
    },
    _makeRows: function() {
        var o = this.options;
        var that = this;
        this.rows = $(this.element).find(o.search_item);
        this.search_rows = $.map(this.rows, function(v, i){
                return that.rows.eq(i).find(o.search_name).text().toLowerCase();
            });
    },
    _filter: function() {
        var term = $.trim( this.search_fild.find('input[type="search"]').val().toLowerCase() );
        var rows = this.rows;
        var search_rows = this.search_rows;
        if( !term ){
            rows.show();
            if(action_activ == "catpage") {
                $('.js-move-me-cat').css({opacity:1,cursor:"move"}).removeClass('js-deact-filter');
                $('.js-new-ul .js-li-cat').css("display","block");
            }
        } else {
            rows.hide();
            if(action_activ == "catpage") {
                $('.js-move-me-cat').css({opacity:0.3,cursor:"default"}).addClass('js-deact-filter');
                $('.js-new-ul .js-li-cat').css("display","none");
            }
            if(action_activ == "plugins") {
                $(".js-plugin-del:checked").prop("checked",false);
            }
            var rEscape = /[\-\[\]{}()*+?.,\\\^$|#\s]/g;
            var regex = new RegExp(term.replace(rEscape, "\\$&"), 'gi');

            $.map(search_rows, function(v, i){
                if(v.search(regex) !== -1) {
                    rows.eq(i).show();
                    return true;
                }
                return null;
            });
        }
    },
};

$.widget('custom.mofilterplugin', moFilterPlugin);

$(function() {
    if(action_activ == "gallery")
        $('.js-gallery').mofilterplugin({search_item:'.js-file-dir',search_name:'.js-gallery-name'});

    if(action_activ == "plugins")
        $('.js-plugins').mofilterplugin({search_item:'.js-plugin',search_name:'.js-plugin-name'});

    if(action_activ == "files")
        $('.js-files').mofilterplugin({search_item:'.js-file-dir',search_name:'.js-gallery-name'});

    if(action_activ == "catpage")
        $('.js-ul-cats').mofilterplugin({search_item:'.js-li-cat',search_name:'.js-cat-name'});
});

