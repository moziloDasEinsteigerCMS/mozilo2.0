<?php if(!defined('IS_CMS')) die();

/***************************************************************
* 
* Eingebettete Galerie für moziloCMS.
* 
***************************************************************/
class Galerie extends Plugin {

	/***************************************************************
    * 
    * Gibt den HTML-Code zurück, mit dem die Plugin-Variable ersetzt 
    * wird.
    * 
    ***************************************************************/
    function getContent($value) {
        $values = explode(",", $value);

        global $CMS_CONF;
        global $specialchars;
        global $lang_gallery_cms;

        $dir = PLUGIN_DIR_REL."Galerie/";
        $lang_gallery_cms = new Language($dir."sprachen/cms_language_".$CMS_CONF->get("cmslanguage").".txt");
 
        $embedded = $this->settings->get("target");
    
        $linkprefix = "index.php?cat=".CAT_REQUEST."&amp;page=".PAGE_REQUEST."&amp;";
        if ($embedded == "_blank") {
            $linkprefix = "index.php?galtemplate=true&amp;";
        }
        if($CMS_CONF->get("modrewrite") == "true") {
            $linkprefix = URL_BASE.CAT_REQUEST."/".PAGE_REQUEST.".html?";
            if ($embedded == "_blank") {
                $linkprefix = "index.php.html?galtemplate=true&amp;";
            }
        }
    
        $index = NULL;
        if (getRequestValue('index', 'get', false))
            $index = getRequestValue('index', 'get', false);
    
    
        $cat_activ = "";
        if(CAT_REQUEST == basename(dirname($_SERVER['REQUEST_URI'])) and $embedded == "_self") {
            $cat_activ = "../";
        }
    
        if ($this->settings->get("usethumbs") == "true")
            $usethumbs = true;
        else
            $usethumbs = false;
    
        // Übergebene Parameter überprüfen
        $gal_request = $specialchars->replacespecialchars($specialchars->getHtmlEntityDecode($values[0]),false);
        if (getRequestValue("gal", 'get', false))
            $gal_request = $specialchars->replacespecialchars(getRequestValue("gal", 'get', false),false);
    
        $GALERIE_DIR = BASE_DIR.GALLERIES_DIR_NAME."/".$gal_request."/";
        $GALERIE_DIR_SRC = str_replace("%","%25",URL_BASE.GALLERIES_DIR_NAME."/".$gal_request."/");
    
        # keine Galerie angegeben oder Galerie gibts nicht
        if (($gal_request == "") || (!file_exists($GALERIE_DIR))) {
            global $syntax;
            if($gal_request == "") {
                return $syntax->createDeadlink($lang_gallery_cms->getLanguageHtml("message_gallerydir_error_0"),$lang_gallery_cms->getLanguageHtml("message_gallerydir_error_0"));
            } else {
                return $syntax->createDeadlink($specialchars->rebuildSpecialChars($gal_request, false, true), $lang_gallery_cms->getLanguageHtml("message_gallerydir_error_1", $specialchars->rebuildSpecialChars($gal_request, false, true)));
            }
        }
    
        # Galerie erzeugen
        if (($embedded == "_self") or (getRequestValue('gal', 'get', false))) {
    
            $alldescriptions = false;
            if(is_file($GALERIE_DIR."texte.conf.php"))
                $alldescriptions = new Properties($GALERIE_DIR."texte.conf.php");
            // Galerieverzeichnis einlesen
            $picarray = getDirAsArray($GALERIE_DIR,"img");
            $allindexes = array();
            for ($i=1; $i<=count($picarray); $i++) {
                array_push($allindexes, $i);
            }
            // globaler Index
            if ((!isset($index)) || (!in_array($index, $allindexes)))
                $index = 1;
            else
                $index = $index;
    
            //
            // Debug: 2020-08-01
            //
            $intindex = (int)$index;
            $prev_pos = $intindex - 1;
            $next_pos = $intindex + 1;
    
            // Bestimmung der Positionen
            $first = 1;
            $last = count($allindexes);
            if (!in_array($prev_pos, $allindexes))
                $previous = $last;
            else
                $previous = $prev_pos;
            if (!in_array($next_pos, $allindexes))
                $next = 1;
            else
                $next = $next_pos;
            $template = NULL;
            if($this->settings->get("gallerytemplate")) {
                    $template = $this->settings->get("gallerytemplate");
                    if(strrpos("tmp".$value,'{NUMBERMENU}') > 0) {
                        $template = $value;                    
                }
            } else { 
                $template = "{GALLERYMENU}{NUMBERMENU}\n{CURRENTPIC}";
                if(strrpos("tmp".$value,'{NUMBERMENU}') > 0) {
                    $template = $value;
                }
            }
            $html = $template;
    
            if (count($picarray) == 0) {
                $html = str_replace('{NUMBERMENU}', $lang_gallery_cms->getLanguageHtml("message_galleryempty_0"), $html);
            }
            # Titel der Galerie
            $html = str_replace('{CURRENTGALLERY}', $specialchars->rebuildSpecialChars($gal_request,false,true), $html);
            if ($usethumbs) {
                $html = str_replace('{GALLERYMENU}', "&nbsp;", $html);
                $html = str_replace('{NUMBERMENU}', $this->getThumbnails($picarray,$alldescriptions,$GALERIE_DIR,$GALERIE_DIR_SRC), $html);
                $html = str_replace('{CURRENTPIC}', "&nbsp;", $html);
                $html = str_replace('{CURRENTDESCRIPTION}', "&nbsp;", $html);
                $html = str_replace('{XOUTOFY}', "&nbsp;", $html);
            } else {
                $html = str_replace('{GALLERYMENU}', $this->getGalleryMenu($picarray,$linkprefix,$gal_request,$index,$first,$previous,$next,$last), $html);
                $html = str_replace('{NUMBERMENU}', $this->getNumberMenu($picarray,$linkprefix,$index,$gal_request,$first,$last), $html);
                $html = str_replace('{CURRENTPIC}', $this->getCurrentPic($picarray,$index,$GALERIE_DIR_SRC), $html);
                if (count($picarray) > 0) {

                    $arraypos = 0;                
                    $currentpos = 0;
                    $currentpos = intval($index); 
                    $arraypos = $currentpos - 1;
                    
                    $gallerypicname = "";
                    $gallerypicname = $picarray[$arraypos];
                    $getcurrentdescription = "";
                    $getcurrentdescription = $this->getCurrentDescription($gallerypicname,$picarray,$alldescriptions);

                    $html = str_replace('{CURRENTDESCRIPTION}', $getcurrentdescription, $html);
                } else {
                    $html = str_replace('{CURRENTDESCRIPTION}', "", $html);
                }
                $html = str_replace('{XOUTOFY}', $this->getXoutofY($picarray,$index,$last), $html);
                $html = str_replace('{CURRENT_INDEX}', $index, $html);
                $html = str_replace('{PREVIOUS_INDEX}', $previous, $html);
                $html = str_replace('{NEXT_INDEX}', $next, $html);
            }
            return $html;
        # Galerie Link erzeugen
        } else {
            $j=0;
            if(file_exists($GALERIE_DIR)) {
                $handle = opendir($GALERIE_DIR);
                while ($file = readdir($handle)) {
                    if (is_file($GALERIE_DIR.$file) and ($file <> "texte.conf.php")) {
                        $j++;
                    }
                }
                closedir($handle);
            } else {
                global $syntax;
                // Galerie nicht vorhanden
                return $syntax->createDeadlink($specialchars->rebuildSpecialChars($values[0], false, true), $lang_gallery_cms->getLanguageHtml("tooltip_link_gallery_error_1", $specialchars->rebuildSpecialChars($values[0], false, true)));
            }
            $gal_name = NULL;
            if(isset($values[0])) {
                $gal_name = $specialchars->rebuildSpecialChars($values[0], false, false);
            }
            if(isset($values[1])) {
                $gal_name = $specialchars->rebuildSpecialChars($values[1], false, false);
            }
            global $syntax;
            return "<div class=\"galleries\"><figure class=\"galleries-item\"><a class=\"gallery\" href=\"".$linkprefix."gal=".$gal_request."\" ".$syntax->getTitleAttribute($lang_gallery_cms->getLanguageHtml("tooltip_link_gallery_2", $specialchars->rebuildSpecialChars($values[0], false, true), $j))." ><figcaption class=\"galleries-caption\">".$gal_name."</figcaption></a></figure></div>";
        }
    } // function getContent
    

    // ------------------------------------------------------------------------------
    // Galeriemenü erzeugen
    // ------------------------------------------------------------------------------
    function getGalleryMenu($picarray,$linkprefix,$gal_request,$index,$first,$previous,$next,$last) {
        global $lang_gallery_cms;
    
        // Keine Bilder im Galerieverzeichnis?
        if (count($picarray) == 0)
            return "&nbsp;";

        $gallerymenu = "<div class=\"gallerymenu\">";
    
        // Link "Erstes Bild"
        if ($index == $first)
            $linkclass = "gallerymenuactive";
        else
            $linkclass = "gallerymenu";
        $gallerymenu .= "<div class=\"gallerymenu-item\"><a href=\"".$linkprefix."gal=".$gal_request."&amp;index=".$first."\" class=\"$linkclass\">".$lang_gallery_cms->getLanguageHtml("message_firstimage_0")."</a></div>";
        // Link "Voriges Bild"
        $gallerymenu .= "<div class=\"gallerymenu-item\"><a href=\"".$linkprefix."gal=".$gal_request."&amp;index=".$previous."\" class=\"gallerymenu\">".$lang_gallery_cms->getLanguageHtml("message_previousimage_0")."</a></div>";
        // Link "Nächstes Bild"
        $gallerymenu .= "<div class=\"gallerymenu-item\"><a href=\"".$linkprefix."gal=".$gal_request."&amp;index=".$next."\" class=\"gallerymenu\">".$lang_gallery_cms->getLanguageHtml("message_nextimage_0")."</a></div>";
        // Link "Letztes Bild"
        if ($index == $last)
            $linkclass = "gallerymenuactive";
        else
            $linkclass = "gallerymenu";
        $gallerymenu .= "<div class=\"gallerymenu-item\"><a href=\"".$linkprefix."gal=".$gal_request."&amp;index=".$last."\" class=\"$linkclass\">".$lang_gallery_cms->getLanguageHtml("message_lastimage_0")."</a></div>";
        // Rückgabe des Menüs
        return $gallerymenu."</div>";
    }

    // ------------------------------------------------------------------------------
    // Nummernmenü erzeugen
    // ------------------------------------------------------------------------------
    function getNumberMenu($picarray,$linkprefix,$index,$gal_request,$first,$last) {
    
        // Keine Bilder im Galerieverzeichnis?
        if (count($picarray) == 0)
            return "&nbsp;";
    
        $numbermenu = "<div class=\"gallerynumbermenu\">";
        for ($i=$first; $i<=$last; $i++) {
            $cssclass = $index == $i ? "gallerynumbermenuactive" : "gallerynumbermenu";
            $numbermenu .= "<div class=\"gallerynumbermenu-item\">"
                ."<a href=\"".$linkprefix."gal=".$gal_request."&amp;index=".$i."\" class=\"".$cssclass."\">".$i."</a>"
        ."</div>";
        }
        // Rückgabe des Menüs
        $numbermenu .= "</div>";
        return $numbermenu;
    }

        // ------------------------------------------------------------------------------
    // Thumbnails erzeugen
    // ------------------------------------------------------------------------------

    function getThumbnails($picarray,$alldescriptions,$GALERIE_DIR,$GALERIE_DIR_SRC) {

        global $specialchars;

        global $lang_gallery_cms;

 //       $picsperrow = $this->settings->get("picsperrow");

 //       if (empty($picsperrow)) $picsperrow = 4;

        $thumbs = "<div class=\"gallerytable\">";

        $i = 0;

        for ($i=0; $i<count($picarray); $i++) {

            // Bildbeschreibung holen

            $description = $this->getCurrentDescription($picarray[$i],$picarray,$alldescriptions);

            if ($description == "")

                $description = "&nbsp;";

            $thumbs .= "<div class=\"gallerytd\">"; 

            if (file_exists($GALERIE_DIR.PREVIEW_DIR_NAME."/".$specialchars->replaceSpecialChars($picarray[$i],false))) {

                $thumbs .= "<a id=\"".$specialchars->rebuildSpecialChars($picarray[$i],true,true)."\" href=\"#".$specialchars->rebuildSpecialChars($picarray[$i],true,true)."\">";
                $thumbs .="<img src=\"".$GALERIE_DIR_SRC.PREVIEW_DIR_NAME."/".$specialchars->replaceSpecialChars($picarray[$i],true)."\" alt=\"$description\" title=\"$description\" class=\"thumbnail\" />";
                $thumbs .="<div class=\"galleryoverlay\">";
                $thumbs .="<figure><img src=\"".$GALERIE_DIR_SRC.$specialchars->replaceSpecialChars($picarray[$i],true)."\" alt=\"$description\" title=\"$description\">";
                $thumbs .="<figcaption>".$description."</figcaption></figure>";
                $thumbs .="</div>";
                $thumbs .="</a>";
                $thumbs .="<a class=\"galeryimgclose\" href=\"#!\"></a>";
         //       $thumbs .="<a class=\"galleryimgprev\" href=\"#image1\">&lt;</a>";
         //       $thumbs .="<a class=\"galleryimgnext\" href=\"#image3\">&gt;</a>";

            } else {

                 $thumbs .= '<div><a style="color:red;" href="'.$GALERIE_DIR_SRC.PREVIEW_DIR_NAME."/".$specialchars->replaceSpecialChars($picarray[$i],true).'" target="_blank" title="'.$lang_gallery_cms->getLanguageHtml("tooltip_gallery_fullscreen_1", $specialchars->rebuildSpecialChars

($picarray[$i],true,true)).'"><b>'.$lang_gallery_cms->getLanguageHtml('message_gallery_no_preview').'</b></a></div>';

            }

   //         $thumbs .= "<div class=\"desc\">$description</div>"

           $thumbs .= "</div>";

        }

        while ($i<count($picarray)) {

            $thumbs .= "<div class=\"gallerytd\"></div>";

            $i++;

        }

        $thumbs .= "</div>";

        // Rückgabe der Thumbnails

        return $thumbs;

    }
    
    // ------------------------------------------------------------------------------
    // Aktuelles Bild anzeigen
    // ------------------------------------------------------------------------------
    function getCurrentPic($picarray,$index,$GALERIE_DIR_SRC) {
        global $specialchars;
        global $lang_gallery_cms;
    
        // Keine Bilder im Galerieverzeichnis?
        if (count($picarray) == 0)
            return "&nbsp;";
            
        $arraypos = 0;                
        $currentpos = 0;
        $currentpos = intval($index); 
        $arraypos = $currentpos - 1;
    
        // Link zur Vollbildansicht öffnen
        $currentpic = "<div class=\"gallerynothumbs\">";
        $currentpic .="<figure>";
        $currentpic .= "<img src=\"".$GALERIE_DIR_SRC.$specialchars->replaceSpecialChars($picarray[$arraypos],true)."\" alt=\"".$lang_gallery_cms->getLanguageHtml("alttext_galleryimage_1", $specialchars->rebuildSpecialChars($picarray[$arraypos],true,true))."\" />";
        $currentpic .="<figcaption>{CURRENTDESCRIPTION}</figcaption>";
        $currentpic .="</figure>";
        // Link zur Vollbildansicht schliessen
        $currentpic .= "</div>";
        // Rückgabe des Bildes
        return $currentpic;
    }

    // ------------------------------------------------------------------------------
    // Beschreibung zum aktuellen Bild anzeigen
    // ------------------------------------------------------------------------------
    function getCurrentDescription($picname,$picarray,$alldescriptions) {
        global $specialchars;
        
        if(!$alldescriptions)
           return "&nbsp;";
        // Keine Bilder im Galerieverzeichnis?
        if (count($picarray) == 0)
           return "&nbsp;";
        // Bildbeschreibung einlesen
        $description = $alldescriptions->get($picname);
        //
        // Debug: 2021-12-17 - PHP 8.1 error
        //        
        if (empty($description)) {
          return "&nbsp;";
        }
        
        if (strlen($description) > 0) {
                return $specialchars->rebuildSpecialChars($description,false,true);
        } else {
            return "&nbsp;";
        }
    }

    // ------------------------------------------------------------------------------
    // Position in der Galerie anzeigen
    // ------------------------------------------------------------------------------
    function getXoutofY($picarray,$index,$last) {
        global $lang_gallery_cms;
    
        // Keine Bilder im Galerieverzeichnis?
        if (count($picarray) == 0)
        return "&nbsp;";
        return "<div class=\"imgpos\">".$lang_gallery_cms->getLanguageHtml("message_gallery_xoutofy_2", $index, $last)."</div>";
    }

    // ------------------------------------------------------------------------------
    // Auslesen des übergebenen Galerieverzeichnisses, Rückgabe als Array
    // ------------------------------------------------------------------------------
    function getPicsAsArray($dir, $filetypes) {
        $picarray = array();
        $currentdir = opendir($dir);
        // Alle Dateien des übergebenen Verzeichnisses einlesen...
        while ($file = readdir($currentdir)){
            if(isValidDirOrFile($file) and (in_array(strtolower(substr($file,strrpos($file, "."))), $filetypes))) {
                // ...wenn alles passt, ans Bilder-Array anhängen
                $picarray[] = $file;
            }
        }
        closedir($currentdir);
        sort($picarray);
        return $picarray;
    }

    // ------------------------------------------------------------------------------
    // Hilfsfunktion: "title"-Attribut zusammenbauen (oder nicht, wenn nicht konfiguriert)
    // ------------------------------------------------------------------------------
    /*
    function getTitleAttribute($value) {
        global $CMS_CONF;
        if ($CMS_CONF->get("showsyntaxtooltips") == "true") {
            return " title=\"".$value."\"";
        }
        return "";
    }*/

    /***************************************************************
    * 
    * Gibt die Konfigurationsoptionen als Array zurück.
    * 
    ***************************************************************/
    function getConfig() {
        global $lang_gallery_admin;

        // Rückgabe-Array initialisieren
        // Das muß auf jeden Fall geschehen!
        $config = array();

        $config['usethumbs'] = array(
            "type" => "checkbox",
            "description" => $lang_gallery_admin->get("config_gallery_usethumbs"),
        );
        $config['target'] = array(
            "type" => "radio",
            "description" => $lang_gallery_admin->get("config_gallery_target"),
            "descriptions" => array(
                "_self" => $lang_gallery_admin->get("config_gallery_target_self"),
                "_blank" => $lang_gallery_admin->get("config_gallery_target_blank"),
                )
        );
        $config['gallerytemplate'] = array(
            "type" => "textarea",
            "cols" => "90",
            "rows" => "7",
            "description" => $lang_gallery_admin->get("config_gallery_placeholders"),
            "template" => "{gallerytemplate_description}<br /><br />{gallerytemplate_textarea}",
        );

        // Nicht vergessen: Das gesamte Array zurückgeben
        return $config;
    } // function getConfig    
    
    
    /***************************************************************
    * 
    * Gibt die Plugin-Infos als Array zurück
    *  
    ***************************************************************/
    
    
    function getInfo() {

        global $ADMIN_CONF;
        global $lang_gallery_admin;

        $dir = PLUGIN_DIR_REL."Galerie/";
        $language = $ADMIN_CONF->get("language");
        $lang_gallery_admin = new Properties($dir."sprachen/admin_language_".$language.".txt",false);
//         if(!isset($lang_gallery_admin->properties['readonly'])) {
//             die($lang_gallery_admin->properties['error']);
//         }

        $info = array(
            // Plugin-Name
            "<b>".$lang_gallery_admin->get("config_gallery_plugin_name")."</b> \$Revision: 144 $",
            // CMS-Version
            "2.0",
            // Kurzbeschreibung
            $lang_gallery_admin->get("config_gallery_plugin_desc"),
            // Name des Autors
           "mozilo",
            // Download-URL
            "",
            # Platzhalter => Kurzbeschreibung
            array('{Galerie|}' => $lang_gallery_admin->get("config_gallery_plugin_name")
            )
            );
            return $info;
    } // function getInfo

} // class plugin

?>
