<?php

declare(strict_types=1);

namespace Imi\TDengine\Pool;

use Imi\App;
use Imi\Bean\Annotation\Bean;
use Imi\Pool\PoolManager;
use TDengine\Connection;
use Yurun\TDEngine\ClientConfig;
use Yurun\TDEngine\TDEngineManager;

/**
 * @Bean("TDengine")
 */
class TDengine
{
    /**
     * 默认连接池名.
     */
    protected ?string $defaultPoolName = null;

    /**
     * 连接配置.
     */
    protected array $connections = [];

    public function __init(): void
    {
        if (null !== $this->defaultPoolName)
        {
            TDEngineManager::setDefaultClientName($this->defaultPoolName);
        }
        if ($this->connections)
        {
            foreach ($this->connections as $name => $config)
            {
                TDEngineManager::setClientConfig($name, new ClientConfig($config));
            }
        }
    }

    /**
     * Get 默认连接池名.
     */
    public static function getDefaultPoolName(): ?string
    {
        /** @var self $tdengine */
        $tdengine = App::getBean('TDengine');

        return $tdengine->defaultPoolName;
    }

    /**
     * Get 连接配置.
     */
    public static function getConnections(): array
    {
        /** @var self $tdengine */
        $tdengine = App::getBean('TDengine');

        return $tdengine->connections;
    }

    /**
     * 获取连接.
     *
     * @return \Yurun\TDEngine\Client|Connection|null
     */
    public static function getConnection(?string $poolName = null)
    {
        /** @var self $tdengine */
        $tdengine = App::getBean('TDengine');
        if (null === $poolName)
        {
            $poolName = $tdengine->defaultPoolName;
        }
        if (PoolManager::exists($poolName))
        {
            return PoolManager::getResource($poolName)->getInstance();
        }
        elseif ($tdengine->connections[$poolName]['extension'] ?? false)
        {
            $config = TDEngineManager::getClientConfig($poolName);
            if (!$config)
            {
                throw new \RuntimeException(sprintf('Client %s config does not found', $poolName));
            }
            $db = $config->getDb();
            $connection = new Connection($config->getHost(), $config->getPort(), $config->getUser(), $config->getPassword(), '' === $db ? null : $db);
            $connection->connect();

            return $connection;
        }
        else
        {
            return TDEngineManager::getClient($poolName);
        }
    }
}
