# imi-tdengine

<p align="center">
    <a href="https://www.imiphp.com" target="_blank">
        <img src="https://cdn.jsdelivr.net/gh/imiphp/imi@2.0/res/logo.png" alt="imi" />
    </a>
</p>

[![Latest Version](https://img.shields.io/packagist/v/imiphp/imi-tdengine.svg)](https://packagist.org/packages/imiphp/imi-tdengine)
![GitHub Workflow Status (branch)](https://img.shields.io/github/workflow/status/imiphp/imi-tdengine/ci/dev)
[![Php Version](https://img.shields.io/badge/php-%3E=7.4-brightgreen.svg)](https://secure.php.net/)
[![Swoole Version](https://img.shields.io/badge/swoole-%3E=4.7.0-brightgreen.svg)](https://github.com/swoole/swoole-src)
[![imi Doc](https://img.shields.io/badge/docs-passing-green.svg)](https://doc.imiphp.com/v2.0/)
[![imi License](https://img.shields.io/badge/license-MulanPSL%201.0-brightgreen.svg)](https://github.com/imiphp/imi-tdengine/blob/2.0/LICENSE)

## 介绍

封装 tdengine 连接池，支持在 imi 框架中使用。

本组件支持 RESTful 和扩展两种方式实现。

扩展安装请移步：<https://github.com/Yurunsoft/php-tdengine>

imi 框架交流群：17916227 [![点击加群](https://pub.idqqimg.com/wpa/images/group.png "点击加群")](https://jq.qq.com/?_wv=1027&k=5wXf4Zq)

## 使用

引入本组件：`composer require imiphp/imi-tdengine`

### 配置

项目配置文件：(config/config.php)

```php
// Swoole 连接池配置，非 Swoole 不要配
// 连接池必须使用扩展
'pools'    => [
    '连接池名'    => [
        'pool'    => [
            'class'        => \Imi\TDengine\Pool\TDengineExtensionCoroutinePool::class,
            'config'       => [
                'maxResources'    => 10,
                'minResources'    => 0,
            ],
        ],
        'resource'    => [
            'host'            => '127.0.0.1',
            'port'            => 6030,
            'user'            => 'root',
            'password'        => 'taosdata',
            'db'              => 'db_test',
        ],
    ],
],

'beans' => [
    // db 配置
    'TDengine' => [
        'defaultPoolName' => '默认连接名',
        'connections'     => [
            // 扩展配置，不需要可不配
            '连接名1' => [
                'extension'       => true, // 必须设为 true
                'host'            => '127.0.0.1',
                'port'            => 6030,
                'user'            => 'root',
                'password'        => 'taosdata',
                'db'              => 'db_test',
            ],
            // restful 配置，不需要可不配
            '连接名2' => [
                'host'            => '127.0.0.1',
                'hostName'        => '', // 域名，没有可不填
                'port'            => 6041,
                'user'            => 'root',
                'password'        => 'taosdata',
                'db'              => 'db_test'
                'ssl'             => false,
                'timestampFormat' => \Yurun\TDEngine\Constants\TimeStampFormat::LOCAL_STRING,
                'keepAlive'       => true,
            ],
        ],
    ],
],
```

### 模型

使用参考：<https://github.com/Yurunsoft/tdengine-orm>

### 连接操作

直接操作连接对象，执行 SQL 语句

#### 获取连接对象

```php
// 获取默认连接名的连接
$connection = \Imi\TDengine\Pool\TDengine::getConnection();
// 获取指定连接名的连接
$connection = \Imi\TDengine\Pool\TDengine::getConnection('连接名123');
// 如果是扩展，$connection 类型为 \TDengine\Connection
// 如果是 restful，$connection 类型为 \Yurun\TDEngine\Client
```

#### 扩展用法

**查询：**

```php
// 查询
$resource = $connection->query($sql); // 支持查询和插入
// 获取结果集时间戳字段的精度，0 代表毫秒，1 代表微秒，2 代表纳秒
$resource->getResultPrecision();
// 获取所有数据
$resource->fetch();
// 获取一行数据
$resource->fetchRow();
// 获取字段数组
$resource->fetchFields();
// 获取列数
$resource->getFieldCount();
// 获取影响行数
$resource->affectedRows();
// 获取 SQL 语句
$resource->getSql();
// 获取连接对象
$resource->getConnection();
// 关闭资源（一般不需要手动关闭，变量销毁时会自动释放）
$resource->close();
```

**参数绑定：**

```php
// 查询
$stmt = $connection->prepare($sql); // 支持查询和插入，参数用?占位
// 绑定参数方法1
$stmt->bindParams(
    // [字段类型, 值]
    [TDengine\TSDB_DATA_TYPE_TIMESTAMP, $time1],
    [TDengine\TSDB_DATA_TYPE_INT, 36],
    [TDengine\TSDB_DATA_TYPE_FLOAT, 44.5],
);
// 绑定参数方法2
$stmt->bindParams([
    // ['type' => 字段类型, 'value' => 值]
    ['type' => TDengine\TSDB_DATA_TYPE_TIMESTAMP, 'value' => $time2],
    ['type' => TDengine\TSDB_DATA_TYPE_INT, 'value' => 36],
    ['type' => TDengine\TSDB_DATA_TYPE_FLOAT, 'value' => 44.5],
]);
// 执行 SQL，返回 Resource，使用方法同 query() 返回值
$resource = $stmt->execute();
// 获取 SQL 语句
$stmt->getSql();
// 获取连接对象
$stmt->getConnection();
// 关闭（一般不需要手动关闭，变量销毁时会自动释放）
$stmt->close();
```

**字段类型：**

| 参数名称 | 说明 |
| ------------ | ------------ 
| `TDengine\TSDB_DATA_TYPE_NULL` | null |
| `TDengine\TSDB_DATA_TYPE_BOOL` | bool |
| `TDengine\TSDB_DATA_TYPE_TINYINT` | tinyint |
| `TDengine\TSDB_DATA_TYPE_SMALLINT` | smallint |
| `TDengine\TSDB_DATA_TYPE_INT` | int |
| `TDengine\TSDB_DATA_TYPE_BIGINT` | bigint |
| `TDengine\TSDB_DATA_TYPE_FLOAT` | float |
| `TDengine\TSDB_DATA_TYPE_DOUBLE` | double |
| `TDengine\TSDB_DATA_TYPE_BINARY` | binary |
| `TDengine\TSDB_DATA_TYPE_TIMESTAMP` | timestamp |
| `TDengine\TSDB_DATA_TYPE_NCHAR` | nchar |
| `TDengine\TSDB_DATA_TYPE_UTINYINT` | utinyint |
| `TDengine\TSDB_DATA_TYPE_USMALLINT` | usmallint |
| `TDengine\TSDB_DATA_TYPE_UINT` | uint |
| `TDengine\TSDB_DATA_TYPE_UBIGINT` | ubigint |

#### restful 用法

```php
// 通过 sql 方法执行 sql 语句
var_dump($connection->sql('create database if not exists db_test'));
var_dump($connection->sql('show databases'));
var_dump($connection->sql('create table if not exists db_test.tb (ts timestamp, temperature int, humidity float)'));
var_dump($connection->sql(sprintf('insert into db_test.tb values(%s,%s,%s)', time() * 1000, mt_rand(), mt_rand() / mt_rand())));

$result = $connection->sql('select * from db_test.tb');

$result->getResponse(); // 获取接口原始返回数据

// 获取列数据
foreach ($result->getColumns() as $column)
{
    $column->getName(); // 列名
    $column->getType(); // 列类型值
    $column->getTypeName(); // 列类型名称
    $column->getLength(); // 类型长度
}

// 获取数据
foreach ($result->getData() as $row)
{
    echo $row['列名']; // 经过处理，可以直接使用列名获取指定列数据
}

$result->getStatus(); // 告知操作结果是成功还是失败；同接口返回格式

$result->getHead(); // 表的定义，如果不返回结果集，则仅有一列“affected_rows”。（从 2.0.17 版本开始，建议不要依赖 head 返回值来判断数据列类型，而推荐使用 column_meta。在未来版本中，有可能会从返回值中去掉 head 这一项。）；同接口返回格式

$result->getRow(); // 表明总共多少行数据；同接口返回格式
```

## 版权信息

imi 遵循 木兰宽松许可证(Mulan PSL v2) 开源协议发布，并提供免费使用。

## 捐赠

![捐赠](https://cdn.jsdelivr.net/gh/imiphp/imi@2.0/res/pay.png)

开源不求盈利，多少都是心意，生活不易，随缘随缘……
