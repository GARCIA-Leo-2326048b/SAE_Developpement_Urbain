<?php

use PHPUnit\Framework\TestCase;
use blog\models\SingletonModel;

class SingletonModelTest extends TestCase
{
    public function testGetInstanceReturnsSingletonInstance()
    {
        $instance1 = SingletonModel::getInstance();
        $instance2 = SingletonModel::getInstance();

        $this->assertInstanceOf(SingletonModel::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }

    public function testGetConnectionReturnsPDOInstance()
    {
        $instance = SingletonModel::getInstance();
        $connection = $instance->getConnection();

        $this->assertInstanceOf(PDO::class, $connection);
    }
}
?>