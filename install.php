<?php
/*

------------------------------------------
fertig:
------------------------------------------

- install.php ins root geben
- wenn nötig recht zurückgeben sonst nicht

dirs setzen:
- admin
- cms
- plugin
R- admin/conf
R- cms/conf
R- kategorien, layouts, galerien, plugin/*.conf.php

------------------------------------------
todo:
------------------------------------------

- sprachen komplett auf admin umstellen (die fehlenden mit install_*)

- test von install/ ordner mit einbeziehen
- nach passwort absenden die conf schreiben und paswort setzen
- erzeugen conf funktionen, mit echo vorerst mal einbinden

*/
$test = false;


define('TEST',true);
define('IS_CMS',true);
define('IS_ADMIN',true);
define('IS_INSTALL',true);
#!!!!!!!! prüfen ob vorhanden gegebenfals ermiteln????????
define("ADMIN_DIR_NAME","admin");
define('CMS_DIR_NAME','cms');

if(strtolower(substr("PHP_OS",0,3)) == "win")
    define("USE_CHMOD", false);
else
    define("USE_CHMOD", true);

// falls da bei winsystemen \\ drin sind in \ wandeln
$base_dir = str_replace("\\\\", "\\", __FILE__);
// zum schluss noch den teil denn wir nicht brauchen abschneiden
$base_dir = substr($base_dir, 0, -(strlen("install.php")));
define("BASE_DIR", $base_dir);
unset($base_dir); // verwendung im script verhindern

if(isset($_POST['go_to_admin'])) {
    $URL_BASE = $_SERVER['SERVER_NAME'].substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], "install.php")).ADMIN_DIR_NAME."/";
    cleanUpUpdate();
    header("Location: http://$URL_BASE");
    exit();
}

/*
define("CHARSET","UTF-8");
define("EXT_PAGE",".txt.php");
define("EXT_HIDDEN",".hid.php");
define("EXT_DRAFT",".tmp.php");
define("EXT_LINK",".lnk.php");
define("EXT_LENGTH",strlen(EXT_PAGE));
*/
$LANG_INSTALL = array();
$LANG_INSTALL['deDE'] = 'Deutsch';
$LANG_INSTALL['enEN'] = 'English';
$LANG_INSTALL['frFR'] = 'Français';
$LANG_INSTALL['esES'] = 'Español';
$LANG_INSTALL['itIT'] = 'Italiano';
$LANG_INSTALL['nlNL'] = 'Nederlands';
$LANG_INSTALL['plPL'] = 'Polski';
$LANG_INSTALL['daDK'] = 'Dansk';
$LANG_INSTALL['ptBR'] = 'Português';
testInstall();

define("BASE_DIR_ADMIN", BASE_DIR.ADMIN_DIR_NAME."/");

$LANG_TMP = "deDE";
if(isset($_POST['language']) and $_POST['language'] != "false")
    $LANG_TMP = $_POST['language'];

#!!!!!! wenn es schon confs gibt nur password und fertig einblenden
$ADMIN_CONF = false;
$CMS_CONF = false;
# eigene session
require_once(BASE_DIR_ADMIN."sessionClass.php");
# Default conf
require_once(BASE_DIR.CMS_DIR_NAME."/DefaultConfCMS.php");
# wenn die sachen kein die() oder fatal error ergeben ist es gut
require_once(BASE_DIR_CMS."DefaultFunc.php");
// Properties Class
require_once(BASE_DIR_CMS."Properties.php");
// Language Class
require_once(BASE_DIR_CMS."Language.php");

require_once(BASE_DIR_ADMIN."filesystem.php");
require_once(BASE_DIR_CMS."SpecialChars.php");
$specialchars = new SpecialChars();

if(is_file(BASE_DIR_CMS.CONF_DIR_NAME.'/main.conf.php') and isFileRW(BASE_DIR_CMS.CONF_DIR_NAME.'/main.conf.php'))
    $CMS_CONF = new Properties(BASE_DIR_CMS.CONF_DIR_NAME.'/main.conf.php');
if(is_file(BASE_DIR_ADMIN.CONF_DIR_NAME.'/basic.conf.php') and isFileRW(BASE_DIR_ADMIN.CONF_DIR_NAME.'/basic.conf.php'))
    $ADMIN_CONF = new Properties(BASE_DIR_ADMIN.CONF_DIR_NAME.'/basic.conf.php');
if(($ADMIN_CONF !== false)
        and (!isset($_POST['language']) or $_POST['language'] == "false")
        and (is_file(BASE_DIR_ADMIN.LANGUAGE_DIR_NAME."/language_".$ADMIN_CONF->get('language').".txt")))
    $LANG_TMP = $ADMIN_CONF->get('language');
$LANG = new Language(BASE_DIR_ADMIN.LANGUAGE_DIR_NAME."/language_".$LANG_TMP.".txt");

session_start();
/*
$URL_BASE = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], "install.php"));
$URL_BASE = htmlentities($URL_BASE, ENT_COMPAT,CHARSET);
define("URL_BASE", $URL_BASE);
unset($URL_BASE);
*/
/* schritte
 1. environment: phpversion, gdlib, safemode
 2. chmod
 3. session
 4. sprache
 5. mod_rewrite
 6. password
*/

#$this->LANG_CONF = new Properties(BASE_DIR."admin/sprachen/language_".$lang.".txt");

ini_set("default_charset", CHARSET);
header('content-type: text/html; charset='.strtolower(CHARSET));

$steps = array("language","chmod_test","environment","rewrite","password","finish");

if($test)
    $_POST["finish_steps"] = "language,chmod_test,environment,rewrite,password,update";

$html_check_update = '';
if(is_file("update.php")) {
    require_once("update.php");
    if(!isset($_POST['check_update']) and function_exists("testUpdate") and testUpdate(true)) {
        $steps = array("language","chmod_test","environment","rewrite","password","update","finish");
        $html_check_update = '<input type="hidden" name="check_update" value="true" />';
    }
    if(isset($_POST['check_update']) and $_POST['check_update'] == "true") {
        $steps = array("language","chmod_test","environment","rewrite","password","update","finish");
        $html_check_update = '<input type="hidden" name="check_update" value="'.$_POST['check_update'].'" />';
    }
}
#    if(isset($_POST['check_update']))

#$sort_array = var_export($_POST,true);
#file_put_contents(BASE_DIR."install.txt",$sort_array."\n",FILE_APPEND);

$current_step = $steps[0];
if(isset($_POST["current_step"]) and in_array($_POST["current_step"],$steps))
    $current_step = $_POST["current_step"];


#!!!!!! nur für die testphase
if(isset($_POST["reset"])) {
    $current_step = $steps[0];
}

if(function_exists($current_step))
    list($status,$html_step) = $current_step();

echo getHtml("start");
echo menu_tabs($steps,$current_step,$status);
echo $html_check_update;
echo '<div style="padding-top:1.2em;" class="install mo-ui-tabs-panel ui-widget-content ui-corner-bottom mo-no-border-top">';
if($test) {
    echo $current_step."<br />";
    echo "<pre>";
    print_r($_POST);
    echo "</pre><br />";
}
echo $html_step;
#$test = true;
if($test)
    echo '<input type="submit" name="reset" value="Reset" />';
echo '</div>';
echo getHtml("end");


// -----------------------------------------------------------------------------
// Funktionen
// -----------------------------------------------------------------------------

function testInstall() {
    if(!is_file(BASE_DIR.CMS_DIR_NAME."/Language.php") and !is_file(BASE_DIR.ADMIN_DIR_NAME."/sessionClass.php"))
        exit("Du must das CMS schonn mit FTP hochladen");

    if(!is_readable('admin') and !is_readable('cms') and !is_readable(BASE_DIR.CMS_DIR_NAME."/Language.php") and !is_readable(BASE_DIR.ADMIN_DIR_NAME."/sessionClass.php"))
        exit("Die rechte Vergabe von deinem Provider ist echt beschissen");
}

function language() {
    $html = "";
    $html1 = getLanguageValue("install_lang_select")."<br />";
    $html2 = getLanguageSelect();
    return array(true,contend_template(getLanguageValue("install_help"),"")
                .contend_template(installHelp("install_lang_help"),"")
                .contend_template(array($html1,$html2),""));
}

function chmod_test() {
#return array(true,"");
    $status = false;
    $no_chmod = getLanguageValue("install_chmod_no_chmod");
    $help = contend_template(installHelp("install_chmod_help"),"");
    $file_test = BASE_DIR."test_install.txt";

    if(!isset($_POST['chmod_test']) or $_POST['chmod_test'] == "false") {
        # die mit FTP Hochgeladen Daterechte Prüfen
        if(!isFileRW(BASE_DIR) or !isFileRW(BASE_DIR."admin")) {
            $html = contend_template(getLanguageValue("install_chmod_change_ftp").'<br /><input type="submit" name="chmod_ftp_change" value="'.getLanguageValue("install_chmod_change_ftp_button").'" />',"");
            $chmod = "false";
        # wir ermitel die Dateirechte von PHP Angelegten Dateien
        } else {
            if(!is_file($file_test))
                file_put_contents($file_test, "chmod test");
            if(fileowner($file_test) == fileowner(BASE_DIR."install.php")) {
                $html = contend_template(getLanguageValue("install_chmod_use",$no_chmod),true);
                $html .= '<input type="hidden" name="chmod_test" value="" />';
                if(is_file($file_test))
                    unlink($file_test);
                return array(true,$help.$html);
            }
            # chmod um 1 erhöhen
            if(isset($_POST['chmod_testfile']) and $_POST['chmod_testfile'] == getLanguageValue("install_chmod_testfile_next_button")) {
                setInstallChmod($file_test,getNextChmod($file_test));
            }
            clearstatcache();
            $html = contend_template(array(getLanguageValue("install_chmod_testfile_rw",basename($file_test),substr(decoct(fileperms($file_test)),-3)),'<input type="submit" name="chmod_testfile" value="'.getLanguageValue("yes").'" />'.'<input type="submit" name="chmod_testfile" value="'.getLanguageValue("install_chmod_testfile_next_button").'" />'),"");
            $chmod = "false";
            # das ist jetzt der chmod wert den wir benutzen müssen
            if(isset($_POST['chmod_testfile']) and $_POST['chmod_testfile'] == getLanguageValue("yes")) {
#!!!!!! chmode hier anwenden auf alle relewanten dateien??????????
                clearstatcache();
                $chmod = substr(decoct(fileperms($file_test)),-3);
                if(is_file($file_test))
                    unlink($file_test);
                $html = contend_template(getLanguageValue("install_chmod_use",$chmod),true);
                $status = true;
            }
        }
    } else {
        if(is_file($file_test))
            unlink($file_test);
        $chmod = $_POST['chmod_test'];
        $chmod_text = $chmod;
        $status = true;
        if($chmod == "")
            $chmod_text = $no_chmod;
        $html = contend_template(getLanguageValue("install_chmod_use",$chmod_text),true);
    }
    $html .= '<input type="hidden" name="chmod_test" value="'.$chmod.'" />';
    return array($status,$help.$html);
}

function environment() {
    $html_ret = "";
    $status = true;

    // conf dateien anlegen
    $conf = makeConfFiles();
    if(true === $conf) {
        $html = array(getLanguageValue("install_environment_conf"),getLanguageValue("yes"));
        $html_ret .= contend_template($html,true);
    } else {
        $html = array(getLanguageValue("install_environment_conf"),getLanguageValue("no"));
        $html_ret .= contend_template($html,false);
        $status = false;
    }

    // Zeile "PHP-Version"
    if(version_compare(PHP_VERSION, '5.1.2') >= 0) {
        $html = array(getLanguageValue("home_phpversion_text"),phpversion());
        $html_ret .= contend_template($html,true);
    } else {
        $status = false;
        $html = array(getLanguageValue("home_phpversion_text"),phpversion());
        $html_ret .= contend_template($html,false);
    }

    // Zeile "Safe Mode"
    if(ini_get('safe_mode')) {
        $html = array(getLanguageValue("home_text_safemode")."<br /><b>".getLanguageValue("home_error_safe_mode")."</b>",getLanguageValue("yes"));
        $html_ret .= contend_template($html,false);
    } else {
        $html = array(getLanguageValue("home_text_safemode"),getLanguageValue("no"));
        $html_ret .= contend_template($html,true);
    }

    // Zeile "GDlib installiert"
    if(!extension_loaded("gd")) {
        $status = false;
        $html = array(getLanguageValue("home_text_gd"),getLanguageValue("no"));
        $html_ret .= contend_template($html,false);
    } else {
        $html = array(getLanguageValue("home_text_gd"),getLanguageValue("yes"));
        $html_ret .= contend_template($html,true);
    }

    // Zeile session test
    $_SESSION["test"] = "test";
#!!!! das muss geprüft werden ob das so geht
    if(isset($_SESSION["test"]) and $_SESSION["test"] == "test") {
        $html = array(getLanguageValue("install_environment_session"),getLanguageValue("yes"));
        $html_ret .= contend_template($html,true);
    } else {
        $html = array(getLanguageValue("install_environment_session"),getLanguageValue("no"));
        $html_ret .= contend_template($html,false);
        $status = false;
    }

    # MULTI_USER
    if(defined('MULTI_USER') and MULTI_USER) {
        $html = array("Multiuser mode Verfügbar",MULTI_USER_TIME." sec.");
        $html_ret .= contend_template($html,"");
    } else {
        $html = array("Multiuser mode Verfügbar",getLanguageValue("no"));
        $html_ret .= contend_template($html,"");
    }

    # backupsystem
    if(function_exists('gzopen')) {
        $html = array("backup system test",getLanguageValue("yes"));
        $html_ret .= contend_template($html,"");
    } else {
        $html = array("backup system test",getLanguageValue("no"));
        $html_ret .= contend_template($html,"");
    }

    if($status)
        $html_ret .= '<input type="hidden" name="environment" value="true" />';
    $help = contend_template(installHelp("install_environment_help"),"");
    return array($status,$help.$html_ret);
}

function rewrite() {
    global $CMS_CONF;
    # rewrite anfrage von install.js
    if(isset($_POST['fromajax']) and $_POST['fromajax'] == "true") {
        if(isset($_POST['modconf'])) {
            writeHtaccess("test",$_POST['modconf']);
        }
        echo '<span id="return-modconf">&nbsp;</span>';
        exit();
    }

    # rewrite wurde schonn ausgeführt
    $rewrite_step = false;
    if(isset($_POST['rewrite']) and $_POST['rewrite'] != "false") {
        $rewrite_step = $_POST['rewrite'];
    }

    $status = true;
    $text_status = "";
    $input = "";
    if($rewrite_step === false) {
        $status = false;
        $rewrite_step = "false";

        if(!is_dir(BASE_DIR.'install')) {
            mkdir(BASE_DIR.'install');
        }
        if(!is_file(BASE_DIR.'install/test.php')) {
            $test_datei = '<?php sleep(2); if (isset($_GET["rewritetest"]) and $_GET["rewritetest"] == "true") echo \'<span id="mod-rewrite-true">&nbsp;</span>\'; ?>';
            file_put_contents(BASE_DIR.'install/test.php', $test_datei);
        }

        $html = '<img style="margin-right:2em;" src="'.URL_BASE.CMS_DIR_NAME.'/jquery/ajax-loader.gif" />'
        .'test <span id="step-mod-conf">0</span> von '.writeHtaccess("test",0,true)
        .'<script language="Javascript" type="text/javascript">/*<![CDATA[*/'
        .'var finish_test = false;'
        .'var max_step = '.writeHtaccess("test",0,true).';'
        .'/*]]>*/</script>';
    } else {
        if(is_dir(BASE_DIR.'install')) {
            if(is_file(BASE_DIR.'install/test.php'))
                unlink(BASE_DIR.'install/test.php');
            if(is_file(BASE_DIR.'install/.htaccess'))
                unlink(BASE_DIR.'install/.htaccess');
            rmdir(BASE_DIR.'install');
        }
        if($rewrite_step == "no_modrewrite") {
            $html = getLanguageValue("install_rewrite_no");
            $text_status = false;
            unlink(BASE_DIR.'.htaccess');
            unlink(BASE_DIR_ADMIN.'.htaccess');
            $CMS_CONF->set("modrewrite","false");
        } else {
            $html = getLanguageValue("install_rewrite_yes");
            $text_status = true;
            writeHtaccess("cms",$rewrite_step);
            writeHtaccess("admin",$rewrite_step);
            $CMS_CONF->set("modrewrite","true");
        }
        $input = '<input type="hidden" name="rewrite" value="'.$rewrite_step.'" />';
    }

    $help = contend_template(installHelp("install_rewrite_help"),"");
    $html_ret = contend_template($html,$text_status);
    return array($status,$help.$html_ret.$input);
}

function password() {
    $html = "";
    $status = false;

    $form_errmsg = ""; // buffer für fehlermeldungen
    $form_username  = "";

    // form abgesendet, inhalte prüfen
    if(isset($_POST['pw_submit'])) {
        if((!isset($_POST['username'])
                or !isset($_POST['password1'])
                or !isset($_POST['password2']))
            or (empty($_POST['username'])
                or empty($_POST['password1'])
                or empty($_POST['password2'])
            )
            ) {
            $form_errmsg .= getLanguageValue("pw_error_missingvalues")."<br />";
        }
        if(empty($form_errmsg))
            $form_username  = $_POST['username'];
        // username muss mind. 5 zeichen haben
        if(strlen($_POST['username']) < 5) {
            $form_errmsg .= getLanguageValue("pw_error_tooshortname")."<br />";
        }

        // pw-komplexität check
        if(strlen($_POST['password1']) < 6
             or !preg_match("/[0-9]/", $_POST['password1']) 
             or !preg_match("/[a-z]/", $_POST['password1'])
             or !preg_match("/[A-Z]/", $_POST['password1'])
           ) {
            // pw nicht komplex genug
            $form_errmsg .= getLanguageValue("pw_error_newpwerror")."<br />";
        }

        // stimmen die eingegebenen pw überein?
        if($_POST['password1'] != $_POST['password2']) {
           $form_errmsg .= getLanguageValue("pw_error_newpwmismatch")."<br />";
        }

        // keine fehler, dann daten schreiben
        if(empty($form_errmsg)) {
            $status = true;
            require_once(BASE_DIR.ADMIN_DIR_NAME.'/PasswordHash.php');
            $t_hasher = new PasswordHash(8, FALSE);
            $pw = $t_hasher->HashPassword($_POST['password1']);
            $loginpassword = new Properties(BASE_DIR.ADMIN_DIR_NAME.'/'.CONF_DIR_NAME."/loginpass.conf.php");
            $loginpassword->set("name", $_POST['username']);
            $loginpassword->set("pw", $pw);
        } else
            $form_errmsg = contend_template($form_errmsg,false);
    }

    $html = getLanguageValue("pw_text_login").'<br /><br />'.getLanguageValue("pw_help")
        .'<table width="100%" cellspacing="0" border="0" cellpadding="0" class="">'
        .'<tr><td>&nbsp;</td><td class="mo-in-li-r">'.getLanguageValue("pw_titel_newname").'</td><td class="mo-in-li-r">'.'<input type="text" class="js-in-pwroot mo-input-text" name="username" value="'.$form_username.'" />'.'</td></tr>'
        .'<tr><td>&nbsp;</td><td>'.getLanguageValue("pw_titel_newpw").'</td><td>'.'<input type="password" class="js-in-pwroot mo-input-text" value="'.NULL.'" name="password1" />'.'</td></tr>'
        .'<tr><td>&nbsp;</td><td>'.getLanguageValue("pw_titel_newpwrepeat").'</td><td>'.'<input type="password" class="js-in-pwroot mo-input-text" value="" name="password2" />'.'</td></tr>'
        .'<tr><td>&nbsp;</td><td>&nbsp;</td><td>'.'<input type="submit" name="pw_submit" value="'.getLanguageValue("button_save").'" />'.'</td></tr>'
        ."</table>";

    $html_ret = contend_template($html,"");

    if($status) {
        $form_errmsg = getLanguageValue("admin_messages_change_password");
        $form_errmsg .= '<input type="hidden" name="password" value="true" />';
        $form_errmsg = contend_template($form_errmsg,true);
    } elseif(isset($_POST['password']) and $_POST['password'] == "true") {
        $status = true;
        $form_errmsg = '<input type="hidden" name="password" value="true" />';
    }
    $help = contend_template(installHelp("install_password_help"),"");
#$status = true;
    return array($status,$help.$form_errmsg.$html_ret);
}

function finish() {
    $button = '<br /><input type="submit" name="go_to_admin" value="'.getLanguageValue("install_finish_submit").'" />';
    $html = contend_template(installHelp("install_finish_help").$button,"");
    return array(true,$html,true);
}

function menu_tabs($steps,$current_step,$status) {
    $post_step_status = '';
    $finish_steps = array();
    # es wurden schonn tabs erledigt
    if(isset($_POST['finish_steps']) and !isset($_POST['reset'])) {
        # die holen wir uns
        $finish_steps = explode(",",$_POST['finish_steps']);
    }
    $tabs = '<ul id="js-menu-tabs" class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-top">';
    foreach($steps as $pos => $step) {
        if(isset($_POST[$step]) and isset($_POST['reset']))
            unset($_POST[$step]);
        if(isset($_POST[$step]) and $_POST[$step] != "false")
            $post_step_status .= '<input type="hidden" name="'.$step.'" value="'.$_POST[$step].'" />';
        else
            $post_step_status .= '<input type="hidden" name="'.$step.'" value="false" />';

        $activ = "";
        # ist nicht im finish array dann hidden
        if(!in_array($step,$finish_steps))
            $activ = " ui-state-disabled js-no-click";

        # der active tab activ setzen
        if($current_step == $step) {
            $next_tab_pos = $pos + 1;
            if(!in_array($step,$finish_steps) and $status)
                $finish_steps[] = $step;
            $activ = " ui-tabs-selected ui-state-active";
        }
        if($status and count($finish_steps) == $pos) {
            $activ = "";
        }

        $tabs .= '<li class="js-multi-user ui-state-default ui-corner-top'.$activ.'">';
        $tabs .= '<a href="install.php" class="step_tabs" title="'.$step.'" name="'.$step.'">'
            .'<span class="mo-bold">'.$step.'</span>'
            .'</a>';

        $tabs .= '</li>';
    }
    $tabs .= '</ul>';
    $tabs .= '<input id="step_input" type="hidden" name="current_step" value="" />';

    $post_finish_steps = '<input type="hidden" name="finish_steps" value="'.implode(",",$finish_steps).'" />';
    return $tabs.$post_step_status.$post_finish_steps;
}


// #############################################################################

function makeConfFiles() {
    if(isset($_POST['environment']) and $_POST['environment'] == "true")
        return true;
    if(version_compare(PHP_VERSION, '5.1.2') < 0)
        return false;

    if(!is_dir(BASE_DIR_ADMIN.CONF_DIR_NAME))
        mkdir(BASE_DIR_ADMIN.CONF_DIR_NAME);
    if(!is_dir(BASE_DIR_CMS.CONF_DIR_NAME))
        mkdir(BASE_DIR_CMS.CONF_DIR_NAME);

    global $page_protect;
    global $ADMIN_CONF;
    global $CMS_CONF;

    require_once(BASE_DIR_ADMIN."default_conf.php");

    $confs = array(
            "basic" => BASE_DIR_ADMIN.CONF_DIR_NAME."/basic.conf.php",
            "logindata" => BASE_DIR_ADMIN.CONF_DIR_NAME."/logindata.conf.php",
            "loginpass" => BASE_DIR_ADMIN.CONF_DIR_NAME."/loginpass.conf.php",
            "gallery" => BASE_DIR_CMS.CONF_DIR_NAME."/gallery.conf.php",
            "main" => BASE_DIR_CMS.CONF_DIR_NAME."/main.conf.php",
            "syntax" => BASE_DIR_CMS.CONF_DIR_NAME."/syntax.conf.php",
            );

    foreach($confs as $name => $dir) {
        $conf = array();
        if($name == "basic" and $ADMIN_CONF !== false)
            $conf = $ADMIN_CONF->toArray();
        elseif($name == "main" and $CMS_CONF !== false)
            $conf = $CMS_CONF->toArray();
        elseif(is_file($dir))
            continue;
        else
            $conf = makeDefaultConf($name,true);
        if($name == "basic") {
            $conf['language'] = $_POST['language'];
            $conf['chmodnewfilesatts'] = $_POST['chmod_test'];
        }
        if($name == "main") {
            $conf['cmslanguage'] = $_POST['language'];
#            $rewrite = "false";
#            if($_POST['rewrite'] != "false")
#                $rewrite = "true";
#            $conf['modrewrite'] = $rewrite;
        }
        $conf = $page_protect.serialize($conf);
        if(false === (file_put_contents($dir,$conf,LOCK_EX))) {
            return false;
        }
        if($name == "loginpass")
            @chmod($dir,0600);
    }
    return true;
}

function installHelp($index) {
    return '<span class="mo-message-erfolg" style="background-image:url('.URL_BASE.ADMIN_DIR_NAME.'/gfx/icons/24x24/information.png);padding-left:34px;">'.getLanguageValue($index).'</span>';
}

function getLanguageValue($index,$param1 = '',$param2 = '') {
    global $LANG;
    return str_replace(array("&lt;","&gt;","&quot;"),array("<",">",'"'),$LANG->getLanguageValue($index,$param1,$param2));
}

function writeHtaccess($art,$step,$getcount = false) {

    $base_url = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], "install.php"));
    if(strlen($base_url) < 1)
        $base_url = "/";
    $rewrite_base_url     = $base_url.'install/';
    if($art == "admin")
        $rewrite_base_url = $base_url.ADMIN_DIR_NAME.'/';
    if($art == "cms")
        $rewrite_base_url = $base_url;
#?????????????? siehe http://www.mozilo.de/forum/viewtopic.php?f=8&t=2907&start=30
# php_value max_input_vars 3000
    $indexes            = 'Options -Indexes'."\n";
    $if_module_start_c  = '<IfModule rewrite_module.c>'."\n";
    $if_module_start    = '<IfModule rewrite_module>'."\n";
    $rewrite_on         = 'RewriteEngine On'."\n";
    $rewrite_base       = 'RewriteBase '.$rewrite_base_url."\n";
    $if_module_end      = '</IfModule>'."\n";

    $rewrite_rule_test  = 'RewriteRule test\.php$ test\.php?rewritetest=true [QSA,L]'."\n";
    $rewrite_rule_admin = 'RewriteRule index\.php$ index\.php?link=rewrite [QSA,L]'."\n";
    $rewrite_rule_cms   = 'RewriteRule '.ADMIN_DIR_NAME.'/index\.php$ '.ADMIN_DIR_NAME.'/index\.php [QSA,L]'."\n"
                         .'RewriteRule \.html$ index\.php [QSA,L]'."\n";


    // die verschiedenen test-configs die wir probieren
    // mit -Indexes und ohne <IfModule...>
    $arr_modrewrite_conf[0] = $indexes.$rewrite_on.${"rewrite_rule_".$art};
    // mit -Indexes und ohne ohne <IfModule...> aber mit RewriteBase
    $arr_modrewrite_conf[1] = $indexes.$rewrite_on.$rewrite_base.${"rewrite_rule_".$art};
    // mit -Indexes und ohne mit <IfModule...>
    $arr_modrewrite_conf[2] = $indexes.$if_module_start.$rewrite_on.${"rewrite_rule_".$art}.$if_module_end;
    // mit -Indexes und ohne mit <IfModule...c>
    $arr_modrewrite_conf[3] = $indexes.$if_module_start_c.$rewrite_on.${"rewrite_rule_".$art}.$if_module_end;
    // mit -Indexes und ohne mit <IfModule...> mit RewriteBase
    $arr_modrewrite_conf[4] = $indexes.$if_module_start.$rewrite_on.$rewrite_base.${"rewrite_rule_".$art}.$if_module_end;
    // mit -Indexes und ohne mit <IfModule...c> mit RewriteBase
    $arr_modrewrite_conf[5] = $indexes.$if_module_start_c.$rewrite_on.$rewrite_base.${"rewrite_rule_".$art}.$if_module_end;
    // ohne <IfModule...>
    $arr_modrewrite_conf[6] = $rewrite_on.${"rewrite_rule_".$art};
    // ohne <IfModule...> aber mit RewriteBase
    $arr_modrewrite_conf[7] = $rewrite_on.$rewrite_base.${"rewrite_rule_".$art};
    // mit <IfModule...>
    $arr_modrewrite_conf[8] = $if_module_start.$rewrite_on.${"rewrite_rule_".$art}.$if_module_end;
    // mit <IfModule...c>
    $arr_modrewrite_conf[9] = $if_module_start_c.$rewrite_on.${"rewrite_rule_".$art}.$if_module_end;
    // mit <IfModule...> mit RewriteBase
    $arr_modrewrite_conf[10] = $if_module_start.$rewrite_on.$rewrite_base.${"rewrite_rule_".$art}.$if_module_end;
    // mit <IfModule...c> mit RewriteBase
    $arr_modrewrite_conf[11] = $if_module_start_c.$rewrite_on.$rewrite_base.${"rewrite_rule_".$art}.$if_module_end;

    if($getcount)
        return count($arr_modrewrite_conf) - 1;

    if(isset($arr_modrewrite_conf[$step])) {
        $base_pfad     = BASE_DIR.'install/';
        if($art == "admin")
            $base_pfad = BASE_DIR_ADMIN;
        if($art == "cms")
            $base_pfad = BASE_DIR;
        if(($art == "admin" or $art == "cms") and is_file($base_pfad.'.htaccess')) {
            rename($base_pfad.'.htaccess',$base_pfad.'htaccess_'.time());
#            file_put_contents($base_pfad.'.htaccess', $arr_modrewrite_conf[$step]);
        }
        file_put_contents($base_pfad.'.htaccess', $arr_modrewrite_conf[$step]);
    }
}

function getNextChmod($file) {
    clearstatcache();
    $file_chmod = substr(decoct(fileperms($file)),-3);
    if(is_dir($file)) {
        if($file_chmod[0] < 7)
            return "7".$file_chmod[1].$file_chmod[2];
        if($file_chmod[1] < 7)
            return "77".$file_chmod[2];
        return "777";
    } else {
        if($file_chmod[0] < 6)
            return "6".$file_chmod[1].$file_chmod[2];
        if($file_chmod[1] < 6)
            return "66".$file_chmod[2];
        return "666";
    }
}

// chmod() setzen und bei dir X-Bit erhöhen
function setInstallChmod($file,$mode) {
#    echo $file."<br>";
    if(is_dir($file)) {
        // X-Bit setzen, um Verzeichniszugriff zu garantieren
        if($mode[0] >= 2 and $mode[0] <= 6) $mode = $mode + 100;
        if($mode[1] >= 2 and $mode[1] <= 6) $mode = $mode + 10;
        if($mode[2] >= 2 and $mode[2] <= 6) $mode = $mode + 1;
    }
    return @chmod($file, octdec($mode));
}

// Datei Les- und Schreibbar?
function isFileRW($file) {
    clearstatcache();
    return (is_readable($file) && is_writeable($file));
}

function cleanUpUpdate() {
    if(defined('TEST') and TEST === true) return;
    unlink('install.php');
    if(is_file('update.php'))
        unlink('update.php');
    if(is_dir(BASE_DIR.'update') and false !== ($currentdir = opendir(BASE_DIR.'update'))) {
        while(false !== ($file = readdir($currentdir))) {
            if($file == "." or $file == "..") continue;
            unlink(BASE_DIR.'update/'.$file);
        }
        closedir($currentdir);
        rmdir(BASE_DIR.'update');
    }
}

function mo_unlink($dir) {
    if(defined('TEST') and TEST === true) return;
    unlink($dir);
}

// -----------------------------------------------------------------------------
// Zeile "SPRACHAUSWAHL"
// -----------------------------------------------------------------------------
function getLanguageSelect() {
    global $LANG_INSTALL;
    global $LANG_TMP;
    $admin_inhalt = '<select id="select-lang" name="language" class="mo-select">';
    foreach ($LANG_INSTALL as $key => $element) {
        if(is_file(BASE_DIR_ADMIN.LANGUAGE_DIR_NAME."/language_".$key.".txt")) {
            $selected = "";
            if($key == $LANG_TMP)
                $selected = 'selected="selected" ';
            $admin_inhalt .= '<option '.$selected.'value="'.$key.'">'.$element.'</option>';
        }
    }
    $admin_inhalt .= "</select>";
    return $admin_inhalt;
}

function contend_template($daten_array,$error = NULL) {
    $template = NULL;
#    foreach($daten_array as $titel => $content) {
        $template_content = NULL;
 #       if(!is_array($daten_array)) $daten_array = array($daten_array);
#        foreach($daten_array as $value) {
            if($error === true) {
                $template_content .= '<div class="mo-in-ul-li ui-widget-content ui-state-highlight ui-corner-all ui-helper-clearfix">';
            } elseif($error === false) {
                $template_content .= '<div class="mo-in-ul-li ui-widget-content ui-state-error ui-corner-all ui-helper-clearfix">';
            } else
                $template_content .= '<div class="mo-in-ul-li ui-widget-content ui-corner-all ui-helper-clearfix">';
            if(is_array($daten_array)) {
#echo $key."=key<br />\n";
                $template_content .= '<div class="mo-in-li-l">'.$daten_array[0].'</div>'
                        .'<div class="mo-in-li-r">'.$daten_array[1].'</div>';
            } else  {
#echo $key."=key<br />\n";
#                $template_content .= '<div class="mo-div">'.$value.'</div>';
                $template_content .= '<div>'.$daten_array.'</div>';
            }
            $template_content .= '</div>';
#        }
#    }
#    $template = '<ul class="mo-ul">';
    $template .= $template_content;
#    $template .= '</ul>';
    return $template;
}

function getHtml($art) {
$install_js = 'function test_modrewrite(url,para,step) {
    var send_to_test = false;
    $.ajax({
        global: true,
        cache: false,
        type: "POST",
        url: url,
        data: para,
        async: true,
        dataType: "html",
        timeout:20000,
        success: function(data, textStatus, jqXHR){
            if($("<span>"+data+"</span>").find("#mod-rewrite-true").length > 0) {
                finish_test = true;
            } else if($("<span>"+data+"</span>").find("#return-modconf").length > 0) {
                send_to_test = true;
            }
        },
        complete: function() {
            if(send_to_test === true) {
                test_modrewrite("install/xy/test.php","",step);
            } else if(finish_test === false && step < max_step) {
                step++;
                $("#step-mod-conf").text(step);
                test_modrewrite("install.php","fromajax=true&current_step=rewrite&modconf="+step,step);
            } else if(finish_test === true) {
                $("#step-mod-conf").text(step);
                $(\'input[name="rewrite"]\').val(step);
                $("form").trigger("submit");
            } else {
                $("#step-mod-conf").text(step);
                $(\'input[name="rewrite"]\').val("no_modrewrite");
                $("form").trigger("submit");
            }
        },
    });
}

$(function() {

    if(typeof max_step != "undefined") {
        $("#step-mod-conf").text("0");
        test_modrewrite("install.php","fromajax=true&current_step=rewrite&modconf=0",0);
    }

    $(".step_tabs").bind("click", function(event) {
        event.preventDefault();
        if($(this).closest("li").hasClass("js-no-click"))
            return false;
        $("#step_input").val($(this).attr("name"));
        $("form").trigger("submit");
    });

    $("#select-lang").bind("change", function(event) {
        event.preventDefault();
        $("form").trigger("submit");
    });

    $(".js-in-pwroot").bind("keydown", function(event) {
        if(event.which == 13)
            event.preventDefault();
    });

    $("form").bind("submit",function(event) {
        if($("#step_input").val() == "")
            $("#step_input").val($(".ui-tabs-selected a").attr("name"));
    });
});';

$html_start ='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
    .'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="de">'
    .'<head>'
        .'<meta http-equiv="Content-Type" content="text/html; charset='.CHARSET.'" />'
        .'<link type="image/x-icon" rel="SHORTCUT ICON" href="'.URL_BASE.ADMIN_DIR_NAME.'/favicon.ico" />'
        .'<link type="text/css" rel="stylesheet" href="'.URL_BASE.ADMIN_DIR_NAME.'/css/mozilo/jquery-ui-1.8.21.custom.css" />'
        .'<link type="text/css" rel="stylesheet" href="'.URL_BASE.ADMIN_DIR_NAME.'/admin.css" />'
        .'<script type="text/javascript" src="'.URL_BASE.CMS_DIR_NAME.'/jquery/jquery-1.7.2.min.js"></script>'
        .'<script language="Javascript" type="text/javascript">/*<![CDATA[*/'
        .$install_js
        .'/*]]>*/</script>'

        .'<title>Setup</title>'
    .'</head>'
    .'<body>'
    .'<body class="ui-widget" style="font-size:12px;">'
    .'<table summary="" width="100%" cellspacing="0" border="0" cellpadding="0" style="margin-top:1.5em;">'
    .'<tr><td>&nbsp;</td>'
    .'<td class="mo-td-content-width" style="vertical-align:top;">'
    .'<noscript><div class="mo-noscript mo-td-content-width ui-state-error ui-corner-all"><div>'.getLanguageValue("error_no_javascript").'</div></div></noscript>'
    .'<form action="install.php" method="post">'

    .'<div class="mo-td-content-width ui-tabs ui-widget ui-widget-content ui-corner-all mo-ui-tabs" style="position:relative;">';
/*
$html .= get_Tabs();
     .'<div class="mo-ui-tabs-panel ui-widget-content ui-corner-bottom mo-no-border-top">';
content
        .'</div>'
*/
    $html_end = '</div></form>'
        .'<div id="out"></div>'
        .'</td><td>&nbsp;</td></tr></table></body></html>';

    if($art == "start")
        return $html_start;
    if($art == "end")
        return $html_end;
}
?>