<?php
if(!function_exists('GroupGetAllChilds'))
{
	function GroupGetAllChilds($Group,&$arr)
	{
		$Childs = $Group->getMany('Children');
		if(!empty($Childs))
		{
			foreach($Childs as $Child)
			{
				$arr[]=$Child->id;
				GroupGetAllChilds($Child,$arr);
			}
		}
	}
}
if(!function_exists('GroupGetAllParents'))
{
	function GroupGetAllParents($Group,&$arr)
	{
		$Parent = $Group->getOne('Parent');
		if($Parent)
		{
			$arr[]=$Parent->id;
			GroupGetAllParents($Parent,$arr);
		}
	}
}

if(!empty($hash))
{
	$modx->addPackage('mailinglist',MODX_CORE_PATH."components/mailinglist/model/");
	$hash = explode('_',$hash);
	$Subscriber = $modx->getObject('MailingListSubscribers',array('hash'=>$hash[0]));
	if($Subscriber)
	{
		switch($Subscriber->get('type'))
		{
			case 'group':
			{
				$User = $modx->getObject('modUser',array('salt'=>$hash[1]));
				if($User)
				{
					$exclude = json_decode($Subscriber->get('exclude'),true);
					if(!in_array('user_'.$User->id,$exclude))
					{
						$exclude[] = 'user_'.$User->id;
					}
					$Subscriber->set('exclude',json_encode($exclude));
					$Subscriber->save();
					if($deleteFromGroup||$deleteFromChilds||$seleteFromParents)
					{
						$userGroups = $User->getMany('UserGroupMembers');
						$GroupsIDs = array();
						$Group = $Subscriber->getOne('Group');
						if($Group&&$deleteFromGroup)
						{
							$GroupsIDs[]=$Group->id;
						}
						if($Group&&$deleteFromChilds)
						{
							$GroupChilds = array();
							GroupGetAllChilds($Group,$GroupChilds);
							if(!empty($GroupChilds))
							{
								$GroupsIDs=array_merge($GroupsIDs,$GroupChilds);
							}
						}
						if($Group&&$deleteFromParents)
						{
							$GroupParents = array();
							GroupGetAllParents($Group,$GroupParents);
							if(!empty($GroupParents))
							{
								$GroupsIDs=array_merge($GroupsIDs,$GroupParents);
							}
						}
						if($userGroups&&$Group)
						{
							foreach($userGroups as $userGroup)
							{
								
								if(in_array($userGroup->get('user_group'),$GroupsIDs))
								{
									$userGroup->remove();
								}
							}
						}
						$User->save();
					}
				}
				$modx->setPlaceholder('unsubscribe_succes','1');
				break;
			}
			case 'user':
			{
				$Subscriber->remove();
				$modx->setPlaceholder('unsubscribe_succes','1');
				break;
			}
			case 'anonym':
			{
				$Subscriber->remove();
				$modx->setPlaceholder('unsubscribe_succes','1');
				break;
			}
		}
	}
}