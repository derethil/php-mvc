<?php

enum DataType
{
  case Login;
  case Register;
}

class Users extends Controller
{
  public function __construct()
  {
    $this->userModel = $this->model('User');
  }

  public function register()
  {
    // Check for POST
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
      // Sanitize POST Data
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

      // Init data
      $data = [
        'name' => trim($_POST['name']),
        'email' => trim($_POST['email']),
        'password' => trim($_POST['password']),
        'confirm_password' => trim($_POST['confirm_password']),
        'name_err' => '',
        'email_err' => '',
        'password_err' => '',
        'confirm_password_err' => '',
      ];

      $data = $this->validateData($data, DataType::Register);

      // Make sure errors are empty
      if (empty($data['email_err']) && empty($data['name_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
        // Validated

        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        // Register User
        if ($this->userModel->register($data)) {
          flash('register_success', 'You are registered and can log in');
          redirect("users/login");
        } else {
          die("Something went wrong");
        }
      } else {
        // Load view with errors
        $this->view('users/register', $data);
      }
    } else if ($_SERVER['REQUEST_METHOD'] == "GET") {
      // Init data
      $data = [
        'name' => '',
        'email' => '',
        'password' => '',
        'confirm_password' => '',
        'name_err' => '',
        'email_err' => '',
        'password_err' => '',
        'confirm_password_err' => '',
      ];

      // Load view
      $this->view('users/register', $data);
    }
  }

  public function login()
  {
    // Check for POST
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
      // Sanitize POST Data
      $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

      // Init data
      $data = [
        'email' => trim($_POST['email']),
        'password' => trim($_POST['password']),
        'email_err' => '',
        'password_err' => '',
      ];

      $data = $this->validateData($data, DataType::Login);

      // Make sure errors are empty
      if (empty($data['email_err']) && empty($data['password_err'])) {
        // Validated
        // Check password and set logged in user
        $userToLogin = $this->userModel->login($data['email'], $data['password']);

        if ($userToLogin) {
          // Create session
          $this->createUserSession($userToLogin);
        } else {
          $data['password_err'] = 'Incorrect password';

          $this->view('users/login', $data);
        }
      } else {
        // Load view with errors
        $this->view('users/login', $data);
      }
    } else if ($_SERVER['REQUEST_METHOD'] == "GET") {
      // Init data
      $data = [
        'email' => '',
        'password' => '',
        'email_err' => '',
        'password_err' => '',
      ];

      // Load view
      $this->view('users/login', $data);
    }
  }

  private function validateData($data, $dataType)
  {
    // Individual Validation

    switch ($dataType) {
      case DataType::Register:
        // Ensure data exists
        $data = checkEmpty($data, 'name');
        $data = checkEmpty($data, 'email');
        $data = checkEmpty($data, 'password');
        $data = checkEmpty($data, 'confirm_password', "Please confirm password");

        // Validate Confirm Password
        if ($data['password'] != $data['confirm_password']) {
          $data['confirm_password_err'] = 'Passwords do not match';
        }

        if ($this->userModel->userExistsByEmail($data['email'])) {
          $data['email_err'] = 'Email is already taken';
        }

        break;

      case DataType::Login:
        // Ensure data exists
        $data = checkEmpty($data, 'email');
        $data = checkEmpty($data, 'password');

        if ($this->userModel->userExistsByEmail($data['email'])) {
          // User Found
        } else {
          $data['email_err'] = 'No user found';
        }

        break;
    }

    // Shared Validation

    // Validate Password length
    if (strlen($data['password']) < 6) {
      $data['password_err'] = 'Password must be at least 6 characters.';
    }

    return $data;
  }

  public function createUserSession($user)
  {
    $_SESSION['user_id'] = $user->id;
    $_SESSION['user_email'] = $user->email;
    $_SESSION['user_name'] = $user->name;
    redirect('posts');
  }

  public function logout()
  {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    session_destroy();
    redirect('users/login');
  }
}
