<?php if(!defined('IS_CMS')) die();

class AccessControlClass {

	// --------------------------------------------------------------------
    // Member Vars
    // --------------------------------------------------------------------
    var $timestamp;
    var $randArray = array();
    var $acUser = array();
    var $acCatPage = array();

    // --------------------------------------------------------------------
    // Konstruktor
    // --------------------------------------------------------------------
    function AccessControlClass($protect_art = "catpage") {

        // Timestamp füllen
        $this->timestamp = time();

        // aus conf user array erstellen
        $tmp = new Properties(BASE_DIR_CMS.CONF_DIR_NAME."/user.conf",true);
        foreach($tmp->toArray() as $user => $pass_salt) {
            list($this->acUser[$user]['_pw-'],$this->acUser[$user]['_salt-']) = explode(",",$pass_salt);
        }
        unset($tmp);

        // aus conf zu schützende cat pages erstellen
        $tmp = new Properties(BASE_DIR_CMS.CONF_DIR_NAME."/catpage.conf",true);
        foreach($tmp->toArray() as $catpage => $user) {
            $catpagekey = explode(":",$catpage);
            if(count($catpagekey) > 1) {
                $this->acCatPage[$catpagekey[0]]['_pages-'][$catpagekey[1]] = explode(",",$user);
            } else {
                $this->acCatPage[$catpagekey[0]] = explode(",",$user);
            }
        }
        unset($tmp);

        // Session Fixation durch Vergabe einer neuen Session-ID beim ersten Logon verhindern
        if (!isset($_SESSION['acData']['acPHPSESSID'])) {
            session_regenerate_id(true);
            $_SESSION['acData']['acPHPSESSID'] = true;
        }
        // es gibt noch keinen user
        if(!isset($_SESSION['acData']['acUserName']))
            $_SESSION['acData']['acUserName'] = array();

        // will sich ein user anmelden
        // Achtung beim Anmelden muss der User, Password und Logon im POST sein
        if(isset($_SESSION['acData']['acForm'])
                and is_array($_SESSION['acData']['acForm'])
                and isset($_SESSION['acData']['acForm']['acLogon'])
                and isset($_POST[$_SESSION['acData']['acForm']['acLogon']])) { # Logoff
            $user = false;
            $password = false;
            if(isset($_SESSION['acData']['acForm']['acUser'])) {
                if(isset($_POST[$_SESSION['acData']['acForm']['acUser']])
                        and isset($this->acUser[$_POST[$_SESSION['acData']['acForm']['acUser']]]))
                    $user = $_POST[$_SESSION['acData']['acForm']['acUser']];
            }
            if($user !== false and isset($_SESSION['acData']['acForm']['acPassword'])) {
                if(isset($_POST[$_SESSION['acData']['acForm']['acPassword']])) {
                    $password = md5($_POST[$_SESSION['acData']['acForm']['acPassword']].$this->acUser[$user]['_salt-']);
                    if($this->acUser[$user]['_pw-'] != $password)
                        $password = false;
                }
            }
            # user gibts und password stimmt auch
            if ($user !== false and $password !== false) {
                # prüfen ob user schon angemeldet ist wenn nicht eintragen
                if (isset($_SESSION['acData']['acUserName']) and !in_array($user, $_SESSION['acData']['acUserName'])) {
                    $_SESSION['acData']['acUserName'][] = $user;
                } else {
                    # wenn nicht angemeldet nicht nochmals eintragen
                    # $_SESSION['acData']['acUserName'][] = $user;
                }
            } else {
                echo "FEHLER Anmelden<br>\n";
            }
        }
        # will sich ein user abmelden
        # Achtung beim Abmelden muss der User und Logoff im POST sein
        if(isset($_SESSION['acData']['acForm'])
                and is_array($_SESSION['acData']['acForm'])
                and isset($_SESSION['acData']['acForm']['acLogoff'])
                and isset($_POST[$_SESSION['acData']['acForm']['acLogoff']])) {
            if(isset($_SESSION['acData']['acForm']['acUser'])
                and isset($_POST[$_SESSION['acData']['acForm']['acUser']])
                and in_array($_POST[$_SESSION['acData']['acForm']['acUser']],$_SESSION['acData']['acUserName'])) {
                    if(false !== ($key = array_search($_POST[$_SESSION['acData']['acForm']['acUser']], $_SESSION['acData']['acUserName'])))
                        unset($_SESSION['acData']['acUserName'][$key]);
                    else
                        echo "FEHLER Abmelden<br>\n";
            }
        }

        # Access soll die Cat Pages schützen
        if($protect_art == "catpage") {
            # Anhand der user liste im $CatPage->CatPageArray den _protect- status ändern
            global $CatPage;
            foreach($this->acCatPage as $cat => $tmp) {
                if(isset($this->acCatPage[$cat]['_pages-'])) {
                    foreach($this->acCatPage[$cat]['_pages-'] as $page => $tmp) {
                       if(isset($CatPage->CatPageArray[$cat]['_pages-'][$page]['_protect-'])) {
                            $status = true;
                            foreach($_SESSION['acData']['acUserName'] as $user) {
                                if(in_array($user, $this->acCatPage[$cat]['_pages-'][$page])) {
                                    $status = false;
                                    break;
                                }
                            }
                            $CatPage->CatPageArray[$cat]['_pages-'][$page]['_protect-'] = $status;
                        }
                    }
                } else {

                    if(isset($CatPage->CatPageArray[$cat]['_protect-'])) {
                        $status = true;
                        foreach($_SESSION['acData']['acUserName'] as $user) {
                            if(in_array($user, $this->acCatPage[$cat])) {
                                $status = false;
                                break;
                            }
                        }
                        $CatPage->CatPageArray[$cat]['_protect-'] = $status;
                    }
                }
            }
        }
        # Access soll die admin/index.php schützen
        if($protect_art == "admin") {
            echo "Access soll die admin/index.php schützen<br>\n";
        }
    }


    // --------------------------------------------------------------------
    // HTML Form-Gerüst
    // --------------------------------------------------------------------
    function getForm($inForm = "", $css = "acform") {
        global $CatPage;
        $html = '<form accept-charset="'.CHARSET.'" class="'.$css.'" method="post" action="'.$CatPage->get_Href(CAT_REQUEST,PAGE_REQUEST).'">'.$inForm."</form>";
        return $html;
    }

    // --------------------------------------------------------------------
    // Gibt den kodierten Formularfeldname zurück
    // --------------------------------------------------------------------
    function getCodedInputName($type = false) {
        if($type and isset($_SESSION['acData']['acForm'][$type])) {
            return $_SESSION['acData']['acForm'][$type];
        }
        $rand = rand(1,5000);
        $valName = md5($rand.$this->timestamp);
        while(in_array($valName,$this->randArray)) {
            $rand = rand(1,5000);
            $valName = md5($rand.$this->timestamp);
        }
        $this->randArray[] = $valName;
        $_SESSION['acData']['acForm'][$type] = $valName;
        return $valName;
    }

	// --------------------------------------------------------------------
    // HTML Form Textfeld Username
	// --------------------------------------------------------------------
    function getInputUsername($css = "acuser", $size = "10", $maxlength = "30") {
        $name = $this->getCodedInputName('acUser');
        $html  = '<input type="text" name="'.$name.'" class="'.$css.'" size="'.$size.'" maxlength="'.$maxlength.'" />';
        return $html;
    }

	// --------------------------------------------------------------------
    // HTML Form Selectbox Username
	// --------------------------------------------------------------------
    function getSelectBoxUsername($user = array(), $css = "acselect", $size = "1") {
        $name = $this->getCodedInputName('acUser');
        if(count($user) < 1)
            $user = array_keys($this->arrayAccessData);
        $html = '<select name="'. $name .'" class="'.$css.'" size="'.$size.'">';
        foreach ($user as $username) {
            $html .= '<option value="'.$username.'">'.$username."</option>";
        }
        $html .= "</select>";
        return $html;
    }

	// --------------------------------------------------------------------
    // HTML Form Selectbox mit eingeloggten Usernamen
	// --------------------------------------------------------------------
    function getSelectBoxLoggedInUsername($css = "acselect", $size = "1") {
        $name = $this->getCodedInputName('acUser');
        if(count($_SESSION['acData']['acUserName']) < 1)
            return NULL;
        $html = '<select name="'. $name .'" class="'.$css.'" size="'.$size.'">';
        foreach ($_SESSION['acData']['acUserName'] as $username) {
            $html .= '<option value="'.$username.'">'.$username."</option>";
        }
        $html .= "</select>";
        return $html;
    }

	// --------------------------------------------------------------------
    // HTML Form Input Password
	// --------------------------------------------------------------------
    function getInputPassword($css = "acpass", $size = "10", $maxlength = "30") {
        $name = $this->getCodedInputName('acPassword');
        $html  = '<input type="password" name="'.$name.'" class="'.$css.'" size="'.$size.'" maxlength="'.$maxlength.'" />';
        return $html;
    }

	// --------------------------------------------------------------------
    // HTML Form Logon Button
	// --------------------------------------------------------------------
    function getLogonButton($title, $css = "aclogon") {
        $name = $this->getCodedInputName('acLogon');
        $html  = '<input type="hidden" name="'.$name.'" value="Logon" /><input type="submit" value="'.$title.'" class="'.$css.'" />';
        return $html;
    }

	// --------------------------------------------------------------------
    // HTML Form Logoff Button
	// --------------------------------------------------------------------
    function getLogoffButton($title, $css = "aclogoff") {
        $name = $this->getCodedInputName('acLogoff');
        $html  = '<input type="hidden" name="'.$name.'" value="Logoff" /><input type="submit" value="'.$title.'" class="'.$css.'" />';
        return $html;
    }

	// --------------------------------------------------------------------
    // Default Login-Form erstellen
    // (Wrapper-Funktion über einige der obige Methoden)
	// --------------------------------------------------------------------
    function getDefaultLogonForm() {
        $html = $this->getForm(
                    "User<br>"
                    .$this->getInputUsername()."<br>"
                    ."Passwort<br>"
                    .$this->getInputPassword()."<br>"
                    .$this->getLogonButton("Login")
                );
        return $html;
    }

    // --------------------------------------------------------------------
    // Default Logoff-Form erstellen
    // --------------------------------------------------------------------
    function getDefaultLogoffForm() {
        $html = $this->getForm(
                    "User<br>"
                    .$this->getSelectBoxLoggedInUsername()."<br>"
                    .$this->getLogoffButton("Logoff")
                );
        return $html;
    }

    // --------------------------------------------------------------------
    // user mit passwort anlegen
    // --------------------------------------------------------------------
    function saveUser($username, $password) {
        $this->arrayAccessData[$username] = $password;
        return true;
    }

    // --------------------------------------------------------------------
    // user löschen
    // --------------------------------------------------------------------
    function deleteUser($username) {
        if (isset($this->arrayAccessData[$username])) {
            unset($this->arrayAccessData[$username]);
            return true;
        }
        return false;
    }

    // --------------------------------------------------------------------
    // alle cat pages die geschützt sind und nicht durch user anmeldung
    // freigegeben sind aus dem menue entfernen
    // --------------------------------------------------------------------
    function hideMenueEntries() {
        global $CatPage;
        foreach($this->acCatPage as $cat => $tmp) {
            if(isset($this->acCatPage[$cat]['_pages-'])) {
                foreach($this->acCatPage[$cat]['_pages-'] as $page => $tmp) {
                    if($CatPage->is_Protectet($cat, $page))
                        $CatPage->delete_Page($cat,$page);
                }
            } else {
                if($CatPage->is_Protectet($cat,false))
                    $CatPage->delete_Cat($cat);
            }
        }
    }

    // --------------------------------------------------------------------
    // ist die cat pages geschützt
    // --------------------------------------------------------------------
    function is_Protectet($cat = false, $page = false) {
        global $CatPage;
        return $CatPage->is_Protectet($cat, $page);
    }


    // --------------------------------------------------------------------
    // ersetzt den aktuellen content inhalt mit $replace
    // --------------------------------------------------------------------
    function showInContent($replace = "") {
        global $syntax;
        list($first,$content,$last) = $syntax->splitContent();
        $syntax->content = str_replace($content,$replace,$syntax->content);
    }


    function makeLoginArray() {
        #$users['user1'] = password
        #$protect['cat'] = array(user)
        #$protect['cat']['_pages-']['page'] = array(user)
        
        
        # Achtung user dürfen keine , oder : oder = enthalten
        #user.conf
        #user = password
        
        #catpage.conf
        #cat:page = user1,user2
    }

}
?>