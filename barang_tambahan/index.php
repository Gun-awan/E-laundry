<?php
session_start();
include '../koneksi.php';

/** @var mysqli $conn */

// ======================
// TAMBAH
// ======================
if(isset($_POST['tambah'])){

    $nama  = mysqli_real_escape_string(
        $conn,
        $_POST['nama_barang']
    );

    $harga = mysqli_real_escape_string(
        $conn,
        $_POST['harga']
    );

    mysqli_query($conn,"
        INSERT INTO barang_tambahan(
            nama_barang,
            harga
        )
        VALUES(
            '$nama',
            '$harga'
        )
    ");

    header("Location:index.php?success=tambah");
    exit;
}

// ======================
// EDIT
// ======================
if(isset($_POST['edit'])){

    $id    = $_POST['id'];

    $nama  = mysqli_real_escape_string(
        $conn,
        $_POST['nama_barang']
    );

    $harga = mysqli_real_escape_string(
        $conn,
        $_POST['harga']
    );

    mysqli_query($conn,"
        UPDATE barang_tambahan
        SET
            nama_barang='$nama',
            harga='$harga'
        WHERE id='$id'
    ");

    header("Location:index.php?success=edit");
    exit;
}

// ======================
// HAPUS
// ======================
if(isset($_GET['hapus'])){

    $id = $_GET['hapus'];

    mysqli_query($conn,"
        DELETE FROM barang_tambahan
        WHERE id='$id'
    ");

    header("Location:index.php?success=hapus");
    exit;
}

// ======================
// DATA
// ======================
$data = mysqli_query($conn,"
    SELECT *
    FROM barang_tambahan
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>
        Barang Tambahan
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>

<body class="bg-light">

<div class="container py-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>

            <h3 class="mb-0">
                Barang Tambahan
            </h3>

            <small class="text-muted">
                Laundry Management System
            </small>

        </div>

        <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#modalTambah">

            + Tambah Barang

        </button>

    </div>

    <!-- ALERT -->
    <?php if(isset($_GET['success'])){ ?>

        <div class="alert alert-success alert-dismissible fade show">

            <?php

            if($_GET['success'] == 'tambah'){
                echo "Barang berhasil ditambahkan";
            }

            if($_GET['success'] == 'edit'){
                echo "Barang berhasil diupdate";
            }

            if($_GET['success'] == 'hapus'){
                echo "Barang berhasil dihapus";
            }

            ?>

            <button
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        </div>

    <?php } ?>

    <!-- CARD -->
    <div class="card border-0 shadow-sm rounded-4">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th>No</th>
                            <th>Nama Barang</th>
                            <th>Harga</th>
                            <th width="170">
                                Aksi
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php
                    $no = 1;

                    while($d = mysqli_fetch_array($data)){
                    ?>

                        <tr>

                            <td>
                                <?php echo $no++; ?>
                            </td>

                            <td>
                                <?php echo $d['nama_barang']; ?>
                            </td>

                            <td>

                                Rp<?php
                                echo number_format(
                                    $d['harga']
                                );
                                ?>

                            </td>

                            <td>

                                <button
                                    class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#edit<?php echo $d['id']; ?>">

                                    Edit

                                </button>

                                <a
                                    href="?hapus=<?php echo $d['id']; ?>"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Hapus barang?')">

                                    Hapus

                                </a>

                            </td>

                        </tr>

                        <!-- MODAL EDIT -->
                        <div
                            class="modal fade"
                            id="edit<?php echo $d['id']; ?>">

                            <div class="modal-dialog modal-dialog-centered">

                                <div class="modal-content rounded-4 border-0">

                                    <div class="modal-header">

                                        <h5 class="modal-title">
                                            Edit Barang
                                        </h5>

                                        <button
                                            class="btn-close"
                                            data-bs-dismiss="modal">
                                        </button>

                                    </div>

                                    <form method="POST">

                                        <div class="modal-body">

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php echo $d['id']; ?>">

                                            <div class="mb-3">

                                                <label class="form-label">
                                                    Nama Barang
                                                </label>

                                                <input
                                                    type="text"
                                                    name="nama_barang"
                                                    class="form-control"
                                                    required
                                                    value="<?php echo $d['nama_barang']; ?>">

                                            </div>

                                            <div class="mb-3">

                                                <label class="form-label">
                                                    Harga
                                                </label>

                                                <input
                                                    type="number"
                                                    name="harga"
                                                    class="form-control"
                                                    required
                                                    value="<?php echo $d['harga']; ?>">

                                            </div>

                                        </div>

                                        <div class="modal-footer">

                                            <button
                                                class="btn btn-secondary"
                                                data-bs-dismiss="modal">

                                                Batal

                                            </button>

                                            <button
                                                class="btn btn-primary"
                                                name="edit">

                                                Simpan

                                            </button>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content rounded-4 border-0">

            <div class="modal-header">

                <h5 class="modal-title">
                    Tambah Barang
                </h5>

                <button
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <form method="POST">

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">
                            Nama Barang
                        </label>

                        <input
                            type="text"
                            name="nama_barang"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Harga
                        </label>

                        <input
                            type="number"
                            name="harga"
                            class="form-control"
                            required>

                    </div>

                </div>

                <div class="modal-footer">

                    <button
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button
                        class="btn btn-primary"
                        name="tambah">

                        Simpan

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>