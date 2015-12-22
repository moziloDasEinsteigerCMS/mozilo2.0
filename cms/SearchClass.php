<?php if(!defined('IS_CMS')) die();

class SearchClass {
    var $chars_to_lower;
    var $placeholder;
    var $phrasearray;
    var $hidecatnamedpages;
    var $showhiddenpagesinsearch;
    var $nosearchwords;
    var $searchnoresult;
    function __construct($placeholder = "SEARCH") {
        global $HIGHLIGHT_REQUEST;
        global $SEARCH_REQUEST;
        global $language;
        global $CMS_CONF;
        $this->setLowerChars();
        $this->placeholder = $placeholder;
        if(strlen($HIGHLIGHT_REQUEST) > 1)
            $this->phrasearray = $this->makePhraseArray($HIGHLIGHT_REQUEST);
        else
            $this->phrasearray = $this->makePhraseArray($SEARCH_REQUEST);

        $this->hidecatnamedpages = $CMS_CONF->get("hidecatnamedpages");
        $this->showhiddenpagesinsearch = $CMS_CONF->get("showhiddenpagesinsearch");

        $this->nosearchwords = $language->getLanguageValue("message_searchhelp_0");
        $query = str_replace(array('"',"'","[","]","{","}"),array("&quot;","&apos;","&#091;","&#093;","&#123;","&#125;"),trim(rawurldecode($SEARCH_REQUEST)));
        $this->searchnoresult = $language->getLanguageValue("message_searchnoresult_1", $query);
    }

    function setLowerChars() {
        global $language;
        # wenn in der lang datei noch buchstaben sind
        $search_chars_hi = $language->getLanguageValue("_search_chars_hi");
        $search_chars_lo = $language->getLanguageValue("_search_chars_lo");
        # es ist nur ein Buchstabe
        if(strlen($search_chars_hi) == 1 and strlen($search_chars_lo) == 1) {
            $search_chars_hi = ",".$search_chars_hi;
            $search_chars_lo = ",".$search_chars_lo;
        # es sind mehrere ein Buchstaben dann mus der 2te ein komma sein
        } elseif(strlen($search_chars_hi) > 0 and strlen($search_chars_lo) > 0
                and $search_chars_hi[1] == "," and $search_chars_lo[1] == ",") {
            $search_chars_hi = ",".$search_chars_hi;
            $search_chars_lo = ",".$search_chars_lo;
        } else {
            $search_chars_hi = "";
            $search_chars_lo = "";
        }
        $lo_str = "a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,à,á,â,ã,ä,å,æ,ç,è,é,ê,ë,ì,í,î,ï,ð,ñ,ò,ó,ô,õ,ö,ø,ù,ú,û,ü,ý,а,б,в,г,д,е,ё,ж,з,и,й,к,л,м,н,о,п,р,с,т,у,ф,х,ц,ч,ш,щ,ъ,ы,ь,э,ю,я".$search_chars_lo;
        $hi_str = "A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,À,Á,Â,Ã,Ä,Å,Æ,Ç,È,É,Ê,Ë,Ì,Í,Î,Ï,Ð,Ñ,Ò,Ó,Ô,Õ,Ö,Ø,Ù,Ú,Û,Ü,Ý,А,Б,В,Г,Д,Е,Ё,Ж,З,И,Й,К,Л,М,Н,О,П,Р,С,Т,У,Ф,Х,Ц,Ч,Ш,Щ,Ъ,Ъ,Ь,Э,Ю,Я".$search_chars_hi;
        $lo_str = explode(",",$lo_str);
        $hi_str = explode(",",$hi_str);
        $this->chars_to_lower = array();
        foreach($hi_str as $pos => $key) {
            if(isset($lo_str[$pos]))
                $this->chars_to_lower[$key] = $lo_str[$pos];
        }
    }

    function lowercase($s) {
        return strtr($s, $this->chars_to_lower);
    }

    function makePhraseArray($phrase) {
        $searchstring = $this->lowercase(rawurldecode($phrase));
        $return_array = array();
        if(trim($searchstring) != "") {
            # Leerzeichen die in einer Umklammerung sind zu %20 wandeln
            $string = preg_replace_callback(
                        "/[\"|\'](.*)[\"|\']/Umsi",
                        array($this,"callback_makePhraseArray"),
                        $searchstring
                      );
            $matches = explode(" ",$string);
            foreach($matches as $string) {
                $string = str_replace("%20"," ",trim($string));
                if(!empty($string)) {
                    $return_array[] = $string;
                }
            }
        }
        return $return_array;
    }

    function callback_makePhraseArray($arr) {
        return str_replace(" ","%20",$arr[1]);
    }

    # die standart mozilo function
    function searchInPages() {
        return $this->getFindPagesMenuList($this->getFindPageArray());
    }

    function getFindPageArray() {
        global $CatPage;
        if(count($this->phrasearray) < 1)
            return array();
        $include_pages = array(EXT_PAGE);
        if($this->showhiddenpagesinsearch == "true")
            $include_pages = array(EXT_PAGE,EXT_HIDDEN);

        $matchingpages = array();
        // Alle Kategorien durchsuchen
        foreach($CatPage->get_CatArray(false,false,$include_pages) as $cat) {
            // Alle Inhaltsseiten durchsuchen
            foreach($CatPage->get_PageArray($cat,$include_pages,true) as $page) {
                // Treffer in der aktuellen Seite?
                if($this->findInPage($cat,$page)) {
                    $matchingpages[$cat][$page] = "true";
                }
            }
        }
        return $matchingpages;
    }

    function getFindPagesMenuList($matchingpages = array(),$searchquery = false,$css = "searchmap") {
        global $CatPage, $SEARCH_REQUEST;

        if(count($this->phrasearray) < 1)
            return $this->nosearchwords;

        // Keine Inhalte gefunden?
        if(count($matchingpages) < 1)
            return $this->searchnoresult;

        $hidepage = false;
        if($this->hidecatnamedpages == "true")
            $hidepage = true;

        $searchresults = '<ul class="'.$css.'">';
        foreach($matchingpages as $cat => $tmp) {
            $searchresults .= "<li>";
            $catname = $CatPage->get_HrefText($cat,false);
            if($hidepage and isset($matchingpages[$cat][$cat])) {
                $searchresults .= "<h2>".$this->makeSearchMenuLink($cat,$cat,$searchquery)."</h2>";
                unset($matchingpages[$cat][$cat]);
            } else
                $searchresults .= "<h2>$catname</h2>";
            if(count($matchingpages[$cat]) > 0)
                $searchresults .= "<ul>";
            foreach($matchingpages[$cat] as $page => $tmp) {
                $pagename = $CatPage->get_HrefText($cat,$page);
                $searchresults .= "<li>".$this->makeSearchMenuLink($cat,$page,$searchquery)."</li>";
            }
            if(count($matchingpages[$cat]) > 0)
                $searchresults .= "</ul>";
            $searchresults .= "</li>";
        }
        // Rueckgabe des Menues
        return $searchresults."</ul>";
    }

    function makeSearchMenuLink($cat,$page,$searchquery = false) {
        global $CatPage, $specialchars, $language, $SEARCH_REQUEST;
        $serach = "";
        $serach_request = $specialchars->replaceSpecialChars($SEARCH_REQUEST,false);
        if($searchquery and strlen($SEARCH_REQUEST) > 0)
            $serach = "&search=".$serach_request;
        $catname = $CatPage->get_HrefText($cat,false);
        if(!$page) {
            return $CatPage->create_LinkTag(
                        $CatPage->get_Href($cat,false,"highlight=".$serach_request.$serach),
                        $this->highlightSearchString($catname),
                        false,
                        $language->getLanguageHtml("tooltip_link_page_2", $catname, $catname)
                    );

        } else {
            $pagename = $CatPage->get_HrefText($cat,$page);
            return $CatPage->create_LinkTag(
                        $CatPage->get_Href($cat,$page,"highlight=".$serach_request.$serach),
                        $this->highlightSearchString($pagename),
                        false,
                        $language->getLanguageHtml("tooltip_link_page_2", $pagename, $catname)
                    );
        }
    }

    function findInPage($cat,$page) {
        global $CatPage;

        // Dateiinhalt auslesen, wenn vorhanden...
        if(false !== ($pagecontent = $CatPage->get_PageContent($cat,$page))) {
            if(strlen($pagecontent) < 1)
            return false;

            # den eigenen Placholder raus nehmen sonst endlosschleife
            $pagecontent = preg_replace("/\{".$this->placeholder."\|(.*)\}/m","", $pagecontent);
            $pagecontent = str_replace("{".$this->placeholder."}","",$pagecontent);
            $tmp_syntax = new Syntax();
            $pagecontent = $tmp_syntax->convertContent($pagecontent, true);

            # alle Komentare raus
            $pagecontent = preg_replace("/\<!--(.*)-->/Umsi"," ", $pagecontent);
            # alle script, select, object, embed sachen raus
            $pagecontent = preg_replace("/\<script(.*)\<\/script>/Umsi", "", $pagecontent);
            $pagecontent = preg_replace("/\<select(.*)\<\/select>/Umsi", "", $pagecontent);
            $pagecontent = preg_replace("/\<object(.*)\<\/object>/Umsi", "", $pagecontent);
            $pagecontent = preg_replace("/\<embed(.*)\<\/embed>/Umsi", "", $pagecontent);
            # alle tags raus
            $pagecontent = strip_tags($pagecontent);
            $pagecontent = $this->lowercase($pagecontent);
            # nach alle Suchbegrieffe suchen
            foreach($this->phrasearray as $phrase) {
                if($phrase == "")
                    continue;
                // Wenn...
                if(
                    // ...der aktuelle Suchbegriff im Seitennamen...
                    (substr_count($this->lowercase($CatPage->get_HrefText($cat,$page)), $phrase) > 0)
                    // ...oder im eigentlichen Seiteninhalt vorkommt
                    or (substr_count($pagecontent, $phrase) > 0)
                    ) {
                    // gefunden
                    return true;
                }
            }
        } else
            return false;
    }

    // ------------------------------------------------------------------------------
    // Phrasen in Inhalt hervorheben
    // ------------------------------------------------------------------------------
    function highlightSearch($content) {
        global $syntax;
        # in $syntax den content setzen
        $syntax->content = $content;
        # alle script style sachen mit dumy ersetzen
        $syntax->find_script_style();
        $syntax->content = $this->highlightSearchString($syntax->content);
        # alle script style sachen wieder einsetzen
        $syntax->find_script_style(false);
        # inhalt zurück
        return $syntax->content;
    }

    function highlightSearchString($content) {
        // jeden Begriff highlighten
        foreach($this->phrasearray as $phrase) {
            // Regex-Zeichen im zu highlightenden Text escapen (.\+*?[^]$(){}=!<>|:)
            $phrase = preg_quote($phrase);
            // Slashes im zu highlightenden Text escapen
            $phrase = preg_replace("/\//", "\\\\/", $phrase);
            # die such worte hervorheben
            while(preg_match('/(>|^)[^<]*(?<!\<span class\="highlight"\>)'.$phrase.'(?!\<\/span\>)[^>]*(<|$)/isU', $content))
                $content = preg_replace('/(^[^<]*|>[^<]*)(?<!\<span class\="highlight"\>)('.$phrase.')(?!\<\/span\>)([^>]*<|[^>]*$)/isU', '${1}<span class="highlight">${2}</span>${3}', $content);

        }
        return $content;
    }

    function getSearchForm() {
        global $language, $LAYOUT_DIR_URL, $CatPage, $SEARCH_REQUEST;
        $draft = '';
        if(DRAFT)
            $draft = '<input type="hidden" name="draft" value="true" />';
        $query = str_replace(array('"',"'","[","]","{","}"),array("&quot;","&apos;","&#091;","&#093;","&#123;","&#125;"),trim(rawurldecode($SEARCH_REQUEST)));
        return '<form accept-charset="'.CHARSET.'" method="get" action="'.$CatPage->get_Href(false,false).'" class="searchform">'
                .'<fieldset id="searchfieldset">'
                .$draft
                .'<input type="hidden" name="action" value="search" />'
                .'<input type="text" name="search" value="'.$query.'" class="searchtextfield" />'
                .'<input type="image" src="'.$LAYOUT_DIR_URL.'/grafiken/searchicon.gif" alt="'.$language->getLanguageHtml("message_search_0").'" class="searchbutton" />'
                .'</fieldset>'
                .'</form>';
    }

    function getUserSearchForm($cat,$page,$id_class,$icon = false,$inputs_array = array()) {
        global $SEARCH_REQUEST,$CMS_CONF,$CatPage;
        if(DRAFT)
            $inputs_array["draft"] = "true";
        $inputs = '<input type="hidden" name="action" value="search" />';
        if($CMS_CONF->get("modrewrite") == "true") {
            if($cat !== false)
                $inputs_array["cat"] = $cat;
            if($page !== false)
                $inputs_array["page"] = $page;
        }
        foreach($inputs_array as $name => $value) {
            if($name == "action" and $value == "search") continue;
            $inputs .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
        }
        $in_icon = '';
        if($icon) { # string "src des icons" ,"default"
            global $language, $LAYOUT_DIR_URL;
            if($icon == "default")
                $in_icon = '<input type="image" src="'.$LAYOUT_DIR_URL.'/grafiken/searchicon.gif" alt="'.$language->getLanguageHtml("message_search_0").'" class="'.$id_class.'button" />';
            else
                $in_icon = '<input type="image" src="'.$icon.'" alt="'.$language->getLanguageHtml("message_search_0").'" class="'.$id_class.'button" />';
        }
        $query = str_replace(array('"',"'"),array("&quot;","&apos;"),trim(rawurldecode($SEARCH_REQUEST)));
        return '<form accept-charset="'.CHARSET.'" method="get" action="'.$CatPage->get_Href($cat,$page).'" class="'.$id_class.'form">'
                .'<fieldset id="'.$id_class.'fieldset">'
                .$inputs
                .'<input type="text" name="search" value="'.$query.'" class="'.$id_class.'textfield" />'
                .$in_icon
                .'</fieldset>'
                .'</form>';
    }
}
?>