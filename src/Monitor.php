<?php
namespace Bybzmt\DB;

use PDO;

/**
 * 数据库连执行情况分析
 */
class Monitor
{
	private $logger;
	private $db;

    /**
     * @param callable logger(time, sql, params)
     */
	public function __construct(PDO $db, callable $logger)
	{
		$this->logger = $logger;
        $this->db = $db;

        $db->setAttribute(PDO::ATTR_STATEMENT_CLASS, array(__NAMESPACE__.'\MonitorStmt', array($this->logger)));
	}

	//记录执行时间
	public function exec($sql)
	{
		$t1 = microtime(true);
		$out = $this->db->exec($sql);
		$t2 = microtime(true);

		$this->logger($t2-$t1, $sql);

		return $out;
	}

	//记录执行时间
	public function query($sql)
	{
		$t1 = microtime(true);
		$out = call_user_func_array(array($this->db, 'query'), func_get_args());
		$t2 = microtime(true);

		$this->logger($t2-$t1, $sql);

		return $out;
	}

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array(array($this->db, $name), $arguments);
    }

}
