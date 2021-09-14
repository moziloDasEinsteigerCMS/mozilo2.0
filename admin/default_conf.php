<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

# $conf_array = Array Name
# $for_new = für neue conf datei
function makeDefaultConf($conf_array,$for_new = false) {
    $basic = array(
                    'text' => array(
                        'adminmail' => '',
                        'noupload' => 'php%2Cphp3%2Cphp4%2Cphp5%2Cphp6',
                        'language' => 'deDE'),
                    'digit' => array(
                        'backupmsgintervall' => '30',
                        'chmodnewfilesatts' => '',
                        'lastbackup' => time()),
                    'userexpert' => array(
                        'tabs' => array(),
                        'config' => array(),
                        'admin' => array(),
                        'plugins' => array(),
                        'template' => array()
                    ),
                    # das sind die Expert Parameter von basic
                    'expert' => array(
                        'language',
                        'adminmail',
                        'backupmsgintervall',
                        'getbackup',
                        'chmodnewfilesatts',
                        'noupload',
                        'userpassword')
                    );

    $main = array(
                    'text' => array(
                        'titlebarseparator' => '%20%3A%3A%20',
                        'websitedescription' => '',
                        'websitekeywords' => '',
                        'titlebarformat' => '%7BWEBSITE%7D%7BSEP%7D%7BCATEGORY%7D%7BSEP%7D%7BPAGE%7D',
                        'websitetitle' => 'moziloCMS%20-%20Das%20CMS%20f%C3%BCr%20Einsteiger',
                        'cmslanguage' => 'deDE',
                        'cmslayout' => 'moziloCMS_Standard_Dunkel',
                        'defaultcat' => 'Willkommen',
                        'defaultcolors' => '',
                        'draftlayout' => 'false'),
                    'checkbox' => array(
                        'hidecatnamedpages' => 'false',
                        'modrewrite' => 'false',
                        'replaceemoticons' => 'true',
                        'showhiddenpagesasdefaultpage' => ' false',
                        'showhiddenpagesinsearch' => 'false',
                        'showhiddenpagesinsitemap' => 'false',
                        'showsyntaxtooltips' => 'true',
                        'targetblank_download' => 'true',
                        'targetblank_link' => 'true',
                        'usesitemap' => 'true',
                        'usecmssyntax' => 'true',
                        'draftmode' => 'false'),
                    # das sind die Expert Parameter von main
                    'expert' => array(
                        'websitetitle',
                        'websitedescription',
                        'cmslanguage',
                        'defaultcat',
                        'draftmode',
                        'usesitemap',
                        'usecmssyntax',
                        'editusersyntax',
                        'replaceemoticons',
                        'defaultcolors',
                        'hiddenpages',
                        'targetblank',
                        'hidecatnamedpages',
                        'modrewrite',
                        'showsyntaxtooltips')
                    );

    $syntax = array('wikipedia' => '[link={DESCRIPTION}|http://de.wikipedia.org/wiki/{VALUE}]');

    $loginpass = array('name' => '',
                        'pw' => '',
                        'username' => '',
                        'userpw' => '');

    $logindata = array('falselogincount' => '0',
                        'falselogincounttemp' => '0',
                        'loginlockstarttime' => '');

    $gallery = array('digit' => array(
                        'maxheight' => '',
                        'maxwidth' => '',
                        'maxthumbheight' => '500',
                        'maxthumbwidth' => '400')
                    );

    # für neue conf nur die subarrays benuzen
    if(isset($$conf_array) and is_array($$conf_array) and $for_new === true) {
        $return_array = array();
        # beim erzeugen duerfen sub arrays nicht mit rein
        foreach($$conf_array as $key => $value) {
            if($key == "expert") continue;
            if(is_array($value)) {
                foreach($value as $key => $value) {
                    $return_array[$key] = $value;
                }
            } else {
                $return_array = $$conf_array;
                break;
            }
        }
        return $return_array;
    # das ganze array zurück
    } elseif(isset($$conf_array) and is_array($$conf_array)) {
        return $$conf_array;
    # conf gibts nicht
    } else
        return false;
}
?>
