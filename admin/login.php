<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

// MAXIMALE ANZAHL FALSCHER LOGINS
$FALSELOGINLIMIT = 3;
// DAUER DER SPERRE NACH FALSCHEN LOGINS IN MINUTEN
$LOGINLOCKTIME = 10;

// Überprüfen: Existiert ein Benutzer? Wenn nicht: admin:install anlegen
if (($loginpassword->get("name") == "") or ($loginpassword->get("pw") == "")) {
    // Install Formular anzeigen
    return login_formular(false,"install_login");
}

// User hat sich ausgeloggt
if(getRequestValue('logout','get',false)) {
    // Session beenden und die Sessiondaten löschen
    session_destroy();
    unset($_SESSION);
    # draft modus entfernen
    $CMS_CONF->set("draft", false);
}

// Wurde das Anmeldeformular verschickt?
if(getRequestValue('login','post',false)
        and false !== ($name = getRequestValue('username','post',false))
        and false !== ($pw = getRequestValue('password','post',false))) {
    // Zugangsdaten prüfen
    if(checkLoginData($name, $pw)) {
        // Daten in der Session merken
        $_SESSION['username'] = $name;
        $_SESSION['login_okay'] = true;
        $_SESSION['login_tmp'] = getClientDaten();
    }
}

// Anmeldung erfolgreich
if(isset($_SESSION['login_okay']) and $_SESSION['login_okay'] === true
        and isset($_SESSION['login_tmp']) and $_SESSION['login_tmp'] === getClientDaten()) {
    # notfall MULTI_USER reset &userssreset=j
    if(MULTI_USER and isset($_GET['userssreset']) and $_GET['userssreset'] == "j") {
        $tmp = @session_save_path().((substr(session_save_path(),-1) != "/") ? "/" : "");
        unlink($tmp."users.conf.php");
        unlink($tmp."session.conf.php");
        header("Location: http://".$_SERVER['SERVER_NAME'].URL_BASE.ADMIN_DIR_NAME."/");
        header("Connection: close"); 
        exit();
    }
    define("LOGIN",true);
    # draft modus setzen
    $CMS_CONF->set("draft", true);
    // Counter für falsche Logins innerhalb der Sperrzeit zurücksetzen
    $LOGINCONF->set("falselogincounttemp", 0);
    return true;

// Anmeldung fehlerhaft
} elseif(getRequestValue('login','post',false)) {
    // Counter hochzählen
    $falselogincounttemp = ($LOGINCONF->get("falselogincounttemp"))+1;
    $LOGINCONF->set("falselogincounttemp", $falselogincounttemp); // Zähler für die aktuelle Sperrzeit
    $falselogincount = ($LOGINCONF->get("falselogincount"))+1;
    $LOGINCONF->set("falselogincount", $falselogincount); // Gesamtzähler

    // maximale Anzahl falscher Logins erreicht?
    if($falselogincounttemp >= $FALSELOGINLIMIT) {
        // Sperrzeit starten
        $LOGINCONF->set("loginlockstarttime", time());
        // Mail an Admin
        if(strlen($ADMIN_CONF->get("adminmail")) > 5) {
            $mailcontent = getLanguageValue("loginlocked_mailcontent")."\r\n\r\n"
                .strftime(getLanguageValue("_dateformat"), time())."\r\n"
                .$_SERVER['REMOTE_ADDR']." / ".gethostbyaddr($_SERVER['REMOTE_ADDR'])."\r\n"
                .getLanguageValue("username").": ".getRequestValue('username','post',false);
            require_once(BASE_DIR_CMS."Mail.php");
            // Prüfen, ob die Mail-Funktion vorhanden ist
            if(function_exists("isMailAvailable")) {
                sendMailToAdmin(getLanguageValue("loginlocked_mailsubject"), $mailcontent);
            }
        }
        // Formular ausgrauen
        return login_formular(false,"warning_false_logins");
    }
    else {
        // Formular nochmal normal anzeigen
        return login_formular(true,"incorrect_login");
    }

// Formular noch nicht abgeschickt? Dann wurde die Seite zum ersten Mal aufgerufen.
} else {
    // Login noch gesperrt?
    if (($LOGINCONF->get("falselogincounttemp") > 0)
            and (time() - $LOGINCONF->get("loginlockstarttime")) <= $LOGINLOCKTIME * 60) {
        // gesperrtes Formular anzeigen
        return login_formular(false,"warning_false_logins");
    } else {
        // Zähler zurücksetzen
        $LOGINCONF->set("falselogincounttemp", 0);
        // normales Formular anzeigen
        return login_formular(true);
    }
}
return false;

// Aufbau des Login-Formulars
function login_formular($enabled,$error_lang = false) {
    # das "error" wird gebraucht damit bei einer ajax anfrage der login erscheint
    $form = '<div class="error mo-login-box js-dialog-content js-dialog-reload">';
    $enabled_css = "ui-state-highlight";
    $enabled_input = "";
    if(!$enabled) {
        $enabled_css = "ui-state-error";
        $enabled_input = ' readonly="readonly"';
    }
    if($error_lang !== false)
        $form .= '<div class="mo-login_message_fehler ui-widget-content ui-state-error ui-corner-all ui-helper-clearfix">'.returnMessage(false, getLanguageValue($error_lang))."</div>";

   $form .= '<div class="mo-login '.$enabled_css.' ui-corner-all">';

    if ($enabled)
        $form .= '<form accept-charset="'.CHARSET.'" name="loginform" action="'.URL_BASE.ADMIN_DIR_NAME."/index.php".'" method="post">';
    $form .= '<table width="100%" cellspacing="10" border="0" cellpadding="0">'
        ."<tr>"
        .'<td width="1%" rowspan="2" align="center" valign="middle">'
        .'<img src="'.ICON_URL.'login.png" alt="Login" />'
        ."</td>"
        .'<td width="1%" class="mo-nowrap">'
        .getLanguageValue("username").":"
        ."</td>"
        ."<td>"
        .'<input class="mo-login_input" type="text" size="15" name="username" autocomplete="off"'.$enabled_input.' />'
        ."</td>"
        ."</tr>"
        ."<tr>"
        .'<td class="mo-nowrap">'
        .getLanguageValue("password").":"
        ."</td>"
        ."<td>"
        .'<input class="mo-login_input" type="password" size="15" name="password" autocomplete="off"'.$enabled_input.' />'
        ."</td>"
        ."</tr>"
        ."<tr>"
        .'<td colspan="3" class="mo-align-center">'
        .'<input name="login" value="Login" class="mo-login_submit" type="submit"'.$enabled_input.' />'
        ."</td>"
        ."</tr>"
        ."</table>";
  if ($enabled)
      $form .= "</form>";
    $form .= '</div></div>';
    return $form;
}

// Logindaten überprüfen
function checkLoginData($user, $pass) {
    global $loginpassword;
    require_once(BASE_DIR_ADMIN.'PasswordHash.php');
    $t_hasher = new PasswordHash(8, FALSE);

    if(($user == $loginpassword->get("name")) and ($pass == $t_hasher->CheckPassword($pass, $loginpassword->get("pw")))) {
        return true;
    } elseif((strlen($loginpassword->get("username")) > 4) and ($user == $loginpassword->get("username")) and ($pass == $t_hasher->CheckPassword($pass, $loginpassword->get("userpw")))) {
        return true;
    } else {
        return false;
    }
}


function getClientDaten() {
    # ie browser senden kein HTTP_USER_AGENT bei einer ajax anfrage
    if(!isset($_SERVER['HTTP_USER_AGENT']) or stristr($_SERVER['HTTP_USER_AGENT'],'MSIE'))
        return "";
    $client = array('HTTP_USER_AGENT','HTTP_ACCEPT_ENCODING','HTTP_ACCEPT_LANGUAGE','HTTP_ACCEPT_CHARSET','REMOTE_ADDR');
    $hash = "";
    foreach($client as $tmp) {
        if(isset($_SERVER[$tmp])) {
            $hash .= $_SERVER[$tmp];
        }
    }
    return $hash;
}
?>