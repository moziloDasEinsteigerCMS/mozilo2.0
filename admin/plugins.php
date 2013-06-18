<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

$debug = "";

function plugins() {
    global $ADMIN_CONF;
    global $CatPage;
    global $message;


global $debug;
    $plugin_manage_open = false;
    # plugins löschen
    if(getRequestValue('plugin-all-del','post') and getRequestValue('plugin-del','post')) {
        plugin_del();
        $plugin_manage_open = true;
    }
    # hochgeladenes plugin installieren
    if(isset($_FILES["plugin-install-file"]["error"]) and getRequestValue('plugin-install','post')) {
$debug .= "install=".$_FILES["plugin-install-file"]["name"]."<br />\n";
        if($_FILES["plugin-install-file"]["error"] == 0 and strtolower(substr($_FILES["plugin-install-file"]["name"],-4)) == ".zip") {
            plugin_install();
            $plugin_manage_open = true;
        }
    }

$debug = false;
if($debug)
    $message .= returnMessage(false,$debug);


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

    $pagecontent = '';

    $show = $ADMIN_CONF->get("plugins");
    if(!is_array($show))
        $show = array();

    if(ROOT or in_array("plugin_-_manage",$show)) {
        $multi_user = "";
        if(defined('MULTI_USER') and MULTI_USER)
            $multi_user = "&amp;multi=true";

        $html_manage = "";
        $plugin_manage = array();
        $disabled = '';
        if(!function_exists('gzopen'))
            $disabled = ' disabled="disabled"';
        $plugin_manage["plugins_title_manage"][] = '<form id="js-plugin-manage" action="index.php?nojs=true&amp;action=plugins'.$multi_user.'" method="post" enctype="multipart/form-data">'
            .'<div class="mo-nowrap align-right">'
                .'<span class="align-left" style="float:left"><span class="mo-bold">'.getLanguageValue("plugins_text_filebutton").'</span><br />'.getLanguageValue("plugins_text_fileinfo").'</span>'
                .'<input type="file" id="js-plugin-install-file" name="plugin-install-file" class="mo-select-div"'.$disabled.' />'
                .'<input type="submit" id="js-plugin-install-submit" name="plugin-install" value="'.getLanguageValue("plugins_button_install",true).'"'.$disabled.' /><br />'
                .'<input type="submit" id="js-plugin-del-submit" value="'.getLanguageValue("plugins_button_delete",true).'" class="mo-margin-top js-send-del-stop" /><br class="mo-clear" />'
            .'</div></form>';

        $plugin_manage["plugins_title_manage"]["toggle"] = true;
        $html_manage = contend_template($plugin_manage);
        $html_manage = str_replace("js-toggle","js-toggle-manage",$html_manage);
        # es wurde in der template verwaltung was gemacht dann soll die aufgeklapt bleiben
        if($plugin_manage_open)
            $html_manage = str_replace("display:none;","",$html_manage);
        $pagecontent .= $html_manage;
    }

    $pagecontent .= '<ul class="js-plugins mo-ul">';

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
            $check_show = ' style="display:none;"';
            if($plugin_manage_open)
                $check_show = '';
            if($plugin_error === false) {
                $pagecontent .= '<span class="js-plugin-name mo-padding-left mo-middle">'.$plugin_name.'</span>'
                    .'<div style="float:right;" class="mo-tag-height-from-icon mo-middle mo-nowrap">'
                    .'<span class="js-plugin-active mo-staus">'.buildCheckBox($currentelement.'[active]', ($conf_plugin->get("active") == "true"),getLanguageValue("plugins_input_active")).'</span>'
                    .'<img class="js-tools-icon-show-hide js-toggle mo-tool-icon mo-icons-icon mo-icons-edit" src="'.ICON_URL_SLICE.'" alt="edit" />'
                    .'<input type="checkbox" value="'.$currentelement.'" class="mo-checkbox mo-checkbox-del js-plugin-del"'.$check_show.' />'
                    .'</div>'
                    .'<br class="mo-clear" />'
                    .'</div>'
                    .'<div class="js-toggle-content mo-in-ul-ul" style="display:none;">'
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
        $template["plugins_info"][] = ''
            .'<img style="float:left;" class="js-help-plugin mo-tool-icon mo-icons-icon mo-icons-info" src="'.ICON_URL_SLICE.'" alt="info" />'
            .'<div style="margin-left:3em;">'
            .'<div style="display:none;" class="mo-help-box js-plugin-help-content ui-corner-all" style="display:none;margin-left:3em;">'.$plugin_info[2].'</div>'
            .'</div>'
            .'<div class="mo-clear"></div>';

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
    $show = '';
    if($conf_plugin->get("active") != "true")
        $show = ' style="display:none;"';
    return '<div class="js-config"'.$show.'><ul class="mo-ul">'
            .'<li class="mo-li ui-widget-content ui-corner-all">'
            .'<div class="js-tools-show-hide mo-li-head-tag mo-tag-height-from-icon mo-li-head-tag-no-ul mo-middle ui-state-default ui-corner-top">'
            .'<span class="mo-bold mo-padding-left">'.getLanguageValue("config_button").'</span>'
            .'<img style="float:right;" class="js-save-plugin mo-tool-icon mo-icons-icon mo-icons-save" src="'.ICON_URL_SLICE.'" alt="save" />'
            .'<br class="mo-clear" />'
           .'</div>'
           .'<ul class="mo-in-ul-ul">'
            .contend_template($ul_template,false,true)
            .'</ul>'
            .'</li>'
            .'</ul></div>';
}


function plugin_del() {
    global $specialchars;
    global $message;
global $debug;

    $plugin_del = getRequestValue('plugin-del','post');
    if(is_array($plugin_del)) {
        foreach($plugin_del as $plugin) {
$debug .= "del=".$plugin."<br />\n";
            if(true !== ($error = deleteDir(BASE_DIR.PLUGIN_DIR_NAME."/".$plugin)))
                $message .= $error;/**/
        }
    } else {
        $message .= returnMessage(false,getLanguageValue("error_post_parameter"));
    }
}

function plugin_install() {
    global $message;
    if(!function_exists('gzopen'))
        return;
    global $specialchars;
global $debug;

    $dir = BASE_DIR.PLUGIN_DIR_NAME."/";
    $zip_file = $dir.$specialchars->replaceSpecialChars($_FILES["plugin-install-file"]["name"],false);
    $zip_name = substr($_FILES["plugin-install-file"]["name"],0,-4);

    if(true === (move_uploaded_file($_FILES["plugin-install-file"]["tmp_name"], $zip_file))) {

        require_once(BASE_DIR_ADMIN."pclzip.lib.php");
        $archive = new PclZip($zip_file);

        if(0 != ($list = $archive->listContent())) {
/*$debug .= "<pre>";
$debug .= var_export($list,true);
$debug .= "</pre><br />";
*/
            $add_dir = false;
            # der replace pfad ist erstmal der zip file name
            $remove_dir = $zip_name;
            $tmp_dir_merker = false;
            $tmp_index = false;
            foreach($list as $tmp) {
                # fehler im zip keine ../ im pfad erlaubt
                if(false !== strpos($tmp["filename"],"../")) {
                    $tmp_index = false;
                    break;
                }
#$debug .= "test_dir1=".$tmp["filename"]."<br />";
#$debug .= "test_dir2=".basename($tmp["filename"])."<br />";
                # wir suchen den ordner wo die index.php enthalten ist
                if(basename($tmp["filename"]) == "index.php") {
                    $tmp_dir = substr($tmp["filename"],0,-(strlen("index.php")));
                    # da scheint noch nee index.php in eine unterordner zu sein
                    if($tmp_dir_merker !== false and strlen($tmp_dir) > strlen($tmp_dir_merker))
                        continue;
                    $tmp_dir_merker = $tmp_dir;
                    $tmp_index = $tmp["index"];
$debug .= "index_dir1=".$tmp_dir."<br />";
                    if(!empty($tmp_dir) and $tmp_dir[(strlen($tmp_dir)-1)] == "/")
                        $tmp_dir = substr($tmp_dir,0,-1);
$debug .= "index_dir2=".$tmp_dir."<br />";
                    # das template ist im zip in einen eigenen ordner
                    if(strrpos($tmp_dir,"/") !== false) {
                        # der replace pfad damit der eigene template ordner aus dem zip benutzt wird
                        $remove_dir = $tmp_dir;
                    }
                }
            }
            # wenn wir die index nummer von der index.php gefunden haben
            if($tmp_index !== false) {
                # nur index.php auspacken
                $archive->extractByIndex($tmp_index
                    ,PCLZIP_OPT_PATH, $dir
                    ,PCLZIP_OPT_REMOVE_ALL_PATH
                    ,PCLZIP_OPT_REPLACE_NEWER);
                # wurde die index.php entpackt
                if(is_file($dir."index.php")) {
                    # die index.php einlessen
                    if(false !== ($tmp_index = @file($dir."index.php"))) {
                        foreach($tmp_index as $rows) {
                            # die zeile suchen wo der Pluginname steht
                            if(strpos($rows,"class") !== false and strpos($rows,"extends") !== false and strpos($rows,"Plugin") !== false) {
                                $rows = substr($rows,0,strpos($rows,"extends"));
                                $rows = str_replace("class","",$rows);
                                $add_dir = trim($rows);
#$debug .= "#".$add_dir."#"."<br />";
                                # ist das ein gültiger Pluginname
                                if($specialchars->replaceSpecialChars($add_dir,true) != $add_dir)
                                    $add_dir = false;
                                break;
                            }
                        }
                        unset($tmp_index);
                    }
                }
                unlink($dir."index.php");
            }

            # wenn es den Pluginnamen gibt
            if($add_dir !== false and getChmod() !== false) {
                $list = $archive->extract(PCLZIP_OPT_PATH, $dir
                    ,PCLZIP_OPT_ADD_PATH, $add_dir
                    ,PCLZIP_OPT_REMOVE_PATH, $remove_dir
                    ,PCLZIP_OPT_SET_CHMOD, getChmod()
                    ,PCLZIP_CB_PRE_EXTRACT, "PclZip_PreExtractCallBack"
                    ,PCLZIP_OPT_REPLACE_NEWER);
                setChmod($dir.$add_dir);
            } elseif($add_dir !== false) {
                $list = $archive->extract(PCLZIP_OPT_PATH, $dir
                    ,PCLZIP_OPT_ADD_PATH, $add_dir
                    ,PCLZIP_OPT_REMOVE_PATH, $remove_dir
                    ,PCLZIP_CB_PRE_EXTRACT, "PclZip_PreExtractCallBack"
                    ,PCLZIP_OPT_REPLACE_NEWER);
            } else {
                # die file strucktur im zip stimt nicht
                $message .= returnMessage(false,getLanguageValue("error_zip_structure"));
            }
        } else {
            # scheint kein gühltiges zip zu sein
            $message .= returnMessage(false,getLanguageValue("error_zip_nozip"));
        }
        unlink($zip_file);
    } else {
        # das zip konnte nicht hochgeladen werden
        $message .= returnMessage(false,getLanguageValue("error_file_upload"));
    }
}


function PclZip_PreExtractCallBack($p_event, &$p_header) {
global $debug;
    if(basename($p_header['filename']) == "plugin.conf.php" and is_file($p_header['filename'])) {
$debug .= "nicht überschreiben=".$p_header['filename']."<br />";
        return 0;
}
    if(!$p_header['folder'] and substr($p_header['filename'],-4) != ".php" and !isValidDirOrFile(basename($p_header['filename'])))
        return 0;
$debug .= $p_header['filename']."<br />";
    return 1;
}


?>