<?php if(!defined('IS_CMS')) die();

class CONTACT extends Plugin {

    function getDefaultSettings($only_formcalcs = false) {
        $tmp = array(
            "formularmail" => "",
            "contactformwaittime" => "15",
            "contactformusespamprotection" => "true",
            "contactformcalcs" => "3 + 7 = 10<br />5 - 3 = 2<br />1 plus 1 = 2<br />17 minus 7 = 10<br />4 * 2 = 8<br />3x3 = 9<br />2 divided by 2 = 1<br />Abraham Lincols first Name = Abraham<br />James Bonds family name = Bond<br />bronze, silver, ... ? = gold",
            "titel_name" => "",
            "titel_name_show" => "true",
            "titel_name_mandatory" => "false",
            "titel_website" => "",
            "titel_website_show" => "true",
            "titel_website_mandatory" => "false",
            "titel_mail" => "",
            "titel_mail_show" => "true",
            "titel_mail_mandatory" => "false",
            "titel_message" => "",
            "titel_message_show" => "true",
            "titel_message_mandatory" => "false",
            "titel_privacy" => "",
            "titel_privacy_show" => "true",
            "titel_privacy_mandatory" => "false"
        );
        if($only_formcalcs)
            return $tmp["contactformcalcs"];
        return $tmp;
    }

    function getContent($value) {
        global $CMS_CONF;
        global $contactformcalcs;
        global $lang_contact;

        $dir = PLUGIN_DIR_REL."CONTACT/";
        $lang_contact = new Language($dir."sprachen/cms_language_".$CMS_CONF->get("cmslanguage").".txt");

        // existiert eine Mailadresse? Wenn nicht: Das Kontaktformular gar nicht anzeigen!
        if(strlen($this->settings->get("formularmail")) < 1) {
            return '<span class="deadlink">'.$lang_contact->getLanguageValue("tooltip_no_mail_error")."</span>";
        }

        if(strlen($this->settings->get("contactformcalcs")) < 5)
            $this->settings->set("contactformcalcs",$this->getDefaultSettings(true));

        require_once($dir."func_contact.php");

        $return = buildContactForm($this->settings);
        return $return;

    } // function getContent

    /***************************************************************
    * 
    * Gibt die Konfigurationsoptionen als Array zurück.
    * Ist keine Konfiguration nötig, ist dieses Array leer.
    * 
    ***************************************************************/
    function getConfig() {
        global $lang_contact_admin;

        $config = array();
        $config['formularmail']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_text_formularmail"),
            "maxlength" => "100",
#            "size" => "40",
            "regex" => "/^[\w-]+(\.[\w-]+)*@([0-9a-z][0-9a-z-]*[0-9a-z]\.)+([a-z]{2,4})$/i",
            "regex_error" => $lang_contact_admin->get("config_error_formularmail")
        );
        $config['contactformwaittime']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_text_contactformwaittime"),
            "maxlength" => "4",
            "size" => "3",
            "regex" => "/^[\d+]+$/",
            "regex_error" => getLanguageValue("check_digit")
        );
        $config['contactformusespamprotection'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_text_contactformusespamprotection")
        );
        $config['contactformcalcs'] = array(
            "type" => "textarea",
#            "cols" => "80",
            "rows" => "10",
            "description" => $lang_contact_admin->get("config_titel_spam_question"),
#            "template" => '<div style="float:left;margin-right:2.4em;">{contactformcalcs_description}</div>{contactformcalcs_textarea}<div class="mo-clear"></div>'
        );
        # name
        $config['titel_name']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_input_contact_name"),
            "maxlength" => "100",
#            "size" => "40"
        );
        $config['titel_name_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_show")
        );
        $config['titel_name_mandatory'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_mandatory")
        );
        # website
        $config['titel_website']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_input_contact_website"),
            "maxlength" => "100",
#            "size" => "40"
        );
        $config['titel_website_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_show")
        );
        $config['titel_website_mandatory'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_mandatory")
        );
        # mail
        $config['titel_mail']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_input_contact_mail"),
            "maxlength" => "100",
#            "size" => "40"
        );
        $config['titel_mail_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_show")
        );
        $config['titel_mail_mandatory'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_mandatory")
        );
        # message
        $config['titel_message']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_input_contact_textarea"),
            "maxlength" => "100",
        );
        $config['titel_message_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_show")
        );
        $config['titel_message_mandatory'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_mandatory")
        );

        $config['titel_privacy']  = array(
            "type" => "text",
            "description" => $lang_contact_admin->get("config_input_contact_privacy"),
            "maxlength" => "100",
        );
        $config['titel_privacy_show'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_show")
        );
        $config['titel_privacy_mandatory'] = array(
            "type" => "checkbox",
            "description" => $lang_contact_admin->get("config_titel_contact_mandatory")
        );
        $config['--template~~'] = ''
                    .'<div class="mo-in-li-l">{formularmail_description}</div>'
                    .'<div class="mo-in-li-r">{formularmail_text}</div>'
                .'</div></li>'
                .'<li class="mo-in-ul-li mo-inline ui-widget-content ui-corner-all ui-helper-clearfix"><div>'
                    .'<div class="mo-in-li-l">{contactformwaittime_description}</div>'
                    .'<div class="mo-in-li-r">{contactformwaittime_text}</div>'
                .'</div></li>'
                .'<li class="mo-in-ul-li mo-inline ui-widget-content ui-corner-all ui-helper-clearfix"><div>'
                    .'<div class="mo-in-li-l" style="width:94%;">{contactformusespamprotection_description}<br /><br /></div>'
                    .'<div class="mo-in-li-r" style="width:5%;">{contactformusespamprotection_checkbox}</div>'
                    .'<div class="mo-clear"></div>'
                    .'<div class="mo-in-li-l" style="width:30%;">{contactformcalcs_description}</div>'
                    .'<div class="mo-in-li-r" style="width:69%;">{contactformcalcs_textarea}</div>'
                .'</div></li>'
                .'<li class="mo-in-ul-li mo-inline ui-widget-content ui-corner-all ui-helper-clearfix"><div>'
                    .$lang_contact_admin->get("config_text_contact").'<br /><br />'
                    .'<table width="100%" cellspacing="0" border="0" cellpadding="0"><tr>'
                        .'<td width="5%">&nbsp;</td>'
                        .'<td class="mo-nowrap mo-bold">'.$lang_contact_admin->get("config_titel_contact_help").'</td>'
                        .'<td width="30%" class="mo-nowrap mo-bold">'.$lang_contact_admin->get("config_titel_contact_input").'</td>'
                        .'<td width="1%" class="mo-nowrap mo-bold" style="padding:0 2em;">'.$lang_contact_admin->get("config_titel_contact_show").'</td>'
                        .'<td width="1%" class="mo-nowrap mo-bold">'.$lang_contact_admin->get("config_titel_contact_mandatory").'</td>'
                    .'</tr><tr>'
                        .'<td>&nbsp;</td>'
                        .'<td class="mo-nowrap mo-padding-top">{titel_name_description}</td>'
                        .'<td class="mo-padding-top">{titel_name_text}</td>'
                        .'<td class="mo-align-center mo-padding-top">{titel_name_show_checkbox}</td>'
                        .'<td class="mo-align-center mo-padding-top">{titel_name_mandatory_checkbox}</td>'
                    .'</tr><tr>'
                        .'<td>&nbsp;</td>'
                        .'<td class="mo-nowrap mo-padding-top">{titel_website_description}</td>'
                        .'<td class="mo-padding-top">{titel_website_text}</td>'
                        .'<td class="mo-align-center mo-padding-top">{titel_website_show_checkbox}</td>'
                        .'<td class="mo-align-center mo-padding-top">{titel_website_mandatory_checkbox}</td>'
                    .'</tr><tr>'
                        .'<td>&nbsp;</td>'
                        .'<td class="mo-nowrap mo-padding-top">{titel_mail_description}</td>'
                        .'<td class="mo-padding-top">{titel_mail_text}</td>'
                        .'<td class="mo-align-center mo-padding-top">{titel_mail_show_checkbox}</td>'
                        .'<td class="mo-align-center mo-padding-top">{titel_mail_mandatory_checkbox}</td>'
                    .'</tr><tr>'
                        .'<td>&nbsp;</td>'
                        .'<td class="mo-nowrap mo-padding-top">{titel_message_description}</td>'
                        .'<td class="mo-padding-top">{titel_message_text}</td>'
                        .'<td class="mo-align-center mo-padding-top">{titel_message_show_checkbox}</td>'
                        .'<td class="mo-align-center mo-padding-top">{titel_message_mandatory_checkbox}</td>'
                    .'</tr><tr>'
                        .'<td>&nbsp;</td>'
                        .'<td class="mo-nowrap mo-padding-top">{titel_privacy_description}</td>'
                        .'<td class="mo-padding-top">{titel_privacy_text}</td>'
                        .'<td class="mo-align-center mo-padding-top">{titel_privacy_show_checkbox}</td>'
                        .'<td class="mo-align-center mo-padding-top">{titel_privacy_mandatory_checkbox}</td>'
                    .'</tr></table>';
        return $config;
    } // function getConfig    
    /***************************************************************
    * 
    * Gibt die Plugin-Infos als Array zurück - in dieser 
    * Reihenfolge:
    *   - Name und Version des Plugins
    *   - für moziloCMS-Version
    *   - Kurzbeschreibung
    *   - Name des Autors
    *   - Download-URL
    *   - Platzhalter für die Selectbox
    * 
    ***************************************************************/
    function getInfo() {
        global $ADMIN_CONF;
        global $lang_contact_admin;
        $dir = PLUGIN_DIR_REL."CONTACT/";
        $language = $ADMIN_CONF->get("language");
        $lang_contact_admin = new Properties($dir."sprachen/admin_language_".$language.".txt",false);

        $info = array(
            // Plugin-Name + Version
            "<b>CONTACT</b>",
            // moziloCMS-Version
            "2.0",
            // Kurzbeschreibung nur <span> und <br /> sind erlaubt
            $lang_contact_admin->get("config_help_contact"),
            // Name des Autors
            "mozilo",
            // Download-URL
            "",
            // Platzhalter für die Selectbox in der Editieransicht 
            // - ist das Array leer, erscheint das Plugin nicht in der Selectbox
            array(
                '{CONTACT}' => $lang_contact_admin->get("toolbar_platzhalter_CONTACT")
            )
        );
        return $info;
    } // function getInfo

}

?>