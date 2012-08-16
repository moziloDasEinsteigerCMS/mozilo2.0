(function($) {

$.fn.farbtastic = function (options) {
    $.farbtastic(this, options);
    return this;
};

$.farbtastic = function (container, callback) {
    var container = $(container).get(0);
    return container.farbtastic || (container.farbtastic = new $._farbtastic(container, callback));
};

$._farbtastic = function (container, callback) {
    // Store farbtastic object
    var fb = this;
    // Insert markup
    $(container).html('<table cellspacing="0" border="0" cellpadding="0" class="ui-widget-content ui-corner-all">'
        +'<tbody>'
            +'<tr>'
                +'<td rowspan="2">'
                    +'<div class="farbtastic">'
                        +'<div class="fb-color"></div>'
                        +'<div class="fb-wheel"></div>'
                        +'<div class="fb-overlay"></div>'
                        +'<div class="fb-h-marker fb-marker"></div>'
                        +'<div class="fb-sl-marker fb-marker"></div>'
                    +'</div>'
                +'</td>'
                +'<td colspan="3" class="fb-td-prev">'
                    +'<input class="fb-in-hex" type="text" name="red" value="" size="6" maxlength="6">'
                    +'<div class="fb-color-curent ui-corner-right"></div>'
                    +'<div class="fb-color-prev ui-corner-left"></div>'
                +'</td>'
            +'</tr>'
            +'<tr>'
                +'<td class="fb-td-slider">'
                    +'<div class="fb-red"></div>'
                    +'<input class="fb-in-red" type="text" name="red" value="255" size="3" maxlength="3">'
                +'</td>'
                +'<td class="fb-td-slider">'
                    +'<div class="fb-green"></div>'
                    +'<input class="fb-in-green" type="text" name="red" value="255" size="3" maxlength="3">'
                +'</td>'
                +'<td class="fb-td-slider">'
                    +'<div class="fb-blue"></div>'
                    +'<input class="fb-in-blue" type="text" name="red" value="255" size="3" maxlength="3">'
                +'</td>'
            +'</tr>'
            +'<tr>'
                +'<td colspan="4" class="fb-td-default"><div class="fb-default-color">'
//                    +fb.get_default_color
//                    +'<img src="'+URL_BASE+ADMIN_DIR_NAME+'/gfx/clear.gif" />'
//                    +'<img src="'+URL_BASE+ADMIN_DIR_NAME+'/gfx/clear.gif" />'
//                    +'<img src="'+URL_BASE+ADMIN_DIR_NAME+'/gfx/clear.gif" />'
                +'</div></td>'
            +'</tr>'
        +'</tbody>'
    +'</table>');

    var e = $('.farbtastic', container);
    fb.wheel = $('.fb-wheel', container).get(0);
    // Dimensions
    fb.radius = 84;
    fb.square = 100;
    fb.width = 194;

    // Fix background PNGs in IE6
    if (navigator.appVersion.match(/MSIE [0-6]\./)) {
        $('*', e).each(function () {
            if (this.currentStyle.backgroundImage != 'none') {
                var image = this.currentStyle.backgroundImage;
                image = this.currentStyle.backgroundImage.substring(5, image.length - 2);
                $(this).css({
                    'backgroundImage': 'none',
                    'filter': "progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true, sizingMethod=crop, src='" + image + "')"
                });
            }
        });
    }

    /* Link to the given element(s) or callback. */
    fb.linkTo = function (callback) {
        // Unbind previous nodes
        if (typeof fb.callback == 'object') {
            $(fb.callback).unbind('keyup', fb.updateValue);
        }
        // Reset color
        fb.color = null;
        // Bind callback or elements
        if (typeof callback == 'function') {
            fb.callback = callback;
        }
        else if (typeof callback == 'object' || typeof callback == 'string') {
            fb.callback = $(callback);
            fb.callback.bind('keyup', fb.updateValue);
            if (fb.callback.get(0).value) {
                fb.setColor(fb.callback.get(0).value);
            }
        }
        return this;
    };

    fb.updateValue = function (event) {
        this.value = this.value.toUpperCase().replace(/[^A-F0-9]/g,"");
        if (this.value && this.value != fb.color) {
            fb.setColor(this.value);
        }
    };

    /* Change color not with HTML syntax 123456 */
    fb.setColor = function (color) {
        color = color.toUpperCase();
        var unpack = fb.unpack(color);
        if (fb.color != color && unpack) {
            fb.color = color;
            fb.rgb = unpack;
            fb.hsl = fb.RGBToHSL(fb.rgb);
            fb.updateDisplay();
        }
        return this;
    };

    /* Change color with HSL triplet [0..1, 0..1, 0..1] */
    fb.setHSL = function (hsl) {
        fb.hsl = hsl;
        fb.rgb = fb.HSLToRGB(hsl);
        fb.color = fb.pack(fb.rgb);
        fb.updateDisplay();
        return this;
    };

    /* Retrieve the coordinates of the given event relative to the center of the widget. */
    fb.widgetCoords = function (event) {
        var offset = $(fb.wheel).offset();
        return { x: (event.pageX - offset.left) - fb.width / 2, y: (event.pageY - offset.top) - fb.width / 2 };
    };

    /* Mousedown handler */
    fb.mousedown = function (event) {
        // Capture mouse
        if (!document.dragging) {
            $(document).bind('mousemove', fb.mousemove).bind('mouseup', fb.mouseup);
            document.dragging = true;
        }
        // Check which area is being dragged
        var pos = fb.widgetCoords(event);
        fb.circleDrag = Math.max(Math.abs(pos.x), Math.abs(pos.y)) * 2 > fb.square;
        // Process
        fb.mousemove(event);
        return false;
    };

    /* Mousemove handler */
    fb.mousemove = function (event) {
        // Get coordinates relative to color picker center
        var pos = fb.widgetCoords(event);
        // Set new HSL parameters
        if (fb.circleDrag) {
            var hue = Math.atan2(pos.x, -pos.y) / 6.28;
            if (hue < 0) hue += 1;
            fb.setHSL([hue, fb.hsl[1], fb.hsl[2]]);
        }
        else {
            var sat = Math.max(0, Math.min(1, -(pos.x / fb.square) + .5));
            var lum = Math.max(0, Math.min(1, -(pos.y / fb.square) + .5));
            fb.setHSL([fb.hsl[0], sat, lum]);
        }
        return false;
    };

    /* Mouseup handler */
    fb.mouseup = function () {
        // Uncapture mouse
        $(document).unbind('mousemove', fb.mousemove);
        $(document).unbind('mouseup', fb.mouseup);
        document.dragging = false;
    };

    /* Update the markers and styles */
    fb.updateDisplay = function () {
        // Markers
        var angle = fb.hsl[0] * 6.28;
        $('.fb-h-marker', e).css({
            left: Math.round(Math.sin(angle) * fb.radius + fb.width / 2) + 'px',
            top: Math.round(-Math.cos(angle) * fb.radius + fb.width / 2) + 'px'
        });
        $('.fb-sl-marker', e).css({
            left: Math.round(fb.square * (.5 - fb.hsl[1]) + fb.width / 2) + 'px',
            top: Math.round(fb.square * (.5 - fb.hsl[2]) + fb.width / 2) + 'px'
        });
        var r = Math.round(fb.rgb[0] * 255),
            g = Math.round(fb.rgb[1] * 255),
            b = Math.round(fb.rgb[2] * 255);
        $('.fb-red').slider('value', r);
        $('.fb-green').slider('value', g);
        $('.fb-blue').slider('value', b);
        $('.fb-in-red').val(r);
        $('.fb-in-green').val(g);
        $('.fb-in-blue').val(b);
        $('.fb-color-prev').css('background-color', "#"+fb.color );
        $('.fb-in-hex').val(fb.color);
        // Saturation/Luminance gradient
        $('.fb-color', e).css('backgroundColor', "#"+fb.pack(fb.HSLToRGB([fb.hsl[0], 1, 0.5])));
        // Linked elements or callback
        if (typeof fb.callback == 'object') {
            // Set background/foreground color
            $(fb.callback).css({
                backgroundColor: "#"+fb.color,
                color: fb.hsl[2] > 0.5 ? '#000' : '#fff'
            });
            // Change linked value
            $(fb.callback).each(function() {
                if (this.value && this.value != fb.color) {
                    this.value = fb.color;
                }
            });
        }
        else if (typeof fb.callback == 'function') {
            fb.callback.call(fb, fb.color);
        }
    };

    /* Various color utility functions */
    fb.pack = function (rgb) {
        var r = Math.round(rgb[0] * 255);
        var g = Math.round(rgb[1] * 255);
        var b = Math.round(rgb[2] * 255);
        return (r < 16 ? '0' : '') + r.toString(16).toUpperCase() +
            (g < 16 ? '0' : '') + g.toString(16).toUpperCase() +
            (b < 16 ? '0' : '') + b.toString(16).toUpperCase();
    };

    fb.unpack = function (color) {
        if (color.length == 6 && color.search(/[A-Fa-f0-9]{6}/) != -1) {
            return [parseInt('0x' + color.substring(0, 2)) / 255,
                parseInt('0x' + color.substring(2, 4)) / 255,
                parseInt('0x' + color.substring(4, 6)) / 255];
        }
    };

    fb.HSLToRGB = function (hsl) {
        var m1, m2, r, g, b;
        var h = hsl[0], s = hsl[1], l = hsl[2];
        m2 = (l <= 0.5) ? l * (s + 1) : l + s - l*s;
        m1 = l * 2 - m2;
        return [this.hueToRGB(m1, m2, h+0.33333),
            this.hueToRGB(m1, m2, h),
            this.hueToRGB(m1, m2, h-0.33333)];
    };

    fb.hueToRGB = function (m1, m2, h) {
        h = (h < 0) ? h + 1 : ((h > 1) ? h - 1 : h);
        if (h * 6 < 1) return m1 + (m2 - m1) * h * 6;
        if (h * 2 < 1) return m2;
        if (h * 3 < 2) return m1 + (m2 - m1) * (0.66666 - h) * 6;
        return m1;
    };

    fb.RGBToHSL = function (rgb) {
        var min, max, delta, h, s, l;
        var r = rgb[0], g = rgb[1], b = rgb[2];
        min = Math.min(r, Math.min(g, b));
        max = Math.max(r, Math.max(g, b));
        delta = max - min;
        l = (min + max) / 2;
        s = 0;
        if (l > 0 && l < 1) {
            s = delta / (l < 0.5 ? (2 * l) : (2 - 2 * l));
        }
        h = 0;
        if (delta > 0) {
            if (max == r && max != g) h += (g - b) / delta;
            if (max == g && max != b) h += (2 + (b - r) / delta);
            if (max == b && max != r) h += (4 + (r - g) / delta);
            h /= 6;
        }
        return [h, s, l];
    };

    fb.hexFromRGB = function (r, g, b) {
        var hex = [r.toString(16),g.toString(16),b.toString(16)];
        $.each(hex, function(nr, val) {
            if (val.length === 1) {
                hex[nr] = "0" + val;
            }
        });
        return hex.join("").toUpperCase();
    };

    // Install mousedown handler (the others are set on the document on-demand)
    $('*', e).mousedown(fb.mousedown);

    // Init color
    fb.setColor('FF0000');

    // Set linked elements/callback
    if (callback) {
        fb.linkTo(callback);
    }

    $('.fb-color-curent').css('background-color',"#"+$(callback).css('background-color'));

    $('.fb-in-hex').bind('keyup', fb.updateValue);
    $('.fb-color-curent').bind('click', function() {
        var new_color = $(this).css('background-color');
        if(new_color.substr(0, 3) == "rgb") {
            new_color = new_color.replace(/rgb\(/, "").replace(/rgba\(/, "").replace(/\)/, "").replace(/\ /g, "");
            new_color = new_color.split(",");
            new_color = fb.hexFromRGB(parseInt(new_color[0]), parseInt(new_color[1]), parseInt(new_color[2]));
        }
        fb.setColor(new_color);
    });

    if(defaultcolors.length > 1) {
        $('.fb-default-color').css('display','block');
        defaultcolors = defaultcolors.split(",");
        for (var i = 0; i < defaultcolors.length; ++i) {
            $('.fb-default-color').append("<img style=\"background-color:#"+defaultcolors[i]+";\" src=\""+URL_BASE+ADMIN_DIR_NAME+"/gfx/clear.gif\" />");
        }
    }

    $('.fb-default-color img').bind('click', function() {
        var new_color = $(this).css('background-color');
        if(new_color.substr(0, 3) == "rgb") {
            new_color = new_color.replace(/rgb\(/, "").replace(/rgba\(/, "").replace(/\)/, "").replace(/\ /g, "");
            new_color = new_color.split(",");
            new_color = fb.hexFromRGB(parseInt(new_color[0]), parseInt(new_color[1]), parseInt(new_color[2]));
        }
        fb.setColor(new_color);
    });

    $('.fb-in-red, .fb-in-green, .fb-in-blue').bind('keyup', function() {
        fb.setColor(fb.hexFromRGB(parseInt($('.fb-in-red').val()), parseInt($('.fb-in-green').val()), parseInt($('.fb-in-blue').val())));
    });

    $('.fb-red, .fb-green, .fb-blue').slider({
        orientation: "vertical",
        range: "min",
        min: 0,
        max: 255,
        value: 127,
        slide: function(event, ui) {
            var red = $('.fb-red').slider('value'),
                green = $('.fb-green').slider('value'),
                blue = $('.fb-blue').slider('value');
            if($(this).hasClass("fb-red"))
                red = ui.value;
            else if($(this).hasClass("fb-green"))
                green = ui.value;
            else if($(this).hasClass("fb-blue"))
                blue = ui.value;
            fb.setColor(fb.hexFromRGB(red, green, blue))
        },
    });
};

})(jQuery);
