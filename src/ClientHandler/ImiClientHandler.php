<?php

declare(strict_types=1);

namespace Imi\TDengine\ClientHandler;

use Imi\App;
use Imi\Pool\PoolManager;
use Imi\TDengine\Pool\TDengine;
use Swoole\Coroutine;
use Yurun\TDEngine\Orm\ClientHandler\IClientHandler;
use Yurun\TDEngine\Orm\Contract\IQueryResult;

class ImiClientHandler implements IClientHandler
{
    /**
     * @var \Yurun\TDEngine\Orm\ClientHandler\IClientHandler[]
     */
    private array $clientHandlers = [];

    private ?bool $haveSwoole = null;

    private ?bool $haveExtensionTDengine = null;

    /**
     * 查询.
     */
    public function query(string $sql, ?string $clientName = null): IQueryResult
    {
        if (null === $clientName)
        {
            $clientName = TDengine::getDefaultPoolName();
        }
        if (($this->haveSwoole ??= \extension_loaded('swoole')) && Coroutine::getuid() > -1 && PoolManager::exists($clientName))
        {
            $clientHandler = ($this->clientHandlers[0] ??= new ExtensionPoolClientHandler());
        }
        else
        {
            /** @var TDengine $tdengine */
            $tdengine = App::getBean('TDengine');
            if (($tdengine->getConnections()[$clientName ?? $tdengine->getDefaultPoolName()]['extension'] ?? false) && ($this->haveExtensionTDengine ??= class_exists(\TDengine\Connection::class, false)))
            {
                $clientHandler = ($this->clientHandlers[1] ??= new \Yurun\TDEngine\Orm\ClientHandler\Extension\Handler());
            }
            else
            {
                $clientHandler = ($this->clientHandlers[2] ??= new \Yurun\TDEngine\Orm\ClientHandler\Restful\Handler());
            }
        }

        return $clientHandler->query($sql, $clientName);
    }
}
