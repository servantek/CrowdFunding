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
    <div class="span6">
        <form enctype="multipart/form-data"  action="<?php echo JRoute::_('index.php?option=com_crowdfunding'); ?>" method="post" name="projectForm" id="crowdf-project-form" class="form-validate" autocomplete="off">
            
            <?php echo $this->form->getLabel('title'); ?>
            <?php echo $this->form->getInput('title'); ?>
            
            <?php echo $this->form->getLabel('short_desc'); ?>
            <?php echo $this->form->getInput('short_desc'); ?>
            
            <?php echo $this->form->getLabel('catid'); ?>
            <?php echo $this->form->getInput('catid'); ?>
            
            <?php echo $this->form->getLabel('location_preview'); ?>
            <?php echo $this->form->getInput('location_preview'); ?>
            
            <?php echo $this->form->getLabel('image'); ?>
            <div class="fileupload fileupload-new" data-provides="fileupload">
                <span class="btn btn-file">
                    <span class="fileupload-new"><?php echo JText::_("COM_CROWDFUNDING_SELECT_FILE");?></span>
                    <span class="fileupload-exists">
                        <?php echo JText::_("COM_CROWDFUNDING_CHANGE");?>
                    </span>
                <?php echo $this->form->getInput('image'); ?>
                </span>
                <span class="fileupload-preview"></span>
                <a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float: none">×</a>
            </div>
            
            <?php echo $this->form->getInput('id'); ?>
            <?php echo $this->form->getInput('location'); ?>
            
            <input type="hidden" name="task" value="project.save" />
            <?php echo JHtml::_('form.token'); ?>
            
            <div class="clearfix"></div>
            <button type="submit" class="button button-large margin-tb-15px" <?php echo $this->disabledButton;?>>
            	<i class="icon-ok icon-white"></i>
                <?php echo JText::_("JSAVE")?>
            </button>
        </form>
    </div>
    <?php if($this->imageSmall) {?>
    <div class="span6">
    	<img src="<?php echo $this->imageFolder."/".$this->imageSmall; ?>" class="img-polaroid" />
    	<?php if(!$this->debugMode) {?>
    	<div class="clearfix">&nbsp;</div>
    	<a href="<?php echo JRoute::_("index.php?option=com_crowdfunding&task=project.removeImage&id=".$this->item->id."&".JSession::getFormToken()."=1");?>" class="btn btn-mini" >
    		<i class="icon-trash">
    		</i> <?php echo JText::_("COM_CROWDFUNDING_REMOVE_IMAGE");?>
		</a>
    	<?php }?>
    </div>
    <?php }?>
</div>
<?php echo $this->version->backlink;?>