function menuSubsIsMobile() {
    // match von https://github.com/pinceladasdaweb/isMobile
    if(navigator.userAgent.match(/android|webos|ip(hone|ad|od)|opera (mini|mobi|tablet)|iemobile|windows.+(phone|touch)|mobile|fennec|kindle (Fire)|Silk|maemo|blackberry|playbook|bb10\; (touch|kbd)|Symbian(OS)|Ubuntu Touch/i))
        return true;
    return false;
}

function menuSubsIsOperaMini() {
    if(menuSubsIsMobile() && navigator.userAgent.match(/opera (mini|mobi|tablet)/i))
        return true;
    return false;
}

function menuSubsToggleContent() {
    for(var z = 0; z < ms_hidden.length; z++) {
        var id = ms_hidden[z]["id"],
            para = ms_hidden[z]["para"],
            val = ms_hidden[z]["val"],
            elem = document.getElementById(id);
        if(!elem)
            continue;
        var s = new RegExp(para+"(\ )*:(\ )*"+val+"(\ )*(;)?"),
            b = new RegExp(para+"(\ )*\:(\ )*([^;]+)([a-z-])+(;)?"),
            string = elem.getAttribute("style"),
            safe_style_value = elem.getAttribute("data-menusubs");
        if(!string)
            string = "";
        else if(string.lastIndexOf(";") < (string.length -1))
            string = string+";";
        // die function wird das erste mal aufgerufen data-menusubs= setzen
        if(!safe_style_value) {
            safe_style_value = string.match(b);
            // gibts schonn diesen "para" dann speichern
            if(safe_style_value)
                safe_style_value = safe_style_value[0];
            else
                safe_style_value = "false";
            elem.setAttribute("data-menusubs",safe_style_value);
        }

        // "para" mit "val" ist gesetzt dann ersetzen mit "safe_style_value"
        if(s.test(string))
            string = string.replace(b, safe_style_value.replace(/false/, ""));
        // "para" gibts dann ersetzen mit "para" : "val"
        else if(b.test(string))
            string = string.replace(b, para+":"+val+";");
        // "para" gibts nicht dann hinzufügen von "para" : "val"
        else
            string += para+":"+val+";";
        elem.setAttribute("style",string);
    }
    return true;
}

function menuSubsOperaMiniStart() {
    if(menuSubsIsOperaMini())
        document.getElementById("menusubs-cats").setAttribute("class", document.getElementById("menusubs-cats").getAttribute("class") + " menusubs-is-operamini");
}

function menuSubsWaitForPageLoad() {
    if(document.readyState != "complete") {
        window.setTimeout("menuSubsWaitForPageLoad()", 100);
        return false;
    }
    menuSubsOperaMiniStart();
}
menuSubsWaitForPageLoad();
