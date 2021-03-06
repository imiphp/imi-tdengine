<?php

declare(strict_types=1);

namespace Imi\TDengine\Test\Tests;

use Imi\TDengine\Pool\TDengine;
use TDengine\Connection;

class ExtensionPoolTest extends ExtensionTest
{
    protected function getClient(): Connection
    {
        return TDengine::getConnection('extension_pool');
    }
}
