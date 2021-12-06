<?php

function makePOFile($po_file_name, $lines)
{

	// rename the old file
	if (is_file($po_file_name))
		rename($po_file_name, $po_file_name . '.old');

	// save the new po file
	file_put_contents($po_file_name, implode(PHP_EOL, $lines));
}

function makeMOFile($po_file_name)
{
	$mo_file_name = substr($po_file_name, 0, -2) . 'mo';

	// rename the old file
	if (is_file($mo_file_name))
		rename($mo_file_name, $mo_file_name . '.old');

	// generate the new mo file
	$output = null;
	$return_val = null;
	$cmd = 'msgfmt ' . $po_file_name . ' -o ' . $mo_file_name;
	exec($cmd, $output, $return_val);
}

function escapeString($string)
{
	$string = str_replace("\n", '\\n', $string);

	return str_replace("\"", '\\"', $string);
}
