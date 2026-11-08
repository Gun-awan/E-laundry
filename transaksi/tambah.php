<?php
include '../koneksi.php';

/** @var mysqli $conn */

$nama   = mysqli_real_escape_string($conn, $_POST['nama']);
$hp     = mysqli_real_escape_string($conn, $_POST['no_hp']);
$alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

$query = mysqli_query($conn,"
    INSERT INTO pelanggan(
        nama,
        no_hp,
        alamat
    )
    VALUES(
        '$nama',
        '$hp',
        '$alamat'
    )
");

if($query){

    header("Location:index.php?success=tambah");

}else{

    echo "
        <script>
            alert('Data gagal disimpan');
            window.location='index.php';
        </script>
    ";

}
?>