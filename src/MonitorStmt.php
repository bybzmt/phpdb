<?php
namespace bybzmt\DB;

/**
 * PDOStatement套子 用来监控执行
 */
class loggerStmt extends \PDOStatement
{
	private $logger;

	protected function __construct($logger)
	{
		$this->logger = $logger;
	}

	//记录执行时间
	public function execute($params=null)
	{
		$t1 = microtime(true);
		$out = parent::execute($params);
		$t2 = microtime(true);

		$this->logger($t2-$t1, $this->queryString, $params);

		return $out;
	}
}
