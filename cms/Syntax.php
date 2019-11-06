<?php if(!defined('IS_CMS')) die();

/*
*
* $Revision: 922 $
* $LastChangedDate: 2011-07-12 14:55:50 +0200 (Di, 12. Jul 2011) $
* $Author: stefanbe $
*
*/

class Syntax {
    
    var $LANG;
    var $LINK_REGEX;
    var $content;
    var $is_preparePageContent = false;
#    var $content;
// ------------------------------------------------------------------------------    
// Konstruktor
// ------------------------------------------------------------------------------
    function __construct(){
        global $CMS_CONF;
        global $USER_SYNTAX;
        global $activ_plugins;
        global $deactiv_plugins;

        // Regulärer Audruck zur überprüfung von Links
        // überprüfung auf Validität >> protokoll :// (username:password@) [(sub.)server.tld|ip-adresse] (:port) (subdirs|files)
                    // protokoll                (https?|t?ftps?|gopher|telnets?|mms|imaps?|irc|pop3s?|rdp|smb|smtps?|sql|ssh):\/\/
                    // username:password@       (\w)+\:(\w)+\@
                    // (sub.)server.tld         ((\w)+\.)?(\w)+\.[a-zA-Z]{2,4}
                    // ip-adresse (ipv4)        ([\d]{1,3}\.){3}[\d]{1,3}
                    // port                     \:[\d]{1,5}
                    // subdirs|files            (\w)+
        $this->LINK_REGEX   = "/^(https?|t?ftps?|gopher|telnets?|mms|imaps?|irc|pop3s?|rdp|smb|smtps?|sql|ssh|svn)\:\/\/((\w)+\:(\w)+\@)?[((\w)+\.)?(\w)+\.[a-zA-Z]{2,4}|([\d]{1,3}\.){3}[\d]{1,3}](\:[\d]{1,5})?((\w)+)?$/";

        if(!isset($GLOBALS['syntax_anchor_counter']))
            $GLOBALS['syntax_anchor_counter'] = 1;
        if(!isset($GLOBALS['syntax_anchor_ueber']))
            $GLOBALS['syntax_anchor_ueber'] = array();
        if(!isset($GLOBALS['syntax_anchor_absatz']))
            $GLOBALS['syntax_anchor_absatz'] = array();

        $syntax_elemente = get_class_methods($this);
        $syntax_array = array();
        foreach($syntax_elemente as $element) {
            if(substr($element,0,strlen("syntax_")) == "syntax_")
                $syntax_array[] = substr($element,strlen("syntax_"));
        }

        $this->syntax_user = $USER_SYNTAX->toArray();
        foreach($this->syntax_user as $user => $inhalt) {
            $syntax_array[] = $user;
        }
        # Damit zuerst z.B. nach links und dann nach link gesucht wird sonst wird links als link gefunden
        rsort($syntax_array);

        $syntax_such = "/\[(".implode("=|",$syntax_array)."=|".implode("|",$syntax_array).")([^\[\]\{\}]*)\|([^\[\]\{\}]*)\]/Um";
        $syntax_such_rest = "/\[(".implode("=|",$syntax_array)."=|".implode("|",$syntax_array).")([^\|]*)\|(.*)\]/Ums";

        # Achtung es muss ---- für horizontale line angehängt werden
        $syntax_such_ohne = "/\[(".implode("|",$syntax_array)."|----)\]/Um";

        $this->activ_plugins = $activ_plugins;
        $this->deactiv_plugins = $deactiv_plugins;
        $plugin = array_merge($this->activ_plugins, $this->deactiv_plugins);
        # Das gleiche hier mit Plugins siehe rsort weiter oben
        rsort($plugin);

        $this->placeholder = array();
        foreach(makePlatzhalter(true) as $value) {
            $tmp = substr($value,1,-1);
            # Wens ein Plugin gibt was so heist wie der Platzhalter benutzen wir das Plugin
            if(!in_array($tmp,$this->activ_plugins))
                $this->placeholder[] = $tmp;
            # damit der Platzhalter wieder funktioniert müssen wir in aus $this->deactiv_plugins löschen
            if(in_array($tmp,$this->deactiv_plugins)) {
                if(false !== ($key = array_search($tmp, $this->deactiv_plugins)));
                    unset($this->deactiv_plugins[$key]);
            }
        }
        rsort($this->placeholder);
        unset($tmp_place);

        $plugin_such = "/\{(".implode("|",$plugin).")\|([^\[\]\{\}]*)\}/Um";
        $plugin_such_rest = "/\{(".implode("|",$plugin).")\|(.*)\}/Ums";
        $plugin_such_ohne = "/\{(".implode("|",$plugin).")\}/Um";

        $this->PLACE_SEARCH = "/\{(".implode("|",$this->placeholder).")\}/Um";
        $this->SYNTAX_SEARCH = $syntax_such;
        $this->SYNTAX_SEARCH_REST = $syntax_such_rest;
        $this->SYNTAX_SEARCH_OHNE = $syntax_such_ohne;
        $this->PLUGIN_SEARCH = $plugin_such;
        $this->PLUGIN_SEARCH_REST = $plugin_such_rest;
        $this->PLUGIN_SEARCH_OHNE = $plugin_such_ohne;
        $this->script_search = array();
        $this->script_replace = array();
        $this->jquery_search = array();
        $this->jquery_replace = array();
        $this->pluginself['placeholder'] = array();
        $this->pluginself['replace'] = array();
    }

    /*
    Der array aufbau der zurückgegeben wird muss immer so sein
    0 = match
    1 = plugin, syntax oder platzhalter
    2 = "description" für Syntax und UserSyntax
    3 = "value" für Plugin, Syntax und UserSyntax
    */
    function clean_syntax_plugins_array($array,$array2 = false,$fill3 = NULL) {
        # test array erstellen wo alle dopelten einträge eintfernt werden
        $test = array_unique($array[0]);
        if(count($array[0]) > count($test)) {
            foreach($array[0] as $pos => $tmp) {
                # alle einträge die nicht im test array sind weg machen das sind doppelte
                if(!isset($test[$pos])) {
                    unset($array[0][$pos],$array[1][$pos],$array[2][$pos],$array[3][$pos]);
                    continue;
                }
                # wenn array 2 oder 3 nicht existieren dann erstellen
                if(!isset($array[2][$pos]))
                    $array[2][$pos] = NULL;
                if(!isset($array[3][$pos]))
                    $array[3][$pos] = $fill3;
            }
        }
        # wenn array 2 oder 3 nicht existieren dann erstellen
        # array_unique hat nichts gefunden also haben wir noch eine vortlaufenden key
        # oder das array ist einfach lehr
        if(!isset($array[2])) {
            $array[2] = array();
            if(count($array[0]) > 0)
                $array[2] = array_fill(0, count($array[0]), NULL);
        }
        if(!isset($array[3])) {
            $array[3] = array();
            if(count($array[0]) > 0)
                $array[3] = array_fill(0, count($array[0]), $fill3);
        }
        # An array2 array anhängen
        if(is_array($array2)) {
            $array[0] = array_merge($array2[0], $array[0]);
            $array[1] = array_merge($array2[1], $array[1]);
            $array[2] = array_merge($array2[2], $array[2]);
            $array[3] = array_merge($array2[3], $array[3]);
        }
        return $array;
    }

    function match_syntax_plugins() {

        # alle <script und <style sachen raus wegen den {} und [] die da meistens drin sind
        $this->find_script_style();

        preg_match_all($this->PLACE_SEARCH, $this->content, $place);
        $matches = $this->clean_syntax_plugins_array($place);

        if(USE_CMS_SYNTAX) {
            preg_match_all($this->SYNTAX_SEARCH, $this->content, $syntax);
            $matches = $this->clean_syntax_plugins_array($syntax,$matches);
            preg_match_all($this->SYNTAX_SEARCH_OHNE, $this->content, $syntax_ohne);
            $matches = $this->clean_syntax_plugins_array($syntax_ohne,$matches);
        }
        preg_match_all($this->PLUGIN_SEARCH, $this->content, $plugins);
        # hier stimt der array aufbau nicht deshalb passen wir in an
        $plugins[3] = $plugins[2];
        $matches = $this->clean_syntax_plugins_array($plugins,$matches);
        preg_match_all($this->PLUGIN_SEARCH_OHNE, $this->content, $plugins_ohne);
        # Achtung hier false mit geben damit die Plugin $value ein false hat wenn im
        # Pluginplatzhalter kein | ist
        $matches = $this->clean_syntax_plugins_array($plugins_ohne,$matches,false);

        # wenn was gefunden wurde
        if(isset($matches[0]) and count($matches[0]) > 0) {
            return $matches;
        }

        # mit den "SYNTAX_SEARCH, PLUGIN_SEARCH, PLUGIN_SEARCH_OHNE"
        # such parametern wurde nichts gefunden
        # dann versuchen wir es noch mit den "SYNTAX_SEARCH_REST, PLUGIN_SEARCH_REST"
        if(USE_CMS_SYNTAX) {
            preg_match_all($this->SYNTAX_SEARCH_REST, $this->content, $syntax_rest);
            $matches = $this->clean_syntax_plugins_array($syntax_rest,$matches);
        }
        preg_match_all($this->PLUGIN_SEARCH_REST, $this->content, $plugins_rest);
        # hier stimt der array aufbau nicht deshalb passen wir in an
        $plugins_rest[3] = $plugins_rest[2];
        $matches = $this->clean_syntax_plugins_array($plugins_rest,$matches);
        # wenn was gefunden wurde

        if(isset($matches[0]) and count($matches[0]) > 0) {
            return $matches;
        }
        return false;
    }
// ------------------------------------------------------------------------------
// Umsetzung der übergebenen CMS-Syntax in HTML, Rückgabe als String
// ------------------------------------------------------------------------------
    function convertContent($content, $firstrecursion) {
        $this->content = $content;
#!!!!!!!! $firstrecursion ist nur true wenn auch USE_CMS_SYNTAX true ist
        if($firstrecursion) {
            $this->content = $this->preparePageContent($this->content);
        }

        $matches = $this->match_syntax_plugins();
        $not_exit = 0;
        $not_exit_max = 20;

        while(isset($matches[0]) and count($matches[0]) > 0) {
            if($not_exit >= $not_exit_max)
                break;
            foreach($matches[1] as $pos => $function) {
                # das gefundene element gibts nicht mehr also nächstes
                if(!strstr($this->content,$matches[0][$pos])) {
                    continue;
                }
                # bei den Benutzer und Syntaxelementen kann es vorkommen das z.B. [mailTest als [mail
                # gefunden wird. Das wird hier geprüft
#!!!!!! das solte mit einer beseren preg_match behoben werden siehe oben
                if($matches[0][$pos][0] == "[") {
                    # erstmal auf -1 für Syntaxelemente die keinen | haben
                    $search_string_pos = -1;
                    if(strpos($matches[0][$pos],"|") !== false) {
                        $search_string_pos = strpos($matches[0][$pos],"|") - 1;
                    }
                    if(strpos($matches[0][$pos],"=") !== false and strpos($matches[0][$pos],"=") < $search_string_pos) {
                        $search_string_pos = strpos($matches[0][$pos],"=");
                    }
                    $syntax_test = substr($matches[0][$pos],1,$search_string_pos);
                    # wenn $function nicht mit $syntax_test übereinstimt gibts die $function nicht
                    # also wird $syntax_test zur $function
                    if($function != $syntax_test) {
                        $function = $syntax_test;
                    }
                }
                # Horizontale Linen ersetzen, es gibt keine function "syntax_----" aber eine syntax_hr
                if($function == "----") {
                    $function = "hr";
                }
                # weil nach syntax= gesucht wird enthält das array auch syntax=
                if(substr($function,-1) == "=")
                    $function = substr($function,0,-1);
                $replace = NULL;
                # Plugin
                if(in_array($function,$this->activ_plugins) or in_array($function,$this->deactiv_plugins)) {
                    $replace = $this->plugin_replace($function,$matches[3][$pos]);
                # User Syntax
                } elseif(USE_CMS_SYNTAX and isset($this->syntax_user[$function])) {
                    $replace = $this->syntax_user($matches[2][$pos],$matches[3][$pos],$function);
                # Syntax
                } elseif(USE_CMS_SYNTAX and method_exists($this, "syntax_".$function)) {
                    $tmp_syntax = "syntax_".$function;
                    $replace = $this->$tmp_syntax($matches[2][$pos],$matches[3][$pos]);
                # mozilo Platzhalter
                } elseif(in_array($function,$this->placeholder)) {
                    $replace = $this->placeholder_replace($function,$matches[0][$pos]);
                # unbekant
                } else {
                    $special_search = array('[',']','{','}','|');
                    $special_replace = array('&#091;','&#093;','&#123;','&#125;','&#124;');
                    $match = str_replace($special_search,$special_replace,$matches[0][$pos]);
                    $replace = '<span style="color:red;font-weight:bold;text-decoration:line-through;">'.$match.'</span>';
                }
                $this->content = str_replace($matches[0][$pos],$replace,$this->content);
                # wenn ein Plugin an sich was übergeben hat
                $this->replacePluginSelfPlaceholderData();
            }
            $not_exit++;
            $matches = $this->match_syntax_plugins();
        }
        if($not_exit >= $not_exit_max)
            echo "ACHTUNG NOT EXIT PRÜFEN<br>\n";

        # script und style sachen wieder einsetzen
        $this->find_script_style(false);

        # das nur machen wenn die function preparePageContent() benutzt wurde
        if($this->is_preparePageContent) {
            // Zeilenwechsel nach Blockelementen entfernen
            // Tag-Beginn                                       <
            // optional: Slash bei schließenden Tags            (\/?)
            // Blockelemente                                    (address|blockquote|div|dl|fieldset|form|h[123456]|hr|noframes|noscript|ol|p|pre|table|ul|center|dir|isindex|menu)
            // optional: sonstige Zeichen (z.B. Attribute)      ([^>]*)
            // Tag-Ende                                         >
            // optional: Zeilenwechsel                          (\r\n|\r|\n)?
            // <br /> mit oder ohne Slash (das, was raus muß!)  <br \/? >
            $this->content = preg_replace('/<(\/?)(address|blockquote|div|dl|fieldset|form|h[123456]|hr|noframes|noscript|ol|p|pre|table|th|tr|td|ul|center|dir|isindex|menu)([^>]*)>(\r\n|\r|\n)?\-html_br\~/', "<$1$2$3>$4",$this->content); /* <br\s?\/?> \-html_br\~ */

            global $specialchars;
            $this->content = $specialchars->decodeProtectedChr($this->content);
        }

        # Syntax html zeichen nach html wandeln
        $search = array("-html_br~","-html_nbsp~","-html_lt~","-html_gt~","-html_amp~");
        $replace = array("<br />","&nbsp;","&lt;","&gt;","&amp;");
        $this->content = str_replace($search,$replace,$this->content);

        if(USE_CMS_SYNTAX) {
            // direkt aufeinanderfolgende Listen zusammenführen
            $this->content = preg_replace('/<\/ul>(\r\n|\r|\n)?<ul class="unorderedlist">/', '', $this->content);
            // direkt aufeinanderfolgende numerierte Listen zusammenführen
            $this->content = preg_replace('/<\/ol>(\r\n|\r|\n)?<ol class="orderedlist">/', '', $this->content);

            $this->replaceAnchorAbsatz();

            # Da {TABLEOFCONTENTS} erst am schluss komplet ist ersetzen wir es auch erst am schluss
            if(strstr($this->content,'<!-- TABLEOFCONTENTS REPLACE-->'))
                $this->content = str_replace('<!-- TABLEOFCONTENTS REPLACE-->',$this->getToC(),$this->content);
        }

        if(strstr($this->content,'<!-- WEBSITE_TITLE REPLACE-->')) {
            $this->content = str_replace('<!-- WEBSITE_TITLE REPLACE-->',$this->getWebsiteTitle(),$this->content);
        }
        return $this->content;
    }


    # wenn ein Plugin einen Platzhalter erstelt um in später mit eigenen
    # inhalt ersetzen möchte. Siehe z.B. das SideBar Plugin
    function pluginSelfPlaceholderData($placeholder,$replace) {
        $this->pluginself['placeholder'][] = $placeholder;
        $this->pluginself['replace'][] = $replace;
    }

    # ersetze vom Plugin selbst erzeugte Platzhalter mit dem Inhalt vom Plugin
    function replacePluginSelfPlaceholderData() {
        # einlesen des arrays mit dem von Plugins übergeben inhalt
        foreach($this->pluginself['placeholder'] as $pos => $placeholder) {
            # nur ersetzen wenn im content auch der zu ersetzende platzhalter enthalten ist
            # ist nötig fals das Plugin den ersetzende platzhalter noch nicht gesetzt hat
            if(strstr($this->content,$placeholder)) {
                $this->content = str_replace($placeholder,$this->pluginself['replace'][$pos],$this->content);
                # der platzhalter wurde ersetzt aus dem array entfernen
                unset($this->pluginself['placeholder'][$pos],$this->pluginself['replace'][$pos]);
            }
        }
    }

    function find_script_style($find = true) {
        if($find) {
            # script und style einträge suchen
            preg_match_all("/\<script(.*)\<\/script>/Umsi", $this->content, $script);
            preg_match_all("/\<style(.*)\<\/style>/Umsi", $this->content, $style);
            $script_style = array_merge($script[0], $style[0]);

            # aufräumen und $this->script_???? erzeugen
            foreach($script_style as $script_match) {
                # wenn lehr nächsten nehmen
                if(empty($script_match))
                    continue;
                $dummy = '<!-- dummy script style '.count($this->script_search).' -->';
                # bei den styles sind gleiche einträge unötig
                if(substr($script_match,0,6) == "<style" and in_array($script_match,$this->script_replace)) {
                    # deshalb dummy lehr
                    $dummy = NULL;
                }
                # script und style ersetzen mit dummy
                $this->content = str_replace($script_match,$dummy,$this->content);
                # wenn dummy nicht lehr arrays füllen
                if(!empty($dummy)) {
                    # in script style können Platzhalter enthalten sein die wir ersetzen müssen
                    preg_match_all($this->PLACE_SEARCH, $script_match, $matches_place);
                    if(isset($matches_place[0][0]) and isset($matches_place[1][0])) {
                        foreach($matches_place[1] as $pos => $function) {
                            $replace = $this->placeholder_replace($function,$matches_place[0][$pos]);
                            $script_match = str_replace($matches_place[0][$pos],$replace,$script_match);
                        }
                    }
                    $this->script_search[] = $dummy;
                    $this->script_replace[] = $script_match;
                }
            }
        } else {
            # jquery sachen einsetzen
            $this->content = str_replace(array("<head>","<HEAD>"),"<head>\n".implode("",$this->jquery_replace),$this->content);
            # script und style sachen wieder einsetzen
            $this->content = str_replace($this->script_search,$this->script_replace,$this->content);
        }
    }

    function insert_in_head($data) {
        if(!in_array($data,$this->script_replace)) {
            $dummy = '<!-- dummy script style '.count($this->script_search).' -->';
            $this->content = str_replace(array("</head>","</HEAD>"),$dummy."\n</head>",$this->content);
            $this->script_search[] = $dummy;
            $this->script_replace[] = $data;
        }
    }

    function insert_in_tail($data) {
        if(!in_array($data,$this->script_replace)) {
            $dummy = '<!-- dummy script_tail '.count($this->script_search).' -->';
            $this->content = str_replace(array("</body>","</BODY>"),$dummy."\n</body>",$this->content);
            $this->script_search[] = $dummy;
            $this->script_replace[] = $data;
        }
    }

    function insert_jquery_in_head($jquery_type) {
        $jquery =array();
        $jquery['jquery'] = '<script type="text/javascript" src="'.URL_BASE.CMS_DIR_NAME.'/jquery/jquery-'.JQUERY.'.min.js"></script>';
        $jquery['jquery-ui'] = '<script type="text/javascript" src="'.URL_BASE.CMS_DIR_NAME.'/jquery/jquery-ui-'.JQUERY_UI.'.custom.min.js"></script>';
        if(isset($jquery[$jquery_type]) and !in_array($jquery_type,$this->jquery_search)) {
            $this->jquery_search[] = $jquery_type;
            $this->jquery_replace[] = $jquery[$jquery_type];
        }
    }

    function syntax_link($desciption,$value) {
        // externer Link
        global $CMS_CONF;
        global $language;
        global $specialchars;
        $link = $value;
        # wenn kein http:// davor steht setzen wirs einfach davor wegen LINK_REGEX
        if(!stristr($value,"://")) {
            $link = "http://".$value;
        }
        // überprüfung auf korrekten Link
        if(preg_match($this->LINK_REGEX, $link)) {
            if(empty($desciption)) {
                $desciption = $value;
            }
            if(strip_tags($desciption) == $desciption)
                $desciption = $specialchars->rebuildSpecialChars($desciption, true, true);
            # erstmal alle HTML Zeichen wandeln
            $link = $specialchars->getHtmlEntityDecode($link);
            # alle url encodete Zeichen wandeln
            $link = $specialchars->rebuildSpecialChars($link,false,false);
            # alles url encodeten
            $link = $specialchars->replaceSpecialChars($link,false);
            # alle :,?,&,;,= zurück wandeln
            $link = str_replace(array('%3A','%3F','%26','%3B','%3D','%40','%2C','%23','%7E'),array(':','?','&amp;',';','=','@',',','#','~'),$link);
            // Externe Links in neuem Fenster öffnen?
            $target = "";
            global $CMS_CONF;
            if ($CMS_CONF->get("targetblank_link") == "true") {
                $target = ' target="_blank"';
            }
            return '<a class="link" href="'.$link.'"'.$this->getTitleAttribute($language->getLanguageHtml("tooltip_link_extern_1", $value)).$target.'>'.$desciption.'</a>';
        } else {
            if(empty($desciption))
                $desciption = $value;
            return $this->createDeadlink($desciption, $language->getLanguageValue("tooltip_link_extern_error_1", $value));
        }
    }

    function syntax_mail($desciption,$value) {
        // Mail-Link mit eigenem Text
        global $language;
        global $specialchars;
        // überprüfung auf korrekten Link
        if (preg_match(MAIL_REGEX, $value)) {
            global $Punycode;
            $value = $specialchars->rebuildSpecialChars($Punycode->decode($value),false,false);
            $mailto = $Punycode->encode($value);
            if(empty($desciption))
                $desciption = $value;
            if(strip_tags($desciption) == $desciption)
                $desciption = $specialchars->obfuscateAdress($specialchars->rebuildSpecialChars($desciption,false,false), 3);
            $mailto = $specialchars->obfuscateAdress('mailto:'.$mailto, 3);
            return '<a class="mail" href="'.$mailto.'"'.$this->getTitleAttribute($specialchars->obfuscateAdress($language->getLanguageValue("tooltip_link_mail_1",$value), 3)).'>'.$desciption.'</a>';
        } else {
            if(empty($desciption))
                $desciption = $value;
            return $this->createDeadlink($desciption, $language->getLanguageValue("tooltip_link_mail_error_1", $value));
        }
    }

    function syntax_kategorie($desciption,$value) {
        // Kategorie-Link (überprüfen, ob Kategorie existiert)
        // Kategorie-Link mit eigenem Text
        global $language;
        global $CatPage;
        global $specialchars;

        list($cat,$page) = $CatPage->split_CatPage_fromSyntax($value);
        # testen ob es keine html tag enthält
        if(strip_tags($desciption) == $desciption)
            $desciption = $specialchars->rebuildSpecialChars($desciption, true, true);
        $link_text = $desciption;
        if(empty($desciption)) {
            $link_text = $CatPage->get_HrefText($cat,false);
        }
        if($CatPage->exists_CatPage($cat,false)) {
            return $CatPage->create_LinkTag($CatPage->get_Href($cat,false)
                    ,$link_text
                    ,"category"
                    ,$language->getLanguageHtml("tooltip_link_category_1", $CatPage->get_HrefText($cat,false))
                    );
        } else {
            return $this->createDeadlink($value, $language->getLanguageValue("tooltip_link_category_error_1", $value));
        }
    }

    function syntax_seite($desciption,$value) {
        // Link auf Inhaltsseite in aktueller oder anderer Kategorie (überprüfen, ob Inhaltsseite existiert)
        // Link auf Inhaltsseite in aktueller oder anderer Kategorie mit beliebigem Text
        global $specialchars;
        global $language;
        global $CatPage;

        list($cat,$page) = $CatPage->split_CatPage_fromSyntax($value);

        if(!$CatPage->exists_CatPage($cat,$page)) {
            $cat_text = $specialchars->rebuildSpecialChars($cat,true,true);
            $page_text = $specialchars->rebuildSpecialChars($page,true,true);
            return $this->createDeadlink($page_text, $language->getLanguageValue("tooltip_link_page_error_2", $page_text, $cat_text));
        }
        # testen ob es keine html tag enthält
        if(strip_tags($desciption) == $desciption)
            $desciption = $specialchars->rebuildSpecialChars($desciption, true, true);
        $link_text = $desciption;
        if(empty($desciption)) {
            $link_text = $CatPage->get_HrefText($cat,$page);
        }
        return $CatPage->create_LinkTag($CatPage->get_Href($cat,$page)
                    ,$link_text
                    ,"page"
                    ,$language->getLanguageHtml("tooltip_link_page_2", $CatPage->get_HrefText($cat,$page), $CatPage->get_HrefText($cat,false))
                    );
    }

    function syntax_absatz($desciption,$value) {
        // Verweise auf Absätze innerhalb der Inhaltsseite
        global $language;
        // Beschreibungstext extrahieren
        $link_text = $value;
        if(!empty($desciption)) {
            $link_text = $desciption;
        }
        # wird kein Absatz angegeben dann ist das Seitenanfang
        if(strlen($value) < 1)
            $value = "_absatztop-";
        if(empty($desciption) and $value == "_absatztop-")
            $link_text = $language->getLanguageValue("anchor_top_0");
        $pos = count($GLOBALS['syntax_anchor_absatz']);
        $GLOBALS['syntax_anchor_absatz'][$pos]['ueber'] = $value;
        $GLOBALS['syntax_anchor_absatz'][$pos]['dummy'] = '<!-- '.$pos.' absatz Dummy -->';
        $GLOBALS['syntax_anchor_absatz'][$pos]['inhalt'] = $link_text;
        return $GLOBALS['syntax_anchor_absatz'][$pos]['dummy'];
    }

    function syntax_datei($desciption,$value) {
        // Datei aus dem Dateiverzeichnis (überprüfen, ob Datei existiert)
        // Datei aus dem Dateiverzeichnis mit beliebigem Text
        global $specialchars;
        global $language;
        global $CatPage;
        global $CMS_CONF;

        list($cat,$datei) = $CatPage->split_CatPage_fromSyntax($value,true);

        if(!$CatPage->exists_File($cat,$datei)) {
            $datei_text = $specialchars->rebuildSpecialChars($datei,true,true);
            return $this->createDeadlink($datei_text, $language->getLanguageValue("tooltip_link_file_error_2", $datei_text, $specialchars->rebuildSpecialChars($cat,true,true)));
        }
        # testen ob es keine html tag enthält
        if(strip_tags($desciption) == $desciption)
            $desciption = $specialchars->rebuildSpecialChars($desciption, true, true);
        $link_text = $desciption;
        if(empty($desciption)) {
            $link_text = $CatPage->get_FileText($cat,$datei);
        }
        // Download-Links in neuem Fenster öffnen?
        $target = false;
        $force_download = true;
        if ($CMS_CONF->get("targetblank_download") == "true") {
            $target = "_blank";
            $force_download = false;
        }
        return $CatPage->create_LinkTag($CatPage->get_HrefFile($cat,$datei,$force_download)
                    ,$link_text
                    ,"file"
                    ,$language->getLanguageHtml("tooltip_link_file_2", $CatPage->get_FileText($cat,$datei), $CatPage->get_HrefText($cat,false))
                    ,$target);
    }

    function syntax_bildlinks($desciption,$value) {
        return $this->syntax_bild($desciption,$value,"bildlinks");
    }
    function syntax_bildrechts($desciption,$value) {
        return $this->syntax_bild($desciption,$value,"bildrechts");
    }
    function syntax_bild($desciption,$value,$syntax = "bild") {
        // Bild aus dem Dateiverzeichnis oder externes Bild
        global $specialchars;
        global $language;
        // Bildunterschrift merken, wenn vorhanden
        $subtitle = "";
        if(!empty($desciption))
            $subtitle = $desciption;
        if(strip_tags($subtitle) == $subtitle)
            $subtitle = $specialchars->rebuildSpecialChars($subtitle, true, true);

        $imgsrc = false;

        $value = $specialchars->getHtmlEntityDecode($value);
        // Bei externen Bildern: $value NICHT nach ":" aufsplitten!
        if (preg_match($this->LINK_REGEX, $value)) {
            $imgsrc = $value;
        }
        // Ansonsten: Nach ":" aufsplitten
        else {
            global $CatPage;
            global $CMS_CONF;

            list($cat,$datei) = $CatPage->split_CatPage_fromSyntax($value,true);

            if(!$CatPage->exists_File($cat,$datei)) {
                $cat_text = $specialchars->rebuildSpecialChars($cat,true,true);
                $datei_text = $specialchars->rebuildSpecialChars($datei,true,true);
                return $this->createDeadlink($datei_text, $language->getLanguageValue("tooltip_image_error_2", $datei_text, $cat_text));
            }
            $imgsrc = $CatPage->get_srcFile($cat,$datei);
        }

        // Nun aber das Bild ersetzen!
        if ($imgsrc) {
            $alt = $specialchars->rebuildSpecialChars(replaceFileMarker($value,false),true,true);
            $cssclass = "";
            if ($syntax == "bild") {
                $cssclass = "contentimage";
            }
            if ($syntax == "bildlinks") {
                $cssclass = "leftcontentimage";
            }
            elseif ($syntax == "bildrechts") {
                $cssclass = "rightcontentimage";
            }

            // ohne Untertitel
            if ($subtitle == "") {
                // normales Bild: ohne <span> rundrum
                if ($syntax == "bild") {
                    return '<img src="'.$imgsrc.'" alt="'.$language->getLanguageHtml("alttext_image_1", $alt).'" class="'.$cssclass.'" />';
                }
                else {
                    return '<span class="'.$cssclass.'"><img src="'.$imgsrc.'" alt="'.$language->getLanguageHtml("alttext_image_1", $alt).'" class="'.$cssclass.'" /></span>';
                }
            }
            // mit Untertitel
            else {
                return '<span class="'.$cssclass.'"><img src="'.$imgsrc.'" alt="'.$language->getLanguageHtml("alttext_image_1", $alt).'" class="'.$cssclass.'" /><br /><span class="imagesubtitle">'.$subtitle.'</span></span>';
            }
        }
    }

    function syntax_hr($desciption,$value) {
        // linksbündiger Text
        return '<hr class="horizontalrule" />';
    }

    function syntax_links($desciption,$value) {
        // linksbündiger Text
        return '<div class="alignleft">'.$value.'</div>';
    }

    function syntax_zentriert($desciption,$value) {
        // zentrierter Text
        return '<div class="aligncenter">'.$value.'</div>';
    }

    function syntax_block($desciption,$value) {
        // Text im Blocksatz
        return '<div class="alignjustify">'.$value.'</div>';
    }

    function syntax_rechts($desciption,$value) {
        // rechtsbündiger Text
        return '<div class="alignright">'.$value.'</div>';
    }

    function syntax_fett($desciption,$value) {
        // Text fett
        return '<b class="contentbold">'.$value.'</b>';
    }

    function syntax_kursiv($desciption,$value) {
        // Text kursiv
        return '<i class="contentitalic">'.$value.'</i>';
    }

    function syntax_unter($desciption,$value) {
        // Text unterstrichen
        return '<u class="contentunderlined">'.$value.'</u>';
    }

    function syntax_durch($desciption,$value) {
        // Text durchgestrichen
        return '<s class="contentstrikethrough">'.$value.'</s>';
    }

    function syntax_ueber1($desciption,$value) {
        $GLOBALS['syntax_anchor_counter']++;
        $GLOBALS['syntax_anchor_ueber'][$value]['count'] = $GLOBALS['syntax_anchor_counter'];
        $GLOBALS['syntax_anchor_ueber'][$value]['type'] = "1";
        // Überschrift groß
        return '<h1 id="a'.$GLOBALS['syntax_anchor_counter'].'" class="heading1">'.$value.'</h1>';
    }

    function syntax_ueber2($desciption,$value) {
        $GLOBALS['syntax_anchor_counter']++;
        $GLOBALS['syntax_anchor_ueber'][$value]['count'] = $GLOBALS['syntax_anchor_counter'];
        $GLOBALS['syntax_anchor_ueber'][$value]['type'] = "2";
        // Überschrift mittel
        return '<h2 id="a'.$GLOBALS['syntax_anchor_counter'].'" class="heading2">'.$value.'</h2>';
    }

    function syntax_ueber3($desciption,$value) {
        $GLOBALS['syntax_anchor_counter']++;
        $GLOBALS['syntax_anchor_ueber'][$value]['count'] = $GLOBALS['syntax_anchor_counter'];
        $GLOBALS['syntax_anchor_ueber'][$value]['type'] = "3";
        // Überschrift normal
        return '<h3 id="a'.$GLOBALS['syntax_anchor_counter'].'" class="heading3">'.$value.'</h3>';
    }

    function syntax_liste($desciption,$value) {
        // Listenpunkt unorderedlist listitem
        return '<ul class="unorderedlist"><li class="listitem">'.$value.'</li></ul>';
    }

    function syntax_numliste($desciption,$value) {
        // numerierter Listenpunkt orderedlist
        return '<ol class="orderedlist"><li class="listitem">'.$value.'</li></ol>';
    }

    function syntax_html($desciption,$value) {
        # alle geschützten lehrzeichen die in preparePageContent() erstelt wurden entfernen
        $value = str_replace("-html_nbsp~","",$value);
        # alle html Zeilenumbrüche die in preparePageContent() erstelt wurden entfernen
        $value = str_replace("-html_br~","",$value);
        # alle < und > im html code wieder herstellen
        $value = str_replace(array("&lt;","&gt;"),array("<",">"),$value);
        return $value;
    }

    function syntax_tabelle($desciption,$value) {
        // Tabellen
        $tabellecss = "contenttable";
        if(!empty($desciption))
            # was nach dem = steht wird als class name verwendet
            $tabellecss = $desciption;
        // Tabelleninhalt aufbauen
        $tablecontent = "";
        // Tabellenzeilen

        preg_match_all("/(&lt;|&lt;&lt;)(.*)(&gt;|&gt;&gt;)/Umsi", $value, $tablelines);
        foreach ($tablelines[0] as $j => $tablematch) {
            // Kopfzeilen
            if (preg_match("/&lt;&lt;([^&gt;]*)/Umsi", $tablematch)) {
                $linecontent = preg_replace('/\|/', '</th><th class="'.$tabellecss.'">', $tablelines[2][$j]);
                $linecontent = preg_replace('/&lt;(.*)/', "$1", $linecontent);
                $tablecontent .= '<tr><th class="'.$tabellecss.'">'.$linecontent.'</th></tr>';
            }
            // normale Tabellenzeilen
            else {
                // CSS-Klasse immer im Wechsel
                $cssline = $tabellecss."1";
                if ($j%2 == 0) {
                    $cssline = $tabellecss."2";
                }
                // Pipes durch TD-Wechsel ersetzen
                $linecontent = explode("|",$tablelines[2][$j]);
                $tablecontent .= "<tr>";
                foreach($linecontent as $pos => $td_content) {
                    # td css vortlaufend nummerieren mit 1 anfangen
                    $tablecontent .= '<td class="'.$cssline.' '.$tabellecss."cell".($pos + 1).'">'.$td_content.'</td>';
                }
                $tablecontent .= "</tr>";
            }
        }
        return '<table class="'.$tabellecss.'" cellspacing="0" border="0" cellpadding="0" summary="">'.$tablecontent.'</table>';
    }

    function syntax_include($desciption,$value) {
        // Includes
        global $specialchars;
        global $language;
        global $CatPage;

        list($cat,$page) = $CatPage->split_CatPage_fromSyntax($value);

        if(!$CatPage->exists_CatPage($cat,$page)) {
            $cat_text = $specialchars->rebuildSpecialChars($cat,true,true);
            $page_text = $specialchars->rebuildSpecialChars($page,true,true);
            return $this->createDeadlink($page_text, $language->getLanguageValue("tooltip_link_page_error_2", $page_text, $cat_text));
        }
/*        $link_text = $desciption;
        if(empty($desciption)) {
            $link_text = $CatPage->get_HrefText($cat,$page);
        }
*/
        # wenn cat page schonn im merker ist fehlermeldung weil sonst
        # include endlosschleife
        $incl_catpage = $CatPage->get_AsKeyName($cat).":".$CatPage->get_AsKeyName($page);
        if(isset($CatPage->SyntaxIncludeRemember[$incl_catpage]))
            return $this->createDeadlink($CatPage->get_HrefText($cat,false).":".$CatPage->get_HrefText($cat,$page), $language->getLanguageValue("tooltip_include_recursion_error_0"));
        else {
            if(false !== ($pagecontent = $CatPage->get_PageContent($cat,$page))) {
                # include merker setzen
                $CatPage->SyntaxIncludeRemember[$incl_catpage] = $CatPage->get_AsKeyName($page);
                # ist eine Inhaltseite also inhalt vorbereiten
                $pagecontent = $this->preparePageContent($pagecontent);
                return $pagecontent;
            }
        }
    }

    function syntax_farbe($desciption,$value) {
        // Farbige Elemente
        global $language;
        // Überprüfung auf korrekten Hexadezimalwert 3 und 6 stelig
        if (preg_match("/^([a-f]|\d){6}$/i", $desciption) or preg_match("/^([a-f]|\d){3}$/i", $desciption)) {
            return '<span style="color:#'.strtolower($desciption).';">'.$value.'</span>';
        }
        else {
            return $this->createDeadlink($value, $language->getLanguageValue("tooltip_color_error_1", $desciption));
        }
    }

    function syntax_fontsize($desciption,$value) {
        // Schrieft Größe
        global $language;
        $ex = array("pt","pc","in","mm","cm","px","em","ex","%");
        if(ctype_digit(str_replace($ex,'',str_replace(".","",$desciption)))
                and str_replace($ex,'',$desciption) != $desciption)
            return '<span class="fontsize" style="font-size:'.$desciption.';">'.$value.'</span>';
        else {
            return $this->createDeadlink($value, $language->getLanguageValue("tooltip_attribute_error_1", $desciption));
        }
    }

    function syntax_user($desciption,$value,$syntax) {
        global $USER_SYNTAX, $specialchars;
        # Wichtig für die syntax.conf da müssen alle Zeichen mit einem ^ dafor geschützt werden
        # das war mal in der Properties
        $syntax = $specialchars->encodeProtectedChr($USER_SYNTAX->get($syntax));

        if(strpos($value,FILE_START) !== false and strpos($value,FILE_END) !== false
                and preg_match('#<(frame|iframe|img|input|a|area|audio|base|link|source|video){1,1}[^>]*?(src|href){1,1}=["\']{VALUE}["\'][^>]*?>#is', $syntax)) {
            global $CatPage;
            list($cat, $file) = $CatPage->split_CatPage_fromSyntax($value, true);
            if($file !== false)
                $value = $CatPage->get_srcFile($cat, $file);
            if($cat === false and $file === false) {
                list($cat, $page) = $CatPage->split_CatPage_fromSyntax($value);
                if($cat !== false)
                    $value = $CatPage->get_Href($cat,$page);
            }
        }
        if(strpos($desciption,FILE_START) !== false and strpos($desciption,FILE_END) !== false
                and preg_match('#<(frame|iframe|img|input|a|area|base|link){1,1}[^>]*?(src|href){1,1}=["\']{DESCRIPTION}["\'][^>]*?>#is', $syntax)) {
            global $CatPage;
            list($cat, $file) = $CatPage->split_CatPage_fromSyntax($desciption, true);
            if($file !== false)
                $desciption = $CatPage->get_srcFile($cat, $file);
            if($cat === false and $file === false) {
                list($cat, $page) = $CatPage->split_CatPage_fromSyntax($desciption);
                if($cat !== false)
                    $desciption = $CatPage->get_Href($cat, $page);
            }
        }

        if(preg_match('#<(col|embed|img|input|link|track){1,1}[^>]*?(alt|title|label){1,1}=["\']{VALUE}["\'][^>]*?>#is', $syntax)) {
            $value = $specialchars->rebuildSpecialChars($value, true, true);
        }
        // Platzhalter {VALUE} im definierten Syntaxelement ersetzen
        $replacetext = str_replace("{VALUE}", $value, $syntax);

        if(preg_match('#<(col|embed|img|input|link|track){1,1}[^>]*?(alt|title|label){1,1}=["\']{DESCRIPTION}["\'][^>]*?>#is', $syntax)) {
            $desciption = $specialchars->rebuildSpecialChars($desciption, true, true);
        }

        // Platzhalter {DESCRIPTION} im definierten Syntaxelement durch die Beschreibung ersetzen
        $replacetext = str_replace("{DESCRIPTION}", $desciption, $replacetext);
        return $replacetext;
    }

    function plugin_replace($plugin,$plugin_parameter) {
        global $language;
        if(in_array($plugin, $this->activ_plugins)) {
            $replacement = NULL;
            // ...ueberpruefen, ob es eine zugehörige Plugin-PHP-Datei gibt
            if(file_exists(PLUGIN_DIR_REL.$plugin."/index.php")) {
                // Plugin-Code includieren
                require_once(PLUGIN_DIR_REL.$plugin."/index.php");
            }
            // Enthaelt der Code eine Klasse mit dem Namen des Plugins?
            if(class_exists($plugin)) {
                // Objekt instanziieren und Inhalt holen!
                if(!isset($this->$plugin))
                    $this->$plugin = new $plugin();
                if(file_exists(PLUGIN_DIR_REL.$plugin."/plugin.css")) {
                    $css = '<style type="text/css"> @import "'.URL_BASE.PLUGIN_DIR_NAME.'/'.$plugin.'/plugin.css"; </style>';
                    $this->insert_in_head($css);
                }
                $replacement = $this->$plugin->getPluginContent($plugin_parameter);
            } else {
                $replacement = $this->createDeadlink($plugin, $language->getLanguageValue("plugin_error_1", $plugin));
            }
            # return Plugin inhalt
            return $replacement;
        }
        # Deactiviertes Plugin mit nichts ersetzen oder alles was nicht in $this->activ_plugins steht
        return NULL;
    }

    function placeholder_replace($function,$placeholder) {
        switch ($placeholder) {
            case '{CHARSET}':
                $replace = CHARSET;
                break;
            case '{LAYOUT_DIR}':
                global $LAYOUT_DIR_URL;
                $replace = $LAYOUT_DIR_URL;
                break;
            case '{BASE_URL}':
                $replace = URL_BASE;
                break;
            case '{WEBSITE_TITLE}':
                # Da der cat und page name geändert werden kann setzen wir einen Verstägten Platzhalter
                $replace = '<!-- WEBSITE_TITLE REPLACE-->';
                break;
            case '{WEBSITE_KEYWORDS}':
                global $specialchars, $CMS_CONF;
                $replace = $specialchars->rebuildSpecialChars($CMS_CONF->get("websitekeywords"),false,true);
                break;
            case '{WEBSITE_DESCRIPTION}':
                global $specialchars, $CMS_CONF;
                $replace = $specialchars->rebuildSpecialChars($CMS_CONF->get("websitedescription"),false,true);
                break;
            case '{MAINMENU}':
                $replace = $this->getMainMenu();
                break;
            case '{DETAILMENU}':
                global $CMS_CONF;
                $replace = "";
                # 0=Normales Detailmenü 1=Submenü (aktuelle Kategorie) 2=Submenü (alle Kategorien)
                if ($CMS_CONF->get("usesubmenu") == 0)
                    $replace = $this->getDetailMenu(CAT_REQUEST);
                break;
            case '{SEARCH}':
                require_once(BASE_DIR_CMS."SearchClass.php");
                $search = new SearchClass();
                $replace = $search->getSearchForm();
                break;
            case '{SITEMAPLINK}':
                global $language;
                $replace = '<a href="'.URL_BASE.'index.php?action=sitemap" id="sitemaplink"'.$this->getTitleAttribute($language->getLanguageHtml("tooltip_showsitemap_0")).">".$language->getLanguageValue("message_sitemap_0")."</a>";
                break;
            case '{CMSINFO}':
                global $language;
                $replace = '<a href="http://www.mozilo.de/" target="_blank" id="cmsinfolink"'.$this->getTitleAttribute($language->getLanguageHtml("tooltip_link_cmsinfo")).">moziloCMS ".CMSVERSION."</a>";
                break;
            case '{TABLEOFCONTENTS}':
                # Da es den Inhalt erst am schluss gibt setzen wir einen Verstägten Platzhalter
                $replace = '<!-- TABLEOFCONTENTS REPLACE-->';
                break;
            case '{WEBSITE_NAME}':
                global $specialchars, $CMS_CONF;
                $replace = $specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"),false,true);
                break;
            case '{CATEGORY}':
                $replace = CAT_REQUEST;
                break;
            case '{CATEGORY_URL}':
                global $specialchars;
                $replace = $specialchars->replaceSpecialChars(CAT_REQUEST,true);
                break;
            case '{CATEGORY_NAME}':
                global $CatPage;
                $replace = $CatPage->get_HrefText(CAT_REQUEST,false);
                break;
            case '{PAGE}':
                $replace = PAGE_REQUEST;
                break;
            case '{PAGE_URL}':
                global $specialchars;
                $replace = $specialchars->replaceSpecialChars(PAGE_REQUEST,true);
                break;
            case '{PAGE_NAME}':
                global $CatPage;
                $replace = $CatPage->get_HrefText(CAT_REQUEST,PAGE_REQUEST);
                break;
            case '{PAGE_FILE}':
                global $CatPage;
                $replace = "";
                if($CatPage->exists_CatPage(CAT_REQUEST,PAGE_REQUEST))
                    $replace = PAGE_REQUEST.$CatPage->get_Type(CAT_REQUEST,PAGE_REQUEST);
                break;
            case '{MEMORYUSAGE}':
                $replace = '<!--&#94;&#123;MEMORYUSAGE&#94;&#125;-->';
                break;
            case '{EXECUTETIME}':
                $replace = '<!--&#94;&#123;EXECUTETIME&#94;&#125;-->';
                break;
            case '{JQUERY}':
                $this->insert_jquery_in_head("jquery");
                $replace = NULL;
                break;
            default:
                $replace = NULL;
        }
        return $replace;
    }

// ------------------------------------------------------------------------------
// Hilfsfunktion: "title"-Attribut zusammenbauen (oder nicht, wenn nicht konfiguriert)
// ------------------------------------------------------------------------------
    function getTitleAttribute($value) {
        global $CMS_CONF;
        if ($CMS_CONF->get("showsyntaxtooltips") == "true") {
            return ' title="'.$value.'"';
        }
        return "";
    }

// ------------------------------------------------------------------------------
// Inhaltsverzeichnis aus den übergebenen Überschrift-Infos aufbauen
// ------------------------------------------------------------------------------
    function getToC() {
        if(count($GLOBALS['syntax_anchor_ueber']) < 1)
            return NULL;
        global $language;
        $tableofcontents = '<div class="tableofcontents">';
        $tableofcontents .= "<ul>";
        foreach($GLOBALS['syntax_anchor_ueber'] as $value => $count_type) {
            $link = '<a class="page" href="#a'.$count_type['count'].'"'.$this->getTitleAttribute($language->getLanguageHtml("tooltip_anchor_goto_1", $value)).'>'.$value.'</a>';
            if ($count_type['type'] >= "2") {
                $tableofcontents .= '<li class="blind"><ul>';
            }
            if ($count_type['type'] >= "3") {
                $tableofcontents .= '<li class="blind"><ul>';
            }
            $tableofcontents .= '<li class="toc_'.$count_type['type'].'">'.$link.'</li>';
            if ($count_type['type'] >= "2") {
                $tableofcontents .= "</ul></li>";
            }
            if ($count_type['type'] >= "3") {
                $tableofcontents .= "</ul></li>";
            }
        }
        $tableofcontents .= "</ul>";
        $tableofcontents .= "</div>";
        return $tableofcontents;
    }

// ------------------------------------------------------------------------------
// Hilfsfunktion: zerteilt content in "vor Inhaltseite", "Inhaltseite" und "nach Inhaltseite"
// ------------------------------------------------------------------------------
    function splitContent($content = false) {
        if($content === false)
            $content = $this->content;
        $content_first = "";
        $content_last = "";
        if(strstr($content,'---content~~~') and strstr($content,'~~~content---')) {
            $start = strpos($content,"---content~~~");
            $content_first = substr($content,0,$start);
            $length = (strpos($content,"~~~content---") + strlen("~~~content---")) - $start;
            $content_last = substr($content,($start + $length));
            $content = substr($content,$start,$length);
        }
        return array($content_first,$content,$content_last);
    }

// ------------------------------------------------------------------------------
// Hilfsfunktion: Inhalte vorbereiten
// ------------------------------------------------------------------------------
    function preparePageContent($content) {
        global $specialchars;

        list($content_first,$content,$content_last) = $this->splitContent($content);
        // Inhaltsformatierungen
        # alle &lt; und &gt; die in einer page sind sollen so sein
        $content = str_replace(array("&lt;","&gt;"),array("-html_lt~","-html_gt~"),$content);
        # alle < und > in &lt; und &gt; wandeln damit sie nicht als html tags angezeigt werden
        $content = str_replace(array("<",">"),array("&lt;","&gt;"),$content);

        # alle zeichen die ein ^ davor sind geschützte zeichen
        $content = $specialchars->encodeProtectedChr($content);
# alle & die nicht zu entities gehören wandeln nach -html_amp~
$content = preg_replace('/&(?!#?[a-z0-9]+;)/i', '-html_amp~', $content);
        // Für Einrückungen
        $content = str_replace("  ","-html_nbsp~-html_nbsp~",$content);
        # Zeilenümbrüche sind in pages später html umbrüche
#        $content = preg_replace('/(\r\n|\r|\n)/', '$1-html_br~', $content);
        $content = preg_replace('/(\r\n|\r|\n)/', '-html_br~$1', $content);
        // Platzhalter ersetzen
#        $content = replacePlaceholders($content, "", "");

        $this->is_preparePageContent = true;
        return $content_first.$content.$content_last;
    }

// ------------------------------------------------------------------------------
// Hilfsfunktion: Deadlink erstellen
// ------------------------------------------------------------------------------
    function createDeadlink($content, $title) {
/*
Solen wir den $content Anzeigen?????????
        $special_search = array('[',']','{','}','|');
        $special_replace = array('&#091;','&#093;','&#123;','&#125;','&#124;');
        $content = str_replace($special_search,$special_replace,$content);*/
        return '<span class="deadlink">'.$title.'</span>';
    }

    function replaceAnchorAbsatz() {
        global $language;
        foreach($GLOBALS['syntax_anchor_absatz'] as $pos => $ueber_array) {
#!!!!!!!! $ueber_array['ueber'] == $language->getLanguageValue("anchor_top_0") ist nur
# zur abwertskompatibelen drin neu einfach lehr lassen z.B. [absatz|] oder [absatz=Nach Oben|]
            if($ueber_array['ueber'] == $language->getLanguageValue("anchor_top_0")
                or $ueber_array['ueber'] == "_absatztop-") {
                $replace = '<a class="paragraph" href="#a0"'.$this->getTitleAttribute($language->getLanguageHtml("tooltip_anchor_gototop_0")).'>'.$ueber_array['inhalt'].'</a>';
            } elseif(isset($GLOBALS['syntax_anchor_ueber'][$ueber_array['ueber']]['count'])) {
                $replace = '<a class="paragraph" href="#a'.$GLOBALS['syntax_anchor_ueber'][$ueber_array['ueber']]['count'].'"'.$this->getTitleAttribute($language->getLanguageHtml("tooltip_anchor_goto_1", $ueber_array['ueber'])).'>'.$ueber_array['inhalt'].'</a>';
            } else {
                $replace = $this->createDeadlink($ueber_array['ueber'], $language->getLanguageValue("tooltip_anchor_error_1", $ueber_array['ueber']));
            }
            $this->content = str_replace($ueber_array['dummy'],$replace,$this->content);
        }
    }

// ------------------------------------------------------------------------------
// Rueckgabe des Website-Titels
// ------------------------------------------------------------------------------
    # $cat=(string) und $page=(false) Platzhalter {PAGE} wird entfernt
    # $cat=(string) und $page=(string) Platzhalter {PAGE} wird auch benutzt
    # $cat=(false) und $page=(false) Default verhalten
    function getWebsiteTitle($cat = false,$page = false) {#$action = false
        global $CMS_CONF;
        global $specialchars;

        $websitetitle = $specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"),false,true);
        # Default verhalten
        if($cat === false and $page === false) {
            if (ACTION_REQUEST == "sitemap") {
                global $language;
                $cat    = $language->getLanguageValue("message_sitemap_0");
                $page   = $language->getLanguageValue("message_sitemap_0");
            } elseif (ACTION_REQUEST == "search") {
                global $SEARCH_REQUEST;
                $searchwords = $specialchars->rebuildSpecialChars($SEARCH_REQUEST,false,true);
                global $language;
                $cat    = $language->getLanguageValue("message_search_0");
                $page   = $language->getLanguageValue("message_searchresult_1", (trim($searchwords)));
#        } elseif($cat === false and $page === false) {
            } else {
                global $CatPage;
                $cat = $CatPage->get_HrefText(CAT_REQUEST,false);
                $page = $CatPage->get_HrefText(CAT_REQUEST,PAGE_REQUEST);
            }
        }
/*        global $passwordok;
        if($passwordok === false) {
            global $language;
            $page   = $language->getLanguageValue("passwordform_title_0");
        }*/
        # es wird nur die $cat benutzt wenn
        $title = $specialchars->rebuildSpecialChars($CMS_CONF->get("titlebarformat"),true,true);
        if(($CMS_CONF->get("hidecatnamedpages") == "true"
                and $cat == $page
                and strstr($title,'{CATEGORY}')
                and strstr($title,'{PAGE}')
                # user $cat $page übergabe
                ) or (($page === false or empty($page))
                    and strstr($title,'{CATEGORY}')
                    and strstr($title,'{PAGE}')
                )
            ) {
            $title = str_replace(array('{SEP}{PAGE}','{PAGE}{SEP}'),'',$title);
        }
        $sep = $specialchars->rebuildSpecialChars($CMS_CONF->get("titlebarseparator"),true,true);
        $title = str_replace(array('{WEBSITE}','{CATEGORY}','{PAGE}','{SEP}'),
                            array($websitetitle,$cat,$page,$sep), $title);
        return $title;
    }


// ------------------------------------------------------------------------------
// Aufbau des Hauptmenues, Rueckgabe als String
// ------------------------------------------------------------------------------
function getMainMenu() {
    global $CMS_CONF;
    global $CatPage;

    $mainmenu = "<ul class=\"mainmenu\">";

    // Jedes Element des Arrays ans Menue anhaengen
    foreach($CatPage->get_CatArray() as $cat) {
        $mainmenu .= '<li class="mainmenu">'
            .$CatPage->create_AutoLinkTag($cat,false,"menu");
        if($CatPage->is_Activ($cat,false)
                and $CMS_CONF->get("usesubmenu") > 0) {
            $mainmenu .= $this->getDetailMenu($cat);
        } elseif(!$CatPage->is_Activ($cat,false)
                and $CMS_CONF->get("usesubmenu") == 2) {
            $mainmenu .= $this->getDetailMenu($cat);
        }
        $mainmenu .= "</li>";

    }
    // Rueckgabe des Menues
    return $mainmenu . "</ul>";
}


// ------------------------------------------------------------------------------
// Aufbau des Detailmenues, Rueckgabe als String
// ------------------------------------------------------------------------------
function getDetailMenu($cat) {
    global $language;
    global $specialchars;
    global $CMS_CONF;
    global $CatPage;

    if ($CMS_CONF->get("usesubmenu") > 0)
        $cssprefix = "submenu";
    else
        $cssprefix = "detailmenu";

    $detailmenu = "<ul class=\"detailmenu\">";
    // Sitemap
    if ((ACTION_REQUEST == "sitemap") and ($CMS_CONF->get("usesubmenu") == 0))
        $detailmenu .= '<li class="detailmenu">'.$CatPage->create_ActionLinkTag("sitemap",$cssprefix."active").'</li>';
    // Suchergebnis
    elseif ((ACTION_REQUEST == "search") and ($CMS_CONF->get("usesubmenu") == 0))
        $detailmenu .= '<li class="detailmenu">'.$CatPage->create_ActionLinkTag("search",$cssprefix."active").'</li>';
    // "ganz normales" Detailmenue einer Kategorie
    else {
        // Content-Verzeichnis der aktuellen Kategorie einlesen
        $pagearray = $CatPage->get_PageArray($cat);
        # wenn keine Inhaltseiten lehr zurück
        if(count($pagearray) == 0)
            return NULL;
        // Jedes Element des Arrays ans Menue anhaengen
        foreach ($pagearray as $page) {
            $detailmenu .= '<li class="detailmenu">'
                .$CatPage->create_AutoLinkTag($cat,$page,$cssprefix)
                ."</li>";
        }
    }
    // Rueckgabe des Menues
    return $detailmenu . "</ul>";
}

}

?>