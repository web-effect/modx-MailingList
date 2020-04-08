<?php
if($hook)
{
	$modx->addPackage('mailinglist',MODX_CORE_PATH."components/mailinglist/model/");
	$componentOptions = array();
	foreach($scriptProperties as $setting_name=>$setting_value)
	{
	    if(strpos($setting_name,'ml_subscribe_')!==0)continue;
	    $param_name = str_replace('ml_subscribe_','',$setting_name);
	    $setting_value = $hook->_process($setting_value, $fields);
	    $componentOptions[lcfirst($param_name)] = $setting_value;
	}
	
	$useCondition = false;
	if(!empty($componentOptions['if']))$useCondition=true;
	
	if((!$useCondition||($useCondition&&$componentOptions['if']==1))&&!empty($componentOptions['to'])&&in_array($componentOptions['type'],array('group','user','anonym')))
	{
		$Subscribed = false;
		switch($componentOptions['type'])
		{
			case 'group':
			{
				$groupId = $fields['scriptProperties']['subscribeGroup'];
				$userGroup = $modx->getObject('modUserGroup',array('id' => $componentOptions['to']));
				if (!$userGroup) return true;
				$role = $modx->getObject('modUserGroupRole',array('name' => 'Member'));
				$member = $modx->newObject('modUserGroupMember');
				$member->set('member',$fields['register.user']->get('id'));
			    $member->set('user_group',$userGroup->get('id'));
			    $member->set('role',$role->get('id'));
			    $fields['register.user']->addMany($member,'UserGroupMembers');
			    $fields['register.user']->save();
			    $Subscribed = true;
			    break;
			}
			case 'user':
			{
				$MailingList = $modx->getObject('MailingList',(int)$componentOptions['to']);
				if(!$MailingList)return true;
				$newSubscriber = $modx->newObject('MailingListSubscribers');
				$newSubscriber->set('mailinglist',(int)$componentOptions['to']);
				$newSubscriber->set('type','user');
				$newSubscriber->set('object_id',$fields['register.user']->get('id'));
				$newSubscriber->set('hash',md5('user'.$fields['register.user']->get('id').$componentOptions['to']));
				$newSubscriber->save();
				$Subscribed = true;
				break;
			}
			case 'anonym':
			{
				$MailingList = $modx->getObject('MailingList',(int)$componentOptions['to']);
				if(!$MailingList)return true;
				$criteria = $this->modx->newQuery('MailingListSubscribers');
				$criteria->where(array(
				   'type'=>'anonym',
				   'mailinglist'=>(int)$componentOptions['to'],
				));
				$criteria->sortby('object_id','DESC');
				$Anonymous = $modx->getCollection('MailingListSubscribers',$criteria);
				$anon_id=0;
				if(!empty($Anonymous))$anon_id = (int)$Anonymous[0]->get('object_id')+1;
				$newSubscriber = $modx->newObject('MailingListSubscribers');
				$newSubscriber->set('mailinglist',(int)$componentOptions['to']);
				$newSubscriber->set('type','anonym');
				$newSubscriber->set('object_id',$anon_id);
				$newSubscriber->set('hash',md5('anonym'.$anon_id.$componentOptions['to']));
				$sfields = array();
				$sfields['email'] = $componentOptions['anonymEmail'];
				$sfields['fullname'] = $componentOptions['anonymFullname'];
				$newSubscriber->set('fields',json_encode($sfields));
				$newSubscriber->save();
				$Subscribed = true;
				break;
			}
		}
		if($Subscribed&&$componentOptions['sendEmail']==1)
		{
			$emailOptions=array();
    		foreach($componentOptions as $setting_name=>$setting_value)
		    {
		    	if(strpos($setting_name,'email')!==0)continue;
			    $param_name = str_replace('email','',$setting_name);
			    $emailOptions[lcfirst($param_name)] = $setting_value;
		    }
		    
		    $placeholders = $fields;
		    $placeholders['user'] = $fields['register.user']->toArray();
		    $emailOptions["message"] = $modx->getChunk($emailOptions['tpl'],$placeholders);
		    $emailOptions["sender"] = $modx->getOption('sender',$emailOptions,$modx->getOption('emailsender'));
		    $emailOptions["html"] = $modx->getOption('html',$emailOptions,true);
			$modx->parser->processElementTags('',$message,true,false);
			
			$modx->getService('mail', 'mail.modPHPMailer');
			$modx->mail->reset();
			$modx->mail->set(modMail::MAIL_BODY,$emailOptions["message"]);
			$modx->mail->set(modMail::MAIL_FROM,$emailOptions["from"]);
			$modx->mail->set(modMail::MAIL_FROM_NAME,$emailOptions["fromName"]);
			$modx->mail->set(modMail::MAIL_SENDER,$emailOptions["sender"]);
			$modx->mail->set(modMail::MAIL_SUBJECT,$emailOptions["subject"]);
			$emailOptions["to"] = explode(',',$emailOptions["to"]);

			foreach($emailOptions["to"] as $emailto)
			{
				$modx->mail->address('to',$emailto);
			}
			$modx->mail->setHTML($emailOptions["html"]);
			$modx->mail->address('reply-to',$emailOptions["replyTo"],$emailOptions["replyToName"]);
			
		    if (!$modx->mail->send())
		    {
		        $modx->log(modX::LOG_LEVEL_ERROR,'[FormIt] An error occurred while trying to send the auto-responder email: '.$modx->mail->mailer->ErrorInfo);
		    }
			$modx->mail->reset();
		}
	}
	return true;
}