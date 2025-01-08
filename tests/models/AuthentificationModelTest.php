<?php

use PHPUnit\Framework\TestCase;
use blog\models\AuthentificationModel;

class AuthentificationModelTest extends TestCase
{
    private $authModel;

    protected function setUp(): void
    {
        $this->authModel = $this->getMockBuilder(AuthentificationModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->authModel->method('test_Pass')
            ->will($this->returnCallback([$this, 'mockTestPass']));
    }

    public function mockTestPass($identifiant, $password)
    {
        $users = [
            'validUser' => password_hash('validPassword', PASSWORD_DEFAULT),
        ];

        if (isset($users[$identifiant]) && password_verify($password, $users[$identifiant])) {
            return ['Identifiant' => $identifiant, 'Password' => $users[$identifiant]];
        }

        return false;
    }

    public function testValidCredentials()
    {
        $result = $this->authModel->test_Pass('validUser', 'validPassword');
        $this->assertNotFalse($result);
        $this->assertEquals('validUser', $result['Identifiant']);
    }

    public function testInvalidCredentials()
    {
        $result = $this->authModel->test_Pass('invalidUser', 'invalidPassword');
        $this->assertFalse($result);
    }

    public function testValidIdentifiantInvalidPassword()
    {
        $result = $this->authModel->test_Pass('validUser', 'invalidPassword');
        $this->assertFalse($result);
    }

    public function testEmptyCredentials()
    {
        $result = $this->authModel->test_Pass('', '');
        $this->assertFalse($result);
    }
}
?>