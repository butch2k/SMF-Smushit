<?php

/**
 * This file is a simplified database installer. It does what it is suppoed to.
 */

// If we have found SSI.php and we are outside of SMF, then we are running standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF')) // If we are outside SMF and can't find SSI.php, then throw an error
	die('<b>Error:</b> Cannot install - please verify you put this file in the same place as SMF\'s SSI.php.');

if (SMF == 'SSI')
	db_extend('packages');

global $modSettings, $smcFunc;

// List settings here in the format: setting_key => default_value.  Escape any "s. (" => \")
$mod_settings = array(
	'smushit_attachments_age' => 0,
	'smushit_attachments_png' => 1,
	'smushit_attachment_size' => 125,
);

// Settings to create the new tables...
$tables = array();

// Add a row to an existing table
$rows = array();
$rows[] = array(
	'method' => 'ignore',
	'table_name' => '{db_prefix}scheduled_tasks',
	'columns' => array(
		'next_time' => 'int',
		'time_offset' => 'int',
		'time_regularity' => 'int',
		'time_unit' => 'string',
		'disabled' => 'int',
		'task' => 'string',
	),
	'data' => array (1231542000, 39620, 1, 'd', 1, 'smushit'),
	'keys' => array('id_task'),
);

// Add a column to an existing table
$columns = array();
$columns[] = array(
	'table_name' => '{db_prefix}attachments',
	'if_exists' => 'ignore',
	'error' => 'fatal',
	'parameters' => array(),
	'column_info' => array(
		 'name' => 'smushit',
		 'auto' => false,
		 'default' => 0,
		 'type' => 'tinyint',
		 'size' => 1,
		 'null' => true,
	)
);

// Update mod settings if applicable
foreach ($mod_settings as $new_setting => $new_value)
{
	if (!isset($modSettings[$new_setting]))
		updateSettings(array($new_setting => $new_value));
}

foreach ($tables as $table)
  $smcFunc['db_create_table']($table['table_name'], $table['columns'], $table['indexes'], $table['parameters'], $table['if_exists'], $table['error']);

foreach ($rows as $row)
  $smcFunc['db_insert']($row['method'], $row['table_name'], $row['columns'], $row['data'], $row['keys']);

foreach ($columns as $column)
  $smcFunc['db_add_column']($column['table_name'], $column['column_info'], $column['parameters'], $column['if_exists'], $column['error']);

if (SMF == 'SSI')
   echo 'Congratulations! You have successfully installed this mod!';