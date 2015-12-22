<?php if(!defined('IS_CMS')) die();

/* 
* 
* $Revision: 871 $
* $LastChangedDate: 2011-05-11 09:44:26 +0200 (Mi, 11. Mai 2011) $
* $Author: stefanbe $
*
*/

class SpecialChars {
    
// ------------------------------------------------------------------------------    
// Konstruktor
// ------------------------------------------------------------------------------
    function __construct(){
    }

    function getHtmlEntityDecode($string) {

        if((version_compare( phpversion(), '5.0' ) < 0)) {
            $replace = array_keys(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES));
            # get_html_translation_table liefert die Zeichen im ISO-8859-1 Format - wir brauchen UTF-8
            $replace = implode(",",$replace);
            if(function_exists("utf8_encode")) {
                $replace = utf8_encode($replace);
            } elseif(function_exists("mb_convert_encoding")) {
                $replace = mb_convert_encoding($replace, CHARSET);
            } elseif(function_exists("iconv")) {
                $replace = iconv('ISO-8859-1', CHARSET.'//IGNORE',$replace);
            }
            $replace = explode(",",$replace);
            $string = str_replace(array_values(get_html_translation_table(HTML_ENTITIES, ENT_QUOTES)), $replace, $string);
        } else {
            $string = html_entity_decode($string,ENT_QUOTES,CHARSET);
        }
        return $string;
    }

// ------------------------------------------------------------------------------    
// Erlaubte Sonderzeichen als RegEx zurückgeben
// ------------------------------------------------------------------------------
    function getSpecialCharsRegex() {

        $regex = "/^[a-zA-Z0-9_\%\-\s\?\!\@\.€".addslashes($this->getHtmlEntityDecode(implode("", get_html_translation_table(HTML_ENTITIES, ENT_QUOTES))))."]+$/";
        $regex = str_replace("&#39;", "'", $regex);
        return $regex;
    }

// ------------------------------------------------------------------------------    
// Inhaltsseiten/Kategorien für Speicherung umlaut- und sonderzeichenbereinigen 
// ------------------------------------------------------------------------------
    function replaceSpecialChars($text,$nochmal_erlauben) {
        # $nochmal_erlauben = für Tags mit src z.B. img dann muss das % auch gewandelt werden
        $text = str_replace('/','ssslashhh',$text);
        if(preg_match('#\%([0-9a-f]{2})#i',$text) < 1)
            $text = mo_rawurlencode(stripslashes($text));
        if($nochmal_erlauben)
            $text = mo_rawurlencode(stripslashes($text));
        $text = str_replace('ssslashhh','/',$text);
        return $text;
    }

// ------------------------------------------------------------------------------    
// Umlaute in Inhaltsseiten/Kategorien für Anzeige 
// ------------------------------------------------------------------------------
    function rebuildSpecialChars($text, $rebuildnbsp, $html) {
        $text = rawurldecode($text);
        if($html) {
            $test = htmlentities($text,ENT_COMPAT,CHARSET);
# hier muss noch geschraubt werden iconv gibts auf manchen systemen nicht!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
            if(empty($test) and function_exists("iconv")) {
                # htmlentities gibt einen leeren sring zurück wenn im string ein unbekantes zeichen ist
                # iconv entfernt es einfach
                $test = htmlentities(@iconv(CHARSET,CHARSET.'//IGNORE',$text),ENT_COMPAT,CHARSET);
            }
            $text = $test;
            $text = str_replace(array("&amp;#","&amp;lt;","&amp;gt;"),array("&#","&lt;","&gt;"),$text);
        }
        // Leerzeichen
        if ($rebuildnbsp and !$html)
            $text = str_replace(" ", "&nbsp;", $text);
        elseif(!$rebuildnbsp and $html)
            $text = str_replace("&nbsp;", " ", $text);
        return $text;
    }


// ------------------------------------------------------------------------------    
// Für Datei-Uploads erlaubte Sonderzeichen als RegEx zurückgeben
// ------------------------------------------------------------------------------
/*
    function getFileCharsRegex() {
        $regex = "/^[a-zA-Z0-9_\%\-\.]+$/";
        return $regex;
    }*/

// ------------------------------------------------------------------------------    
// Für Datei-Uploads erlaubte Sonderzeichen userlesbar als String zurückgeben
// ------------------------------------------------------------------------------
/*
    function getFileCharsString($sep, $charsperline) {
        $filecharsstring = "";
        $filecharshtml = "";
        for ($i=65; $i<=90;$i++)
            $filecharsstring .= chr($i);
        for ($i=97; $i<=122;$i++)
            $filecharsstring .= chr($i);
        for ($i=48; $i<=57;$i++)
            $filecharsstring .= chr($i);
        $filecharsstring .= "_-.";
        for ($i=0; $i<=strlen($filecharsstring); $i+=$charsperline) {
            $filecharshtml .= htmlentities(substr($filecharsstring, $i, $charsperline),ENT_COMPAT,CHARSET)."<br />";
        }
        return $filecharshtml;
    }*/

// ------------------------------------------------------------------------------
// Hilfsfunktion: Wandelt numerische Entities im übergebenen Text in Zeichen
// ------------------------------------------------------------------------------
/*
    function numeric_entities_decode($text) {
        return str_replace('&amp;#', '&#', $text);
    }*/

// ------------------------------------------------------------------------------
// Geschütze zeichen codieren
// ------------------------------------------------------------------------------
    function encodeProtectedChr($text) {# protected
        # alle geschützten zeichen suchen und in html code wandeln auch das ^
        $text = preg_replace_callback(
                    "/\^(.)/Umsi",
                    array($this,"callback_encodeProtectedChr"),
                    $text
                );
        return $text;
    }

// ------------------------------------------------------------------------------
// Geschütze zeichen wieder herstellen
// ------------------------------------------------------------------------------
    function decodeProtectedChr($text) {
        # alle &#94;&#?????; suchen und als zeichen ohne &#94; (^) ersetzen
        $text = preg_replace_callback(
                    "/&#94;&#(\d{2,5});/",
                    array($this,"callback_decodeProtectedChr"),
                    $text
                );
        return $text;
    }

    function callback_encodeProtectedChr($arr) {
        return "&#94;&#".ord($arr[1]).";";
    }

    function callback_decodeProtectedChr($arr) {
        return chr($arr[1]);
    }

    function ordutf8($string, &$offset) {
        $code = ord(substr($string, $offset,1));
        if ($code >= 128) {        //otherwise 0xxxxxxx
            if ($code < 224) $bytesnumber = 2;                //110xxxxx
            else if ($code < 240) $bytesnumber = 3;        //1110xxxx
            else if ($code < 248) $bytesnumber = 4;    //11110xxx
            $codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
            for ($i = 2; $i <= $bytesnumber; $i++) {
                $offset ++;
                $code2 = ord(substr($string, $offset, 1)) - 128;        //10xxxxxx
                $codetemp = $codetemp*64 + $code2;
            }
            $code = $codetemp;
        }
        $offset += 1;
        if ($offset >= strlen($string)) $offset = -1;
        return $code;
    }
// ------------------------------------------------------------------------------
// E-Mail-Adressen verschleiern
// ------------------------------------------------------------------------------
// Dank fuer spam-me-not.php an Rolf Offermanns!
// Spam-me-not in JavaScript: http://www.zapyon.de
# Achtung muss url encoded sein
    function obfuscateAdress($originalString, $mode) {
        // $mode == 1            dezimales ASCII
        // $mode == 2            hexadezimales ASCII
        // $mode == 3            zufaellig gemischt
        $encodedString = "";
        $nowCodeString = "";

        $originalLength = strlen($originalString);
        $encodeMode = $mode;

        $i = 0;
        while ($i >= 0 and $i < $originalLength) {
#        for ( $i = 0; $i < $originalLength; $i++) {
            if($originalString[$i] == "%") {
                $encodedString .= $originalString[$i].$originalString[$i+1].$originalString[$i+2];
                $i = $i + 3;
                continue;
            }
            if ($mode == 3) $encodeMode = rand(1,2);
            $ord = $this->ordutf8($originalString, $i);
            switch ($encodeMode) {
                case 1: // Decimal code
#                    $nowCodeString = "&#" . ord($originalString[$i]) . ";";
                    $nowCodeString = "&#" . $ord. ";";
                    break;
                case 2: // Hexadecimal code
#                    $nowCodeString = "&#x" . dechex(ord($originalString[$i])) . ";";
                    $nowCodeString = "&#x" . dechex($ord) . ";";
                    break;
                default:
                    return "ERROR: wrong encoding mode.";
            }
            $encodedString .= $nowCodeString;
        }
        return $encodedString;
    }

}
?>