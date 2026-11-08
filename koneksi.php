<?php
date_default_timezone_set("Asia/Jakarta");
$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "laundry"
);

if(!$conn){
    die("Koneksi gagal : " . mysqli_connect_error());
}
?>