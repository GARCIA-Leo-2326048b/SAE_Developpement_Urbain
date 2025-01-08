<?php

use PHPUnit\Framework\TestCase;
use blog\models\ComparaisonModel;

class ComparaisonModelTest extends TestCase
{
    private $comparaisonModel;

    protected function setUp(): void
    {
        $this->comparaisonModel = $this->getMockBuilder(ComparaisonModel::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testFetchGeoJson()
    {
        $geoJson = '{"type": "FeatureCollection", "features": []}';
        $this->comparaisonModel->method('fetchGeoJson')
            ->willReturn($geoJson);

        $result = $this->comparaisonModel->fetchGeoJson('testFile');
        $this->assertEquals($geoJson, $result);
    }

    public function testGetEPSGCode()
    {
        $geoJson = '{"type": "Point", "coordinates": [10, 20]}';
        $this->comparaisonModel->method('getEPSGCode')
            ->willReturn('EPSG:32632');

        $result = $this->comparaisonModel->getEPSGCode($geoJson);
        $this->assertEquals('EPSG:32632', $result);
    }

    public function testProjectGeoJson()
    {
        $geoJson = '{"type": "FeatureCollection", "features": []}';
        $this->comparaisonModel->method('projectGeoJson')
            ->willReturn($geoJson);

        $result = $this->comparaisonModel->projectGeoJson($geoJson);
        $this->assertEquals($geoJson, $result);
    }

    public function testGetAreasAndPerimeters()
    {
        $geometry = $this->createMock(\geoPHP::class);
        $geometry->method('geometryType')
            ->willReturn('Polygon');
        $geometry->method('area')
            ->willReturn(100.0);
        $geometry->method('length')
            ->willReturn(40.0);

        $areas = [];
        $perimeters = [];
        $result = $this->comparaisonModel->getAreasAndPerimeters($geometry, $areas, $perimeters);

        $this->assertEquals([100.0], $result['areas']);
        $this->assertEquals([40.0], $result['perimeters']);
    }

    public function testGetShapeIndexStats()
    {
        $polygon = [
            'areas' => [100.0],
            'perimeters' => [40.0]
        ];
        $result = $this->comparaisonModel->getShapeIndexStats($polygon);

        $this->assertNotEmpty($result);
    }

    public function testGetStat()
    {
        $values = [1, 2, 3, 4, 5];
        $result = $this->comparaisonModel->getStat($values);

        $this->assertEquals(3, $result['mean']);
        $this->assertEquals(1, $result['min']);
        $this->assertEquals(5, $result['max']);
        $this->assertNotEmpty($result['std']);
    }

    public function testCalculateStandardDeviation()
    {
        $values = [1, 2, 3, 4, 5];
        $mean = 3;
        $result = $this->comparaisonModel->calculateStandardDeviation($values, $mean);

        $this->assertNotEmpty($result);
    }

    public function testGetHausdorffDistance()
    {
        $geometry1 = $this->createMock(\geoPHP::class);
        $geometry2 = $this->createMock(\geoPHP::class);

        $this->comparaisonModel->method('getHausdorffDistance')
            ->willReturn(10.0);

        $result = $this->comparaisonModel->getHausdorffDistance($geometry1, $geometry2);
        $this->assertEquals(10.0, $result);
    }

    public function testGrapheDonnees()
    {
        $areaStatsSim = ['mean' => 1, 'min' => 1, 'max' => 1, 'std' => 1];
        $areaStatsVer = ['mean' => 1, 'min' => 1, 'max' => 1, 'std' => 1];
        $shapeIndexStatsSim = ['mean' => 1, 'min' => 1, 'max' => 1, 'std' => 1];
        $shapeIndexStatsVer = ['mean' => 1, 'min' => 1, 'max' => 1, 'std' => 1];

        $result = $this->comparaisonModel->grapheDonnees($areaStatsSim, $areaStatsVer, $shapeIndexStatsSim, $shapeIndexStatsVer);

        $this->assertNotEmpty($result);
    }
}
?>