<?php
/**
 * 数据库连接类
 *
 *
 */

namespace Bybzmt\DB;

/**
 * 数据库连接类
 */
class Prefix extends DB
{
	protected $placehold;
	protected $prefix;

	// 替换前缀
	public function prepare($sql, $opts=array())
	{
		return parent::prepare($this->replace_prefix($sql), $opts);
	}

	// 替换前缀
	public function exec($sql)
	{
		return parent::exec($this->replace_prefix($sql));
	}

	// 替换前缀
	public function query($sql)
	{
		$params = func_get_args();
		$params[0] = $this->replace_prefix($sql);

		return call_user_func_array(array('parent', 'query'), $params);
	}

	//设置前缀
	public function setPrefix($placehold, $prefix)
	{
		$this->placehold = $placehold;
		$this->prefix = $prefix;
	}

	// 替换前缀
	protected function replace_prefix($sql)
	{
		return $this->placehold ? str_replace($this->placehold, $this->prefix, $sql) : $sql;
	}
}
