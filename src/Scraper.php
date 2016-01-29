<?php

namespace Ratb;

use Goutte\Client as Goutte;

class Scraper
{
    protected $lines = [];

    protected static $sources = [
        'base'         => 'http://ratb.ro/',
        'normal'       => 'v_trasee.php',
        'night'        => 'v_noapte.php',
        'nightDetails' => 'v_statii_noapte.php',
    ];

    protected $types = [
        Line::TYPE_TRAM     => 'tlin1',
        Line::TYPE_TROLLEY  => 'tlin2',
        Line::TYPE_BUS      => 'tlin3',
        Line::TYPE_SUBURBAN => 'tlin4',
        Line::TYPE_EXPRESS  => 'tlin5',
    ];

    private $cache;

    public function __construct(\Desarrolla2\Cache\Cache $cache)
    {
        $this->cache = $cache;
    }

    public function getLines()
    {
        if (empty($this->lines)) {
            $this->lines = [];

            // $this->scrapeNormalLines();
            $this->scrapeNightLines();
        }

        return $this->lines;
    }

    protected function scrapeNormalLines()
    {
        $cacheKey = 'normal_lines';

        if (!$this->cache->has($cacheKey)) {

            $client = new Goutte();
            $crawler = $client->request('GET', self::source('normal'));

            $data = [];

            foreach ($this->types as $type => $name) {
                $lines = $crawler->filter('select[name="' . $name . '"] option')->each(function ($node) use ($type) {

                    // Skip the "Select" option
                    if ($node->attr('value') == 0) {
                        return null;
                    }

                    $number = $node->attr('value');

                    return new Line($number, $type);
                });

                $data = array_merge($data, array_filter($lines));
            }

            $this->cache->set($cacheKey, $data);
        }

        $this->lines = array_merge($this->lines, $this->cache->get($cacheKey));

        return $this;
    }

    protected function scrapeNightLines()
    {
        $cacheKey = 'night_lines';

        if (!$this->cache->has($cacheKey)) {

            $client = new Goutte();
            $crawler = $client->request('GET', self::source('night'));

            $lines = $crawler->filter('a')->each(function ($node) {

                // Skip unrelated nodes
                if (strrpos($node->attr('href'), self::$sources['nightDetails']) === false) {
                    return null;
                }

                $number = $node->text();

                return $this->scrapeNightLineDetails(new Line($number, Line::TYPE_BUS, true));
            });

            $this->cache->set($cacheKey, array_filter($lines));
        }

        $this->lines = array_merge($this->lines, $this->cache->get($cacheKey));

        return $this;
    }

    protected function scrapeNightLineDetails(Line $line)
    {
        $client = new Goutte();
        $source = self::source('nightDetails') . '?' . http_build_query(['tlin1' => $line->number]);
        $crawler = $client->request('GET', $source);

        dump($crawler);

        return $line;
    }

    protected static function source($name)
    {
        if (!array_key_exists($name, self::$sources)) {
            throw new \Exception('Invalid source: ' . $name);
        }

        return self::$sources['base'] . self::$sources[$name];
    }
}
