# MySQLDDIC 
## 使用PHP CLI模式生成数据库数据字典表格
- [x] 使用Excel表格方式浏览整理
- [x] 支持导出数据表目录、数据表字段详情
- [x] 支持导出视图详情（待完善）
- [x] 支持导出触发器（待完善）
- [x] 支持导出存储过程（待完善）
- [x] 支持导出函数（待完善）
- [x] 支持导出客户端用户信息（待完善）
------
### 0. DEMO
![图一](https://github.com/qiutianjia/MySQLDDIC/blob/master/demo/Snipaste_1.png)

![图二](https://github.com/qiutianjia/MySQLDDIC/blob/master/demo/Snipaste_2.png)

![图三](https://github.com/qiutianjia/MySQLDDIC/blob/master/demo/Snipaste_3.png)

![图四](https://github.com/qiutianjia/MySQLDDIC/blob/master/demo/Snipaste_4.png)

### 1. 安装项目
1. git@github.com:qiutianjia/MySQLDDIC.git
2. cd MySQLDDIC
3. composer install
### 2. 修改配置文件src/Config.php需要导出数据库连接信息
```php
'database' => [
    'dsn' => 'mysql:host=127.0.0.1;dbname=erp;charset=utf8',
    'username' => 'root',
    'password' => 'admin',
    'driver_options' => [
    ]
]
```
### 3. 在项目根目录执行，等待文件导出完成提示信息
```shell
php index.php
```


  [1]: https://github.com/qiutianjia/MySQLDDIC/blob/master/demo/Snipaste_1.png
