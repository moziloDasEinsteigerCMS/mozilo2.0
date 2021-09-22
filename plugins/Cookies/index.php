<?php if(!defined('IS_CMS')) die();
 
class Cookies extends Plugin {
 
  public $admin_lang;
  private $cms_lang;
  
  function getContent($value) {
      global $CatPage;
		global $CMS_CONF;
		global $specialchars;
		global $language;
      global $syntax;
      global $lang_cookies;

      $dir = PLUGIN_DIR_REL."Cookies/";
      $lang_cookies = new Language($dir."sprachen/cms_language_".$CMS_CONF->get("cmslanguage").".txt");
      
    # cookieDays
    if ($this->settings->get("cookieDays") == true) {
     $cookieDays = $this->settings->get("cookieDays");
     }else {
     $cookieDays = '30';
     }      
          
    # cookieTitle
    if ($this->settings->get("cookieTitle") == true) {
     $cookieTitle = $this->settings->get("cookieTitle");
     }else {
     $cookieTitle = '';
     }
     
    # cookieDesc
    if ($this->settings->get("cookieDesc") == true) {
     $cookieDesc = $this->settings->get("cookieDesc");
     }else {
     $cookieDesc = $lang_cookies->getLanguageHtml("cookieDesc");
     }
     
    # cookieBtn
    if ($this->settings->get("cookieBtn") == true) {
     $cookieBtn = $this->settings->get("cookieBtn");
     }else {
     $cookieBtn = $lang_cookies->getLanguageHtml("cookieBtn");
     }
     
    # cookiePrivacy
    if ($this->settings->get("cookiePrivacy") == true) {
     $cookiePrivacy = $this->settings->get("cookiePrivacy");
     }else {
     $cookiePrivacy = $lang_cookies->getLanguageHtml("cookiePrivacy");
     }
   
    $content = '<div id="cookie">';
    $content .= '<div class="cookieTitle">'.$cookieTitle.'</div>';
    $content .= '<div class="cookieDesc">'.$cookieDesc.' ';
    
    $cat = $this->settings->get("cookieCat");
    $page = $this->settings->get("cookiePage");
    
    $linkprivacy = "index.php?cat=".$cat."&amp;page=".$page."";		
			if($CMS_CONF->get("modrewrite") == "true") {
				$linkprivacy = URL_BASE. $cat."/".$page.".html";          
			}
			if(!$CatPage->exists_CatPage($cat,$page)) {
            $category_text = $specialchars->rebuildSpecialChars($cat,true,true);
            $page_text = $specialchars->rebuildSpecialChars($page,true,true);
            $deadlink = $language->getLanguageValue("tooltip_link_page_error_2", $page_text, $category_text);
           
            $content .= "<br><span class=\"deadlink\">".$deadlink."</span>";      
       } else {
       	$content .= "<a href=\"". $linkprivacy ."\">".$cookiePrivacy."</a>";
       }
       $content .= "</div>";
       $content .= '<div class="cookieBtn"><a href="JavaScript:void(0)" id="close" class="button">'.$cookieBtn.'</a></div>';
       $content .= '</div>'; 
       
       $tail = '<script>';
       $tail .= 'if (!readCookie(\'mozilo\')) {
    $(\'#cookie\').fadeIn("slow");
}
  $(\'#close\').click(function() {
    $(\'#cookie\').fadeOut("slow");
    createCookie(\'mozilo\', true, '.$cookieDays.')
    return false;
  });

function createCookie(name,value,days) {
  if (days) {
    var date = new Date();
    date.setTime(date.getTime()+(days*24*60*60*1000));
    var expires = "; expires="+date.toGMTString();
  }
  else var expires = "";
  document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(\';\');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==\' \') c = c.substring(1,c.length);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
  }
  return null;
}

function eraseCookie(name) {
  createCookie(name,"",-1);
}';
      $tail .='</script>';
       
       $syntax->insert_in_tail($tail);
       $syntax->insert_jquery_in_head('jquery');
       return $content;
  }
 
  function getConfig() {
 
    $config = array();
 
    $config['cookieDays']  = array(
      'type' => 'text',
      'description' => $this->admin_lang->getLanguageValue('config_cookieDays'),
      'maxlength' => '3',
      'size' => '5',
      'regex' => "/^[0-9]{2,3}$/",
      'regex_error' => $this->admin_lang->getLanguageValue('config_cookieDays_error')
    );
        $config['cookieTitle']  = array(
      'type' => 'text',
      'description' => $this->admin_lang->getLanguageValue('config_cookieTitle'),
    );
    $config['cookieDesc']  = array(
      'type' => 'text',
      'description' => $this->admin_lang->getLanguageValue('config_cookieDesc'),
    );
   $config['cookieBtn']  = array(
      'type' => 'text',
      'description' => $this->admin_lang->getLanguageValue('config_cookieBtn'),
    );
   $config['cookiePrivacy']  = array(
      'type' => 'text',
      'description' => $this->admin_lang->getLanguageValue('config_cookiePrivacy'),
    );
   $config['cookieCat']  = array(
      'type' => 'text',
      'description' => $this->admin_lang->getLanguageValue('config_cookieCat'),
      'maxlength" => "100'
    );
   $config['cookiePage']  = array(
      'type' => 'text',
      'description' => $this->admin_lang->getLanguageValue('config_cookiePage'),
      'maxlength" => "100'
   );    
    return $config;
  } 
 
  function getInfo() {
 
    global $ADMIN_CONF;
 
    $this->admin_lang = new Language(PLUGIN_DIR_REL . 'Cookies/sprachen/admin_language_' . $ADMIN_CONF->get('language') . '.txt');
 
    $info = array(
      // plugin name and version
      '<b>Cookies</b> Revision:1',
      // moziloCMS version
      '2.0',
      // short description, only <span> and <br /> are allowed
      $this->admin_lang->getLanguageValue('description'), 
      // author
      'moziloCMS',
      // documentation url
      'https://www.mozilo.de',
      // plugin tag for select box when editing a page, can be emtpy
      array(
        '{Cookies}' => $this->admin_lang->getLanguageValue('placeholder'),
      )
    ); 
    return $info;
  }  
} 
?>