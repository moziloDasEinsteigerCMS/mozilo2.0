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
            $USER_SYNTAX->setFromTextarea($content);
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
            echo ajax_return("success",false).$selctbox.$var_UserSyntax;
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
    // Zeile "WEBSITE-TITEL", "TITEL-TRENNER" und "WEBSITE-TITELLEISTE"
    if(ROOT or in_array("websitetitle",$show)) {
        $error[$titel][] = false;
        $template[$titel][] = '<div class="ui-helper-clearfix">'
                        .'<div class="mo-in-li-l">'.getLanguageValue("config_text_websitetitle").'</div>'
                        .'<div class="mo-in-li-r">'.'<input type="text" class="mo-input-text" name="websitetitle" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"),true,true).'" />'.'</div>'
                    .'</div>'
                    .'<div class="mo-padding-top ui-helper-clearfix">'
                        .'<div class="mo-in-li-l">'.getLanguageValue("config_text_websitetitleseparator").'</div>'
                        .'<div class="mo-in-li-r">'.'<input type="text" class="mo-input-text" name="titlebarseparator" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("titlebarseparator"),true,true).'" />'.'</div>'
                    .'</div>'
                    .'<div class="mo-padding-top">'
                        .getLanguageValue("config_text_websitetitlebar")
                        .'<br /><input type="text" class="mo-input-text mo-input-margin-top" name="titlebarformat" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("titlebarformat"),true,true).'" />'
                    .'</div>';

    }

    // Zeile "WEBSITE-BESCHREIBUNG" und "WEBSITE-KEYWORDS"
    if(ROOT or in_array("websitedescription",$show)) {
        $error[$titel][] = false;
        $template[$titel][] = getLanguageValue("config_text_websitedescription").'<br /><input type="text" class="mo-input-text mo-input-margin-top" name="websitedescription" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("websitedescription"),true,true).'" /><div class="mo-padding-top">'.getLanguageValue("config_text_websitekeywords").'</div><input type="text" class="mo-input-text mo-input-margin-top" name="websitekeywords" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("websitekeywords"),true,true).'" />';
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
            $languagefile = new Properties(BASE_DIR_CMS."sprachen/".$file);
            $conf_inhalt .= $currentlanguagecode." (".getLanguageValue("config_input_translator")." ".$languagefile->get("_translator_0").")";
            $conf_inhalt .= "</option>";
        }
        $conf_inhalt .= "</select></div>";
        $template[$titel][] = array(getLanguageValue("config_text_cmslanguage"),$conf_inhalt);
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

    if(ROOT or in_array("draftmode",$show)) {
        $conf_checkbox = buildCheckBox("draftmode", $CMS_CONF->get("draftmode"),getLanguageValue("config_input_draftmode"));
        $conf_select = "";
        $tmp_array = getDirAsArray(BASE_DIR."layouts","dir","natcasesort");
        if(count($tmp_array) <= 0) {
            $error[$titel][] = getLanguageValue("config_error_layouts_emty");
        } else
            $error[$titel][] = false;
        $conf_select .= '<div style="font-size:.4em;">&nbsp;</div><div class="mo-select-div"><select name="draftlayout" class="mo-select">';
        $conf_select .= '<option value="false">'.getLanguageValue("config_input_draftlayout").'</option>';
        foreach ($tmp_array as $file) {
            $selected = NULL;
            if ($file == $CMS_CONF->get("draftlayout")) {
                $selected = " selected";
            }
            $conf_select .= '<option'.$selected.' value="'.$file.'">';
            $conf_select .= $specialchars->rebuildSpecialChars($file, true, true);
            $conf_select .= "</option>";
        }
        $conf_select .= "</select></div>";

        $template[$titel][] = array(getLanguageValue("config_text_draftmode"),$conf_checkbox.$conf_select);
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
            $template[$titel][] = array(getLanguageValue("config_text_usersyntax"),'<div class="js-usecmssyntax">'.'<img class="js-editsyntax mo-tool-icon mo-icons-icon mo-icons-page-edit" src="'.ICON_URL_SLICE.'" alt="page-edit" hspace="0" vspace="0" />'.'</div>');
        }

        // Zeile "ERSETZE EMOTICONS"
        if(ROOT or in_array("replaceemoticons",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_replaceemoticons"),'<div class="js-usecmssyntax">'.buildCheckBox("replaceemoticons", ($CMS_CONF->get("replaceemoticons") == "true"),getLanguageValue("config_input_replaceemoticons")).'</div>');
        }
        if(ROOT or in_array("defaultcolors",$show)) {
            $error[$titel][] = false;
            $colors_div = '<div class="mo-in-li-l">';
            $colors_div .= '<img class="js-save-default-color mo-tool-icon mo-icons-icon mo-icons-save" src="'.ICON_URL_SLICE.'" alt="save" />';
            $colors_div .= '<img id="js-del-config-default-color" class="mo-tool-icon ui-corner-all mo-icons-icon mo-icons-delete" src="'.ICON_URL_SLICE.'" alt="delete" hspace="0" vspace="0" />';
            $colors_div .= '<div id="js-config-default-color-box" class="ce-default-color-box ui-widget-content ui-corner-all">&nbsp;';
            $colors_div .= '</div>';
            $colors_div .= '</div>';
            $colors_div .= '<div id="js-menu-config-default-color" class="mo-in-li-r">'
                .'← <img class="js-new-config-default-color ce-bg-color-change ce-default-color-img ui-widget-content ui-corner-all" alt="" title="" src="'.ICON_URL_SLICE.'" />'
                .'<input type="text" maxlength="6" value="DD0000" class="ce-bg-color-change js-in-hex ce-in-hex" id="js-new-default-color-value" size="6" />'
                .'<img class="js-coloreditor-button ed-icon-border ed-syntax-icon ed-farbeedit" alt="'.getLanguageValue("dialog_title_coloredit").'" title="'.getLanguageValue("dialog_title_coloredit").'" src="'.ICON_URL_SLICE.'" style="display:none;" />'
            .'</div>';
            $template[$titel][] = '<div class="mo-margin-bottom">'.getLanguageValue("config_text_defaultcolors").'</div>'.$colors_div.'<input type="hidden" name="defaultcolors" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("defaultcolors"),false,false).'" />';
        }
    }

    // Erweiterte Einstellungen
    // Zeile "showhiddenpagesin"
    $titel = "config_titel_expert";
    if(ROOT or in_array("hiddenpages",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_showhiddenpages"),
                buildCheckBox("showhiddenpagesinsearch", ($CMS_CONF->get("showhiddenpagesinsearch") == "true"),getLanguageValue("config_input_search")).'<br />'
                .buildCheckBox("showhiddenpagesinsitemap", ($CMS_CONF->get("showhiddenpagesinsitemap") == "true"),getLanguageValue("config_input_sitemap")).'<br />'
                .buildCheckBox("showhiddenpagesasdefaultpage", ($CMS_CONF->get("showhiddenpagesasdefaultpage") == "true"),getLanguageValue("config_input_pagesasdefaultpage"))
                );
    }

    // Zeile "Links öffnen self blank"
    if(ROOT or in_array("targetblank",$show)) {
           $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_target"),buildCheckBox("targetblank_download", ($CMS_CONF->get("targetblank_download") == "true"),getLanguageValue("config_input_download")).'<br />'.buildCheckBox("targetblank_link", ($CMS_CONF->get("targetblank_link") == "true"),getLanguageValue("config_input_link")));
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

    $pagecontent .= contend_template($template,$error);

    if(ROOT or in_array("editusersyntax",$ADMIN_CONF->get("config"))) {
        $pagecontent .= pageedit_dialog();
    }

    return $pagecontent;
}

function set_config_para() {
    global $CMS_CONF, $specialchars;

    $title = "";
    $main = makeDefaultConf("main");
    unset($main['expert']);
    foreach($main as $type => $type_array) {
        foreach($main[$type] as $syntax_name => $dumy) {
            if(false === ($syntax_value = getRequestValue($syntax_name,'post')))
                continue;
            if($type == 'text') {
                if($CMS_CONF->get($syntax_name) != $syntax_value) {
                    $CMS_CONF->set($syntax_name, $syntax_value);
                    if($syntax_name == "websitetitle")
                        $title = '<span id="replace-item"><span id="admin-websitetitle" class="mo-bold mo-td-middle">'.$specialchars->rebuildSpecialChars($syntax_value, false, true).'</span></span>';
                }
            }
            if($type == 'checkbox') {
                if($syntax_value != "true" and $syntax_value != "false")
                    return ajax_return("error",false,returnMessage(false,getLanguageValue("properties_error_save")),true,true);
                # die checkbox hat immer einen anderen wert als der gespeicherte deshalb keine prüfung
                $CMS_CONF->set($syntax_name, $syntax_value);
                if($syntax_name == "modrewrite" and true !== ($error = write_modrewrite($syntax_value)))
                    return $error;
                if($syntax_name == "usesitemap") {
                    if(true !== ($error = write_robots()))
                        return $error;
                    if(true != ($error = write_xmlsitmap(true)))
                        return $error;
                }
            }
        }
    }
    return ajax_return("success",false).$title;
}
?>