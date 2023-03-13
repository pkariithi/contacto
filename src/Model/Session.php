<?php

namespace SMVC\Model;

use SMVC\Core\Model;
use SMVC\Helpers\Date;

class Session extends Model {

  public function __construct($app) {
    parent::__construct($app);

    // module and table
    $this->module = 'Session';
    $this->table = 'sessions';
    $this->base_url_path = 'sessions';

    // table columns
    $this->columns = [
      $this->table.'.id',
      $this->table.'.user_id',
      'users.username',
      $this->table.'.ip_address',
      $this->table.'.session',
      $this->table.'.logged_in_at',
      $this->table.'.last_seen_at',
      'status.status'
    ];

    // columns to display on UI
    $this->listing = [
      'id' => ['table'=>$this->table, 'label'=>'ID'],
      'username' => ['table'=>'users', 'label'=>'Username'],
      'ip_address' => ['table'=>$this->table, 'label'=>'IP Address'],
      'logged_in_at' => ['table'=>$this->table, 'label'=>'Logged In At'],
      'last_seen_at' => ['table'=>$this->table, 'label'=>'Last Seen At'],
      'status' => ['table'=>'status', 'label'=>'Status']
    ];

    // columns to search when filtering from UI
    $this->columnsToSearch = [
      'users.username',
      $this->table.'.ip_address',
      $this->table.'.logged_in_at',
      $this->table.'.last_seen_at',
      'status.status',
    ];

    $this->joins = [
      'inner' => [
        "users ON {$this->table}.user_id = users.id",
        "status ON {$this->table}.status_id = status.id",
      ]
    ];

    // links and buttons, with their respective permission
    $this->links = [
      'header' => [
        [
          'name' => 'export',
          'label' => 'Export All Sessions',
          'href' => $this->base_url_path.'/export',
          'permissions' => ['Can export sessions']
        ]
      ],
      'listing' => [
        [
          'name' => 'view',
          'label' => 'View',
          'icon' => 'view',
          'href' => $this->base_url_path.'/{id}/view',
          'permissions' => ['Can view sessions']
        ]
      ],
      'detail' => [
        'detail-buttons-left' => [],
        'detail-buttons-right' => []
      ]
    ];
  }

  public function fetchSession($vars, $options = []) {
    return $this->fetchOne($vars, $options);
  }

  public function fetchSessions($vars = [], $options = []) {
    return $this->fetchMultiple($vars, $options);
  }

  public function updateSession($values, $where, $options = []) {
    return $this->update($values, $where, $options);
  }

  public function getActiveSession($status_id = Status::ACTIVE_STATUS) {
    $session_name = $this->app->config->security->logged_in_session_name;
    if($this->app->session->has($session_name)) {
      $session_id = $this->app->session->get($session_name);
      return $this->fetchSession([
        'session'=>$session_id,
        'status_id'=>$status_id
      ]);
    }
    return false;
  }

  public function closeSessions($user_id) {
    $sql = "UPDATE {$this->table} SET status = :closed_status WHERE user_id = :user_id AND status = :active_status";
    return $this->db->query($sql, [
      'user_id' => $user_id,
      'closed_status' => Status::CLOSED_STATUS,
      'active_status' => Status::ACTIVE_STATUS
    ]);
  }

  public function updateLastSeen($id) {
    return $this->update(
      ['last_seen_at' => Date::now('Y-m-d H:i:s')],
      ['id' => $id]
    );
  }

  public function newSession($session_id, $user_id) {
    return $this->insert(
      [
        'ip_address' => $this->app->request->getIpAddress(),
        'session' => $session_id,
        'user_id' => $user_id,
        'status_id' => Status::ACTIVE_STATUS
      ]
    );
  }
}
