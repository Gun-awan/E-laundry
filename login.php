<?php
session_start();
include 'koneksi.php';

/** @var mysqli $conn */

if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = md5($_POST['password']);

    $data = mysqli_query($conn,"
        SELECT * FROM users
        WHERE username='$username'
        AND password='$password'
    ");

    if(mysqli_num_rows($data) > 0){

        $_SESSION['login'] = true;

        header("Location: dashboard.php");
    }else{
        echo "Login gagal";
    }
}
?>

<form method="POST">

    <input type="text" name="username" placeholder="Username">

    <input type="password" name="password" placeholder="Password">

    <button name="login">
        Login
    </button>

</form>