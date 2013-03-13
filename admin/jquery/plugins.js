var user_para = "chanceplugin=true";
var dialog_plugin = false;

var activ_handler = function() {
    var that = $(this),
        upper_tag = that.closest(".js-plugin"),
        plugin_name = "&plugin_name="+that.attr("name").replace(/\[active\]/,""),
        activ = "=true";
    if(that.prop("checked")) {
        // die anzeige einstelungen zeigen
        upper_tag.find(".js-config").show(anim_speed);
    } else {
        // die anzeige einstelungen hiden
        // wenn js-toggle-content hiden ist müssen wir mit display none arbeiten da hide das nicht macht
        if(upper_tag.find(".js-toggle-content").is(":visible")) {
            upper_tag.find(".js-config").hide(anim_speed);
        } else {
            upper_tag.find(".js-config").css("display","none");
        }
        activ = "=false";
    }
    send_data(user_para+plugin_name+"&"+that.attr("name")+activ);
};

var save_handler = function() {
    var plugin = $(this).closest(".js-plugin"),
        plugin_name = plugin.find(".js-plugin-active input").attr("name").replace(/\[active\]/,""),
        plugin_name_search = new RegExp(plugin_name+"%5B",'g'),
        para = plugin.find(".js-config [name]").serialize();
    para = para.replace(plugin_name_search, plugin_name+"[");
    para = para.replace(/%5D=/g, "]=");

    if(para.length > 0)
        para = "&"+para;

    send_data(user_para+"&plugin_name="+plugin_name+para);
};


$(function() {

    $('body').append('<div id="dialog-plugin-admin"><iframe frameborder="0" width="100%" height="100%" align="left" style="overflow:visible;" /></div>');
    $('#dialog-plugin-admin').dialog({
        autoOpen: false,
        resizable: true,
        height: "auto",
        width: "auto",
        modal: true,
        title: "admin Plugin",
        buttons: [],
        create: function(event, ui) {
            dialog_plugin = $(this);
        }
    });

    $('body').on("click",".js-config-adminlogin", function(event){
        event.preventDefault();
        var plugin_name = $(this).attr("name").replace(/\[pluginadmin\]/,""),
            para = URL_BASE+ADMIN_DIR_NAME+"/index.php?nojs=true&pluginadmin="+plugin_name+"&action="+action_activ;
        $('#dialog-plugin-admin iframe').attr("src",para);
        $('#dialog-plugin-admin iframe').load(function(){
            $(this).contents().find('html,body').css('min-width','auto');
        });
        dialog_plugin.dialog({
            width: $(".mo-td-content-width").eq(0).width(),
            height: (parseInt($(window).height()) - dialogMaxheightOffset),
            title: "admin Plugin "+plugin_name}).dialog("open");
//        dialog_open("messages",iframe);

//!!!!!!!!!!! das neu login geht hier noch nicht
// ewentuell ein bind onsubmit auf forms inerhalb vom iframe und wenn login zurückkomt anmelde dialog
// und gespeicherten inhalt im iframe wieder herstellen
    });

    // alle plugin activ checkbox die nicht activ sind suchen
     $('.js-plugin-active input[type="checkbox"]:not(:checked)').each(function(i,tag) {
         var upper_tag = $(this).closest(".js-plugin");
         // die anzeige einstelungen hiden
         upper_tag.find(".js-config").css("display","none");
     });

    // weil der in einem hide() drin ist muss display:none benutzt werden
    $(".js-plugin-help-content").css("display","none");

    // toggle für die hilfe
    $('body').on("click",".js-help-plugin", function(event) {
        var upper_tag = $(this).closest("table");
        if(upper_tag.find(".js-plugin-help-content").is(":visible")) {
            upper_tag.find(".js-plugin-help-content").hide(anim_speed);
            return;
        } else if(!upper_tag.find(".js-plugin-help-content").is(":visible")) {
            upper_tag.find(".js-plugin-help-content").show(anim_speed);
            return;
        }
    });


    $('body').on("click",".js-save-plugin",save_handler);
    $('body').on("click",'.js-plugin-active input[type="checkbox"]',activ_handler);

    $(".js-select:not(.js-multi)").multiselect({
        multiple: false,
        showClose: false,
        showSelectAll:false,
        noneSelectedText: false,
        selectedList: 1
    }).multiselectfilter();

    $(".js-select.js-multi").multiselect({
        multiple: true,
        showClose: false,
        showSelectAll:false,
        noneSelectedText: false,
        selectedList: 1
    }).multiselectfilter();

});