<?php

/**
 * @class  ajaxboardAdminController
 * @author 이즈야 (contact@ajaxboard.co.kr)
 * @brief  AJAXBoard module admin controller class.
 **/

class ajaxboardAdminController extends ajaxboard
{
	/**
	 * @brief Initialization.
	 **/
	function init()
	{
	}
	
	/**
	 * @brief 
	 **/
	function procAjaxboardAdminInsertConfig()
	{
		$oModuleController = getController('module');
		
		$args   = Context::getRequestVars();
		$output = $oModuleController->insertModuleConfig('ajaxboard', $args);
		if (!$output->toBool())
		{
			return $output;
		}
		
		$this->setMessage('success_updated');
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAjaxboardAdminConfig'));
	}
	
	/**
	 * @brief 
	 **/
	function procAjaxboardAdminInsertAjaxboard()
	{
		$oModuleModel = getModel('module');
		$oModuleController = getController('module');
		
		$args = Context::getRequestVars();
		$args->module = 'ajaxboard';
		$args->module_srl_list = implode('|@|', $args->module_srl_list);
		$args->notify_list = implode('|@|', $args->notify_list);
		
		if ($args->module_srl)
		{
			$module_info = $oModuleModel->getModuleInfoByModuleSrl($args->module_srl);
			if ($module_info->module_srl != $args->module_srl)
			{
				unset($args->module_srl);
			}
		}
		if ($args->module_srl)
		{
			$output = $oModuleController->updateModule($args);
			$msg_code = 'success_updated';
		}
		else
		{
			$output = $oModuleController->insertModule($args);
			$msg_code = 'success_registed';
		}
		if (!$output->toBool())
		{
			return $output;
		}
		
		$this->setMessage($msg_code);
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAjaxboardAdminAjaxboardInfo', 'module_srl', $output->get('module_srl')));
	}
	
	/**
	 * @brief delete the ajaxboard module.
	 **/
	function procAjaxboardAdminDeleteAjaxboard()
	{
		$oModuleController = getController('module');
		
		$module_srl = Context::get('module_srl');
		$output = $oModuleController->deleteModule($module_srl);
		if (!$output->toBool())
		{
			return $output;
		}
		
		$this->setMessage('success_deleted');
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAjaxboardAdminContent'));
	}
	
	function procAjaxboardAdminSendPush()
	{
		$message = Context::get('message');
		$notice = Context::get('notice');
		$receiver_srl = Context::get('receiver_srl');
		$receiver_srls = Context::get('receiver_srls');
		
		if (!$message)
		{
			return new Object(-1, 'msg_invalid_request');
		}
		if ($notice != 'Y' && !$receiver_srl && !(is_array($receiver_srls) && count($receiver_srls)))
		{
			return new Object(-1, 'msg_not_exists_member');
		}
		if ($notice == 'Y')
		{
			$oAjaxboardModel = getModel('ajaxboard');
			$module_config = $oAjaxboardModel->getConfig();
			
			$args = array(
				'type'            => 'noticeOfServer',
				'receiver_tokens' => $module_config->token,
				'message'         => $message
			);
		}
		else if ($receiver_srl)
		{
			$oMemberModel = getModel('member');
			$receiver_info = $oMemberModel->getMemberInfoByMemberSrl($receiver_srl);
			if ($receiver_info->member_srl != $receiver_srl)
			{
				return new Object(-1, 'msg_not_exists_member');
			}
			
			$args = array(
				'type'          => 'notice',
				'receiver_srls' => $receiver_srl,
				'message'       => $message
			);
		}
		else
		{
			$message = nl2br(htmlspecialchars($message, ENT_COMPAT | ENT_HTML401, 'UTF-8', false));
			$receiver_srls = implode(',', $receiver_srls);
			
			$args = array(
				'type'          => 'notice',
				'receiver_srls' => $receiver_srls,
				'message'       => $message
			);
		}
		
		$oAjaxboardController = getController('ajaxboard');
		$oAjaxboardController->emitEvent($args);
		
		if (Context::get('is_popup') != 'Y')
		{
			$this->setMessage('success_sended');
			$this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAjaxboardAdminSendPush'));
		}
		else
		{
			htmlHeader();
			alertScript(Context::getLang('success_sended'));
			closePopupScript();
			htmlFooter();
			Context::close();
			exit();
		}
	}
}

/* End of file ajaxboard.admin.controller.php */
/* Location: ./modules/ajaxboard/ajaxboard.admin.controller.php */