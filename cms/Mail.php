<?php if(!defined('IS_CMS')) die();

if(!class_exists('idna_convert')) {
    require_once(BASE_DIR_CMS.'idna_convert.class.php');
    global $Punycode;
    $Punycode = new idna_convert();
}
// Sendet eine Mail an die konfigurierte Admin-Adresse (Absender ist der CMS-Titel)
function sendMailToAdmin($subject, $content) {
    global $ADMIN_CONF;
    global $specialchars;
    $from = $specialchars->rebuildSpecialChars($ADMIN_CONF->get("adminmail"),false,false);
    sendMail($subject, $content, $from, $from, $from);
}

// Sendet eine Mail an die konfigurierte Kontakt-Adresse oder eine Kopie an die Usermail-Adresse
function sendMail($subject, $content, $from, $to, $replyto = "") {
    global $specialchars;
    global $Punycode;
    $from = $Punycode->encode($from);
    $to = $Punycode->encode($to);
    $replyto = $Punycode->encode($replyto);
    @mail(
           $specialchars->getHtmlEntityDecode($to),
           "=?".CHARSET."?B?".base64_encode($specialchars->getHtmlEntityDecode($subject))."?=",
           $specialchars->getHtmlEntityDecode($content),
           getHeader ($specialchars->getHtmlEntityDecode($from), $specialchars->getHtmlEntityDecode($replyto))
         );
}

// Baut den Mail-Header zusammen
function getHeader($from, $replyto) {
    if (empty($replyto))
        $replyto = $from;
    return "From: ".$from."\r\n"
        ."MIME-Version: 1.0\r\n"
        ."Content-type: text/plain; charset=".CHARSET."\r\n"
        ."Reply-To: ".$replyto."\r\n"
        ."X-Priority: 0\r\n"
        ."X-MimeOLE: \r\n"
        ."X-mailer: moziloCMS\r\n";
}

// Prüft ob die Mail-Funktion verfügbar ist
function isMailAvailable() {
    return function_exists("mail");
}

function isMailAddressValid($from) {
    global $Punycode;
    $from = $Punycode->encode($from);
    if(preg_match(MAIL_REGEX, $from))
        return true;
    return false;
}

?>