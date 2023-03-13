<?php

namespace SMVC\Core;

use SMVC\Core\Database;
use SMVC\Model\Status;

class Model {

  protected $app;
  public $db;
  public $module = null;
  protected $table = null;
  protected $columns = [];
  protected $columnsToSearch = [];
  protected $joins = [];
  protected $groupby = [];

  public $base_url_path = null;
  public $listing = []; // UI listing columns
  public $detail = []; // UI detail columns, uses listing if not set
  public $form = [];
  public $links = [];

  public function __construct($app, $db_instance = null) {
    $this->app = $app;
    $this->db = new Database($app, $db_instance);
  }

  protected function getMergedOptions($type = null, $options = []) {
    $default_options = [
      'one' => [
        'columns' => [],
        'orderBy' => 'id'
      ],
      'multiple' => [
        'submit' => null,
        'search' => null,
        'columns' => [],
        'page' => 1,
        'perPage' => 50,
        'start' => 0,
        'orderBy' => 'id',
        'orderDir' => 'ASC'
      ],
      'update' => [
        'limit' => null
      ],
      'delete' => [
        'limit' => null
      ]
    ];

    // validate type
    if(!in_array($type, array_keys($default_options))) {
      return [];
    }

    // build options
    $merged = [];
    foreach($default_options[$type] as $k => $v) {
      $merged[$k] = $v;
      if(isset($options[$k]) && !empty($options[$k])) {
        $merged[$k] = $options[$k];
      }
    }

    // start row
    if($type == 'multiple' && $merged['perPage'] != 'all') {
      $merged['start'] = ($merged['page'] - 1) * $merged['perPage'];
    }

    // return
    return $merged;
  }

  protected function fetchOne($vars = [], $options = [], $where_params = []) {

    // merge options and extract as individual variables
    $options = $this->getMergedOptions('one', $options);

    // columns
    $columns = empty($options['columns']) ? $this->columns : $options['columns'];
    $columns = implode(', ', $columns);

    // params
    $where_arr = [];
    foreach($vars as $col => $value) {
      if(is_int($col)) { // direct query string
        $where_arr[] = "({$value})";
      } else {
        $c = str_replace('.', '_', $col);
        $where_params[$c] = $value;
        if(strpos($col, '.') !== false) {
          $where_arr[] = is_null($value) ? "$col IS NULL" : "$col = :{$c}";
        } else {
          $where_arr[] = is_null($value) ? "{$this->table}.{$col} IS NULL" : "{$this->table}.{$col} = :{$c}";
        }
      }
    }
    $where = implode(' AND ', $where_arr);

    // joins
    $joins = $this->getJoins();

    // group by
    $groupby = null;
    if(!empty($this->groupby)) {
      $groupby = 'GROUP BY ';
      foreach($this->groupby as $gb) {
        $groupby .= $gb.', ';
      }
    }
    $groupby = trim(trim($groupby), ',');

    // orderby
    $orderBy = null;
    if(isset($options['orderBy'])) {
      $orderBy = 'ORDER BY ';
      if($options['orderBy'] == 'RAND()') {
        $orderBy .= 'RAND()';
      } else {
        if(isset($this->listing[$options['orderBy']]['table'])) {
          $orderBy .= $this->listing[$options['orderBy']]['table'].'.'.$options['orderBy'];
        } else {
          $orderBy .= $this->table.'.'.$options['orderBy'];
        }
      }
    }

    $sql = "SELECT {$columns} FROM {$this->table} {$joins} WHERE {$where} {$groupby} {$orderBy} LIMIT 1";
    return $this->db->row($sql, $where_params);
  }

  protected function fetchMultiple($vars = [], $options = [], $where_var_params = [], $distinct = false) {

    // merge options and extract as individual variables
    $options = $this->getMergedOptions('multiple', $options);

    // columns
    $columns = empty($options['columns']) ? $this->columns : $options['columns'];
    $columns = implode(', ', $columns);

    // search
    $where = null;
    if(!is_null($options['search'])) {
      $search_columns = 'IFNULL('.implode(',""), IFNULL(', $this->columnsToSearch).',"")';
      $where = " WHERE (CONCAT({$search_columns}) REGEXP '{$options['search']}') ";
    }

    // vars
    $where_var_arr = [];
    foreach($vars as $col => $value) {
      if(is_int($col)) { // direct query string
        $where_var_arr[] = "({$value})";
      } else {
        $c = str_replace('.', '_', $col);
        $where_var_params[$c] = $value;
        if(strpos($col, '.') !== false) {
          $where_var_arr[] = is_null($value) ? "$col IS NULL" : "$col = :{$c}";
        } else {
          $where_var_arr[] = is_null($value) ? "{$this->table}.{$col} IS NULL" : "{$this->table}.{$col} = :{$c}";
        }
      }
    }
    if(!empty($where_var_arr)) {
      $where_var = implode(' AND ', $where_var_arr);
      $where .= is_null($where) ? 'WHERE '.$where_var : 'AND '.$where_var;
    }

    // joins
    $joins = $this->getJoins();

    // group by
    $groupby = null;
    if(!empty($this->groupby)) {
      $groupby = 'GROUP BY ';
      foreach($this->groupby as $gb) {
        $groupby .= $gb.', ';
      }
    }
    $groupby = trim(trim($groupby), ',');

    // orderby
    $orderBy = 'ORDER BY '.$this->listing[$options['orderBy']]['table'].'.'.$options['orderBy'];

    // sql
    $select = $distinct ? 'SELECT DISTINCT' : 'SELECT';
    $select_sql = "{$select} {$columns} FROM {$this->table} {$joins} {$where} {$groupby} {$orderBy} {$options['orderDir']}";
    if($options['perPage'] != 'all') {
      $select_sql .= " LIMIT {$options['start']}, {$options['perPage']}";
    }
    $rows = $this->db->query($select_sql, $where_var_params);

    // count
    $count_sql = "SELECT COUNT(DISTINCT({$this->table}.id)) AS count FROM {$this->table} {$joins} {$where}";
    $count = $this->db->query($count_sql, $where_var_params);
    $count = array_shift($count);

    // count pages
    $pages = 1;
    if($options['perPage'] != 'all') {
      $pages = ceil($count->count / $options['perPage']);
      if($pages == 0) {
        $pages = 1;
      }
    }

    // return
    $return = (object) [
      'rows' => $rows,
      'meta' => (object) [
        'count' => $count->count,
        'start' => $options['start'],
        'perPage' => $options['perPage'],
        'page' => $options['page'],
        'pages' => $pages
      ]
    ];
    return $return;
  }

  protected function update($values, $where, $options = []) {

    if(is_object($values)) { $values = (array) $values; }
    if(is_object($where)) { $where = (array) $where; }

    if(!is_array($values) || !is_array($where)) {
      return false;
    }

    // merge options and extract as individual variables
    $options = $this->getMergedOptions('update', $options);

    // generate columns
    $values_keys = array_keys($values);
    $columns_arr = [];
    foreach($values_keys as $vk) {
      $columns_arr[] = $vk.' = :c_'.$vk;
    }
    $columns_sql = implode(', ', $columns_arr);

    // generate where
    $where_keys = array_keys($where);
    $where_arr = [];
    foreach($where_keys as $wa) {
      $where_arr[] = $wa.' = :w_'.$wa;
    }
    $where_sql = empty($where_arr) ? null : 'WHERE '.implode(' AND ', $where_arr);

    // limit
    $limit = null;
    if(!is_null($options['limit'])) {
      $limit = "LIMIT {$options['limit']}";
    }

    // params
    $params = [];
    foreach($values as $k => $v) {
      $params['c_'.$k] = $v;
    }
    foreach($where as $k => $v) {
      $params['w_'.$k] = $v;
    }

    $sql = "UPDATE {$this->table} SET {$columns_sql} {$where_sql} {$limit}";
    return $this->db->query($sql, $params);
  }

  protected function delete($where, $options) {

    if(is_object($where)) { $where = (array) $where; }
    if(!is_array($where)) {
      return false;
    }

    // merge options and extract as individual variables
    $options = $this->getMergedOptions('delete', $options);

    // generate where
    $where_keys = array_keys($where);
    $where_arr = [];
    foreach($where_keys as $wa) {
      $where_arr[] = $wa.' = :w_'.$wa;
    }
    $where_sql = implode(' AND ', $where_arr);

    // limit
    $limit = null;
    if(!is_null($options['limit'])) {
      $limit = "LIMIT {$options['limit']}";
    }

    // params
    $params = [];
    foreach($where as $k => $v) {
      $params['w_'.$k] = $v;
    }

    $sql = "DELETE FROM {$this->table} WHERE {$where_sql} {$limit}";
    return $this->db->query($sql, $params);
  }

  protected function disable($where, $options) {
    return $this->update(['status_id' => Status::INACTIVE_STATUS], $where, $options);
  }

  protected function enable($where, $options) {
    return $this->update(['status_id' => Status::ACTIVE_STATUS], $where, $options);
  }

  protected function insert($values, $insert_type="insert") {

    if(!is_array($values)) {
      return false;
    }

    // generate columns
    $values_keys = array_keys($values);
    $columns_arr = [];
    foreach($values_keys as $vk) {
      $columns_arr[] = ':'.$vk;
    }
    $columns_sql = implode(', ', $values_keys);
    $columns_values_sql = implode(', ', $columns_arr);

    // type
    $insert_type_sql = "INSERT";
    if($insert_type == 'insert ignore') {
      $insert_type_sql = "INSERT IGNORE";
    }

    $sql = "{$insert_type_sql} INTO {$this->table} ($columns_sql) VALUES ($columns_values_sql)";
    return $this->db->query($sql, $values);
  }

  protected function getJoins() {
    if(empty($this->joins)) {
      return null;
    }

    $joins_sql = null;
    foreach($this->joins as $join_type => $joins) {
      switch($join_type) {
        case 'inner':
          $joins_sql .= " INNER JOIN ".implode(" INNER JOIN ", $joins);
          break;
        case 'left':
          $joins_sql .= " LEFT JOIN ".implode(" LEFT JOIN ", $joins);
          break;
      }
    }

    return $joins_sql;
  }

  public function setModule($module) {
    $this->module = $module;
  }
}
