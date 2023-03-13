<form action="" method="POST">
  <div class="form-group">
    <label for="email">Email Address:</label>
    <input type="text" name="email" id="email" placeholder="Your email address" value="<?php echo $vars->post->auth->email ?? null; ?>">
  </div>
  <div class="form-group">
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" placeholder="Your password">
  </div>
  <div class="form-footer">
    <button type="submit" name="submit" id="submit" value="login">Login</button>
    <span>
      <a href="/register">Register</a>
      <a href="/forgot">Forgot Password</a>
    </span>
  </div>
</form>
