<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();
global $activ_plugins,$deactiv_plugins;

$var_PluginsActiv = '';
if(isset($activ_plugins) and count($activ_plugins) > 0) {
    rsort($activ_plugins);
    $var_PluginsActiv = 'var moziloPluginsActiv = "'.implode('|',$activ_plugins).'";';
}

$var_PluginsDeactiv = '';
if(isset($deactiv_plugins) and count($deactiv_plugins) > 0) {
    rsort($deactiv_plugins);
    $var_PluginsDeactiv = 'var moziloPluginsDeactiv = "'.implode('|',$deactiv_plugins).'";';
}

$moziloPlace = makePlatzhalter(true);
foreach($moziloPlace as $key => $value) {
    $value = substr($value,1,-1);
    if(in_array($value,$activ_plugins)) {
        unset($moziloPlace[$key]);
        continue;
    }
    $moziloPlace[$key] = $value;
}
if(count($moziloPlace) > 0) {
    rsort($moziloPlace);
    $var_Place = 'var moziloPlace = "'.implode('|',$moziloPlace).'|VALUE|DESCRIPTION";';
} else
    $var_Place = 'var moziloPlace = "VALUE|DESCRIPTION";';

$var_UserSyntax = '';
global $USER_SYNTAX;
$moziloUserSyntax  = $USER_SYNTAX->toArray();
if(count($moziloUserSyntax) > 0) {
    $moziloUserSyntax = array_keys($moziloUserSyntax);
    rsort($moziloUserSyntax);
    $var_UserSyntax = 'var moziloUserSyntax = "'.implode('|',$moziloUserSyntax).'";';
}

$smileys = new Properties(BASE_DIR_CMS."smileys/smileys.txt");
$moziloSmileys = $smileys->toArray();
$var_Smileys = '';
if(count($moziloSmileys) > 0) {
    $moziloSmileys = array_keys($moziloSmileys);
    rsort($moziloSmileys);
    $var_Smileys = 'var moziloSmileys = "'.implode('|',$moziloSmileys).'";';
}

$moziloSyntax = 'var moziloSyntax = "';
require_once(BASE_DIR_CMS."Syntax.php");
$syntax_elemente = get_class_methods('Syntax');
rsort($syntax_elemente);
foreach($syntax_elemente as $element) {
    if($element == "syntax_hr") continue;
    if(substr($element,0,strlen("syntax_")) == "syntax_")
        $moziloSyntax .= substr($element,strlen("syntax_"))."|";
}
$moziloSyntax .= '----";';

$editor_area_html = '<link type="text/css" rel="stylesheet" href="editsite.css" />';

$editor_edit_usersyntax = "false";
if(ACTION == "config")
    $editor_edit_usersyntax = "true";

$editor_area_html .= '<script language="Javascript" type="text/javascript" charset="utf-8">/*<![CDATA[*/
var meditorID = "pagecontent";
var editor_edit_usersyntax = '.$editor_edit_usersyntax.';
'.$var_PluginsActiv.'
'.$var_PluginsDeactiv.'
'.$var_Place.'
'.$var_UserSyntax.'
'.$var_Smileys.'
'.$moziloSyntax.
'/*]]>*/</script>';
$editor_area_html .= '<script src="ace_editor/src-min/ace.js" type="text/javascript" charset="utf-8"></script>';

?>