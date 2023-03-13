<div class="content-page main-section">
  <div class="sample-uploads">
    <div class="sample-uploads-title">
      <h3>Sample Bulk Files</h3>
      <p>Download your preferred sample file below. Edit it, then upload it using the form below.</p>
      <p><strong>NOTE:</strong> The following columns are optional: phone, email, address, comments</p>
    </div>
    <div class="sample-uploads-files">
    <?php foreach($vars->sample_uploads AS $su): ?>
      <a href="<?php echo $su->link; ?>">
        <img src="<?php echo $su->image; ?>" alt="<?php echo $su->alt; ?>" />
        <p><?php echo $su->type; ?></p>
      </a>
    <?php endforeach; ?>
    </div>
  </div>
  <div class="resource-form <?php echo $vars->resource_name; ?>-form resource-upload <?php echo $vars->resource_name; ?>-upload">
    <?php echo $vars->form; ?>
  </div>
</div>
