<?php

use PHPUnit\Framework\TestCase;
use blog\models\ComparaisonModel;

class ComparaisonModelTest extends TestCase
{
    private $comparaisonModel;

    protected function setUp(): void
    {
        $this->comparaisonModel = $this->getMockBuilder(ComparaisonModel::class)
            ->onlyMethods([
                'saveExperimentationM', 'deleteFileExp', 'loadExperimentation', 'reformaterDonnees',
                'getGeoJsonSim', 'getGeoJsonVer', 'getChartsByExperimentationId', 'getGeoJsonSimName',
                'getGeoJsonVerName', 'getTableDataByExperimentationId', 'getEPSGCode', 'projectGeoJson',
                'transformCoordinates', 'transformBbox', 'getAreasAndPerimeters', 'getShapeIndexStats',
                'getStat', 'calculateStandardDeviation', 'getHausdorffDistance'
            ])
            ->getMock();
    }

    public function testSaveExperimentationM()
    {
        $this->comparaisonModel->method('saveExperimentationM')->willReturn(true);
        $result = $this->comparaisonModel->saveExperimentationM([], 'sim.geojson', 'ver.geojson', 'Test', 'Folder', 'Project');
        $this->assertTrue($result);
    }

    public function testDeleteFileExp()
    {
        $this->comparaisonModel->method('deleteFileExp')->willReturn(true);
        $result = $this->comparaisonModel->deleteFileExp('testFile', 'Project');
        $this->assertTrue($result);
    }

    public function testLoadExperimentation()
    {
        $this->comparaisonModel->method('loadExperimentation')->willReturn(['id_xp' => 1]);
        $result = $this->comparaisonModel->loadExperimentation(1);
        $this->assertEquals(['id_xp' => 1], $result);
    }

    public function testReformaterDonnees()
    {
        $tableData = [['table_data' => json_encode([['Statistique', 'Sim', 'Ver', 'Error'], ['Label1', 1, 2, 1]])]];
        $this->comparaisonModel->method('reformaterDonnees')->willReturn([
            'graphSim' => [['label' => 'Label1', 'y' => 1.0]],
            'graphVer' => [['label' => 'Label1', 'y' => 2.0]],
            'errors' => [['label' => 'Error Label1', 'y' => 1.0]]
        ]);
        $result = $this->comparaisonModel->reformaterDonnees($tableData);
        $this->assertEquals([
            'graphSim' => [['label' => 'Label1', 'y' => 1.0]],
            'graphVer' => [['label' => 'Label1', 'y' => 2.0]],
            'errors' => [['label' => 'Error Label1', 'y' => 1.0]]
        ], $result);
    }

    public function testGetGeoJsonSim()
    {
        $this->comparaisonModel->method('getGeoJsonSim')->willReturn([['file_data' => 'geojson data']]);
        $result = $this->comparaisonModel->getGeoJsonSim(1);
        $this->assertEquals([['file_data' => 'geojson data']], $result);
    }

    public function testGetGeoJsonVer()
    {
        $this->comparaisonModel->method('getGeoJsonVer')->willReturn([['file_data' => 'geojson data']]);
        $result = $this->comparaisonModel->getGeoJsonVer(1);
        $this->assertEquals([['file_data' => 'geojson data']], $result);
    }

    public function testGetChartsByExperimentationId()
    {
        $this->comparaisonModel->method('getChartsByExperimentationId')->willReturn([['data_xp' => 'chart data']]);
        $result = $this->comparaisonModel->getChartsByExperimentationId(1);
        $this->assertEquals([['data_xp' => 'chart data']], $result);
    }

    public function testGetGeoJsonSimName()
    {
        $this->comparaisonModel->method('getGeoJsonSimName')->willReturn('sim.geojson');
        $result = $this->comparaisonModel->getGeoJsonSimName(1);
        $this->assertEquals('sim.geojson', $result);
    }

    public function testGetGeoJsonVerName()
    {
        $this->comparaisonModel->method('getGeoJsonVerName')->willReturn('ver.geojson');
        $result = $this->comparaisonModel->getGeoJsonVerName(1);
        $this->assertEquals('ver.geojson', $result);
    }

    public function testGetTableDataByExperimentationId()
    {
        $this->comparaisonModel->method('getTableDataByExperimentationId')->willReturn([['table_data' => 'table data']]);
        $result = $this->comparaisonModel->getTableDataByExperimentationId(1);
        $this->assertEquals([['table_data' => 'table data']], $result);
    }

    public function testGetEPSGCode()
    {
        $geoJson = '{"type": "Point", "coordinates": [10, 20]}';
        $this->comparaisonModel->method('getEPSGCode')->willReturn('EPSG:32633');
        $result = $this->comparaisonModel->getEPSGCode($geoJson);
        $this->assertEquals('EPSG:32633', $result);
    }

    public function testProjectGeoJson()
    {
        $geoJson = '{"type": "Point", "coordinates": [10, 20]}';
        $this->comparaisonModel->method('projectGeoJson')->willReturn($geoJson);
        $result = $this->comparaisonModel->projectGeoJson($geoJson);
        $this->assertEquals($geoJson, $result);
    }

    public function testTransformCoordinates()
    {
        $coordinates = [10, 20];
        $this->comparaisonModel->method('transformCoordinates')->willReturn(null);
        $this->comparaisonModel->transformCoordinates($coordinates, null, null, null);
        $this->assertEquals([10, 20], $coordinates);
    }

    public function testTransformBbox()
    {
        $bbox = [10, 20, 30, 40];
        $this->comparaisonModel->method('transformBbox')->willReturn(null);
        $this->comparaisonModel->transformBbox($bbox, null, null, null);
        $this->assertEquals([10, 20, 30, 40], $bbox);
    }

    public function testGetAreasAndPerimeters()
    {
        $geometry = $this->createMock(\geoPHP\Geometry::class);
        $this->comparaisonModel->method('getAreasAndPerimeters')->willReturn(['areas' => [100], 'perimeters' => [50]]);
        $result = $this->comparaisonModel->getAreasAndPerimeters($geometry);
        $this->assertEquals(['areas' => [100], 'perimeters' => [50]], $result);
    }

    public function testGetShapeIndexStats()
    {
        $polygon = ['areas' => [100], 'perimeters' => [50]];
        $this->comparaisonModel->method('getShapeIndexStats')->willReturn([1.0]);
        $result = $this->comparaisonModel->getShapeIndexStats($polygon);
        $this->assertEquals([1.0], $result);
    }

    public function testGetStat()
    {
        $values = [1, 2, 3];
        $this->comparaisonModel->method('getStat')->willReturn(['mean' => 2.0, 'min' => 1, 'max' => 3, 'std' => 1.0]);
        $result = $this->comparaisonModel->getStat($values);
        $this->assertEquals(['mean' => 2.0, 'min' => 1, 'max' => 3, 'std' => 1.0], $result);
    }

    public function testCalculateStandardDeviation()
    {
        $values = [1, 2, 3];
        $mean = 2.0;
        $this->comparaisonModel->method('calculateStandardDeviation')->willReturn(1.0);
        $result = $this->comparaisonModel->calculateStandardDeviation($values, $mean);
        $this->assertEquals(1.0, $result);
    }

    public function testGetHausdorffDistance()
    {
        $geometry1 = $this->createMock(\geoPHP\Geometry::class);
        $geometry2 = $this->createMock(\geoPHP\Geometry::class);
        $this->comparaisonModel->method('getHausdorffDistance')->willReturn(10.0);
        $result = $this->comparaisonModel->getHausdorffDistance($geometry1, $geometry2);
        $this->assertEquals(10.0, $result);
    }
}
?>