<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();
function config() {
    global $CMS_CONF;
    global $specialchars;
    global $CONTACT_CONF;
    global $ADMIN_CONF;
    global $USER_SYNTAX;

    if(getRequestValue('savesyntax','post') == "true") {
        if(false !== ($content = getRequestValue('content','post',false))
                and $CMS_CONF->get('usecmssyntax') == "true") {
            $ret = $USER_SYNTAX->setFromTextarea($content);
            $syntax = "";
            foreach($ret as $key => $value) {
                $syntax .= '<b>'.$key.'</b> = <span style="color:#ff0000">SYNTAX&gt;&gt;&gt;</span>'.htmlentities($value).'<span style="color:#ff0000">&lt;&lt;&lt;SYNTAX</span><br /><hr />';
            }
            $syntax = '<div class="mo-nowrap">'.$syntax."</div>";
            require_once(BASE_DIR_ADMIN.'editsite.php');
            $selctbox = '<span id="replace-item">'.returnUserSyntaxSelectbox().'</span>';
            $var_UserSyntax = '0E0M0P0T0Y0';
            # die userSxntax hat sich geändert deshalb schiecken wir dem editor userSyntax die neuen
            global $USER_SYNTAX;
            $moziloUserSyntax = $USER_SYNTAX->toArray();
            if(count($moziloUserSyntax) > 0) {
                $moziloUserSyntax = array_keys($moziloUserSyntax);
                rsort($moziloUserSyntax);
                $var_UserSyntax = implode('|',$moziloUserSyntax);
            }
            $var_UserSyntax = '<span id="moziloUserSyntax">'.$var_UserSyntax.'</span>';

            echo ajax_return("success",false,returnMessage(true,$syntax),getLanguageValue("config_titel_usersyntax_test"),true).$selctbox.$var_UserSyntax;
        } elseif($CMS_CONF->get('usecmssyntax') == "true") {
            require_once(BASE_DIR_ADMIN.'editsite.php');
            $selctbox = '<span id="replace-item">'.returnUserSyntaxSelectbox().'</span>';
            $selctbox .= '<textarea id="page-content">'.$USER_SYNTAX->getToTextarea().'</textarea>';
            echo ajax_return("success",false).$selctbox;
        }
        exit();
    } elseif(getRequestValue('chanceconfig','post') == "true") {
        echo set_config_para();
        exit();
    }



    $pagecontent = NULL;


    $show = $ADMIN_CONF->get("config");
    if(!is_array($show))
        $show = array();

    $template = array();
    $error = array();
    // ALLGEMEINE EINSTELLUNGEN
    $titel = "config_titel_cmsglobal";
        // Zeile "WEBSITE-TITEL"
    if(ROOT or in_array("websitetitle",$show)) {
        $error[$titel][] = false;
        $template[$titel][] = array(getLanguageValue("config_text_websitetitle"),'<input type="text" class="mo-input-text" name="websitetitle" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"),true,true).'" />');
    }

        // Zeile "TITEL-TRENNER"
    if(ROOT or in_array("titlebarseparator",$show)) {
       $error[$titel][] = false;
        $template[$titel][] = array(getLanguageValue("config_text_websitetitleseparator"),'<input type="text" class="mo-input-text" name="titlebarseparator" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("titlebarseparator"),true,true).'" />');
    }

        // Zeile "WEBSITE-TITELLEISTE"
    if(ROOT or in_array("titlebarformat",$show)) {
        $error[$titel][] = false;
        $template[$titel][] = getLanguageValue("config_text_websitetitlebar").'<br /><input type="text" class="mo-input-text mo-input-margin-top" name="titlebarformat" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("titlebarformat"),true,true).'" />';
    }

        // Zeile "WEBSITE-BESCHREIBUNG"
    if(ROOT or in_array("websitedescription",$show)) {
        $error[$titel][] = false;
        $template[$titel][] = getLanguageValue("config_text_websitedescription").'<br /><input type="text" class="mo-input-text mo-input-margin-top" name="websitedescription" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("websitedescription"),true,true).'" />';
    }

        // Zeile "WEBSITE-KEYWORDS"
    if(ROOT or in_array("websitekeywords",$show)) {
        $error[$titel][] = false;
        $template[$titel][] = getLanguageValue("config_text_websitekeywords").'<br /><input type="text" class="mo-input-text mo-input-margin-top" name="websitekeywords" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("websitekeywords"),true,true).'" />';
    }

        // Zeile "SPRACHAUSWAHL"
    if(ROOT or in_array("cmslanguage",$show)) {
        $tmp_array = getDirAsArray(BASE_DIR_CMS.'sprachen',"file","natcasesort");
        if(count($tmp_array) <= 0) {
            $error[$titel][] = getLanguageValue("config_error_language_empty");
        } elseif(!in_array("language_".$CMS_CONF->get('cmslanguage').".txt",$tmp_array)) {
            $error[$titel][] = getLanguageValue("config_error_languagefile_error")."<br />".CMS_DIR_NAME."/sprachen/language_".$CMS_CONF->get('cmslanguage').".txt";
        } else
            $error[$titel][] = false;
        $conf_inhalt = '<div class="mo-select-div"><select name="cmslanguage" class="mo-select">';
        foreach($tmp_array as $file) {
            $currentlanguagecode = substr($file,strlen("language_"),strlen($file)-strlen("language_")-strlen(".txt"));
            $selected = NULL;
            // aktuell ausgewählte Sprache als ausgewählt markieren 
            if($currentlanguagecode == $CMS_CONF->get("cmslanguage")) {
                $selected = " selected";
            }
            $conf_inhalt .= '<option'.$selected.' value="'.$currentlanguagecode.'">';
            // Übersetzer aus der aktuellen Sprachdatei holen
            $languagefile = new Properties("../cms/sprachen/$file");
            $conf_inhalt .= $currentlanguagecode." (".getLanguageValue("config_input_translator")." ".$languagefile->get("_translator_0").")";
            $conf_inhalt .= "</option>";
        }
        $conf_inhalt .= "</select></div>";
        $template[$titel][] = array(getLanguageValue("config_text_cmslanguage"),$conf_inhalt);
    }

        // Zeile "LAYOUTAUSWAHL"
    if(ROOT or in_array("cmslayout",$show)) {
        $tmp_array = getDirAsArray(BASE_DIR."layouts","dir","natcasesort");
        if(count($tmp_array) <= 0) {
            $error[$titel][] = getLanguageValue("config_error_layouts_emty");
        } elseif(!in_array($CMS_CONF->get('cmslayout'),$tmp_array)) {
            $error[$titel][] = getLanguageValue("config_error_layouts_existed")."<br />".$specialchars->rebuildSpecialChars($CMS_CONF->get('cmslayout'),true,true);
        } else
            $error[$titel][] = false;
        $conf_inhalt = '<div class="mo-select-div"><select name="cmslayout" class="mo-select">';
        foreach ($tmp_array as $file) {
            $selected = NULL;
            if ($file == $CMS_CONF->get("cmslayout")) {
                $selected = " selected";
            }
            $conf_inhalt .= '<option'.$selected.' value="'.$file.'">';
            // Übersetzer aus der aktuellen Sprachdatei holen
            $conf_inhalt .= $specialchars->rebuildSpecialChars($file, true, true);
            $conf_inhalt .= "</option>";
        }
        $conf_inhalt .= "</select></div>";
        $template[$titel][] = array(getLanguageValue("config_text_cmslayout"),$conf_inhalt);
    }

    // Zeile "STANDARD-KATEGORIE"
    if(ROOT or in_array("defaultcat",$show)) {
        $tmp_array = getDirAsArray(CONTENT_DIR_REL,"dir","natcasesort");
        if(count($tmp_array) <= 0) {
            $error[$titel][] = getLanguageValue("config_error_defaultcat_emty");
        } elseif(!in_array($CMS_CONF->get('defaultcat'),$tmp_array)) {
            $error[$titel][] = getLanguageValue("config_error_defaultcat_existed")."<br />".$specialchars->rebuildSpecialChars($CMS_CONF->get('defaultcat'),true,true);
        } else
            $error[$titel][] = false;
        $conf_inhalt = '<div class="mo-select-div"><select name="defaultcat" class="mo-select">';
        foreach($tmp_array as $element) {
            if (count(getDirAsArray(CONTENT_DIR_REL.$element,array(EXT_PAGE,EXT_HIDDEN),"none")) == 0) {
                continue;
            }
            $selected = NULL;
            if ($element == $CMS_CONF->get("defaultcat")) {
                $selected = "selected ";
            }
            $conf_inhalt .= '<option '.$selected.'value="'.$element.'">'.$specialchars->rebuildSpecialChars($element, true, true)."</option>";
        }
        $conf_inhalt .= "</select></div>";
        $template[$titel][] = array(getLanguageValue("config_text_defaultcat"),$conf_inhalt);
    }
    # sitemap.xml
    if(ROOT or in_array("usesitemap",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_usesitemap"),buildCheckBox("usesitemap", $CMS_CONF->get("usesitemap"),getLanguageValue("config_input_usesitemap")));
    }

    // Zeile "NUTZE CMS-SYNTAX"
        // SYNTAX-EINSTELLUNGEN
        $titel = "config_titel_cmssyntax";
    if(ROOT or in_array("usecmssyntax",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_usesyntax"),buildCheckBox("usecmssyntax", $CMS_CONF->get("usecmssyntax"),getLanguageValue("config_input_usesyntax")));
    }

    if(ROOT or ((in_array("usecmssyntax",$show))
        or (!in_array("usecmssyntax",$show) and $CMS_CONF->get("usecmssyntax") == "true"))) {

        if(ROOT or in_array("editusersyntax",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_usersyntax"),'<div class="js-usecmssyntax">'.'<img class="js-editsyntax mo-tool-icon" src="'.ADMIN_ICONS.'page-edit.png" alt="page-edit" hspace="0" vspace="0" />'.'</div>');
        }

        // Zeile "ERSETZE EMOTICONS"
        if(ROOT or in_array("replaceemoticons",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_replaceemoticons"),'<div class="js-usecmssyntax">'.buildCheckBox("replaceemoticons", ($CMS_CONF->get("replaceemoticons") == "true"),getLanguageValue("config_input_replaceemoticons")).'</div>');
        }
        if(ROOT or in_array("defaultcolors",$show)) {
            $error[$titel][] = false;
            $colors_div = '<div class="mo-in-li-l">';
            $colors_div .= '<img class="js-save-default-color mo-tool-icon" src="'.URL_BASE.ADMIN_DIR_NAME.'/gfx/icons/24x24/save.png" alt="work" />';
            $colors_div .= '<img id="js-del-config-default-color" class="mo-tool-icon ui-corner-all" src="'.URL_BASE.ADMIN_DIR_NAME.'/gfx/icons/24x24/delete.png" alt="delete" hspace="0" vspace="0" />';
            $colors_div .= '<div id="js-config-default-color-box" class="ce-default-color-box ui-widget-content ui-corner-all">&nbsp;';
            $colors_div .= '</div>';
            $colors_div .= '</div>';
            $colors_div .= '<div id="js-menu-config-default-color" class="mo-in-li-r">'
                .'<img class="js-new-config-default-color ce-bg-color-change ce-default-color-img ui-widget-content ui-corner-all" alt="" title="" src="'.URL_BASE.ADMIN_DIR_NAME.'/gfx/clear.gif" />'
                .'<input type="text" maxlength="6" value="DD0000" class="ce-bg-color-change ce-in-hex" id="js-new-default-color-value" size="6" />'
                .'<img class="js-coloreditor-button ed-syntax-icon ui-state-active" alt="Farbe Bearbeiten" title="Farbe Bearbeiten" src="gfx/jsToolbar/farbeedit.png"  />'
            .'</div>';
            $template[$titel][] = '<div class="mo-margin-bottom">'.getLanguageValue("config_text_defaultcolors").'</div>'.$colors_div.'<input type="hidden" name="defaultcolors" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("defaultcolors"),false,false).'" />';
        }
    }

    // Erweiterte Einstellungen
        $titel = "config_titel_expert";
            // Zeile "showhiddenpagesin"
    if(ROOT or in_array("hiddenpages",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_showhiddenpages"),
                buildCheckBox("showhiddenpagesinsearch", ($CMS_CONF->get("showhiddenpagesinsearch") == "true"),getLanguageValue("config_input_search"))
                .buildCheckBox("showhiddenpagesinsitemap", ($CMS_CONF->get("showhiddenpagesinsitemap") == "true"),getLanguageValue("config_input_sitemap"))
                .buildCheckBox("showhiddenpagesasdefaultpage", ($CMS_CONF->get("showhiddenpagesasdefaultpage") == "true"),getLanguageValue("config_input_pagesasdefaultpage"))
                );
    }

            // Zeile "Links öffnen self blank"
    if(ROOT or in_array("targetblank",$show)) {
           $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_target"),buildCheckBox("targetblank_download", ($CMS_CONF->get("targetblank_download") == "true"),getLanguageValue("config_input_download")).buildCheckBox("targetblank_link", ($CMS_CONF->get("targetblank_link") == "true"),getLanguageValue("config_input_link")));
    }
            // Zeile "wenn page == cat"
    if(ROOT or in_array("hidecatnamedpages",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_catnamedpages"),buildCheckBox("hidecatnamedpages", ($CMS_CONF->get("hidecatnamedpages") == "true"),getLanguageValue("config_input_catnamedpages")));
    }
            // Zeile "mod_rewrite"
    if(ROOT or in_array("modrewrite",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_modrewrite"),buildCheckBox("modrewrite", ($CMS_CONF->get("modrewrite") == "true"),getLanguageValue("config_input_modrewrite")));
    }
            // Zeile "showsyntaxtooltips"
    if(ROOT or in_array("showsyntaxtooltips",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_showsyntaxtooltips"),buildCheckBox("showsyntaxtooltips", ($CMS_CONF->get("showsyntaxtooltips") == "true"),getLanguageValue("config_input_showsyntaxtooltips")));
    }
#if(!isset($template))

    $pagecontent .= contend_template($template,$error);

    if(ROOT or in_array("editusersyntax",$ADMIN_CONF->get("config"))) {
        $pagecontent .= pageedit_dialog();
    }

    return $pagecontent;
}

function set_config_para() {
    global $CMS_CONF;

    $main = makeDefaultConf("main");
    unset($main['expert']);
    foreach($main as $type => $type_array) {
        foreach($main[$type] as $syntax_name => $dumy) {
            if(false === ($syntax_value = getRequestValue($syntax_name,'post')))
                continue;
            if($type == 'text') {
                if($CMS_CONF->get($syntax_name) != $syntax_value) {
                    $CMS_CONF->set($syntax_name, $syntax_value);
                }
            }
            if($type == 'checkbox') {
                if($syntax_value != "true" and $syntax_value != "false")
                    return ajax_return("error",false,returnMessage(false,getLanguageValue("properties_error_save")),true,true);
                if($syntax_name == "modrewrite" and !getRequestValue('link','get') and $syntax_value == "true") {
                    return ajax_return("error",false,returnMessage(false,getLanguageValue("config_error_modrewrite")),true,true);
                }
                if($syntax_name == "usesitemap") {
                    if(true !== ($error = write_robots($syntax_value)))
                        return $error;
                }
                # die checkbox hat immer einen anderen wert als der gespeicherte deshalb keine prüfung
                $CMS_CONF->set($syntax_name, $syntax_value);
            }
        }
    }
    return ajax_return("success",false);
}

function write_robots($syntax_value) {
    if(is_file(BASE_DIR.'robots.txt')) {
        if(false === ($lines = file(BASE_DIR.'robots.txt')))
            return ajax_return("error",false,returnMessage(false,getLanguageValue("error_read_robots")),true,true);
    } else {
        $lines = array('User-agent: *','Disallow: /'.ADMIN_DIR_NAME.'/','Disallow: /'.CMS_DIR_NAME.'/','Disallow: /kategorien/','Disallow: /galerien/','Disallow: /layouts/','Disallow: /plugins/');
    }
    foreach($lines as $pos => $value) {
        if(strstr($value,'Sitemap:')) {
            unset($lines[$pos]);
            continue;
        }
        $lines[$pos] = trim($value);
    }
    $text = implode("\n",$lines)."\n";
    if($syntax_value == "true") {
        $text = 'Sitemap: http://'.$_SERVER['SERVER_NAME'].'/sitemap.xml'."\n".$text;
    }
    if(true != (mo_file_put_contents(BASE_DIR."robots.txt",$text)))
        return ajax_return("error",false,returnMessage(false,getLanguageValue("error_write_robots")),true,true);
    return true;
}
?>