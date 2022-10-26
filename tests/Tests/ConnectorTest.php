<?php

declare(strict_types=1);

namespace Imi\TDengine\Test\Tests;

use Imi\TDengine\Pool\TDengine;
use PHPUnit\Framework\TestCase;
use Yurun\TDEngine\Client;

class ConnectorTest extends TestCase
{
    protected function getClient(): Client
    {
        return TDengine::getConnection('restful');
    }

    protected function getDbName(): string
    {
        return getenv('TDENGINE_REST_DB_NAME') ?: 'db_test';
    }

    public function testCreateDatabase(): void
    {
        $client = $this->getClient();
        $result = $client->sql('create database if not exists ' . $this->getDbName());
        $this->assertTrue(true);
    }

    public function testCreateTable(): void
    {
        $client = $this->getClient();
        $result = $client->sql('create table if not exists ' . $this->getDbName() . '.tb (ts timestamp, temperature int, humidity float) ');
        $this->assertTrue(true);
    }

    public function testInsert(): array
    {
        $client = $this->getClient();
        $time = time() * 1000;
        $result = $client->sql(sprintf('insert into db_test.tb values(%s,%s,%s)', $time, 36, 44.5));
        $this->assertEquals(1, $result->getRows());

        return ['time' => $time];
    }

    /**
     * @depends testInsert
     */
    public function testSelect(array $data): void
    {
        $client = $this->getClient();
        $result = $client->sql('select * from db_test.tb order by ts desc limit 1');
        $resultData = $result->getData();
        $this->assertTrue(\in_array($resultData, [
            [
                [
                    'ts'          => gmdate('Y-m-d H:i:s', (int) ($data['time'] / 1000)) . '.000',
                    'temperature' => 36,
                    'humidity'    => 44.5,
                ],
            ],
            [
                [
                    'ts'          => gmdate('Y-m-d\TH:i:s.000\Z', (int) ($data['time'] / 1000)),
                    'temperature' => 36,
                    'humidity'    => 44.5,
                ],
            ],
        ]), json_encode($resultData, \JSON_PRETTY_PRINT));
    }
}
