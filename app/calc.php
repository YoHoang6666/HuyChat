<?php
session_start();
require 'db_connect.php';

// Check if user session exists
if (!isset($_SESSION['user_id'])) {
    // Check for 'remember_me' cookie
    if (isset($_COOKIE['remember_me'])) {
        $token = $_COOKIE['remember_me'];

        $stmt = $conn->prepare("SELECT id, username FROM users WHERE remember_token = ? AND remember_token_expires > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
        } else {
            // Invalid or expired token; clear the cookie
            setcookie('remember_me', '', time() - 3600, "/", "", true, true);
            header('Location: login');
            exit();
        }
    } else {
        // No session and no valid cookie, redirect to login
        header('Location: login.php');
        exit();
    }
}

// Ensure $user is set to avoid undefined errors in HTML
$user = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username']
];

// Debugging in console (safe to remove in production)
echo "<script>console.log(" . json_encode($user) . ");</script>";
?>
<!DOCTYPE html>
<!-- Coding By CodingNepal - codingnepalweb.com -->
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Calculator</title>
    <style>
        /* Import Google font - Poppins */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
          font-family: "Poppins", sans-serif;
        }
        body {
          height: 100vh;
          display: flex;
          align-items: center;
          justify-content: center;
          background: #ffffff;
        }
        .container {
          position: relative;
          max-width: 300px;
          width: 100%;
          border-radius: 12px;
          padding: 10px 20px 20px;
          background: #fff;
          box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);
        }
        .display {
          height: 80px;
          width: 100%;
          outline: none;
          border: none;
          text-align: right;
          margin-bottom: 10px;
          font-size: 25px;
          color: #000e1a;
          pointer-events: none;
        }
        .buttons {
          display: grid;
          grid-gap: 10px;
          grid-template-columns: repeat(4, 1fr);
        }
        .buttons button {
          padding: 10px;
          border-radius: 6px;
          border: none;
          font-size: 20px;
          cursor: pointer;
          background-color: #eee;
        }
        .buttons button:active {
          transform: scale(0.99);
        }
        .operator {
          color: #2f9fff;
        }
        .back {
            position: absolute;
            top: 10px;
            left: 20px;
        }
        .profile-dropdown {
            position: absolute;
            top: 10px;
            right: 20px;
        }
        .profile-btn {
            background: none;
            border: none;
            cursor: pointer;
        }
        .profile-btn img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            background: #2c3e50;
            right: 0;
            margin-top: 5px;
            border-radius: 5px;
            overflow: hidden;
            z-index: 1000;
        }
        .dropdown-menu a {
            display: block;
            padding: 10px;
            text-decoration: none;
            color: #fff;
        }
        .dropdown-menu a:hover {
            background: #1abc9c;
        }
        .dropdown-menu .upgrade {
            display: none;
        }
        .diamond-tier .dropdown-menu .upgrade {
            display: none !important;
        }
        .profile-dropdown:hover .dropdown-menu {
            display: block;
        }
    </style>
  </head>
  <body>
    <a class="back" onclick="history.back()">
    <img src="back arrow.png">
    </a>
    <div class="profile-dropdown">
        <button class="profile-btn"><?= htmlspecialchars($_SESSION['username']); ?></button>
        <div class="dropdown-menu">
            <a href="profile">Profile</a>
            <a href="logout">Logout</a>
            <a href="upgrade.php" class="upgrade">Upgrade</a>
        </div>
    </div>
    <div class="container">
      <input type="text" class="display" />
      <div class="buttons">
        <button class="operator" data-value="AC">AC</button>
        <button class="operator" data-value="DEL">DEL</button>
        <button class="operator" data-value="%">%</button>
        <button class="operator" data-value="/">/</button>
        <button data-value="7">7</button>
        <button data-value="8">8</button>
        <button data-value="9">9</button>
        <button class="operator" data-value="*">*</button>
        <button data-value="4">4</button>
        <button data-value="5">5</button>
        <button data-value="6">6</button>
        <button class="operator" data-value="-">-</button>
        <button data-value="1">1</button>
        <button data-value="2">2</button>
        <button data-value="3">3</button>
        <button class="operator" data-value="+">+</button>
        <button data-value="0">0</button>
        <button data-value="00">00</button>
        <button data-value=".">.</button>
        <button class="operator" data-value="=">=</button>
      </div>
    </div>
    <script>
        const display = document.querySelector(".display");
        const buttons = document.querySelectorAll("button");
        const specialChars = ["%", "*", "/", "-", "+", "="];
        let output = "";
        //Define function to calculate based on button clicked.
        const calculate = (btnValue) => {
          display.focus();
          if (btnValue === "=" && output !== "") {
            //If output has '%', replace with '/100' before evaluating.
            output = eval(output.replace("%", "/100"));
          } else if (btnValue === "AC") {
            output = "";
          } else if (btnValue === "DEL") {
            //If DEL button is clicked, remove the last character from the output.
            output = output.toString().slice(0, -1);
          } else {
            //If output is empty and button is specialChars then return
            if (output === "" && specialChars.includes(btnValue)) return;
            output += btnValue;
          }
          display.value = output;
        };
        //Add event listener to buttons, call calculate() on click.
        buttons.forEach((button) => {
          //Button click listener calls calculate() with dataset value as argument.
          button.addEventListener("click", (e) => calculate(e.target.dataset.value));
        });
    </script>
  </body>
</html>