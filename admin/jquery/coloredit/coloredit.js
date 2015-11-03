// 194 = img hsv breite höhe
// 126 = dreieck breite
// 109 = dreieck höhe
// 34 = dreieck abstand links
// 24 = dreieck abstand top
// 93 = aüserer ring durchmesser
// 73 = innerer ring durchmesser
// 20 = ring breite
$(function() {

    $.widget('ui.coloreditor',{
        _create: function() {
            if($('.js-coloreditor-button').length < 1)
                return false;
            if($('#ce-colorchange').length > 0)
                return false;

            this.hsv = [0,0,0];
            this.dragMode = false;

            var that = this,
                img_url = URL_BASE+ADMIN_DIR_NAME+'/jquery/coloredit/';

            $('.ce-in-hex').removeClass('js-in-hex');
            $('.js-coloreditor-button').show();

            this.color_box = $('<div id="ce-colorchange" \/>').addClass("ce-pos-rel").appendTo("body");

            var hsv_box = $('<div \/>').addClass("ce-pos-rel ce-color-hsv-dim")
                .bind({
                mousedown: function(event) {
                    if(event.which != 1) return true;
                    event.preventDefault();
                    $('input, a').trigger("blur");
                    that.dragMode = that._ifInColorRange(event);
                    if(false === that.dragMode)
                        return true;
                    $(this).css("cursor", "crosshair");
                    $(document).on({
                        "mouseup.colorchange": function(event) {
                            that.dragMode = false;
                            $(this).css("cursor", "default");
                            $(document).off('.colorchange');
                        }
                    });
                    that._moveCursor(event);
                },
                mousemove: function(event) {
                    if(that.dragMode) {
                        that._moveCursor(event);
                        return true;
                    }
                    if(that._ifInColorRange(event))
                        $(this).css("cursor", "crosshair");
                    else
                        $(this).css("cursor", "default");
                }}).appendTo(this.color_box);

            this.color_sv = $('<div \/>').addClass("ce-pos-abs ce-tri-dim").appendTo(hsv_box);

            this.color_hsv = $('<div \/>').addClass("ce-pos-abs ce-color-hsv ce-color-hsv-dim").appendTo(hsv_box);

            this.marker_h = $('<div \/>').addClass("ce-pos-abs ce-marker").appendTo(hsv_box);

            this.marker_sv = this.marker_h.clone().appendTo(hsv_box);

            var default_colors = $('<div class="ce-default-color-box ui-widget-content ui-corner-all" \/>').on("click", "img", function(event){that._updateTagBG(event)}).appendTo(this.color_box);

            this._makeDefaultColors();

            var prev_box = $('<div \/>').addClass("ce-pos-abs ce-prev-box").appendTo(this.color_box);

            $('<input type="text" value="" size="6" maxlength="6" \/>').addClass('ce-pos-abs ce-in-hex').appendTo(prev_box);

            this.color_curent = $('<div \/>').addClass("ce-pos-abs ce-color-curent ui-widget-content ui-corner-right").click( function(event) {that._updateTagBG(event)}).appendTo(prev_box);

            $('<div \/>').addClass("ce-pos-abs ce-bg-color-change ui-widget-content ui-corner-left").appendTo(prev_box);

            var slider_ul = $('<ul \/>').addClass("ce-pos-abs").appendTo(this.color_box);

            this.a_rgb = new Array("red","lime","blue");
            this.a_sv = new Array("saturation","brightness");

            var n = this.a_rgb.concat(this.a_sv);
            var max_value = 255;
            for(i = 0; i < n.length; i++) {
                if(n[i] == "saturation" || n[i] == "brightness")
                    max_value = 100;
                this["s_"+n[i]] = $('<div \/>').addClass("ce-slide").slider({
                        range:"min",min:0,max:max_value,value:0,
                        slide: function(event, ui) {
                            switch ($(this).data("art")) {
                                case "red":
                                    that.hsv = that._RGBtoHSV([ui.value,that.s_lime.slider('value'),that.s_blue.slider('value')]);
                                    break;
                                case "lime":
                                    that.hsv = that._RGBtoHSV([that.s_red.slider('value'),ui.value,that.s_blue.slider('value')]);
                                    break;
                                case "blue":
                                    that.hsv = that._RGBtoHSV([that.s_red.slider('value'),that.s_lime.slider('value'),ui.value]);
                                    break;
                                case "saturation":
                                    that.hsv = [that.hsv[0],(ui.value / 100),that.hsv[2]];
                                    break;
                                case "brightness":
                                    that.hsv = [that.hsv[0],that.hsv[1],(ui.value / 100)];
                                    break;
                            }
                            that._updateDisplay();
                        }
                    }).data("art",n[i]);
                if(n[i] == "saturation" || n[i] == "brightness") {
                    this["s_"+n[i]].css("background-image","url("+img_url+n[i]+".png)").find('.ui-slider-range').css("background","transparent");
                    if(n[i] == "saturation")
                        this["s_"+n[i]].prepend('<img src="'+img_url+'black.png" style="position:absolute;width:100px" class="ui-corner-all ui-slider-horizontal" \/>');
                } else {
                    this["s_"+n[i]].find('.ui-slider-range').css("background",n[i]);
                    this["s_"+n[i]].find('.ui-slider-handle').css("border-color",n[i]);
                }
                this["i_"+n[i]] = $('<input type="text" value="0" size="3" maxlength="3" \/>')
                    .addClass("ce-value").data("max_value",max_value)
                    .bind({
                        keyup: function(event) {that._checkDezValue(event);},
                        focusout: function() {$(this).val(that._getInputDez($(this)));}
                    });
                $('<li \/>').append(this["s_"+n[i]]).append(this["i_"+n[i]]).appendTo(slider_ul);
                if($().spinner)
                    this["i_"+n[i]].spinner({max:max_value,min:0,value:0,
                        spin: function(event,ui) { that._checkDezValue(event); }
                    });
            }

            if($().spinner)
                var hsv_box_right = this.s_red.outerWidth(true) + this.i_red.parent().outerWidth(true);
            else
                var hsv_box_right = this.s_red.outerWidth(true) + this.i_red.outerWidth(true);

            prev_box.width(hsv_box_right);
            slider_ul.width(hsv_box_right);
            hsv_box.css({"margin-right":hsv_box_right+"px"});

            $('.ce-default-color-box').width((194 + hsv_box_right) - (default_colors.outerWidth() - default_colors.width()));
            var color_box_width = this.color_box.outerWidth(true);

            this.color_box.width(color_box_width).css("float","none");

            this.color_box.dialog({
                autoOpen: false,
                width: color_box_width,
                modal: false,
                resizable: false,
                show: anim_speed,
                title:mozilo_lang["dialog_title_coloredit"],
                dialogClass: "mo-shadow",
                create: function(event, ui) {
                    $(this).parents('.ui-dialog').find('.ui-dialog-titlebar').prepend(mo_docu_coloredit);
                }
            });

            this.hsv = this._HEXtoHSV(this._getInputHex(this.element.siblings('input')));
            this.color_curent.css('background-color', "#"+this._HSVtoHEX(this.hsv));
            this.color_curent.attr('title',this._HSVtoHEX(this.hsv));
            this._updateDisplay();
            this._bindEvents();
        },

        _makeDefaultColors: function() {
            var html = "";
            if(defaultcolors.length > 1) {
                defaultcolors_a = defaultcolors.split(",");
                for (var i = 0; i < defaultcolors_a.length; ++i) {
                    html += '<img title="'+defaultcolors_a[i]+'" class="ce-default-color-img ui-widget-content ui-corner-all" style="background-color:#'+defaultcolors_a[i]+';" src="'+ICON_URL_SLICE+'" \/>';
                }
                $('.ce-default-color-box').html(html);
            } else
                $('#ce-colorchange .ce-default-color-box').hide();
        },

        _bindEvents : function() {
            var that = this;
            $('.js-coloreditor-button').on({
                click: function(event) {
                    if(that.color_box.dialog("isOpen")) {
                        that.color_box.dialog("close");
                    } else {
                        that.color_curent.css('background-color', "#"+that._HSVtoHEX(that.hsv)).attr('title',that._HSVtoHEX(that.hsv));
                        that.color_box.dialog({position:{my:"right top",at:"left bottom",of:this}});
                        that.color_box.dialog("open");
                    }
                }
            });
            $('.ce-in-hex').on({
                keyup: function(event) {that._checkHexValue(event)},
                focusout: function(){
                    $(this).val(that._getInputHex($(this)));
                }
            });
        },
        _ifInColorRange : function(event) {
            var offset = this.color_hsv.offset(),
                var_top = event.pageY - offset.top - 24,
                pos = this._getRingCenter(event),
                angle = Math.atan2(pos.x, -pos.y);
            pos.x = Math.abs(pos.x);
            pos.y = Math.abs(pos.y);

            if(pos.x <= (126 / 2) && var_top >= 0 && var_top <= 109 && pos.x < (var_top * 0.5779)) {
                return "triangle";
            }
            if(Math.abs(Math.sin(angle) * 93) > pos.x && Math.abs(Math.sin(angle) * 73) < pos.x && Math.abs(Math.cos(angle) * 93) > pos.y && Math.abs(Math.cos(angle) * 73) < pos.y) {
                return "ring";
            }
            return false;
        },
        _moveCursor : function(event) {
            if (this.dragMode == "ring") {
                var pos = this._getRingCenter(event),
                    H = Math.atan2(pos.x, -pos.y) / 6.28;
                if (H < 0) H += 1;
                this.hsv[0] = H;
            } else if(this.dragMode == "triangle") {
                var offset = this.color_hsv.offset(),
                    var_top = 109 - (event.pageY - offset.top - 24),
                    var_left = event.pageX - offset.left - 34;
                this._XYtoSV(Math.round(var_left), Math.round(var_top));
            } else
                return;
            this._updateDisplay();
        },
        _XYtoSV : function(left,top) {
            var S = 0;
            if(top > 0)
                S = top / 0.8660;
            var tri_len = left + (S * 0.5);
            if(S > 0)
                S = S / tri_len;
            if(S > 1)
                tri_len = top / 0.8660;
            var V = tri_len / 126;
            this.hsv[1] = Math.max(0, Math.min(1, S));
            this.hsv[2] = Math.max(0, Math.min(1, V));
        },
        _SVtoXY : function() {
            return [(((126 * this.hsv[2]) * this.hsv[1]) * 0.8660),((126 * this.hsv[2]) - (((126 * this.hsv[2]) * this.hsv[1]) * 0.5))];
        },

        _updateDisplay : function() {
            var angle = this.hsv[0] * 6.28,
                top_left = this._SVtoXY(),
                rgb = this._HSVtoRGB(this.hsv),
                hex = this._RGBtoHEX(rgb);
            this.marker_h.css({
                left: Math.round(Math.sin(angle) * (93 - (20 / 2)) + 194 / 2) + 'px',
                top: Math.round(-Math.cos(angle) * (93 - (20 / 2)) + 194 / 2) + 'px'
            });
            this.marker_sv.css({
                top : ((109 - top_left[0]) + 24) + 'px',
                left : (top_left[1] + 34) + 'px'
            });
            this.color_sv.css("backgroundColor", "#"+this._HSVtoHEX([this.hsv[0], 1, 1]));
            var i, f_length = this.a_rgb.length;
            for(i = 0; i < f_length; i++) {
                if($('a:not(.ui-state-active)',this["s_"+this.a_rgb[i]]).length > 0)
                    this["s_"+this.a_rgb[i]].slider("value", rgb[i]);
                this["i_"+this.a_rgb[i]].not(':focus').val(rgb[i]);
            }
            f_length = this.a_sv.length;
            for(i = 0; i < f_length; i++) {
                if($('a:not(.ui-state-active)',this["s_"+this.a_sv[i]]).length > 0)
                    this["s_"+this.a_sv[i]].slider("value", this.hsv[i + 1] * 100);
                this["i_"+this.a_sv[i]].not(':focus').val(Math.round(this.hsv[i + 1] * 100));
            }
            this.s_brightness.css("backgroundColor", "#"+this._HSVtoHEX([this.hsv[0], this.hsv[1], 1]));
            this.s_saturation.css("backgroundColor", "#"+this._HSVtoHEX([this.hsv[0], 1, this.hsv[2]]))
            .find('img').css("opacity", (1 - this.hsv[2]));

            $('.ce-in-hex:not(:focus)').val(hex);
            $('.ce-bg-color-change').css({
                backgroundColor: "#"+hex,
                color: this._TextColorInput()
            }).not(':focus').val(hex);
        },
        _TextColorInput : function(event) {
            var t_c = '#000';
            if((this.hsv[0] > 0.57 && this.hsv[0] <= 1) || (this.hsv[0] >= 0 && this.hsv[0] < 0.083))
                t_c = '#fff';
            if(this.hsv[2] < 0.7)
                t_c = '#fff';
            if(this.hsv[2] > 0.7 && this.hsv[1] < 0.45)
                t_c = '#000';
            return t_c;
        },
        _checkHexValue : function(event) {
            checkHexValue(event);
            this.hsv = this._HEXtoHSV(this._getInputHex($(event.target)));
            this._updateDisplay();
        },
        _checkDezValue : function(event) {
            checkDezValue(event);
            if($(event.target).data("max_value") == 100)
                this.hsv = [this.hsv[0],(this._getInputDez(this.i_saturation) / 100),(this._getInputDez(this.i_brightness) / 100)];
            else
                this.hsv = this._RGBtoHSV([(this._getInputDez(this.i_red)),(this._getInputDez(this.i_lime)),(this._getInputDez(this.i_blue))]);
            this._updateDisplay();
        },
        _updateTagBG : function(event) {
            var new_color = $(event.target).css('background-color');
            if($(event.target).attr('title'))
                new_color = $(event.target).attr('title');
            if(new_color.substr(0, 3) == "rgb") {
                new_color = new_color.replace(/rgb\(/, "").replace(/rgba\(/, "").replace(/\)/, "").replace(/\ /g, "");
                new_color = new_color.split(",");
                this.hsv = this._RGBtoHSV([new_color[0],new_color[1],new_color[2]]);
            } else
                this.hsv = this._HEXtoHSV(new_color);
            this._updateDisplay();
        },

        _getRingCenter : function(event) {
            var offset = this.color_hsv.offset();
            return { x: (event.pageX - offset.left) - 194 / 2, y: (event.pageY - offset.top) - 194 / 2 };
        },
        _getInputDez : function(el) {
            var v = el.val();
            return (v == "" ? 0 : Math.min(Math.max(v ,0), Number(el.data("max_value"))));
        },
        _getInputHex : function(el) {
            var v = el.val().toUpperCase().replace(/[^A-F0-9]/g,"");
            return v+("000000".substr((Math.min(v.length,6))));
        },

        _HEXtoRGB : function(hex) {
            return [parseInt('0x' + hex.substring(0, 2)),
                    parseInt('0x' + hex.substring(2, 4)),
                    parseInt('0x' + hex.substring(4, 6))];
        },

        _HEXtoHSV : function(hex) {
            return this._RGBtoHSV(this._HEXtoRGB(hex));
        },

        _RGBtoHSV : function(rgb) {
            var r = ( rgb[0] / 255 ),
                g = ( rgb[1] / 255 ),
                b = ( rgb[2] / 255 ),
                max = Math.max( r, g, b ),
                delta = max - Math.min( r, g, b ),
                H = 0,
                S = 0,
                V = max;
            if ( delta != 0 ) {
                S = delta / max;
                var del_R = ( ( ( max - r ) / 6 ) + ( delta / 2 ) ) / delta,
                    del_G = ( ( ( max - g ) / 6 ) + ( delta / 2 ) ) / delta,
                    del_B = ( ( ( max - b ) / 6 ) + ( delta / 2 ) ) / delta;
                if      ( r == max ) H = del_B - del_G;
                else if ( g == max ) H = ( 1 / 3 ) + del_R - del_B;
                else if ( b == max ) H = ( 2 / 3 ) + del_G - del_R;
                if ( H < 0 ) H += 1;
                if ( H > 1 ) H -= 1;
            }
            return [H, S, V];
        },
        _HSVtoRGB : function(hsv) {
            var H = hsv[0], S = hsv[1], V = hsv[2],
                R = V * 255,
                G = V * 255,
                B = V * 255;
            if ( S > 0 ) {
                var var_r, var_g, var_b,
                    var_h = H * 6;
                if ( var_h == 6 ) var_h = 0;
                var var_i = Math.floor( var_h ),
                    var_1 = V * ( 1 - S ),
                    var_2 = V * ( 1 - S * ( var_h - var_i ) ),
                    var_3 = V * ( 1 - S * ( 1 - ( var_h - var_i ) ) );

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
        _HSVtoHEX : function(hsv) {
            return this._RGBtoHEX(this._HSVtoRGB(hsv));
        },
        _RGBtoHEX : function(rgb) {
            return (("0"+rgb[0].toString(16)).slice(-2) + ("0"+rgb[1].toString(16)).slice(-2) + ("0"+rgb[2].toString(16)).slice(-2)).toUpperCase();
        }
    });

    if($('.js-coloreditor-button').length > 0)
        $('.js-coloreditor-button').coloreditor();
    else if($('#js-menu-config-default-color').length > 0) {
        $('#js-menu-config-default-color').parents('li').eq(0).hide();
        var hex = $('.ce-in-hex').val().toUpperCase().replace(/[^A-F0-9]/g,"");
        hex += "000000".substr((Math.min(hex.length,6)));
        $('.ce-bg-color-change').css({
            backgroundColor: "#"+hex,
            color: hex > '7F7F7F' ? '#000000' : '#FFFFFF'
        });
        $('.ce-in-hex').bind("keyup",function(event) {
            var hex = $(this).val().toUpperCase().replace(/[^A-F0-9]/g,"");
            hex += "000000".substr((Math.min(hex.length,6)));
            $('.ce-bg-color-change').css({
                backgroundColor: "#"+hex,
                color: hex > '7F7F7F' ? '#000000' : '#FFFFFF'
            });
        });
    }
});
