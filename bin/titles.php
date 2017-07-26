<?php

require dirname(__FILE__) . '/../../Library/vendor/autoload.php';

$options = [
	'driver'   => 'mysqli',
	'host'     => 'localhost',
	'username' => 'root',
	'password' => '',
	'database' => '318_cms',
];

// v případě chyby vyhodí Dibi\Exception
$db = new Dibi\Connection($options);

$sql = "SELECT a.id,language_id,t.text FROM article a
JOIN name_has_text nht ON nht.name_id=a.name_id
JOIN text t ON t.id=nht.text_id
WHERE a.title_name_id IS NULL
";

$rows = $db->query($sql)->fetchAll();
foreach ($rows as $i => $row) {
	$parts = explode('</h', $row->text);
	$title = trim(substr($parts[0], 4));
	$db->query("INSERT INTO text", ['url' => \Nette\Utils\Strings::webalize($title), 'text' => $title]);
	$textId = $db->getInsertId();
	$db->query("INSERT INTO [name]", ['modul' => 'article']);
	$nameId = $db->getInsertId();
	$db->query("INSERT INTO name_has_text", ['name_id' => $nameId, 'text_id' => $textId, 'language_id' => $row->language_id]);
	$db->query("UPDATE article SET title_name_id=? WHERE id=?", $nameId, $row->id);
	echo $i .'/' . count($rows) . "\n";
}