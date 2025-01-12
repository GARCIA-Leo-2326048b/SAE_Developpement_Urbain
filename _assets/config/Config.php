<?php

namespace _assets\config;

class Config
{
    public function getDatabaseConfig(): array
    {
        return [
            'dsn' => 'mysql:host=mysql-developpement-urbain.alwaysdata.net;dbname=developpement-urbain_344;charset=utf8',
            'username' => '379003',
            'password' => 'saeflouvat',
        ];
    }
}
