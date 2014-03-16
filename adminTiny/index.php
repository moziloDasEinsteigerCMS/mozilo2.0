<?php
define("IS_CMS", true);
define("IS_ADMIN", true);
# wenn der Ordner admin geändert wurde reicht es in hier einzutragen
define("ADMIN_DIR_NAME","admin");
define("ADMIN_TINY_DIR_NAME","adminTiny");
define("CMS_DIR_NAME","cms");
# falls bei windowssystemen \\ drin sind in \ wandeln
$BASE_DIR = str_replace("\\\\", "\\",__FILE__);
# zum schluss noch den teil denn wir nicht brauchen abschneiden
$BASE_DIR = substr($BASE_DIR,0,-(strlen(ADMIN_TINY_DIR_NAME."/index.php")));
define("BASE_DIR",$BASE_DIR);
unset($BASE_DIR);
define("BASE_DIR_ADMIN", BASE_DIR.ADMIN_DIR_NAME."/");

$name_id = 'MOZILOID_'.md5($_SERVER['SERVER_NAME'].BASE_DIR);
define("SESSION_MO",$name_id);
unset($name_id);

define("DRAFT", true);

if(is_file("../".ADMIN_DIR_NAME."/sessionClass.php")) {
   require_once("../".ADMIN_DIR_NAME."/sessionClass.php");
} else
    @session_name(SESSION_MO);

session_start();

require_once("../".CMS_DIR_NAME."/DefaultConfCMS.php");
require_once("../".ADMIN_DIR_NAME."/filesystem.php");
require_once("../".CMS_DIR_NAME."/DefaultFunc.php");
require_once("../".CMS_DIR_NAME."/Properties.php");
require_once("../".CMS_DIR_NAME."/SpecialChars.php");

$CMS_CONF    = new Properties(BASE_DIR_CMS.CONF_DIR_NAME."/main.conf.php");
$specialchars = new SpecialChars();

$function = getRequestValue('function','get',false);

switch ($function) {
    case 'login':
        $user = getRequestValue('user','get',false);
        $pw = getRequestValue('pw','get',false);
        echo json_encode(CheckLogin($user,$pw));
        break;
    case 'inhaltsseiten' :
        $userLogedIn = isset($_SESSION['user']);
        if ($userLogedIn == false) {
            echo json_encode($userLogedIn);
        }else{
            $seite = getRequestValue('page','get',false);
            if ($seite == '') {
                echo json_encode(GetInhaltsseiten());
            }else{
                echo json_encode(GetInhaltsseite($seite));
            }
        }        
        break;
    case 'logout' :
        session_unset();
        echo json_encode(true);
        break;
    case 'logedin' :
        echo json_encode(isset($_SESSION['user']));
        break;
    default:
        $JSONData = json_decode(file_get_contents("php://input"));
        if ((isset($JSONData->function)) && ($JSONData->function == 'save')) {
            echo json_encode(Save($JSONData));
        }else{
            echo file_get_contents(BASE_DIR.ADMIN_TINY_DIR_NAME.'/views/index.tmpl.html');
        }
        break;
}


//WebAPI functions

function CheckLogin($user,$pw) {
    $loginpassword = new Properties(BASE_DIR_ADMIN.CONF_DIR_NAME."/loginpass.conf.php");    
    require_once(BASE_DIR_CMS.'PasswordHash.php');
    $t_hasher = new PasswordHash(8, FALSE);    
    if (($user == $loginpassword->get("name")) and (true === $t_hasher->CheckPassword($pw, $loginpassword->get("pw")))) {
        $_SESSION['user'] = $user;
        return true;
    }elseif((strlen($loginpassword->get("username")) > 4) and ($user == $loginpassword->get("username")) and (true === $t_hasher->CheckPassword($pw, $loginpassword->get("userpw")))) {
        $_SESSION['user'] = $user;
        return true;        
    }else{
        session_unset();
        return false;
    }
}

function GetInhaltsseiten() {
    require_once("../".CMS_DIR_NAME."/CatPageClass.php");
    $CatPage = new CatPageClass();
    $CatArray = $CatPage->get_CatArray(false,true);
    $Result = array();
    for ($i = 0; $i < count($CatArray); $i++) {
        $Result[] = array("caption"=>$CatPage->get_HrefText($CatArray[$i],false),"name"=>$CatArray[$i],"class"=>"dropdown-header","link"=>false);
        $PageArray = $CatPage->get_PageArray($CatArray[$i]);
        for ($j = 0; $j < count($PageArray); $j++) {
            $Result[] = array("caption"=>$CatPage->get_HrefText($CatArray[$i],$PageArray[$j]),"name"=>$CatArray[$i].":".$PageArray[$j],"class"=>"","link"=>true);
        }  
    }
    return $Result;
}

function GetInhaltsseite($seite) {
    require_once("../".CMS_DIR_NAME."/CatPageClass.php");
    $CatPage = new CatPageClass();
    list($cat,$page) = $CatPage->split_CatPage_fromSyntax($seite);       
    $Filename = CONTENT_DIR_REL.$cat.'/'.$CatPage->get_FileSystemName($cat,$page);
    if(!file_exists($Filename) or false === ($content = file_get_contents($Filename)))
        return die('File ('.$Filename.') not found.');
    global $page_protect_search;
    # Achtung die ersetzung mit \n ist nötig da sonst nee lehrzeile am anfang verschluckt wird
    $content = str_replace($page_protect_search,"\n",$content);
    return array($content); 
}

function Save($JSONData) {
    if ((isset($JSONData->typ)) && ($JSONData->typ == 'inhaltsseite')) {
        if ((isset($JSONData->name)) && (isset($JSONData->value))) {
            require_once("../".CMS_DIR_NAME."/CatPageClass.php");
            $CatPage = new CatPageClass();
            list($cat,$page) = $CatPage->split_CatPage_fromSyntax($JSONData->name);
            $Filename = CONTENT_DIR_REL.$cat.'/'.$CatPage->get_FileSystemName($cat,$page);
            global $page_protect;
            if(false === ($content = file_put_contents($Filename,$page_protect.$JSONData->value,LOCK_EX)))
                return die('File ('.$Filename.') not writable.');
            return true;
        }else 
            return false;
    }else 
        return false;
}

?>