<?php

/**
 * This file is a simplified database uninstaller. It does what it is suppoed to.
 */

// If we have found SSI.php and we are outside of SMF, then we are running standalone.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF')) // If we are outside SMF and can't find SSI.php, then throw an error
	die('<b>Error:</b> Cannot uninstall - please verify you put this file in the same place as SMF\'s SSI.php.');

if (SMF == 'SSI')
	db_extend('packages');

global $modSettings, $smcFunc;

// List all mod settingss here to REMOVE
$mod_settings_to_remove = array(
	'smushit_attachments_age',
	'smushit_attachments_png',
	'smushit_attachment_size',
);

// REMOVE entire tables...
$tables = array();

// REMOVE columns from an existing table
$columns = array();
$columns[] = array(
	'table_name' => '{db_prefix}attachments',
	'column_name' => 'smushit',
	'parameters' => array(),
	'error' => 'fatal',
);

// REMOVE rows from an existing table
$smcFunc['db_query']('', '
	DELETE FROM {db_prefix}scheduled_tasks
	WHERE task = {string:name}',
	array(
		'name' => 'smushit',
	)
);

if (count($mod_settings_to_remove) > 0) {

	// Remove the mod_settings if applicable, first the session
	foreach ($mod_settings_to_remove as $setting)
		if (isset($modSettings[$setting]))
			unset($modSettings[$setting]);

	// And now the database values
	$smcFunc['db_query']('', '
		DELETE FROM {db_prefix}settings
		WHERE variable IN ({array_string:settings})',
		array(
			'settings' => $mod_settings_to_remove,
		)
	);

	// Make sure the cache is reset as well
	updateSettings(array(
		'settings_updated' => time(),
	));
}

foreach ($tables as $table)
  $smcFunc['db_drop_table']($table['table_name'], $table['parameters'], $table['error']);

foreach ($columns as $column)
  $smcFunc['db_remove_column']($column['table_name'], $column['column_name'], $column['parameters'], $column['error']);

if (SMF == 'SSI')
   echo 'Congratulations! You have successfully removed the integration hooks.';