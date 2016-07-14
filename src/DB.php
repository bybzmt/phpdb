<?php
/**
 * 数据库连接类
 *
 *
 */

namespace bybzmt\DB;

use \PDO;

/**
 * 数据库连接类
 */
class DB extends \PDO
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

	/**
	 * 执行sql并取出1行记录
	 *
	 * 成功返回 array 失败返回 false
	 */
	public function fetch($sql, array $params=array())
	{
		if ($params) {
			$stmt = $this->prepare($sql);
			if (!$stmt) { return false; }

			$flag = $stmt->execute($params);
			if (!$flag) { return false; }
		} else {
			$stmt = $this->query($sql);
			if (!$stmt) { return false; }
		}

		return $stmt->fetch();
	}

	/**
	 * 执行sql并取出所有记录
	 *
	 * 成功返回 array 失败返回 false
	 */
	public function fetchAll($sql, array $params=array())
	{
		if ($params) {
			$stmt = $this->prepare($sql);
			if (!$stmt) { return false; }

			$flag = $stmt->execute($params);
			if (!$flag) { return false; }
		} else {
			$stmt = $this->query($sql);
			if (!$stmt) { return false; }
		}

		return $stmt->fetchAll();
	}

	/**
	 * 执先sql并取第1行中指定列的数据
	 *
	 * 成功返回数据 失败返回 false
	 */
	public function fetchColumn($sql, array $params=array(), $column=0)
	{
		if ($params) {
			$stmt = $this->prepare($sql);
			if (!$stmt) { return false; }

			$flag = $stmt->execute($params);
			if (!$flag) { return false; }
		} else {
			$stmt = $this->query($sql);
			if (!$stmt) { return false; }
		}

		return $stmt->fetchColumn($column);
	}

	/**
	 * 执行sql并取出指定列的所有数据
	 *
	 * 成功返回 array 失败返回 false
	 */
	public function fetchColumnAll($sql, array $params=array(), $column=0)
	{
		if ($params) {
			$stmt = $this->prepare($sql);
			if (!$stmt) { return false; }

			$flag = $stmt->execute($params);
			if (!$flag) { return false; }
		} else {
			$stmt = $this->query($sql);
			if (!$stmt) { return false; }
		}

		//return $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_BOTH, $column);
		return $stmt->fetchAll(PDO::FETCH_COLUMN, $column);
	}

	/**
	 * 执行一条 SQL 语句，并返回受影响的行数
	 *
	 * 成功返回 数字 失败返回 false
	 */
	public function execute($sql, array $params=array())
	{
		if ($params) {
			$stmt = $this->prepare($sql);
			if (!$stmt) { return false; }

			$flag = $stmt->execute($params);
			if (!$flag) { return false; }
		} else {
			$stmt = $this->query($sql);
			if (!$stmt) { return false; }
		}

		return $stmt->rowCount();
	}

	/**
	 * 添加一条记录
	 *
	 * @param string $table 表名
	 * @param array  $feilds 数据 (格式: key=>val, key2=>val2)
	 * @return 失败返回false, 成功返影响记录条数
	 */
	public function insert($table, $feilds)
	{
		$keys = $vals = array();

		foreach ($feilds as $key => $val) {
			$keys[] = "`{$key}`";
			$vals[] = $this->quote($val);
		}

		$keys = implode(', ', $keys);
		$vals = implode(', ', $vals);

		$sql = "INSERT INTO {$table}\n({$keys})\nVALUES({$vals})";

		return $this->exec($sql);
	}

	/**
	 * 添加一批记录
	 *
	 * @param string $table  表名
	 * @param array  $values 格式: array( array(key=>val, ..), ..)
	 * @return 失败返回false, 成功返影响记录条数
	 */
	public function inserts($table, $values)
	{
		$vals = array();

		$feilds = array_keys(reset($values));

		foreach ($values as $value) {
			$tmp = array();

			foreach ($value as $val) {
				$tmp[] = $this->quote($val);
			}

			$vals[] = '(' . implode(', ', $tmp) . ')';
		}

		$vals = implode(",\n", $vals);

		$sql = "INSERT INTO {$table}\n(`" .implode('`, `', $feilds). "`)\nVALUES {$vals}";

		return $this->exec($sql);
	}

	/**
	 * 修改一条数据
	 *
	 * @param string $table 表名
	 * @param array  $feilds 数据 (格式: key=>val)
	 * @param array|string  $where
	 *     如果where为字串则会直接作为sql的where使用
	 *     如查where为数组 格式为: array(key=>val, key2=>val2)
	 *     其中如果val为数组为会解析成 key in (...)
	 *
	 * @return 失败返回false, 成功返影响记录条数
	 */
	public function update($table, $feilds, $where)
	{
		$set = array();

		foreach ($feilds as $key => $val) {
			$set[] = "`{$key}` = " . $this->quote($val);
		}

		$set = implode(', ', $set);

		$sql = "UPDATE {$table} SET {$set} WHERE " . $this->_where($where);

		return $this->exec($sql);
	}

	/**
	 * 删除数据
	 *
	 * @param string $table 表名
	 * @param int    $limit 最大修改数量限制(可选)
	 * @param array|string  $where
	 *     如果where为字串则会直接作为sql的where使用
	 *     如查where为数组 格式为: array(key=>val, key2=>val2)
	 *     其中如果val为数组为会解析成 key in (...)
	 *
	 * @return 失败返回false, 成功返影响记录条数
	 */
	public function delete($table, $where, $limit=0)
	{
		$sql = "DELETE FROM {$table} WHERE " . $this->_where($where);

		if ($limit) {
			$sql .= "LIMIT {$limit}";
		}

		return $this->exec($sql);
	}

	private function _where($where)
	{
		if (!is_array($where)) {
			return $where;
		}

		$_where = array();

		foreach ($where as $key => $val) {
			if (is_array($val)) {
				$_where[] = "`{$key}` IN (" . implode(', ', array_map(array($this, 'quote'), $val)) . ')';
			}
			else {
				$_where[] = "`{$key}` = " . $this->quote($val);
			}
		}

		return implode(' AND ', $_where);
	}


}
