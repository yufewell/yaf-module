<?php

class DB {

    protected static $instance   = null;    // 类的实例
    protected static $connection = null;    // 数据库连接实例
    protected static $table      = '';
    protected static $db_prefix  = '';

    protected $fields            = '*';
    protected $condition         = '';
    protected $order             = '';
    protected $limit             = '';
    protected $sql               = '';


    /**
     * 构造方法
     * 获取配置, 连接数据库, 并保存实例
     * 设置表前缀
     */
    public function __construct() {
        $config = Yaf_Registry::get('config')->mysql;

        // $config = Yaf_Config::get('application');
        self::$connection = self::getConnection($config);
        self::$db_prefix = $config->db_prefix;
    }   
    
    /**
     * 获取数据库连接对象
     */
    public static function getConnection($config){
        if (self::$connection !== null) {
            return self::$connection;
        }

        $username = $config['username'];
        $password = $config['password'];
        $host = $config['hostname'];
        $db = $config['database'];
        $port = $config['port'];
        
        self::$connection = new PDO("mysql:dbname=$db;host=$host;port=$port", $username, $password);
        if (!self::$connection) {
            Log::write('数据库连接失败', $config);
        }
        return self::$connection;
    }

    /**
     * 设置table, 获取实例
     */
    public static function table($table = '') {
        if (self::$instance === null)
            self::$instance = new static();

        self::$table = self::$db_prefix . strtolower($table);

        return self::$instance;
    }

    /**
     * 执行sql语句, 并返回结果
     */
    private function query($sql){
        $environ = Yaf_Registry::get('environ');
        if ($environ != 'product') {
            Log::write($sql, [], 'sql'); 
        }
        
        $stmt = self::$connection->prepare($sql);
        return $stmt->execute();
    }

    /**
     * 查询数据
     */
    public function getAll($sql) {
        $stmt = self::$connection->prepare($sql);
        $rst = $stmt->execute();
        if (!$rst) {
            Log::write('getAll error', ['sql' => $sql]);
            return $rst;
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 查询一条数据
     */
    public function getOne($sql) {
        $stmt = self::$connection->prepare($sql);
        $rst = $stmt->execute();
        if (!$rst) {
            Log::write('getOne error', ['sql' => $sql]);
            return $rst;
        }
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * 设置查询字段
     */
    public function field($fields) {
        $this->fields = $fields;
        return self::$instance;
    }

    /**
     * 拼接sql
     */
    private function _make_select_sql() {
        $this->sql = 'SELECT ' . $this->fields . ' FROM ' . '`' . self::$table . '`';
        if (!empty($this->condition)) {
            $this->sql .= ' WHERE ' . $this->condition . ' ';
        }
        if (!empty($this->order)) {
            $this->sql .= ' ORDER BY ' . $this->order . ' ';
        }
        if (!empty($this->limit)) {
            $this->sql .= ' LIMIT ' . $this->limit . ' ';
        }

        $this->condition = $this->order = $this->limit = '';

        // Log::write('SQL: '.$this->sql);
    }

    /**
     * 查询构造方法
     */
    public function select() {
        $this->_make_select_sql();
        return $this->getAll($this->sql);
    }

    /**
     * 获取单条记录
     */
    public function find() {
        $this->_make_select_sql();
        return $this->getOne($this->sql);
    }

    /**
     * 设置where条件
     */
    public function where($condition) {
        $this->condition = $condition;
        return self::$instance;
    }

    /**
     * 设置order条件
     * @param  [type] $order [description]
     * @return [type]        [description]
     */
    public function order($order) {
        $this->order = $order;
        return self::$instance;
    }

    /**
     * 设置limit条件
     * @param  [type] $start    [description]
     * @param  [type] $pagesize [description]
     * @return [type]           [description]
     */
    public function limit($start, $pagesize) {
        $this->limit = intval($start) . ',' . intval($pagesize);
        return self::$instance;
    }

    /**
     * 插入数据
     */
    public function insert($data) {
        $sql = 'INSERT INTO `'. self::$table .'` SET ';
        foreach ($data as $key => $value) {
            $sql .= "`$key` = '$value', ";
        }

        $sql = trim($sql, ', ');

        if (!$this->query($sql)) {
            return false;
        }
        return self::$connection->lastInsertId();
    }

    /**
     * 更新数据
     */
    public function update($data) {
        $sql = 'UPDATE `'. self::$table .'` SET ';
        foreach ($data as $key => $value) {
            $sql .= "`$key` = '$value', ";
        }
        $sql = trim($sql, ', ');

        if ($this->condition) {
            $sql .= ' WHERE ' . $this->condition;
        }

        return $this->query($sql);
    }

    /**
     * 获取uuid
     */
    public function uuid() {
        $sql = 'select uuid() as uuid';
        $data = $this->getOne($sql);
        if (!$data) {
            // add_error_log('获取uuid失败');
            return false;
        }
        return str_replace('-', '', $data['uuid']);
    }

    /**
     * [beginTrans description]
     * @return [type] [description]
     */
    public function beginTrans() {
        self::$connection->beginTransaction();
    }

    /**
     * [rollBack description]
     * @return [type] [description]
     */
    public function rollBack() {
        self::$connection->rollBack();
    }

    /**
     * [commit description]
     * @return [type] [description]
     */
    public function commit() {
        self::$connection->commit();
    }
}