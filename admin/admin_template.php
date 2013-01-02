<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

function admin_Template($pagecontent,$message) {
    $html = get_HtmlHead();
    $js_loginfocus = '';
    if(!LOGIN)
        $js_loginfocus = ' onLoad="document.loginform.username.focus();document.loginform.username.select()"';

    $html .= '<body class="ui-widget" style="font-size:12px;"'.$js_loginfocus.'>';

    if(!defined('PLUGINADMIN')) {

        $html .= '<table summary="" width="100%" cellspacing="0" border="0" cellpadding="0">';
        $html .= '<tr><td>&nbsp;</td><td id="mo-admin-td" class="mo-td-content-width">';

        $html .= '<noscript><div class="mo-noscript mo-td-content-width ui-state-error ui-corner-all"><div>'.getLanguageValue("error_no_javascript").'</div></div></noscript>';

        $html .= get_Head();
        $border = "";
        if(LOGIN) {
            $border = " mo-ui-tabs";
        }
        $html .= '<div class="mo-td-content-width ui-tabs ui-widget ui-widget-content ui-corner-all'.$border.'" style="position:relative;">';

        if(LOGIN) {
            $html .= get_Tabs();
            $html .= '<div class="'.ACTION.' mo-ui-tabs-panel ui-widget-content ui-corner-bottom mo-no-border-top">';
        }
        $menu_fix = "";
        if(is_array($pagecontent)) {
#            $menu_fix = '<div id="menu-fix" class="mo-td-content-width ui-widget"><div class="ui-state-error ui-corner-all">'.$pagecontent[1].'</div></div>';
$menu_fix = '<div id="menu-fix" class="ui-widget ui-widget-content ui-corner-right">'
    .'<div id="menu-fix-content" class="ui-corner-all">'.$pagecontent[1].'</div>'
.'</div>';

            $pagecontent = $pagecontent[0];
        }
        $html .= $pagecontent;
        if(LOGIN) {
            $html .= "</div>";
        }

        $html .= $menu_fix;
        $html .= "</div>";
#        $html .= $menu_fix;
        $html .= '<div class="mo-td-content-width" id="out"></div>';
        if(LOGIN)
            $html .= get_Message($message);
        $html .= '<img class="mo-td-content-width" src="'.ICON_URL_SLICE.'" alt=" " height="1" hspace="0" vspace="0" align="left" border="0" />';
#-------------------------------------
        $html .= '</td><td>&nbsp;</td></tr></table>';
#$html .= '<div style="width:800px;height:300px;position:relative;"><div id="pagecontent"></div></div>';

    } else {
        $html .= $pagecontent;
        if(LOGIN)
            $html .= get_Message($message);
    }

    $html .= "</body></html>";

    if(strpos($html,"<!--{MEMORYUSAGE}-->") > 1)
        $html = str_replace("<!--{MEMORYUSAGE}-->",get_memory(),$html);

    if(strpos($html,"<!--{EXECUTETIME}-->") > 1)
        $html = str_replace("<!--{EXECUTETIME}-->",get_executTime(START_TIME),$html);

    return $html;
}

function get_HtmlHead() {
    global $ADMIN_CONF;
    global $CMS_CONF;
    global $specialchars;
    $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" '."\n"
        .'  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n"
        .'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">'."\n";
    $html .= "<head>";
    $html .= '<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" />';
    $html .= '<title>'.getLanguageValue("cms_admin_titel",true).' - '.getLanguageValue(ACTION."_button").'</title>';
    $html .= '<link type="image/x-icon" rel="SHORTCUT ICON" href="'.URL_BASE.ADMIN_DIR_NAME.'/favicon.ico" />';

    $html .= '<link type="text/css" rel="stylesheet" href="'.URL_BASE.ADMIN_DIR_NAME.'/css/mozilo/jquery-ui-1.8.21.custom.css" />';

# new jquery test
#    $html .= '<link type="text/css" rel="stylesheet" href="'.URL_BASE.ADMIN_DIR_NAME.'/css/test192/jquery-ui-1.9.2.custom.css" />';
    $html .= '<link type="text/css" rel="stylesheet" href="admin.css" />';
#    if(!LOGIN)
#        $html .= '<link type="text/css" rel="stylesheet" href="login.css" />';
if(defined('PLUGINADMIN'))
    $html .= '<link type="text/css" rel="stylesheet" href="'.URL_BASE.PLUGIN_DIR_NAME.'/'.PLUGINADMIN.'/plugin.css" />';

    if(file_exists(BASE_DIR_ADMIN.ACTION.'.css'))
        $html .= '<link type="text/css" rel="stylesheet" href="'.ACTION.'.css" />';
    $html .= '<script language="Javascript" type="text/javascript">/*<![CDATA[*/';
    $html .= 'var FILE_START = "'.FILE_START.'"; ';
    $html .= 'var FILE_END = "'.FILE_END.'"; ';
    $html .= 'var EXT_PAGE = "'.EXT_PAGE.'"; ';
    $html .= 'var EXT_HIDDEN = "'.EXT_HIDDEN.'"; ';
    $html .= 'var EXT_DRAFT = "'.EXT_DRAFT.'"; ';
    $html .= 'var EXT_LINK = "'.EXT_LINK.'"; ';
    $html .= 'var EXT_LENGTH = '.EXT_LENGTH.'; ';
    $html .= 'var action_activ = "'.ACTION.'"; ';
#    $html .= 'var icons_src = "'.ADMIN_ICONS.'";';
    $html .= 'var URL_BASE = "'.URL_BASE.'";';
    $html .= 'var ADMIN_DIR_NAME = "'.ADMIN_DIR_NAME.'";';
#    $html .= 'var ICON_SIZE = "'.ICON_SIZE.'";';
    $html .= 'var ICON_URL = "'.ICON_URL.'";';
    $html .= 'var ICON_URL_SLICE = "'.ICON_URL_SLICE.'";';
#    $html .= 'var ADMIN_ICONS = "'.ADMIN_ICONS.'";';
    $html .= 'var usecmssyntax = "'.$CMS_CONF->get("usecmssyntax").'";';
    $html .= 'var modrewrite = "'.$CMS_CONF->get("modrewrite").'";';
    $html .= 'var defaultcolors = "'.$specialchars->rebuildSpecialChars($CMS_CONF->get("defaultcolors"),false,false).'";';

    $dialog_jslang = array("close","yes","no","button_cancel","button_save","page_reload","page_edit_discard","page_cancel_reload","dialog_title_send","dialog_title_error","dialog_title_messages","dialog_title_save_beforeclose","dialog_title_delete","dialog_title_lastbackup","dialog_title_docu","login_titel_dialog","error_name_no_freename","error_save_beforeclose","dialog_title_coloredit");

    $home_jslang = array("home_error_test_mail");

    $gallery_jslang = array("files","url_adress","page_error_save","images");

    $catpage_jslang = array("self","blank","target","page_status","files","pages","page_edit","url_adress","page_error_save",array(EXT_PAGE,"page_saveasnormal"),array(EXT_HIDDEN,"page_saveashidden"),array(EXT_DRAFT,"page_saveasdraft"));


    if(isset(${ACTION."_jslang"}) and is_array(${ACTION."_jslang"})) {
        $html .= makeJsLanguageObject(array_merge($dialog_jslang,${ACTION."_jslang"} ));
    } else
        $html .= makeJsLanguageObject($dialog_jslang);

    $acceptfiletypes = "/(\\.".str_replace("%2C","|\\.",$ADMIN_CONF->get("noupload")).")$/i;";
    if(strlen($acceptfiletypes) > 0)
        # nur die nicht in der liste sind
       $html .= 'var mo_acceptFileTypes = '.$acceptfiletypes;
    else
        # alle erlauben
        $html .= 'var mo_acceptFileTypes = /#$/i;';
    if(LOGIN and defined('MULTI_USER') and MULTI_USER) {
       $html .= 'var multi_user_time = '.((MULTI_USER_TIME - 10) * 1000).';'; # Sekunde * 1000 = Millisekunden
    }

    $html .= '/*]]>*/</script>';
    $html .= '<script type="text/javascript" src="'.URL_BASE.CMS_DIR_NAME.'/jquery/jquery-1.7.2.min.js"></script>';
    $html .= '<script type="text/javascript" src="'.URL_BASE.CMS_DIR_NAME.'/jquery/jquery-ui-1.8.22.custom.min.js"></script>';

# new jquery test
#    $html .= '<script type="text/javascript" src="'.URL_BASE.CMS_DIR_NAME.'/jquery/jquery-1.8.3.min.js"></script>';
#    $html .= '<script type="text/javascript" src="'.URL_BASE.CMS_DIR_NAME.'/jquery/jquery-ui-1.9.2.custom.min.js"></script>';
 
    if(LOGIN and defined('MULTI_USER') and MULTI_USER) {
        $html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/multi_user.js"></script>';
    }
    $html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/dialog.js"></script>';
    $html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/default.js"></script>';
#    if(file_exists(BASE_DIR_ADMIN."docu/docu.js"))
#        $html .= '<script type="text/javascript" src="docu/docu.js"></script>';

    $html .= '<link rel="stylesheet" type="text/css" href="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/ui-multiselect-widget/jquery.multiselect.css" />';
    $html .= '<link rel="stylesheet" type="text/css" href="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/ui-multiselect-widget/jquery.multiselect.filter.css" />';
    $html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/ui-multiselect-widget/src/jquery.multiselect.js"></script>';
    $html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/ui-multiselect-widget/src/jquery.multiselect.filter.js"></script>';


    if(file_exists(BASE_DIR_ADMIN."jquery/".ACTION.'.js'))
        $html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/'.ACTION.'.js"></script>';
    if(file_exists(BASE_DIR_ADMIN."jquery/".ACTION.'_func.js'))
        $html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/'.ACTION.'_func.js"></script>';

    if((ACTION == "catpage" and (ROOT or in_array("editusersyntax",$ADMIN_CONF->get("config")))) or ACTION == "config" or ACTION == "template") {
        $html .= '<link type="text/css" rel="stylesheet" href="jquery/coloredit/coloredit.min.css" />';
        $html .= '<script type="text/javascript" charset="utf-8" src="jquery/coloredit/coloredit.js"></script>';
    }

    if((ACTION == "config" and (ROOT or in_array("editusersyntax",$ADMIN_CONF->get("config")))) or ACTION == "catpage" or ACTION == "template") {
        $html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/dialog-editor-ace.js"></script>';
        require_once(BASE_DIR_ADMIN."ace_editor/mozilo_edit_ace.php");
        $html .= $editor_area_html;
    }

    if(ACTION == "files" or ACTION == "gallery" or ACTION == "template") {
        $html .= '<link type="text/css" rel="stylesheet" href="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.fileupload-ui.css" />';

$html .= '<!-- Bootstrap CSS Toolkit styles -->
<link type="text/css" rel="stylesheet" href="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/bootstrap.cms.css" />';

$html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/load-image.min.js"></script>';

        $html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/dialog_prev.js"></script>';

        $html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.iframe-transport.js"></script>';

        $html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.fileupload.js"></script>';

$html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.fileupload-ip.js"></script>';
        $html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.fileupload-ui.js"></script>';
$html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/locale.js"></script>';
$html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/fileupload-cms-ui.js"></script>';

if(ACTION != "gallery")
$html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/fileupload.template.js"></script>';
else
$html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/fileupload.template_gal.js"></script>';
$html .= '<script type="text/javascript" src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/fileupload.js"></script>';

    }
    $html .= "</head>";
    return $html;
}

function get_Head() {
    $html = '<div class="mo-td-content-width mo-margin-bottom">';
    $html .= '<table width="100%" cellspacing="0" border="0" cellpadding="0" class="ui-widget ui-state-default ui-corner-all mo-tag-height-from-icon">';
    $html .= '<tr>';
    $html .= '<td width="1%" nowrap="nowrap" class="align-left">';
    $html .= '<div class="mo-padding-left">';
    $html .= getHelpIcon();
    $html .= '<a href="../index.php?draft=true" title="'.getLanguageValue("help_website_button",true).'" target="_blank" class="mo-butten-a-img"><img class="mo-icons-icon mo-icons-website" src="'.ICON_URL_SLICE.'" alt="" /></a>';
    $html .= '</div>';
    $html .= '</td>';
    $html .= '<td class="mo-td-middle">';
    $html .= '<b>'.getLanguageValue("cms_admin_titel",true).' - <i>'.getLanguageValue(ACTION."_button").'</i> - <!--{EXECUTETIME}--> <!--{MEMORYUSAGE}--></b>';
    $html .= '</td>';

    $html .= '<td nowrap="nowrap" class="align-right">';
    $html .= '<a href="index.php?logout=true" title="'.getLanguageValue("logout_button",true).'" target="_self" class="mo-butten-a-img"><img class="mo-icons-icon mo-icons-logout" src="'.ICON_URL_SLICE.'" alt="" /></a>';
    $html .= '</td></tr>';
    $html .= '</table>';
    $html .= "</div>";
    return $html;
}


function get_Tabs() {
    global $array_tabs;
    global $users_array;

    $multi_user = "";
    if(defined('MULTI_USER') and MULTI_USER)
        $multi_user = "&amp;multi=true";

    $html = '<ul id="js-menu-tabs" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-top">';
    foreach($array_tabs as $position => $language) {
        if(ACTION == $language)
            $activ = " ui-tabs-selected ui-state-active";# js-no-click
        else
            $activ = " js-hover-default";

        $deact_user = "";
        if($language != "home" and in_array($language,$users_array))
            $deact_user = " ui-state-disabled js-no-click";

        $html .= '<li class="js-multi-user ui-state-default ui-corner-top'.$activ.$deact_user.'">';
        $html .= '<a href="index.php?action='.$language.$multi_user.'" title="'.getLanguageValue($language."_button",true).'" name="'.$language.'"><img class="js-menu-icon mo-icon-text-right mo-tabs-icon mo-tab-'.$language.'" src="'.ICON_URL_SLICE.'" alt=" " hspace="0" vspace="0" border="0" /><span class="mo-bold">'.getLanguageValue($language."_button").'</span></a>';
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
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