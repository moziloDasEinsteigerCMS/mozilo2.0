var ce_img_hsv_width = 194;
var ce_tri_width = 126;
var ce_radius_outer = 93;
var ce_ring_width = 20;

var ce_tri_height = Math.round(ce_tri_width * Math.sin(Math.PI / 3));
var ce_radius_inner = ce_radius_outer - ce_ring_width;
var ce_tri_offset_left = (ce_img_hsv_width - ce_tri_width) / 2;
var ce_tri_offset_top = ((ce_img_hsv_width - (ce_radius_outer * 2)) / 2) + ce_ring_width;
var ce_img_url = URL_BASE+ADMIN_DIR_NAME+'/jquery/coloredit/';

var ColorEditor = {
    container:'<table id="ce-colorchange" cellspacing="0" border="0" cellpadding="0">'
        +'<tbody>'
            +'<tr>'
                +'<td rowspan="6" class="ce-td-hsv">'
                    +'<div id="ce-color-hsv" class="d_ui-widget-content d_ui-corner-all">'
                        +'<div id="ce-color"></div>'
                        +'<div id="ce-hsv" class="d_ui-corner-all"></div>'
                        +'<img id="ce-h-marker" class="ce-marker" src="'+ce_img_url+'marker.png" />'
                        +'<img id="ce-sv-marker" class="ce-marker" src="'+ce_img_url+'marker.png" />'
                    +'</div>'
                +'</td>'
                +'<td colspan="2" class="ce-td-prev">'
                    +'<input class="ce-in-hex" type="text" value="" size="6" maxlength="6">'
                    +'<div>'
                        +'<div id="ce-color-curent" class="ui-widget-content ui-corner-right"></div>'
                        +'<div id="ce-color-prev" class="ce-bg-color-change ui-widget-content ui-corner-left"></div>'
                    +'</div>'
                +'</td>'
            +'</tr>'
            +'<tr>'
                +'<td class="ce-td-slider"><div class="ce-red"></div></td>'
                +'<td class="ce-td-value"><input class="ce-in-red ce-in-value" type="text" value="255" size="3" maxlength="3"></td>'
            +'</tr>'
            +'<tr>'
                +'<td class="ce-td-slider"><div class="ce-green"></div></td>'
                +'<td class="ce-td-value"><input class="ce-in-green ce-in-value" type="text" value="255" size="3" maxlength="3"></td>'
            +'</tr>'
            +'<tr>'
                +'<td class="ce-td-slider"><div class="ce-blue"></div></td>'
                +'<td class="ce-td-value"><input class="ce-in-blue ce-in-value" type="text" value="255" size="3" maxlength="3"></td>'
            +'</tr>'
            +'<tr>'
                +'<td class="ce-td-slider"><div class="ce-saturation"></div></td>'
                +'<td class="ce-td-value"><input class="ce-in-saturation ce-in-value" type="text" value="100" size="3" maxlength="3"></td>'
            +'</tr>'
            +'<tr>'
                +'<td class="ce-td-slider ce-tr-last"><div class="ce-brightness"></div></td>'
                +'<td class="ce-td-value ce-tr-last"><input class="ce-in-brightness ce-in-value" type="text" value="100" size="3" maxlength="3"></td>'
            +'</tr>'
            +'<tr>'
                +'<td colspan="3" class="ce-td-default-color"><div class="ce-default-color-box ui-widget-content ui-corner-all">&nbsp;</div>'
            +'</td></tr>'
        +'</tbody>'
    +'</table>',
    options: {
        dragMode : false
    },
    _hsv : [0,0,0],
    _makeDefaultColors: function () {
        var html = "";
        if(defaultcolors.length > 1) {
            defaultcolors_a = defaultcolors.split(",");
            for (var i = 0; i < defaultcolors_a.length; ++i) {
                html += '<img title="'+defaultcolors_a[i]+'" class="ce-default-color-img ui-widget-content ui-corner-all" style="background-color:#'+defaultcolors_a[i]+';" src="'+URL_BASE+ADMIN_DIR_NAME+'/gfx/clear.gif" />';
            }
            $('.ce-default-color-box').html(html);
        } else
            $('#ce-colorchange .ce-default-color-box').hide();
    },

    _create: function() {

        if($('.js-coloreditor-button').length < 1)
            return false;
        if($('#ce-colorchange').length > 0)
            return false;

        var that = this;

        $("body").append(this.container);
//        this._makeDefaultColors();

        $('.ce-td-hsv, #ce-color-hsv, #ce-hsv').css({
            width:ce_img_hsv_width + 'px',
            height:ce_img_hsv_width + 'px'
        });
        $('#ce-color').css({
            width:ce_tri_width + 'px',
            height:ce_tri_height + 'px',
            top:ce_tri_offset_top,
            left:ce_tri_offset_left
        });
        $('#ce-color-curent').css('backgroundColor',"#ffffff");
        $('#ce-color-curent').attr('title',"ffffff");

        if($().spinner) {
            $('.ce-in-saturation, .ce-in-brightness').spinner({
                max:100,
                min:0,
                value:50,
                spin: function( event, ui ) { that._updateSVValue(event) }
            });
            $('.ce-in-red, .ce-in-green, .ce-in-blue').spinner({
                max:255,
                min:0,
                value:50,
                spin: function( event, ui ) { that._updateRGBValue(event) }
            });
        }

        $('.ce-red, .ce-green, .ce-blue').slider({
//            orientation: "vertical",
            range: "min",
            min: 0,
            max: 255,
            value: 127,
            slide: function(event, ui) {
                var red = $('.ce-red').slider('value'),
                    green = $('.ce-green').slider('value'),
                    blue = $('.ce-blue').slider('value');
                if($(this).is('.ce-red'))
                    red = ui.value;
                else if($(this).is('.ce-green'))
                    green = ui.value;
                else if($(this).is('.ce-blue'))
                    blue = ui.value;
                that._hsv = that._RGBToHSV([red,green,blue]);
                that._updateDisplay();
            }
        });

        $('.ce-saturation, .ce-brightness').slider({
//            orientation: "vertical",
            range: "min",
            min: 0,
            max: 100,
            value: 127,
            slide: function(event, ui) {
                var satur = $('.ce-saturation').slider('value'),
                    lumi = $('.ce-brightness').slider('value');
                if($(this).is('.ce-saturation'))
                    satur = ui.value;
                else if($(this).is('.ce-brightness'))
                    lumi = ui.value;
                that._hsv = [that._hsv[0],(satur / 100),(lumi / 100)],
                that._updateDisplay();
            }
        });
        $('.ce-default-color-box').width($('.ce-td-default-color').width() - ($('#ce-colorchange .ce-default-color-box').outerWidth() - $('#ce-colorchange .ce-default-color-box').width()));
        this._makeDefaultColors();

        $('#ce-colorchange').dialog({
            autoOpen: false,
            height: "auto",
            width: "auto",
            modal: false,
            resizable: false,
            show: anim_speed,
            title:"Farb Wähler"
        });

        $('.ce-saturation').prepend('<img src="'+ce_img_url+'black.png" style="position: absolute;" class="ui-corner-all ui-slider-horizontal" />');

        this._hsv = this._HexToHSV(this._getInputHex(this.element.siblings('input')));
        this._updateDisplay();
        this._bindEvents();
    },

    _bindEvents : function () {
        var that = this;
        $('.js-coloreditor-button').on({
            click: function(event) {
                if($('#ce-colorchange').dialog("isOpen")) {
                    $('#ce-colorchange').dialog("close");
                } else {
                    $('#ce-colorchange').dialog({position: { my: "right top", at: "left bottom", of: this }});
                    $('#ce-color-curent').css('background-color', "#"+that._hexFromRGB(that._HSVToRGB(that._hsv)));
                    $('#ce-color-curent').attr('title',that._hexFromRGB(that._HSVToRGB(that._hsv)));
                    $('#ce-colorchange').dialog("open");
                }
            }
        });
        $('#ce-colorchange').on({
            mousedown: function(event) {
                $('#ce-colorchange input, .ce-in-hex').blur();
                that.options.dragMode = that._ifInColorRange(event);
                if(false === that.options.dragMode)
                    return false;
                $(this).css("cursor", "crosshair");
                $(document).on({
                    "mouseup.colorchange": function (event) {
                        that.options.dragMode = false;
                        $(this).css("cursor", "default");
                        $(document).off('.colorchange');
                    }
                });
                that._moveCursor(event);
                return false;
            },
            mousemove: function (event) {
                if(that.options.dragMode) {
                    that._moveCursor(event);
                    return true;
                }
                $(this).css("cursor", "default");
                if(that._ifInColorRange(event))
                    $(this).css("cursor", "crosshair");
            }
        },'#ce-color-hsv').on({
            keyup: function(event) {that._updateRGBValue(event)},
            focusout: function(){
                $(this).val(that._getInputRGB($(this)));
            }
        },'.ce-in-red, .ce-in-green, .ce-in-blue').on({
            keyup: function(event) {that._updateSVValue(event)},
            focusout: function(){
                $(this).val(that._getInputSV($(this)));
            }
        },'.ce-in-saturation, .ce-in-brightness').on({
            click: function(event) {that._updateTagBG(event)}
        },'.ce-td-default-color img, #ce-color-curent');
        $('.ce-in-hex').on({
            keyup: function(event) {that._updateHexValue(event)},
            focusout: function(){
                $(this).val(that._getInputHex($(this)));
            }
        });
    },
    _ifInColorRange : function (event) {
        var offset = $('#ce-hsv').offset();
        var var_top = event.pageY - offset.top - ce_tri_offset_top;
        var pos = this._getRingCenter(event);
        var angle = Math.atan2(pos.x, -pos.y);
        pos.x = Math.abs(pos.x);
        pos.y = Math.abs(pos.y);

        if(pos.x <= (ce_tri_width / 2) && var_top >= 0 && var_top <= ce_tri_height && pos.x < (var_top * 0.5779)) {
            return "triangle";
        }
        if(Math.abs(Math.sin(angle) * ce_radius_outer) > pos.x && Math.abs(Math.sin(angle) * ce_radius_inner) < pos.x && Math.abs(Math.cos(angle) * ce_radius_outer) > pos.y && Math.abs(Math.cos(angle) * ce_radius_inner) < pos.y) {
            return "ring";
        }
        return false;
    },
    _moveCursor : function (event) {
        if (this.options.dragMode == "ring") {
            var pos = this._getRingCenter(event);
            var H = Math.atan2(pos.x, -pos.y) / 6.28;
            if (H < 0) H += 1;
            this._hsv[0] = H;
        } else if(this.options.dragMode == "triangle") {
            var offset = $('#ce-hsv').offset();
            var var_top = ce_tri_height - (event.pageY - offset.top - ce_tri_offset_top);
            var var_left = event.pageX - offset.left - ce_tri_offset_left;
            this._XYtoSV(Math.round(var_left), Math.round(var_top));
        } else
            return;
        this._updateDisplay();
    },
    _XYtoSV : function (left,top) {
        var S = 0;
        if(top > 0)
            S = top / 0.8660;
        var tri_len = left + (S * 0.5);
        if(S > 0)
            S = S / tri_len;
        if(S > 1)
            tri_len = top / 0.8660;
        var V = tri_len / ce_tri_width;
        this._hsv[1] = Math.max(0, Math.min(1, S));
        this._hsv[2] = Math.max(0, Math.min(1, V));
    },
    _SVtoXY : function () {
        var top =  ( (ce_tri_width * this._hsv[2]) * this._hsv[1] ) * 0.8660;
        var left =   (ce_tri_width * this._hsv[2]) -  (( (ce_tri_width * this._hsv[2]) * this._hsv[1] ) * 0.5);
        return [top,left]
    },

    _updateDisplay : function () {
        // Markers
        var angle = this._hsv[0] * 6.28;
        $('#ce-h-marker').css({
            left: Math.round(Math.sin(angle) * (ce_radius_outer - (ce_ring_width / 2)) + ce_img_hsv_width / 2) + 'px',
            top: Math.round(-Math.cos(angle) * (ce_radius_outer - (ce_ring_width / 2)) + ce_img_hsv_width / 2) + 'px'
        });
        var top_left = this._SVtoXY();
        $('#ce-sv-marker').css({
            top : ((ce_tri_height - top_left[0]) + ce_tri_offset_top) + 'px',
            left : (top_left[1] + ce_tri_offset_left) + 'px'
        });
        // Saturation/Luminance gradient
        $('#ce-color').css('backgroundColor', "#"+this._hexFromRGB(this._HSVToRGB([this._hsv[0], 1, 1])));
        var rgb = this._HSVToRGB(this._hsv);
        var hex = this._hexFromRGB(rgb);

        if($('.ce-red a:not(.ui-state-active)').length > 0)
            $('.ce-red').slider("value", rgb[0]);
        if($('.ce-green a:not(.ui-state-active)').length > 0)
            $('.ce-green').slider("value", rgb[1]);
        if($('.ce-blue a:not(.ui-state-active)').length > 0)
            $('.ce-blue').slider("value", rgb[2]);
        $('.ce-in-red:not(:focus)').val(rgb[0]);
        $('.ce-in-green:not(:focus)').val(rgb[1]);
        $('.ce-in-blue:not(:focus)').val(rgb[2]);
        $('.ce-in-hex:not(:focus)').val(hex);
        if($('.ce-saturation a:not(.ui-state-active)').length > 0)
            $('.ce-saturation').slider("value", this._hsv[1] * 100);
        if($('.ce-brightness a:not(.ui-state-active)').length > 0)
            $('.ce-brightness').slider("value", this._hsv[2] * 100);
        $('.ce-in-saturation:not(:focus)').val(Math.round(this._hsv[1] * 100));
        $('.ce-in-brightness:not(:focus)').val(Math.round(this._hsv[2] * 100));
        $('.ce-brightness').css({
            backgroundColor: "#"+this._hexFromRGB(this._HSVToRGB([this._hsv[0], this._hsv[1], 1]))
        });
        $('.ce-saturation').css({
            backgroundColor: "#"+this._hexFromRGB(this._HSVToRGB([this._hsv[0], 1, this._hsv[2]]))
        });
        $('.ce-saturation img').css({
            opacity: (1 - this._hsv[2])
        });

        $('.ce-bg-color-change').css({
            backgroundColor: "#"+hex,
            color: this._hsv[2] > 0.5 ? '#000' : '#fff'
        }).not(':focus').val(hex);
    },

    _getCaretPos : function (item) {
        var pos = 0;
        item.focus();
        if(document.selection) {
            var sel = document.selection.createRange().duplicate();
            sel.moveStart('character',-item.value.length);
            pos = sel.text.length;
        } else if(item.selectionStart)
            pos = item.selectionStart;
        return pos;
    },

    _setCaretPos : function (item,pos) {
        item.focus();
        if(document.selection) {
            var range = item.createTextRange(); 
            range.move("character", pos); 
            range.select(); 
        } else if(item.selectionStart) {
            item.selectionStart = pos;
            item.selectionEnd = pos;
        }
    },

    _updateHexValue : function (event) {
        var ele = $(event.target);
        // aktuelle cursor position merken
        var caret_pos = this._getCaretPos(event.target);
        // alles gross schreiben
        var new_value = ele.val().toUpperCase();
        // wenn es nicht hex konform ist
        if(new_value.search(/[^A-F0-9]/) != -1) {
            // neue cursorposition ist das unerlaubte zeichen
            caret_pos = new_value.search(/[^A-F0-9]/);
            // unerlaubte zeichen entfernen
            new_value = new_value.replace(/[^A-F0-9]/g,"");
        }
        // input mit neuen inhalt fühlen und cursor setzen
        ele.val(new_value);
        this._setCaretPos(event.target,caret_pos);
        this._hsv = this._HexToHSV(this._getInputHex(ele));
        this._updateDisplay();
    },

    _updateSVValue : function (event) {
        var ele = $(event.target);
        // aktuelle cursor position merken
        var caret_pos = this._getCaretPos(event.target);
        // alles gross schreiben
        var new_value = ele.val();
        // wenn es nicht hex konform ist
        if(new_value.search(/[^0-9]/) != -1) {
            // neue cursorposition ist das unerlaubte zeichen
            caret_pos = new_value.search(/[^0-9]/);
            // unerlaubte zeichen entfernen
            new_value = new_value.replace(/[^0-9]/g,"");
        }
        // input mit neuen inhalt fühlen und cursor setzen
        ele.val(new_value);
        this._setCaretPos(event.target,caret_pos);
        this._hsv = [this._hsv[0],(this._getInputSV($('.ce-in-saturation')) / 100),(this._getInputSV($('.ce-in-brightness')) / 100)];
        this._updateDisplay();
    },

    _updateRGBValue : function (event) {
        var ele = $(event.target);
        // aktuelle cursor position merken
        var caret_pos = this._getCaretPos(event.target);
        // alles gross schreiben
        var new_value = ele.val();
        // wenn es nicht hex konform ist
        if(new_value.search(/[^0-9]/) != -1) {
            // neue cursorposition ist das unerlaubte zeichen
            caret_pos = new_value.search(/[^0-9]/);
            // unerlaubte zeichen entfernen
            new_value = new_value.replace(/[^0-9]/g,"");
        }
        // input mit neuen inhalt fühlen und cursor setzen
        ele.val(new_value);
        this._setCaretPos(event.target,caret_pos);
        this._hsv = this._RGBToHSV([(this._getInputRGB($('.ce-in-red'))),(this._getInputRGB($('.ce-in-green'))),(this._getInputRGB($('.ce-in-blue')))]);
        this._updateDisplay();
    },
    _updateTagBG : function (event) {
        var new_color = $(event.target).css('background-color');
        if($(event.target).attr('title'))
            new_color = $(event.target).attr('title');
        if(new_color.substr(0, 3) == "rgb") {
            new_color = new_color.replace(/rgb\(/, "").replace(/rgba\(/, "").replace(/\)/, "").replace(/\ /g, "");
            new_color = new_color.split(",");
            this._hsv = this._RGBToHSV([new_color[0],new_color[1],new_color[2]]);
        } else
            this._hsv = this._HexToHSV(new_color);
        this._updateDisplay();
    },

    // Retrieve the coordinates of the given event relative to the center of the widget.
    _getRingCenter : function (event) {
        var offset = $('#ce-hsv').offset();
        return { x: (event.pageX - offset.left) - ce_img_hsv_width / 2, y: (event.pageY - offset.top) - ce_img_hsv_width / 2 };
    },
    _getInputRGB : function (el) {
        var v = el.val();
        return (v == "" ? 0 : Math.min(Math.max(v ,0), 255));
    },
    _getInputSV : function (el) {
        var v = el.val();
        return (v == "" ? 0 : Math.min(Math.max(v ,0), 100));
    },
    _getInputHex : function (el) {
        var v = el.val().toUpperCase().replace(/[^A-F0-9]/g,"");
        return v+("000000".substr((Math.min(v.length,6))));
    },

    _HexToRGB : function (hex) {
        return [parseInt('0x' + hex.substring(0, 2)),
                parseInt('0x' + hex.substring(2, 4)),
                parseInt('0x' + hex.substring(4, 6))];
    },

    _HexToHSV : function (hex) {
        return this._RGBToHSV(this._HexToRGB(hex));
    },

    _RGBToHSV : function (rgb) {
        var r = ( rgb[0] / 255 );
        var g = ( rgb[1] / 255 );
        var b = ( rgb[2] / 255 );

        var min = Math.min( r, g, b );
        var max = Math.max( r, g, b );
        var delta = max - min;

        var H = 0;
        var S = 0;
        var V = max;
        if ( delta != 0 ) {
            S = delta / max;

            var del_R = ( ( ( max - r ) / 6 ) + ( delta / 2 ) ) / delta;
            var del_G = ( ( ( max - g ) / 6 ) + ( delta / 2 ) ) / delta;
            var del_B = ( ( ( max - b ) / 6 ) + ( delta / 2 ) ) / delta;

            if      ( r == max ) H = del_B - del_G;
            else if ( g == max ) H = ( 1 / 3 ) + del_R - del_B;
            else if ( b == max ) H = ( 2 / 3 ) + del_G - del_R;

            if ( H < 0 ) H += 1;
            if ( H > 1 ) H -= 1;
        }
        return [H, S, V];
    },
    _HSVToRGB : function (hsv) {
        var H = hsv[0], S = hsv[1], V = hsv[2];
            var R = V * 255;
            var G = V * 255;
            var B = V * 255;
        if ( S > 0 ) {
            var var_r, var_g, var_b;
            var var_h = H * 6;
            if ( var_h == 6 ) var_h = 0;
            var var_i = Math.floor( var_h );
            var var_1 = V * ( 1 - S );
            var var_2 = V * ( 1 - S * ( var_h - var_i ) );
            var var_3 = V * ( 1 - S * ( 1 - ( var_h - var_i ) ) );

            if      ( var_i == 0 ) { var_r = V;      var_g = var_3;  var_b = var_1; }
            else if ( var_i == 1 ) { var_r = var_2;  var_g = V;      var_b = var_1; }
            else if ( var_i == 2 ) { var_r = var_1;  var_g = V;      var_b = var_3; }
            else if ( var_i == 3 ) { var_r = var_1;  var_g = var_2;  var_b = V;     }
            else if ( var_i == 4 ) { var_r = var_3;  var_g = var_1;  var_b = V;     }
            else                   { var_r = V;      var_g = var_1;  var_b = var_2; }

            R = var_r * 255;
            G = var_g * 255;
            B = var_b * 255;
        }
        return [Math.round(R),Math.round(G),Math.round(B)];
    },
    _hexFromRGB : function (rgb) {
        var hex = [rgb[0].toString(16),rgb[1].toString(16),rgb[2].toString(16)];
        for(var i = 0;i < hex.length;i++) {
            if (hex[i].length === 1) {
                hex[i] = "0" + hex[i];
            }
        }
        return hex.join("").toUpperCase();
    }
};
$.widget ('ui.coloreditor', ColorEditor);

