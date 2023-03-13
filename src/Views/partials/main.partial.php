<?php

$menu = [
  [
    'label' => 'Dashboard',
    'href' => 'dashboard',
    'icon' => $vars->icons->dashboard,
    'active' => 'dashboard',
    'is_enabled' => true,
    'permissions' => []
  ],
  [
    'label' => 'Contacts',
    'href' => '#',
    'icon' => $vars->icons->contacts,
    'permissions' => [
      'Can view contacts',
      'Can view groups',
      'Can view uploads',
      'Can view sent messages'
    ],
    'items' => [
      [
        'label' => 'Contacts',
        'href' => 'contacts',
        'active' => 'contacts',
        'is_enabled' => true,
        'permissions' => [
          'Can view contacts'
        ]
      ],
      [
        'label' => 'Groups',
        'href' => 'groups',
        'active' => 'groups',
        'is_enabled' => true,
        'permissions' => [
          'Can view groups'
        ]
      ],
      [
        'label' => 'Bulk Uploads',
        'href' => 'uploads',
        'active' => 'uploads',
        'is_enabled' => true,
        'permissions' => [
          'Can view uploads'
        ]
      ],
      [
        'label' => 'Messaging',
        'href' => 'messaging',
        'active' => 'messaging',
        'is_enabled' => true,
        'permissions' => [
          'Can view sent messages'
        ]
      ],
    ]
  ],
  [
    'label' => 'Manage',
    'href' => '#',
    'icon' => $vars->icons->manage,
    'permissions' => [
      'Can view roles',
      'Can view permissions',
      'Can view users',
      'Can view sessions'
    ],
    'items' => [
      [
        'label' => 'Users',
        'href' => 'users',
        'active' => 'users',
        'is_enabled' => true,
        'permissions' => [
          'Can view users'
        ]
      ],
      [
        'label' => 'Roles',
        'href' => 'roles',
        'active' => 'roles',
        'is_enabled' => true,
        'permissions' => [
          'Can view roles'
        ]
      ],
      [
        'label' => 'Permissions',
        'href' => 'permissions',
        'active' => 'permissions',
        'is_enabled' => true,
        'permissions' => [
          'Can view permissions'
        ]
      ],
      [
        'label' => 'Sessions',
        'href' => 'sessions',
        'active' => 'sessions',
        'is_enabled' => true,
        'permissions' => [
          'Can view sessions'
        ]
      ],
    ]
  ],
];

$menu_html = '';
foreach($menu as $m):
  if(isset($m['items'])) {

    $dd_actives = [];
    foreach($m['items'] as $i) {
      $dd_actives[] = $i['active'];
    }

    $active_class = $dd_active = $dd_icon = null;
    if(in_array($vars->active, $dd_actives)) {
      $active_class = ' class="active"';
      $dd_active = ' class="dropdown-active"';
      $dd_icon = ' dropdown-open';
    }

    if(!empty(array_intersect($m['permissions'], $this->vars->user_permissions))):
      $menu_html .= '<li>';
      $menu_html .= '<a href="'.$m['href'].'"'.$active_class.' title="'.$m['label'].'">';
      $menu_html .= '<span class="label">'.$m['icon'].'<span>'.$m['label'].'</span></span>';
      $menu_html .= '<span class="dropdown'.$dd_icon.'">'.$vars->icons->dropdown.'</span>';
      $menu_html .= '</a><ul'.$dd_active.'>';

      foreach($m['items'] as $j):
        if($j['is_enabled'] && (empty($j['permissions']) || !empty(array_intersect($j['permissions'], $this->vars->user_permissions)))):
          $active_class = $vars->active == $j['active'] ? ' class="active"' : null;
          $menu_html .= '<li><a href="'.$j['href'].'"'.$active_class.'>';
          $menu_html .= '<span>'.$j['label'].'</span></a></li>';
        endif;
      endforeach;

      $menu_html .= '</ul></li>';
    endif;
  } else {
    if($m['is_enabled'] && (empty($m['permissions']) || !empty(array_intersect($m['permissions'], $this->vars->user_permissions)))):
      $active_class = $vars->active == $m['active'] ? ' class="active"' : null;
      $menu_html .= '<li><a href="'.$m['href'].'"'.$active_class.' title="'.$m['label'].'">';
      $menu_html .= '<span class="label">'.$m['icon'].'<span>'.$m['label'].'</span></span></a></li>';
    endif;
  }
endforeach;

?><div class="wrapper wrapper-dashboard">
  <div class="aside" id="sidebar">
    <div class="aside-brand">
      <h1><?php echo $vars->config->app->company; ?></h1>
      <img src="assets/images/full-logo.png" alt="Contacto Logo">
    </div>
    <div id="aside_menu" class="aside-menu">
      <ul><?php echo $menu_html; ?></ul>
    </div>
  </div>

  <div class="main" id="mainbar">
    <div class="navbar">
      <div class="navbar-left">
        <div class="main-header">
          <h2 class="app-name"><?php echo $vars->config->app->name; ?></h2>
        </div>
      </div>
      <div class="navbar-right">
        <div class="profile-section">
          <a href="#" id="profile_avatar">
            <span class="initials"><?php echo $vars->profile->initials; ?></span>
          </a>
          <div class="profile-dropdown" id="profile_dropdown">
            <h3><?php echo $vars->profile->username; ?></h3>
            <p><?php echo $vars->profile->email; ?></p>
            <ul>
              <li><a href="profile"><?php echo $vars->icons->profile; ?> Profile</a></li>
              <li><a href="logout"><?php echo $vars->icons->logout; ?> Logout</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="content">
      <div class="content-header">
        <div>
          <h2><?php echo $vars->pageheader; ?></h2>
          <?php echo $vars->breadcrumbs; ?>
        </div>

        <?php if(isset($vars->links_header) && !empty($vars->links_header)): ?>
        <div>
          <?php echo $vars->links_header; ?>
        </div>
        <?php endif; ?>
      </div>
      <?php echo empty($vars->flash) ? null : $vars->flash; ?>
      <?php echo $page; ?>
    </div>
  </div>
</div>
