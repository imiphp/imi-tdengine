<?php

declare(strict_types=1);

namespace Imi\TDengine\Test\Tests;

use Imi\TDengine\Pool\TDengine;
use Imi\TDengine\Test\Model\DeviceLogModel;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    public function testCreateSuperTable(): void
    {
        DeviceLogModel::createSuperTable();
        $this->assertTrue(true);
    }

    public function testCreateTable(): void
    {
        $client = TDengine::getConnection('restful');

        $table = 'device_' . md5(uniqid('', true));
        $deviceId = md5(uniqid('', true));
        DeviceLogModel::createTable($table, [$deviceId]);
        $this->assertTableExists($table);
        $client->sql('DROP TABLE db_test.' . $table);

        $table = 'device_' . md5(uniqid('', true));
        $deviceId = md5(uniqid('', true));
        DeviceLogModel::createTable($table, ['deviceId' => $deviceId]);
        $this->assertTableExists($table);
        $client->sql('DROP TABLE db_test.' . $table);

        $table = 'device_' . md5(uniqid('', true));
        $deviceId = md5(uniqid('', true));
        DeviceLogModel::createTable($table, ['device_id' => $deviceId]);
        $this->assertTableExists($table);
        $client->sql('DROP TABLE db_test.' . $table);
    }

    public function testInsert(): void
    {
        $table = 'device_insert';
        $record = new DeviceLogModel([], $table);
        $record->time = $time = (int) (microtime(true) * 1000);
        $record->deviceId = '00000001';
        $record->voltage = 1.23;
        $record->electricCurrent = 4.56;
        $record->insert();

        $client = TDengine::getConnection('restful');
        $result = $client->sql('select * from db_test.device_insert order by time desc limit 1');
        $resultData = $result->getData();
        $this->assertTrue(\in_array($resultData, [
            [
                [
                    'time'             => gmdate('Y-m-d H:i:s', (int) ($time / 1000)) . '.' . substr((string) $time, -3, 3),
                    'voltage'          => 1.23,
                    'electric_current' => 4.56,
                ],
            ],
            [
                [
                    'time'             => gmdate('Y-m-d\TH:i:s', (int) ($time / 1000)) . '.' . substr((string) $time, -3, 3) . 'Z',
                    'voltage'          => 1.23,
                    'electric_current' => 4.56,
                ],
            ],
        ]), json_encode($resultData, \JSON_PRETTY_PRINT));
    }

    public function testBatchInsert(): void
    {
        $table1 = 'device_batch_insert_1';
        $records = [];
        $record = new DeviceLogModel([], $table1);
        $record->time = $time1 = (int) (microtime(true) * 1000);
        $record->deviceId = '00000001';
        $record->voltage = 1.23;
        $record->electricCurrent = 4.56;
        $records[] = $record;

        usleep(1000);
        $table2 = 'device_batch_insert_2';
        $time = microtime(true);
        $time2 = (int) ($time * 1000);
        $records[] = new DeviceLogModel([
            'time'            => gmdate('Y-m-d\TH:i:s.', (int) $time) . substr((string) $time2, -3, 3) . 'Z',
            'deviceId'        => '00000002',
            'voltage'         => 1.1,
            'electricCurrent' => 2.2,
        ], $table2);
        DeviceLogModel::batchInsert($records);

        $client = TDengine::getConnection('restful');
        $result = $client->sql('select * from db_test.device_batch_insert_1 order by time desc limit 1');
        $resultData = $result->getData();
        $this->assertTrue(\in_array($resultData, [
            [
                [
                    'time'             => gmdate('Y-m-d H:i:s', (int) ($time1 / 1000)) . '.' . substr((string) $time1, -3, 3),
                    'voltage'          => 1.23,
                    'electric_current' => 4.56,
                ],
            ],
            [
                [
                    'time'             => gmdate('Y-m-d\TH:i:s', (int) ($time1 / 1000)) . '.' . substr((string) $time1, -3, 3) . 'Z',
                    'voltage'          => 1.23,
                    'electric_current' => 4.56,
                ],
            ],
        ]), json_encode($resultData, \JSON_PRETTY_PRINT));
        $result = $client->sql('select * from db_test.device_batch_insert_2 order by time desc limit 1');
        $resultData = $result->getData();
        $this->assertTrue(\in_array($resultData, [
            [
                [
                    'time'             => gmdate('Y-m-d H:i:s', (int) ($time2 / 1000)) . '.' . substr((string) $time2, -3, 3),
                    'voltage'          => 1.1,
                    'electric_current' => 2.2,
                ],
            ],
            [
                [
                    'time'             => gmdate('Y-m-d\TH:i:s', (int) ($time2 / 1000)) . '.' . substr((string) $time2, -3, 3) . 'Z',
                    'voltage'          => 1.1,
                    'electric_current' => 2.2,
                ],
            ],
        ]), json_encode($resultData, \JSON_PRETTY_PRINT));

        $this->assertTrue(true);
    }

    private function assertTableExists(string $tableName): void
    {
        $result = TDengine::getConnection('restful')->sql('show db_test.tables');
        foreach ($result->getData() as $row)
        {
            if ($tableName === $row['table_name'])
            {
                $this->assertTrue(true);

                return;
            }
        }
        $this->assertTrue(false);
    }
}
