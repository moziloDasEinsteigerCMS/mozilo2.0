<?php if(!defined('IS_CMS')) die();

define("CMSVERSION","2.0");
define("CMSNAME","Amalia");
define("CMSREVISION","51");

#!!!!!!!! die version müssen wir noch checken
define("MIN_PHP_VERSION","5.1.2");

define("PACK_JS",true);
define("PACK_CSS",true);
define("ADMIN_JQUERY","1.8.3");
define("ADMIN_JQUERY_UI","1.9.2");
define("JQUERY","1.7.2");
define("JQUERY_UI","1.9.2");

define("CHARSET","UTF-8");
define("CONTENT_DIR_NAME","kategorien");
define("CONTENT_FILES_DIR_NAME","dateien");
define("PLUGIN_DIR_NAME","plugins");
define("GALLERIES_DIR_NAME","galerien");
define("PREVIEW_DIR_NAME","vorschau");
define("LAYOUT_DIR_NAME","layouts");
define("LANGUAGE_DIR_NAME","sprachen");
define("CONF_DIR_NAME","conf");
define("BACKUP_DIR_NAME","backup");
// Dateiendungen fuer Inhaltsseiten
# Achtung die endungen muessen alle gleich lang sein
define("EXT_PAGE",".txt.php");
define("EXT_HIDDEN",".hid.php");
define("EXT_DRAFT",".tmp.php");
define("EXT_LINK",".lnk.php");
define("EXT_LENGTH",strlen(EXT_PAGE));

define("FILE_START","@=");
define("FILE_END","=@");

define("BASE_DIR_CMS",BASE_DIR.CMS_DIR_NAME."/");
define("CONTENT_DIR_REL",BASE_DIR.CONTENT_DIR_NAME."/");
define("GALLERIES_DIR_REL",BASE_DIR.GALLERIES_DIR_NAME."/");
define("PLUGIN_DIR_REL",BASE_DIR.PLUGIN_DIR_NAME."/");
define("SORT_CAT_PAGE",BASE_DIR.CONTENT_DIR_NAME."/SortCatPage.php");

// Punycode-URLs können beliebige Zeichen im Domainnamen enthalten!
define("MAIL_REGEX",'/^([^\s@,:"<>]+)@([^\s@,:"<>]+\.[^\s@,:"<>.\d]{2,40}|(\d{1,3}\.){3}\d{1,3})$/');

global $ALOWED_IMG_ARRAY;
$ALOWED_IMG_ARRAY = array(".png",".jpg",".jpeg",".gif",".ico");

# Um Cross-Site Scripting-Schwachstellen zu verhindern
$_SERVER["SCRIPT_NAME"] = htmlspecialchars($_SERVER["SCRIPT_NAME"], ENT_QUOTES, CHARSET);
$_SERVER["REQUEST_URI"] = htmlspecialchars($_SERVER["REQUEST_URI"], ENT_QUOTES, CHARSET);
if(isset($_SERVER["SCRIPT_URL"]))
    $_SERVER["SCRIPT_URL"] = htmlspecialchars($_SERVER["SCRIPT_URL"], ENT_QUOTES, CHARSET);
if(isset($_SERVER["SCRIPT_URI"]))
    $_SERVER["SCRIPT_URI"] = htmlspecialchars($_SERVER["SCRIPT_URI"], ENT_QUOTES, CHARSET);

if(!empty($_SERVER['HTTPS']) and $_SERVER['HTTPS'] !== 'off')
    define("HTTP","https://");
else
    define("HTTP","http://");

$plus_search = "";
if(defined('ADMIN_DIR_NAME'))
    $plus_search = ADMIN_DIR_NAME."/";
$URL_BASE = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], $plus_search."index.php"));
if(defined('IS_INSTALL'))
    $URL_BASE = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], "install.php"));
$URL_BASE = htmlentities($URL_BASE, ENT_COMPAT,CHARSET);
define("URL_BASE", $URL_BASE);
unset($URL_BASE);

# Inhaltseiten Schutz und conf dateien schutz
$page_protect_search = array("<?php die(); ?>\r\n","<?php die(); ?>\r","<?php die(); ?>\n");
$page_protect = "<?php die(); ?>\n";

# das ist nur wegen update drin fligt irgendwann raus
if(is_file(BASE_DIR_CMS."SortCatPage.php") and !is_file(SORT_CAT_PAGE))
    rename(BASE_DIR_CMS."SortCatPage.php",SORT_CAT_PAGE);
if(is_file(SORT_CAT_PAGE)) {
    include_once(SORT_CAT_PAGE);
    global $cat_page_sort_array;
}

# Alle Platzhalter
function makePlatzhalter($all = false) {
    # Alle Platzhalter für die Selctbox im Editor als array
    $platzhalter = array(
                        '{BASE_URL}',
                        '{CATEGORY_NAME}',
                        '{CATEGORY}',
                        '{CATEGORY_URL}',
                        '{PAGE_NAME}',
                        '{PAGE_FILE}',
                        '{PAGE_URL}',
                        '{PAGE}',
                        '{SEARCH}',
                        '{SITEMAPLINK}',
                        '{CMSINFO}',
                        '{TABLEOFCONTENTS}'
    );
    # Die Restlichen Platzhalter
    $platzhalter_rest = array(
                        '{CHARSET}',
                        '{LAYOUT_DIR}',
                        '{WEBSITE_TITLE}',
                        '{WEBSITE_KEYWORDS}',
                        '{WEBSITE_DESCRIPTION}',
                        '{WEBSITE_NAME}',
                        '{MAINMENU}',
                        '{DETAILMENU}',
                        '{MEMORYUSAGE}',
                        '{EXECUTETIME}',
                        '{JQUERY}'
    );

    if($all) {
        $platzhalter = array_merge($platzhalter,$platzhalter_rest);
    }
    return $platzhalter;
}

?>
