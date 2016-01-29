<?php

namespace Ratb;

class Line
{
    const TYPE_TRAM     = 'tram';
    const TYPE_TROLLEY  = 'trolley';
    const TYPE_BUS      = 'bus';
    const TYPE_SUBURBAN = 'suburban';
    const TYPE_EXPRESS  = 'express';
    const TYPE_METRO    = 'metro';

    public $number;
    public $type;
    public $night;
    public $stops;
    public $departures;

    public function __construct($number, $type = null, $night = null)
    {
        $this->number = (string) $number;

        if (!empty($type)) {
            $this->type = $type;
        }

        if (is_null($night) && $this->type === self::TYPE_EXPRESS) {
            $night = true;
        }
        $this->night = (!is_null($night)) ? $night : false;

        $this->stops = new \stdClass;
        $this->stops->outbound = [];
        $this->stops->inbound = [];

        $days = array_fill_keys(['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'], []);
        $this->departures = new \stdClass;
        $this->departures->outbound = $days;
        $this->departures->inbound = $days;
    }
}
