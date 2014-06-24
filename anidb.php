<?php

/**
 * Konata season summary (version 1.0 released on 24.6.2014, http://www.konata.cz)
 *
 * Copyright (c) 2014 Václav Vrbka (aurielle@aurielle.cz)
 */

namespace Aurielle\KonataSeasonSummary;
use Nette;
use Kdyby\Curl;

require_once __DIR__ . '/vendor/autoload.php';
Nette\Diagnostics\Debugger::enable(FALSE, __DIR__ . '/log');
Nette\Utils\SafeStream::register();


// Config loading
$config = __DIR__ . '/config.php';
if (!file_exists($config)) {
	echo 'Config file is missing. Example config should be present, so go by instructions inside.' . PHP_EOL;
	exit;
}

require_once $config;

// Image dir check
if (!is_dir($imgDir) || !is_writable($imgDir)) {
	echo 'Specified image dir does not exist or is not writable.' . PHP_EOL;
	exit;
}


// Load IDs
$c = file_get_contents($source);
$ids = explode("\n", $c);
$info = array();

foreach ($ids as $id) {
	$id = trim($id);
	if (!ctype_digit($id)) {
		echo "!!! Invalid ID found: $id" . PHP_EOL;
		continue;
	}

	// Build the URL
	$url = "http://api.anidb.net:9001/httpapi?request=anime&client=$client&clientver=$version&protover=1&aid=$id";

	// And download the XML
	$ch = new Curl\Request($url);
	$ch->setTimeout($timeout);
	$ch->options['encoding'] = '';  // This makes curl decode the gzip
	$res = $ch->send();

	$xml = new \SimpleXMLElement($res->getResponse());
	$item = new \stdClass();

	$stud = $xml->xpath('/anime/creators/name[@type="Animation Work"]');
	if (!count($stud)) {
		$stud = $xml->xpath('/anime/creators/name[@type="Work"]');
	}

	$studio = array();
	foreach ($stud as $s) {
		$studio[] = (string) $s;
	}

	$item->id = $id;
	$item->name = (string) $xml->xpath('/anime/titles/title[@type="main"]')[0];
	$item->pic = (string) $xml->xpath('/anime/picture')[0];
	$item->url = (string) $xml->xpath('/anime/url')[0];
	$item->studio = $studio;
	$item->premiere = new \DateTime((string) $xml->xpath('/anime/startdate')[0]);

	$info[] = $item;

	// Download image
	$fh = fopen('safe://' . $imgDir . '/' . $item->pic, 'wb');
	$ch = new Curl\Request('http://img7.anidb.net/pics/anime/' . $item->pic);
	$ch->setTimeout($timeout);
	$res = $ch->send();
	fwrite($fh, $res->getResponse());
	fclose($fh);

	$img = Nette\Image::fromString($res->getResponse());
	$img->resize(150, 150, Nette\Image::EXACT);
	$img->save($imgDir . '/' . pathinfo($item->pic, PATHINFO_FILENAME) . '_thumb.' . pathinfo($item->pic, PATHINFO_EXTENSION));

	// And wait
	sleep(2);
}

if (empty($info)) {
	echo 'Nothing to render.' . PHP_EOL;
	exit;
}

// template
$template = new Nette\Templating\FileTemplate($template);
$template->registerFilter(new Nette\Latte\Engine());
$template->registerHelperLoader('Nette\Templating\Helpers::loader');

$template->data = $info;
$template->save($output);
echo 'Finished~!' . PHP_EOL;