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
        global $ADMIN_CONF;
        $language = $ADMIN_CONF->get("language");

        $config['deDE'] = array();
        $config['deDE']['messagetext']  = array(
            "type" => "text",
            "description" => 'Eigener Text für "Letzte Änderung:"',
            "maxlength" => "100"
            );
        $config['deDE']['date']  = array(
            "type" => "text",
            "description" => "Eigenes Datumsformat",
            "maxlength" => "100"
            );
        $config['deDE']['showhiddenpagesinlastchanged'] = array(
            "type" => "checkbox",
            "description" => "Versteckte Inhaltsseiten mit einbeziehen"
            );

        // Nicht vergessen: Das gesamte Array zurückgeben
        if(isset($config[$language])) {
            return $config[$language];
        } else {
            return $config['deDE'];
        }

    } // function getConfig
    /***************************************************************
    * 
    * Gibt die Plugin-Infos als Array zurück. 
    * 
    ***************************************************************/
    function getInfo() {
        global $ADMIN_CONF;
        global $ADMIN_CONF;
        $adminlanguage = $ADMIN_CONF->get("language");

        $info['deDE'] = array(
            // Plugin-Name
            "<b>LastChange</b> \$Revision: 138 $",
            // CMS-Version
            "2.0",
            // Kurzbeschreibung
            'Zeigt die letzte Änderung an.<br />
            <br />
            <span style="font-weight:bold;">Nutzung:</span><br />
            {LASTCHANGE} gibt etwas aus wie: "Letzte Änderung: Willkommen (22.02.2010, 09:07:20)"<br />
            {LASTCHANGE|text} gibt etwas aus wie: "Letzte Änderung"<br />
            {LASTCHANGE|page} gibt etwas aus wie: "Willkommen"<br />
            {LASTCHANGE|pagelink} gibt etwas aus wie: "Willkommen" (mit Link auf die Inhaltsseite)<br />
            {LASTCHANGE|date} gibt etwas aus wie: "(22.02.2010, 09:07:20)"<br />
            <br />
            <span style="font-weight:bold;">Konfiguration:</span><br />
            Das Plugin bezieht den Text "Letzte Änderung" und das Datumsformat aus der CMS-Sprachdatei; man kann beides aber auch selbst angeben. Dabei orientiert sich das Datumsformat an der PHP-Funktion strftime().',
            // Name des Autors
            "mozilo",
            // Download-URL
            "",
            array(
                '{LASTCHANGE}' => 'Letzte Änderung mit Link und Datum',
                '{LASTCHANGE|text}' => 'Text "Letzte Änderung:"',
                '{LASTCHANGE|page}' => 'Name der zuletzt geänderten Inhaltsseite',
                '{LASTCHANGE|pagelink}' => 'Link auf die zuletzt geänderte Inhaltsseite',
                '{LASTCHANGE|date}' => 'Datum der letzten Änderung')
            );

        if(isset($info[$adminlanguage])) {
            return $info[$adminlanguage];
        } else {
            return $info['deDE'];
        }
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
        $lastchangedate = strftime($this->dateformat, $latestchanged['time']);
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

} // class LASTCHANGE

?>