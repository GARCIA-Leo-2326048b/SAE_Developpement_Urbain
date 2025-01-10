<?php

namespace blog\models;

class ComparaisonData
{
    public array $results;
    public string $geoJsonSim;
    public string $geoJsonVer;
    public string $geoJsonSimName;
    public string $geoJsonVerName;

    public function __construct(array $results, string $geoJsonHouseSim, string $geoJsonHouseVer, string $geoJsonHouseSimName, string $geoJsonHouseVerName,string $geoJsonHouseSim, string $geoJsonHouseVer, string $geoJsonHouseSimName, string $geoJsonHouseVerName) {
        $this->results = $results;
        $this->geoJsonSim = $geoJsonSim;
        $this->geoJsonVer = $geoJsonVer;
        $this->geoJsonSimName = $geoJsonSimName;
        $this->geoJsonVerName = $geoJsonVerName;
    }
}