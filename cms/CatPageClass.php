<?php if(!defined('IS_CMS')) die();
# die übergebenen variablen $cat und $page können in dieser form
# übergeben werden müssen aber urlcodiert sein (wie im filesystem)
#   nur name
#   mit endung
#   mit allen

#   Beispiel:
#   catpage
#   catpage.txt
#   link-_blank-http://www.test.de

class CatPageClass {
    # mit $CatPageArray wird gearbeitet
    var $CatPageArray = array();
    # $OrgCatPageArray ist für die widerherstelung zuständig z.B. undelete_Page()
    var $OrgCatPageArray = array();
    # $SyntaxIncludeRemember wird zur verhinderung von syntax_include endlosschleife und
    # um bei Syntaxelementen die in include page sind die richtige cat zu ermiteln
    var $SyntaxIncludeRemember = array();
    # bei cat page als link müssen diese zeichen wiederhergestelt werden
    # wird im ["_link-"] benutzt
#!!!!!!!!!!gibts das noch?
    var $link_search = array("%3A","%2F","%3F","%26","%3D","%23");
    var $link_replace = array(":","/","?","&amp;","=","#");

    var $ActionLinksearch;
    var $ActionLinksitemap;

    function __construct() {
        if(defined("isCatPage"))
            die("die class CatPage darf nur einmal initaliesiert werden");
        $this->CatPageArray = $this->make_DirCatPageArray(CONTENT_DIR_REL);
        $this->OrgCatPageArray = $this->CatPageArray;
        $this->ActionLinksearch = $this->get_Href(false,false,"action=search");
        $this->ActionLinksitemap = $this->get_Href(false,false,"action=sitemap");
        # nur der admin darf die neu einlessen
        if(!defined("IS_ADMIN") or !IS_ADMIN)
            define("isCatPage",true);
    }

    function get_FirstCatPage() {
        global $CMS_CONF;
        $pages = array(EXT_PAGE);
        if($CMS_CONF->get("showhiddenpagesasdefaultpage") == "true") {
            $pages = array(EXT_PAGE,EXT_HIDDEN);
        }
        $firstcat = $this->get_CatArray(false,false,$pages);
        reset($firstcat);
        $firstcat = current($firstcat);
        if($firstcat) {
            $firstpage = $this->get_PageArray($firstcat,$pages,true);
            reset($firstpage);
            $firstpage = current($firstpage);
            return array($firstcat,$firstpage);
        }
        return array("","");
    }

    function get_FirstPageOfCat($cat) {
        $cat = $this->get_AsKeyName($cat);
        global $CMS_CONF;
        $pages = array(EXT_PAGE);
        if($CMS_CONF->get("showhiddenpagesasdefaultpage") == "true") {
            $pages = array(EXT_PAGE,EXT_HIDDEN);
        }
        $firstpage = $this->get_PageArray($cat,$pages,true);
        reset($firstpage);
        $firstpage = current($firstpage);
        if($firstpage) {
            return $firstpage;
        }
        return false;
     }

    # Erzeugt ein durchnummeriertes array mit cats in richtiger Reihenfolge
    # $any = true # alle cats $containspage werden übergangen
    # $showlink = false # keine catlinks ins array
    # $containspage = array(ext.) # mit page ext. die enthalten sein müssen damit cat ins array geht
    # Default = array mit catlinks und cat mit midestens einer normalen page oder link
    function get_CatArray($any = false, $showlink = true, $containspage = false) {
        $return = array();
        # Default $containspage array erzeugen
        if(!$containspage or !is_array($containspage)) {
            $containspage = array(EXT_PAGE, EXT_LINK);
        }
        foreach($this->CatPageArray as $cat => $info) {
            # wenn cat ein Link ist und $showlink = true ist
            if($showlink and $info['_type-'] == EXT_LINK) {
                $return[] = $cat;
                continue;
            # wenn cat ein Link ist und $showlink = false ist
            } elseif(!$showlink and $info['_type-'] == EXT_LINK)
                continue;
            # alle cat
            if($any)
                $return[] = $cat;
            else {
                # nur cat zulassen wenn auch pages mit array($containspage) vorhanden sind
                if(count($this->get_PageArray($cat,$containspage,true)) > 0)
                    $return[] = $cat;
            }
        }
        return $return;
    }


    # Erzeugt ein durchnummeriertes array mit pages in richtiger Reihenfolge
    # $cat = cat
    # $showext = array() mit ext. die ins array solen
    # $hidecatnamedpages = true $CMS_CONF->get("hidecatnamedpages") wird übergangen
    # Default = alle pages der cat mit normalen page und links und $CMS_CONF->get("hidecatnamedpages")
    # wird benutzt
    function get_PageArray($cat,$showext = false,$hidecatnamedpages = false) {
        global $CMS_CONF;
        $hidenamedpage = false;
        if(!$hidecatnamedpages and $CMS_CONF->get("hidecatnamedpages") == "true")
            $hidenamedpage = true;
        $cat = $this->get_AsKeyName($cat);
        $return = array();
        # Default page arten erzeugen
        if(!$showext or !is_array($showext)) {
            $showext = array(EXT_PAGE, EXT_LINK);
        }
        if(isset($this->CatPageArray[$cat]['_pages-'])) {
            foreach($this->CatPageArray[$cat]['_pages-'] as $page => $info) {
                # wenn page art nicht in $showext array ist nächste page
                if(!in_array($info['_type-'],$showext))
                    continue;
                # wenn catname gleich pagename nächste page
                if($hidenamedpage and $cat == $page)
                    continue;
                $return[] = $page;
            }
        }
        return $return;
    }

    # gibt die files die im cat dateien ordner sind als array zurück
    # wenn nichts gefunden dann mit array() zurück
    # $only_ext = false oder array() dann werden nur die files zurückgeben die die extension haben
    # gross/kleinschreibung ist egal. Achtung mit Punkt angeben
    # z.B. array(".jpg",".png")
    function get_FileArray($cat,$only_ext = false) {
        $cat = $this->get_AsKeyName($cat);
        $return_array = array();
        # nur wens auch files array gibt
        if(isset($this->CatPageArray[$cat]['_files-'])) {
            # alle files zurück
            if($only_ext === false) {
                $return_array = $this->CatPageArray[$cat]['_files-'];
            # nur die files die extension haben die in $only_ext enthalten sind
            } elseif(is_array($only_ext) and count($only_ext) > 0) {
                # alle ext im array in kleinschreibung wandeln
                $only_ext = array_map('strtolower', $only_ext);
                foreach($this->CatPageArray[$cat]['_files-'] as $file) {
                    if(in_array($this->get_FileType($file),$only_ext))
                        $return_array[] = $file;
                }
            }
        }
        return $return_array;
    }

    # gibt die extension von $file kleingeschrieben zurück
    # es wird das als extension angesehen was nach dem letzten punk ist
    function get_FileType($file) {
        # ab denn letzen punkt ist die ext
        $type = substr($file,strrpos($file,"."));
        if(strlen($type) > 1)
            # kleingeschrieben zurück
            return strtolower($type);
        return false;
    }

    # Erzeugt einen HTML Link
    # $url = TEXT
    # $urltext = TEXT
    # $css = false oder TEXT
    # $titel = false oder TEXT
    # $target = false oder _blank, _self
    # $id = false oder TEXT
    function create_LinkTag($url,$urltext,$css = false,$titel = false,$target = false,$id = false) {
        global $syntax;
        $linkcss = NULL;
        # ist $css ein TEXT wird ein class atribut erzeugt ansonsten nicht
        if($css !== false)
            $linkcss = ' class="'.$css.'"';
        $linktitel = NULL;
        # ist $titel ein TEXT wird ein titel atribut erzeugt ansonsten nicht
        if($titel !== false)
            $linktitel = $syntax->getTitleAttribute($titel);
        $linktarget = NULL;
        if($target !== false and $target == "_blank")
            $linktarget = ' target="'.$target.'"';
        $linkid = NULL;
        if($id !== false)
            $linkid = ' id="'.$id.'"';
        return '<a href="'.$url.'"'.$linkcss.$linktitel.$linktarget.$linkid.'>'.$urltext.'</a>';
    }

    function create_ActionLinkTag($action,$cssprefix = "") {
        if($action != "search" and $action != "sitemap") return NULL;
        global $specialchars, $language, $SEARCH_REQUEST;

        $url = $this->{"ActionLink".$action};
        $add = "?";
        if(strpos($url,"?") > 1)
            $add = "&amp;";
        $requesturl = NULL;
        $lang = $language->getLanguageHtml("message_sitemap_0");
        if($action == "search") {
            if(strlen($SEARCH_REQUEST) > 0)
                $requesturl = $add."search=".$specialchars->replaceSpecialChars($SEARCH_REQUEST, false);
            $lang = $language->getLanguageHtml("message_searchresult_1", $specialchars->rebuildSpecialChars($SEARCH_REQUEST,false,true));
        }
        if(DRAFT) {
            if($requesturl === NULL)
                $requesturl = $add."draft=true";
            else
                $requesturl .= "&amp;draft=true";
        }
        return $this->create_LinkTag($url.$requesturl
                ,$lang
                ,$cssprefix
                ,false);
    }

    # erzeugt einen default link wie im menue mit default tooltips und setzt in automatisch activ
    # $css = css class, wenn cat oder page activ wird an die class ein "active" angehängt
    # $request = TEXT für url Parameter und alle & werden nach $amp; gewandelt
    function create_AutoLinkTag($cat,$page,$css,$request = false) {
        global $language;
        if($page !== false) {
            if($this->get_Type($cat,$page) == EXT_LINK) {
                $title = $language->getLanguageHtml("tooltip_link_extern_1", $this->get_HrefText($cat,$page));
                $target = $this->get_HrefTarget($cat,$page);
            } else {
                $title = $language->getLanguageHtml("tooltip_link_page_2", $this->get_HrefText($cat,$page),$this->get_HrefText($cat,false));
                $target = false;
            }
            return $this->create_LinkTag(
                    $this->get_Href($cat,$page,$request),
                    $this->get_HrefText($cat,$page),
                    $css.$this->get_CssActiv($cat,$page),
                    $title,$target);
        }
        if($this->get_Type($cat,false) == EXT_LINK) {
            $title = $language->getLanguageHtml("tooltip_link_extern_1", $this->get_HrefText($cat,false));
            $target = $this->get_HrefTarget($cat,false);
        } else {
            $title = $language->getLanguageHtml("tooltip_link_category_1", $this->get_HrefText($cat,false));
            $target = false;
        }
        return $this->create_LinkTag(
                $this->get_Href($cat,false,$request),
                $this->get_HrefText($cat,false),
                $css.$this->get_CssActiv($cat,false),
                $title,$target);
    }

    # gibt $activtext zurück wenn cat oder page activ ist
    # Default $activtext = active
    function get_CssActiv($cat,$page,$activtext = "active") {
        if($this->is_Activ($cat,$page))
            return $activtext;
        return NULL;
    }

    # gibt nur denn Namen zurück ohne Endungen, Linksachen
    # wie er in $this->CatPageArray steht
    # $name kann sein z.B.
    #       01_catpage
    #       01_catpage.txt
    #       01_link-_blank-http://www.test.de
    # $change_chars = true, es werden sonderzeichen und htmltities nach %?? gewandelt
    # Achtung $change_chars nur benutzen wenn nötig wegen geschwindigkeit
    function get_AsKeyName($name, $change_chars = false) {
        $ext = array(EXT_PAGE, EXT_HIDDEN, EXT_LINK, EXT_DRAFT);
        if(strpos($name,"-_self-") > 1)
            $name = substr($name,0,strpos($name,"-_self-"));
        if(strpos($name,"-_blank-") > 1)
            $name = substr($name,0,strpos($name,"-_blank-"));
        if(in_array(substr($name,-(EXT_LENGTH)),$ext))
            $name = substr($name,0,-(EXT_LENGTH));
        if($change_chars === true) {
            $name = $this->get_UrlCoded($name);
        }
        return $name;
    }

    # prüft ob $name text enthält also nicht boolean oder lehr ist
    # alle zeichen die trim() entfernt sind kein text
    function is_ParaString($name) {
        if($name === false or $name === true) {
            return false;
        }
        $name = trim($name);
        if(strlen($name) <= 0) {
            return false;
        }
        return true;
    }

    # wandelt $name von z.B. "Über uns" nach "%C3%9Cber%20uns"
    function get_UrlCoded($name,$protectUrlChr = false) {
        $name = html_entity_decode($name,ENT_QUOTES,CHARSET);
        if(preg_match('#\%([0-9a-f]{2})#i',$name) < 1) {
            $name = mo_rawurlencode(stripslashes($name));
        }

        if($protectUrlChr === true)
            $name = str_replace($this->link_search,$this->link_replace,$name);
        return $name;
    }

    function get_FileSystemName($cat,$page) {
        $cat = $this->get_AsKeyName($cat);
        if($page !== false) {
            $page = $this->get_AsKeyName($page);
            if(isset($this->CatPageArray[$cat]['_pages-'][$page])) {
                if($this->get_Type($cat,$page) == EXT_LINK) {
                    $link = str_replace($this->link_replace,$this->link_search,$this->CatPageArray[$cat]['_pages-'][$page]['_link-']);
                    return $page.'-'.$this->CatPageArray[$cat]['_pages-'][$page]['_target-'].'-'.$link.$this->CatPageArray[$cat]['_pages-'][$page]['_type-'];
                } else {
                    return $page.$this->CatPageArray[$cat]['_pages-'][$page]['_type-'];
                }
            } else
                return false;
        }
        if(isset($this->CatPageArray[$cat])) {
            if($this->get_Type($cat,false) == EXT_LINK) {
                $link = str_replace($this->link_replace,$this->link_search,$this->CatPageArray[$cat]['_link-']);
                return $cat.'-'.$this->CatPageArray[$cat]['_target-'].'-'.$link.$this->CatPageArray[$cat]['_type-'];
            } else {
                return $cat;
            }
        }
        return false;
    }

    # gibt anhand eines Syntaxelement cat:page (cat:page muss nicht rawurlencodet sein)
    # ein array($cat,$page) zurück
    # der Inhalt von array($cat,$page) ist filesystem konform formatiert
    # $syntax_catpage kann sein "nur page" oder "cat:page" wobei page auch eine datei
    # sein kann dann muss $file true sein
    # $file = optinal und muss true sein wenn page eine datei ist
    # bei nur page/file wird wenn vorhanden CAT_REQUEST genommen
    function split_CatPage_fromSyntax($syntax_catpage, $file = false) {
        $syntax_catpage = str_replace('-html_amp~','&',$syntax_catpage);
        $syntax_catpage = replaceFileMarker($syntax_catpage,false);
        $syntax_catpage = $this->get_AsKeyName($syntax_catpage, true);
        $syntax_catpage = str_replace(":","%3A",$syntax_catpage);
        $valuearray = explode("%3A", $syntax_catpage);
        # wenn cat:page/file oder in cat : enthalten ist
        if(count($valuearray) > 0) {
            for($i = 1;$i < (count($valuearray) + 1);$i++) {
                $cat = implode("%3A",array_slice($valuearray, 0,$i));
                $page = implode("%3A",array_slice($valuearray, $i));
                if($file === true) {
                    if($this->exists_File($cat,$page))
                        return array($cat,$page);
                } else {
                    if($this->exists_CatPage($cat,$page))
                        return array($cat,$page);
                    elseif(strlen($page) == 0 and $this->exists_CatPage($cat,false))
                        return array($cat,false);
                }
            }
        }
        # cat wurde nicht gefunden dann ist cat die CAT_REQUEST
        if(defined('CAT_REQUEST')) {
            if($file === false and $this->exists_CatPage(CAT_REQUEST,$syntax_catpage))
                return array(CAT_REQUEST,$syntax_catpage);
            elseif($file === true and $this->exists_File(CAT_REQUEST,$syntax_catpage))
                return array(CAT_REQUEST,$syntax_catpage);
        }
        return array(false,false);
    }

    function exists_CatPage($cat,$page) {
        $cat = $this->get_AsKeyName($cat);
        if($page !== false) {
            $page = $this->get_AsKeyName($page);
            if(isset($this->CatPageArray[$cat]['_pages-'][$page]))
                return true;
            else
                return false;
        }
        if(isset($this->CatPageArray[$cat]))
            return true;
        return false;
    }

    function is_Protectet($cat,$page) {
        $cat = $this->get_AsKeyName($cat);
        if($page !== false) {
            $page = $this->get_AsKeyName($page);
            if(isset($this->CatPageArray[$cat]['_pages-'][$page]['_protect-'])
                    and $this->CatPageArray[$cat]['_pages-'][$page]['_protect-'])
                return true;
            else
                return false;
        }
        if(isset($this->CatPageArray[$cat]['_protect-']) and $this->CatPageArray[$cat]['_protect-'])
            return true;
        return false;
    }

    function set_Protectet($cat,$page,$status = true) {
        if($status !== false and $status !== true)
            return false;
        $cat = $this->get_AsKeyName($cat);
        if($page !== false) {
            $page = $this->get_AsKeyName($page);
            if(isset($this->CatPageArray[$cat]['_pages-'][$page]['_protect-'])) {
                $this->CatPageArray[$cat]['_pages-'][$page]['_protect-'] = $status;
                return true;
            }
            return false;
        }
        if(isset($this->CatPageArray[$cat]['_protect-'])) {
            $this->CatPageArray[$cat]['_protect-'] = $status;
            return true;
        }
        return false;
    }

    function exists_File($cat,$file) {
        $cat = $this->get_AsKeyName($cat);
        if(isset($this->CatPageArray[$cat]['_files-'])) {
            $file = $this->get_UrlCoded($file);
            if(in_array($file,$this->CatPageArray[$cat]['_files-']))
                return true;
        }
        return false;
    }

    # gibt die art der cat page zurück
    # bei cat ist art = cat oder .lnk
    # bei page ist art = .txt, .hid, .tmp oder .lnk
    function get_Type($cat,$page) {
        $cat = $this->get_AsKeyName($cat);
        if($page !== false) {
            $page = $this->get_AsKeyName($page);
            if(isset($this->CatPageArray[$cat]['_pages-'][$page]['_type-']))
                return $this->CatPageArray[$cat]['_pages-'][$page]['_type-'];
            else
                return NULL;
        }
        if(isset($this->CatPageArray[$cat]['_type-']))
            return $this->CatPageArray[$cat]['_type-'];
        return NULL;
    }

    # gibt den Timstamp der cat page zurück
    function get_Time($cat,$page) {
        $cat = $this->get_AsKeyName($cat);
        if($page !== false) {
            $page = $this->get_AsKeyName($page);
            if(isset($this->CatPageArray[$cat]['_pages-'][$page]['_time-']))
                return $this->CatPageArray[$cat]['_pages-'][$page]['_time-'];
            else
                return NULL;
        }
        if(isset($this->CatPageArray[$cat]['_time-']))
            return $this->CatPageArray[$cat]['_time-'];
        return NULL;
    }

    # erzeugt einen Query String anhand $_SERVER['QUERY_STRING'] und $query
    # wenn $query ein String ist werden die keys die in $query sind
    # aus $_SERVER['QUERY_STRING'] rausgenommen fals vorhanden
    # alle & werden nach $amp; gewandelt
    function get_Query($query = false) {
        if($query === false)
            return $_SERVER['QUERY_STRING'];
        $uri_query = array();
        if(strlen($_SERVER['QUERY_STRING']) > 1)
            $uri_query = explode("&",$_SERVER['QUERY_STRING']);
        $uri = array();
        foreach($uri_query as $para) {
            $key = explode("=",$para);
            if(!isset($key[1]))
                $key[1] = "QUERY_STRING_DUMMY";
            $uri[$key[0]] = $key[1];
        }
        $query = str_replace("&amp;","&",$query);
        if($query[0] == "&")
            $query = substr($query,1);
        $query = explode("&",$query);
        $query_string = NULL;
        foreach($query as $para) {
            $query_string .= "&".$para;
            $key = explode("=",$para);
            if(isset($uri[$key[0]]))
                unset($uri[$key[0]]);
        }
        foreach($uri as $key => $para) {
            if($para == "QUERY_STRING_DUMMY")
                $query_string .= "&".$key;
            else
                $query_string .= "&".$key."=".$para;
        }
        if(DRAFT) {
            $query_string .= "&draft=true";
        }

        $query_string = substr($query_string,1);
        $query_string = str_replace("&","&amp;",$query_string);
        return $query_string;
    }

    # erzeugt eine url in abhängikeit von $CMS_CONF->get("modrewrite")
    # wenn $cat und $page = false oder nicht existieren wird nur index.php benutzt
    # $request = TEXT für url Parameter und alle & werden nach $amp; gewandelt
    function get_Href($cat,$page,$request = false) {
        global $CMS_CONF;
        global $specialchars;

        $requesturl = NULL;
        if($request !== false and strlen($request) > 0) {
            $request = str_replace("&amp;","&",$request);
            if($request[0] == "&")
                $request = substr($request,1);
            $request = str_replace("&","&amp;",$request);
            $requesturl = "?".$request;
        }

        if(DRAFT) {
            if($requesturl === NULL)
                $requesturl = "?draft=true";
            else
                $requesturl .= "&amp;draft=true";
        }

        if($cat !== false) {
            $cat = $this->get_AsKeyName($cat);
            # cat gibts nicht dann setzen wir auch $page auf false
            if(!isset($this->CatPageArray[$cat])) {
                $cat = false;
                $page = false;
            }
        }
        if($cat !== false and $page !== false) {
            $page = $this->get_AsKeyName($page);
            if(!isset($this->CatPageArray[$cat]['_pages-'][$page]))
                $page = false;
        }
        # wenn cat und page false sind
        if($cat === false and $page === false) {
            $dummy = ".php";
            if($CMS_CONF->get("modrewrite") == "true")
                $dummy = ".html";
            return URL_BASE."index".$dummy.$requesturl;
        }
        $cat = $this->get_AsKeyName($cat);
        # wenn cat ein link ist
        if($this->get_Type($cat,false) == EXT_LINK) {
            if(strlen($this->CatPageArray[$cat]['_link-']) > 3 and strpos($this->CatPageArray[$cat]['_link-'],"://") < 1)
                return "http://".$this->CatPageArray[$cat]['_link-'];
            else
                return $this->CatPageArray[$cat]['_link-'];
        }
        # wenn page ein link ist
        if($this->get_Type($cat,$page) == EXT_LINK) {
            if(strlen($this->CatPageArray[$cat]['_pages-'][$page]['_link-']) > 3 and strpos($this->CatPageArray[$cat]['_pages-'][$page]['_link-'],"://") < 1)
                return "http://".$this->CatPageArray[$cat]['_pages-'][$page]['_link-'];
            else
                return $this->CatPageArray[$cat]['_pages-'][$page]['_link-'];
        }
        $pageurl = NULL;
        $url = URL_BASE;
        if($CMS_CONF->get("modrewrite") == "true") {
            if($page !== false)
                $pageurl = "/".str_replace('%2F','/',$page);
#                $pageurl = "/".$page;
#                $pageurl = "/".str_replace('%2F','%252F',$page);
            $url .= str_replace('%2F','/',$cat).$pageurl.".html".$requesturl;
#            $url .= $cat.$pageurl.".html".$requesturl;
#            $url .= str_replace('%2F','%252F',$cat).$pageurl.".html".$requesturl;
        } else {
            if($request)
                $requesturl = "&amp;".$request;
            $caturl = "?cat=".$cat;
            if($page !== false)
                $pageurl = "&amp;page=".$page;
            $url .= "index.php".$caturl.$pageurl.$requesturl;
        }
        return $url;
    }

    # erzeugt eine url für Datei Download
    function get_HrefFile($cat,$datei,$force_download = false) {
        $cat = $this->get_FileSystemName($cat,false);
        if($cat !== false and $this->exists_File($cat,$datei)) {
            $open_dialog = "";
            if($force_download)
                $open_dialog = "&amp;dialog=true";
            $datei = $this->get_UrlCoded($datei);
            # Achtung vor Entfernen der # Prüfen ob es die cms/download.php gibt
#            return URL_BASE.CMS_DIR_NAME.'/download.php?cat='.$cat.'&amp;file='.$datei.$open_dialog;
            return URL_BASE."index.php?cat=".str_replace('%2F','/',$cat)."&amp;file=".$datei.$open_dialog;
        }
        return false;
    }

    # erzeugt eine url für alle tags die src= verwenden
    # $twice = true ist nur nötig für src von z.B. einem flashplayer
    function get_srcFile($cat,$file,$twice = false) {
        $cat = $this->get_FileSystemName($cat,false);
        if($cat !== false and $this->exists_File($cat,$file)) {
            global $specialchars;
            $file = $this->get_UrlCoded($file);
            $file = $specialchars->replaceSpecialChars(URL_BASE.CONTENT_DIR_NAME."/".$cat."/".CONTENT_FILES_DIR_NAME."/".$file,true);
            if($twice === true)
                $file = str_replace("%","%25",$file);;
            return $file;
        }
        return false;
    }

    # erzeugt den filesystem pfad der datei
    function get_pfadFile($cat,$file) {
        $cat = $this->get_FileSystemName($cat,false);
        if($cat !== false and $this->exists_File($cat,$file)) {
            return BASE_DIR.CONTENT_DIR_NAME."/".$cat."/".CONTENT_FILES_DIR_NAME."/".$this->get_UrlCoded($file);
        }
        return false;
    }

    # erzeugt einen Datei Link text
    function get_FileText($cat,$file) {
        $cat = $this->get_AsKeyName($cat);
        if($cat !== false and $this->exists_File($cat,$file)) {
            global $specialchars;
            return $specialchars->rebuildSpecialChars($file, true, true);
        }
        return NULL;
    }

    # gibt wenns ein link ist den target zurück ansonsten false
    function get_HrefTarget($cat,$page) {
        $cat = $this->get_AsKeyName($cat);
        if($page !== false) {
            $page = $this->get_AsKeyName($page);
            if(isset($this->CatPageArray[$cat]['_pages-'][$page]['_target-']))
                return $this->CatPageArray[$cat]['_pages-'][$page]['_target-'];
            else
                return false;
        }
        if(isset($this->CatPageArray[$cat]['_target-']))
            return $this->CatPageArray[$cat]['_target-'];
        return false;
    }

    # erzeugt einen Link text
    function get_HrefText($cat,$page) {
        global $specialchars;
        $cat = $this->get_AsKeyName($cat);
        if($page !== false) {
            $page = $this->get_AsKeyName($page);
            if(isset($this->CatPageArray[$cat]['_pages-'][$page]['_name-'])) {
                return $specialchars->rebuildSpecialChars($this->CatPageArray[$cat]['_pages-'][$page]['_name-'], true, true);
            } else
                return NULL;
        }
        if(isset($this->CatPageArray[$cat]['_name-'])) {
            return $specialchars->rebuildSpecialChars($this->CatPageArray[$cat]['_name-'], true, true);
        }
        return NULL;
    }

    function is_Activ($cat,$page) {
        $req_cat = $this->get_AsKeyName(CAT_REQUEST);
        $cat = $this->get_AsKeyName($cat);
        if($page !== false) {
            $page = $this->get_AsKeyName($page);
            $req_page = $this->get_AsKeyName(PAGE_REQUEST);
            if($cat == $req_cat and $page == $req_page and $this->get_Type($cat,$page) != EXT_LINK)
                return true;
            return false;
        }
        if($cat == $req_cat and $this->get_Type($cat,false) != EXT_LINK)
            return true;
        return false;
    }

    # ändert denn Namen der von get_HrefText() ausgegeben wird
    function change_Name($cat,$page,$newname) {
        # prüfen ob $newname ein text ist
        if($this->is_ParaString($newname)) {
            $cat = $this->get_AsKeyName($cat);
            if($page !== false) {
                $page = $this->get_AsKeyName($page);
                if(isset($this->CatPageArray[$cat]['_pages-'][$page]['_name-'])) {
                    $this->CatPageArray[$cat]['_pages-'][$page]['_name-'] = $newname;
                    $this->OrgCatPageArray[$cat]['_pages-'][$page]['_name-'] = $newname;
                    return true;
                } else {
                    return false;
                }
            }
            if(isset($this->CatPageArray[$cat]['_name-'])) {
                $this->CatPageArray[$cat]['_name-'] = $newname;
                $this->OrgCatPageArray[$cat]['_name-'] = $newname;
                return true;
            }
            return false;
        }
        return false;
    }

    # stellt den Original Namen wieder her
    function unchange_Name($cat,$page) {
        $cat = $this->get_AsKeyName($cat);
        if($page !== false) {
            $page = $this->get_AsKeyName($page);
            if(isset($this->OrgCatPageArray[$cat]['_pages-'][$page]['_orgname-'])) {
                # fals delete_Page() benutz wurde
                if(isset($this->CatPageArray[$cat]['_pages-'][$page]['_orgname-']))
                    $this->CatPageArray[$cat]['_pages-'][$page]['_name-'] = $this->CatPageArray[$cat]['_pages-'][$page]['_orgname-'];
                $this->OrgCatPageArray[$cat]['_pages-'][$page]['_name-'] = $this->OrgCatPageArray[$cat]['_pages-'][$page]['_orgname-'];
                return true;
            } else
                return false;
        }
        if(isset($this->OrgCatPageArray[$cat]['_orgname-'])) {
            # fals delete_Cat() benutz wurde
            if(isset($this->CatPageArray[$cat]['_orgname-']))
                $this->CatPageArray[$cat]['_name-'] = $this->CatPageArray[$cat]['_orgname-'];
            $this->OrgCatPageArray[$cat]['_name-'] = $this->OrgCatPageArray[$cat]['_orgname-'];
            return true;
        }
        return false;
    }

    function delete_Cat($cat) {
        $cat = $this->get_AsKeyName($cat);
        if(isset($this->CatPageArray[$cat])) {
            unset($this->CatPageArray[$cat]);
            return true;
        }
        return false;
    }

    function delete_Page($cat,$page) {
        $cat = $this->get_AsKeyName($cat);
        $page = $this->get_AsKeyName($page);
        if(isset($this->CatPageArray[$cat]['_pages-'][$page])) {
            unset($this->CatPageArray[$cat]['_pages-'][$page]);
            return true;
        }
        return false;
    }

    function undelete_Cat($cat,$includepage = true) {
        $cat = $this->get_AsKeyName($cat);
        $tmp_array = array();
        $undelete = false;
        foreach($this->OrgCatPageArray as $cattmp => $inhalt) {
            if(isset($this->CatPageArray[$cattmp])) {
                $tmp_array[$cattmp] = $this->CatPageArray[$cattmp];
            } elseif($cattmp == $cat) {
                $tmp_array[$cat] = $this->OrgCatPageArray[$cat];
                if(!$includepage and isset($tmp_array[$cat]['_pages-']))
                    unset($tmp_array[$cat]['_pages-']);
                $undelete = true;
            }
        }
        if($undelete)
            $this->CatPageArray = $tmp_array;
        return $undelete;
    }

    function undelete_Page($cat,$page) {
        $cat = $this->get_AsKeyName($cat);
        $page = $this->get_AsKeyName($page);
        $tmp_array = array();
        $undelete = false;
        foreach($this->OrgCatPageArray[$cat]['_pages-'] as $pagetmp => $inhalt) {
            if(isset($this->CatPageArray[$cat]['_pages-'][$pagetmp])) {
                $tmp_array[$pagetmp] = $this->CatPageArray[$cat]['_pages-'][$pagetmp];
            } elseif($pagetmp == $page) {
                $tmp_array[$pagetmp] = $this->OrgCatPageArray[$cat]['_pages-'][$page];
                $undelete = true;
            }
        }
        if($undelete)
            $this->CatPageArray[$cat]['_pages-'] = $tmp_array;
        return $undelete;
    }

    function get_PageContent($cat,$page,$for_syntax = false,$convert_content = false) {
        $cat = $this->get_AsKeyName($cat);
        $page = $this->get_AsKeyName($page);

        if($this->CatPageArray[$cat]['_protect-']) {
            return false;
        }
        if($this->CatPageArray[$cat]['_pages-'][$page]['_protect-']) {
            return false;
        }
        # wenn das nee Vituelle page ist
        if(isset($this->CatPageArray[$cat]['_pages-'][$page]["_content-"]) and $this->CatPageArray[$cat]['_pages-'][$page]["_content-"]) {
            $page_content = $this->CatPageArray[$cat]['_pages-'][$page]["_content-"];
            if($for_syntax and !$convert_content) {
                global $syntax;
                $page_content = $syntax->preparePageContent($page_content);
            }
            if($convert_content) {
                $mysyntax = new Syntax();
                $page_content = $mysyntax->convertContent($page_content, $for_syntax);
            }
            return $page_content;
        }

        $cat = $this->get_FileSystemName($cat,false);
        $page = $this->get_FileSystemName($cat,$page);
        if($this->get_Type($cat,$page) != EXT_LINK) {
            if(file_exists(CONTENT_DIR_REL.$cat.'/'.$page)) {
                $page_content = file_get_contents(CONTENT_DIR_REL.$cat.'/'.$page);
                global $page_protect_search;
                $page_content = str_replace($page_protect_search,"",$page_content);
                if($for_syntax and !$convert_content) {
                    global $syntax;
                    $page_content = $syntax->preparePageContent($page_content);
                }
                if($convert_content) {
                    $mysyntax = new Syntax();
                    $page_content = $mysyntax->convertContent($page_content, $for_syntax);
                }
                return $page_content;
            }
        }
        return false;
    }

    # erzeugen einer Kategorie die es nicht gibt
    # $cat muss nicht url codiert sein
    function make_DummyCat($cat) {
        $cat = $this->get_AsKeyName($cat, true);
        if($this->exists_CatPage($cat,false))
            return false;
        $this->CatPageArray[$cat]['_pages-'] = array();
        $this->CatPageArray[$cat]["_name-"] = $cat;
        $this->CatPageArray[$cat]["_orgname-"] = $cat;
        $this->CatPageArray[$cat]["_type-"] = "cat";
        $this->CatPageArray[$cat]["_files-"] = array();
        $this->CatPageArray[$cat]["_time-"] = "1";
        $this->CatPageArray[$cat]["_protect-"] = false;
        $this->OrgCatPageArray[$cat] = $this->CatPageArray[$cat];
        return true;
    }

    # erzeugen in einer Kategorie eine Inhaltseite die es nicht gibt
    # $cat und $page muss nicht url codiert sein
    # $type = EXT_PAGE, EXT_HIDDEN oder EXT_DRAFT
    # $content = da es die Inhaltsseite nicht gibt kann das der Inhalt der Inhaltsseit sein
    #            wird dann ausgegenen mit get_PageContent()
    #            Achtung das solte nur ein Plugin Platzhalter sein
    function make_DummyPage($cat,$page,$type = EXT_PAGE,$content = false) {
        if($type != EXT_PAGE and $type != EXT_HIDDEN and $type != EXT_DRAFT)
            return false;
        $cat = $this->get_AsKeyName($cat, true);
        $page = $this->get_AsKeyName($page, true);
        if($this->exists_CatPage($cat,$page))
            return false;
        $this->CatPageArray[$cat]['_pages-'][$page]["_name-"] = $page;
        $this->CatPageArray[$cat]['_pages-'][$page]["_orgname-"] = $page;
        $this->CatPageArray[$cat]['_pages-'][$page]["_type-"] = $type;
        $this->CatPageArray[$cat]['_pages-'][$page]["_time-"] = "1";
        $this->CatPageArray[$cat]['_pages-'][$page]["_protect-"] = false;
        $this->CatPageArray[$cat]['_pages-'][$page]["_content-"] = $content;
        $this->OrgCatPageArray[$cat]['_pages-'][$page] = $this->CatPageArray[$cat]['_pages-'][$page];
        return true;
    }

###############################################################################
# Ab hier die functionen die nur von der function CatPage() verwendet werden dürfen
###############################################################################
    private function make_DirPageArray($dir) {
        $page_a = array();
        $page_sort = array();
        $currentdir = getDirAsArray($dir,"file","sort_cat_page");
        foreach($currentdir as $file) {
            if(!DRAFT and substr($file, -(EXT_LENGTH)) == EXT_DRAFT)
                continue;
            if(substr($file, -(EXT_LENGTH)) == EXT_LINK) {
                $target = "-_blank-";
                if(strpos($file,"-_self-") > 1)
                    $target = "-_self-";
                $tmp = explode($target,$file);
                $page_a[$tmp[0]]["_name-"] = $tmp[0];
                $page_a[$tmp[0]]["_orgname-"] = $page_a[$tmp[0]]["_name-"];
                $page_a[$tmp[0]]["_type-"] = EXT_LINK;
                $url = str_replace($this->link_search,$this->link_replace,substr($tmp[1],0,strlen($tmp[1])-(EXT_LENGTH)));
                $page_a[$tmp[0]]["_link-"] = $url;
                $page_a[$tmp[0]]["_target-"] = str_replace("-","",$target);
            } else {
                $key = substr($file,0,strlen($file)-(EXT_LENGTH));
                $page_a[$key]["_name-"] = $key;
                $page_a[$key]["_orgname-"] = $page_a[$key]["_name-"];
                $page_a[$key]["_type-"] = substr($file,-(EXT_LENGTH));
                $page_a[$key]["_time-"] = filemtime($dir."/".$file);
                $page_a[$key]["_protect-"] = false;
            }
        }
        return $page_a;
    }

    private function make_DirCatPageArray($dir) {
        global $CMS_CONF;
        $draft_modus = false;
        $draft_cat = "";
        if(!IS_ADMIN and getRequestValue('draft') != "true" and $CMS_CONF->get("draftmode") == "true") {
            $draft_modus = true;
            $draft_cat = $CMS_CONF->get("defaultcat");
        }
        $cat_a = array();
        $cat_sort = array();
        $currentdir = getDirAsArray($dir,"dir","sort_cat_page");
        foreach($currentdir as $file) {
            if($draft_modus and $draft_cat != $file)
                continue;
            if(substr($file, -(EXT_LENGTH)) == EXT_LINK) {
                $target = "-_blank-";
                if(strpos($file,"-_self-") > 1)
                    $target = "-_self-";
                $tmp = explode($target,$file);
                $cat_a[$tmp[0]]["_name-"] = $tmp[0];
                $cat_a[$tmp[0]]["_orgname-"] = $cat_a[$tmp[0]]["_name-"];
                $cat_a[$tmp[0]]["_type-"] = EXT_LINK;
                $url = str_replace($this->link_search,$this->link_replace,substr($tmp[1],0,strlen($tmp[1])-(EXT_LENGTH)));
                $cat_a[$tmp[0]]["_link-"] = $url;
                $cat_a[$tmp[0]]["_target-"] = str_replace("-","",$target);
            } else {
                $cat_a[$file]['_pages-'] = $this->make_DirPageArray($dir."/".$file);
                $cat_a[$file]["_name-"] = $file;
                $cat_a[$file]["_orgname-"] = $cat_a[$file]["_name-"];
                $cat_a[$file]["_type-"] = "cat";
                $cat_a[$file]["_files-"] = getDirAsArray($dir."/".$file."/".CONTENT_FILES_DIR_NAME,"file");
                $cat_a[$file]["_time-"] = filemtime($dir."/".$file);
                $cat_a[$file]["_protect-"] = false;
            }
        }
        return $cat_a;
    }
}
?>