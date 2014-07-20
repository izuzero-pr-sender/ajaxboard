<?php

/**
 * @class  ajaxboard
 * @author 이즈야 (contact@ajaxboard.co.kr)
 * @brief  AJAXBoard module high class.
 **/

class ajaxboard extends ModuleObject
{
	const socket_io_version = '1.0.6';
	
	var $triggers = array(
		array('menu.getModuleListInSitemap', 'ajaxboard', 'model', 'triggerAfterModuleListInSitemap', 'after'),
		array('member.getMemberMenu', 'ajaxboard', 'controller', 'triggerAfterMemberMenu', 'after'),
		array('moduleHandler.proc', 'ajaxboard', 'controller', 'triggerAfterModuleHandlerProc', 'after'),
		array('moduleObject.proc', 'ajaxboard', 'controller', 'triggerAfterModuleObjectProc', 'after'),
		array('document.insertDocument', 'ajaxboard', 'controller', 'triggerAfterInsertDocument', 'after'),
		array('document.deleteDocument', 'ajaxboard', 'controller', 'triggerAfterDeleteDocument', 'after'),
		array('document.updateVotedCount', 'ajaxboard', 'controller', 'triggerAfterUpdateVotedDocument', 'after'),
		array('comment.insertComment', 'ajaxboard', 'controller', 'triggerAfterInsertComment', 'after'),
		array('comment.deleteComment', 'ajaxboard', 'controller', 'triggerAfterDeleteComment', 'after'),
		array('comment.updateVotedCount', 'ajaxboard', 'controller', 'triggerAfterUpdateVotedComment', 'after')
	);
	
	/**
	 * @brief Install the module.
	 **/
	function moduleInstall()
	{
		if (!$this->isSupported())
		{
			return new Object();
		}
		
		$oModuleController = getController('module');
		
		foreach ($this->triggers as $trigger)
		{
			$oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
		}
		
		return new Object();
	}
	
	/**
	 * @brief Check module method.
	 **/
	function checkUpdate()
	{
		$oModuleModel = getModel('module');

		foreach ($this->triggers as $trigger)
		{
			if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * @brief Update module.
	 **/
	function moduleUpdate()
	{
		if (!$this->isSupported())
		{
			return new Object();
		}
		
		$oModuleModel      = getModel('module');
		$oModuleController = getController('module');
		
		foreach ($this->triggers as $trigger)
		{
			if (!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]))
			{
				$oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
			}
		}
		
		return new Object(0, 'success_updated');
	}
	
	/**
	 * @brief Uninstall the module.
	 **/
	function moduleUninstall()
	{
		$oModuleModel      = getModel('module');
		$oModuleController = getController('module');

		foreach ($this->triggers as $trigger)
		{
			$oModuleController->deleteTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
		}
		
		$output = executeQueryArray('ajaxboard.getAllAjaxboard');
		if (!($output->data && $output->toBool()))
		{
			return new Object();
		}
		@set_time_limit(0);
		
		foreach ($output->data as $ajaxboard)
		{
			$oModuleController->deleteModule($ajaxboard->module_srl, $ajaxboard->site_srl);
		}
		
		return new Object();
	}
	
	/**
	 * @brief Recompile module cache.
	 **/
	function recompileCache()
	{
	}
	
	/**
	 * @brief 
	 **/
	function isSupported()
	{
		if (function_exists('curl_init'))
		{
			return true;
		}
		return false;
	}
}

/* End of file ajaxboard.class.php */
/* Location: ./modules/ajaxboard/ajaxboard.class.php */