var moFilterPlugin = {
    options: {
        search_item: '.js-file-dir',
        search_name: '.js-gallery-name',
        search_min: 1
    },
    _create: function() {
        var o = this.options;
        var that = this;
//console.log("_create="+o.search_item)
        this.rows = [];
        this.search_rows = [];
        if($(this.element).find(o.search_item).length > o.search_min) {
            this.search_fild = $('<div class="mo-margin-top ui-state-default ui-corner-all mo-li-head-tag-no-ul mo-li-head-tag mo-td-middle"><span class="mo-padding-right mo-padding-left">Filter: </span><input id="serach_in" class="mo-plugin-input" type="search" style="width:200px" /></div>');

            this.search_fild.insertBefore(this.element);

            this.search_fild.find('input')
                .bind({
                keydown: function( e ){
                    if( e.which === 13 )
                        e.preventDefault();
                },
                keyup: function( e ){that._filter();},
                click: function( e ){that._filter();},
                focus: function( e ){that._makeRows();}
            });
//            this._makeRows();
        }
    },
/*    upDate:  function() {
console.log("upDate")
        this._makeRows();
    },*/
    _makeRows: function() {
        var o = this.options;
        var that = this;
        this.rows = $(this.element).find(o.search_item);
        this.search_rows = $.map(this.rows, function(v, i){
                return that.rows.eq(i).find(o.search_name).text().toLowerCase();
            });
    },
    _filter: function() {
//console.log("_filter")
        var term = $.trim( this.search_fild.find('input').val().toLowerCase() );
        var rows = this.rows;
        var search_rows = this.search_rows;
        if( !term ){
            rows.show();
            if(action_activ == "catpage") {
                $('.js-move-me-cat').css({opacity:1,cursor:"move"}).removeClass('js-deact-filter');
//                $('.js-new-ul .js-li-cat').css({visibility: "visible"});
                $('.js-new-ul .js-li-cat').show(0);
            }
        } else {
            rows.hide();
            if(action_activ == "catpage") {
                $('.js-move-me-cat').css({opacity:0.3,cursor:"default"}).addClass('js-deact-filter');
//                $('.js-new-ul .js-li-cat').css({visibility: "hidden"});
                $('.js-new-ul .js-li-cat').hide(0);
            }
            if(action_activ == "plugins") {
                $(".js-plugin-del:checked").prop("checked",false);
            }
            var rEscape = /[\-\[\]{}()*+?.,\\\^$|#\s]/g;
            var regex = new RegExp(term.replace(rEscape, "\\$&"), 'gi');

            $.map(search_rows, function(v, i){
//console.log("v="+v)
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
        $('.js-gallery').mofilterplugin({search_item: '.js-file-dir',search_name: '.js-gallery-name'});

    if(action_activ == "plugins")
        $('.js-plugins').mofilterplugin({search_item: '.js-plugin',search_name: '.js-plugin-name'});

    if(action_activ == "files")
        $('.js-files').mofilterplugin({search_item: '.js-file-dir',search_name: '.js-gallery-name'});

    if(action_activ == "catpage")
        $('.js-ul-cats').mofilterplugin({search_item: '.js-li-cat',search_name: '.js-cat-name'});
});

