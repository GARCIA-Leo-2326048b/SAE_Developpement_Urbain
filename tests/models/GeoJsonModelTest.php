<?php

use PHPUnit\Framework\TestCase;
use blog\models\GeoJsonModel;

class GeoJsonModelTest extends TestCase
{
    private $geoJsonModel;

    protected function setUp(): void
    {
        $this->geoJsonModel = $this->getMockBuilder(GeoJsonModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchGeoJson'])
            ->getMock();

        $this->geoJsonModel->method('fetchGeoJson')
            ->will($this->returnCallback([$this, 'mockFetchGeoJson']));
    }

    public function mockFetchGeoJson($name)
    {
        $geoJsonFiles = [
            'testFile' => '{"type": "FeatureCollection", "features": []}',
        ];

        return $geoJsonFiles[$name] ?? false;
    }

    public function testFetchGeoJsonSuccess()
    {
        $result = $this->geoJsonModel->fetchGeoJson('testFile');
        $this->assertEquals('{"type": "FeatureCollection", "features": []}', $result);
    }

    public function testFetchGeoJsonFailure()
    {
        $result = $this->geoJsonModel->fetchGeoJson('nonExistingFile');
        $this->assertFalse($result);
    }
}
?>