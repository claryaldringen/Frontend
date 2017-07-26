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

$menuId = [30 => 83, 31 => 82, 33 => 79, 34 => 81, 35 => 80];

$rows = $db->query("SELECT * FROM clanky WHERE kategorie IN (30,31,33,34,35)")->fetchAll();
foreach ($rows as $row) {
	$text = '<h1>' . $row->titulek . '</h1>' . $row->obsah;
	$db->query("INSERT INTO text", ['url' => $row->permalink, 'text' => $text]);
	$textId = $db->getInsertId();
	$db->query("INSERT INTO [name]", ['modul' => 'article']);
	$nameId = $db->getInsertId();
	$db->query("INSERT INTO name_has_text", ['name_id' => $nameId, 'text_id' => $textId, 'language_id' => 36]);
	$db->query("INSERT INTO article", ['name_id' => $nameId, 'menu_id' => $menuId[$row->kategorie], 'sort' => 10]);
}

