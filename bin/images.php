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

$rows = $db->query("SELECT * FROM image WHERE type='image' AND width=0")->fetchAll();
foreach ($rows as $i => $row) {
	try {
		$image = \Nette\Utils\Image::fromFile("https://cms.freetech.cz/images/userimages/original/" . $row->hash . '.' . $row->mime);
		$db->query("UPDATE image SET width=?, height=? WHERE id=?", $image->width, $image->height, $row->id);
	} catch(Exception $e) {
		echo $e->getMessage() . "\n";
	}
	echo $i .'/' . count($rows) . "\n";
}
