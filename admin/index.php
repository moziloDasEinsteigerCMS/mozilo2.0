<?php
define("IS_CMS", true);
define("IS_ADMIN", true);
# wenn der Ordner admin geändert wurde reicht es in hier einzutragen
define("ADMIN_DIR_NAME","admin");
define("CMS_DIR_NAME","cms");
# fals da bei winsystemen \\ drin sind in \ wandeln
$BASE_DIR = str_replace("\\\\", "\\",__FILE__);
# zum schluss noch den teil denn wir nicht brauchen abschneiden
$BASE_DIR = substr($BASE_DIR,0,-(strlen(ADMIN_DIR_NAME."/index.php")));
define("BASE_DIR",$BASE_DIR);
unset($BASE_DIR);

$name_id = 'MOZILOID_'.md5($_SERVER['SERVER_NAME'].BASE_DIR);
define("SESSION_MO",$name_id);
unset($name_id);

if(is_file("sessionClass.php")) {
   require_once("sessionClass.php");
} else
    @session_name(SESSION_MO);

session_start();

if(strtolower(substr("PHP_OS",0,3)) == "win")
    define("USE_CHMOD", false);
else
    define("USE_CHMOD", true);

define("DRAFT", true);

# -1 für debug
error_reporting(-1);
// Initial: Fehlerausgabe unterdrücken, um Path-Disclosure-Attacken ins Leere laufen zu lassen
ini_set("display_errors", 1);
# ab php > 5.2.0 hat preg_* ein default pcre.backtrack_limit von 100000 zeichen
# deshalb der versuch mit ini_set
@ini_set('pcre.backtrack_limit', 1000000);


define("BASE_DIR_ADMIN", BASE_DIR.ADMIN_DIR_NAME."/");

if (is_file(BASE_DIR.CMS_DIR_NAME."/DefaultConfCMS.php")) {
    require_once(BASE_DIR.CMS_DIR_NAME."/DefaultConfCMS.php");
} else {
    die("Fatal Error File doesn't exist: "."DefaultConfCMS.php");
}
// UTF-8 erzwingen - experimentell!
@ini_set("default_charset", CHARSET);

#$start_time = get_executTime(false);
define("START_TIME",get_executTime(false));

# aus sicherheits gründen lehren wir immer den backup ordner
if(function_exists('gzopen') and is_dir(BASE_DIR.BACKUP_DIR_NAME)) {
    $dh = opendir(BASE_DIR.BACKUP_DIR_NAME);
    while(($entry = readdir($dh)) !== false) {
        if($entry == "." or $entry == "..")
            continue;
        @unlink(BASE_DIR.BACKUP_DIR_NAME.'/'.$entry);
    }
    closedir($dh);
}


$test_dir = array(
    BASE_DIR_ADMIN.LANGUAGE_DIR_NAME => LANGUAGE_DIR_NAME,
    BASE_DIR_ADMIN.CONF_DIR_NAME => CONF_DIR_NAME,
    BASE_DIR_CMS.CONF_DIR_NAME => CONF_DIR_NAME,
    BASE_DIR.CONTENT_DIR_NAME => CONTENT_DIR_NAME,
    BASE_DIR.LAYOUT_DIR_NAME => LAYOUT_DIR_NAME,
    BASE_DIR_CMS.LANGUAGE_DIR_NAME => LANGUAGE_DIR_NAME,
    BASE_DIR.GALLERIES_DIR_NAME => GALLERIES_DIR_NAME
);

foreach($test_dir as $dir => $name) {
    if(!is_dir($dir)) {
        die("Fatal Error Directory doesn't exist: ".$name);
    }
}

if(is_file(BASE_DIR_CMS."DefaultFunc.php")) {
    require_once(BASE_DIR_CMS."DefaultFunc.php");
} else {
    die("Fatal Error File doesn't exist: "."DefaultFunc.php");
}

$_GET = cleanREQUEST($_GET);
$_REQUEST = cleanREQUEST($_REQUEST);
$_POST = cleanREQUEST($_POST);

if (isset($_GET['logout'])) {
  $attack = $_GET['logout'];
  if (!empty($attack)) {
    if ($attack != "true") {
      die("Fatal Error: possible attack");
    }
  }
}


if(isset($_FILE)) $_FILE = cleanREQUEST($_FILE);

$message = NULL;

#define("ICON_SIZE","24x24"); # 16x16 22x22 24x24 32x32 48x48
#define("ADMIN_ICONS", URL_BASE.ADMIN_DIR_NAME."/gfx/icons/".ICON_SIZE."/");
#define("ADMIN_ICONS_TABS",URL_BASE.ADMIN_DIR_NAME."/gfx/icons/22x22/");

define("ICON_URL",URL_BASE.ADMIN_DIR_NAME.'/gfx/');
define("ICON_URL_SLICE",URL_BASE.ADMIN_DIR_NAME.'/gfx/clear.gif');

require_once(BASE_DIR_ADMIN."default_conf.php");

require_once(BASE_DIR_CMS."Properties.php");
require_once(BASE_DIR_CMS."SpecialChars.php");
$specialchars = new SpecialChars();

$ADMIN_CONF = new Properties(BASE_DIR_ADMIN.CONF_DIR_NAME."/basic.conf.php");
$CMS_CONF    = new Properties(BASE_DIR_CMS.CONF_DIR_NAME."/main.conf.php");
#$LANGUAGE  = new Properties(BASE_DIR_ADMIN."sprachen/language_".$ADMIN_CONF->get("language").".txt");
require_once(BASE_DIR_CMS."Language.php");
$LANGUAGE  = new Language(BASE_DIR_ADMIN.LANGUAGE_DIR_NAME."/language_".$ADMIN_CONF->get("language").".txt");
setTimeLocale($LANGUAGE);
$LOGINCONF = new Properties(BASE_DIR_ADMIN.CONF_DIR_NAME."/logindata.conf.php");
# Achtung die loginpass darf nur mit php Angelegt werden
if(is_file(BASE_DIR_ADMIN.CONF_DIR_NAME."/loginpass.conf.php"))
    @chmod(BASE_DIR_ADMIN.CONF_DIR_NAME."/loginpass.conf.php",0600);
$loginpassword = new Properties(BASE_DIR_ADMIN.CONF_DIR_NAME."/loginpass.conf.php");

// Login ueberpruefen
$LoginContent = require_once(BASE_DIR_ADMIN."login.php");
# der user wurde automatisch abgemeldet und hat sich über ajax wieder angemeldet
if($LoginContent === true and isset($_POST['ajaxlogin']) and $_POST['ajaxlogin'] == "true") {
    ajax_return("success",true,returnMessage(true,getLanguageValue("login_ajax_success")),true,true);
}
if(!defined('LOGIN'))
    define("LOGIN",false);

if(LOGIN) { #-------------------------------
    header('Content-Type: text/html; charset='.CHARSET.'');

    if(defined('MULTI_USER') and MULTI_USER and getRequestValue('logout_other_users','post') == "true") {
        define('LOGOUT_OTHER_USERS',true);
        # der trick ist hier das true da damit die eigene function destroy aufgerufen wird
        session_regenerate_id(true);
    }
    # Achtung nojs darf nur von nicht ajax anfragen benutzt werden
    if(getRequestValue('nojs','get')) {
        session_regenerate_id(true);
    }
    # wird für den Editor gebraucht
    list($activ_plugins,$deactiv_plugins,$plugin_first) = findPlugins();

    # Backup Erinnerung bestätigen
    if(isset($_POST["lastbackup_yes"]) and $_POST["lastbackup_yes"] == "true") {
        $ADMIN_CONF->set("lastbackup",time());
        ajax_return("success",true);
    }
    # mod_rewrite test
    if(getRequestValue('moderewrite','get') and getRequestValue('moderewrite','get') == "ok") {
        echo contend_template(array("home_serverinfo" => array(array('<span id="mod-rewrite-true">'.getLanguageValue("home_mod_rewrite").'</span>',getLanguageValue("yes")))),array("home_serverinfo" => array("ok")));
        exit();
    }

    require_once(BASE_DIR_ADMIN."filesystem.php");

    if(!is_file(SORT_CAT_PAGE)) {
        $cat_page_sort_array = array();
        $cats = getDirAsArray(CONTENT_DIR_REL,"dir");
        foreach($cats as $cat) {
            $cat_page_sort_array[$cat] = array();
            if(substr($cat,-(EXT_LENGTH)) == EXT_LINK) {
                $cat_page_sort_array[$cat] = "null";
                continue;
            }
            $pages = getDirAsArray(CONTENT_DIR_REL.$cat,"file");
            foreach($pages as $page) {
                $cat_page_sort_array[$cat][$page] = "null";
            }
        }
        $sort_array = var_export($cat_page_sort_array,true);
        if(true != (mo_file_put_contents(SORT_CAT_PAGE,"<?php if(!defined('IS_CMS')) die();\n\$cat_page_sort_array = ".$sort_array.";\n?>")))
            $message .= returnMessage(false,"Achtung kann SortCatPage nicht Schreiben");
    }

    require_once(BASE_DIR_CMS.'idna_convert.class.php');
    $Punycode = new idna_convert();

    require_once(BASE_DIR_CMS."CatPageClass.php");
    $CatPage         = new CatPageClass();

    $GALLERY_CONF = new Properties(BASE_DIR_CMS.CONF_DIR_NAME."/gallery.conf.php");
    $USER_SYNTAX = new Properties(BASE_DIR_CMS.CONF_DIR_NAME."/syntax.conf.php");

    define("ALLOWED_SPECIALCHARS_REGEX",$specialchars->getSpecialCharsRegex());

    # hier das tabs array
    $array_tabs = array("home","catpage","files","gallery","config","admin","plugins","template");
    if($_SESSION['username'] == $loginpassword->get("username")) {
        $array_tabs = $ADMIN_CONF->get("tabs");
        define("ROOT",false);
    } else {
        define("ROOT",true);
    }
    # Plugin-Tab nur anzeigen wenn plugin Ordner mit mind. einem plugin vorhanden ist
/*    if (count(getDirAsArray(PLUGIN_DIR_REL, "dir")) < 1 ) {
        foreach($array_tabs as $key => $value) {
            if($value == "plugins") {
                unset($array_tabs[$key]);
                break;
            }
        }
    }
*/
    $users_array = array();
    $tmp_action = getRequestValue('action');
    if(defined('MULTI_USER') and MULTI_USER) {
        $USERS = new Properties(session_save_path().((substr(session_save_path(),-1) != "/") ? "/" : "").MULTI_USER_FILE."users.conf.php");
        $id = md5(session_id());
        $users_array = $USERS->toArray();
        unset($users_array[$id]);
        # die refresh ajax anfrage
#!!!!!!!!!!!!!! kann gelöscht werden wenn ich das mit dem neuen multi mode fertig habe
/*        if(getRequestValue('refresh_session') == "true") {
            $hidden_action = "";
            foreach($users_array as $action) {
                if($action == "home" or $action == "login") continue;
                if(in_array($action,$array_tabs))
                    $hidden_action .= ",".$action;
            }
            if(strlen($hidden_action) > 1)
                $hidden_action = substr($hidden_action,1);
            exit($hidden_action);
        }*/
        # es gab ein redirect
        if(false !== ($tmp = strstr($USERS->get($id),"#"))) {
            $tmp = substr($tmp,1);
            $message .= returnMessage(false,getLanguageValue("error_multi_user_tab",false,getLanguageValue($tmp."_button"),MULTI_USER_TIME));
            $USERS->set($id,$tmp);
        # nur reingehen bei click auf eins der tabs
        } elseif(getRequestValue('multi','get') and $tmp_action != "home" and in_array($tmp_action,$array_tabs)) {
            if("freetab" == ($tmp = $USERS->get($id)))
                $tmp = "home";
            $url = $_SERVER['HTTP_HOST'].URL_BASE.ADMIN_DIR_NAME.'/index.php?nojs=true&amp;action='.$tmp.'&amp;multi=true';
            $USERS->set($id,$tmp_action);
            # seite besetzt
            if(in_array($tmp_action,$users_array)) {
                $USERS->set($id,"busy#".$tmp_action);
                header("Location: ".HTTP.$url);
                exit();
           }
        # nur reingehen wenn action home ist oder es noch keine get parameter gibt
        } elseif(getRequestValue('multi','get') or (!getRequestValue('multi','get') and !$tmp_action)) {
            $USERS->set($id,"freetab");
        }
        # im FileUpload wird der tab besetzt mit window.location.href behandelt
        if(getRequestValue('fileupload','get')) {
            $url = $_SERVER['HTTP_HOST'].URL_BASE.ADMIN_DIR_NAME.'/index.php?nojs=true&amp;action=home&amp;multi=true';
            $USERS->set($id,"busy#".getRequestValue('fileupload','get'));
            header("Location: http://$url");
            exit();
        # hier gehts um die anfragen die von ajax kommen
        } elseif(!getRequestValue('multi','get') and in_array(getRequestValue('action'),$users_array)) {
            ajax_return("error",true,returnMessage(false,getLanguageValue("error_multi_user_tab",false,getLanguageValue(getRequestValue('action')."_button"),MULTI_USER_TIME)),true,true);
        }
        unset($id);
   }

    if(in_array($tmp_action,$array_tabs))
        define("ACTION",$tmp_action);
    else
        define("ACTION","home");
    unset($tmp_action);

    if(file_exists(BASE_DIR_ADMIN.ACTION.'.php'))
        require_once(BASE_DIR_ADMIN.ACTION.'.php');
    else
        die("Fatal Error File doesn't exist: ".ACTION.".php");


    $func = ACTION;
    $pagecontent = $func();
    unset($func);
} elseif($LoginContent !== false) {
    header('content-type: text/html; charset='.CHARSET.'');
    define("ACTION","login");
    $pagecontent = $LoginContent;
} else
    die("Fatal Error");

require_once(BASE_DIR_CMS."javaScriptPacker.php");
require_once(BASE_DIR_CMS."cssMinifier.php");
require_once(BASE_DIR_ADMIN.'admin_template.php');
// Ausgabe der kompletten Seite 
admin_Template($pagecontent,$message);

function get_executTime($start_time) {
    if(!function_exists('gettimeofday'))
        return NULL;
    list($usec, $sec) = explode(" ", microtime());
    if($start_time === false) {
        return ((float)$usec + (float)$sec);
    }
    return getLanguageValue("get_execut_time",false,sprintf("%.4f", (((float)$usec + (float)$sec) - START_TIME)));
}

function get_memory() {
    $size = 0;
    if(function_exists('memory_get_usage'))
        $size = @memory_get_usage();
    if(function_exists('memory_get_peak_usage'))
        $size = @memory_get_peak_usage();
    $unit = array('B','KB','MB','GB','TB','PB');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i].' '.getLanguageValue("get_memory");
}

/*------------------------------
 Zusätzliche Funktionen
 ------------------------------ */

function pageedit_dialog() {
    require_once(BASE_DIR_ADMIN.'editsite.php');
    $dialog = '<div id="pageedit-box">'.showEditPageForm().'</div>';
    return $dialog;
}


// Gib Erfolgs- oder Fehlermeldung zurück
function returnMessage($success, $message) {
    if ($success === true) {
        return '<span class="mo-message-erfolg"><img class="mo-message-icon mo-icons-icon mo-icons-information" src="'.ICON_URL_SLICE.'" alt="information" />'.$message.'</span>';
    } else {
        return '<span class="mo-message-fehler"><img class="mo-message-icon mo-icons-icon mo-icons-error" src="'.ICON_URL_SLICE.'" alt="error" />'.$message.'</span>';
    }
}

# erzeugt die ausgabe für das return einer ajax anfrage
# anhand desen wir per js eine dialogbox geöfnet siehe dialog_auto()
# $success = success oder error
# $title = false kein title
#           true default title
#           "mein title"
# $buttons = js-dialog-close und js-dialog-reload oder true = js-dialog-close
# wenn kein $buttons und $content nicht lehr ist wird automatisch der js-dialog-close benutzt
function ajax_return($success,$exit,$content = "",$title = false,$buttons = false) {
    if($title === true) {
        if($success == "success")
            $title = ' title="'.getLanguageValue("dialog_title_messages").'"';
        else
            $title = ' title="'.getLanguageValue("dialog_title_error").'"';
    } elseif($title !== false)
        $title = ' title="'.$title.'"';
    else
        $title = "";
    if($buttons === true)
        $buttons = ' js-dialog-close';
    if($buttons !== false)
        $buttons = ' '.$buttons;
    else
        $buttons = "";
    if($exit) {
        echo '<span class="'.$success.$buttons.' js-dialog-content"'.$title.'>'.$content.'</span>';
        exit();
    }
    return '<span class="'.$success.$buttons.' js-dialog-content"'.$title.'>'.$content.'</span>';
}

// Gibt eine Checkbox mit dem uebergebenen Namen zurueck. Der Parameter checked bestimmt, ob die Checkbox angehakt ist.
function buildCheckBox($name, $checked,$label = false) {
    $id = NULL;
    $label_tag = NULL;
    if($label) {
        $id = ' id="'.str_replace('[active]','',$name).'"';
        $label_tag = '<label for="'.str_replace('[active]','',$name).'">'.$label.'</label>';
    }
    $checkbox = '<input type="checkbox" value="true" class="mo-checkbox js-checkbox"';
    if ($checked == "true") {
        $checkbox .= ' checked=checked';
    }
    $checkbox .= ' name="'.$name.'"'.$id.' />';
    return $checkbox.$label_tag;
}

function contend_template($daten_array,$error = false,$only_content = false) {
    $template = NULL;
    foreach($daten_array as $titel => $content) {
        $template_content = NULL;
        $toggle = false;
        if(isset($content["toggle"]) and $content["toggle"] === true) {
            unset($content["toggle"]);
            $toggle = true;
        }
        foreach($content as $key => $value) {
            if($error !== false and isset($error[$titel][$key]) and $error[$titel][$key] === "ok") {
                $template_content .= '<li class="mo-in-ul-li mo-inline ui-widget-content ui-state-highlight ui-corner-all ui-helper-clearfix">';
            } elseif($error !== false and isset($error[$titel][$key]) and $error[$titel][$key] !== false and is_string($error[$titel][$key])) {
                $template_content .= '<li class="mo-in-ul-li mo-inline ui-widget-content ui-state-error ui-corner-all ui-helper-clearfix"><div class="mo-error">'.$error[$titel][$key].'</div>';
            } elseif($error !== false and isset($error[$titel][$key]) and $error[$titel][$key] === true) {
                $template_content .= '<li class="mo-in-ul-li mo-inline ui-widget-content ui-state-error ui-corner-all ui-helper-clearfix">';
            } else
                $template_content .= '<li class="mo-in-ul-li mo-inline ui-widget-content ui-corner-all ui-helper-clearfix">';
            if(is_array($value)) {
                $template_content .= '<div class="mo-in-li-l">'.$value[0].'</div>'
                        .'<div class="mo-in-li-r">'.$value[1].'</div>';
            } else  {
                $template_content .= '<div>'.$value.'</div>';
            }
            $template_content .= '</li>';
        }
        if($only_content === true)
            $template .= $template_content;
        else
            $template .= get_template_truss($template_content,$titel,$toggle);
    }
    return $template;
}

function get_template_truss($content,$titel,$toggle = false) {
        if($toggle) {
            $template = '<ul class="mo-ul">'
                    .'<li class="mo-li ui-widget-content ui-corner-all">'
                    .'<div class="js-tools-show-hide mo-li-head-tag mo-tag-height-from-icon mo-li-head-tag-no-ul mo-middle ui-state-default ui-corner-top ui-helper-clearfix">'
                    .'<span class="mo-bold mo-padding-left">'.getLanguageValue($titel).'</span>'
                    .'<img style="float:right;" class="js-toggle mo-tool-icon mo-icon mo-icons-icon mo-icons-edit" src="'.ICON_URL_SLICE.'" alt="edit" />'
                    .'</div>'
                    .'<ul class="mo-in-ul-ul js-toggle-content" style="display:none;">';
        } else
            $template = '<ul class="mo-ul">'
                    .'<li class="mo-li ui-widget-content ui-corner-all">'
                    .'<div class="mo-li-head-tag mo-tag-height-from-icon mo-li-head-tag-no-ul mo-middle ui-state-default ui-corner-top">'
                    .'<span class="mo-bold mo-padding-left">'.getLanguageValue($titel).'</span></div>'
                    .'<ul class="mo-in-ul-ul">';

        $template .= $content;
        $template .= '</ul>'
                .'</li>'
            .'</ul>';
    return $template;
}

function getLanguageValue($confpara,$html = false,$param1 = '', $param2 = '') {
    global $LANGUAGE;
    if($html)
        return $LANGUAGE->getLanguageHtml($confpara, $param1, $param2);
    else
        return $LANGUAGE->getLanguageValue($confpara, $param1, $param2);
}

function getHelpIcon($artikel = false,$subartikel = false) {

    if($artikel === false)
        $artikel = ACTION;
    if($subartikel !== false)
        $subartikel = "&amp;subartikel=".$subartikel;
    if(file_exists(BASE_DIR."docu/index.php"))
        return '<a href="'.URL_BASE.'docu/index.php?menu=false&amp;artikel='.$artikel.$subartikel.'" target="_blank" class="js-docu-link mo-butten-a-img"><img class="mo-icons-icon mo-icons-help" src="'.ICON_URL_SLICE.'" alt="help" hspace="0" vspace="0" border="0" /></a>';
    else return NULL;
}

?>
