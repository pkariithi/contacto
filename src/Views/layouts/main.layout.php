<!DOCTYPE html>
<html lang="<?php echo $vars->config->app->lang; ?>">
<head>
  <base href="<?php echo $vars->base_url; ?>">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $vars->pagetitle; ?></title>
  <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
  <link rel="manifest" href="assets/favicon/site.webmanifest">
  <link rel="mask-icon" href="assets/favicon/safari-pinned-tab.svg" color="#5bbad5">
  <link rel="shortcut icon" href="assets/favicon/favicon.ico">
  <meta name="msapplication-TileColor" content="#da532c">
  <meta name="msapplication-config" content="assets/favicon/browserconfig.xml">
  <meta name="theme-color" content="#ffffff">
  <?php /*<link rel="stylesheet" type="text/css" href="assets/vendor/datepicker/datepicker.min.css">
  <link rel="stylesheet" type="text/css" href="assets/vendor/timepicker/timepicker.min.css"> */ ?>
  <link rel="stylesheet" type="text/css" href="assets/css/style.css?v=<?php echo $vars->asset_version; ?>">
</head>
<body>
  <?php echo $content; ?>
  <script src="assets/js/jquery-3.6.0.min.js"></script>
  <?php /* <script src="assets/vendor/datepicker/datepicker.min.js"></script>
  <script src="assets/vendor/timepicker/timepicker.min.js"></script>
  <script src="assets/vendor/chartjs/chart.min.js"></script>
  <script src="assets/vendor/html2canvas/html2canvas.min.js"></script>
  <script src="assets/vendor/jspdf/jspdf.umd.min.js"></script> */ ?>
  <script src="assets/js/script.js?v=<?php echo $vars->asset_version; ?>"></script>
</body>
</html>
