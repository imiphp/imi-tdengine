<?php

declare(strict_types=1);

namespace Imi\TDengine\Listener;

use Imi\App;
use Imi\Bean\Annotation\Listener;
use Imi\Event\EventParam;
use Imi\Event\IEventListener;
use Imi\TDengine\ClientHandler\ImiClientHandler;
use Yurun\TDEngine\Orm\TDEngineOrm;

/**
 * @Listener("IMI.APP_RUN")
 */
class AppRunListener implements IEventListener
{
    /**
     * 事件处理方法.
     */
    public function handle(EventParam $e): void
    {
        TDEngineOrm::setClientHandler(new ImiClientHandler());
    }
}
