<?php if(!defined('IS_CMS')) die();
// ------------------------------------------------------------------------------
// Gibt das Kontaktformular zurueck
// ------------------------------------------------------------------------------
    function buildContactForm($contactformconfig) {
#        global $contactformconfig;
        global $lang_contact;
        global $CMS_CONF;
        global $WEBSITE_NAME;
        global $specialchars;
        global $lang_contact;

#        require_once($BASE_DIR_CMS."Mail.php");
        
        // Sollen die Spamschutz-Aufgaben verwendet werden?
        $usespamprotection = $contactformconfig->get("contactformusespamprotection") == "true";

        foreach(array("name","website","mail","message") as $name) {
            ${"config_".$name}[0] = $lang_contact->getLanguageValue("contactform_".$name."_0");
            if($contactformconfig->get("titel_".$name))
                ${"config_".$name}[0] = $specialchars->rebuildSpecialChars($contactformconfig->get("titel_".$name),false,true);
            ${"config_".$name}[1] = "false";
            if($contactformconfig->get("titel_".$name."_show"))
                ${"config_".$name}[1] = $contactformconfig->get("titel_".$name."_show");
            ${"config_".$name}[2] = "false";
            if($contactformconfig->get("titel_".$name."_mandatory"))
                ${"config_".$name}[2] = $contactformconfig->get("titel_".$name."_mandatory");
        }

        $mandatory = false;
        if(($config_name[2] == "true") or ($config_mail[2] == "true") or ($config_website[2] == "true") or ($config_message[2] == "true"))
            $mandatory = true;

        $errormessage = "";
        $form = "";
        
        if (isset($_SESSION['contactform_name'])) {
            $name       = getRequestValue($_SESSION['contactform_name'],'post', false);
            $mail       = getRequestValue($_SESSION['contactform_mail'],'post', false);
            $website    = getRequestValue($_SESSION['contactform_website'],'post', false);
            $message    = getRequestValue($_SESSION['contactform_message'],'post', false);
            $calcresult = getRequestValue($_SESSION['contactform_calculation'],'post', false);
        }
        else {
            $name       = "";
            $mail       = "";
            $website    = "";
            $message    = "";
            $calcresult = "";
        }
        // Das Formular wurde abgesendet
        if (getRequestValue('submit','post', false) <> "") { 

            // Bot-Schutz: Wurde das Formular innerhalb von x Sekunden abgeschickt?
            $sendtime = $contactformconfig->get("contactformwaittime");
            if (($sendtime == "") || !preg_match("/^[\d+]+$/", $sendtime)) {
                $sendtime = 15;
            }
            if (time() - $_SESSION['contactform_loadtime'] < $sendtime) {
                $errormessage = $lang_contact->getLanguageValue("contactform_senttoofast_1", $sendtime);
            }
            if ($usespamprotection) {
                // Nochmal Spamschutz: Ergebnis der Spamschutz-Aufgabe auswerten
                if (strtolower($calcresult) != strtolower($_SESSION['calculation_result'])) {
                    $errormessage = $lang_contact->getLanguageValue("contactform_wrongresult_0");
                }
            }
            // Es ist ein Fehler aufgetreten!
            if ($errormessage == "") {
                // Eines der Pflichtfelder leer?
                if (($config_name[2] == "true") && ($name == "")) {
                    $errormessage = $lang_contact->getLanguageValue("contactform_fieldnotset_0")." ".$config_name[0];
                }
                else if (($config_mail[2] == "true") && ($mail == "")) {
                    $errormessage = $lang_contact->getLanguageValue("contactform_fieldnotset_0")." ".$config_mail[0];
                }
                else if (($config_website[2] == "true") && ($website == "")) {
                    $errormessage = $lang_contact->getLanguageValue("contactform_fieldnotset_0")." ".$config_website[0];
                }
                else if (($config_message[2] == "true") && ($message == "")) {
                    $errormessage = $lang_contact->getLanguageValue("contactform_fieldnotset_0")." ".$config_message[0];
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
                $mailsubject = $lang_contact->getLanguageValue("contactform_mailsubject_1", $specialchars->getHtmlEntityDecode($WEBSITE_NAME));
                $mailsubject_confirm = $lang_contact->getLanguageValue("contactform_mailsubject_confirm_1", $specialchars->getHtmlEntityDecode($WEBSITE_NAME));
                
                require_once(BASE_DIR_CMS."Mail.php");
                // Wenn Mail-Adresse im Formular gesetzt ist - versuchen Kopie dorthin zu senden
                if ($mail <> "") {
                    sendMail($mailsubject_confirm, $mailcontent, $contactformconfig->get("formularmail"), $mail, $contactformconfig->get("formularmail"));
                }
                // Mail an eingestellte Mail-Adresse (Mail-Absender muss auch diese Adresse sein,
                // sonst gibts kein Mail wenn der keine oder ungültige Adresse eingibt..
                sendMail($mailsubject, $mailcontent, $contactformconfig->get("formularmail"), $contactformconfig->get("formularmail"), $mail);
                $form .= "<span id=\"contact_successmessage\">".$lang_contact->getLanguageValue("contactform_confirmation_0")."</span>";
                
                // Felder leeren
                $name = "";
                $mail = "";
                $website = "";
                $message = "";
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
        if($usespamprotection) {
            $mandatory = true;
            // Spamschutz-Aufgabe
            $calculation_data = getRandomCalculationData();
            $_SESSION['calculation_result'] = $calculation_data[1];
            $form .= "<tr><td colspan=\"2\">".$lang_contact->getLanguageValue("contactform_spamprotection_text_0")."</td></tr>"
                ."<tr><td style=\"padding-right:10px;\">".$calculation_data[0]."*</td>"
                ."<td><input type=\"text\" id=\"contact_calculation\" name=\"".$_SESSION['contactform_calculation']."\" value=\"\" /></td></tr>";
            
        }
        if($mandatory)
            $form .= "<tr><td style=\"padding-right:10px;\">&nbsp;</td><td>".$lang_contact->getLanguageValue("contactform_mandatory_fields_0")."</td></tr>";
        $form .= "<tr><td style=\"padding-right:10px;\">&nbsp;</td><td><input type=\"submit\" class=\"submit\" id=\"contact_submit\" name=\"submit\" value=\"".$lang_contact->getLanguageValue("contactform_submit_0")."\" /></td></tr>";
        $form .= "</table>"
        ."</form>";
        
        return $form;
    }


// ------------------------------------------------------------------------------
// Hilfsfunktion: Zufaellige Spamschutz-Rechenaufgabe und deren Ergebnis zurueckgeben
// ------------------------------------------------------------------------------
    function getRandomCalculationData() {
        global $contactformcalcs;
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
    }


?>