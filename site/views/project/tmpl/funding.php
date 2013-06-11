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
?>
<?php echo $this->loadTemplate("nav");?>
<div class="row-fluid">
    <form action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="projectForm" id="crowdf-funding-form" class="form-validate" autocomplete="off">
        
        <div class="row-fluid">
            <div class="span2"><?php echo $this->form->getLabel('goal'); ?></div>
            <div class="span10">
                <?php echo $this->form->getInput('goal'); ?>
                <span class="help-block">
                <?php echo JText::sprintf("COM_CROWDFUNDING_MINIMUM_AMOUNT", $this->minAmount);?>
                </span>
            </div>
        </div>
        
        <div class="row-fluid">
            <div class="span2"><?php echo $this->form->getLabel('funding_type'); ?></div>
            <div class="span10"><?php echo $this->form->getInput('funding_type'); ?></div>
        </div>
    
    	<div class="row-fluid">
            <div class="span2">
            	<label title="<?php echo JText::_("COM_CROWDFUNDING_FIELD_FUNDING_DURATION")."::".JText::_("COM_CROWDFUNDING_FIELD_FUNDING_DURATION_DESC");?>" class="hasTip required" for="jform_funding_type" id="jform_funding_type-lbl">
            	<?php echo JText::_("COM_CROWDFUNDING_FIELD_FUNDING_DURATION");?><span class="star">&nbsp;*</span>
            	</label>
            </div>
            
            <div class="span10">
                
                <input type="radio" value="days" name="jform[funding_duration_type]" id="funding_duration_type0" <?php echo $this->checkedDays;?>>
                <?php echo $this->form->getLabel('funding_days'); ?>
                <div class="clearfix"></div>
                <?php echo $this->form->getInput('funding_days'); ?>
                <span class="help-block"><?php echo JText::sprintf("COM_CROWDFUNDING_MINIMUM_DAYS", $this->minDays);?></span>
    			
    			<div class="clearfix"></div>
    			<input type="radio" value="date" name="jform[funding_duration_type]" id="funding_duration_type1" <?php echo $this->checkedDate;?>>            
                <?php echo $this->form->getLabel('funding_end'); ?>
                <div class="clearfix"></div>
                <?php echo $this->form->getInput('funding_end'); ?>
        
            </div>
        </div>
        
        <?php echo $this->form->getInput('id'); ?>
        <input type="hidden" name="task" value="funding.save" />
        <?php echo JHtml::_('form.token'); ?>
        
        <div class="clearfix"></div>
        <button type="submit" class="button button-large margin-tb-15px" <?php echo $this->disabledButton;?>>
        	<i class="icon-ok icon-white"></i>
            <?php echo JText::_("JSAVE")?>
        </button>
    </form>
</div>
<?php echo $this->version->backlink;?>