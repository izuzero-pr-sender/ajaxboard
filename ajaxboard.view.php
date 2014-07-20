<?php

/**
 * @class  ajaxboardView
 * @author 이즈야 (contact@ajaxboard.co.kr)
 * @brief  AJAXBoard module view class.
 **/

class ajaxboardView extends ajaxboard
{
	/**
	 * @brief Initialization.
	 **/
	function init()
	{
		$oAjaxboardModel = getModel('ajaxboard');
		
		$module_config = $oAjaxboardModel->getConfig();
		unset($module_config->token, $module_config->private_key);
		
		Context::set('module_config', $module_config);
		Context::set('socket_io_version', self::socket_io_version);
		
		$template_path = sprintf('%sskins/%s/', $this->module_path, $this->module_info->skin);
		if (!(is_dir($template_path) && $this->module_info->skin))
		{
			$this->module_info->skin = 'default';
			$template_path = sprintf('%sskins/%s/', $this->module_path, $this->module_info->skin);
		}
		$this->setTemplatePath($template_path);
	}
	
	/**
	 * @brief 
	 **/
	function dispAjaxboardServerInfo()
	{
		if (!$this->grant->access)
		{
			return new Object(-1, 'msg_not_permitted');
		}
		$oAjaxboardModel = getModel('ajaxboard');
		$oAjaxboardModel->loadDefaultComponents('admin');
		$this->setTemplateFile('server_info');
	}
}

/* End of file ajaxboard.view.php */
/* Location: ./modules/ajaxboard/ajaxboard.view.php */