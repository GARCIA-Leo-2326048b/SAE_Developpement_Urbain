<?php

use PHPUnit\Framework\TestCase;
use blog\models\InscriptionModel;

class InscriptionModelTest extends TestCase
{
    private $inscriptionModel;

    protected function setUp(): void
    {
        $this->inscriptionModel = $this->getMockBuilder(InscriptionModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->inscriptionModel->method('test_User')
            ->will($this->returnCallback([$this, 'mockTestUser']));
        $this->inscriptionModel->method('inscrire')
            ->will($this->returnCallback([$this, 'mockInscrire']));
    }

    public function mockTestUser($identifiant)
    {
        $users = [
            'existingUser' => ['Identifiant' => 'existingUser'],
        ];

        return $users[$identifiant] ?? false;
    }

    public function mockInscrire($identifiant, $password)
    {
        if ($identifiant === 'newUser' && $password === 'newPassword') {
            return true;
        }
        return false;
    }

    public function testUserExists()
    {
        $result = $this->inscriptionModel->test_User('existingUser');
        $this->assertNotFalse($result);
        $this->assertEquals('existingUser', $result['Identifiant']);
    }

    public function testUserDoesNotExist()
    {
        $result = $this->inscriptionModel->test_User('nonExistingUser');
        $this->assertFalse($result);
    }

    public function testInscrireSuccess()
    {
        $result = $this->inscriptionModel->inscrire('newUser', 'newPassword');
        $this->assertTrue($result);
    }

    public function testInscrireFailure()
    {
        $result = $this->inscriptionModel->inscrire('newUser', 'wrongPassword');
        $this->assertFalse($result);
    }
}
?>