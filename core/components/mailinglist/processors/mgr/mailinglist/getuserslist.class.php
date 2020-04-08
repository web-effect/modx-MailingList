<?php

class MailingListGetUsersProcessor extends modProcessor
{

    public function process()
	{
        $scriptProperties = $this->getProperties();
		//$this->setToLog($scriptProperties);
		$NodeID = $scriptProperties['id'];
		$scriptProperties['id'] = str_replace('group_','',$scriptProperties['id']);
		$scriptProperties['id'] = str_replace('user_','',$scriptProperties['id']);
		$scriptProperties['currentState'] = json_decode($scriptProperties['currentState'],true);
		
		
		if(!empty($scriptProperties['mailinglist']))
		{
			
		}
		
		
		if($scriptProperties['id']=='root')
		{
			$Groups = array();
			$modGroups = $this->modx->getCollection('modUserGroup',array('parent'=>0));
			foreach($modGroups as $Group)
			{
				$isChecked = isset($scriptProperties['currentState']['group_'.$Group->get('id')]);
				$Groups[] = array
				(
					'text' => $Group->get('name'),
					'id' => 'group_'.$Group->get('id'),
					'type' => 'Group',
					'classKey' => 'Group',
					'allowDrop' => false,
					'hasChildren' => true,
					'expanded' => false,
					'checked'=>$isChecked,
				);
			}
			return $this->modx->toJSON($Groups);
		}
		if($scriptProperties['type']=='Group')
		{
			$Nodes = array();
			$modGroup = $this->modx->getObject('modUserGroup',(int)$scriptProperties['id']);
			$isChecked = $this->isChecked($scriptProperties,$NodeID,$modGroup);
			$modGroups = $modGroup->getMany('Children');
			$checkAll = false;
			if($isChecked&&!$this->hasExcluded($scriptProperties,$NodeID,$modGroup))$checkAll = true;
			if(!empty($modGroups))
			{
				foreach($modGroups as $Group)
				{
					$nodeIsChecked = false;
					if($checkAll)$nodeIsChecked=true;
					else
					{
						if($isChecked&&!in_array('group_'.$Group->get('id'),$scriptProperties['currentState'][$NodeID]['exclude']))$nodeIsChecked = true;
						if(!$isChecked){$nodeIsChecked = isset($scriptProperties['currentState']['group_'.$Group->get('id')]);}
					}
					$Nodes[] = array
					(
						'text' => $Group->get('name'),
						'id' => 'group_'.$Group->get('id'),
						'type' => 'Group',
						'classKey' => 'Group',
						'allowDrop' => false,
						'hasChildren' => true,
						'expanded' => false,
						'checked'=>$nodeIsChecked,
					);
				}
			}
			$modMembers = $modGroup->getMany('UserGroupMembers');
			if(!empty($modMembers))
			{
				foreach($modMembers as $Member)
				{
					$User = $Member->getOne('User');
					$isChild = false;
					$AnotherGroups = $User->getMany('UserGroupMembers');
					if(count($AnotherGroups)>1)
					{
						foreach($AnotherGroups as $AnotherGroup)
						{
							if($AnotherGroup->get('group_id')!=$modGroup->get('id'))
							{
								$isChild = $this->isChildOf($AnotherGroup->getOne('UserGroup'),$modGroup->get('id'));
								if($isChild)break;
							}
						}
					}
					if(!$isChild)
					{
						$nodeIsChecked = false;
						if($checkAll)$nodeIsChecked=true;
						else
						{
							if($isChecked&&!in_array('user_'.$User->get('id'),$scriptProperties['currentState'][$NodeID]['exclude']))$nodeIsChecked = true;
							if(!$isChecked){$nodeIsChecked = isset($scriptProperties['currentState']['user_'.$User->get('id')]);}
						}
						$Nodes[] = array
						(
							'text' => $User->get('username').' ('.$User->get('id').')',
							'id' => 'user_'.$User->get('id'),
							'type' => 'User',
							'classKey' => 'User',
							'allowDrop' => false,
							'hasChildren' => false,
							'children' => array(),
							'expanded' => true,
							'checked'=>$nodeIsChecked,
						);
					}
				}
			}
			return $this->modx->toJSON($Nodes);
		}
		if($scriptProperties['type']=='User')
		{
			$Users=array();
			return '[]';
		}
    }
	
	public function isChildOf($Group,$parent_id)
	{
		$Parent = $Group->getOne('Parent');
		if(!$Parent)return false;
		if($Parent->get('id')==$parent_id)return true;
		return $this->isChildOf($Parent,$parent_id);
	}
	
	public function isChecked($scriptProperties,$NodeID,$Group,$Subs=array())
	{
		if(isset($scriptProperties['currentState'][$NodeID]))
		{
			if(!empty($Subs)&&!empty($scriptProperties['currentState'][$NodeID]['exclude']))
			{
				foreach($Subs as $Sub)
				{
					if(in_array($Sub,$scriptProperties['currentState'][$NodeID]['exclude']))return false;
				}
			}
			return true;
		}
		$Parent = $Group->getOne('Parent');
		if(!$Parent)return false;
		$pNodeID = 'group_'.$Parent->get('id');
		$Subs[] = $NodeID;
		return $this->isChecked($scriptProperties,$pNodeID,$Parent,$Subs);
	}
	
	public function hasExcluded($scriptProperties,$NodeID,$Group,$Subs=array())
	{
		if(!empty($scriptProperties['currentState'][$NodeID]['exclude']))
		{
			return true;
		}
		return false;
	}
	
	public function setToLog($value)
	{
		ob_start();
		var_dump($value);
		$value = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__FILE__).'/MailingListGetUsersProcessor.log',$value,FILE_APPEND);
	}
}

return 'MailingListGetUsersProcessor';