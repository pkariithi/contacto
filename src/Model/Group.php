<?php

namespace SMVC\Model;

use SMVC\Core\Model;
use SMVC\Model\Status;

class Group extends Model {

  public function __construct($app) {
    parent::__construct($app);

    $this->module = 'Group';
    $this->table = 'contact_groups';
    $this->base_url_path = 'groups';

    // table columns
    $this->columns = [
      $this->table.'.id',
      $this->table.'.name',
      'status.status',
      $this->table.'.comments',
      'creator.username AS created_by',
      $this->table.'.created_at',
      'updator.username AS updated_by',
      $this->table.'.updated_at',
    ];

    // columns to display on UI
    $this->listing = [
      'id' => ['table'=>$this->table, 'label'=>'ID'],
      'name' => ['table'=>$this->table, 'label'=>'Name'],
      'status' => ['table'=>'status', 'label'=>'Status'],
      'created_by' => ['table'=>'creator', 'label'=>'Created By'],
      'created_at' => ['table'=>$this->table, 'label'=>'Created At'],
      'updated_by' => ['table'=>'updator', 'label'=>'Updated By'],
      'updated_at' => ['table'=>$this->table, 'label'=>'Updated At']
    ];

    // columns to display on UI
    $this->detail = [
      'id' => ['table'=>$this->table, 'label'=>'ID'],
      'name' => ['table'=>$this->table, 'label'=>'Name'],
      'status' => ['table'=>'status', 'label'=>'Status'],
      'created_by' => ['table'=>'creator', 'label'=>'Created By'],
      'created_at' => ['table'=>$this->table, 'label'=>'Created At'],
      'updated_by' => ['table'=>'updator', 'label'=>'Updated By'],
      'updated_at' => ['table'=>$this->table, 'label'=>'Updated At'],
      'comments' => ['table'=>$this->table, 'label'=>'Comments']
    ];

    // columns to search when filtering from UI
    $this->columnsToSearch = [
      $this->table.'.name',
      'status.status',
      'creator.username',
      $this->table.'.created_at',
      'updator.username',
      $this->table.'.updated_at',
      $this->table.'.comments'
    ];

    $this->joins = [
      'inner' => [
        "status ON {$this->table}.status_id = status.id",
        "users creator ON {$this->table}.created_by = creator.id",
        "users updator ON {$this->table}.updated_by = updator.id"
      ]
    ];

    // form to add / edit records
    $this->form['resource'] = [
      'name' => [
        'label'=>'Name',
        'placeholder' => 'The group\'s name',
        'type' => 'text',
        'validate' => [
          'rules' => [
            'required' => [
              'msg' => 'The name is required'
            ],
            'notin' => [
              'values' => '{registered_group_names}',
              'msg' => 'The name is already in use'
            ],
          ]
        ]
      ],
      'comments' => [
        'label'=>'Comments (optional)',
        'placeholder' => 'The group\'s comments',
        'type' => 'textarea',
        'validate' => [
          'rules' => []
        ]
      ],
      'submit' => [
        'new' => ['label' => 'Add Group'],
        'edit' => ['label' => 'Save Group'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to edit records
    $this->form['edit'] = [
      'name' => [
        'label'=>'Name',
        'placeholder' => 'The group\'s name',
        'type' => 'text',
        'validate' => [
          'rules' => [
            'required' => [
              'msg' => 'The name is required'
            ],
            'notin' => [
              'values' => '{registered_group_names}',
              'msg' => 'The name is already in use'
            ],
          ]
        ]
      ],
      'comments' => [
        'label'=>'Comments (optional)',
        'placeholder' => 'The group\'s comments',
        'type' => 'textarea',
        'validate' => [
          'rules' => []
        ]
      ],
      'submit' => [
        'edit' => ['label' => 'Save Group'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to delete records
    $this->form['delete'] = [
      'submit' => [
        'delete' => ['label' => 'Delete Group'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to enable records
    $this->form['enable'] = [
      'submit' => [
        'enable' => ['label' => 'Enable Group'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to disable records
    $this->form['disable'] = [
      'submit' => [
        'disable' => ['label' => 'Disable Group'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to send sms
    $this->form['sms'] = [
      'submit' => [
        'sms' => ['label' => 'Send SMS'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to send email
    $this->form['email'] = [
      'submit' => [
        'email' => ['label' => 'Send Email'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // links and buttons, with their respective permission
    $this->links = [
      'header' => [
        [
          'name' => 'new',
          'label' => 'Add New Group',
          'href' => $this->base_url_path.'/new',
          'permissions' => ['Can add groups']
        ],
        [
          'name' => 'export',
          'label' => 'Export All Groups',
          'href' => $this->base_url_path.'/export',
          'permissions' => ['Can export groups']
        ]
      ],
      'listing' => [
        [
          'name' => 'view',
          'label' => 'View',
          'icon' => 'view',
          'href' => $this->base_url_path.'/{id}/view',
          'permissions' => ['Can view groups']
        ],
        [
          'name' => 'edit',
          'label' => 'Edit',
          'icon' => 'edit',
          'href' => $this->base_url_path.'/{id}/edit',
          'permissions' => ['Can edit groups']
        ],
        [
          'name' => 'delete',
          'label' => 'Delete',
          'icon' => 'delete',
          'href' => $this->base_url_path.'/{id}/delete',
          'permissions' => ['Can delete groups']
        ],
        [
          'name' => 'enable',
          'label' => 'Enable',
          'icon' => 'enable',
          'href' => $this->base_url_path.'/{id}/enable',
          'permissions' => ['Can enable groups']
        ],
        [
          'name' => 'disable',
          'label' => 'Disable',
          'icon' => 'disable',
          'href' => $this->base_url_path.'/{id}/disable',
          'permissions' => ['Can disable groups']
        ]
      ],
      'detail' => [
        'detail-buttons-left' => [
          [
            'name' => 'edit',
            'label' => 'Edit',
            'icon' => 'edit',
            'href' => $this->base_url_path.'/{id}/edit',
            'permissions' => ['Can edit groups']
          ],
          [
            'name' => 'delete',
            'label' => 'Delete',
            'icon' => 'delete',
            'href' => $this->base_url_path.'/{id}/delete',
            'permissions' => ['Can delete groups']
          ],
          [
            'name' => 'enable',
            'label' => 'Enable',
            'icon' => 'enable',
            'href' => $this->base_url_path.'/{id}/enable',
            'permissions' => ['Can enable groups']
          ],
          [
            'name' => 'disable',
            'label' => 'Disable',
            'icon' => 'disable',
            'href' => $this->base_url_path.'/{id}/disable',
            'permissions' => ['Can disable groups']
          ]
        ],
        'detail-buttons-right' => [
          [
            'name' => 'sms',
            'label' => 'Send SMS',
            'icon' => 'send-sms',
            'href' => $this->base_url_path.'/{id}/send-sms',
            'permissions' => ['Can message a contact']
          ],
          [
            'name' => 'email',
            'label' => 'Send Email',
            'icon' => 'send-email',
            'href' => $this->base_url_path.'/{id}/send-email',
            'permissions' => ['Can message a contact']
          ],
          [
            'name' => 'roles',
            'label' => 'Manage Contacts',
            'icon' => 'manage-contacts',
            'href' => $this->base_url_path.'/{id}/contacts',
            'permissions' => ['Can manage group contacts']
          ],
          [
            'name' => 'export',
            'label' => 'Export Contacts',
            'icon' => 'export-contacts',
            'href' => $this->base_url_path.'/{id}/export-contacts',
            'permissions' => ['Can export groups']
          ],
          [
            'name' => 'upload',
            'label' => 'Upload Bulk Contacts',
            'href' => $this->base_url_path.'/{id}/upload',
            'permissions' => ['Can bulk upload contacts']
          ]
        ]
      ]
    ];

  }

  public function fetchGroup($vars, $options = []) {
    return $this->fetchOne($vars, $options);
  }

  public function fetchGroups($vars = [], $options = []) {
    return $this->fetchMultiple($vars, $options);
  }

  public function newGroup($name, $comments, $created_by = 0) {
    return $this->insert(
      [
        'name' => $name,
        'comments' => $comments,
        'status_id' => Status::ACTIVE_STATUS,
        'created_by' => $created_by,
        'updated_by' => $created_by
      ]
    );
  }

  public function updateGroup($values, $where, $options = []) {
    return $this->update($values, $where, $options);
  }

  public function deleteGroup($where, $options = []) {
    return $this->delete($where, $options);
  }

  public function disableGroup($where, $options = []) {
    return $this->disable($where, $options);
  }

  public function enableGroup($where, $options = []) {
    return $this->enable($where, $options);
  }

  public function deleteGroupContacts($group_id) {
    $sql = "DELETE FROM group_role_mapping WHERE group_id = :group_id";
    return $this->db->query($sql, [
      'group_id' => $group_id
    ]);
  }

  public function loadGroupContacts($group_id) {
    $sql = "SELECT DISTINCT id, surname, other_names FROM contacts WHERE id IN (SELECT contact_id FROM contact_group_mapping WHERE group_id = :group_id)";
    return $this->db->query($sql, ['group_id' => $group_id]);
  }

  public function saveGroupContacts($group_id, $user_id, $contacts = [], $new_contacts = []) {

    // delete removed contacts
    if(!empty($contacts)) {
      $adms = implode(', ', $contacts);
      $delete_sql = "DELETE FROM contact_group_mapping WHERE group_id = :group_id AND contact_id NOT IN ({$adms})";
      $this->db->query($delete_sql, ['group_id' => $group_id]);
    }

    // save new groups
    if(!empty($new_contacts)) {
      $values = [];
      foreach($new_contacts as $a) {
        $values[] = "({$group_id}, {$a}, {$user_id})";
      }
      $values_sql = implode(', ', $values);

      $insert_sql = "INSERT INTO contact_group_mapping (`group_id`,`contact_id`,`created_by`) VALUES {$values_sql}";
      return $this->db->query($insert_sql);
    }
    return false;
  }

  public function getRegisteredGroupnames() {
    $groups = $this->fetchGroups([], ['perPage'=>'all']);
    $names = [];
    foreach($groups->rows as $g) {
      $names[] = $g->name;
    }
    return $names;
  }

}
