<?php

declare(strict_types=1);

namespace Imi\TDengine\ClientHandler;

use Imi\Pool\PoolManager;
use Yurun\TDEngine\Orm\ClientHandler\Extension\Handler;
use Yurun\TDEngine\Orm\ClientHandler\Extension\QueryResult;
use Yurun\TDEngine\Orm\ClientHandler\IClientHandler;
use Yurun\TDEngine\Orm\Contract\IQueryResult;

class ExtensionPoolClientHandler implements IClientHandler
{
    private ?Handler $extensionHandler = null;

    /**
     * 查询.
     */
    public function query(string $sql, ?string $clientName = null): IQueryResult
    {
        if (null !== $clientName && PoolManager::exists($clientName))
        {
            return new QueryResult(PoolManager::getRequestContextResource($clientName)->getInstance()->query($sql));
        }
        else
        {
            $connection = ($this->extensionHandler ??= new Handler());

            return $connection->query($sql, $clientName);
        }
    }
}
