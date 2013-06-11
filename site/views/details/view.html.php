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

class CrowdFundingViewDetails extends JViewLegacy {
    
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
        
        if (!$this->item) {
            $app = JFactory::getApplication();
            /** @var $app JSite **/
            
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), "notice");
            $app->redirect(JRoute::_('index.php?option=com_crowdfunding&view=discover', false));
            return;
        }
        
        // Get rewards of the project
        $this->rewards        = $this->get("Rewards");
        $this->imageFolder    = $this->params->get("images_directory", "images/projects");
        
        // Include HTML helper
        JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
        
        // Get currency
        jimport("crowdfunding.currency");
        $currencyId           = $this->params->get("project_currency");
		$this->currency       = CrowdFundingCurrency::getInstance($currencyId);
		
        // Prepare the link that points to project page
        $host  = JFactory::getURI()->toString(array("scheme", "host"));
        $this->item->link        = $host.JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug));
        
        // Prepare the link that points to project image
        $this->item->link_image  = $host."/".$this->imageFolder."/".$this->item->image;
        
        // Get the current screen
        $this->screen = $app->input->getCmd("screen", "home");
        
        $this->prepareDocument();
        
        switch($this->screen) {
            
            case "updates":
                $this->prepareUpdatesScreen();
                break;
                
            case "comments":
                $this->prepareCommentsScreen();
                break;
                
            case "funders":
                $this->prepareFundersScreen();
                break;
                
            default: // Home
                break;
        }
        
        // Events
        JPluginHelper::importPlugin('content');
        $dispatcher	       = JDispatcher::getInstance();
        $this->item->event = new stdClass();
        $offset            = 0;
        
        $results           = $dispatcher->trigger('onContentBeforeDisplay', array('com_crowdfunding.details', &$this->item, &$this->params, $offset));
		$this->item->event->beforeDisplayContent = trim(implode("\n", $results));
		
		$results           = $dispatcher->trigger('onContentAfterDisplayMedia', array('com_crowdfunding.details', &$this->item, &$this->params, $offset));
		$this->item->event->onContentAfterDisplayMedia = trim(implode("\n", $results));
		
		$results           = $dispatcher->trigger('onContentAfterDisplay', array('com_crowdfunding.details', &$this->item, &$this->params, $offset));
		$this->item->event->onContentAfterDisplay = trim(implode("\n", $results));
		
		// Count hits
		$model = $this->getModel();
		$model->hit($this->item->id);
		
		$this->version     = new CrowdfundingVersion();
		
        parent::display($tpl);
    }
    
    protected function prepareUpdatesScreen() {
        
        $model         = JModelLegacy::getInstance("Updates", "CrowdFundingModel", $config = array('ignore_request' => false));
        $this->items   = $model->getItems();
        $this->form    = $model->getForm();
        
        $this->userId  = JFactory::getUser()->id;
        $this->isOwner = ($this->userId != $this->item->user_id) ? false : true;
        
        // Get a social platform for integration
        $this->socialPlatform = $this->params->get("integration_social_platform");
        $this->avatars        = $this->params->get("integration_avatars");
        
        
        // Styles
        $this->document->addStyleSheet('media/'.$this->option.'/css/jquery.pnotify.default.css');

        // Scripts
        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');
        
        $this->document->addScript('media/'.$this->option.'/js/jquery.pnotify.min.js');
        $this->document->addScript('media/'.$this->option.'/js/helper.js');
        
        $this->document->addScript('media/'.$this->option.'/js/site/updates.js');
    }
    
    protected function prepareCommentsScreen() {
        
        $model         = JModelLegacy::getInstance("Comments", "CrowdFundingModel", $config = array('ignore_request' => false));
        $this->items   = $model->getItems();
        $this->form    = $model->getForm();
        
        $this->userId  = JFactory::getUser()->id;
        $this->isOwner = ($this->userId != $this->item->user_id) ? false : true;
        
        // Get a social platform for integration
        $this->socialPlatform = $this->params->get("integration_social_platform");
        $this->avatars        = $this->params->get("integration_avatars");
        
        // Styles
        $this->document->addStyleSheet('media/'.$this->option.'/css/jquery.pnotify.default.css');

        // Scripts
        JHtml::_('behavior.keepalive');
        JHtml::_('behavior.formvalidation');
        
        $this->document->addScript('media/'.$this->option.'/js/jquery.pnotify.min.js');
        $this->document->addScript('media/'.$this->option.'/js/helper.js');
        
        $this->document->addScript('media/'.$this->option.'/js/site/comments.js');
    }
    
    protected function prepareFundersScreen() {
        
        $model         = JModelLegacy::getInstance("Funders", "CrowdFundingModel", $config = array('ignore_request' => false));
        $this->items   = $model->getItems();
        
        // Get a social platform for integration
		$this->socialPlatform = $this->params->get("integration_social_platform");
		$this->avatars        = $this->params->get("integration_avatars");
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
        
        // Meta description
        $this->document->setDescription($this->item->short_desc);
        
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
        $currentBreadcrumb = JHtmlString::truncate($this->item->title, 32);
        $pathway->addItem($currentBreadcrumb, '');
        
        // Add styles
        $this->document->addStyleSheet( 'media/'.$this->option.'/css/site/style.css');
        // Add scripts
        JHtml::_('behavior.framework');
        JHtml::_('bootstrap.framework');
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
        
        switch ($this->screen) {
            
            case "updates":
                $title .= " | " .JText::_("COM_CROWDFUNDING_UPDATES");
            break;
                
            case "comments":
                $title .= " | " .JText::_("COM_CROWDFUNDING_COMMENTS");    
            break;
            
            case "funders":
                $title .= " | " .JText::_("COM_CROWDFUNDING_FUNDERS");
            break;
            
        }
        
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