<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();
function plugins() {
    global $ADMIN_CONF;
    global $CatPage;

    require_once(BASE_DIR_CMS."Plugin.php");

    if(false !== ($plugin_name = getRequestValue('pluginadmin'))) {#,'get'
        if(file_exists(PLUGIN_DIR_REL.$plugin_name)) {
            define("PLUGINADMIN",$plugin_name);
            if(file_exists(PLUGIN_DIR_REL.PLUGINADMIN."/plugin.conf.php") and file_exists(PLUGIN_DIR_REL.PLUGINADMIN."/index.php")) {
                require_once(PLUGIN_DIR_REL.PLUGINADMIN."/index.php");
                # Enthält der Code eine Klasse mit dem Namen des Plugins und ist es auch der Dirname?
                if(class_exists(PLUGINADMIN) and in_array(PLUGINADMIN, get_declared_classes())) {
                    define("PLUGINADMIN_GET_URL",URL_BASE.ADMIN_DIR_NAME."/index.php?pluginadmin=".PLUGINADMIN."&amp;nojs=true&amp;action=".ACTION);
                    $plugin = new $plugin_name();
                    $info = $plugin->getInfo();
                    $config = $plugin->getConfig();
                    if(PLUGIN_DIR_REL.$plugin_name.'/'.$config["--admin~~"]["datei_admin"] == PLUGIN_DIR_REL.PLUGINADMIN."/index.php")
                        return $plugin->getContent("");
                    else
                        return require_once(PLUGIN_DIR_REL.$plugin_name.'/'.$config["--admin~~"]["datei_admin"]);
                }
            } else
                die();
        } else
            die();
    }

    if(getRequestValue('chanceplugin','post') == "true"
            and false !== ($plugin_name = getRequestValue('plugin_name','post'))) {
        if(file_exists(PLUGIN_DIR_REL.$plugin_name)
                and file_exists(PLUGIN_DIR_REL.$plugin_name."/plugin.conf.php")
                and file_exists(PLUGIN_DIR_REL.$plugin_name."/index.php")) {
            $conf_plugin = new Properties(PLUGIN_DIR_REL.$plugin_name."/plugin.conf.php");
        } else
            die("Fatal Error");
        if(false !== ($activ = getRequestValue(array($plugin_name,'active'),'post'))
                    and ($activ == "true" or $activ == "false")) {
                $conf_plugin->set("active",$activ);
                ajax_return("success",true);
        } elseif($conf_plugin->get("active") == "true") {
            require_once(PLUGIN_DIR_REL.$plugin_name."/index.php");
            # Enthält der Code eine Klasse mit dem Namen des Plugins und ist es auch der Dirname?
            if(class_exists($plugin_name) and in_array($plugin_name, get_declared_classes())) {
                $plugin = new $plugin_name();
                # das ist nötig weil es sein kann das in getInfo() variblen initaliesiert werden
                $tmp = $plugin->getInfo();
                $config = $plugin->getConfig();
                echo save_plugin_settings($conf_plugin,$config,$plugin_name);
                exit();
            } else
                die("Fatal Error");
        }
        die("Fatal Error");
    }



    $pagecontent = '<ul class="js-plugins mo-ul">';

    $show = $ADMIN_CONF->get("plugins");
    if(!is_array($show))
        $show = array();

    $dircontent = getDirAsArray(PLUGIN_DIR_REL,"dir","natcasesort");
    foreach ($dircontent as $currentelement) {
        $new_plugin_conf = false;
        if(!ROOT and !in_array($currentelement,$show))
            continue;
        if(file_exists(PLUGIN_DIR_REL.$currentelement."/index.php")) {
            if(!is_file(PLUGIN_DIR_REL.$currentelement."/plugin.conf.php")) {
                if(false === (newConf(PLUGIN_DIR_REL.$currentelement."/plugin.conf.php")))
                    die();
                else
                    $new_plugin_conf = true;
            }
            require_once(PLUGIN_DIR_REL.$currentelement."/index.php");
            # Enthält der Code eine Klasse mit dem Namen des Plugins und ist es auch der Dirname?
            if(class_exists($currentelement) and in_array($currentelement, get_declared_classes()))
                $plugin = new $currentelement();
            else
                # Plugin Dirname stimt nicht mit Plugin Classnamen überein
                continue;
#            $conf_plugin = new Properties(PLUGIN_DIR_REL.$currentelement."/plugin.conf.php");
#!!!!!!!!! muss das an $conf_plugin übergeben werden
            $conf_plugin = $plugin->settings;
            # plugin.conf.php wurde neu erstelt.
            # Wenn es die getDefaultSettings() gibt fühle die plugin.conf.php damit
            if($new_plugin_conf and method_exists($plugin,'getDefaultSettings')) {
                $conf_plugin->setFromArray($plugin->getDefaultSettings());
            }
            $plugin_css_li_error = NULL;
            $plugin_error = false;
            $plugin_info = $plugin->getInfo();
            # Plugin Info Prüfen
            if(isset($plugin_info) and count($plugin_info) > 0) {
                $plugin_name = strip_tags($plugin_info[0],'<b>');
                if(substr(strip_tags($plugin_name),0,(strlen($currentelement))) != $currentelement)
                    $plugin_name = "<b>".$currentelement."</b> ".strip_tags($plugin_name);
                $plugin_name = htmlentities($plugin_name,ENT_COMPAT,CHARSET);
                $plugin_name = str_replace(array("&lt;","&gt;","$"),array("<",">",""),$plugin_name);
            } else {
                $plugin_error = '<img class="mo-tool-icon mo-icons-icon mo-icons-error" src="'.ICON_URL_SLICE.'" alt="error" />'.getLanguageValue('plugins_error').' <b>'.$currentelement.'</b>';
                $plugin_css_li_error = ' ui-state-error';
            }
            $pagecontent .= '<li class="js-plugin mo-li ui-widget-content ui-corner-all'.$plugin_css_li_error.'">'
            .'<div class="js-tools-show-hide mo-li-head-tag mo-li-head-tag-no-ul ui-state-active ui-corner-all">';
            if($plugin_error === false) {
                $pagecontent .= '<table width="100%" cellspacing="0" border="0" cellpadding="0" class="mo-tag-height-from-icon">'
                .'<tr>'
                .'<td width="99%" class="mo-nowrap">'
                .'<span class="mo-padding-left">'.$plugin_name.'</span>'
                .'</td>'
                .'<td class="mo-nowrap">'
                .'<div class="js-plugin-active mo-staus">'.buildCheckBox($currentelement.'[active]', ($conf_plugin->get("active") == "true"),getLanguageValue("plugins_input_active")).'</div>'
                .'</td>'
                .'<td class="d_td_icons mo-nowrap">'
                    .'<img class="js-tools-icon-show-hide js-toggle mo-tool-icon mo-icons-icon mo-icons-edit" src="'.ICON_URL_SLICE.'" alt="edit" />'
                .'</td>'
                .'</tr>'
                .'</table>'
                .'</div>'
                .'<div class="js-toggle-content mo-in-ul-ul">'
                .get_plugin_info($plugin_info);
                # geändert damit getConfig() nicht 2mal ausgeführt wird
                $config = $plugin->getConfig();
                # Beschreibung und inputs der Konfiguration Bauen und ausgeben
                $pagecontent .= get_plugin_config($conf_plugin,$config,$currentelement);

            } else
                $pagecontent .= $plugin_error;
            $pagecontent .= '</div></li>';
        }
    }
    $pagecontent .= '</ul>';
    return $pagecontent;
}

function save_plugin_settings($conf_plugin,$config,$currentelement) {
    $messages = NULL;

    if(count($config) < 1)
        return ajax_return("success",false);

    foreach($config as $name => $inhalt) {
        if($name == "--admin~~" or $name == "--template~~") continue;
        if(false !== ($conf_inhalt = getRequestValue(array($currentelement,$name),'post',false))) {
            # ist array bei radio und select multi
            if(is_array($conf_inhalt)) {
                $conf_inhalt = implode(",", $conf_inhalt);
            # alle die kein array sind
            } else {
                $conf_inhalt = str_replace(array("\r\n","\r","\n"),"<br />",$conf_inhalt);
            }

            if(isset($config[$name]['regex_error'])) {
                $regex_error = $config[$name]['regex_error'];
            } else {
                $regex_error = getLanguageValue("plugins_error_regex_error");
            }
            if(isset($config[$name]['regex']) and strlen($conf_inhalt) > 0) {
                if(preg_match($config[$name]['regex'], $conf_inhalt)) {
                    # bei Password und verschlüsselung an
                    if($config[$name]['type'] == "password" and $config[$name]['saveasmd5'] == "true") {
                        $conf_inhalt = md5($conf_inhalt);
                    }
                    # nur in conf schreiben wenn sich der wert geändert hat
                    if($conf_plugin->get($name) != $conf_inhalt) {
                        $conf_plugin->set($name,$conf_inhalt);
                    }
                } else {
                    $messages .= ajax_return("error",false,returnMessage(false, $regex_error),true,true);
                }
            } else {
                # nur in conf schreiben wenn sich der wert geändert hat und es kein password ist
                if($conf_plugin->get($name) != $conf_inhalt and $config[$name]['type'] != "password") {
                    $conf_plugin->set($name,$conf_inhalt);
                }
            }
        # checkbox request gibts nicht wenn kein hacken gesetzt ist
        } elseif($config[$name]['type'] == "checkbox" and $conf_plugin->get($name) != "false") {
            $conf_plugin->set($name,"false");
        # variable gibts also schreiben mit lehren wert
        } elseif($conf_plugin->get($name)) {
            $conf_plugin->set($name,"");
        }
    }
    if(strlen($messages) > 0)
        return $messages;
    return ajax_return("success",false);
}
function get_plugin_info($plugin_info) {
    $template = array();

    if(isset($plugin_info[1]) and strlen($plugin_info[1]) > 1)
        $template["plugins_info"][] = array(getLanguageValue("plugins_titel_version"),$plugin_info[1]);

    if(isset($plugin_info[3]) and strlen($plugin_info[3]) > 1)
        $template["plugins_info"][] = array(getLanguageValue("plugins_titel_author"),$plugin_info[3]);
    if(isset($plugin_info[4]) and !empty($plugin_info[4])) {
        $link = '';
        $link_text = '';
        if(is_array($plugin_info[4]) and count($plugin_info[4]) == 2) {
            $link = strip_tags($plugin_info[4][0]);
            $link_text = strip_tags($plugin_info[4][1]);
        } else {
            $link = strip_tags($plugin_info[4]);
            $link_text = strip_tags($plugin_info[4]);
        }
        if(strlen($link_text) > 1 and stristr($link,"http://"))
            $template["plugins_info"][] = array(getLanguageValue("plugins_titel_web"),'<a href="'.$link.'" target="_blank">'.$link_text.'</a>');
    }
    if(isset($plugin_info[2]) and strlen($plugin_info[2]) > 1)
        $template["plugins_info"][] = '<table width="100%" cellspacing="0" border="0" cellpadding="0" class="ui-corner-all"><tr><td align="left" valign="top" width="1%">'
            .'<img class="js-help-plugin mo-tool-icon mo-icons-icon mo-icons-info" src="'.ICON_URL_SLICE.'" alt="info" />'
            .'</td><td align="left" valign="top">'
            .'<div class="mo-help-box js-plugin-help-content d_ui-widget-content ui-corner-all"><div class="js-width-show-helper">'.$plugin_info[2].'</div></div>'
            .'</td></tr></table>';

    return contend_template($template);
}

function get_plugin_config($conf_plugin,$config,$currentelement) {
    if(count($config) < 1)
        return NULL;
    $search = array();
    $replace = array();
    $template = false;
    $pagecontent_conf = NULL;
    $ul_template = array();
    if(isset($config["--template~~"])) {
        $template = $config["--template~~"];
        unset($config["--template~~"]);
    }
    if(isset($config["--admin~~"]) and isset($config["--admin~~"]['description']) and isset($config["--admin~~"]['buttontext'])) {
        $ul_template["config_button"][] = array($config["--admin~~"]['description'],'<input type="button" name="'.$currentelement.'[pluginadmin]" value="'.$config["--admin~~"]['buttontext'].'" class="js-config-adminlogin" />');
        unset($config["--admin~~"]);
    }
    foreach($config as $name => $inhalt) {

        $value = NULL;
        if(strlen($conf_plugin->get($name)) > 0) {
            # in einem input feld type text darf der inhalt keine " haben
            if($config[$name]['type'] == "text") $value = ' value="'.str_replace('"',"&#34;",$conf_plugin->get($name)).'"';
            if($config[$name]['type'] == "textarea")
                $value = $conf_plugin->getToTextarea($name);
            if($config[$name]['type'] == "password") $value = NULL;
        }
        $template_conf = false;
        if(isset($config[$name]['template']))
            $template_conf = true;
        $maxlength = NULL;
        if(isset($config[$name]['maxlength'])) $maxlength = ' maxlength="'.$config[$name]['maxlength'].'"';
        $size = NULL;
        if(isset($config[$name]['size'])) $size = ' size="'.$config[$name]['size'].'"';
        $cols = NULL;
        if(isset($config[$name]['cols'])) $cols = ' cols="'.$config[$name]['cols'].'"';
        $rows = NULL;
        if(isset($config[$name]['rows'])) $rows = ' rows="'.$config[$name]['rows'].'"';
        $multiple = NULL;
        if(isset($config[$name]['multiple']) and $config[$name]['multiple'] == "true") $multiple = ' multiple';
        $css_add = NULL;
        if(empty($size) and empty($cols))
            $css_add = " mo-plugin-size-cols";
        $type = NULL;
        $input = NULL;
        if(isset($config[$name]['type'])) {
            $type = ' type="'.$config[$name]['type'].'"';
            if($config[$name]['type'] == "textarea") {
                $input = '<textarea name="'.$currentelement.'['.$name.']"'.$cols.$rows.' class="mo-plugin-textarea'.$css_add.'">'.$value.'</textarea>';
                if($template !== false or $template_conf === true) {
                    $search[] = '{'.$name.'_textarea}';
                    $replace[] = $input;
                }
            } elseif($config[$name]['type'] == "select") {
                $plus_array = NULL;
                $css_multi = NULL;
                if(!empty($multiple)) {
                    $css_multi = " js-multi";
                    $plus_array = '[]';
                }
                $input = '<select name="'.$currentelement.'['.$name.']'.$plus_array.'"'.$size.$multiple.' class="mo-plugin-select js-select'.$css_multi.$css_add.'">';
                if(is_array($config[$name]['descriptions'])) {
                    foreach($config[$name]['descriptions'] as $key => $descriptions) {
                        $value = ' value="'.$key.'"';
                        $selected = NULL;
                        if($conf_plugin->get($name)) {
                            $select_array = explode(",",$conf_plugin->get($name));
                            foreach($select_array as $test) {
                                if($test == $key) {
                                    $selected = ' selected="selected"';
                                }
                            }
                        }
                        $input .= '<option'.$value.$selected.'>'.$descriptions.'</option>';
                    }
                }
                $input .= '</select>';
                if($template !== false or $template_conf === true) {
                    $search[] = '{'.$name.'_select}';
                    $replace[] = $input;
                }
            } elseif($config[$name]['type'] == "radio") {
                if(is_array($config[$name]['descriptions'])) {
                    $input = '';
                    foreach($config[$name]['descriptions'] as $key => $descriptions) {
                        $value = ' value="'.$key.'"';
                        $checked = NULL;
                        if($conf_plugin->get($name) == $key) {
                            $checked = ' checked="checked"';
                        }
                        $in_radio = '<input name="'.$currentelement.'['.$name.']"'.$type.$value.$checked.' id="'.$currentelement.'-'.$name.'-'.$key.'" />';
                        $descriptions = '<label for="'.$currentelement.'-'.$name.'-'.$key.'">'.$descriptions.'</label>';
                        $input .= $descriptions.'&nbsp;&nbsp;'.$in_radio.'<br />';
                        if($template !== false or $template_conf === true) {
                            $search[] = '{'.$name.'_radio_'.$key.'}';
                            $replace[] = $in_radio;
                            $search[] = '{'.$name.'_descriptions_'.$key.'}';
                            $replace[] = $descriptions;
                        }
                    }
                }
            } elseif($config[$name]['type'] == "checkbox") {
                $checked = NULL;
                if($conf_plugin->get($name) == "true") {
                    $checked = ' checked="checked"';
                }
                $input = '<input name="'.$currentelement.'['.$name.']"'.$type.$checked.' value="true" id="'.$currentelement.'-'.$name.'" />';
                $config[$name]['description'] = '<label for="'.$currentelement.'-'.$name.'">'.$config[$name]['description'].'</label>';
                if($template !== false or $template_conf === true) {
                    $search[] = '{'.$name.'_checkbox}';
                    $replace[] = $input;
                }
            } elseif($config[$name]['type'] == "file") {
                $input = '<span style="background-color:#FF0000;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
            } else {
                $input = '<input name="'.$currentelement.'['.$name.']"'.$type.$value.$maxlength.$size.' class="mo-plugin-input'.$css_add.'" />';
                if($template !== false or $template_conf === true) {
                    $search[] = '{'.$name.'_text}';
                    $replace[] = $input;
                }
            }
        }
        if($template !== false or $template_conf === true) {
            $search[] = '{'.$name.'_description}';
            $replace[] = $config[$name]['description'];
        }

        if(($template === false and $template_conf === false) or $template == "template_test") {
            $ul_template["config_button"][] = array($config[$name]['description'],$input);
        } elseif($template_conf === true) {
            if($config[$name]['template'] == "template_test")
                # wennn in $config[name][template] nur template_test steht alle Platzhalter ausgeben
                $ul_template["config_button"][] = array("Template Platzhalter",implode("<br />", $search));
            else
                $ul_template["config_button"][] = str_replace($search,$replace,$config[$name]['template']);
        }
    }
    # Ausgeben template
    if($template !== false and $template != "template_test") {
        $ul_template["config_button"][] = str_replace($search,$replace,$template);
    }
    # Achtung wenn in $config['--template~~'] nur "template_test" steht werden
    # alle $search ergebnisse ausgegeben
    if($template == "template_test") {
        $ul_template["config_button"][] = array("Template Platzhalter",implode("<br />", $search));
    }

    return '<div class="js-config"><ul class="mo-ul">'
           .'<li class="mo-li ui-widget-content ui-corner-all">'
           .'<div class="js-tools-show-hide mo-li-head-tag mo-tag-height-from-icon mo-li-head-tag-no-ul mo-middle ui-state-default ui-corner-top">'
           .'<table class="mo-tag-height-from-icon" width="100%" cellspacing="0" border="0" cellpadding="0">'
           .'<tbody>'
           .'<tr>'
               .'<td class="mo-nowrap" width="99%">'
                   .'<span class="mo-bold mo-padding-left">'.getLanguageValue("config_button").'</span>'
               .'</td>'
               .'<td class="mo-nowrap">'
                   .'<img class="d_js-tools-icon-show-hide js-save-plugin mo-tool-icon mo-icons-icon mo-icons-save" src="'.ICON_URL_SLICE.'" alt="save" />'
               .'</td>'
           .'</tr>'
           .'</tbody>'
           .'</table>'
           .'</div>'
           .'<ul class="mo-in-ul-ul">'
            .contend_template($ul_template,false,true)
            .'</ul>'
            .'</li>'
            .'</ul></div>';
}
?>