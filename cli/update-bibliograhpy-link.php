<?php /** @noinspection PhpComposerExtensionStubsInspection */

print 'Loading config...' . PHP_EOL;

include_once 'cli-config.php';

function RunSQL(mysqli $cn, string $sql): void
{
	CloseRecordset($cn);
	$result = $cn->multi_query($sql);
	if ($result === false) trigger_error($cn->error, E_USER_ERROR);
	$cn->store_result();
}

function CloseRecordset(mysqli $cn): void
{
	global $rs;
	do {
		if (!is_null($rs)) {
			$rs->free();
			$rs = null;
		}

		if ($cn->more_results()) $cn->next_result();
	} while ($cn->more_results());
}

function OpenRecordset(mysqli $cn, string $sql): mysqli_result
{
	CloseRecordset($cn);

	$stmt = $cn->prepare($sql);
	if ($stmt === false)
		trigger_error($cn->error, E_USER_ERROR);

	$result = $stmt->execute();
	if ($result === false)
		trigger_error($cn->error, E_USER_ERROR);

	return $stmt->get_result();
}

function RecordsetToArray(mysqli_result $rs): array
{
	$return_val = array();

	while ($row = $rs->fetch_object()) {
		$return_val[] = $row;
	}

	return $return_val;
}

/** @var mysqli_result $rs */
$rs = null;

print 'Opening data connection...' . PHP_EOL;

// connect to the MySQL database
$cn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);

$cn->set_charset('utf8mb4');
RunSQL($cn, "SET collation_connection = 'utf8mb4_unicode_ci';");
RunSQL($cn, 'SET SESSION wait_timeout=120');  // 120 seconds = 2 minutes
$cn->select_db(DB_NAME);

print 'Getting list of blogs...' . PHP_EOL;

// get the list of blogs
$sql = <<<SQL
SELECT blog_id, path FROM webonary.wp_blogs
SQL;

$rs = OpenRecordset($cn, $sql);
$blogs = RecordsetToArray($rs);

$exists_sql = <<<SQL
SELECT COUNT(*) AS count_of
FROM information_schema.tables
WHERE table_schema = 'webonary'
  AND table_name = 'wp_%s_postmeta';
SQL;

$update_sql = <<<SQL
UPDATE webonary.wp_@id@_postmeta
SET meta_value = REGEXP_REPLACE(meta_value, 'https?://.*sil.org/search/node/([a-zA-Z]+)', 'https://www.sil.org/resources/search/language/\\\\1')
WHERE meta_value LIKE '%sil.org/search/node/%'
SQL;

foreach ($blogs as $blog) {

	$sql = sprintf($exists_sql, $blog->blog_id);
	$rs = OpenRecordset($cn, $sql);
	$row = $rs->fetch_object();
	$found = !empty($row->count_of);

	if (!$found)
		continue;

	print 'Updating blog ' . trim($blog->path, '/') . '...' . PHP_EOL;

	$sql = str_replace('@id@', $blog->blog_id, $update_sql);
	RunSQL($cn, $sql);
}

print 'Finished.' . PHP_EOL;
