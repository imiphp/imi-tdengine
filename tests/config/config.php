<?php

declare(strict_types=1);

use Yurun\TDEngine\Constants\TimeStampFormat;

return [
    'ignoreNamespace'   => [
        'Imi\TDengine\Test\Tests\*',
        'Imi\TDengine\Test\Model\*',
    ],
    // 组件命名空间
    'components'    => [
        'TDengine' => 'Imi\TDengine',
        'Swoole'   => 'Imi\Swoole',
    ],
    // 日志配置
    'logger' => [
        'channels' => [
            'imi' => [
                'handlers' => [
                    [
                        'class'     => \Imi\Log\Handler\ConsoleHandler::class,
                        'formatter' => [
                            'class'     => \Imi\Log\Formatter\ConsoleLineFormatter::class,
                            'construct' => [
                                'format'                     => null,
                                'dateFormat'                 => 'Y-m-d H:i:s',
                                'allowInlineLineBreaks'      => true,
                                'ignoreEmptyContextAndExtra' => true,
                            ],
                        ],
                    ],
                    [
                        'class'     => \Monolog\Handler\RotatingFileHandler::class,
                        'construct' => [
                            'filename' => dirname(__DIR__) . '/logs/log.log',
                        ],
                        'formatter' => [
                            'class'     => \Monolog\Formatter\LineFormatter::class,
                            'construct' => [
                                'dateFormat'                 => 'Y-m-d H:i:s',
                                'allowInlineLineBreaks'      => true,
                                'ignoreEmptyContextAndExtra' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    // 连接池配置
    'pools'    => [
        'extension_pool'    => [
            'pool'    => [
                'class'        => \Imi\TDengine\Pool\TDengineExtensionCoroutinePool::class,
                'config'       => [
                    'maxResources'    => 10,
                    'minResources'    => 0,
                ],
            ],
            'resource'    => [
                'host'            => getenv('TDENGINE_EXT_HOST') ?: '127.0.0.1',
                'port'            => getenv('TDENGINE_EXT_PORT') ?: 6030,
                'user'            => getenv('TDENGINE_EXT_USER') ?: 'root',
                'password'        => getenv('TDENGINE_EXT_PASSWORD') ?: 'taosdata',
                'db'              => getenv('TDENGINE_EXT_DB_NAME') ?: 'db_test',
            ],
        ],
    ],

    'beans' => [
        // db 配置
        'TDengine' => [
            'defaultPoolName' => 'restful',
            'connections'     => [
                'restful' => [
                    'host'            => getenv('TDENGINE_REST_HOST') ?: '127.0.0.1',
                    'hostName'        => getenv('TDENGINE_REST_HOST_NAME') ?: '',
                    'port'            => getenv('TDENGINE_REST_PORT') ?: 6041,
                    'user'            => getenv('TDENGINE_REST_USER') ?: 'root',
                    'password'        => getenv('TDENGINE_REST_PASSWORD') ?: 'taosdata',
                    'db'              => getenv('TDENGINE_REST_DB_NAME') ?: 'db_test',
                    'ssl'             => getenv('TDENGINE_REST_SSL') ?: false,
                    'timestampFormat' => getenv('TDENGINE_REST_TIMESTAMP_FORMAT') ?: TimeStampFormat::LOCAL_STRING,
                ],
                'extension' => [
                    'extension'       => true,
                    'host'            => getenv('TDENGINE_EXT_HOST') ?: '127.0.0.1',
                    'port'            => getenv('TDENGINE_EXT_PORT') ?: 6030,
                    'user'            => getenv('TDENGINE_EXT_USER') ?: 'root',
                    'password'        => getenv('TDENGINE_EXT_PASSWORD') ?: 'taosdata',
                    'db'              => getenv('TDENGINE_EXT_DB_NAME') ?: 'db_test',
                ],
            ],
        ],
    ],
];
