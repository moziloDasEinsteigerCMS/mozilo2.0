<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

// Anzeige der Editieransicht
function showEditPageForm()    {
    global $CMS_CONF;

    // Anzeige der Formatsymbolleiste, wenn die CMS-Syntax aktiviert ist
    $toolbar = NULL;
    if ($CMS_CONF->get("usecmssyntax") == "true" or ACTION == "config") {
        $display = "";
        if ($CMS_CONF->get("usecmssyntax") != "true")
            $display = "display:none;";
        $toolbar = '<div id="js-editor-toolbar" style="padding-top:1px;padding-bottom:1px;'.$display.'">'.returnFormatToolbar().'</div>';
    }
    $content = '<div id="pageedit-box-inhalt" style="height:100%;width:100%;">'
                .$toolbar
    .'<div id="ace-menu-box" class="ui-widget-header ui-corner-top" style="border-bottom-width:0;">'

    .'<table class="" width="100%" cellspacing="0" border="0" cellpadding="0"><tr>'
    .'<td width="1%" class="mo-nowrap">'
            .'<img id="show_gutter" class="ed-ace-icon ed-icon-border ed-syntax-icon ed-number" src="'.ICON_URL_SLICE.'" alt="number" title="'.getLanguageValue("toolbar_editor_linenumber",true).'" hspace="0" vspace="0" />'
            .'<img id="show_hidden" class="ed-ace-icon ed-icon-border ed-syntax-icon ed-noprint" src="'.ICON_URL_SLICE.'" alt="noprint" title="'.getLanguageValue("toolbar_editor_controlcharacter",true).'" hspace="0" vspace="0" />'
    .'</td>'
    .'<td width="1%" class="mo-ace-td-select">'
            .'<div><select name="select-mode" title="'.getLanguageValue("toolbar_editor_highlighter",true).'" id="select-mode" class="mo-ace-in-select js-ace-select">'
                .'<option value="mozilo">'."Mozilo".'</option>'
                .'<option value="text">'."Text".'</option>'
                .'<option value="css">'."CSS".'</option>'
                .'<option value="html">'."HTML".'</option>'
                .'<option value="javascript">'."JavaScript".'</option>'
                .'<option value="php">'."PHP".'</option>'
            ."</select></div>"
    .'</td>'
    .'<td width="1%" class="mo-ace-td-select">'
            .'<div><select name="select-fontsize" title="'.getLanguageValue("toolbar_editor_fontsize",true).'" id="select-fontsize" class="mo-ace-in-select js-ace-select">'
                .'<option value="10px">'."10px".'</option>'
                .'<option value="12px">'."12px".'</option>'
                .'<option value="14px">'."14px".'</option>'
                .'<option value="16px">'."16px".'</option>'
                .'<option value="18px">'."18px".'</option>'
            ."</select></div>"
    .'</td>'
    .'<td width="1%" class="mo-nowrap">'
            .'<img id="undo" class="ed-ace-icon ed-syntax-icon ed-undo" src="'.ICON_URL_SLICE.'" alt="undo"  title="'.getLanguageValue("toolbar_editor_undo",true).'"hspace="0" vspace="0" />'
            .'<img id="redo" class="ed-ace-icon ed-syntax-icon ed-redo" src="'.ICON_URL_SLICE.'" alt="redo"  title="'.getLanguageValue("toolbar_editor_redo",true).'"hspace="0" vspace="0" />'

            .'<img id="toggle_fold" class="ed-ace-icon ed-syntax-icon ed-expand" src="'.ICON_URL_SLICE.'" alt="expand" title="'.getLanguageValue("toolbar_editor_togglefold",true).'" hspace="0" vspace="0" />'
    .'</td>'
    .'<td width="1%" id="colordiv-editor" class="mo-nowrap">';
    if ($CMS_CONF->get("usecmssyntax") != "true" and ACTION != "config")
        $content .= returnToolbarColoredit();
    $content .= '</td>'
    .'<td width="1%" class="mo-nowrap">'
            .'<input class="mo-ace-in-text" id="search-text" type="text" name="search-text" value="" />'
            .'<img id="search" class="ed-ace-icon ed-syntax-icon ed-find" src="'.ICON_URL_SLICE.'" alt="find"  title="'.getLanguageValue("toolbar_editor_search",true).'"hspace="0" vspace="0" />'
            .'<input class="mo-ace-in-check" type="checkbox" id="search-all" title="'.getLanguageValue("toolbar_editor_searchall",true).'" />'
            .'<label class="mo-ace-in-check-label" for="search-all">'.getLanguageValue("toolbar_editor_textall",true).'</label>'
            .'<input class="mo-ace-in-text" id="replace-text" type="text" name="search" value="" />'
            .'<img id="replace" class="ed-ace-icon ed-syntax-icon ed-replace" src="'.ICON_URL_SLICE.'" alt="replace" title="'.getLanguageValue("toolbar_editor_replace",true).'" hspace="0" vspace="0" />'
    .'</td>'
    ."</tr>"
    ."</table>"
    .'</div>'
    .'<div id="pagecontent-border" style="position:relative;overflow:hidden;" class="ui-widget-content">'
        .'<div id="pagecontent"></div>'
    .'</div>'
.'</div>';

    $subnav = false;
    if(ACTION == "config")
        $subnav = "editusersyntax";
    elseif(ACTION == "template")
        $subnav = "template";

    $content .= getHelpIcon("editsite",$subnav);
    return $content;
}

function returnFormatToolbar() {
    global $CMS_CONF;
    global $USER_SYNTAX;

    $content = '<table class="mo-menue-row-bottom mo-menue-row-top" width="100%" cellspacing="0" border="0" cellpadding="0">'
    ."<tr>"
    // Syntaxelemente
    .'<td width="50%" class="mo-nowrap">'
    .returnFormatToolbarIcon("link")
    .returnFormatToolbarIcon("mail")
    .returnFormatToolbarIcon("seite")
    .returnFormatToolbarIcon("kategorie")
    .returnFormatToolbarIcon("datei")
    .returnFormatToolbarIcon("bild")
    .returnFormatToolbarIcon("bildlinks")
    .returnFormatToolbarIcon("bildrechts")
    .returnFormatToolbarIcon("absatz")
    .returnFormatToolbarIcon("liste")
    .returnFormatToolbarIcon("numliste")
    .returnFormatToolbarIcon("tabelle")
    .returnFormatToolbarIcon("linie")
    .returnFormatToolbarIcon("html")
    .returnFormatToolbarIcon("include")
    ."</td>"
    // Textformatierung
    .'<td width="41%" class="mo-nowrap">'
    .returnFormatToolbarIcon("ueber1")
    .returnFormatToolbarIcon("ueber2")
    .returnFormatToolbarIcon("ueber3")
    .returnFormatToolbarIcon("links")
    .returnFormatToolbarIcon("zentriert")
    .returnFormatToolbarIcon("block")
    .returnFormatToolbarIcon("rechts")
    .returnFormatToolbarIcon("fett")
    .returnFormatToolbarIcon("kursiv")
    .returnFormatToolbarIcon("unter")
    .returnFormatToolbarIcon("durch")
    .returnFormatToolbarIcon("fontsize=0.8em")
    ."</td>"
    // Farben
    .'<td width="9%" id="colordiv-mozilo" class="mo-nowrap">'
        .returnToolbarColoredit()
    ."</td>"
    ."</tr>"
    ."</table>";


    // Smileys
    if(($user_icons = returnUserSyntaxIcons()) or $CMS_CONF->get("replaceemoticons") == "true") {
        $content .= '<table class="mo-menue-row-bottom" width="100%" cellspacing="0" border="0" cellpadding="0"><tr>';
        if($CMS_CONF->get("replaceemoticons") == "true")
            $content .= '<td class="mo-nowrap" width="2%">'.returnSmileyBar().'</td><td width="3%">&nbsp;</td>';
        if($user_icons)
            $content .= '<td class="mo-nowrap">'.$user_icons.'</td>';
        $content .= '</tr></table>';
    }


    $content .= '<table class="mo-menue-row-bottom" width="100%" cellspacing="0" border="0" cellpadding="0">'
    ."<tr>";
    # Template
    $template_title = NULL;
    $template_selectbox = "&nbsp;";
    if(ACTION == "template") {
        $template_title = "Template CSS und Bilder";
        $template_selectbox = returnTemplateSelectbox();
    }

    $content .= '<td style="width:22%;"><div class="mo-select-div">'.returnCatPagesSelectbox()."</div></td>"
    .'<td style="width:22%;"><div class="mo-select-div">'.returnFilesSelectbox()."</div></td>"
    .'<td style="width:22%;"><div class="mo-select-div">'.returnGalerySelectbox()."</div></td>"
   .'<td style="width:34%;">'.$template_selectbox."</td>"
    ."</tr>"
    ."</table>"

    .'<table class="mo-menue-row-bottom" width="100%" cellspacing="0" border="0" cellpadding="0">'
    .'<tr><td width="33%"><div class="mo-select-div">'.returnPluginSelectbox().'</div></td>'
    .'<td width="33%"><div class="mo-select-div">'.returnPlatzhalterSelectbox().'</div></td>'
     // Benutzerdefinierte Syntaxelemente
    .'<td width="34%">'.returnUserSyntaxSelectbox().'</td>'
    .'</tr></table>';
    return $content;
}

function returnToolbarColoredit() {
    $content = '<div id="js-color-menu" class="mo-nowrap">'
            .'<img class="ed-syntax-icon ed-icon-border ed-syntax-color ce-bg-color-change ed-farbe" alt="Farbe" title="[farbe=RRGGBB| ... ]" src="'.ICON_URL_SLICE.'" onclick="insert_ace(\'[farbe=\' + document.getElementById(\'farbcode\').value + \'|\', \']\',true)" />'
            .'<input type="text" maxlength="6" value="DD0000" class="ce-bg-color-change js-in-hex ce-in-hex" id="farbcode" size="6" />'
            .'<img class="js-coloreditor-button ed-icon-border ed-syntax-icon ed-farbeedit" alt="'.getLanguageValue("dialog_title_coloredit",true).'" title="'.getLanguageValue("dialog_title_coloredit",true).'" src="'.ICON_URL_SLICE.'" style="display:none;" />'
        .'</div>';
    return $content;
}

// Rueckgabe eines Standard-Formatsymbolleisten-Icons
function returnFormatToolbarIcon($tag) {
    if(strpos($tag,"=") > 0) {
        $tag = substr($tag,0,strpos($tag,"="));
        return '<img class="ed-syntax-icon ed-icon-border ed-'.$tag.'" alt="'.$tag.'" src="'.ICON_URL_SLICE.'" title="['.$tag.'=|...]" onclick="insert_ace(\'['.$tag.'=|\', \']\',true)" />';
    } elseif($tag == "tabelle")
        return '<img class="ed-syntax-icon ed-icon-border ed-'.$tag.'" alt="'.$tag.'" src="'.ICON_URL_SLICE.'" title="['.$tag.'|...]" onclick="insert_ace(\'['.$tag.'|\\n&lt;&lt; \', \' |  &gt;&gt;\\n&lt;  |  &gt;\\n]\',true)" />';
    elseif($tag == "linie")
        return '<img class="ed-syntax-icon ed-icon-border ed-'.$tag.'" alt="'.$tag.'" src="'.ICON_URL_SLICE.'" title="[----]" onclick="insert_ace(\'[----]\', false,false)" />';
    else
        return '<img class="ed-syntax-icon ed-icon-border ed-'.$tag.'" alt="'.$tag.'" src="'.ICON_URL_SLICE.'" title="['.$tag.'|...]" onclick="insert_ace(\'['.$tag.'|\', \']\',true)" />';
}


// Icons mit benutzerdefinierten Syntaxelementen
function returnUserSyntaxIcons() {
    global $USER_SYNTAX, $CatPage;
    $user_array = $USER_SYNTAX->toArray();
    $content = NULL;
    foreach($user_array as $key => $value) {
        if(array_key_exists($key.'___icon',$user_array)) {
            $inhalt = getUserSyntaxValueDescription($key,$value);
            $user_array[$key.'___icon'] = trim($user_array[$key.'___icon']);
            if(false !== strpos($user_array[$key.'___icon'],FILE_START)
                    and false !== strpos($user_array[$key.'___icon'],FILE_END)) {
                list($cat,$file) = $CatPage->split_CatPage_fromSyntax($user_array[$key.'___icon'],true);
                if($CatPage->exists_File($cat,$file))
                    $content .= '<input class="ed-syntax-user ed-icon-border" type="image" src="'.$CatPage->get_srcFile($cat,$file).'" title="'.$inhalt.'" value="'.$inhalt.'" />';
            } else
                $content .= '<button type="button" class="ed-syntax-user ed-icon-border" title="'.$inhalt.'" value="'.$inhalt.'">'.$user_array[$key.'___icon'].'</button>';
        }
    }
    return $content;
}

// Selectbox mit allen benutzerdefinierten Syntaxelementen
function returnUserSyntaxSelectbox() {
    global $USER_SYNTAX;

    $content = '<select name="usersyntax" class="usersyntaxselectbox" title="'.getLanguageValue("toolbar_usersyntax",true).'">';
    $user_array = $USER_SYNTAX->toArray();
    foreach($user_array as $key => $value) {
        if(array_key_exists($key.'___icon',$user_array)
                or false !== strpos($key,'___icon'))
            continue;
        $inhalt = getUserSyntaxValueDescription($key,$value);
        $content .= '<option value="'.$inhalt.'">'.$inhalt.'</option>';
    }
    $content .= "</select>";
    return $content;
}

function getUserSyntaxValueDescription($key,$value) {
    if(false !== strpos($value,"{DESCRIPTION}") and false === strpos($value,"{VALUE}")) {
        return "[".$key."=...|]";
    } elseif(false === strpos($value,"{DESCRIPTION}") and false !== strpos($value,"{VALUE}")) {
        return "[".$key."|...]";
    } elseif(false !== strpos($value,"{DESCRIPTION}") and false !== strpos($value,"{VALUE}")) {
        return "[".$key."=|...]";
    } elseif(false === strpos($value,"{DESCRIPTION}") and false === strpos($value,"{VALUE}")) {
        return (strlen($value) == 0) ? "[".$key."|...]" : "[".$key."]";
    }
    return NULL;
}

// Selectbox mit allen benutzerdefinierten Syntaxelementen
function returnPlatzhalterSelectbox() {
    global $specialchars;
    global $activ_plugins;
    $all = false;
    if(ACTION == "template" or ACTION == "config")
        $all = true;
    $selectbox = '<select name="platzhalter" class="overviewselect" title="'.getLanguageValue("toolbar_platzhalter",true).'">';
    if(ACTION == "config") {
        $selectbox .= '<option title="'.getLanguageValue("toolbar_platzhalter_VALUE",true).'" value="{VALUE}">{VALUE}</option>';
        $selectbox .= '<option title="'.getLanguageValue("toolbar_platzhalter_DESCRIPTION",true).'" value="{DESCRIPTION}">{DESCRIPTION}</option>';
    }
    foreach(makePlatzhalter($all) as $value) {
        $language = str_replace(array('{','}'),'',$value);
        if(in_array($language,$activ_plugins))
            continue;
        $selectbox .= '<option title="'.getLanguageValue("toolbar_platzhalter_".$language,true).'" value="'.$value.'">'.$value.'</option>';
    }
    $selectbox .= '</select>';
    return $selectbox;
}

// Selectbox mit allen Plugin Platzhaltern die nichts mit dem Template zu tun haben
function returnPluginSelectbox() {
    global $specialchars;
    global $activ_plugins;
    require_once(BASE_DIR_CMS."Plugin.php");
    $selectbox = '<select name="plugins" class="overviewselect" title="'.getLanguageValue("toolbar_plugins",true).'">';
    foreach($activ_plugins as $currentplugin) {
        if(file_exists(PLUGIN_DIR_REL.$currentplugin."/index.php") and file_exists(PLUGIN_DIR_REL.$currentplugin."/plugin.conf.php")) {
            require_once(PLUGIN_DIR_REL.$currentplugin."/index.php");
            $plugin = new $currentplugin();
            $plugin_info = $plugin->getInfo();
            // Plugin nur in der Auswahlliste zeigen, wenn es aktiv geschaltet ist
            $plugin_conf = new Properties(PLUGIN_DIR_REL.$currentplugin."/plugin.conf.php");
            if(isset($plugin_info[5]) and is_array($plugin_info[5])) {
                foreach($plugin_info[5] as $platzh => $info) {
                    $platzh = $specialchars->rebuildSpecialChars($platzh, false, true);
                    $platzhtext = str_replace("|}","|...}",$platzh);
                    $selectbox .= '<option title="'.$specialchars->rebuildSpecialChars($info, false, true).'" value="'.$platzh.'">'.$platzhtext.'</option>';
                }
            }
        }
    }
    $selectbox .= "</select>";
    return $selectbox;
}

// Smiley-Liste
function returnSmileyBar() {
    require_once(BASE_DIR_CMS."Smileys.php");
    $smileys = new Smileys(BASE_DIR_CMS."smileys");
    $content = "";
    foreach($smileys->getSmileysArray() as $icon => $emoticon) {
        $icon = trim($icon);
        $content .= '<img class="ed-smileys-icon ed-icon-border" alt=":'.$icon.':" title=":'.$icon.':" src="'.URL_BASE.CMS_DIR_NAME.'/smileys/'.$icon.'.gif" onclick="insert_ace(\' :'.$icon.': \', \'\',false)" />';
    }
    return $content;
}

function returnCatPagesSelectbox() {
    global $specialchars;
    global $CatPage;

    $select = '<select name="pages" class="overviewselect" title="'.getLanguageValue("category_button",true)." &#047; ".getLanguageValue("page_button",true).':">';
    foreach ($CatPage->get_CatArray(true,false) as $catdir) {
        $cleancatname = $CatPage->get_HrefText($catdir,false);
        $optgroup = "";
        foreach($CatPage->get_PageArray($catdir, array(EXT_PAGE,EXT_HIDDEN), true) as $file) {
            $cleanpagename = $CatPage->get_HrefText($catdir,$file);
            $label = NULL;
            if ($CatPage->get_Type($catdir,$file) == EXT_HIDDEN)
                $label = " (".getLanguageValue("page_saveashidden").")";
            $optgroup .= '<option value="'.$cleancatname.":".$cleanpagename.'">'.$cleanpagename.$label."</option>";
        }
        if(!empty($optgroup))
            $select .= '<optgroup label="'.$cleancatname.'">'.$optgroup.'</optgroup>';
    }
    $select .= "</select>";
    return $select;
}
function returnFilesSelectbox() {
    global $specialchars;
    global $CatPage;

    $select = '<select name="files" class="overviewselect" title="'.getLanguageValue("files_button",true).':">';
    foreach($CatPage->get_CatArray(true,false) as $catdir) {
        $cleancatname = $CatPage->get_HrefText($catdir,false);
        $optgroup = "";
        foreach($CatPage->get_FileArray($catdir) as $current_file) {
            $optgroup .= '<option value="'.$cleancatname.":".$specialchars->rebuildSpecialChars($current_file, true, true).'">'.$specialchars->rebuildSpecialChars($current_file, false, true)."</option>";
        }
        if(!empty($optgroup))
            $select .= '<optgroup label="'.$cleancatname.'">'.$optgroup.'</optgroup>';
    }
    $select .= "</select>";
    return $select;
}

function returnGalerySelectbox() {
    global $specialchars;
    $select = '<select name="gals" class="overviewselect" title="'.getLanguageValue("gallery_button",true).':">';
    $galleries = getDirAsArray(GALLERIES_DIR_REL,"dir","natcasesort");
    foreach ($galleries as $currentgallery) {
        $select .= '<option value="'.$specialchars->rebuildSpecialChars($currentgallery, false, false).'">'.$specialchars->rebuildSpecialChars($currentgallery, false, true)."</option>";
    }
    $select .= "</select>";
    return $select;
}

function returnTemplateSelectbox() {
    global $CMS_CONF;
    global $specialchars;

    $LAYOUT_DIR = BASE_DIR.LAYOUT_DIR_NAME."/".$CMS_CONF->get("cmslayout").'/';

    $selectbox = '<select name="template_css" class="overviewselect" title="'.getLanguageValue("toolbar_template",true).':">';
    $selectbox .= '<optgroup label="'.getLanguageValue("toolbar_template_css",true).'">';
    foreach(getDirAsArray($LAYOUT_DIR.'css',array(".css"),"natcasesort") as $file) {
        $selectbox .= '<option value="{LAYOUT_DIR}/css/'.$specialchars->replaceSpecialChars($file,true).'">'.$specialchars->rebuildSpecialChars($file, false, true).'</option>';
    }
    $selectbox .= '</optgroup>';
    $selectbox .= '<optgroup label="'.getLanguageValue("toolbar_template_image",true).'">';

    foreach(getDirAsArray($LAYOUT_DIR.'grafiken',"img","natcasesort") as $file) {
        $selectbox .= '<option value="{LAYOUT_DIR}/grafiken/'.$specialchars->replaceSpecialChars($file,true).'">'.$specialchars->rebuildSpecialChars($file, false, true).'</option>';
    }

    $selectbox .= '</optgroup>';
    $selectbox .= "</select>";
    return $selectbox;
}

?>