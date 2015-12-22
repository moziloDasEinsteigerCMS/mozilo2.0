<?php if(!defined('IS_CMS')) die();

class Language {

    var $LANG_CONF;
    // ------------------------------------------------------------------------------
    // Konstruktor
    // ------------------------------------------------------------------------------
    function __construct($lang_dir = false) {
        global $CMS_CONF;
        if(!$lang_dir) {
            $currentlanguage = $CMS_CONF->get("cmslanguage");
            // Standardsprache Deutsch verwenden, wenn konfigurierte Sprachdatei nicht vorhanden
            if (($currentlanguage == "") || (!file_exists(BASE_DIR_CMS.LANGUAGE_DIR_NAME."/language_".$currentlanguage.".txt"))) {
                $currentlanguage = "deDE";
            }
            $this->LANG_CONF = new Properties(BASE_DIR_CMS.LANGUAGE_DIR_NAME."/language_".$currentlanguage.".txt");
        } else {
            # gibts die Sprache nicht wird es mit der Defaultsprache versucht
            if(!file_exists($lang_dir) and true === file_exists(substr($lang_dir,0,-8)."deDE.txt"))
                $lang_dir = substr($lang_dir,0,-8)."deDE.txt";
            $this->LANG_CONF = new Properties($lang_dir);
        }
    }

    // ------------------------------------------------------------------------------
    // Sprachelement mit keinem, einem oder zwei Parametern aus Sprachdatei holen
    // ------------------------------------------------------------------------------
    function getLanguageValue($phrase, $param1 = "", $param2 = "") {
        $text = $this->LANG_CONF->get($phrase);
        $text = str_replace(array("{PARAM1}","{PARAM2}"), array($param1, $param2), $text);
##
if ($text === "") $text = "Textvar: ". $phrase." gibts nicht!";
##
         return $text;
    }

    // ------------------------------------------------------------------------------
    // Sprachelement htmlkonform mit keinem, einem oder zwei Parametern aus Sprachdatei holen
    // ------------------------------------------------------------------------------
    function getLanguageHtml($phrase, $param1 = "", $param2 = "") {
        $text = $this->LANG_CONF->get($phrase);
        $text = str_replace(array("{PARAM1}","{PARAM2}"), array($param1, $param2), $text);
        $text = html_entity_decode($text,ENT_QUOTES,CHARSET);
        $text = htmlentities($text, ENT_COMPAT, CHARSET);
##
if ($text === "") $text = "Textvar: ". $phrase." gibts nicht!";
##
         return $text;
    }
}
?>