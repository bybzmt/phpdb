<?php
namespace bybzmt\DB;

/**
 * PDOStatement套子 用来监控执行
 */
class MonitorStmt extends \PDOStatement
{
	private $monitor;

	protected function __construct($monitor)
	{
		$this->monitor = $monitor;
	}

	//记录执行时间
	public function execute($params=null)
	{
		$t1 = microtime(true);
		$out = parent::execute($params);
		$t2 = microtime(true);

		$this->monitor->logSQLRun($t2-$t1, $this->queryString, $params);

		return $out;
	}
}
