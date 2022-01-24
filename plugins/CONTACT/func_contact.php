<?php if(!defined('IS_CMS')) die();
// ------------------------------------------------------------------------------
// Gibt das Kontaktformular zurueck
// ------------------------------------------------------------------------------
    function buildContactForm($settings) {
    	  global $CatPage;
        global $lang_contact;
        global $CMS_CONF;
        global $specialchars;
        global $language;

        $WEBSITE_NAME = $specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"),false,true);
        if($WEBSITE_NAME == "")
            $WEBSITE_NAME = "Titel der Website";

        foreach(array("name","subject","website","mail","message","privacy") as $name) {
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
        if(($config_name[2] == "true") or ($config_subject[2] == "true") or ($config_mail[2] == "true") or ($config_website[2] == "true") or ($config_message[2] == "true") or ($config_privacy[2] == "true"))
            $mandatory = true;

        $errormessage = "";
        $form = "";

        if (isset($_SESSION['contactform_name'])) {
            $name       = getRequestValue($_SESSION['contactform_name'],'post', false);
            $subject    = getRequestValue($_SESSION['contactform_subject'],'post', false);
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
            $subject    = "";
            $mail       = "";
            $website    = "";
            $message    = "";
            $calcresult = "";
            $privacy    = "";
        }
        // Das Formular wurde abgesendet
        if (getRequestValue('submit','post', false) <> "") { 
        
            if (empty($_POST['url'])) {
              $hp = "";
            } else {
              $hp = $_POST['url'];
            }

            $hpmessage = "Nice try but we don't like Spam!";
        
            //hp prüfen
	          if ( ! empty( $hp ) ){
		          return $hpmessage;
	          }

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
                else if (($config_subject[2] == "true") && ($subject == "")) {
                    $errormessage = $lang_contact->getLanguageValue("contactform_fieldnotset")." ".$config_subject[0];
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
                $form .= "<div id=\"contact_errormessage\">".$errormessage."</div>";
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
                if ($config_subject[1] == "true") {
                    $mailcontent .= $config_subject[0].":\t".$subject."\r\n";
                }
                if ($config_message[1] == "true") {
                    $mailcontent .= "\r\n".$config_message[0].":\r\n".$message."\r\n";
                }
                if ($config_privacy[1] == "true") {
                    # □ &#x25A1; ▣ &#x25A3;
                    $checket = "□";
                    if(!empty($privacy))
                        $checket = "▣";
                    $mailcontent .= "\r\n".$checket." ".$lang_contact->getLanguageValue("contactform_privacy")." ".$lang_contact->getLanguageValue("contactform_privacy1")." ".$lang_contact->getLanguageValue("contactform_privacy2")."\r\n";
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
                $form .= "<div id=\"contact_successmessage\">".$lang_contact->getLanguageValue("contactform_confirmation")."</div>";
                
                // Felder leeren
                $name = "";
                $subject = "";
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
        ."<input type=\"hidden\" name=\"page\" value=\"".$CatPage->get_AsKeyName(PAGE_REQUEST)."\" />";
        if ($config_name[1] == "true") {
            // Bezeichner aus formular.conf nutzen, wenn gesetzt
            if ($config_name[2] == "true") {
                $name_mandatory = "*";
            }
             else { 
					$name_mandatory = "";            
            }
            $form .= "<div class=\"form-group\">";
            $form .= "<label class=\"hide-mobile\">$name_mandatory $config_name[0]</label>";
            $form .= "<input type=\"text\" id=\"contact_name\" name=\"".$_SESSION['contactform_name']."\" placeholder=\"$name_mandatory $config_name[0] \">";
            $form .= "</div>";
        }
      if ($config_mail[1] == "true") {
            // Bezeichner aus formular.conf nutzen, wenn gesetzt
            if ($config_mail[2] == "true") {
                $mail_mandatory = "*";
            } else { 
					$mail_mandatory = "";            
            }
            $form .= "<div class=\"form-group\">";
            $form .= "<label class=\"hide-mobile\">$mail_mandatory $config_mail[0]</label>";
            $form .= "<input type=\"email\" id=\"contact_mail\" name=\"".$_SESSION['contactform_mail']."\" placeholder=\"$mail_mandatory $config_mail[0]\">";
            $form .= "</div>";
        }
        if ($config_website[1] == "true") {
            // Bezeichner aus formular.conf nutzen, wenn gesetzt
            if ($config_website[2] == "true") {
                $website_mandatory = "*";
            } else { 
					$website_mandatory = "";            
            }
            $form .= "<div class=\"form-group\">";
            $form .= "<label class=\"hide-mobile\">$website_mandatory $config_website[0]</label>";
            $form .= "<input type=\"url\" id=\"contact_website\" name=\"".$_SESSION['contactform_website']."\" placeholder=\"$website_mandatory $config_website[0]\">";
            $form .= "</div>";
        }
        //hp schutz
            $form .= "<div class=\"form-group hp\">";
            $form .= "<label class=\"hide-mobile\">*URL</label>";
        		$form .= "<input name=\"url\" type=\"text\" id=\"url\" placeholder=\"Enter Your Website URL here\">";
            $form .= "</div>";        
        //hp Ende
          if ($config_subject[1] == "true") {
            // Bezeichner aus formular.conf nutzen, wenn gesetzt
            if ($config_subject[2] == "true") {
                $subject_mandatory = "*";
            } else { 
					$subject_mandatory = "";            
            }
            $form .= "<div class=\"form-group\">";
            $form .= "<label class=\"hide-mobile\">$subject_mandatory $config_subject[0]</label>";
            $form .= "<input type=\"text\" id=\"contact_subject\" name=\"".$_SESSION['contactform_subject']."\" placeholder=\"$subject_mandatory $config_subject[0]\">";
            $form .= "</div>";
        }
        if ($config_message[1] == "true") {
            // Bezeichner aus formular.conf nutzen, wenn gesetzt
            if ($config_message[2] == "true") {
                $message_mandatory = "*";
            } else { 
					$message_mandatory = "";            
            }
            $form .= "<div class=\"form-group\">";
            $form .= "<label class=\"hide-mobile\">$message_mandatory $config_message[0]</label>";
            $form .= "<textarea id=\"contact_message\" name=\"".$_SESSION['contactform_message']."\" placeholder=\"$message_mandatory $config_message[0]\" >".$message."</textarea>";
            $form .= "</div>";
        }
        if($settings->get("contactformusespamprotection") == "true") {
            $mandatory = true;
            // Spamschutz-Aufgabe
            $calculation_data = getRandomCalculationData($settings);
            $_SESSION['calculation_result'] = $calculation_data[1];
            $form .= "<label><span>* ".$lang_contact->getLanguageValue("contactform_spamprotection_text")."</span>"
                ."<span>&nbsp;".$calculation_data[0]."</span></label>"
                ."<input type=\"text\" id=\"contact_calculation\" name=\"".$_SESSION['contactform_calculation']."\">";            
        }
        if ($config_privacy[1] == "true") {
        
        	$cat = $settings->get("category");
        	$page = $settings->get("data_protection_page");        
          
			    $linkprivacy = "index.php?cat=".$cat."&amp;page=".$page."";		
			    if ($CMS_CONF->get("modrewrite") == "true") {
				    $linkprivacy = URL_BASE. $cat."/".$page.".html";          
			    }
			    if (!$CatPage->exists_CatPage($cat,$page)) {
            $category_text = $specialchars->rebuildSpecialChars($cat,true,true);
            $page_text = $specialchars->rebuildSpecialChars($page,true,true);
            $deadlink = $language->getLanguageValue("tooltip_link_page_error_2", $page_text, $category_text);
            
            //
            // Debug: 2021-12-26
            //
            // Sind Textinhalte im Plugin CONTACT für die Datenschutz Seite vorhanden?
            // Wenn nein, 
            // dann nicht anzeigen, auch keine Fehlertexte, einfach weg lassen.
            //
            $test_empty_strings_contact_privacy = false;         
            if (empty($cat)) {
              $test_empty_strings_contact_privacy = true;
            }
            if (empty($page)) {
              $test_empty_strings_contact_privacy = true;
            }            
            
            if ($test_empty_strings_contact_privacy == false) {
              $form .= "<label><input type=\"checkbox\" id=\"contact_privacy\" name=\"".$_SESSION['contactform_privacy']."\" value=\"".$_SESSION['contactform_privacy']."\" /><span>".$lang_contact->getLanguageValue("contactform_privacy")." <span class=\"deadlink\">".$deadlink."</span> ".$lang_contact->getLanguageValue("contactform_privacy2")."</span>";            
            }
       
          }	else {
			      $form .= "<label><input type=\"checkbox\" id=\"contact_privacy\" name=\"".$_SESSION['contactform_privacy']."\" value=\"".$_SESSION['contactform_privacy']."\" />";
					  if ($config_privacy[2] == "true") {
              $form .= "*&nbsp;";
            }
			      $form .= "<span>".$lang_contact->getLanguageValue("contactform_privacy")." <a href=\"". $linkprivacy . "\">".$lang_contact->getLanguageValue("contactform_privacy1")."</a> ".$lang_contact->getLanguageValue("contactform_privacy2")."</span>";
			    }
          $form .= "</label>";
        }
        
        if ($mandatory)
          $form .= "<span>".$lang_contact->getLanguageValue("contactform_mandatory_fields")."</span>";
            
        $form .= "<input type=\"submit\" class=\"submit\" id=\"contact_submit\" name=\"submit\" value=\"".$lang_contact->getLanguageValue("contactform_submit")."\" />";
        $form .= "</form>";
        
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
        $_SESSION['contactform_subject'] = time()-rand(20, 30);
        $_SESSION['contactform_mail'] = time()-rand(10, 20);
        $_SESSION['contactform_website'] = time()-rand(0, 10);
        $_SESSION['contactform_message'] = time()-rand(40, 50);
        $_SESSION['contactform_calculation'] = time()-rand(50, 60);
        $_SESSION['contactform_privacy'] = time()-rand(60, 70);
    }
?>
