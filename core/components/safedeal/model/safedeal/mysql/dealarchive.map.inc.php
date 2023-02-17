<?php
$xpdo_meta_map['DealArchive']= array (
  'package' => 'safedeal',
  'version' => '1.1',
  'table' => 'safedeal_archive',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'deal_id' => 0,
    'user_id' => 0,
  ),
  'fieldMeta' => 
  array (
    'deal_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'user_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
  ),
);
