<?php if(!defined('IS_CMS')) die();

/***************************************************************
* 
* Breadcrumb für moziloCMS.
* 
***************************************************************/
class Breadcrumb extends Plugin {

    /***************************************************************
    * 
    * Gibt den HTML-Code zurück, mit dem die Plugin-Variable ersetzt 
    * wird.
    * 
    ***************************************************************/

 public $admin_lang;
  private $cms_lang;
 
  function getContent($value) {
 
    global $CMS_CONF;

    # Vorsatz abfragen
    if (!empty($this->settings->get("breadcrumb_text"))) {
     $entry = $this->settings->get("breadcrumb_text");
     $entry .=':';
     }
else {
     $entry = '';
    }
	
    # Start abfragen
    if ($this->settings->get("first_entry") == true) {
     $start = $this->settings->get("first_entry");
     }else {
     $start = 'Start';
     }

    # Trennzeichen abfragen
    if ($this->settings->get("breadcrumb_divider") == true) {
     $separator = $this->settings->get("breadcrumb_divider");
     }else {
     $separator = '&raquo;';
     }

    $content = '<div class="breadcrumb">' .$entry. '
<ol itemscope itemtype="http://schema.org/BreadcrumbList">
  <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
    <a itemprop="item" href="{BASE_URL}">
    <span itemprop="name">' .$start. '</span></a>
    <meta itemprop="position" content="1" />
  </li>
'.$separator.'
  <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
    <a itemprop="item" href="{CATEGORY}.html">
    <span itemprop="name">{CATEGORY_NAME}</span></a>
    <meta itemprop="position" content="2" />
  </li>
'.$separator.'
  <li itemprop="itemListElement" itemscope
      itemtype="http://schema.org/ListItem">
    <a itemprop="item" href="{PAGE}.html">
    <span itemprop="name">{PAGE_NAME}</span></a>
    <meta itemprop="position" content="3" />
  </li>
</ol>
</div>';
    return $content;

    } // function getContent

    /***************************************************************
    * 
    * Gibt die Konfigurationsoptionen als Array zurück.
    * 
    ***************************************************************/
    function getConfig() {
 
    $config = array();
 
    // first entry
    $config['breadcrumb_text']  = array(
      'type' => 'text',
      'description' => $this->admin_lang->getLanguageValue('config_breadcrumb_text'),
      'maxlength' => '20',
    );

    // entry
    $config['first_entry']  = array(
      'type' => 'text',
      'description' => $this->admin_lang->getLanguageValue('config_first_entry'),
      'maxlength' => '20',
    );
    
        // entry
    $config['breadcrumb_divider']  = array(
      'type' => 'text',
      'description' => $this->admin_lang->getLanguageValue('config_breadcrumb_divider'),
      'maxlength' => '3',
    ); 
 
    return $config;
  }
 
    /***************************************************************
    * 
    * Gibt die Plugin-Infos als Array zurück
    *  
    ***************************************************************/
 
  function getInfo() {
 
    global $ADMIN_CONF;
 
    $this->admin_lang = new Language(PLUGIN_DIR_REL . 'Breadcrumb/sprachen/admin_language_' . $ADMIN_CONF->get('language') . '.txt');
 
    $info = array(
      // plugin name and version
      '<b>Breadcrumb</b> Revision: 1',
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
        '{Breadcrumb}' => $this->admin_lang->getLanguageValue('placeholder'),
      )
    );
 
    return $info;
  }
}
 
?>