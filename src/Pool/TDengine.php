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
    public function getDefaultPoolName(): ?string
    {
        return $this->defaultPoolName;
    }

    /**
     * Get 连接配置.
     */
    public function getConnections(): array
    {
        return $this->connections;
    }

    /**
     * 获取连接.
     *
     * @return \Yurun\TDEngine\Client|Connection|null
     */
    public static function getConnection(?string $poolName = null)
    {
        if (null !== $poolName && PoolManager::exists($poolName))
        {
            return PoolManager::getResource($poolName)->getInstance();
        }
        elseif (App::getBean('TDengine')->connections[$poolName]['extension'] ?? false)
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
