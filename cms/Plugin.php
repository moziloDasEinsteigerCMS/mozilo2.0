<?php if(!defined('IS_CMS')) die();
/* 
* Abstrakte Basisklasse für moziloCMS-Plugins.
*
* PHP4 kennt das Prinzip der Abstraktion noch nicht,
* deswegen ist es durch die Hintertür implementiert:
* Im Konstruktor wird sichergestellt, daß niemand 
* diese abstrakte Klasse hier direkt instanziieren 
* kann; dann wird geprüft, ob erbende Klassen auch 
* sauber alle wichtigen Funktionen implementieren.

*/

class Plugin {
    
    // Membervariable für eventuelle Fehlermeldungen
    var $error;
    
    // Membervariable für bequemen Zugriff auf die Plugin-Settings
    var $settings; 
    
    var $PLUGIN_SELF_DIR;
    var $PLUGIN_SELF_URL;
    /*
    * Konstruktor
    */
    function __construct() {
        $plugin_str = 'Plugin';
        $plugin_class = get_class($this);
        $plugin_class_dir = $plugin_class;
/*        if((version_compare( phpversion(), '5.0' ) < 0)) {
            # php4
            $plugin_str = strtolower($plugin_str);
            $declared_classes = get_declared_classes();
            if ($handle = opendir(BASE_DIR.PLUGIN_DIR_NAME."/")) {
                while (false !== ($plugin_dir = readdir($handle))) {
                    $key = array_search(strtolower($plugin_dir), $declared_classes);
                    if(isset($declared_classes[$key]) and $key > 0 and $plugin_class == strtolower($plugin_dir)) {
                        $plugin_class = $declared_classes[$key];
                        $plugin_class_dir = $plugin_dir;
                        break;
                    }
                }
                closedir($handle);
            }
        }
*/
        // diese (abstrakte) Klasse darf nicht direkt instanziiert werden!
        if ($plugin_class == $plugin_str || !is_subclass_of($this, $plugin_str)){
#        if (get_class($this) == 'Plugin' || !is_subclass_of ($this, 'Plugin')){
            trigger_error('This class is abstract; it cannot be instantiated.', E_USER_ERROR);
        }

        // prüfen, ob alle "abstrakten" Methoden implementiert wurden:
        $this->error = null;
        $this->checkForMethod("getContent");
        $this->checkForMethod("getConfig");
        $this->checkForMethod("getInfo");
        
        $this->PLUGIN_SELF_DIR = BASE_DIR.PLUGIN_DIR_NAME."/".$plugin_class_dir."/";
        $this->PLUGIN_SELF_URL = URL_BASE.PLUGIN_DIR_NAME."/".$plugin_class_dir."/";
        // Settings-Variable als Properties-Objekt der plugin.conf instanziieren
#        if (file_exists("plugins/".get_class($this)."/plugin.conf")) {
#            $this->settings = new Properties("plugins/".get_class($this)."/plugin.conf");
#        }
        if (file_exists(BASE_DIR.PLUGIN_DIR_NAME."/".$plugin_class_dir."/plugin.conf.php")) {
            $this->settings = new Properties(BASE_DIR.PLUGIN_DIR_NAME."/".$plugin_class_dir."/plugin.conf.php");
        }
        // Wenn plugin.conf nicht vorhanden ist, wird die Fehlervariable gefüllt
        else {
        	// im Admin wird die Klasse Plugin verwendet; die Klasse Syntax kann aber nicht geladen werden. Die Abfrage verhindert einfach eine Fehlermeldung. 
            if(class_exists("Syntax")) {
                $syntax = new Syntax();
                $language = new Language();
                $this->error = $syntax->createDeadlink("{".get_class($this)."}", $language->getLanguageValue("plugin_error_missing_pluginconf_1", get_class($this)));
            }
        }
    }

    /*
    * Gibt den Inhalt des Plugins zurück
    */
    function getPluginContent($param) {
        // erst prüfen, ob bei der Initialisierung ein Fehler aufgetreten ist
        if ($this->error == null) {
            return $this->getContent(replaceFileMarker($param));
        }
        // Bei Fehler: Inhalt der Fehlervariablen zurückgeben
        else {
            return $this->error;
        }
    }
    
    /*
    * Prüft, ob das Objekt eine Methode mit dem übergebenen Namen besitzt
    */
    function checkForMethod($method) {
        // wenn die Methode nicht existiert, wird die Fehlervariable gefüllt
        if (class_exists("Syntax") and !method_exists($this, $method)) {
            $syntax = new Syntax();
            $language = new Language();
            $this->error = $syntax->createDeadlink("{".get_class($this)."}", $language->getLanguageValue("plugin_error_missing_method_2", get_class($this), $method));
        }
    }

# {PLUGIN| (PARAMETER) $separation (KEY $separation_key_value VALUE) }
    # damit kann man sich die $value die in getContent() übergeben wird serlegen lassen
    # $value = die übergebenen $value
    # $userparamarray = ein array mit den werten die man haben möchte und auch gleich vorbelegung mit default werten
    # $separation = trennung der einzelnen Parameter
    # $separation_key_value = trennung des Parameters in Key=Value
    # Ablauf:
    # 1. $value wird erst mit $separation zerlegt
    # 2. die zerlegten teile werden mit $separation_key_value zerlegt
    # Beispiel wir gehen von den Default $separation und $separation_key_value aus:
    # $value = eins,zwei,key1=value1,drei
    # ergebnis = array( eins, zwei, key1 => value1, drei )
    # bei userparamarray = array( def1, def2, key1 => def, def3, def4 )
    # ergebnis = array( eins, zwei, key1 => value1, drei, def4 )
    # bei userparamarray = array( def1, def2, key1 => def )
    # ergebnis = array( eins, zwei, key1 => value1 )
    function makeUserParaArray($value,$userparamarray = false,$separation = ",",$separation_key_value = "=") {
        global $specialchars;
        $separation_protect = $specialchars->encodeProtectedChr('^'.$separation);
        $separation_key_value_protect = $specialchars->encodeProtectedChr('^'.$separation_key_value);

        $protect_tag_search = array($separation,$separation_key_value,"<",">");
        $protect_tag_replace = array($separation_protect,$separation_key_value_protect,$specialchars->encodeProtectedChr('^<'),$specialchars->encodeProtectedChr('^>'));
        $protect_tags = false;
        # erst alle Tags suchen die nicht geschlossen werden müssen
        # area base basefont br col frame hr img input isindex link meta param
        preg_match_all("/<(area|base|basefont|br|col|frame|hr|img|input|isindex|link|meta|param)[^>]*>/", $value, $tags);
        if($tags[0]) {
            $protect_tags = true;
            $tags = array_unique($tags[0],SORT_STRING);
            foreach($tags as $search)
                $value = str_replace($search,str_replace($protect_tag_search,$protect_tag_replace,$search),$value);
        }
        unset($tags);
        # dann nur die äußeren Tags von Verschachtelten suchen
        preg_match_all('@\<\s*?(\w+)(?:\b(?:[^\>])*)?\>((?:(?>[^\<]*)|(?R))*)\<\/\s*?\\1(?:\b[^\>]*)?\>@uxis', $value, $tags);
        if($tags[0]) {
            $protect_tags = true;
            $tags = array_unique($tags[0],SORT_STRING);
            foreach($tags as $search)
                $value = str_replace($search,str_replace($protect_tag_search,$protect_tag_replace,$search),$value);
        }
        unset($tags);

        # wenn im Trenn zeichen ein ^ ist müssen wir das decoden da die Syntax.php das encodet hat
        # siehe preparePageContent()
        $separation = $specialchars->encodeProtectedChr($separation);
        $separation_key_value = $specialchars->encodeProtectedChr($separation_key_value);

        $user_array = array();
        $para_array = array();

        # alle werte die gefunden werden in ein $para_array einsetzen
        $tmp = explode($separation,$value);
        foreach($tmp as $pos => $values) {
            if(strstr($values,$separation_key_value)) {
                $tmp = explode($separation_key_value,$values);
                # wenn zwischen $separation und $separation_key_value ein -html_br~ wegmachen
                $tmp[0] = str_replace("-html_br~","",$tmp[0]);
                # und ein trim wegen Zeilenumbruch und Lehrzeichen
                $tmp[0] = trim($tmp[0]);
                if($protect_tags) {
                    $tmp[0] = str_replace($protect_tag_replace,$protect_tag_search,$tmp[0]);
                    $tmp[1] = str_replace($protect_tag_replace,$protect_tag_search,$tmp[1]);
                }
                $para_array[$tmp[0]] = $tmp[1];
            } else {
                if($protect_tags) {
                    $pos = str_replace($protect_tag_replace,$protect_tag_search,$pos);
                    $values = str_replace($protect_tag_replace,$protect_tag_search,$values);
                }
                $para_array[$pos] = $values;
            }
        }
        # wenn $userparaarray ein array wird $user_array nur mit den Vorgegebene Werten erzeugt
        if(is_array($userparamarray)) {
            foreach($userparamarray as $key => $values) {
                $user_array[$key] = $values;
                if(isset($para_array[$key]))
                    $user_array[$key] = $para_array[$key];
            }
            return $user_array;
        }
        return $para_array;
    }
}
?>