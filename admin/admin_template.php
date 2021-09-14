<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

function admin_Template($pagecontent,$message) {
    $packJS = get_HtmlHead();

    if(!defined('PLUGINADMIN')) {
        echo '<body class="ui-widget" style="font-size:12px;">'
            .'<div id="mo-admin-td" class="mo-td-content-width">'
            .'<noscript><div class="mo-noscript mo-td-content-width ui-state-error ui-corner-all"><div>'.getLanguageValue("error_no_javascript").'</div></div></noscript>';

        get_Head();
        $border = "";
        if(LOGIN) {
            $border = " mo-ui-tabs";
        }
        echo '<div class="mo-td-content-width ui-tabs ui-widget ui-widget-content ui-corner-all'.$border.'" style="position:relative;">';

        if(LOGIN) {
            get_Tabs();
            echo '<div class="'.ACTION.' mo-ui-tabs-panel ui-widget-content ui-corner-bottom mo-no-border-top">';
        }
        $menu_fix = "";
        if(is_array($pagecontent)) {
            $menu_fix = '<div id="menu-fix" class="ui-widget ui-widget-content ui-corner-right">'
                .'<div id="menu-fix-content" class="ui-corner-all">'.$pagecontent[1].'</div>'
                .'</div>';
            $pagecontent = $pagecontent[0];
        }
        echo $pagecontent;
        if(LOGIN) {
            echo "</div>";
        }

        echo $menu_fix
            ."</div>"
            .'<div class="mo-td-content-width" id="out"></div>';
        if(LOGIN)
            echo get_Message($message);
        echo '<img class="mo-td-content-width" src="'.ICON_URL_SLICE.'" alt=" " height="1" hspace="0" vspace="0" align="left" border="0" />'
            .'</div>';

    } else {
        echo '<body class="ui-widget body-pluginadmin" style="font-size:12px;">'
            .$pagecontent;
        if(LOGIN)
            echo get_Message($message);
    }

    $javaScriptPacker = new JavaScriptPacker();
    $javaScriptPacker->echoPack($packJS);

    echo "</body></html>";
}

function get_HtmlHead() {
    global $ADMIN_CONF;
    global $CMS_CONF;
    global $specialchars;
    $packJS = array();
    $packCSS = array();

    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" '."\n"
        .'  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n"
        .'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">'."\n"
        ."<head>"
        .'<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" />'."\n"
        .'<meta name="robots" content="noindex" />'."\n"
#        .'<meta http-equiv="Pragma" content="no-cache" />'
#        .'<meta http-equiv="Cache-Control" content="no-cache" />'
#        .'<meta http-equiv="Expires" content="-1" />'
        .'<title>'.getLanguageValue("cms_admin_titel",true).' - '.getLanguageValue(ACTION."_button").'</title>'."\n"
        .'<link type="image/x-icon" rel="SHORTCUT ICON" href="'.URL_BASE.ADMIN_DIR_NAME.'/favicon.ico" />'."\n";

    $packCSS[] = ADMIN_DIR_NAME.'/css/mozilo/jquery-ui-1.9.2.custom.css';
    $packCSS[] = ADMIN_DIR_NAME.'/admin.css';
    $packCSS[] = ADMIN_DIR_NAME.'/jquery/ui-multiselect-widget/jquery.multiselect.css';
    $packCSS[] = ADMIN_DIR_NAME.'/jquery/ui-multiselect-widget/jquery.multiselect.filter.css';

    if(file_exists(BASE_DIR_ADMIN.ACTION.'.css'))
        $packCSS[] = ADMIN_DIR_NAME.'/'.ACTION.'.css';

    if(ACTION == "files" or ACTION == "gallery" or ACTION == "template") {
        $packCSS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.fileupload-ui.css';
        $packCSS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/bootstrap.cms.css';
    }

    if(ACTION == "catpage" or ACTION == "config" or ACTION == "template")
        $packCSS[] = ADMIN_DIR_NAME.'/jquery/coloredit/coloredit.min.css';

    if(defined('PLUGINADMIN') and is_file(BASE_DIR.PLUGIN_DIR_NAME.'/'.PLUGINADMIN.'/plugin.css'))
        $packCSS[] = PLUGIN_DIR_NAME.'/'.PLUGINADMIN.'/plugin.css';

    $cssMinifier = new cssMinifier();
    $cssMinifier->echoCSS($packCSS);

    $dialog_jslang = array("close","yes","no","button_cancel","button_save","button_preview","page_reload","page_edit_discard","page_cancel_reload","dialog_title_send","dialog_title_error","dialog_title_messages","dialog_title_save_beforeclose","dialog_title_delete","dialog_title_lastbackup","dialog_title_docu","login_titel_dialog","error_name_no_freename","error_save_beforeclose","dialog_title_coloredit","error_exists_file_dir","error_datei_file_name","error_zip_nozip","filter_button_all_hide","filter_button_all_show","filter_text","filter_text_gallery","filter_text_plugins","filter_text_files","filter_text_catpage","config_error_modrewrite","template_title_editor","gallery_text_subtitle","pixels");

    $home_jslang = array("home_error_test_mail");

    $gallery_jslang = array("files","url_adress","page_error_save","images","gallery_delete_confirm");

    $catpage_jslang = array("self","blank","target","page_status","files","pages","page_edit","url_adress","page_error_save",array(EXT_PAGE,"page_saveasnormal"),array(EXT_HIDDEN,"page_saveashidden"),array(EXT_DRAFT,"page_saveasdraft"));

    echo '<script type="text/javascript">/*<![CDATA[*/'."\n"
        .'var FILE_START = "'.FILE_START.'";'
        .'var FILE_END = "'.FILE_END.'";'
        .'var EXT_PAGE = "'.EXT_PAGE.'";'
        .'var EXT_HIDDEN = "'.EXT_HIDDEN.'";'
        .'var EXT_DRAFT = "'.EXT_DRAFT.'";'
        .'var EXT_LINK = "'.EXT_LINK.'";'
        .'var EXT_LENGTH = '.EXT_LENGTH.';'
        .'var action_activ = "'.ACTION.'";'
        .'var URL_BASE = "'.URL_BASE.'";'
        .'var ADMIN_DIR_NAME = "'.ADMIN_DIR_NAME.'";'
        .'var ICON_URL = "'.ICON_URL.'";'
        .'var ICON_URL_SLICE = "'.ICON_URL_SLICE.'";'
        .'var usecmssyntax = "'.$CMS_CONF->get("usecmssyntax").'";'
        .'var modrewrite = "'.$CMS_CONF->get("modrewrite").'";'
        .'var defaultcolors = "'.$specialchars->rebuildSpecialChars($CMS_CONF->get("defaultcolors"),false,false).'";'
        .'var MULTI_USER = "'.((defined('MULTI_USER') and MULTI_USER) ? "true" : "false").'";';

    if(isset(${ACTION."_jslang"}) and is_array(${ACTION."_jslang"}))
        echo makeJsLanguageObject(array_merge($dialog_jslang,${ACTION."_jslang"} ));
    else
        echo makeJsLanguageObject($dialog_jslang);

    $acceptfiletypes = "/(\\.".str_replace("%2C","|\\.",$ADMIN_CONF->get("noupload")).")$/i;";
    if(strlen($acceptfiletypes) > 0)
        # nur die nicht in der liste sind
        echo 'var mo_acceptFileTypes = '.$acceptfiletypes;
    else
        # alle erlauben
        echo 'var mo_acceptFileTypes = /#$/i;';
/*
    if(LOGIN and defined('MULTI_USER') and MULTI_USER)
       echo 'var multi_user_time = '.((MULTI_USER_TIME - 10) * 1000).';'; # Sekunde * 1000 = Millisekunden
*/
    if(ACTION == "catpage" or ACTION == "config" or ACTION == "template")
         echo 'var mo_docu_coloredit = \''.str_replace("/",'\/',getHelpIcon("editsite","color")).'\';';

    echo '/*]]>*/</script>'."\n"
        .'<script type="text/javascript" src="'.URL_BASE.CMS_DIR_NAME.'/jquery/jquery-'.ADMIN_JQUERY.'.min.js"></script>'."\n"
        .'<script type="text/javascript" src="'.URL_BASE.CMS_DIR_NAME.'/jquery/jquery-ui-'.ADMIN_JQUERY_UI.'.custom.min.js"></script>'."\n";

/*
    if(LOGIN and defined('MULTI_USER') and MULTI_USER)
        $packJS[] = ADMIN_DIR_NAME.'/jquery/multi_user.js';
*/

    if(ACTION == "catpage" or ACTION == "files" or ACTION == "plugins" or ACTION == "gallery")
        $packJS[] = ADMIN_DIR_NAME.'/jquery/filter.js';

    $packJS[] = ADMIN_DIR_NAME.'/jquery/dialog.js';
    $packJS[] = ADMIN_DIR_NAME.'/jquery/default.js';
    $packJS[] = ADMIN_DIR_NAME.'/jquery/ui-multiselect-widget/src/jquery.multiselect.js';
    $packJS[] = ADMIN_DIR_NAME.'/jquery/ui-multiselect-widget/src/jquery.multiselect.filter.js';


    if(file_exists(BASE_DIR_ADMIN."jquery/".ACTION.'.js')) {
        $packJS[] = ADMIN_DIR_NAME.'/jquery/'.ACTION.'.js';
        if(file_exists(BASE_DIR_ADMIN."jquery/".ACTION.'_func.js')) {
            $packJS[] = ADMIN_DIR_NAME.'/jquery/'.ACTION.'_func.js';
        }
    }

    if(ACTION == "catpage" or ACTION == "config" or ACTION == "template")
        $packJS[] = ADMIN_DIR_NAME.'/jquery/coloredit/coloredit.js';

    if((ACTION == "config" and (ROOT or in_array("editusersyntax",$ADMIN_CONF->get("config")))) or ACTION == "catpage" or ACTION == "template") {
        $packJS[] = ADMIN_DIR_NAME.'/jquery/dialog-editor-ace.js';
        require_once(BASE_DIR_ADMIN."ace_editor/mozilo_edit_ace.php");
        echo $editor_area_html;
    }

    if(ACTION == "files" or ACTION == "gallery" or ACTION == "template") {
        echo '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/load-image.min.js"></script>'."\n";

        $packJS[] = ADMIN_DIR_NAME.'/jquery/dialog_prev.js';
        $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.iframe-transport.js';
        $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.fileupload.js';
        $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.fileupload-ip.js';
        $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.fileupload-ui.js';
        $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/locale.js';
        $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/fileupload-cms-ui.js';

        if(ACTION != "gallery") {
            $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/fileupload.template.js';
        } else {
            $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/fileupload.template_gal.js';
        }
        $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/fileupload.js';
    }

#!!!!!!!!!!! nee function insert_in_head und alle js und css über die einzelnen ACTION.php steuern
    # der plugin eigene admin ist im dialog fenster
    global $PLUGIN_ADMIN_ADD_HEAD;
    $unique = false;
    $packCSS = array();
    if(defined('PLUGINADMIN') and is_array($PLUGIN_ADMIN_ADD_HEAD)) {
        foreach($PLUGIN_ADMIN_ADD_HEAD as $pos => $item) {
            if(strpos($item,"<script") !== false and strpos($item,"src=") !== false) {
                preg_match('#<(script){1,1}[^>]*?(src){1,1}=["\'](.*)["\'][^>]*?>#is', $item,$match);
                if(isset($match[3]) and strpos($match[3],".min.js") === false) {
                    $packJS[] = substr_replace($match[3],"",0,strlen(URL_BASE));
                    unset($PLUGIN_ADMIN_ADD_HEAD[$pos]);
                    $unique = true;
                }
            } elseif(strpos($item,"<link") !== false and strpos($item,"href=") !== false) {
                preg_match('#<(link){1,1}[^>]*?(href){1,1}=["\'](.*)["\'][^>]*?>#is', $item,$match);
                if(isset($match[3]) and strpos($match[3],".min.css") === false) {
                    $packCSS[] = substr_replace($match[3],"",0,strlen(URL_BASE));
                    unset($PLUGIN_ADMIN_ADD_HEAD[$pos]);
                }
            }
        }
        if(count($packCSS) > 0)
            $cssMinifier->echoCSS($packCSS);

        if($unique)
            $packJS = array_unique($packJS);
        echo implode("",$PLUGIN_ADMIN_ADD_HEAD);
    }

    echo "</head>"."\n";
    return $packJS;
}

function get_Head() {
    global $CMS_CONF, $specialchars;

    echo '<div class="mo-td-content-width mo-margin-bottom">'
        .'<div class="mo-align-center mo-head-box ui-widget ui-state-default ui-corner-all mo-li-head-tag-no-ul mo-li-head-tag mo-td-middle ui-helper-clearfix">'
            .'<span style="float:left;" class="mo-td-middle">'
                .getHelpIcon()
                .'<a href="../index.php?draft=true" title="'.getLanguageValue("help_website_button",true).'" target="_blank" class="mo-butten-a-img"><img class="mo-icons-icon mo-icons-website" src="'.ICON_URL_SLICE.'" alt="" /></a>'
                .'<span class="mo-bold mo-td-middle mo-padding-left">'
                    .getLanguageValue("cms_admin_titel",true)
                .'</span>'
            .'</span>'
            .'<span id="admin-websitetitle" class="mo-bold mo-td-middle">'
                .$specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"), false, true)
            .'</span>'
            .'<img style="width:1px;" class="mo-icons-icon mo-icons-blank mo-td-middle" src="'.ICON_URL_SLICE.'" alt="" />'
            .'<a style="float:right;" href="index.php?logout=true" title="'.getLanguageValue("logout_button",true).'" class="mo-butten-a-img"><img class="mo-icons-icon mo-icons-logout" src="'.ICON_URL_SLICE.'" alt="" /></a>'
        ."</div>"
    ."</div>";
}


function get_Tabs() {
    global $array_tabs;
    global $users_array;

    $multi_user = "";
    if(defined('MULTI_USER') and MULTI_USER)
        $multi_user = "&amp;multi=true";

    echo '<ul id="js-menu-tabs" class="mo-menu-tabs ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-top">';
    foreach($array_tabs as $position => $language) {
        echo '<li class="js-multi-user ui-state-default ui-corner-top'
            .((ACTION == $language) ? " ui-tabs-selected ui-state-active" : " js-hover-default mo-ui-state-hover")
            .(($language != "home" and in_array($language,$users_array)) ? " ui-state-disabled js-no-click" : "").'">'
            .'<a href="index.php?nojs=true&amp;action='.$language.$multi_user.'" title="'.getLanguageValue($language."_button",true).'" name="'.$language.'"><img class="js-menu-icon mo-icon-text-right mo-tabs-icon mo-tab-'.$language.'" src="'.ICON_URL_SLICE.'" alt=" " hspace="0" vspace="0" border="0" /><span class="mo-bold">'.getLanguageValue($language."_button").'</span></a>'
            .'</li>';
    }
    echo '</ul>';
}

function get_Message($message) {
    global $LOGINCONF;
    global $ADMIN_CONF;

    $html = "";

    if(!empty($message)) {
        if(is_array($message)) {
            foreach($message as $inhalt) {
                $html .= $inhalt;
            }
        } else {
            $html .= $message;
        }
    }

    // Warnung, wenn seit dem letzten Login Logins fehlgeschlagen sind
    if ($LOGINCONF->get("falselogincount") > 0) {
        $html .= returnMessage(false, getLanguageValue("messages_false_logins")." ".$LOGINCONF->get("falselogincount"));
        // Gesamt-Counter fuer falsche Logins zuruecksetzen
        $LOGINCONF->set("falselogincount", 0);
    }

    // Warnung, wenn die letzte Backupwarnung mehr als $intervallsetting Tage her ist
    if(ROOT or (is_array($ADMIN_CONF->get("admin"))
            and in_array("backupmsgintervall",$ADMIN_CONF->get("admin")))) {
        $intervallsetting = $ADMIN_CONF->get("backupmsgintervall");
        if($intervallsetting != "" and $intervallsetting > 0) {
            $intervallinseconds = 60 * 60 * 24 * $intervallsetting;
            $lastbackup = $ADMIN_CONF->get("lastbackup");
            // initial: nur setzen 
            if($lastbackup == "") {
                $ADMIN_CONF->set("lastbackup",time());
            // wenn schon gesetzt: pruefen und ggfs. warnen
            } else {
                $nextbackup = $lastbackup + $intervallinseconds;
                if($nextbackup <= time())    {
                    $html .= '<span id="lastbackup">'.returnMessage(true,getLanguageValue("admin_messages_backup")).'</span><span style="display:none;" id="lastbackup_yes">lastbackup_yes=true</span>';
                }
            }
        }
    }

    if(strlen($html) > 1)
        return '<div id="dialog-auto" style="display:none;">'.$html.'</div>';
    else
        return "";
}

function getLanguageJsVar($key) {
    global $LANGUAGE;
    return str_replace(array("[","]","{","}","'",'"',"(",")"),
                   array("\[","\]","\{","\}","\'",'\"',"\(","\)"),
                    $LANGUAGE->getLanguageValue($key));
}

function makeJsLanguageObject($lang_array) {
    $tmp = 'var mozilo_lang = new Object(); ';
    foreach($lang_array as $key) {
        if(is_array($key))
            $tmp .= 'mozilo_lang["'.$key[0].'"] = "'.getLanguageJsVar($key[1]).'"; ';
        else
            $tmp .= 'mozilo_lang["'.$key.'"] = "'.getLanguageJsVar($key).'"; ';
    }
    return $tmp;
}

?>