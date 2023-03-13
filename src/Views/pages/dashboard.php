<div class="content-page dashboard-content-page">
  <div>
    <?php
      $html = '';
      foreach($vars->dashboard_widgets as $groupname => $group):
        $html .= '<div class="dashboard-group dashboard-group-'.$groupname.'">';

        // default
        if(in_array($groupname, ['default'])) {
          $html .= '<div class="dashboard-group-content">';
          foreach($group->widgets as $name => $widget) {
            $html .= $widget;
          }
          $html .= '</div>';
        }

        $html .= '</div>';
      endforeach;
      echo $html;
    ?>
  </div>
</div>
