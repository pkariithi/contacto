<?php

$columns = array_keys($vars->columns);

?><div class="content-page main-section">
  <div class="detail roles-detail">

    <?php if(isset($vars->links_detail) && !empty($vars->links_detail)) { ?>
    <div class="detail-buttons">
      <?php echo $vars->links_detail[$vars->resource->id]; ?>
    </div>
    <?php } ?>

    <div class="details-table">
      <table>
        <thead>
          <tr>
            <th>Column</th>
            <th>Value</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($columns as $c): ?>
          <?php
            $class = null;
            if($c == 'enabled') {
              $class = 'enabled enabled-'.mb_strtolower($vars->resource->enabled);
            }
            if($c == 'status') {
              $class = 'status status-'.mb_strtolower(str_replace(' ', '_', $vars->resource->status));
            }
            if($c == 'overwrite_value') {
              $class = 'config-overwrite config-detail-overwrite';
              if($vars->resource->overwrite_value != 'Default Value') {
                $class .= ' config-overwritten';
              }
            }
            if($c == 'default_value') {
              $class = 'config-default config-detail-default';
            }
          ?>
          <tr>
            <td><strong><?php echo $vars->columns[$c]['label']; ?></strong></td>
            <td><span class="<?php echo $class; ?>"><?php echo $vars->resource->{$c}; ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if(isset($vars->module) && $vars->module == 'User'): ?>
    <div class="user-roles-detail">
      <h3>Roles:</h3>
      <?php if(count($vars->resource->roles) !== 0): ?>
      <table>
        <thead>
          <tr>
            <th>Id</th>
            <th>Role</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($vars->resource->roles as $role): ?>
          <tr>
            <td><?php echo $role->id; ?></td>
            <td><?php echo $role->role; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <p>User has not been mapped to any role</p>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </div>
</div>
