<?php
require 'flatfile.php';
$db = new Flatfile();
$db->datadir = '';

define('PROVIDER_FILE',     '.nodes');
define('PROVIDER_PEERID',          0);
define('PROVIDER_LASTPING',        1);

if (isset($_GET['port'])) {
	$port = (int) $_GET['port'];
	if ($port > 65535) { $port = 0; }
	$request_peerid = $_SERVER['REMOTE_ADDR'] . ':' . $port;
} else {
	$request_peerid = $_SERVER['REMOTE_ADDR'] . ':44545';
}

$rows = $db->selectWhere(PROVIDER_FILE, new SimpleWhereClause(PROVIDER_PEERID, '=', $request_peerid, STRING_COMPARISON), 1);
if (count($rows) > 0) {
	$db->updateSetWhere(PROVIDER_FILE, array(PROVIDER_LASTPING => time()), new SimpleWhereClause(PROVIDER_PEERID, '=', $request_peerid, STRING_COMPARISON));
} else {
	$entry = array();
	$entry[PROVIDER_PEERID] = $request_peerid;
	$entry[PROVIDER_LASTPING] = time();
	$db->insert(PROVIDER_FILE, $entry);
}

echo "?";

$compClause = new AndWhereClause();
$compClause->add(new SimpleWhereClause(PROVIDER_LASTPING, '>=', (time() - 120), INTEGER_COMPARISON));
$compClause->add(new SimpleWhereClause(PROVIDER_PEERID, '!=', $request_peerid, STRING_COMPARISON));

$rows = $db->selectWhere(PROVIDER_FILE, $compClause);
foreach ($rows as $entry) {
	echo $entry[PROVIDER_PEERID] . "\n";
}
?>