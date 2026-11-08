<?php
session_start();
include '../koneksi.php';

/** @var mysqli $conn */

// TAMBAH CUSTOMER
if(isset($_POST['tambah'])){

    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $hp     = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    mysqli_query($conn,"
        INSERT INTO pelanggan(nama, no_hp, alamat)
        VALUES('$nama','$hp','$alamat')
    ");

    header("Location: index.php?success=tambah");
    exit;
}

// HAPUS CUSTOMER
if(isset($_GET['hapus'])){

    $id = $_GET['hapus'];

    mysqli_query($conn,"
        DELETE FROM pelanggan
        WHERE id='$id'
    ");

    header("Location: index.php?success=hapus");
    exit;
}

// EDIT CUSTOMER
if(isset($_POST['edit'])){

    $id      = $_POST['id'];
    $nama    = mysqli_real_escape_string($conn, $_POST['nama']);
    $hp   = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat  = mysqli_real_escape_string($conn, $_POST['alamat']);

    mysqli_query($conn,"
        UPDATE pelanggan
        SET
            nama='$nama',
            no_hp='$hp',
            alamat='$alamat'
        WHERE id='$id'
    ");

    header("Location: index.php?success=edit");
    exit;
}

// CARI CUSTOMER
$cari = isset($_GET['cari']) ? $_GET['cari'] : '';

$data = mysqli_query($conn,"
    SELECT *
    FROM pelanggan
    WHERE nama LIKE '%$cari%'
    ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Data Customer</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body class="bg-light">

<div class="container py-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h3 class="mb-0">Data Customer</h3>
            <small class="text-muted">
                Laundry Management System
            </small>
        </div>

        <button
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#modalTambah">

            + Tambah Customer

        </button>

    </div>

    <!-- ALERT -->
    <?php if(isset($_GET['success'])){ ?>

        <div class="alert alert-success alert-dismissible fade show">

            <?php

            if($_GET['success'] == 'tambah'){
                echo "Customer berhasil ditambahkan";
            }

            if($_GET['success'] == 'edit'){
                echo "Customer berhasil diupdate";
            }

            if($_GET['success'] == 'hapus'){
                echo "Customer berhasil dihapus";
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

            <!-- SEARCH -->
            <form class="mb-3">

                <div class="row g-2">

                    <div class="col-md-10">

                        <input
                            type="text"
                            name="cari"
                            class="form-control"
                            placeholder="Cari customer..."
                            value="<?php echo $cari; ?>">

                    </div>

                    <div class="col-md-2 d-grid">

                        <button class="btn btn-dark">
                            Cari
                        </button>

                    </div>

                </div>

            </form>

            <!-- TABLE -->
            <div class="table-responsive">

                <table class="table table-hover align-middle">

                    <thead class="table-dark">

                        <tr>

                            <th>No</th>
                            <th>Nama</th>
                            <th>No HP</th>
                            <th>Alamat</th>
                            <th width="170">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php
                    $no = 1;

                    while($d = mysqli_fetch_array($data)){
                    ?>

                        <tr>

                            <td><?php echo $no++; ?></td>

                            <td>
                                <?php echo $d['nama']; ?>
                            </td>

                            <td>
                                <?php echo $d['no_hp']; ?>
                            </td>

                            <td>
                                <?php echo $d['alamat']; ?>
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
                                    onclick="return confirm('Hapus customer?')">

                                    Hapus

                                </a>

                            </td>

                        </tr>

                        <!-- MODAL EDIT -->
                        <div class="modal fade" id="edit<?php echo $d['id']; ?>">

                            <div class="modal-dialog modal-dialog-centered">

                                <div class="modal-content rounded-4 border-0">

                                    <div class="modal-header">

                                        <h5 class="modal-title">
                                            Edit Customer
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
                                                    Nama
                                                </label>

                                                <input
                                                    type="text"
                                                    name="nama"
                                                    class="form-control"
                                                    required
                                                    value="<?php echo $d['nama']; ?>">

                                            </div>

                                            <div class="mb-3">

                                                <label class="form-label">
                                                    No HP
                                                </label>

                                                <input
                                                    type="text"
                                                    name="no_hp"
                                                    class="form-control"
                                                    required
                                                    value="<?php echo $d['no_hp']; ?>">

                                            </div>

                                            <div class="mb-3">

                                                <label class="form-label">
                                                    Alamat
                                                </label>

                                                <textarea
                                                    name="alamat"
                                                    class="form-control"
                                                    rows="3"
                                                    required><?php echo $d['alamat']; ?></textarea>

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
                    Tambah Customer
                </h5>

                <button
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>

            </div>

            <form method="POST" action="tambah.php">

                <div class="modal-body">

                    <div class="mb-3">

                        <label class="form-label">
                            Nama Customer
                        </label>

                        <input
                            type="text"
                            name="nama"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            No HP
                        </label>

                        <input
                            type="text"
                            name="no_hp"
                            class="form-control"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">
                            Alamat
                        </label>

                        <textarea
                            name="alamat"
                            class="form-control"
                            rows="2"
                            required></textarea>

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