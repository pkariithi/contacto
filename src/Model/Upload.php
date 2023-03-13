<?php

namespace SMVC\Model;

use SMVC\Core\Model;
use SMVC\Model\Status;

class Upload extends Model {

  public function __construct($app) {
    parent::__construct($app);

    $this->module = 'Upload';
    $this->table = 'bulk_uploads';
    $this->base_url_path = 'uploads';

    // table columns
    $this->columns = [
      $this->table.'.id',
      'contact_groups.id AS group_id',
      'contact_groups.name AS group_name',
      $this->table.'.original_name',
      $this->table.'.new_name',
      $this->table.'.extension',
      $this->table.'.size',
      $this->table.'.mime',
      'status.status',
      $this->table.'.comments',
      'creator.username AS created_by',
      $this->table.'.created_at'
    ];

    // columns to display on UI
    $this->listing = [
      'id' => ['table'=>$this->table, 'label'=>'ID'],
      'group_name' => ['table'=>'contact_groups', 'label'=>'Group'],
      'original_name' => ['table'=>$this->table, 'label'=>'File Name'],
      'size' => ['table'=>$this->table, 'label'=>'File Size'],
      'status' => ['table'=>'status', 'label'=>'Status'],
      'created_by' => ['table'=>'creator', 'label'=>'Created By'],
      'created_at' => ['table'=>$this->table, 'label'=>'Created At']
    ];

    // columns to display on UI
    $this->detail = [
      'id' => ['table'=>$this->table, 'label'=>'ID'],
      'group_name' => ['table'=>'contact_groups', 'label'=>'Group'],
      'original_name' => ['table'=>$this->table, 'label'=>'File Name'],
      'size' => ['table'=>$this->table, 'label'=>'File Size'],
      'mime' => ['table'=>$this->table, 'label'=>'File Mime'],
      'status' => ['table'=>'status', 'label'=>'Status'],
      'created_by' => ['table'=>'creator', 'label'=>'Created By'],
      'created_at' => ['table'=>$this->table, 'label'=>'Created At'],
      'comments' => ['table'=>$this->table, 'label'=>'Comments']
    ];

    // columns to search when filtering from UI
    $this->columnsToSearch = [
      $this->table.'.id',
      'contact_groups.name',
      $this->table.'.original_name',
      $this->table.'.size',
      $this->table.'.mime',
      'status.status',
      'creator.username',
      $this->table.'.created_at',
      $this->table.'.comments'
    ];

    $this->joins = [
      'inner' => [
        "status ON {$this->table}.status_id = status.id",
        "users creator ON {$this->table}.created_by = creator.id"
      ],
      "left" => [
        "contact_groups ON {$this->table}.contact_group_id = contact_groups.id"
      ]
    ];

    // links and buttons, with their respective permission
    $this->links = [
      'header' => [
        [
          'name' => 'upload',
          'label' => 'Upload Bulk Contacts',
          'href' => $this->base_url_path.'/new',
          'permissions' => ['Can bulk upload contacts']
        ],
        [
          'name' => 'export',
          'label' => 'Export Bulk Contacts',
          'href' => $this->base_url_path.'/export',
          'permissions' => ['Can export uploads']
        ]
      ],
      'listing' => [
        [
          'name' => 'view',
          'label' => 'View',
          'icon' => 'view',
          'href' => $this->base_url_path.'/{id}/view',
          'permissions' => ['Can view uploads']
        ],
        [
          'name' => 'download',
          'label' => 'Download',
          'icon' => 'download',
          'href' => $this->base_url_path.'/{id}/download',
          'permissions' => ['Can download uploaded files']
        ]
      ],
      'detail' => [
        'detail-buttons-left' => [
          [
            'name' => 'download',
            'label' => 'Download',
            'icon' => 'download',
            'href' => $this->base_url_path.'/{id}/download',
            'permissions' => ['Can download uploaded files']
          ]
        ],
        'detail-buttons-right' => []
      ]
    ];

  }

  public function fetchUpload($vars, $options = []) {
    return $this->fetchOne($vars, $options);
  }

  public function fetchUploads($vars = [], $options = []) {
    return $this->fetchMultiple($vars, $options);
  }

  public function newUpload($file, $comments, $group_id = null, $created_by = 0) {
    return $this->insert(
      [
        'contact_group_id' => $group_id,
        'original_name' => $file['name'],
        'new_name' => $file['new_file_name'],
        'extension' => $file['extension'],
        'size' => $file['size'],
        'mime' => $file['type'],
        'status_id' => Status::ACTIVE_STATUS,
        'comments' => $comments,
        'created_by' => $created_by
      ]
    );
  }

  public function loadUploadContacts($upload_id) {
    $sql = "SELECT DISTINCT id, surname, other_names FROM contacts WHERE id IN (SELECT contact_id FROM contact_upload_mapping WHERE upload_id = :upload_id)";
    return $this->db->query($sql, ['upload_id' => $upload_id]);
  }

  public function saveUploadContacts($upload_id, $user_id, $contacts = [], $new_contacts = []) {

    if(empty($contacts)) {
      return;
    }

    // delete removed contacts
    $adms = implode(', ', $contacts);
    $delete_sql = "DELETE FROM contact_upload_mapping WHERE upload_id = :upload_id AND contact_id NOT IN ({$adms})";
    $this->db->query($delete_sql, ['upload_id' => $upload_id]);

    // save new uploads
    if(!empty($new_contacts)) {
      $values = [];
      foreach($new_contacts as $a) {
        $values[] = "({$upload_id}, {$a}, {$user_id})";
      }
      $values_sql = implode(', ', $values);

      $insert_sql = "INSERT INTO contact_upload_mapping (`upload_id`,`contact_id`,`created_by`) VALUES {$values_sql}";
      return $this->db->query($insert_sql);
    }
  }

}
