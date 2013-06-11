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

class CrowdFundingViewBacking extends JViewLegacy {
    
    protected $state;
    protected $item;
    protected $params;
    
    protected $option;
    
    protected $modelContext;
    protected $projectContext;
    
    public function __construct($config) {
        parent::__construct($config);
        $this->option = JFactory::getApplication()->input->get("option");
    }
    
    public function display($tpl = null) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        $model             = $this->getModel();
        
        // Get model state.
        $this->state       = $this->get('State');
        $this->item        = $this->get("Item");
        $this->params      = $this->state->get("params");
        
        if (!$this->item) {
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_PROJECT"), "notice");
            $app->redirect(JRoute::_('index.php?option=com_crowdfunding&view=discover', false));
            return;
        }
        
        // Get model context
        $this->modelContext      = $model->getContext();
        $this->projectContext    = $this->modelContext.".project".$this->item->id;
        
        // Set the flag for step one.
        $this->flagStep1         = $app->getUserState($this->projectContext.".step1", false);
        
        // Get selected reward ID
        $this->rewardId          = $this->state->get($this->modelContext.".rid");
        
        // Images
        $this->imageFolder       = $this->params->get("images_directory", "images/projects");
        
        // Include HTML helper
        JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
        
        // Get currency
		jimport("crowdfunding.currency");
        $currencyId              = $this->params->get("project_currency");
        $this->currency          = CrowdFundingCurrency::getInstance($currencyId);
		
		// Get a social platform for integration
		$this->socialPlatform    = $this->params->get("integration_social_platform");
		
        // Set a link to project page
        $host  = JFactory::getURI()->toString(array("scheme", "host"));
        $this->item->link        = $host.JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($this->item->slug, $this->item->catslug), false);
        
        // Set a link to image
        $this->item->link_image  = $host."/".$this->imageFolder."/".$this->item->image;
        
        $this->layout      = $this->getLayout();
        
        switch($this->layout) {
            
            case "payment":
                $this->preparePayment();
                break;
                
            case "share":
                $this->prepareShare();
                break;
                
            default: //  Pledge and Rewards 
                $this->prepareRewards();
                break;
        }
        
        // Check days left. If there is no days, disable the button.
        $this->disabledButton = "";
        if(!$this->item->days_left) {
	        $this->disabledButton = 'disabled="disabled"';
	    }
	    
	    $this->version        = new CrowdfundingVersion();
	    
        $this->prepareDebugMode();
		$this->prepareDocument();
		
        parent::display($tpl);
    }
    
	/**
     * Check the system for debug mode
     */
    protected function prepareDebugMode() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Check for maintenance (debug) state
        $params = $this->state->get("params");
        $this->debugMode = $params->get("debug_payment_disabled", 0);
        if($this->debugMode) {
            $msg = JString::trim($params->get("debug_disabled_functionality_msg"));
            if(!$msg) {
                $msg = JText::_("COM_CROWDFUNDING_DEBUG_MODE_DEFAULT_MSG");
            }
            $app->enqueueMessage($msg, "notice");
            
            $this->disabledButton = 'disabled="disabled"';
        }
        
    }
    
    protected function prepareRewards() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Get amount from session
        $this->rewardAmount    = $app->getUserState($this->projectContext.".amount", 0);

        // Get rewards
        $this->rewards         = $this->get("Rewards");
        
        // Compare amount with the amount of reward, that is selected.
        // If the amount of selected reward is larger than amount from session, 
        // use the amount of selected reward  
        if(!empty($this->rewardId)) {
            foreach($this->rewards as $reward) {
                if($this->rewardId == $reward->id) {
                    
                    if($this->rewardAmount < $reward->amount) {
                        $this->rewardAmount = $reward->amount;
                        
                        // Set step 1 to false
                        $app->setUserState($this->projectContext.".step1", false);
                        $this->flagStep1 = false;
                    }
                    
                    break;
                } 
            }
        }
        
    }
    
    protected function prepareShare() {
            
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Get amount from session
        $this->amount      = $app->getUserState($this->projectContext.".amount", 0);
        
        $model             = $this->getModel();
        $this->reward      = null;
        if(!empty($this->rewardId)) {
            $this->reward  = $model->getReward($this->rewardId);
        }
        
        // Events
        JPluginHelper::importPlugin('content');
        $dispatcher	       = JDispatcher::getInstance();
        $this->item->event = new stdClass();
        $offset            = 0;
        
        $results           = $dispatcher->trigger('onContentAfterDisplay', array('com_crowdfunding.payment.share', &$this->item, &$this->params, $offset));
		$this->item->event->afterDisplayContent = trim(implode("\n", $results));
		
    }
    
    protected function preparePayment() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        if(!$this->item->days_left) {
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_PROJECT_COMPLETED"), "notice");
            $app->redirect(JRoute::_("index.php?option=com_crowdfunding&view=backing&id=".(int)$this->item->id, false));
            return; 
        }
        
        // Rewards
        $this->reward       = $this->get("Reward");
        if( !is_null($this->reward) ) {
            if( $this->reward->isLimited() AND !$this->reward->getAvailable() ) {
                $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_REWARD_NOT_AVAILABLE"), "notice");
                $app->redirect(JRoute::_("index.php?option=com_crowdfunding&view=backing&id=".(int)$this->item->id, false));
                return; 
            }
        }
        
        $this->amount       = $app->getUserState($this->projectContext.".amount", 0);
        if(!$this->amount) {
            $app->enqueueMessage(JText::_("COM_CROWDFUNDING_ERROR_INVALID_AMOUNT"), "notice");
            $app->redirect(JRoute::_("index.php?option=com_crowdfunding&view=backing&id=".(int)$this->item->id, false));
            return; 
        }
        
        // Events
        JPluginHelper::importPlugin('crowdfundingpayment');
        $dispatcher	        = JDispatcher::getInstance();
        $this->item->event  = new stdClass();
        
        $item               = new stdClass();
        
        $item->id           = $this->item->id;
        $item->title        = $this->item->title;
        $item->slug         = $this->item->slug;
        $item->catslug      = $this->item->catslug;
        $item->rewardId     = $this->rewardId;
        $item->amount       = $this->amount;
        $item->currencyCode = $this->currency->abbr;
        
        $results            = $dispatcher->trigger('onProjectPayment', array('com_crowdfunding.payment', $item, $this->params));
		$this->item->event->onProjectPayment = trim(implode("\n", $results));
		
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
        $this->document->addStyleSheet( 'media/'.$this->option.'/css/site/style.css');
        
        // Add scripts
        JHtml::_('bootstrap.framework');
        $this->document->addScript('media/'.$this->option.'/js/site/backing.js');
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
            $this->params->def('page_heading', JText::sprintf('COM_CROWDFUNDING_BACKING_DEFAULT_PAGE_TITLE', $this->item->title));
        }
    
    }
    
    private function prepearePageTitle() {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/
        
        // Prepare page title
//        $title = $this->params->get('page_title', $this->item->title);
        $title = JText::sprintf("COM_CROWDFUNDING_INVESTING_IN", $this->escape($this->item->title) );
        
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