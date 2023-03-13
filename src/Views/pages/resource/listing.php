<?php

$columns = array_values($vars->columns);
$values = array_keys($vars->columns);

?><div class="content-page">

    <?php if(isset($vars->filter_form) && !empty($vars->filter_form)): ?>
    <div class="topbar records-topbar">
      <div class="topbar-filter">
        <?php echo $vars->filter_form; ?>
      </div>
    </div>
    <?php endif; ?>

  <?php if(empty($vars->records)): ?>

    <div class="empty-listing">
      <p>No records found</p>
    </div>

  <?php else: ?>

    <div class="main-section main-section-npt resource-listing <?php echo $vars->resource_name; ?>-listing">
      <table>
        <thead>
          <tr>
            <?php foreach($columns as $column): ?>
            <th><?php echo $column['label']; ?></th>
            <?php endforeach; ?>
            <?php if(isset($vars->links_listing)) { ?>
            <th>Actions</th>
            <?php } ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach($vars->records as $record): ?>
          <tr>

            <?php foreach($values as $value): ?>
            <?php
              $class = null;
              if($value == 'status') {
                $class = 'status status-'.mb_strtolower(str_replace(' ', '_', $record->status));
              }
            ?>
            <td><span class="<?php echo $class; ?>"><?php echo $record->{$value}; ?></span></td>
            <?php endforeach; ?>

            <?php if(isset($vars->links_listing)) { ?>
            <td class="resource-actions"><div><?php echo $vars->links_listing[$record->id]; ?></div></td>
            <?php } ?>

            <?php if(isset($vars->links_revision) && !empty($vars->links_revision)) { ?>
            <td class="resource-actions"><div><?php echo isset($vars->links_revision[$record->id]) ? $vars->links_revision[$record->id] : null ; ?></div></td>
            <?php } ?>

          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php echo $vars->pager; ?>
    </div>

  <?php endif; ?>
</div>
