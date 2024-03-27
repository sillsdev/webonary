<?php

function makePOFile($po_file_name, $lines, $rename_existing = true): void
{
	// rename the old file
	if (is_file($po_file_name) && $rename_existing)
		rename($po_file_name, $po_file_name . '.old');

	// save the new po file
	file_put_contents($po_file_name, implode(PHP_EOL, $lines));
}

function makeMOFile($po_file_name, $rename_existing = true): void
{
	$mo_file_name = substr($po_file_name, 0, -2) . 'mo';

	// rename the old file
	if (is_file($mo_file_name) && $rename_existing)
		rename($mo_file_name, $mo_file_name . '.old');

	// generate the new mo file
	$output = null;
	$cmd = 'msgfmt ' . $po_file_name . ' -o ' . $mo_file_name;
	exec($cmd, $output);
}

function escapeString($string): string
{
	return str_replace("\n", '\\n', $string);

	// return str_replace("\"", '\\"', $string);
}

function addOrReplaceInPO(string $key, string $value, array &$po_list, bool $do_not_add = false, string $comment = 'extra string'): void
{
	$e_key = escapeString($key);
	$e_val = escapeString($value);

	$find = 'msgid "' . $e_key . '"';
	$idx = array_search($find, $po_list);

	if ($idx === false) {

		if ($do_not_add)
			return;

		// add a blank line
		if ($po_list[count($po_list) - 1] != '')
			$po_list[] = '';

		$po_list[] = '#: ' . $comment;
		$po_list[] = 'msgid "' . $e_key . '"';
		$po_list[] = 'msgstr "' . $e_val . '"';
	}
	else {
		$po_list[$idx + 1] = 'msgstr "' . $e_val . '"';
	}
}
