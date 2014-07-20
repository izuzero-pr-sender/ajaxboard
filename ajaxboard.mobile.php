<?php

/**
 * @class  ajaxboardMobile
 * @author 이즈야 (contact@ajaxboard.co.kr)
 * @brief  AJAXBoard module mobile class.
 **/

require_once(_XE_PATH_ . 'modules/ajaxboard/ajaxboard.view.php');

class ajaxboardMobile extends ajaxboardView
{
	function init()
	{
		$oAjaxboardModel = getModel('ajaxboard');
		
		$module_config = $oAjaxboardModel->getConfig();
		unset($module_config->token, $module_config->private_key);
		
		Context::set('module_config', $module_config);
		Context::set('socket_io_version', self::socket_io_version);
		
		$template_path = sprintf('%sm.skins/%s/', $this->module_path, $this->module_info->mskin);
		if (!(is_dir($template_path) && $this->module_info->mskin))
		{
			$this->module_info->mskin = 'default';
			$template_path = sprintf('%sm.skins/%s/', $this->module_path, $this->module_info->mskin);
		}
		$this->setTemplatePath($template_path);
	}
}

/* End of file ajaxboard.mobile.php */
/* Location: ./modules/ajaxboard/ajaxboard.mobile.php */