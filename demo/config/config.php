<?php
return [
    // 数据库配置
    'database' => [
        'dsn' => 'mysql://root@127.0.0.1/test',
        'driver' => 'mysql',                    // 数据库类型
        'host' => '127.0.0.1',                    // 地址
        'port' => 3306,                            // 端口
        'dbname' => 'test',                  // 数据库名称
        'user' => 'root',                            // 帐号
        'passwd' => 'root',                        // 密码
        'charset' => 'utf8',                        // 数据表编码
    ],
    /////////////
    // 加载用户数据 //
    /////////////
    'alias' => [],
    // 模版配置
    'view' => [
        'tpl_driver' => 'template',    // 模板引擎,如果使用php原生留空即可
        'tpl_theme' => 'default',      // 模板主题名
        'tpl_path' => 'views',        // 模板路径
        'tpl_suffix' => '.phtml',        // 模板文件后缀
        'tpl_prefix' => 'q_cache_',    // 缓存文件前缀
        'tpl_expire' => 0,             // 模板文件缓存时间
        'tpl_cache_path' => 'D:\phpStudy\WWW\qframework\qframework\sys_cache\caches',             // 模板文件缓存位置
    ],
    //缓存配置
    'cache' => [
        'apc' => [
            'prefix' => 'q_',
            'expire' => 60
        ],
        'file' => [
            'path' => 'D:\phpStudy\WWW\qframework\qframework\sys_cache\caches',
            'prefix' => 'q_',
            'expire' => 60
        ],
        'memcached' => [
            'host' => '127.0.0.1',
            'port' => '11211',
            'weight' => '1',
            'prefix' => 'q_',
            'expire' => 60,
            'servers' => [
                [
                    '127.0.0.1', '11212', 1
                ],
                [
                    '127.0.0.1', '11213', 2
                ]
            ]
        ],
        'redis' => [
            'host' => '127.0.0.1',
            'port' => '6385',
            'password' => null,
            'timeout' => 30,
            'prefix' => 'q_',
            'expire   ' => 60
        ],
        'memcache' => [

        ]
    ],
    // session
    'session' => [
        'auto_start' => false,    // 自动加载
        'passwd' => '',    // 连接store的密码
        // 'dsn' => 'mysql://root:@127.0.0.1:3306/qframework/session',
        // 'dsn'=>'memcache://127.0.0.1:11211', 	// 字符串形式
        'dsn' => 'redis://127.0.0.1:6385',    // 字符串形式
        'driver' => 'redis',                    // 存储session数据库的模型
        'host' => '127.0.0.1',                    // 地址
        'port' => 6385,                            // 端口号
        'user' => 'root',                            // 用户名
        'timeout' => 30,                             //for redis
        'dbname' => 'qframework',                // 存储session的数据库名称
        'tbname' => 'session',                // 存储session的数据表名称
        'charset' => 'utf8',                        // 编码方式
        'prefix' => '',                            // 表前缀
        'sess_name' => 'QFRAMEWORKSSID',
        'sess_expire' => 3600 * 24,        // 默认session过期时间
        'sess_save_path' => '/data/wwwroot/qframework/cache/session',               //session路径
        'cookie_lifetime' => 0,            //传递会话ID的Cookie有效期(秒)，0 在浏览器打开期间有效
        'cookie_path' => '/',            // cookie 路径
        'cookie_domain' => '',        // cookie 域名
        'cookie_secure' => false,     //否仅仅通过安全连接(https)发送cookie
    ],
    //****扩展配置****//
    'application' => [
        'id' => 'demo',                                                // 应用程序的id，项目的命名空间会用到
        'timezone' => 'RPC',                                            // 设置时区
    ],
    'ext_files' => ['hooks', 'siteconf'],                                           //扩展配置文件
    'xss_filter' => true,
    'csrf_filter' => true,
    'csrf_token_name' => 'csrf_test_name',
    'csrf_cookie_name' => 'csrf_cookie_name',
    'csrf_expire' => 7200,
    'csrf_exclude_uris' => [],//白名单
    'cookie' => [
        'cookie_expire' => 2592000,
        'cookie_prefix' => 'qframework_',
        'cookie_domain' => '',
        'cookie_path' => '/'
    ],
    // 路由配置
    'router' => [
        # 1 default：index.php?m=user&c=index&a=run
        # 2 rewrite：/user/index/run/?id=100
        # 3 path: /user/index/run/id/100
        # 4 html: user-index-run?uid=100
        'urlmode' => 4,
        'defaultModule' => '',            // 默认模型
        'defaultController' => 'Index',        // 默认控制器
        'defaultAction' => 'index',            // 默认方法
        'base' => 'http://localhost/qframework',//站点url
        'indexPage' => 'index.php',
        'showDomain' => true,//是否显示域名
        'pathinfo_depr' => '/',//pathinfo模式下参数分割符
        'regex' => [                            // 正则匹配url规则
            'pattern' => 'Index',
        ],
    ],
    'log' => [
        'is_open' => true,
        'dir' => 'demo/logs',
        'file_size' => 8388608,
    ],
    'exception_tpl' => APP_PATH . '/views/exception.html',
    'debug' => true,
];