<?php

use PHPUnit\Framework\TestCase;
use blog\models\UploadModel;

class UploadModelTest extends TestCase
{
    private $uploadModel;
    private $dbMock;

    protected function setUp(): void
    {
        $this->dbMock = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->uploadModel = new UploadModel($this->dbMock);
    }

    public function testProjetExiste()
    {
        $project = 'testProject';
        $userId = 1;

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('bindParam')
            ->withConsecutive(
                [$this->equalTo(':projet'), $this->equalTo($project)],
                [$this->equalTo(':utilisateur'), $this->equalTo($userId)]
            );
        $stmtMock->expects($this->once())
            ->method('execute');
        $stmtMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1);

        $this->dbMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT COUNT(*) FROM projets WHERE nom = :projet and utilisateur = :utilisateur'))
            ->willReturn($stmtMock);

        $result = $this->uploadModel->projetExiste($project, $userId);
        $this->assertTrue($result);
    }

    public function testCreateProjectM()
    {
        $project = 'newProject';
        $userId = 1;

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('bindParam')
            ->withConsecutive(
                [$this->equalTo(':project'), $this->equalTo($project)],
                [$this->equalTo(':user'), $this->equalTo($userId)]
            );
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->dbMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO projets VALUES (:project, :user)'))
            ->willReturn($stmtMock);

        $result = $this->uploadModel->createProjectM($project, $userId);
        $this->assertTrue($result);
    }

    public function testGetUserProjects()
    {
        $userId = 1;
        $expectedProjects = [
            ['projet' => 'project1'],
            ['projet' => 'project2']
        ];

        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('bindParam')
            ->with($this->equalTo(':userId'), $this->equalTo($userId));
        $stmtMock->expects($this->once())
            ->method('execute');
        $stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedProjects);

        $this->dbMock->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT nom AS projet FROM projets WHERE utilisateur = :userId ORDER BY nom'))
            ->willReturn($stmtMock);

        $result = $this->uploadModel->getUserProjects($userId);
        $this->assertEquals($expectedProjects, $result);
    }
}
?>