<?php

declare(strict_types=1);

namespace Imi\TDengine\Test\Tests;

use Imi\TDengine\Pool\TDengine;
use PHPUnit\Framework\TestCase;
use TDengine\Connection;

class ExtensionTest extends TestCase
{
    protected function getClient(): Connection
    {
        return TDengine::getConnection('extension');
    }

    protected function getDbName(): string
    {
        return getenv('TDENGINE_REST_DB_NAME') ?: 'db_test';
    }

    public function testCreateDatabase(): void
    {
        $client = $this->getClient();
        $client->query('create database if not exists ' . $this->getDbName());
        $this->assertTrue(true);
    }

    public function testCreateTable(): void
    {
        $client = $this->getClient();
        $result = $client->query('create table if not exists ' . $this->getDbName() . '.tb (ts timestamp, temperature int, humidity float) ');
        $this->assertTrue(true);
    }

    public function testInsert(): array
    {
        $client = $this->getClient();
        $time = time() * 1000;
        $result = $client->query(sprintf('insert into db_test.tb values(%s,%s,%s)', $time, 36, 44.5));
        $this->assertEquals(1, $result->affectedRows());

        return ['time' => $time];
    }

    /**
     * @depends testInsert
     */
    public function testSelect(array $data): void
    {
        $client = $this->getClient();
        $result = $client->query('select * from db_test.tb order by ts desc limit 1');
        $this->assertEquals([
            [
                'ts'          => $data['time'],
                'temperature' => 36,
                'humidity'    => 44.5,
            ],
        ], $result->fetch());
    }
}
