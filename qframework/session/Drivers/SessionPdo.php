<?php
/**
 * Q框架
 * @package Qframework
 * @author  北海有鱼(zhangsw2613@163.com)
 * @since   Version 1.0.0
 */
namespace qframework\session\Drivers;

use qframework\exception\QException;
use qframework\session\SessionHandlerInterface;
use PDO;

class SessionPdo implements SessionHandlerInterface
{

    protected $dsn = '';
    protected static $config = [];
    protected static $db;
    protected $session_id = '';
    protected $row_exists = false;

    public function __construct($config = [])
    {
        $this->dsn = strtolower($config['scheme']) . ':host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['dbname'] . ';charset=' . $config['charset'] . ';';
        self::$config = $config;
        $this->dbConnect();
        $sql = "CREATE TABLE IF NOT EXISTS {$config['tbname']} ( id varchar(128) COLLATE utf8_unicode_ci NOT NULL, `data` text COLLATE utf8_unicode_ci, unixtime int(16) unsigned NOT NULL, PRIMARY KEY (id) ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $sth = self::$db->prepare($sql);
        $sth->execute();
    }

    public function open($file_path, $name)
    {
        if (!is_object(self::$db)) {
            $this->dbConnect();
        }
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($session_id)
    {
        $this->session_id = $session_id;
        $result = $this->sessionFetch($session_id);
        if ($result === false) {
            return '';
        }
        $this->row_exists = true;
        return $result['data'];
    }

    public function write($session_id, $session_data)
    {
        if ($session_id !== $this->session_id) {
            $this->session_id = $session_id;
            $this->row_exists = false;
        }
        if ($this->row_exists === false) {
            $sth = self::$db->prepare("INSERT INTO " . self::$config['tbname'] . " (id, data, unixtime) VALUES (:id, :data, :time)");
            $sth->execute([':id' => $session_id,
                ':data' => $session_data,
                ':time' => time()]);
            $this->row_exists = true;
            return true;
        }
        $sth = self::$db->prepare("UPDATE " . self::$config['tbname'] . " SET data = :data, unixtime = :time WHERE id = :id");
        $sth->execute([':id' => $session_id,
            ':data' => $session_data,
            ':time' => time()]);
        return true;
    }

    public function destroy($session_id)
    {
        $this->dbConnect();
        $sth = self::$db->prepare("DELETE FROM " . self::$config['tbname'] . " WHERE id = :id");
        $sth->execute([':id' => $session_id]);
        return true;
    }

    //垃圾回收
    public function gc($maxlifetime)
    {
        $this->dbConnect();
        $sth = self::$db->prepare("DELETE FROM " . self::$config['tbname'] . " WHERE unixtime < :time");
        $sth->execute([':time' => (time() - (int)$maxlifetime)]);
        return true;
    }

    private function dbConnect()
    {
        try {
            self::$db = new PDO($this->dsn, self::$config['user'], self::$config['passwd']);
        } catch (\PDOException $e) {
            throw new QException($e->getMessage());
        }
    }

    private function  sessionFetch($session_id)
    {
        $sql = "SELECT * FROM " . self::$config['tbname'] . " WHERE id=:id AND unixtime>=" . (time() - self::$config['sess_expire']);
        $sth = self::$db->prepare($sql);
        $sth->execute([':id' => $session_id]);
        $datas = $sth->fetchAll(PDO::FETCH_ASSOC);
        return empty($datas) ? false : $datas[0];
    }
}