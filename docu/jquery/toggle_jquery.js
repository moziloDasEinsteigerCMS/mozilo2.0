$(function () {

    var toggle_speed = 500;
    // manuelle buttons
    $('.to-open').show(0);
    $('.to-content, .to-auto-content, .to-docu-content').css("display","none");
    $('body').on({
        click: function(event) {
            event.preventDefault();
            var toggle_item = $(this).parents('.to-toggle');
            if($('.to-content',toggle_item).eq(0).is(':hidden')) {
                $('.to-content',toggle_item).eq(0).slideDown(toggle_speed);
                if(!$('.to-open',toggle_item).eq(0).hasClass('to-close')) {
                    $('.to-close',toggle_item).eq(0).show(0);
                    $('.to-open',toggle_item).eq(0).hide(0);
                }
            } else {
                $('.to-content',toggle_item).eq(0).slideUp(toggle_speed);
                if(!$('.to-open',toggle_item).eq(0).hasClass('to-close')) {
                    $('.to-close',toggle_item).eq(0).hide(0);
                    $('.to-open',toggle_item).eq(0).show(0);
                }
            };
        }
    },'.to-open, .to-close');

    // automatisch erzeugten buttons
    $("<span class=\"to-auto-button\">"+out_text+"</span>").insertBefore('.to-auto-content');
    $('body').on({
        click: function(event) {
            event.preventDefault();
            var toggle_item = $(this).next('.to-auto-content').eq(0);
            if(toggle_item.is(':hidden')) {
                toggle_item.slideDown(toggle_speed);
                $(this).html(in_text);
            } else {
                toggle_item.slideUp(toggle_speed);
                $(this).html(out_text);
            };
        }
    },'.to-auto-button');

    // docu module buttons
    $('body').on({
        click: function(event) {
            event.preventDefault();
            var id_nr = this.id.substr(6);
            if($('#to-docu-content'+id_nr).is(':hidden')) {
                $('#to-docu-content'+id_nr).slideDown(toggle_speed);
                $('#to-b-c'+id_nr).show(0);
                $('#to-b-o'+id_nr).hide(0);
            } else {
                $('#to-docu-content'+id_nr).slideUp(toggle_speed);
                $('#to-b-c'+id_nr).hide(0);
                $('#to-b-o'+id_nr).show(0);
            };
        }
    },'.to-docu-button');

    $('#to-docu-button-all-o').on('click',function(event) {
        $('#to-docu-button-all-c').show(0);
        $('#to-docu-button-all-o').hide(0);
        $('.to-docu-button').each(function(){
//console.log(this.id);
            var id_nr = this.id.substr(6);
            $('#to-docu-content'+id_nr).slideDown(0);
            $('#to-b-c'+id_nr).show(0);
            $('#to-b-o'+id_nr).hide(0);
        });
    });

    $('#to-docu-button-all-c').on('click',function(event) {
        $('#to-docu-button-all-o').show(0);
        $('#to-docu-button-all-c').hide(0);
        $('.to-docu-button').each(function(){
            var id_nr = this.id.substr(6);
            $('#to-docu-content'+id_nr).slideUp(0);
            $('#to-b-c'+id_nr).hide(0);
            $('#to-b-o'+id_nr).show(0);
        });
    });
});
