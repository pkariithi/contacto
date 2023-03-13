<?php

$columns = array_keys($vars->columns);

?><div class="content-page main-section">
  <h3 class="detail-title">Are you sure you want to permanently delete this <?php echo $vars->resource_type; ?>?</h3>
  <div class="detail">
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
          <?php $class = $c == 'enabled' ? ' class="enabled enabled-'.mb_strtolower($vars->resource->enabled).'"' : null; ?>
          <tr>
            <td><?php echo $vars->columns[$c]['label']; ?></td>
            <td><span<?php echo $class; ?>><?php echo $vars->resource->{$c}; ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="resource-form <?php echo $vars->resource_name; ?>-form resource-delete <?php echo $vars->resource_name; ?>-delete">
    <?php echo $vars->form; ?>
  </div>
</div>
