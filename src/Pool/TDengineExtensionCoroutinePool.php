<?php

declare(strict_types=1);

namespace Imi\TDengine\Pool;

use Imi\Bean\BeanFactory;
use Imi\Pool\TUriResourceConfig;
use Imi\Swoole\Pool\BaseAsyncPool;
use TDengine\Connection;

if (\Imi\Util\Imi::checkAppType('swoole'))
{
    /**
     * 协程 TDengine 扩展客户端连接池.
     */
    class TDengineExtensionCoroutinePool extends BaseAsyncPool
    {
        use TUriResourceConfig;

        public function __construct(string $name, \Imi\Pool\Interfaces\IPoolConfig $config = null, $resourceConfig = null)
        {
            parent::__construct($name, $config, $resourceConfig);
            $this->initUriResourceConfig();
        }

        /**
         * {@inheritDoc}
         */
        protected function createResource(): \Imi\Pool\Interfaces\IPoolResource
        {
            $config = $this->getNextResourceConfig();

            return BeanFactory::newInstance(TDengineExtensionResource::class, $this, new Connection($config['host'] ?? '127.0.0.1', $config['port'] ?? 6030, $config['user'] ?? 'root', $config['pass'] ?? 'taosdata', $config['db'] ?? null));
        }
    }
}
