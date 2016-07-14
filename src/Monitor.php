<?php
namespace bybzmt\DB;

/**
 * 数据库连执行情况分析
 */
class Monitor
{
	/**
	 * 监控实例,可以把自己的实际监控程序的实例设置过来
	 */
	static public $instance;

	static public function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * 记录执行时间
	 */
	public function logSQLRun($time, $sql, $param=null)
	{
	}
}
