<?php
$xpdo_meta_map['MailingListInstance']= array (
  'package' => 'mailinglist',
  'version' => '1.0',
  'table' => 'mailinglist_instances',
  'extends' => 'xPDOObject',
  'fields' => 
  array (
    'id' => 0,
    'mailinglist' => 0,
    'status' => NULL,
    'start_date' => NULL,
    'end_date' => NULL,
    'task' => NULL,
  ),
  'fieldMeta' => 
  array (
    'id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'attributes' => 'unsigned',
      'null' => false,
      'default' => 0,
      'index' => 'pk',
    ),
    'mailinglist' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'attributes' => 'unsigned',
      'null' => false,
      'default' => 0,
    ),
    'status' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => NULL,
    ),
    'start_date' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => null,
    ),
    'end_date' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => null,
    ),
    'task' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => NULL,
    ),
  ),
  'indexes' => 
  array (
    'PRIMARY' => 
    array (
      'alias' => 'PRIMARY',
      'primary' => true,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => 
      array (
        'id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
  'composites' => 
  array (
    'Queues' => 
    array (
      'class' => 'MailingListQueue',
      'local' => 'id',
      'foreign' => 'instance',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
  'aggregates' => 
  array (
    'MailingList' => 
    array (
      'class' => 'MailingList',
      'local' => 'mailinglist',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
