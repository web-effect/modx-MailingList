<?php
$xpdo_meta_map['MailingListSettings']= array (
  'package' => 'mailinglist',
  'version' => '1.0',
  'table' => 'mailinglist_settings',
  'extends' => 'xPDOObject',
  'fields' => 
  array (
    'mailinglist' => 0,
    'emailsubject' => NULL,
    'emailfrom' => NULL,
    'emailfromname' => NULL,
    'emailreplyto' => NULL,
    'emailreplytoname' => NULL,
    'attachments' => NULL,
  ),
  'fieldMeta' => 
  array (
    'mailinglist' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'attributes' => 'unsigned',
      'null' => false,
      'default' => 0,
      'index' => 'pk',
    ),
    'emailsubject' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => NULL,
    ),
    'emailfrom' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => NULL,
    ),
    'emailfromname' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => NULL,
    ),
    'emailreplyto' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => NULL,
    ),
    'emailreplytoname' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => NULL,
    ),
    'attachments' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => NULL,
    ),
  ),
  'indexes' => 
  array (
    'unique_key' => 
    array (
      'alias' => 'unique_key',
      'primary' => true,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => 
      array (
        'mailinglist' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
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
