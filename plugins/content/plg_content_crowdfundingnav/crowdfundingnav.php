<?php
/**
 * @package      CrowdFunding
 * @subpackage   Plugins
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

jimport('joomla.plugin.plugin');

/**
 * CrowdFunding Navigation Plugin
 *
 * @package      CrowdFunding
 * @subpackage   Plugins
 */
class plgContentCrowdFundingNav extends JPlugin {
    
    public function onContentBeforeDisplay($context, &$article, &$params, $page = 0) {
        
        $app = JFactory::getApplication();
        /** @var $app JSite **/

        if($app->isAdmin()) {
            return;
        }

        $doc     = JFactory::getDocument();
        /**  @var $doc JDocumentHtml **/
        
        // Check document type
        $docType = $doc->getType();
        if(strcmp("html", $docType) != 0){
            return;
        }
       
        if(strcmp("com_crowdfunding.details", $context) != 0){
            return;
        }
        
        // Load language
        $this->loadLanguage();
        
        $itemId = $app->input->getInt("id");
        $stats  = $this->getNavStats($itemId);
        
        $screen = $app->input->getCmd("screen", "home");
        
        $html  = '<ul class="nav nav-pills">';
        
        if($this->params->get("display_home")) {
            $class = "";
            if(strcmp($screen, "home") == 0) {
                $class = 'class="active"';
            }
            $html .= '<li '.$class.'><a href="'.JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($article->slug, $article->catslug)).'">'.JText::_("PLG_CONTENT_CROWDFUNDINGNAV_HOME")."</a></li>";
        }
        
        if($this->params->get("display_updates")) {
            $class = "";
            if(strcmp($screen, "updates") == 0) {
                $class = 'class="active"';
            }
            
            $stat  = '<span class="label">'.JArrayHelper::getValue($stats, "updates", 0).'</span>';
            $html .= '<li '.$class.'><a href="'.JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($article->slug, $article->catslug, "updates")).'">'.JText::_("PLG_CONTENT_CROWDFUNDINGNAV_UPDATES") .' '. $stat .'</a></li>';
        }
        
        if($this->params->get("display_comments")) {
            $class = "";
            if(strcmp($screen, "comments") == 0) {
                $class = 'class="active"';
            }
            
            $stat  = '<span class="label">'.JArrayHelper::getValue($stats, "comments", 0).'</span>';
            $html .= '<li '.$class.'><a href="'.JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($article->slug, $article->catslug, "comments")).'">'.JText::_("PLG_CONTENT_CROWDFUNDINGNAV_COMMENTS") .' '. $stat .'</a></li>';
        }
        
        if($this->params->get("display_funders")) {
            $class = "";
            if(strcmp($screen, "funders") == 0) {
                $class = 'class="active"';
            }
            $stat  = '<span class="label">'.JArrayHelper::getValue($stats, "funders", 0).'</span>';
            $html .= '<li '.$class.'><a href="'.JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($article->slug, $article->catslug, "funders")).'">'.JText::_("PLG_CONTENT_CROWDFUNDINGNAV_FUNDERS") .' '. $stat .'</a></li>';
        }
        
        $html .= '</ul>';
        
        return $html;
        
    }
    
    /**
     * This method returns number of updates, comments and funders.
     * 
     * @param integer $itemId
     * @return array
     * 
     * @todo do it with one query
     */
    private function getNavStats($itemId) {
        
        $results = array();
        
        $db    = JFactory::getDbo();
        
        /// Updates
        $query = $db->getQuery(true);
        $query
            ->select("COUNT(*) AS updates")
            ->from($db->quoteName("#__crowdf_updates"))
            ->where("project_id = ". (int)$itemId);
        
        $db->setQuery($query);
        $results["updates"] = $db->loadResult();
        
        // Comments
        $query = $db->getQuery(true);
        $query
            ->select("COUNT(*) AS comments")
            ->from($db->quoteName("#__crowdf_comments"))
            ->where("project_id = ". (int)$itemId)
            ->where("published = 1");
        
        $db->setQuery($query);
        $results["comments"] = $db->loadResult();
        
         // Funders
        $query = $db->getQuery(true);
        $query
            ->select("COUNT(*) AS funders")
            ->from($db->quoteName("#__crowdf_transactions"))
            ->where("project_id  = ". (int)$itemId);
        
        $db->setQuery($query);
        $results["funders"] = $db->loadResult();
        
        return $results;
    }
}