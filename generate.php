<?php
session_start();
require_once('vendor/autoload.php');

// Setup caching

use Desarrolla2\Cache\Cache;
use Desarrolla2\Cache\Adapter\File as FileCache;

$adapter = new FileCache('cache');
$adapter->setOption('ttl', 3600);
$adapter = new Desarrolla2\Cache\Adapter\NotCache();
$cache = new Cache($adapter);

// Gather the data

$crawler = new Ratb\Scraper($cache);

dump($crawler->getLines());
