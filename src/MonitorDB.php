<?php
namespace bybzmt\DB;

/**
 * 数据库连执行情况分析
 */
class MonitorDB extends DB
{
	private $monitor;

	public function __construct($dsn, $user, $pass, array $opts=array())
	{
		$this->monitor = Monitor::getInstance();

		$opts[\PDO::ATTR_STATEMENT_CLASS] = array(__NAMESPACE__.'\MonitorStmt', array($this->monitor));

		parent::__construct($dsn, $user, $pass, $opts);
	}

	//记录执行时间
	public function exec($sql)
	{
		$t1 = microtime(true);
		$out = parent::exec($sql);
		$t2 = microtime(true);

		$this->monitor->logSQLRun($t2-$t1, $sql);

		return $out;
	}

	//记录执行时间
	public function query($sql)
	{
		$t1 = microtime(true);
		$out = call_user_func_array(array('parent', 'query'), func_get_args());
		$t2 = microtime(true);

		$this->monitor->logSQLRun($t2-$t1, $sql);

		return $out;
	}

}
