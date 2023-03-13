<?php

namespace SMVC\Model;

use SMVC\Core\Model;
use SMVC\Model\Status;

class Messaging extends Model {

  public function __construct($app) {
    parent::__construct($app);

    $this->module = 'Messaging';
    $this->table = 'messaging';
    $this->base_url_path = 'messaging';

    // table columns
    $this->columns = [
      $this->table.'.id',
      $this->table.'.contact_group_id',
      'contact_groups.name AS group_name',
      $this->table.'.subject',
      $this->table.'.message',
      'creator.username AS created_by',
      $this->table.'.created_at',
      'updator.username AS updated_by',
      $this->table.'.updated_at',
    ];

    // columns to display on UI
    $this->listing = [
      'id' => ['table'=>$this->table, 'label'=>'ID'],
      'group_name' => ['table'=>'contact_groups', 'label'=>'Group'],
      'subject' => ['table'=>$this->table, 'label'=>'Subject'],
      'message' => ['table'=>$this->table, 'label'=>'Message'],
      'created_by' => ['table'=>'creator', 'label'=>'Created By'],
      'created_at' => ['table'=>$this->table, 'label'=>'Created At'],
      'updated_by' => ['table'=>'updator', 'label'=>'Updated By'],
      'updated_at' => ['table'=>$this->table, 'label'=>'Updated At']
    ];

    // columns to search when filtering from UI
    $this->columnsToSearch = [
      $this->table.'.id',
      'contact_groups.name',
      $this->table.'.subject',
      $this->table.'.message',
      'creator.username',
      $this->table.'.created_at',
      'updator.username',
      $this->table.'.updated_at'
    ];

    $this->joins = [
      'inner' => [
        "users creator ON {$this->table}.created_by = creator.id",
        "users updator ON {$this->table}.updated_by = updator.id",
        "messaging_contact_mapping ON {$this->table}.id = messaging_contact_mapping.messaging_id",
      ],
      'left' => [
        "contact_groups ON {$this->table}.contact_group_id = contact_groups.id"
      ]
    ];

    // links and buttons, with their respective permission
    $this->links = [
      'header' => [
        [
          'name' => 'export',
          'label' => 'Export Messaging',
          'href' => $this->base_url_path.'/export',
          'permissions' => ['Can export sent messages']
        ]
      ],
      'listing' => [
        [
          'name' => 'view',
          'label' => 'View',
          'icon' => 'view',
          'href' => $this->base_url_path.'/{id}/view',
          'permissions' => ['Can view sent messages']
        ],
      ],
      'detail' => [
        'SMVC-detail-buttons-left' => [],
        'SMVC-detail-buttons-right' => [
          [
            'name' => 'contacts',
            'label' => 'View Contacts',
            'icon' => 'manage-contacts',
            'href' => $this->base_url_path.'/{id}/contacts',
            'permissions' => ['Can manage group contacts']
          ]
        ]
      ]
    ];

  }

  public function fetchMessaging($vars, $options = []) {
    return $this->fetchOne($vars, $options);
  }

  public function fetchMessagings($vars = [], $options = []) {
    return $this->fetchMultiple($vars, $options);
  }

  public function newMessaging($subject, $message, $contact_ids, $group_id = null, $created_by = 0) {
    $insert = $this->insert(
      [
        'contact_group_id' => $group_id,
        'subject' => $subject,
        'message' => $message,
        'created_by' => $created_by,
        'updated_by' => $created_by
      ]
    );

    if($insert) {
      $ids = [];
      foreach($contact_ids as $a) {
        $ids[] = "({$insert}, {$a})";
      }
      $ids_sql = implode(', ', $ids);

      $insert_sql = "INSERT INTO messaging_contact_mapping (`messaging_id`,`contact_id`) VALUES {$ids_sql}";
      return $this->db->query($insert_sql);
    }
    return false;
  }

  public function loadMessagingContacts($group_id) {
    $sql = "SELECT DISTINCT id, surname, other_names FROM contacts WHERE id IN (SELECT contact_id FROM contact_group_mapping WHERE group_id = :group_id)";
    return $this->db->query($sql, ['group_id' => $group_id]);
  }

}
