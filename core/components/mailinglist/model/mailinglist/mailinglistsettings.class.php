<?php

class MailingListSettings extends xPDOObject
{
	public function save($cacheFlag = null)
	{
		$new = $this->isNew();
		$parent = parent::save($cacheFlag);

		return $parent;
	}

	public function remove(array $ancestors = array())
	{
		return parent::remove($ancestors);
	}

}