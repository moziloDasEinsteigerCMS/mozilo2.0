<?php if(!defined('IS_CMS')) die();
// ------------------------------------------------------------------------------
// Gibt das Kontaktformular zurueck
// ------------------------------------------------------------------------------
    function buildContactForm($settings) {
        global $lang_contact;
        global $CMS_CONF;
        global $specialchars;
        global $lang_contact;

        $WEBSITE_NAME = $specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"),false,true);
        if($WEBSITE_NAME == "")
            $WEBSITE_NAME = "Titel der Website";

        foreach(array("name","website","mail","message","privacy") as $name) {
            ${"config_".$name}[0] = $lang_contact->getLanguageValue("contactform_".$name);
            if($settings->get("titel_".$name))
                ${"config_".$name}[0] = $specialchars->rebuildSpecialChars($settings->get("titel_".$name),false,false);
            ${"config_".$name}[1] = "false";
            if($settings->get("titel_".$name."_show"))
                ${"config_".$name}[1] = $settings->get("titel_".$name."_show");
            ${"config_".$name}[2] = "false";
            if($settings->get("titel_".$name."_mandatory"))
                ${"config_".$name}[2] = $settings->get("titel_".$name."_mandatory");
        }

        $mandatory = false;
        if(($config_name[2] == "true") or ($config_mail[2] == "true") or ($config_website[2] == "true") or ($config_message[2] == "true") or ($config_privacy[2] == "true"))
            $mandatory = true;

        $errormessage = "";
        $form = "";

        if (isset($_SESSION['contactform_name'])) {
            $name       = getRequestValue($_SESSION['contactform_name'],'post', false);
            $mail       = getRequestValue($_SESSION['contactform_mail'],'post', false);
            $website    = getRequestValue($_SESSION['contactform_website'],'post', false);
            $message    = getRequestValue($_SESSION['contactform_message'],'post', false);
            $calcresult = getRequestValue($_SESSION['contactform_calculation'],'post', false);
            $privacy    = "";
            if(getRequestValue($_SESSION['contactform_privacy'],'post', false))
                $privacy    = getRequestValue($_SESSION['contactform_privacy'],'post', false);
        }
        else {
            $name       = "";
            $mail       = "";
            $website    = "";
            $message    = "";
            $calcresult = "";
            $privacy    = "";
        }
        // Das Formular wurde abgesendet
        if (getRequestValue('submit','post', false) <> "") { 

            // Bot-Schutz: Wurde das Formular innerhalb von x Sekunden abgeschickt?
            $sendtime = $settings->get("contactformwaittime");
            if (($sendtime == "") || !preg_match("/^[\d+]+$/", $sendtime)) {
                $sendtime = 15;
            }
            if (time() - $_SESSION['contactform_loadtime'] < $sendtime) {
                $errormessage = $lang_contact->getLanguageValue("contactform_senttoofast", $sendtime);
            }
            if ($settings->get("contactformusespamprotection") == "true") {
                // Nochmal Spamschutz: Ergebnis der Spamschutz-Aufgabe auswerten
                if (strtolower($calcresult) != strtolower($_SESSION['calculation_result'])) {
                    $errormessage = $lang_contact->getLanguageValue("contactform_wrongresult");
                }
            }
            // Es ist ein Fehler aufgetreten!
            if ($errormessage == "") {
                // Eines der Pflichtfelder leer?
                if (($config_name[2] == "true") && ($name == "")) {
                    $errormessage = $lang_contact->getLanguageValue("contactform_fieldnotset")." ".$config_name[0];
                }
                else if (($config_mail[2] == "true") && ($mail == "")) {
                    $errormessage = $lang_contact->getLanguageValue("contactform_fieldnotset")." ".$config_mail[0];
                }
                else if (($config_website[2] == "true") && ($website == "")) {
                    $errormessage = $lang_contact->getLanguageValue("contactform_fieldnotset")." ".$config_website[0];
                }
                else if (($config_message[2] == "true") && ($message == "")) {
                    $errormessage = $lang_contact->getLanguageValue("contactform_fieldnotset")." ".$config_message[0];
                }
                else if (($config_privacy[2] == "true") && ($privacy == "")) {
                    $errormessage = $lang_contact->getLanguageValue("contactform_fieldnotset_privacy");
                }
            }
            // Es ist ein Fehler aufgetreten!
            if ($errormessage <> "") {
                $form .= "<span id=\"contact_errormessage\">".$errormessage."</span>";
            }
            else {
                $mailcontent = "";
                if ($config_name[1] == "true") {
                    $mailcontent .= $config_name[0].":\t".$name."\r\n";
                }
                if ($config_mail[1] == "true") {
                    $mailcontent .= $config_mail[0].":\t".$mail."\r\n";
                }
                if ($config_website[1] == "true") {
                    $mailcontent .= $config_website[0].":\t".$website."\r\n";
                }
                if ($config_message[1] == "true") {
                    $mailcontent .= "\r\n".$config_message[0].":\r\n".$message."\r\n";
                }
                if ($config_privacy[1] == "true") {
                    # ☐ &#x2610; ☒ &#x2612;
                    $checket = "☐";
                    if(!empty($privacy))
                        $checket = "☒";
                    $mailcontent .= $checket." ".$config_privacy[0]."\r\n";
                }
                $mailsubject = $lang_contact->getLanguageValue("contactform_mailsubject", $specialchars->getHtmlEntityDecode($WEBSITE_NAME));
                $mailsubject_confirm = $lang_contact->getLanguageValue("contactform_mailsubject_confirm", $specialchars->getHtmlEntityDecode($WEBSITE_NAME));
                
                require_once(BASE_DIR_CMS."Mail.php");
                // Wenn Mail-Adresse im Formular gesetzt ist - versuchen Kopie dorthin zu senden
                if ($mail <> "" and $settings->get("titel_mail_send_copy") == "true") {
                    sendMail($mailsubject_confirm, $mailcontent, $settings->get("formularmail"), $mail, $settings->get("formularmail"));
                }
                // Mail an eingestellte Mail-Adresse (Mail-Absender muss auch diese Adresse sein,
                // sonst gibts kein Mail wenn der keine oder ungültige Adresse eingibt..
                sendMail($mailsubject, $mailcontent, $settings->get("formularmail"), $settings->get("formularmail"), $mail);
                $form .= "<span id=\"contact_successmessage\">".$lang_contact->getLanguageValue("contactform_confirmation")."</span>";
                
                // Felder leeren
                $name = "";
                $mail = "";
                $website = "";
                $message = "";
                $privacy = "";
            }
        }

        // Wenn das Formular nicht abgesendet wurde: die Feldnamen neu bestimmen
        else {
            renameContactInputs();
        }
        
        // aktuelle Zeit merken
        $_SESSION['contactform_loadtime'] = time();
        global $CatPage;
        $action_para = $CatPage->get_Href(CAT_REQUEST,PAGE_REQUEST);

        $form .= "<form accept-charset=\"".CHARSET."\" method=\"post\" action=\"$action_para\" name=\"contact_form\" id=\"contact_form\">"
        ."<input type=\"hidden\" name=\"cat\" value=\"".$CatPage->get_AsKeyName(CAT_REQUEST)."\" />"
        ."<input type=\"hidden\" name=\"page\" value=\"".$CatPage->get_AsKeyName(PAGE_REQUEST)."\" />"
        ."<table id=\"contact_table\" summary=\"contact form table\">";
        if ($config_name[1] == "true") {
            // Bezeichner aus formular.conf nutzen, wenn gesetzt
            $form .= "<tr><td style=\"padding-right:10px;\">".$config_name[0];
            if ($config_name[2] == "true") {
                $form .= "*";
            }
            $form .= "</td><td><input type=\"text\" id=\"contact_name\" name=\"".$_SESSION['contactform_name']."\" value=\"".$name."\" /></td></tr>";
        }
        if ($config_website[1] == "true") {
            // Bezeichner aus formular.conf nutzen, wenn gesetzt
            $form .= "<tr><td style=\"padding-right:10px;\">".$config_website[0];
            if ($config_website[2] == "true") {
                $form .= "*";
            }
            $form .= "</td><td><input type=\"text\" id=\"contact_website\" name=\"".$_SESSION['contactform_website']."\" value=\"".$website."\" /></td></tr>";
        }
        if ($config_mail[1] == "true") {
            // Bezeichner aus formular.conf nutzen, wenn gesetzt
            $form .= "<tr><td style=\"padding-right:10px;\">".$config_mail[0];
            if ($config_mail[2] == "true") {
                $form .= "*";
            }
            $form .= "</td><td><input type=\"text\" id=\"contact_mail\" name=\"".$_SESSION['contactform_mail']."\" value=\"".$mail."\" /></td></tr>";
        }
        if ($config_message[1] == "true") {
            // Bezeichner aus formular.conf nutzen, wenn gesetzt
            $form .= "<tr><td style=\"padding-right:10px;\">".$config_message[0];
            if ($config_message[2] == "true") {
                $form .= "*";
            }
            $form .= "</td><td><textarea rows=\"10\" cols=\"50\" id=\"contact_message\" name=\"".$_SESSION['contactform_message']."\">".$message."</textarea></td></tr>";
        }

        if($settings->get("contactformusespamprotection") == "true") {
            $mandatory = true;
            // Spamschutz-Aufgabe
            $calculation_data = getRandomCalculationData($settings);
            $_SESSION['calculation_result'] = $calculation_data[1];
            $form .= "<tr><td colspan=\"2\">".$lang_contact->getLanguageValue("contactform_spamprotection_text")."</td></tr>"
                ."<tr><td style=\"padding-right:10px;\">".$calculation_data[0]."*</td>"
                ."<td><input type=\"text\" id=\"contact_calculation\" name=\"".$_SESSION['contactform_calculation']."\" value=\"\" /></td></tr>";
            
        }

        if ($config_privacy[1] == "true") {
            $form .= "<tr><td colspan=\"2\" style=\"padding-right:10px;\"><input type=\"checkbox\" id=\"contact_privacy\" name=\"".$_SESSION['contactform_privacy']."\" value=\"".$_SESSION['contactform_privacy']."\" /><label for=\"contact_privacy\" style=\"padding-left:10px;\">".$config_privacy[0];
            if ($config_privacy[2] == "true") {
                $form .= "*";
            }
            $form .= "</label></td></tr>";
        }
        if($mandatory)
            $form .= "<tr><td style=\"padding-right:10px;\">&nbsp;</td><td>".$lang_contact->getLanguageValue("contactform_mandatory_fields")."</td></tr>";
        $form .= "<tr><td style=\"padding-right:10px;\">&nbsp;</td><td><input type=\"submit\" class=\"submit\" id=\"contact_submit\" name=\"submit\" value=\"".$lang_contact->getLanguageValue("contactform_submit")."\" /></td></tr>";
        $form .= "</table>"
        ."</form>";
        
        return $form;
    }


// ------------------------------------------------------------------------------
// Hilfsfunktion: Zufaellige Spamschutz-Rechenaufgabe und deren Ergebnis zurueckgeben
// ------------------------------------------------------------------------------
    function getRandomCalculationData($settings) {
        $tmp_calcs = explode("<br />",$settings->get("contactformcalcs"));
        foreach($tmp_calcs as $zeile) {
            $tmp_z = explode(" = ",$zeile);
            if(isset($tmp_z[0]) and isset($tmp_z[1]) and !empty($tmp_z[0]) and !empty($tmp_z[1]))
                $contactformcalcs[$tmp_z[0]] = $tmp_z[1];
        }
        $tmp = array_keys($contactformcalcs);
        $randnum = rand(0, count($contactformcalcs)-1);
        return array($tmp[$randnum],$contactformcalcs[$tmp[$randnum]]);
    }
// ------------------------------------------------------------------------------
// Hilfsfunktion: Bestimmt die Inputnamen neu
// ------------------------------------------------------------------------------    
    function renameContactInputs() {
        $_SESSION['contactform_name'] = time()-rand(30, 40);
        $_SESSION['contactform_mail'] = time()-rand(10, 20);
        $_SESSION['contactform_website'] = time()-rand(0, 10);
        $_SESSION['contactform_message'] = time()-rand(40, 50);
        $_SESSION['contactform_calculation'] = time()-rand(50, 60);
        $_SESSION['contactform_privacy'] = time()-rand(60, 70);
    }


?>