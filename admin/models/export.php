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

jimport('joomla.application.component.model');

class CrowdFundingModelExport extends JModelLegacy {
    
    public function getCurrencies() {
        
        $db     = $this->getDbo();
        /** @var $db JDatabaseMySQLi **/
        
        // Create a new query object.
        $query  = $db->getQuery(true);

        // Select the required fields from the table.
        $query
            ->select('a.id, a.title, a.abbr, a.symbol, a.position')
            ->from($db->quoteName('#__crowdf_currencies').' AS a');
        
        
        $db->setQuery($query);
        $results = $db->loadAssocList();
        
        $output = $this->prepareXML($results, "currencies", "curreny");
        
        return $output;
    }
    
    public function getLocations() {
        
        $db     = $this->getDbo();
        /** @var $db JDatabaseMySQLi **/
        
        // Create a new query object.
        $query  = $db->getQuery(true);

        // Select the required fields from the table.
        $query
            ->select(
            'a.id, a.name, a.latitude, a.longitude, a.country_code, 
             a.timezone, a.published'
            )
            ->from($db->quoteName('#__crowdf_locations').' AS a');
        
        
        $db->setQuery($query);
        $results = $db->loadAssocList();
        
        $output = $this->prepareXML($results, "locations", "location");
        
        return $output;
    }
    
    protected function prepareXML($results, $root, $child) {
        
        $xml = new SimpleXMLElement('<xml/>');

        if(!empty($root) AND !empty($child) ) {
            
            // Set ROOT item
            $rootItem = $xml->addChild($root);
            
            foreach( $results as $currency ) {
                
                $item = $rootItem->addChild($child);
                
                foreach( $currency as $key => $value ) {
                    $item->addChild($key, $value);
                }
            }
        }
        
        return $xml->asXML();

    } 
}