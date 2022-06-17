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
        die('SUCCESS');
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
    switch ($dataType) {
      case DataType::Register:
        // Validate Name
        if (empty($data['name'])) {
          $data['name_err'] = 'Please enter name';
        }

        // Validate Confirm Password
        if (empty($data['confirm_password'])) {
          $data['confirm_password_err'] = 'Please confirm password';
        } else if ($data['password'] != $data['confirm_password']) {
          $data['confirm_password_err'] = 'Passwords do not match';
        }

      case DataType::Login:
        // Validate Email Is Not Empty
        if (empty($data['email'])) {
          $data['email_err'] = 'Please enter email';
        }

        // Validate Password
        if (empty($data['password'])) {
          $data['password_err'] = 'Please enter password';
        } else if (strlen($data['password']) < 6) {
          $data['password_err'] = 'Password must be at least 6 characters.';
        }

        break;
    }

    // Extra validation on Register after non-empty check
    if ($dataType == DataType::Register) {

      // Validate Email
      if ($this->userModel->userExists($data['email'])) {
        $data['email_err'] = 'Email is already taken';
      }
    }

    return $data;
  }
}
