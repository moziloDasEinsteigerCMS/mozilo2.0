<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

function home() {
    require_once(BASE_DIR_CMS."Mail.php");
    // Testmail schicken und gleich raus hier
    if(false !== ($test_mail_adresse = getRequestValue('test_mail_adresse','post'))
            and $test_mail_adresse != "") {
        header('content-type: text/html; charset='.CHARSET.'');
        global $specialchars;
        $test_mail_adresse = $specialchars->rebuildSpecialChars($test_mail_adresse,false,false);
        if(isMailAddressValid($test_mail_adresse)) {
            sendMail(getLanguageValue("home_mailtest_mailsubject"),
                getLanguageValue("home_mailtest_mailcontent"),
                $test_mail_adresse,
                $test_mail_adresse);
            ajax_return("success",true,returnMessage(true,getLanguageValue("home_messages_test_mail")."<br /><br /><b>".$test_mail_adresse.'</b>'),true,true);
        } else {
            ajax_return("error",true,returnMessage(false,getLanguageValue("home_error_test_mail")."<br /><br /><b>".$test_mail_adresse.'</b>'),true,true);
        }
        exit();
    }

    // CMS-Hilfe
    $titel = "home_help";
    if(file_exists(BASE_DIR."docu/docu.php")) {
        $error[$titel][] = false;
        $template[$titel][] = getLanguageValue("home_help_text_docu").'&nbsp;&nbsp;<a href="'.URL_BASE.'docu/docu.php" target="_blank" class="mo-butten-a-img"><img src="'.ADMIN_ICONS.'/docu.png" alt="help" hspace="0" vspace="0" border="0" /></a>';
        $error[$titel][] = false;
        $template[$titel][] = getLanguageValue("home_help_text_info").'&nbsp;&nbsp;<a href="'.URL_BASE.'docu/docu.php?menu=false&amp;artikel=start" target="_blank" class="js-docu-link mo-butten-a-img"><img src="'.ADMIN_ICONS.'/help.png" alt="help" hspace="0" vspace="0" border="0" /></a>';
    } else {
        $error[$titel][] = true;
        $template[$titel][] = getLanguageValue("home_no_help");
    }

    // CMS-INFOS
    $titel = "home_cmsinfo";
    // Zeile "CMS-VERSION"
    $error[$titel][] = false;
    $template[$titel][] = array(getLanguageValue("home_cmsversion_text"),CMSVERSION.' ("'.CMSNAME.'")<br />'.getLanguageValue("home_cmsrevision_text").' '.CMSREVISION);

    // Zeile "Gesamtgröße des CMS"
    $cmssize = convertFileSizeUnit(dirsize(BASE_DIR));
    if($cmssize === false) {
        $error[$titel][] = true;
        $cmssize = "0";
    } else
        $error[$titel][] = false;
    $template[$titel][] = array(getLanguageValue("home_cmssize_text"),$cmssize);

    // Zeile "Installationspfad" und alle 40 Zeichen einen Zeilenumbruch einfügen
    $path = BASE_DIR;
    if(strlen($path) >= 40) {
        $path = explode("/",$path);
        if(is_array($path)) {
            if(empty($path[count($path)-1]))
                unset($path[count($path)-1]);
            $i = 0;
            $new_path[$i] = "";
            foreach($path as $string) {
                $string = $string."/";
                if(strlen($new_path[$i].$string) <= 40)
                    $new_path[$i] = $new_path[$i].$string;
                else {
                    $i++;
                    $new_path[$i] = $string;
                }
            }
        }
        $path = implode("<br />",$new_path);
    }
    $error[$titel][] = false;
    $template[$titel][] = array(getLanguageValue("home_installpath_text"),$path);

     // SERVER-INFOS
    $titel = "home_serverinfo";

    // Zeile "PHP-Version"
#!!!!!!!! die version müssen wir noch checken
    if(version_compare(PHP_VERSION, '5.1.2') >= 0) {
        $error[$titel][] = "ok";
        $template[$titel][] = array(getLanguageValue("home_phpversion_text"),phpversion());
    } else {
        $error[$titel][] = getLanguageValue("home_error_phpversion_text");
        $template[$titel][] = array(getLanguageValue("home_phpversion_text"),phpversion());
    }

    // Zeile "Safe Mode"
    if(ini_get('safe_mode')) {
        $error[$titel][] = getLanguageValue("home_error_safe_mode");
        $template[$titel][] = array(getLanguageValue("home_text_safemode"),getLanguageValue("yes"));
    } else {
        $error[$titel][] = "ok";
        $template[$titel][] = array(getLanguageValue("home_text_safemode"),getLanguageValue("no"));
    }

    // Zeile "GDlib installiert"
    if(!extension_loaded("gd")) {
        $error[$titel][] = getLanguageValue("home_error_gd");
        $template[$titel][] = array(getLanguageValue("home_text_gd"),getLanguageValue("no"));
    } else {
        $error[$titel][] = "ok";
        $template[$titel][] = array(getLanguageValue("home_text_gd"),getLanguageValue("yes"));
    }

    # mod_rewrite
    if("rewrite" == getRequestValue('link','get')) {
        $error[$titel][] = "ok";
        $template[$titel][] = array(getLanguageValue("home_mod_rewrite"),getLanguageValue("yes"));
    } else {
        $error[$titel][] = getLanguageValue("home_error_mod_rewrite");
        $template[$titel][] = array(getLanguageValue("home_mod_rewrite"),getLanguageValue("no"));
    }

    # backupsystem
    if(function_exists('gzopen')) {
        $error[$titel][] = "ok";
        $template[$titel][] = array(getLanguageValue("home_text_backupsystem"),getLanguageValue("yes"));
    } else {
        $error[$titel][] = true;
        $template[$titel][] = array(getLanguageValue("home_error_backupsystem"),getLanguageValue("no"));
    }

    # MULTI_USER
    if(defined('MULTI_USER') and MULTI_USER) {
        $error[$titel][] = "ok";
        $template[$titel][] = array("Multiuser mode Verfügbar",MULTI_USER_TIME." sec.");
    } else {
        $error[$titel][] = true;
        $template[$titel][] = array("Multiuser mode Verfügbar",getLanguageValue("no"));
    }

    // E-Mail test
    if(isMailAvailable()) {
        $titel = "home_titel_test_mail";
        $error[$titel][] = false;
        $template[$titel][] = array(getLanguageValue("home_text_test_mail"),'<input type="text" class="mo-input-text" name="test_mail_adresse" value="" />');
    } else {
        $titel = "home_titel_test_mail";
        $error[$titel][] = true;
        $template[$titel][] = getLanguageValue("home_messages_no_mail");
    }

    return contend_template($template,$error);
}


?>