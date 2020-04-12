<?php
$xpdo_meta_map['MailingList']= array (
  'package' => 'mailinglist',
  'version' => '1.0',
  'extends' => 'modResource',
  'tableMeta' => 
  array (
    'engine' => 'MyISAM',
  ),
  'fields' => 
  array (
  ),
  'fieldMeta' => 
  array (
  ),
  'composites' => 
  array (
    'Settings' => 
    array (
      'class' => 'MailingListSettings',
      'local' => 'id',
      'foreign' => 'mailinglist',
      'cardinality' => 'one',
      'owner' => 'local',
    ),
    'Subscribers' => 
    array (
      'class' => 'MailingListSubscribers',
      'local' => 'id',
      'foreign' => 'mailinglist',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Instances' => 
    array (
      'class' => 'MailingListInstance',
      'local' => 'id',
      'foreign' => 'mailinglist',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Queues' => 
    array (
      'class' => 'MailingListQueue',
      'local' => 'id',
      'foreign' => 'mailinglist',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);
