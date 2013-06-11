<?php
/**
 * @package      CrowdFunding
 * @subpackage   Components
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2010 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * CrowdFunding is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class CrowdFundingViewEmbed extends JViewLegacy {
    
    protected $state;
    protected $item;
    protected $params;
    
    protected $option;
    
    public function __construct($config) {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }
    
    public function display($tpl = null) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Get model state.
        $this->state       = $this->get('State');
        $this->item        = $this->get("Item");
        $this->params      = $this->state->get("params");
        
        $this->imageFolder = $this->params->get("images_directory", "images/projects");
        
        if (!$this->item) {
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), "notice");
            $app->redirect(JRoute::_('index.php?option=com_crowdfunding&view=discover', false));
            return;
        }
        
        // Include HTML helper
        JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
        
        // Get currency
        jimport("crowdfunding.currency");
        $currencyId           = $this->params->get("project_currency");
		$this->currency       = CrowdFundingCurrency::getInstance($currencyId);
		
		// Get a social platform for integration
		$this->socialPlatform = $this->params->get("integration_social_platform");
		
        // Set a link to project page
        $uri   = JFactory::getURI();
        $host  = $uri->toString(array("scheme", "host"));
        $this->item->link        = $host.JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug), false);
        
        // Set a link to image
        $this->item->link_image  = $host."/".$this->imageFolder."/".$this->item->image;
        
        $layout = $this->getLayout();
        switch($layout) {
            
            case "email":
                
                if(!$this->params->get("security_display_friend_form", 0)) {
                    $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_CANT_SEND_MAIL"), "notice");
                    $app->redirect(JRoute::_($this->item->link, false));
                    return;
                }
                
                $this->prepareEmailForm($this->item);
                
                break;
                 
            default:  // Embed HTML code
                $this->embedCode = $this->prepareEmbedCode($this->item, $host);
                break;
            
        }
        
        $this->version     = new CrowdfundingVersion();
        
		$this->prepareDocument();
		
        parent::display($tpl);
    }
    
    /**
     * Generate HTML code for embeding.
     * 
     * @param object $item
     * @param string $host
     * @return string
     */
    protected function prepareEmbedCode($item, $host) {
        
        // Generate embed link
        $this->embedLink   = $host.JRoute::_(CrowdFundingHelperRoute::getEmbedRoute($this->item->slug, $this->item->catslug)."&layout=widget&tmpl=component", false);
        
        $code = '<iframe src="'.$this->embedLink.'" width="280px" height="560px" frameborder="0" scrolling="no"></iframe>';
        
        return $code;
    }
    
    /**
     * Display a form that will be used for sending mail to friend
     * 
     * @param object $item
     */
    protected function prepareEmailForm($item) {
        
        $model         = JModelLegacy::getInstance("FriendMail", "CrowdFundingModel", $config = array('ignore_request' => false));
        
        // Prepare default content of the form
        $formData = array(
            "id"       =>  $item->id,
            "subject"  =>  JText::sprintf("COM_CROWDFUNDING_SEND_FRIEND_DEFAULT_SUBJECT", $item->title),
            "message"  =>  JText::sprintf("COM_CROWDFUNDING_SEND_FRIEND_DEFAULT_MESSAGE", $item->link)
        );
        
        // Set user data
        $user   = JFactory::getUser();
        if(!empty($user->id)) {
            $formData["sender_name"] = $user->name;
            $formData["sender"]      = $user->email;
        }
        
        $this->form    = $model->getForm($formData);
        
        // Scripts
        JHtml::_('behavior.tooltip');
        JHtml::_('behavior.formvalidation');
        
    }
    
    /**
     * Prepare the document
     */
    protected function prepareDocument() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));
        
        // Prepare page heading
        $this->prepearePageHeading();
        
        // Prepare page heading
        $this->prepearePageTitle();
        
        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        } else {
            $this->document->setDescription($this->item->short_desc);
        }
        
        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }
        
        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
        
        // Load JHtmlString
        JLoader::register('JHtmlString', JPATH_LIBRARIES.'/joomla/html/html/string.php');
        
        // Breadcrumb
        $pathway = $app->getPathWay();
        $currentBreadcrumb = JHtmlString::truncate($this->item->title, 16);
        $pathway->addItem($currentBreadcrumb, '');
        
        // Add styles
        $this->document->addStyleSheet( 'media/'.$this->option.'/css/site/bootstrap.min.css');
        $this->document->addStyleSheet( 'media/'.$this->option.'/css/site/style.css');
        
        // Add scripts
        JHtml::_('behavior.framework');
    }
    
    private function prepearePageHeading() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menus = $app->getMenu();
        $menu  = $menus->getActive();
        
        // Prepare page heading
        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', JText::sprintf('COM_CROWDFUNDING_DETAILS_DEFAULT_PAGE_TITLE', $this->item->title));
        }
    
    }
    
    private function prepearePageTitle() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Prepare page title
//        $title = $this->params->get('page_title', $this->item->title);
        $title = $this->item->title;
        
        // Add title before or after Site Name
        if (!$title) {
            $title = $app->getCfg('sitename');
        } elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
            $title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
        } elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
            $title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
        }
        
        $this->document->setTitle($title);
    
    }

}