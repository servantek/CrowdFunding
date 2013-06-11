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
defined('_JEXEC') or die;?>
<div class="<?php echo $this->pageclass_sfx;?>">
    <?php if ($this->params->get('show_page_heading', 1)) { ?>
    <h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
    <?php } ?>
    
    <?php foreach($this->items as $items) {?>
	<ul class="thumbnails">
	<?php for($i = 1, $max = 3; $i < 4; $i++) { ?>
	  <?php if(isset($items[$i])) {
	      $raised   = $this->currency->getAmountString($items[$i]->funded); 
		
		  // Prepare the value that I am going to display
		  $fundedPercents = JHtml::_("crowdfunding.funded", $items[$i]->funded_percents);
		
		  $user = JFactory::getUser($items[$i]->user_id);
		  $socialProfile  = JHtml::_("crowdfunding.socialProfile", $this->socialPlatform, $user);
	 ?>
      <li class="span4">
        <div class="thumbnail">
          <a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($items[$i]->slug, $items[$i]->catslug)); ?>">
          	<img src="<?php echo $this->imageFolder."/".$items[$i]->image;?>" alt="<?php echo $items[$i]->title;?>" width="<?php echo $this->imageWidth;?>" height="<?php echo $this->imageHeight;?>">
      	  </a>
          <div class="caption">
            <h3><a href="<?php echo JRoute::_(CrowdFundingHelperRoute::getDetailsRoute($items[$i]->slug, $items[$i]->catslug)); ?>"><?php echo $items[$i]->title;?></a></h3>
            <span class="cf-founder">by 
                <?php if(!empty($socialProfile)){ ?>
                <a href="<?php echo $socialProfile;?>"><?php echo $items[$i]->user_name; ?></a>
                <?php } else {?>
                <?php echo $items[$i]->user_name; ?>
                <?php }?>
            </span>
            <p><?php echo $items[$i]->short_desc;?></p>
                <?php echo JHtml::_("crowdfunding.progressbar", $fundedPercents, $items[$i]->days_left, $items[$i]->funding_type);?>
            <div class="row-fluid">
            	<div class="span4">
            	<div><strong><?php echo $items[$i]->funded_percents;?>%</strong></div>
            	<?php echo strtoupper( JText::_("COM_CROWDFUNDING_FUNDED") );?>
            	</div>
            	<div class="span4">
            	<div><strong><?php echo $raised;?></strong></div>
            	<?php echo strtoupper( JText::_("COM_CROWDFUNDING_RAISED") );?>
            	</div>
            	<div class="span4">
            	<div><strong><?php echo $items[$i]->days_left;?></strong></div>
            	<?php echo strtoupper( JText::_("COM_CROWDFUNDING_DAYS_LEFT") );?>
            	</div>
            </div>
          </div>
        </div>
      </li>
        <?php } ?>
    <?php }?>
    </ul>
    <?php }?>
    <div class="clearfix"></div>
    <div class="pagination">
        <?php if ($this->params->def('show_pagination_results', 1)) : ?>
            <p class="counter">
                <?php echo $this->pagination->getPagesCounter(); ?>
            </p>
        <?php endif; ?>
    
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
    <div class="clearfix">&nbsp;</div>
</div>
<div class="clearfix">&nbsp;</div>
<?php echo $this->version->backlink;?>