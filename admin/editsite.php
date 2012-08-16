<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

// Anzeige der Editieransicht
function showEditPageForm()    {
    global $CMS_CONF;

    // Anzeige der Formatsymbolleiste, wenn die CMS-Syntax aktiviert ist
    $toolbar = NULL;
    if ($CMS_CONF->get("usecmssyntax") == "true") {
        $toolbar = '<div id="js-editor-toolbar" style="padding-top:1px;padding-bottom:1px;">'.returnFormatToolbar().'</div>';
    }
# style="padding-top:1px;padding-bottom:1px;" ui-widget-content ui-state-active
    $content = '<div id="pageedit-box-inhalt" style="height:100%;width:100%;">'
                .$toolbar
    .'<div id="ace-menu-box" class="ui-widget-header ui-corner-top" style="border-bottom-width:0;">'

    .'<table class="" width="100%" cellspacing="0" border="0" cellpadding="0"><tr>'
    .'<td width="1%" class="mo-nowrap">'

#        .'<div style="float:left;white-space:nowrap;">'
            .'<img id="show_gutter" class="mo-tool-icon mo-ace-icon ui-state-default ui-corner-all" src="'.URL_BASE.ADMIN_DIR_NAME.'/gfx/ace/number.png" hspace="0" vspace="0" />'
            .'<img id="show_hidden" class="mo-tool-icon mo-ace-icon ui-state-default ui-corner-all" src="'.URL_BASE.ADMIN_DIR_NAME.'/gfx/ace/noprint.png" hspace="0" vspace="0" />'
    .'</td>'
    .'<td width="1%" class="mo-ace-td-select">'
            .'<div><select name="select-mode" id="select-mode" class="mo-ace-in-select js-ace-select">'
                .'<option value="mozilo">'."Mozilo".'</option>'
                .'<option value="text">'."Text".'</option>'
                .'<option value="css">'."CSS".'</option>'
                .'<option value="html">'."HTML".'</option>'
                .'<option value="javascript">'."JavaScript".'</option>'
                .'<option value="php">'."PHP".'</option>'
            ."</select></div>"
    .'</td>'
    .'<td width="1%" class="mo-ace-td-select">'
            .'<div><select name="select-fontsize" id="select-fontsize" class="mo-ace-in-select js-ace-select">'
                .'<option value="10px">'."10px".'</option>'
                .'<option value="12px">'."12px".'</option>'
                .'<option value="14px">'."14px".'</option>'
                .'<option value="16px">'."16px".'</option>'
                .'<option value="18px">'."18px".'</option>'
            ."</select></div>"
    .'</td>'
    .'<td width="1%" class="mo-nowrap">'
            .'<img id="undo" class="mo-ace-icon mo-tool-icon" src="'.URL_BASE.ADMIN_DIR_NAME.'/gfx/ace/undo.png" hspace="0" vspace="0" />'
            .'<img id="redo" class="mo-ace-icon mo-tool-icon" src="'.URL_BASE.ADMIN_DIR_NAME.'/gfx/ace/redo.png" hspace="0" vspace="0" />'

            .'<img id="toggle_fold" class="mo-tool-icon mo-ace-icon" src="'.URL_BASE.ADMIN_DIR_NAME.'/gfx/ace/expand.png" hspace="0" vspace="0" />'
    .'</td>'
    .'<td width="1%" class="mo-nowrap">'
        .'<img id="js-ace-color-img" class="mo-tool-icon mo-ace-icon fb-color-change" alt="Farbe" title="#RRGGBB" src="gfx/jsToolbar/farbe.png" onclick="insert_ace(\'#\' + document.getElementById(\'farbcode\').value , \'\',true)" />'
    .'</td>'
    .'<td id="colordiv-editor" class="mo-nowrap">'
    .'</td>'

    .'<td width="1%" class="mo-nowrap">'
#        .'</div>'
#        .'<div style="float:right;white-space:nowrap;">'
            .'<input class="mo-ace-in-text" id="search-text" type="text" name="search-text" value="" />'
            .'<img id="search" class="mo-ace-icon mo-tool-icon" src="'.URL_BASE.ADMIN_DIR_NAME.'/gfx/ace/find.png" hspace="0" vspace="0" />'
            .'<input class="mo-ace-in-check" type="checkbox" id="search-all" />'
            .'<label class="mo-ace-in-check-label" for="search">Alle</label>'
            .'<input class="mo-ace-in-text" id="replace-text" type="text" name="search" value="" />'
            .'<img id="replace" class="mo-ace-icon mo-tool-icon" src="'.URL_BASE.ADMIN_DIR_NAME.'/gfx/ace/replace.png" hspace="0" vspace="0" />'
#        .'</div>'
#       .'<div class="mo-clear"></div>'
    .'</td>'
    ."</tr>"
    ."</table>"
    .'</div>'
    .'<div id="pagecontent-border" style="position:relative;overflow:hidden;" class="ui-widget-content">'
        .'<pre id="pagecontent"></pre>'
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
/*    ."<tr>"
    // Überschrift Syntaxelemente
    .'<td width="58%" class="mo-nowrap">'
    .getLanguageValue("toolbar_syntaxelements")
    ."</td>"
    // Überschrift Textformatierung
    .'<td width="31%" class="mo-nowrap">'
    .getLanguageValue("toolbar_textformatting")
    ."</td>"
    // Überschrift Farben
    .'<td width="11%" class="mo-nowrap">'
    .getLanguageValue("toolbar_textcoloring")
    ."</td>"
    ."</tr>"*/
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
    .'<td width="1%" class="mo-nowrap">'
        .'<img class="ed-syntax-icon ui-state-active fb-color-change" alt="Farbe" title="[farbe=RRGGBB| ... ] - '.getLanguageValue("toolbar_desc_farbe").'" src="gfx/jsToolbar/farbe.png" onclick="insert_ace(\'[farbe=\' + document.getElementById(\'farbcode\').value + \'|\', \']\',true)" />'
    ."</td>"
    .'<td id="colordiv-mozilo" width="8%" class="mo-nowrap">'
        .'<div class="colordiv ui-state-active">'
            .'<input type="text" maxlength="6" value="FF0000" class="fb-color-change" id="farbcode" size="6" />'
            .'<span class="colorimage ui-state-default"><span class="ui-icon ui-icon-triangle-2-n-s">&nbsp;</span></span>'
            .'<div class="box-farbtastic mo-shadow"></div>'
        .'</div>'
    ."</td>"
    ."</tr>"
    ."</table>";


    // Smileys
    if ($CMS_CONF->get("replaceemoticons") == "true") {
        $content .= '<table class="mo-menue-row-bottom" width="33%" cellspacing="0" border="0" cellpadding="0"><tr><td class="mo-nowrap">'.returnSmileyBar().'</td></tr></table>';
    }


    $content .= '<table class="mo-menue-row-bottom" width="100%" cellspacing="0" border="0" cellpadding="0">'# style="width:100%"
/*    ."<tr>";

    // Überschrift Inhalte
    $content .=    '<td colspan="3">'
    .getLanguageValue("toolbar_contents")
    ."</td>";
    // Überschrift Benutzerdefinierte Syntaxelemente
    $content .= '<td>';
    $content .= getLanguageValue("toolbar_usersyntax");
    $content .= "</td>";
    $content .= "</tr>"*/
    ."<tr>";
    // Inhalte
//.'<ul>'

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

// Rueckgabe eines Standard-Formatsymbolleisten-Icons
function returnFormatToolbarIcon($tag) {
    if(strpos($tag,"=") > 0) {
        $tag = substr($tag,0,strpos($tag,"="));
        return '<img class="ed-syntax-icon ed-syntax-hover ui-state-active" alt="'.$tag.'" src="gfx/jsToolbar/'.$tag.'.png" title="['.$tag.'=|...]" onclick="insert_ace(\'['.$tag.'=|\', \']\',true)" />';
    } elseif($tag == "tabelle")
        return '<img class="ed-syntax-icon ed-syntax-hover ui-state-active" alt="'.$tag.'" src="gfx/jsToolbar/'.$tag.'.png" title="['.$tag.'|...]" onclick="insert_ace(\'['.$tag.'|\\n&lt;&lt; \', \' |  &gt;&gt;\\n&lt;  |  &gt;\\n]\',true)" />';
    elseif($tag == "linie")
        return '<img class="ed-syntax-icon ed-syntax-hover ui-state-active" alt="'.$tag.'" src="gfx/jsToolbar/'.$tag.'.png" title="[----]" onclick="insert_ace(\'[----]\', \'\',false)" />';
    else
        return '<img class="ed-syntax-icon ed-syntax-hover ui-state-active" alt="'.$tag.'" src="gfx/jsToolbar/'.$tag.'.png" title="['.$tag.'|...]" onclick="insert_ace(\'['.$tag.'|\', \']\',true)" />';
}


// Selectbox mit allen benutzerdefinierten Syntaxelementen
function returnUserSyntaxSelectbox() {
    global $USER_SYNTAX;

    $content = '<select name="usersyntax" class="usersyntaxselectbox" title="'.getLanguageValue("toolbar_usersyntax").'">';
    foreach($USER_SYNTAX->toArray() as $key => $value) {
        if(false !== strpos($value,"{DESCRIPTION}") and false === strpos($value,"{VALUE}")) {
            $inhalt = "[".$key."=...|]";
#            $valinhalt = "[".$key."=|]";
        } elseif(false === strpos($value,"{DESCRIPTION}") and false !== strpos($value,"{VALUE}")) {
            $inhalt = "[".$key."|...]";
#            $valinhalt = "[".$key."|]";
        } elseif(false !== strpos($value,"{DESCRIPTION}") and false !== strpos($value,"{VALUE}")) {
            $inhalt = "[".$key."=|...]";
#            $valinhalt = "[".$key."=|]";
        } elseif(false === strpos($value,"{DESCRIPTION}") and false === strpos($value,"{VALUE}")) {
            $inhalt = "[".$key."]";
#            $valinhalt = "[".$key."]";
        }
        $content .= '<option value="'.$inhalt.'">'.$inhalt.'</option>';

/*
        $content .= '<option value="'.$key.'">['.$key.'|...]</option>';*/
    }
    $content .= "</select>";
    return $content;
}

// Selectbox mit allen benutzerdefinierten Syntaxelementen
function returnPlatzhalterSelectbox() {
    global $specialchars;
    global $activ_plugins;
    $all = false;
    if(ACTION == "template" or ACTION == "config")
        $all = true;
    $selectbox = '<select name="platzhalter" class="overviewselect" title="'.getLanguageValue("toolbar_platzhalter").'">';
    if(ACTION == "config") {
        $selectbox .= '<option title="'.getLanguageValue("toolbar_platzhalter_VALUE").'" value="{VALUE}">{VALUE}</option>';
        $selectbox .= '<option title="'.getLanguageValue("toolbar_platzhalter_DESCRIPTION").'" value="{DESCRIPTION}">{DESCRIPTION}</option>';
    }
    foreach(makePlatzhalter($all) as $value) {
        $language = str_replace(array('{','}'),'',$value);
        if(in_array($language,$activ_plugins))
            continue;
        $selectbox .= '<option title="'.getLanguageValue("toolbar_platzhalter_".$language).'" value="'.$value.'">'.$value.'</option>';
    }
    $selectbox .= '</select>';
    return $selectbox;
}

// Selectbox mit allen Plugin Platzhaltern die nichts mit dem Template zu tun haben
function returnPluginSelectbox() {
    global $specialchars;
    global $activ_plugins;
    require_once(BASE_DIR_CMS."Plugin.php");
    $selectbox = '<select name="plugins" class="overviewselect" title="'.getLanguageValue("toolbar_plugins").'">';
    foreach($activ_plugins as $currentplugin) {
        if(file_exists(PLUGIN_DIR_REL.$currentplugin."/index.php") and file_exists(PLUGIN_DIR_REL.$currentplugin."/plugin.conf.php")) {
            require_once(PLUGIN_DIR_REL.$currentplugin."/index.php");
            $plugin = new $currentplugin();
            $plugin_info = $plugin->getInfo();
            // Plugin nur in der Auswahlliste zeigen, wenn es aktiv geschaltet ist
            $plugin_conf = new Properties(PLUGIN_DIR_REL.$currentplugin."/plugin.conf.php");
            if(isset($plugin_info[5]) and is_array($plugin_info[5])) {
                foreach($plugin_info[5] as $platzh => $info) {
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
        if($icon == "readonly" or $icon == "error") {
            continue;
        }
        $icon = trim($icon);
        $content .= '<img class="ed-syntax-icon ed-syntax-hover ui-state-active" alt="'.$emoticon.'"title=":'.$icon.':" src="'.URL_BASE.CMS_DIR_NAME.'/smileys/'.$icon.'.gif" onclick="insert_ace(\' :'.$icon.': \', \'\',false)" />';
    }
    return $content;
}

function returnCatPagesSelectbox() {
    global $specialchars;
    global $CatPage;

    $select = '<select name="pages" class="overviewselect" title="'.getLanguageValue("category_button")." &#047; ".getLanguageValue("page_button").':">';
    foreach ($CatPage->get_CatArray(true,false) as $catdir) {
        $cleancatname = $CatPage->get_HrefText($catdir,false);
        $select .= '<optgroup label="'.$cleancatname.'">';
        foreach($CatPage->get_PageArray($catdir, array(EXT_PAGE,EXT_HIDDEN), true) as $file) {
            $cleanpagename = $CatPage->get_HrefText($catdir,$file);
            $label = NULL;
            if ($CatPage->get_Type($catdir,$file) == EXT_HIDDEN)
                $label = " (".getLanguageValue("page_saveashidden").")";
            $select .= '<option value="'.$cleancatname.":".$cleanpagename.'">'.$cleanpagename.$label."</option>";
        }
        $select .= '</optgroup>';
    }
    $select .= "</select>";
    return $select;
}
function returnFilesSelectbox() {
    global $specialchars;
    global $CatPage;

    $select = '<select name="files" class="overviewselect" title="'.getLanguageValue("files_button").':">';
    foreach($CatPage->get_CatArray(true,false) as $catdir) {
        $cleancatname = $CatPage->get_HrefText($catdir,false);
        if(count($CatPage->get_FileArray($catdir)) > 0) {
            $select .= '<optgroup label="'.$cleancatname.'">';
            foreach($CatPage->get_FileArray($catdir) as $current_file) {
                $select .= '<option value="'.$cleancatname.":".$specialchars->rebuildSpecialChars($current_file, true, true).'">'.$specialchars->rebuildSpecialChars($current_file, false, true)."</option>";
            }
            $select .= '</optgroup>';
        }
    }
    $select .= "</select>";
    return $select;
}

function returnGalerySelectbox() {
    global $specialchars;
    $select = '<select name="gals" class="overviewselect" title="'.getLanguageValue("gallery_button").':">';
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

    $selectbox = '<select name="template_css" class="overviewselect" title="'."Template CSS und Bilder".':">';#getLanguageValue("toolbar_plugins")
    $selectbox .= '<optgroup label="CSS">';
    foreach(getDirAsArray($LAYOUT_DIR.'css',array(".css"),"natcasesort") as $file) {
        $selectbox .= '<option value="{LAYOUT_DIR}/css/'.$specialchars->replaceSpecialChars($file,true).'">'.$specialchars->rebuildSpecialChars($file, false, true).'</option>';
    }
    $selectbox .= '</optgroup>';
    $selectbox .= '<optgroup label="Bilder">';

    foreach(getDirAsArray($LAYOUT_DIR.'grafiken',"img","natcasesort") as $file) {
        $selectbox .= '<option value="{LAYOUT_DIR}/grafiken/'.$specialchars->replaceSpecialChars($file,true).'">'.$specialchars->rebuildSpecialChars($file, false, true).'</option>';
    }

    $selectbox .= '</optgroup>';
    $selectbox .= "</select>";
    return $selectbox;
}

?>