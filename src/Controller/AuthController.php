<?php

namespace SMVC\Controller;

use SMVC\Core\Database;
use SMVC\Core\Controller;

use SMVC\Model\Forgot;
use SMVC\Model\Status;

use SMVC\Helpers\Date;
use SMVC\Helpers\Text;
use SMVC\Helpers\Validate;

class AuthController extends Controller {

  public function __construct($app) {
    parent::__construct($app);
    $this->resource_name = 'auth';
    $this->view->setPartial('auth');
  }

  public function login() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // if user is already logged in, redirect to dashboard
    if($this->is_logged_in) {
      $this->app->flash->set("You are already logged in", 'info', '/dashboard');
    }

    // authentication page variables
    $auth = (object) [
      'title' => 'Login',
      'subtitle' => 'Please login with your email and password'
    ];
    $this->view->setVar('auth', $auth);

    // return form view
    return $this->view->page('auth.login');
  }

  public function processLogin() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // remove any variable not in expected array, also trim vars
    $expected = ['email','password'];
    $post = $this->app->request->getPostParams();
    $post = $this->expectedOnly($expected, $post);

    // sanitize post
    $this->app->session->set('post', [
      $this->resource_name => (object) ['email' => $post['email']]
    ]);
    $post['email'] = filter_var(
      $post['email'],
      FILTER_SANITIZE_EMAIL,
      FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
    );

    // validate details
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Message'=>"Validating login details..."]);
    $validation_rules = [
      'email' => [
        'value' => $post['email'],
        'rules' => [
          'required' => [
            'msg' => 'The email is required'
          ],
          'email' => [
            'msg' => 'The email is invalid'
          ],
        ]
      ],
      'password' => [
        'value' => $post['password'],
        'rules' => [
          'required' => [
            'msg' => 'The password is required'
          ]
        ]
      ]
    ];
    $validate = Validate::run($validation_rules);

    // if valid data
    if($validate->success) {

      // check if email in database
      $user = $this->model->user->fetchUser(['email' => $post['email']]);
      if($user) {

        if(in_array($user->status, ['Active','Created'])) {

          if (password_verify($post['password'], $user->password)) {
            if(in_array($user->status, ['Created'])) {
              $this->model->user->updateUser(
                ['status_id' => Status::ACTIVE_STATUS],
                ['id' => $user->id]
              );
            }
            $this->deleteResourceSessionPostData();
            $this->generateSession($user);
          } else {
            $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Login failed: Invalid password for {$post['email']}"]);
            $this->app->flash->set(['Invalid Credentials','Please try again'], 'error', '/');
          }

        } else {
          $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Login failed - User account status is: {$user->status} for {$post['email']}"]);
          $this->app->flash->set(['Your account is locked','Kindly contact the administrator for assistance'], 'error', '/');
        }

      } else {
        // redirect back to login screen with no account notice
        $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"No account available for the email '{$post['email']}'. Login failed."]);
        $this->app->flash->set(['Invalid credentials. Please try again'], 'error', '/');
      }

    } else {
      // redirect back to login screen with validation errors
      $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"validation failed: ".json_encode($validate->errors)]);
      $this->app->flash->set($validate->errors, 'error', $this->app->route->route->url);
    }
  }

  public function register() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // if user is already logged in, redirect to dashboard
    if($this->is_logged_in) {
      $this->app->flash->set("You are already logged in", 'info', '/dashboard');
    }

    // authentication page variables
    $auth = (object) [
      'title' => 'Register',
      'subtitle' => 'Register with your email address'
    ];
    $this->view->setVar('auth', $auth);

    // return form view
    return $this->view->page('auth.register');
  }

  public function processRegister() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // remove any variable not in expected array, also trim vars
    $expected = ['username','email','password','confirmpassword'];
    $post = $this->app->request->getPostParams();
    $post = $this->expectedOnly($expected, $post);

    // sanitize post
    $this->app->session->set('post', ['auth' => (object) [
      'username' => $post['username'],
      'email' => $post['email']
    ]]);
    $post['email'] = filter_var(
      $post['email'],
      FILTER_SANITIZE_EMAIL,
      FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
    );

    // registered usernames and emails
    $registered = $this->model->user->getRegisteredUsernamesAndEmails();
    $username_field_rules = $this->model->user->form['resource']['username']['validate']['rules'];
    $username_field_rules['notin']['values'] = $registered['usernames'];
    $email_field_rules = $this->model->user->form['resource']['email']['validate']['rules'];
    $email_field_rules['notin']['values'] = $registered['emails'];

    // validate details
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Message'=>"Validating register details..."]);
    $validation_rules = [
      'username' => [
        'value' => $post['username'],
        'rules' => $username_field_rules,
      ],
      'email' => [
        'value' => $post['email'],
        'rules' => $email_field_rules,
      ],
      'password' => [
        'value' => $post['password'],
        'rules' => [
          'required' => [
            'msg' => 'The password is required'
          ],
          'minLen' => [
            'length' => 8,
            'msg' => 'The password should be 8 characters or more',
          ]
        ]
      ],
      'confirmpassword' => [
        'value' => $post['confirmpassword'],
        'rules' => [
          'required' => [
            'msg' => 'The confirm password is required'
          ],
          'similar' => [
            'to' => $post['password'],
            'msg' => 'The password and confirm password do not match'
          ]
        ]
      ]
    ];
    $validate = Validate::run($validation_rules);

    // if valid data
    if($validate->success) {

      // create new user
      $created_user = $this->model->user->newUser($post['username'], $post['email'], $post['password']);

      // log them in
      $this->deleteResourceSessionPostData();
      $this->generateSession($created_user);

    } else {
      // redirect back to login screen with validation errors
      $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"validation failed: ".json_encode($validate->errors)]);
      $this->app->flash->set($validate->errors, 'error', $this->app->route->route->url);
    }
  }

  public function forgot() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // if user is already logged in, redirect to dashboard
    if($this->is_logged_in) {
      $this->app->flash->set("You are already logged in", 'info', '/dashboard');
    }

    // authentication page variables
    $auth = (object) [
      'title' => 'Forgot Password',
      'subtitle' => 'Enter your email address and we will send you a password reset link'
    ];
    $this->view->setVar('auth', $auth);

    // return form view
    return $this->view->page('auth.forgot');
  }

  public function processForgot() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // remove any variable not in expected array, also trim vars
    $expected = ['email'];
    $post = $this->app->request->getPostParams();
    $post = $this->expectedOnly($expected, $post);

    // sanitize post
    $this->app->session->set('post', ['auth' => (object) ['email' => $post['email']]]);
    $post['email'] = filter_var(
      $post['email'],
      FILTER_SANITIZE_EMAIL,
      FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
    );

    // validate details
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Message'=>"Validating forgot details..."]);
    $validation_rules = [
      'email' => [
        'value' => $post['email'],
        'rules' => [
          'required' => [
            'msg' => 'The email is required'
          ],
          'email' => [
            'msg' => 'The email is invalid'
          ],
        ]
      ]
    ];
    $validate = Validate::run($validation_rules);

    // if valid data
    if($validate->success) {

      // check if email in database
      $user = $this->model->user->fetchUser(['email' => $post['email']]);
      if($user) {

        if(in_array($user->status, ['Active','Created'])) {

          // generate token
          $token = Text::randomString('alnum', 48, true);

          // save request
          $forgot_model = new Forgot($this->app);
          $forgot_model->newForgot($user->id, $token);

          // update previous unused tokens as expired
          $forgot_model->updateForgot(
            ['status_id'=>Status::EXPIRED_STATUS],
            ['user_id'=>$user->id, 'status_id' => Status::ACTIVE_STATUS]
          );

          // TODO send email with token

          // redirect to the same page
          $this->deleteResourceSessionPostData();
          $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Message'=>"User with email '{$user->email}' successfully requested a reset link."]);$this->app->flash->set(['Kindly follow the instructions sent to your email address to reset your password'], 'success', '/forgot');

        } else {
          $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Forgot failed - User account status is: {$user->status} for {$post['email']}"]);
          $this->app->flash->set(['Your account is locked','Kindly contact the administrator for assistance'], 'error', '/forgot');
        }

      } else {
        // redirect back to login screen with no account notice
        $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"No account available for the email '{$post['email']}'. Forgot failed."]);
        $this->app->flash->set(['The email entered is not registered'], 'error', '/forgot');
      }

    } else {
      // redirect back to login screen with validation errors
      $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"validation failed: ".json_encode($validate->errors)]);
      $this->app->flash->set($validate->errors, 'error', $this->app->route->route->url);
    }
  }

  public function reset() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // get token
    $token = $this->app->route->values->token ?? null;
    if(empty($token)) {
      return $this->app->response->redirect('404');
    }

    // query forgot record by token
    $forgot_model = new Forgot($this->app);
    $record = $forgot_model->fetchForgot(['token'=>$token]);
    if(empty($record)) {
      return $this->app->response->redirect('404');
    }

    // if user is already logged in, redirect to dashboard
    if($this->is_logged_in) {
      $this->app->flash->set("You are already logged in", 'info', '/dashboard');
    }

    // authentication page variables
    $auth = (object) [
      'title' => 'Reset Password',
      'subtitle' => 'Enter and confirm your new password'
    ];
    $this->view->setVar('auth', $auth);

    // return form view
    return $this->view->page('auth.reset');
  }

  public function processReset() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // get token
    $token = $this->app->route->values->token ?? null;
    if(empty($token)) {
      return $this->app->response->redirect('404');
    }

    // query forgot record by token
    $forgot_model = new Forgot($this->app);
    $forgot = $forgot_model->fetchForgot(['token'=>$token]);
    if(empty($forgot)) {
      return $this->app->response->redirect('404');
    }

    // remove any variable not in expected array, also trim vars
    $expected = ['password','confirmpassword'];
    $post = $this->app->request->getPostParams();
    $post = $this->expectedOnly($expected, $post);

    // validate details
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Message'=>"Validating reset details..."]);
    $validation_rules = [
      'password' => [
        'value' => $post['password'],
        'rules' => [
          'required' => [
            'msg' => 'The password is required'
          ],
          'minLen' => [
            'length' => 8,
            'msg' => 'The password should be 8 characters or more',
          ]
        ]
      ],
      'confirmpassword' => [
        'value' => $post['confirmpassword'],
        'rules' => [
          'required' => [
            'msg' => 'The confirm password is required'
          ],
          'similar' => [
            'to' => $post['password'],
            'msg' => 'The password and confirm password do not match'
          ]
        ]
      ]
    ];
    $validate = Validate::run($validation_rules);

    // if valid data
    if($validate->success) {

      // load user
      $user = $this->model->user->fetchUser(['username'=>$forgot->username]);

      // reset user password
      $this->model->user->resetPassword($post['password'], $user->id);

      // mark token as used
      $forgot_model->updateForgot(
        ['status_id'=>Status::USED_STATUS],
        ['id'=>$forgot->id]
      );

      // redirect to login
      $this->app->flash->set('Your password has been reset. Kindly login', 'success', '/');

    } else {
      // redirect back to login screen with validation errors
      $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"validation failed: ".json_encode($validate->errors)]);
      $this->app->flash->set($validate->errors, 'error', $this->app->route->route->url);
    }
  }

  public function logout() {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Controller'=>$this->app->route->route->controller]);

    // fetch active session from database
    $session = $this->model->session->getActiveSession();

    // user
    if($session) {
      $user = $this->model->user->fetchUser(['id' => $session->user_id]);
      $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Message'=>"Logging out the user with email: ".$user->email]);

      // update last seen and status
      $this->model->session->updateSession(
        ['last_seen_at' => Date::now('Y-m-d H:i:s'), 'status_id' => Status::LOGGED_OUT_STATUS],
        ['id' => $session->id]
      );
    }

    // destroy session
    $this->destroySession();

    // redirect to login
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Message'=>"User successfully logged out. Redirecting to login."]);
    return $this->app->response->redirect('/');
  }

  private function generateSession($user) {
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Message'=>"Trying to generate session and then insert into local database..."]);

    // generate session id
    $session_id = $this->generateUserSessionId();
    $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Message'=>"Session generated."]);

    // save session to db
    $insert = $this->model->session->newSession($session_id, $user->id);
    if($insert) {
      $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Message'=>"Session saved into the database successfully."]);

      // save session
      $session_name = $this->app->config->security->logged_in_session_name;
      $this->app->session->set($session_name, $session_id);

      // update number of logins
      $this->model->user->updateUser(
        ['login_count' => $user->login_count + 1],
        ['id' => $user->id]
      );

      // redirect to dashboard
      $this->app->log->info(['TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'Message'=>"User with email '{$user->email}' successfully logged in."]);
      return $this->app->response->redirect('/dashboard');

    } else {
      $this->app->log->error(['ResponseCode'=>400,'TransactionType'=>__CLASS__,'Action'=>__FUNCTION__,'File'=>__FILE__.':'.__LINE__,'ErrorDescription'=>"Login failed: can't create a session for the user"]);
      $this->app->flash->set(['System error while logging you in','Please try again'], 'error', '/');
    }
  }

  private function destroySession() {
    $session_name = $this->app->config->security->logged_in_session_name;
    $this->app->session->delete($session_name);
    $this->app->session->destroy();
  }

}
