<?php

declare(strict_types=1);

namespace Imi\TDengine\Pool;

use Imi\Pool\BasePoolResource;
use TDengine\Connection;
use TDengine\Exception\TDengineException;

class TDengineExtensionResource extends BasePoolResource
{
    private Connection $connection;

    public function __construct(\Imi\Pool\Interfaces\IPool $pool, Connection $connection)
    {
        parent::__construct($pool);
        $this->connection = $connection;
    }

    /**
     * 打开
     */
    public function open(): bool
    {
        if (!$this->connection->isConnected())
        {
            $this->connection->connect();
        }

        return $this->connection->isConnected();
    }

    /**
     * 关闭.
     */
    public function close(): void
    {
        $this->connection->close();
    }

    /**
     * 获取对象实例.
     */
    public function getInstance(): Connection
    {
        return $this->connection;
    }

    /**
     * 重置资源，当资源被使用后重置一些默认的设置.
     */
    public function reset(): void
    {
    }

    /**
     * 检查资源是否可用.
     */
    public function checkState(): bool
    {
        try
        {
            $this->connection->getServerInfo();

            return true;
        }
        catch (TDengineException $te)
        {
            throw $te;
        }
    }
}
