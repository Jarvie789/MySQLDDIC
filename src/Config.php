<?php
/**
 * @author: 秋田嘉
 * @email: 997348985@qq.com
 * @fileName Config.php
 * @date: 2018.7.10 下午 09:47
 * @describe: TODO
 */

return [
    'database' => [
        'dsn' => 'mysql:host=127.0.0.1;dbname=erp;charset=utf8',
        'username' => 'root',
        'password' => 'admin',
        'driver_options' => [

        ]
    ],
    'excel' => [
        'font_name' => '宋体',
        'font_size' => 10,
        'category' => 'Mysql',
        'creator' => '秋田嘉',
        'title' => '数据字典信息',
        'keywords' => '数据字典',
        'description' => '表详情',
        'created' => time(),
        'last_modified_by' => '秋田嘉<997348985@qq.com>',
        'worksheet' => [
            'version_history' => [
                'list' => [
                    'title' => '版本历史'
                ]
            ],
            'table' => [
                'list' => [
                    'title' => '数据表',
                ],
                'index' => [
                    'title' => '数据表目录',
                ],
            ],
            'view' => [
                'list' => [
                    'title' => '视图'
                ]
            ],
            'trigger' => [
                'list' => [
                    'title' => '触发器'
                ]
            ],
            'procedure' => [
                'list' => [
                    'title' => '存储过程'
                ]
            ],
            'function' => [
                'list' => [
                    'title' => '函数'
                ]
            ]
        ]
    ],
];