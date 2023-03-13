<?php

namespace SMVC\Model;

use SMVC\Core\Model;
use SMVC\Model\Status;

class Contact extends Model {

  public function __construct($app) {
    parent::__construct($app);

    $this->module = 'Contact';
    $this->table = 'contacts';
    $this->base_url_path = 'contacts';

    // table columns
    $this->columns = [
      $this->table.'.id',
      $this->table.'.bulk_upload_id',
      $this->table.'.surname',
      $this->table.'.other_names',
      $this->table.'.email',
      $this->table.'.phone',
      $this->table.'.address',
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
      'surname' => ['table'=>$this->table, 'label'=>'Surname'],
      'other_names' => ['table'=>$this->table, 'label'=>'Other Names'],
      'email' => ['table'=>$this->table, 'label'=>'Email'],
      'phone' => ['table'=>$this->table, 'label'=>'Phone Number'],
      'address' => ['table'=>$this->table, 'label'=>'Address'],
      'status' => ['table'=>'status', 'label'=>'Status'],
      'created_by' => ['table'=>'creator', 'label'=>'Created By'],
      'created_at' => ['table'=>$this->table, 'label'=>'Created At'],
      'updated_by' => ['table'=>'updator', 'label'=>'Updated By'],
      'updated_at' => ['table'=>$this->table, 'label'=>'Updated At']
    ];

    // columns to display on UI
    $this->detail = [
      'id' => ['table'=>$this->table, 'label'=>'ID'],
      'surname' => ['table'=>$this->table, 'label'=>'Surname'],
      'other_names' => ['table'=>$this->table, 'label'=>'Other Names'],
      'email' => ['table'=>$this->table, 'label'=>'Email'],
      'phone' => ['table'=>$this->table, 'label'=>'Phone Number'],
      'address' => ['table'=>$this->table, 'label'=>'Address'],
      'status' => ['table'=>'status', 'label'=>'Status'],
      'created_by' => ['table'=>'creator', 'label'=>'Created By'],
      'created_at' => ['table'=>$this->table, 'label'=>'Created At'],
      'updated_by' => ['table'=>'updator', 'label'=>'Updated By'],
      'updated_at' => ['table'=>$this->table, 'label'=>'Updated At'],
      'comments' => ['table'=>$this->table, 'label'=>'Comments']
    ];

    // columns to search when filtering from UI
    $this->columnsToSearch = [
      $this->table.'.id',
      $this->table.'.surname',
      $this->table.'.other_names',
      $this->table.'.email',
      $this->table.'.phone',
      $this->table.'.address',
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
      ],
      'left' => [
        "bulk_uploads ON {$this->table}.bulk_upload_id = bulk_uploads.id"
      ]
    ];

    // form to add / edit records
    $this->form['resource'] = [
      'surname' => [
        'label'=>'Surname',
        'placeholder' => 'The contact\'s surname',
        'type' => 'text',
        'validate' => [
          'rules' => [
            'required' => [
              'msg' => 'The surname is required'
            ],
          ]
        ]
      ],
      'other_names' => [
        'label'=>'Other Names',
        'placeholder' => 'The contact\'s other names',
        'type' => 'text',
        'validate' => [
          'rules' => [
            'required' => [
              'msg' => 'Other names are required'
            ],
          ]
        ]
      ],
      'email' => [
        'label'=>'Email Address (optional)',
        'placeholder' => 'The contact\'s email address',
        'type' => 'email',
        'validate' => [
          'rules' => [
            'email' => [
              'msg' => 'The email address is invalid'
            ],
          ]
        ]
      ],
      'phone' => [
        'label'=>'Phone Number (optional)',
        'placeholder' => 'The contact\'s phone number',
        'type' => 'text',
        'validate' => [
          'rules' => []
        ]
      ],
      'address' => [
        'label'=>'Address (optional)',
        'placeholder' => 'The contact\'s address',
        'type' => 'text',
        'validate' => [
          'rules' => []
        ]
      ],
      'comments' => [
        'label'=>'Comments (optional)',
        'placeholder' => 'The contact\'s comments',
        'type' => 'textarea',
        'validate' => [
          'rules' => []
        ]
      ],
      'submit' => [
        'new' => ['label' => 'Add Contact'],
        'edit' => ['label' => 'Save Contact'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to add / edit records
    $this->form['edit'] = [
      'surname' => [
        'label'=>'Surname',
        'placeholder' => 'The contact\'s surname',
        'type' => 'text',
        'validate' => [
          'rules' => [
            'required' => [
              'msg' => 'The surname is required'
            ],
          ]
        ]
      ],
      'other_names' => [
        'label'=>'Other Names',
        'placeholder' => 'The contact\'s other names',
        'type' => 'text',
        'validate' => [
          'rules' => [
            'required' => [
              'msg' => 'Other names are required'
            ],
          ]
        ]
      ],
      'email' => [
        'label'=>'Email Address (optional)',
        'placeholder' => 'The contact\'s email address',
        'type' => 'email',
        'validate' => [
          'rules' => [
            'email' => [
              'msg' => 'The email address is invalid'
            ],
          ]
        ]
      ],
      'phone' => [
        'label'=>'Phone Number (optional)',
        'placeholder' => 'The contact\'s phone number',
        'type' => 'text',
        'validate' => [
          'rules' => []
        ]
      ],
      'address' => [
        'label'=>'Address (optional)',
        'placeholder' => 'The contact\'s address',
        'type' => 'text',
        'validate' => [
          'rules' => []
        ]
      ],
      'comments' => [
        'label'=>'Comments (optional)',
        'placeholder' => 'The contact\'s comments',
        'type' => 'textarea',
        'validate' => [
          'rules' => []
        ]
      ],
      'submit' => [
        'edit' => ['label' => 'Save Contact'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to delete records
    $this->form['delete'] = [
      'submit' => [
        'delete' => ['label' => 'Delete Contact'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to enable records
    $this->form['enable'] = [
      'submit' => [
        'enable' => ['label' => 'Enable Contact'],
        'cancel' => ['label' => 'Cancel']
      ]
    ];

    // form to disable records
    $this->form['disable'] = [
      'submit' => [
        'disable' => ['label' => 'Disable Contact'],
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
          'label' => 'Add New Contact',
          'href' => $this->base_url_path.'/new',
          'permissions' => ['Can add contacts']
        ],
        [
          'name' => 'upload',
          'label' => 'Upload Bulk Contacts',
          'href' => $this->base_url_path.'/upload',
          'permissions' => ['Can bulk upload contacts']
        ],
        [
          'name' => 'export',
          'label' => 'Export All Contacts',
          'href' => $this->base_url_path.'/export',
          'permissions' => ['Can export contacts']
        ]
      ],
      'listing' => [
        [
          'name' => 'view',
          'label' => 'View',
          'icon' => 'view',
          'href' => $this->base_url_path.'/{id}/view',
          'permissions' => ['Can view contacts']
        ],
        [
          'name' => 'edit',
          'label' => 'Edit',
          'icon' => 'edit',
          'href' => $this->base_url_path.'/{id}/edit',
          'permissions' => ['Can edit contacts']
        ],
        [
          'name' => 'delete',
          'label' => 'Delete',
          'icon' => 'delete',
          'href' => $this->base_url_path.'/{id}/delete',
          'permissions' => ['Can delete contacts']
        ],
        [
          'name' => 'enable',
          'label' => 'Enable',
          'icon' => 'enable',
          'href' => $this->base_url_path.'/{id}/enable',
          'permissions' => ['Can enable contacts']
        ],
        [
          'name' => 'disable',
          'label' => 'Disable',
          'icon' => 'disable',
          'href' => $this->base_url_path.'/{id}/disable',
          'permissions' => ['Can disable contacts']
        ]
      ],
      'detail' => [
        'detail-buttons-left' => [
          [
            'name' => 'edit',
            'label' => 'Edit',
            'icon' => 'edit',
            'href' => $this->base_url_path.'/{id}/edit',
            'permissions' => ['Can edit contacts']
          ],
          [
            'name' => 'delete',
            'label' => 'Delete',
            'icon' => 'delete',
            'href' => $this->base_url_path.'/{id}/delete',
            'permissions' => ['Can delete contacts']
          ],
          [
            'name' => 'enable',
            'label' => 'Enable',
            'icon' => 'enable',
            'href' => $this->base_url_path.'/{id}/enable',
            'permissions' => ['Can enable contacts']
          ],
          [
            'name' => 'disable',
            'label' => 'Disable',
            'icon' => 'disable',
            'href' => $this->base_url_path.'/{id}/disable',
            'permissions' => ['Can disable contacts']
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
            'name' => 'groups',
            'label' => 'Manage Groups',
            'icon' => 'manage-groups',
            'href' => $this->base_url_path.'/{id}/groups',
            'permissions' => ['Can manage contact groups']
          ],
        ]
      ]
    ];

  }

  public function fetchContact($vars, $options = []) {
    return $this->fetchOne($vars, $options);
  }

  public function fetchContacts($vars = [], $options = []) {
    return $this->fetchMultiple($vars, $options);
  }

  public function newContact($surname, $other_names, $email, $phone, $address, $comments, $created_by = 0) {
    return $this->insert([
      'surname' => $surname,
      'other_names' => $other_names,
      'email' => $email,
      'phone' => $phone,
      'address' => $address,
      'comments' => $comments,
      'status_id' => Status::ACTIVE_STATUS,
      'created_by' => $created_by,
      'updated_by' => $created_by
    ]);
  }

  public function bulkInsert($records, $bulk_upload_id, $created_by = 0) {

    // columns - first element
    $columns = array_shift($records);
    $columns[] = 'bulk_upload_id';
    $columns[] = 'status_id';
    $columns[] = 'created_by';
    $columns[] = 'updated_by';
    $columns_sql = "(`".implode('`, `', array_map("trim", $columns))."`)";

    // values
    $values = [];
    foreach($records as $k => $r) {
      $records[$k][] = $bulk_upload_id;
      $records[$k][] = Status::ACTIVE_STATUS;
      $records[$k][] = $created_by;
      $records[$k][] = $created_by;
      $values[] = "('".implode("', '", array_map("trim", $records[$k]))."')";
    }
    $values_sql = implode(', ', $values);

    $insert_sql = "INSERT INTO {$this->table} {$columns_sql} VALUES {$values_sql}";
    return $this->db->query($insert_sql);
  }

  public function updateContact($values, $where, $options = []) {
    return $this->update($values, $where, $options);
  }

  public function deleteContact($where, $options = []) {
    return $this->delete($where, $options);
  }

  public function disableContact($where, $options = []) {
    return $this->disable($where, $options);
  }

  public function enableContact($where, $options = []) {
    return $this->enable($where, $options);
  }

  public function deleteContactGroups($contact_id) {
    $sql = "DELETE FROM contact_group_mapping WHERE contact_id = :contact_id";
    return $this->db->query($sql, ['contact_id' => $contact_id]);
  }

  public function loadContactGroups($contact_id) {
    $sql = "SELECT DISTINCT id, name FROM contact_groups WHERE id IN (SELECT group_id FROM contact_group_mapping WHERE contact_id = :contact_id)";
    return $this->db->query($sql, ['contact_id' => $contact_id]);
  }

  public function saveContactGroups($contact_id, $user_id, $groups = [], $new_groups = []) {

    if(empty($groups)) {
      return;
    }

    // delete removed groups
    $adms = implode(', ', $groups);
    $delete_sql = "DELETE FROM contact_group_mapping WHERE contact_id = :contact_id AND group_id NOT IN ({$adms})";
    $this->db->query($delete_sql, ['contact_id' => $contact_id]);

    // save new groups
    if(!empty($new_groups)) {
      $values = [];
      foreach($new_groups as $a) {
        $values[] = "({$contact_id}, {$a}, {$user_id})";
      }
      $values_sql = implode(', ', $values);

      $insert_sql = "INSERT INTO contact_group_mapping (`contact_id`,`group_id`,`created_by`) VALUES {$values_sql}";
      return $this->db->query($insert_sql);
    }
  }

}
