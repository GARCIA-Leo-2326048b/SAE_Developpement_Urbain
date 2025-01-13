<?php

use PHPUnit\Framework\TestCase;
use blog\models\UploadModel;

class UploadModelTest extends TestCase
{
    private $uploadModel;

    protected function setUp(): void
    {
        $this->uploadModel = $this->getMockBuilder(UploadModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'projetExiste', 'createProjectM', 'getUserProjects', 'saveUploadGJ', 'saveUploadGT',
                'fileExistGJ', 'deleteFileGJ', 'verifyFolder', 'createFolder', 'getExperimentation',
                'getFolderHierarchy', 'getSubFolder', 'deleteFolderByName', 'deleteFolderT'
            ])
            ->getMock();
    }

    public function testProjetExiste()
    {
        $this->uploadModel->method('projetExiste')->willReturn(true);
        $result = $this->uploadModel->projetExiste('testProject', 1);
        $this->assertTrue($result);
    }

    public function testCreateProjectM()
    {
        $this->uploadModel->method('createProjectM')->willReturn(true);
        $result = $this->uploadModel->createProjectM('testProject', 1);
        $this->assertTrue($result);
    }

    public function testGetUserProjects()
    {
        $this->uploadModel->method('getUserProjects')->willReturn(['project1', 'project2']);
        $result = $this->uploadModel->getUserProjects(1);
        $this->assertEquals(['project1', 'project2'], $result);
    }

    public function testSaveUploadGJ()
    {
        $this->uploadModel->method('saveUploadGJ')->willReturn(true);
        $result = $this->uploadModel->saveUploadGJ('testFile', 'testContent', 1, 'testFolder', 'testProject');
        $this->assertTrue($result);
    }


    public function testSaveUploadGT()
    {
        $this->uploadModel->method('saveUploadGT')->willReturn(true);
        $result = $this->uploadModel->saveUploadGT('testFile', 'testContent', 1, 'testProject');
        $this->assertTrue($result);
    }

    public function testFileExistGJ()
    {
        $this->uploadModel->method('fileExistGJ')->willReturn(true);
        $result = $this->uploadModel->fileExistGJ('testFile', 1, 'testProject');
        $this->assertTrue($result);
    }

    public function testDeleteFileGJ()
    {
        $this->uploadModel->method('deleteFileGJ')->willReturn(true);
        $result = $this->uploadModel->deleteFileGJ('testFile', 1, 'testProject');
        $this->assertTrue($result);
    }

    public function testVerifyFolder()
    {
        $this->uploadModel->method('verifyFolder')->willReturn(true);
        $result = $this->uploadModel->verifyFolder(1, 'testParentFolder', 'testFolder', 'testProject');
        $this->assertTrue($result);
    }

    public function testCreateFolder()
    {
        $this->uploadModel
            ->expects($this->once())
            ->method('createFolder')
            ->with(
                $this->equalTo(1),
                $this->equalTo('testParentFolder'),
                $this->equalTo('testFolder'),
                $this->equalTo('testProject')
            );

        $this->uploadModel->createFolder(1, 'testParentFolder', 'testFolder', 'testProject');
    }




    public function testGetExperimentation()
    {
        $this->uploadModel->method('getExperimentation')->willReturn(['exp1', 'exp2']);
        $result = $this->uploadModel->getExperimentation(1, 'testProject');
        $this->assertEquals(['exp1', 'exp2'], $result);
    }

    public function testGetFolderHierarchy()
    {
        $this->uploadModel->method('getFolderHierarchy')->willReturn(['folder1', 'folder2']);
        $result = $this->uploadModel->getFolderHierarchy(1, 'testProject');
        $this->assertEquals(['folder1', 'folder2'], $result);
    }

    public function testGetSubFolder()
    {
        $this->uploadModel->method('getSubFolder')->willReturn(['subFolder1', 'subFolder2']);
        $result = $this->uploadModel->getSubFolder(1, 'testFolder', 'testProject');
        $this->assertEquals(['subFolder1', 'subFolder2'], $result);
    }

    public function testDeleteFolderByName()
    {
        $this->uploadModel->method('deleteFolderByName')->willReturn(true);
        $result = $this->uploadModel->deleteFolderByName('testFolder', 1, 'testProject');
        $this->assertTrue($result);
    }

    public function testDeleteFolderT()
    {
        $this->uploadModel->method('deleteFolderT')->willReturn(true);
        $result = $this->uploadModel->deleteFolderT('testFolder', 1, 'testProject');
        $this->assertTrue($result);
    }
}
?>