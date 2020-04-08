<?php

class MailingListGetExpectedProcessor extends modProcessor
{

    public function process()
	{
        $scriptProperties = $this->getProperties();
		$page = $this->modx->getOption( 'page', $scriptProperties, 1 );
        $limit = $this->modx->getOption( 'limit', $scriptProperties, 20 );
        $offset = $this->modx->getOption( 'start', $scriptProperties, false );
		$this->setToLog($scriptProperties);
		
		$Queues=array();
		$total = 0;
		if($offset===false){$offset = $limit*($page-1);}
		if(!empty($scriptProperties['instance']))
		{
			$Instance = $this->modx->getObject('MailingListInstance',(int)$scriptProperties['instance']);
			$criteria = $this->modx->newQuery('MailingListQueue');
			$criteria->where(array(
			   'status'=>'expected',
			   'instance'=>$Instance->id
			));
			$total = $this->modx->getCount('MailingListQueue',$criteria);
			$criteria->sortby('id','DESC');
			$criteria->limit($limit,$offset);
			$oQueues = $Instance->getMany('Queues',$criteria);
			foreach($oQueues as $Queue)
			{
				$Subscriber = $Queue->getOne('Subscriber');
				$fullname = '';
				switch($Subscriber->get('type'))
				{
					case 'group':
					{
						$qHash = $Queue->get('hash');
						$gHash = $Subscriber->get('hash').'_';
						$salt = str_replace($gHash,'',$qHash);
						$User = $this->modx->getObject('modUser',array('salt'=>$salt));
						$Profile = $User->getOne('Profile');
						$fullname = $Profile->get('fullname');
						break;
					}
					case 'user':
					{
						$User = $Subscriber->getOne('User');
						$Profile = $User->getOne('Profile');
						$fullname = $Profile->get('fullname');
						break;
					}
					case 'anonym':
					{
						$fields = json_decode($Subscriber->get('fields'),true);
						$fullname = $fields['fullname'];
						break;
					}
				}
				$Queues[] = array('id'=>$Queue->id,'email'=>$Queue->get('email'),'fullname'=>$fullname);
			}
		}
		$count = count($Queues);
		
		return $this->outputArray($Queues, $total);
    }
	
	public function setToLog($value)
	{
		ob_start();
		var_dump($value);
		$value = ob_get_contents();
		ob_end_clean();
		file_put_contents(dirname(__FILE__).'/MailingListGetExpectedProcessor.log',$value,FILE_APPEND);
	}
}

return 'MailingListGetExpectedProcessor';