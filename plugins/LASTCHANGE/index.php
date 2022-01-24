<?php if(!defined('IS_CMS')) die();

/***************************************************************
*
* Plugin für moziloCMS, das die letzte Änderungen zurückgibt
* 
***************************************************************/

class LASTCHANGE extends Plugin {

    /***************************************************************
    * 
    * Gibt den HTML-Code zurück, mit dem die Plugin-Variable ersetzt 
    * wird.
    * 
    ***************************************************************/
    var $include_pages = array();
    var $dateformat = NULL;

    function getContent($value) {
        global $language;

        $this->include_pages = array(EXT_PAGE);
        if($this->settings->get("showhiddenpagesinlastchanged") == "true")
            $this->include_pages = array(EXT_PAGE,EXT_HIDDEN);
        $messagetext = $language->getLanguageValue("message_lastchange_0");
        if($this->settings->get("messagetext"))
             $messagetext = $this->settings->get("messagetext");
        $this->dateformat = $language->getLanguageValue("_dateformat_0");
        if($this->settings->get("date"))
             $this->dateformat = $this->settings->get("date");
        if($value == "text") {
            return $messagetext;
        } elseif($value == "page") {
            $lastchangeinfo = $this->getLastChangedContentPageAndDateLAST();
            return $lastchangeinfo[0];
        } elseif($value == "pagelink") {
            $lastchangeinfo = $this->getLastChangedContentPageAndDateLAST();
            return $lastchangeinfo[1];
        } elseif($value == "date") {
            $lastchangeinfo = $this->getLastChangedContentPageAndDateLAST();
            return $lastchangeinfo[2];
        } else {
            $lastchangeinfo = $this->getLastChangedContentPageAndDateLAST();
            return $messagetext." ".$lastchangeinfo[1]." (".$lastchangeinfo[2].")";
        }

        return "";
    } // function getContent
    /***************************************************************
    * 
    * Gibt die Konfigurationsoptionen als Array zurück.
    * 
    ***************************************************************/
        function getConfig() {
        global $lang_lastchange_admin;

        $config = array();
        $config ['messagetext']  = array(
            "type" => "text",
            "description" => $lang_lastchange_admin->get("config_lastchange_messagetext"),
            "maxlength" => "100"
            );
        $config ['date']  = array(
            "type" => "text",
            "description" => $lang_lastchange_admin->get("config_lastchange_date"),
            "maxlength" => "100"
            );
        $config ['showhiddenpagesinlastchanged'] = array(
            "type" => "checkbox",
            "description" => $lang_lastchange_admin->get("config_lastchange_showhiddenpagesinlastchanged")
            );

        // Nicht vergessen: Das gesamte Array zurückgeben
        return $config;
    } // function getConfig
    /***************************************************************
    * 
    * Gibt die Plugin-Infos als Array zurück. 
    * 
    ***************************************************************/
    function getInfo() {
        global $ADMIN_CONF;
        global $lang_lastchange_admin;

        $dir = PLUGIN_DIR_REL."LASTCHANGE/";
        $language = $ADMIN_CONF->get("language");
        $lang_lastchange_admin = new Properties($dir."sprachen/admin_language_".$language.".txt",false);

        $info = array(
            // Plugin-Name
            "<b>".$lang_lastchange_admin->get("config_lastchange_plugin_name")."</b> \$Revision: 139 $",
            // CMS-Version
            "2.0",
            // Kurzbeschreibung
            $lang_lastchange_admin->get("config_lastchange_plugin_desc"),
            // Name des Autors
            "mozilo",
            // Download-URL
            "",
            array(
                '{LASTCHANGE}' => $lang_lastchange_admin->get("config_lastchange_plugin_lastchange"),
                '{LASTCHANGE|text}' => $lang_lastchange_admin->get("config_lastchange_plugin_text"),
                '{LASTCHANGE|page}' => $lang_lastchange_admin->get("config_lastchange_plugin_page"),
                '{LASTCHANGE|pagelink}' => $lang_lastchange_admin->get("config_lastchange_plugin_pagelink"),
                '{LASTCHANGE|date}' => $lang_lastchange_admin->get("config_lastchange_plugin_date")
                )
            );
            return $info;
    } // function getInfo
    // ------------------------------------------------------------------------------
    // Rueckgabe eines Arrays, bestehend aus:
    // - Name der zuletzt geaenderten Inhaltsseite
    // - kompletter Link auf diese Inhaltsseite  
    // - formatiertes Datum der letzten Aenderung
    // ------------------------------------------------------------------------------
    function getLastChangedContentPageAndDateLAST() {
        global $language;
        global $CatPage;

        $latestchanged = array("cat" => "catname", "page" => "pagename", "time" => 0);
        $currentdir = $CatPage->get_CatArray(false, false, $this->include_pages);
        foreach($currentdir as $cat) {
            $latestofdir = $this->getLastChangeOfCatLAST($cat);
            if ($latestofdir['time'] > $latestchanged['time']) {
                $latestchanged['cat'] = $cat;
                $latestchanged['page'] = $latestofdir['page'];
                $latestchanged['time'] = $latestofdir['time'];
            }
        }
        $lastchangedpage = $CatPage->get_HrefText($latestchanged['cat'],$latestchanged['page']);
        $url = $CatPage->get_Href($latestchanged['cat'],$latestchanged['page']);
        $titel = $language->getLanguageHTML("tooltip_link_page_2", $lastchangedpage, $CatPage->get_HrefText($latestchanged['cat'],false));
        $linktolastchangedpage = $CatPage->create_LinkTag($url,$lastchangedpage,false,$titel,false,"lastchangelink");
        $lastchangedate = date($this->dateformat, $latestchanged['time']);
        return array($lastchangedpage, $linktolastchangedpage,$lastchangedate);
    }
    // ------------------------------------------------------------------------------
    // Einlesen eines Kategorie-Verzeichnisses, Rueckgabe der zuletzt geaenderten Datei
    // ------------------------------------------------------------------------------
    function getLastChangeOfCatLAST($cat) {
        global $CatPage;

        $latestchanged = array("page" => "pagename", "time" => 0);
        $currentdir = $CatPage->get_PageArray($cat,$this->include_pages,true);
        foreach($currentdir as $page) {
            if ($CatPage->get_Time($cat,$page) > $latestchanged['time']) {
                $latestchanged['page'] = $page;
                $latestchanged['time'] = $CatPage->get_Time($cat,$page);
            }

        }
        return $latestchanged;
    }

 }// class LASTCHANGE

?>
