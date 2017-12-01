<?php
/**
 * 数据库连接类
 *
 *
 */

namespace Bybzmt\DB;

use PDO;

/**
 * 数据库连接类
 */
class PDOConn extends PDO
{
    public function find($table, array $columns, array $where0)
    {
        $_columns = implode("`,`", $columns);

        $tmp = array();

        $sql = "SELECT `$_columns` FROM `{$table}` WHERE " . $this->_where($where, $tmp);

        return $this->fetch($sql, $tmp);
    }

    public function findAll($table, array $columns, array $where, $offset=0, $length=0)
    {
        $_columns = implode("`,`", $columns);

        $tmp = array();

        $sql = "SELECT `$_columns` FROM `{$table}` WHERE " . $this->_where($where, $tmp);

        return $this->fetchAll($sql, $tmp, $offset, $length);
    }

	public function findColumn($table, $column, array $where)
    {
        $tmp = array();

        $sql = "SELECT `$column` FROM `{$table}` WHERE " . $this->_where($where, $tmp);

        return $this->fetchColumn($sql, $tmp);
    }

    public function findColumnAll($table, $column, array $where, $offset=0, $length=0)
    {
        $tmp = array();

        $sql = "SELECT `$column` FROM `{$table}` WHERE " . $this->_where($where, $tmp);

        return $this->fetchColumnAll($sql, $tmp, 0, $offset, $length);
    }

	/**
	 * 执行sql并取出1行记录
	 *
	 * 成功返回 array 失败返回 false
	 */
	public function fetch($sql, array $params=array())
	{
        $sql .= " LIMIT 1";

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
	public function fetchAll($sql, array $params=array(), $offset=0, $length=0)
	{
        if ($length > 0) {
            $sql .= " LIMIT " . (int)$offset . ', '. (int)$length;
        }

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
	public function fetchColumn($sql, array $params=null, $column=0)
	{
        $sql .= " LIMIT 1";

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
	public function fetchColumnAll($sql, array $params=null, $column=0, $offset=0, $length=0)
	{
        if ($length > 0) {
            $sql .= " LIMIT " . (int)$offset . ', '. (int)$length;
        }

		if ($params) {
			$stmt = $this->prepare($sql);
			if (!$stmt) { return false; }

			$flag = $stmt->execute($params);
			if (!$flag) { return false; }
		} else {
			$stmt = $this->query($sql);
			if (!$stmt) { return false; }
		}

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
	 * @return bool
	 */
	public function insert($table, $row)
	{
		$keys = implode('`, `', array_keys($row));
        $holds = implode(',', array_fill(0, count($row), '?'));
		$vals = array_values($row);

		$sql = "INSERT INTO `{$table}` (`{$keys}`) VALUES({$holds})";

        $stmt = $this->prepare($sql);
        return $stmt ? $stmt->execute($vals) : false;
	}

	/**
	 * 添加一批记录
	 *
	 * @param string $table  表名
	 * @param array  $values 格式: array( array(key=>val, ..), ..)
     *
	 * @return bool
	 */
	public function inserts($table, $rows)
	{
        if (!$rows) {
            return false;
        }

		$holds = array();

		$feilds = array_keys(reset($rows));

        $hold = '('.implode(',', array_fill(0, count($feilds), '?')).')';
		$holds = implode(",\n", array_fill(0, count($rows), $hold));
		$feilds = implode("`,`", $feilds);

        $vals = [];
        foreach ($rows as $row) {
            $vals = array_merge($vals, array_values($row));
        }

		$sql = "INSERT INTO `{$table}` (`{$feilds}`)\n VALUES {$holds}";

        $stmt = $this->prepare($sql);
        return $stmt ? $stmt->execute($vals) : false;
	}

	/**
	 * 修改一条数据
	 *
	 * @param string $table 表名
	 * @param array  $feilds 数据 (格式: key=>val)
	 * @param array $where key/val结构, 其中如val为组将会解析成 key IN (...)
	 * @param int    $limit 最大修改数量限制(可选)
     *
	 * @return bool
	 */
	public function update($table, $feilds, array $where, $limit=0)
	{
		$set = array();
        $vals = array();

		foreach ($feilds as $key => $val) {
			$set[] = "`{$key}` = ?";
            $vals[] = $val;
		}

		$set = implode(', ', $set);

		$sql = "UPDATE {$table} SET {$set} WHERE " . $this->_where($where, $vals);

        if ($limit > 0) {
            $sql .= " LIMIT ".(int)$limit;
        }

        $stmt = $this->prepare($sql);
        return $stmt ? $stmt->execute($vals) : false;
	}

	/**
	 * 删除数据
	 *
	 * @param string $table 表名
	 * @param array $where key/val结构, 其中如val为组将会解析成 key IN (...)
	 * @param int    $limit 最大修改数量限制(可选)
	 *
	 * @return bool
	 */
	public function delete($table, array $where, $limit=0)
	{
        $tmp = array();

		$sql = "DELETE FROM {$table} WHERE " . $this->_where($where, $tmp);

		if ($limit) {
			$sql .= " LIMIT ".(int)$limit;
		}

        $stmt = $this->prepare($sql);
        return $stmt ? $stmt->execute($tmp) : false;
	}

	private function _where($where, &$tmp)
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
