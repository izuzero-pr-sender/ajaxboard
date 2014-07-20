<?php

/**
 * @class  ajaxboardModel
 * @author 이즈야 (contact@ajaxboard.co.kr)
 * @brief  AJAXBoard module model class.
 **/

class ajaxboardModel extends ajaxboard
{
	function init()
	{
	}
	
	function getConfig()
	{
		$oModuleModel = getModel('module');
		$module_config = $oModuleModel->getModuleConfig('ajaxboard');
		if (!$module_config->timeout) $module_config->timeout = 1000;
		
		$this->module_config = $module_config;
		
		return $this->module_config;
	}
	
	function getSkin($skin = NULL, $skin_type = 'P', $site_srl = 0)
	{
		if ($skin == '/USE_DEFAULT/')
		{
			$oModuleModel = getModel('module');
			$skin = $oModuleModel->getModuleDefaultSkin('ajaxboard', $skin_type, $site_srl, false);
		}
		
		return $skin;
	}
	
	function getSkinVars($module_srl = 0)
	{
		$oModuleModel = getModel('module');
		$skin_vars = $oModuleModel->getModuleSkinVars($module_srl);
		
		return $skin_vars;
	}
	
	function getMobileSkinVars($module_srl = 0)
	{
		$oModuleModel = getModel('module');
		$mskin_info = $oModuleModel->getModuleMobileSkinVars($module_srl);
		
		return $mskin_vars;
	}
	
	function arrangeSkinVars($skin_vars)
	{
		$res = new stdClass();
		foreach ($skin_vars as $key=>$val)
		{
			$res->{$key} = $val->value;
		}
		
		return $res;
	}
	
	function getModulesInfo($column_list = array())
	{
		$output = executeQueryArray('ajaxboard.getAllAjaxboard', NULL, array('module_srl'));
		if (!$output->toBool())
		{
			return $output;
		}
		foreach ($output->data as $val)
		{
			$module_srls[] = $val->module_srl;
		}
		
		$oModuleModel = getModel('module');
		$modules_info = $oModuleModel->getModulesInfo($module_srls, $column_list);
		
		return $modules_info;
	}
	
	function getLinkedModuleInfoByModuleSrl($module_srl = 0)
	{
		$modules_info = $this->getModulesInfo();
		if (!$modules_info)
		{
			return NULL;
		}
		foreach ($modules_info as $module_info)
		{
			$module_srl_list = explode('|@|', $module_info->module_srl_list);
			if (in_array($module_srl, $module_srl_list))
			{
				return $module_info;
			}
		}
		
		return NULL;
	}
	
	function getLinkedModuleInfoByMid($mid = NULL)
	{
		if (is_null($mid)) return NULL;
		
		$oModuleModel = getModel('module');
		$module_info = $oModuleModel->getModuleInfoByMid($mid);
		
		return $this->getLinkedModuleInfoByModuleSrl($module_info->module_srl);
	}
	
	function getNotify($config)
	{
		if (!$config)
		{
			$config = new stdClass();
		}
		
		$output = executeQueryArray('ajaxboard.getNotify', $config);
		if (!$output->data)
		{
			$output->data = array();
		}
		
		return $output->data;
	}
	
	function getAllNotify($notified = 'N')
	{
		$args = new stdClass();
		$args->notified = $notified;
		
		$output = executeQueryArray('ajaxboard.getAllNotify', $args);
		if (!$output->data)
		{
			$output->data = array();
		}
		
		return $output->data;
	}
	
	function getModuleSrlList($args = NULL, $column_list = array())
	{
		$column_list = array_merge($column_list, array('module_srl'));
		$output = executeQueryArray('ajaxboard.getModuleSrlList', $args, $column_list);
		if (!$output->toBool())
		{
			return $output;
		}
		if (!$output->data)
		{
			return;
		}
		foreach ($output->data as $val)
		{
			$module_srl_list[$val->module_srl] = $val;
		}
		
		return $module_srl_list;
	}
	
	function getUsableModuleSrlList($module_srl = 0, $args = NULL, $column_list = array())
	{
		$modules_info = $this->getModulesInfo();
		$module_srl_list = $this->getModuleSrlList($args, $column_list);
		
		foreach ($modules_info as $module_info)
		{
			if ($module_info->module_srl == $module_srl)
			{
				continue;
			}
			$module_info->module_srl_list = array_flip(explode('|@|', $module_info->module_srl_list));
			$module_srl_list = array_diff_key($module_srl_list, $module_info->module_srl_list);
		}
		
		return $module_srl_list;
	}
	
	function loadDefaultComponents($target = 'client')
	{
		$module_config = $this->getConfig();
		
		if ($module_config->use_cdn == 'Y')
		{
			Context::loadFile(array(sprintf('///cdn.socket.io/socket.io-%s.js', self::socket_io_version), 'head', NULL, 0));
		}
		else
		{
			Context::loadFile(array(sprintf('%stpl/js/libs/socket.io.js', $this->module_path), 'head', NULL, 0));
		}
		Context::loadFile(array('./common/js/js_app.js', 'head', NULL, -100000));
		Context::loadFile(array(sprintf('%stpl/js/libs/intrinsic.function.js', $this->module_path), 'head', NULL, 0));
		Context::loadFile(array(sprintf('%stpl/js/ajaxboard.%s.js', $this->module_path, $target), 'head', NULL, 0));
	}
	
	function loadSkinComponents($skin = NULL, $dir = 'skins', $site_srl = 0)
	{
		$this->loadDefaultComponents();
		
		$skin = $this->getSkin($skin, $dir === 'skins' ? 'P' : 'M', $site_srl);
		$template_path = sprintf('%s%s/%s/', $this->module_path, $dir, $skin);
		$template_file = 'common';
		
		$oTemplate = TemplateHandler::getInstance();
		$output = $oTemplate->compile($template_path, $template_file);
		
		Context::addHtmlFooter($output);
		
		return $output;
	}
	
	function getAjaxboardWholeVariables()
	{
		$logged_info = Context::get('logged_info');
		$document_srl = Context::get('document_srl');
		$mid = Context::get('mid');
		
		$oModuleModel = getModel('module');
		$module_info = $this->getLinkedModuleInfoByMid($mid);
		$origin_module_info = $oModuleModel->getModuleInfoByMid($mid);
		$module_config = $this->getConfig();
		
		$lang = new stdClass();
		$lang->msg_delete_comment = Context::getLang('msg_delete_comment');
		$lang->msg_password_required = Context::getLang('msg_password_required');
		
		$result = new stdClass();
		$result->lang         = $lang;
		$result->module_path  = $this->module_path;
		$result->module_srl   = $module_info->module_srl;
		$result->member_srl   = $logged_info->member_srl;
		$result->document_srl = $document_srl;
		$result->notify_list  = array_flip(explode('|@|', $module_info->notify_list));
		$result->use_wfsr     = $module_info->use_wfsr;
		$result->timeout      = $module_config->timeout;
		$result->token        = $module_config->token;
		$result->server_uri   = $module_config->server_uri;
		
		if (Mobile::isFromMobilePhone() && $origin_module_info->use_mobile == 'Y')
		{
			if ($module_info->use_module_mobile == 'Y')
			{
				$result->skin_info = $this->arrangeSkinVars($this->getMobileSkinVars($module_info->module_srl));
			}
		}
		else if ($module_info->use_module_pc == 'Y')
		{
			$result->skin_info = $this->arrangeSkinVars($this->getSkinVars($module_info->module_srl));
		}
		
		$this->adds($result);
	}
	
	function getAjaxboardDocument()
	{
		$document_srl = Context::get('document_srl');
		
		$oDocumentModel = getModel('document');
		$oDocument = $oDocumentModel->getDocument($document_srl);
		
		$args = new stdClass();
		$args->is_exists     = $oDocument->isExists();
		$args->is_granted    = $oDocument->isGranted();
		$args->is_accessible = $oDocument->isAccessible();
		if ($args->is_granted || $args->is_accessible)
		{
			$args->module_srl   = $oDocument->get('module_srl');
			$args->document_srl = $oDocument->get('document_srl');
			$args->member_srl   = $oDocument->getMemberSrl();
			$args->title        = $oDocument->getTitleText();
			$args->content      = trim(strip_tags(nl2br($oDocument->getContent(false, false, false, false, false))));
			$args->nickname     = $oDocument->getNickName();
			$args->voted_count  = $oDocument->get('voted_count');
			$args->blamed_count = $oDocument->get('blamed_count');
		}
		
		$this->adds($args);
	}
	
	function getAjaxboardComment()
	{
		$comment_srl = Context::get('comment_srl');
		
		$oCommentModel = getModel('comment');
		$oComment = $oCommentModel->getComment($comment_srl);
		
		if (!$oComment->get('parent_srl'))
		{
			$oDocumentModel = getModel('document');
			$oDocument = $oDocumentModel->getDocument($oComment->get('document_srl'));
			$oComment->add('parent_srl', $oDocument->get('member_srl'));
		}
		
		$args = new stdClass();
		$args->is_exists     = $oComment->isExists();
		$args->is_granted    = $oComment->isGranted();
		$args->is_accessible = $oComment->isAccessible();
		if ($args->is_granted || $args->is_accessible)
		{
			$args->module_srl   = $oComment->get('module_srl');
			$args->parent_srl   = $oComment->get('parent_srl');
			$args->document_srl = $oComment->get('document_srl');
			$args->comment_srl  = $oComment->get('comment_srl');
			$args->member_srl   = $oComment->getMemberSrl();
			$args->content      = trim(strip_tags(nl2br($oComment->getContent(false, false, false))));
			$args->nickname     = $oComment->getNickName();
			$args->voted_count  = $oComment->get('voted_count');
			$args->blamed_count = $oComment->get('blamed_count');
		}
		
		$this->adds($args);
	}
	
	function triggerAfterModuleListInSitemap(&$obj)
	{
		array_push($obj, 'ajaxboard');
	}
}

/* End of file ajaxboard.model.php */
/* Location: ./modules/ajaxboard/ajaxboard.model.php */