<?php
session_start();
include '../koneksi.php';

function kirimWA($conn, $id_transaksi){

    // AMBIL DATA TRANSAKSI
    $trx = mysqli_fetch_array(mysqli_query($conn,"
        SELECT
            transaksi.*,
            pelanggan.nama,
            pelanggan.no_hp,
            paket.nama_paket
        FROM transaksi
        JOIN pelanggan
        ON pelanggan.id = transaksi.pelanggan_id
        JOIN paket
        ON paket.id = transaksi.paket_id
        WHERE transaksi.id='$id_transaksi'
    "));

    // FORMAT NOMOR
    $hp = preg_replace(
        '/^0/',
        '62',
        $trx['no_hp']
    );

    // STATUS PEMBAYARAN
    if($trx['bayar'] > 0){

        $pembayaran = "Lunas";

    }else{

        $pembayaran = "Belum Bayar";
    }

    // FORMAT DEADLINE
    $deadline = date(
        'd M Y H:i',
        strtotime($trx['deadline'])
    );

    // PESAN
    $pesan =
"Hallo ".$trx['nama'].",

Berikut invoice laundry anda :

Invoice : ".$trx['kode_invoice']."

Paket : ".$trx['nama_paket']."
Berat : ".$trx['berat']." Kg
Total : Rp".number_format($trx['total'])."
Pembayaran : ".$pembayaran."
Deadline : ".$deadline."
Status : ".$trx['status']."

Terima kasih 🙏";

    // LINK WA
    $wa =
        "https://wa.me/".
        $hp.
        "?text=".urlencode($pesan);

    return $wa;
}

/** @var mysqli $conn */
// ======================
// TAMBAH TRANSAKSI
// ======================

if (isset($_POST['tambah'])) {

    $nama   = mysqli_real_escape_string($conn, $_POST['nama']);
    $hp     = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    mysqli_query($conn, "
        INSERT INTO pelanggan(nama, no_hp, alamat)
        VALUES('$nama','$hp','$alamat')
    ");

    header("Location: index.php?success=tambah");
    exit;
}

if (isset($_POST['update'])) {

    $id           = $_POST['id'];

    $pelanggan_id = $_POST['pelanggan_id'];

    $paket_id     = $_POST['paket_id'];

    $berat = floatval($_POST['berat']);

    $bayar = floatval($_POST['bayar'] ?? 0);

    // AMBIL PAKET
    $paket = mysqli_fetch_array(mysqli_query($conn, "
        SELECT * FROM paket
        WHERE id='$paket_id'
    "));

    $harga = $paket['harga'];

    $total = $harga * $berat;

    // PEMBULATAN
    $total = floor($total / 500) * 500;

    $kembalian = $bayar - $total;

    mysqli_query($conn, "
        UPDATE transaksi
        SET
            pelanggan_id='$pelanggan_id',
            paket_id='$paket_id',
            berat='$berat',
            total='$total',
            bayar='$bayar',
            kembalian='$kembalian'
        WHERE id='$id'
    ");

    // LINK WA
$wa = urlencode(
    kirimWA($conn, $id)
);

// REDIRECT
header("Location:index.php?success=edit&wa=$wa");

exit;

}

if (isset($_POST['simpan'])) {

    $invoice      = "INV-" . date("mdHi");

    $pelanggan_id = $_POST['pelanggan_id'];
    $paket_id     = $_POST['paket_id'];
    $berat = floatval($_POST['berat']);

$bayar = floatval($_POST['bayar'] ?? 0);
    $status_bayar = $_POST['status_bayar'];

    // ambil data paket
    $paket = mysqli_fetch_array(mysqli_query($conn, "
        SELECT * FROM paket
        WHERE id='$paket_id'
    "));

    $hari = $paket['keterangan'];

    $deadline = date(
        'Y-m-d H:i:s',
        strtotime("+$hari days")
    );

    $harga = $paket['harga'];

    $total = $harga * $berat;

    // PEMBULATAN KE BAWAH 500
    $total = floor($total / 500) * 500;

    if ($status_bayar == 'Bayar Nanti') {

        $bayar = 0;
        $kembalian = 0;
    }

    $kembalian = $bayar - $total;

    mysqli_query($conn, "
        INSERT INTO transaksi(
            kode_invoice,
            pelanggan_id,
            paket_id,
            berat,
            total,
            bayar,
            kembalian,
            status,
            status_bayar,
            deadline
        )
        VALUES(
            '$invoice',
            '$pelanggan_id',
            '$paket_id',
            '$berat',
            '$total',
            '$bayar',
            '$kembalian',
            'Diproses',
            '$status_bayar',
            '$deadline'
        )
    ");

    // ID TRANSAKSI
$id_transaksi = mysqli_insert_id($conn);

// LINK WA
$wa = urlencode(
    kirimWA($conn, $id_transaksi)
);

// REDIRECT
header("Location:index.php?success=simpan&wa=$wa");
    exit;

}

// UPDATE STATUS
if (isset($_POST['status'])) {

    $id     = $_POST['id'];

    $status = $_POST['status'];

    mysqli_query($conn, "
        UPDATE transaksi
        SET status='$status'
        WHERE id='$id'
    ");

    // JIKA STATUS SELESAI
    if ($status == 'Selesai') {

        $trx = mysqli_fetch_array(mysqli_query($conn, "
            SELECT
                transaksi.*,
                pelanggan.nama,
                pelanggan.no_hp
            FROM transaksi
            JOIN pelanggan
            ON pelanggan.id = transaksi.pelanggan_id
            WHERE transaksi.id='$id'
        "));

        // FORMAT NOMOR
        $hp = preg_replace(
            '/^0/',
            '62',
            $trx['no_hp']
        );

        // CEK PEMBAYARAN
        if ($trx['bayar'] > 0) {

            $infoPembayaran =
                "Pembayaran : Lunas";
        } else {

            $infoPembayaran =
                "Total Pembayaran : Rp" .
                number_format($trx['total']);
        }

        // PESAN
        $pesan =
            "Halo " . $trx['nama'] .

            "%0A%0ALaundry anda sudah selesai dan siap diambil." .

            "%0A%0AInvoice : " . $trx['kode_invoice'] .

            "%0A" . $infoPembayaran .

            "%0A%0ATerima kasih";

        // LINK WA
$wa =
    "https://wa.me/" .
    $hp .
    "?text=" .
    $pesan;

// REDIRECT KE INDEX + WA
$wa = urlencode($wa);

header(
    "Location:index.php?success=selesai&wa=$wa");

exit;
    }

    header("Location:index.php");
    exit;
}

// HAPUS TRANSAKSI
if (isset($_GET['hapus'])) {

    $id = $_GET['hapus'];

    mysqli_query($conn, "
        DELETE FROM transaksi
        WHERE id='$id'
    ");

    header("Location:index.php?success=hapus");
    exit;
}
// ======================
// DATA TRANSAKSI
// ======================
$data = mysqli_query($conn, "
    SELECT
        transaksi.*,
        pelanggan.nama AS nama_pelanggan,
        pelanggan.no_hp,
        paket.nama_paket
    FROM transaksi
    JOIN pelanggan
    ON pelanggan.id = transaksi.pelanggan_id
    JOIN paket
    ON paket.id = transaksi.paket_id
    ORDER BY transaksi.id DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Transaksi Laundry</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background: #f5f7fb;
        }

        .card {
            border: none;
            border-radius: 20px;
        }

        .table th {
            white-space: nowrap;
        }

        .badge {
            padding: 8px 12px;
            border-radius: 20px;
        }
    </style>

</head>

<body>

<?php

$pesanAlert = "Transaksi berhasil disimpan";

if(isset($_GET['success'])){

    if($_GET['success'] == 'edit'){

        $pesanAlert =
            "Transaksi berhasil diupdate";

    }

}

?>


    <div class="container-fluid py-4">

        <!-- HEADER -->
        <div class="d-flex justify-content-between align-items-center mb-0">

            <div>

                <h3 class="fw-bold mb-0">
                    Transaksi Laundry
                </h3>

                <small class="text-muted">
                    Laundry Management System
                </small>

            </div>

        </div>

            </div>

        <div class="row">

            <!-- FORM -->
            <div class="col-lg-4 mb-4">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h5 class="mb-4">
                                Input Transaksi
                            </h5>

                            <button
                                class="btn btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#modalTambah">

                                + Customer Baru

                            </button>
                        </div>

                        <?php
                        $edit = null;

                        if (isset($_GET['edit'])) {

                            $id_edit = $_GET['edit'];

                            $edit = mysqli_fetch_array(mysqli_query($conn, "
        SELECT *
        FROM transaksi
        WHERE id='$id_edit'
    "));
                        }

                        ?>

                        <form method="POST">

                            <?php if (isset($edit)) { ?>

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?php echo $edit['id']; ?>">

                            <?php } ?>

                            <!-- CUSTOMER -->
                            <div class="mb-3">

                                <!-- <label class="form-label">
                                Customer
                            </label> -->

                                <select
                                    id="customer"
                                    name="pelanggan_id"
                                    class="form-select"
                                    required>

                                    <option value="">
                                        -- Pilih Customer --
                                    </option>

                                    <?php
                                    $pelanggan = mysqli_query($conn, "
                                    SELECT * FROM pelanggan
                                    ORDER BY nama ASC
                                ");

                                    while ($p = mysqli_fetch_array($pelanggan)) {
                                    ?>

                                        <option
                                            value="<?php echo $p['id']; ?>"

                                            data-hp="<?php echo $p['no_hp']; ?>"

                                            <?php
                                            if (
                                                isset($edit['pelanggan_id']) &&
                                                $edit['pelanggan_id'] == $p['id']
                                            ) {
                                                echo 'selected';
                                            }
                                            ?>>

                                            <?php echo $p['nama']; ?>

                                        </option>

                                    <?php } ?>

                                </select>

                                <small
                                    id="nohp"
                                    class="text-muted">

                                    No. HP: -

                                </small>

                            </div>

                            <!-- PAKET -->
                            <div class="mb-3">

                                <!-- <label class="form-label">
                                Paket Laundry
                            </label> -->

                                <select
                                    id="paket"
                                    name="paket_id"
                                    class="form-select"
                                    required>

                                    <option>
                                        -- Pilih Paket --
                                    </option>

                                    <?php
                                    $paket = mysqli_query($conn, "
                                    SELECT * FROM paket
                                    ORDER BY nama_paket ASC
                                ");

                                    while ($pk = mysqli_fetch_array($paket)) {
                                    ?>

                                        <option
                                            value="<?php echo $pk['id']; ?>"
                                            data-harga="<?php echo $pk['harga']; ?>"

                                            <?php
                                            if (
                                                isset($edit['paket_id']) &&
                                                $edit['paket_id'] == $pk['id']
                                            ) {
                                                echo 'selected';
                                            }
                                            ?>>

                                            <?php echo $pk['nama_paket']; ?>
                                            -
                                            Rp<?php echo number_format($pk['harga']); ?>/kg
                                            - (<?php echo $pk['keterangan']; ?> Hari)

                                        </option>

                                    <?php } ?>

                                </select>

                            </div>

                            <!-- BERAT -->
                            <div class="mb-3">

                                <!-- <label class="form-label">
                                Berat (Kg)
                            </label> -->

                                <input
                                    placeholder="Berat (Kg)"
                                    type="number"
                                    step="0.1"
                                    id="berat"
                                    name="berat"
                                    class="form-control"
                                    required

                                    value="<?php
                                            echo isset($edit['berat'])
                                                ? $edit['berat']
                                                : '';
                                            ?>">

                            </div>

                            <!-- BARANG TAMBAHAN -->
                            <div class="mb-3">

                                <label class="form-label">
                                    Barang Tambahan
                                </label>

                                <?php
                                $tambahan = mysqli_query($conn, "
                                SELECT * FROM barang_tambahan
                                ORDER BY nama_barang ASC
                            ");

                                while ($t = mysqli_fetch_array($tambahan)) {
                                ?>

                                    <div class="row align-items-center mb-2">

                                        <!-- CHECKBOX -->
                                        <div class="col-7">

                                            <div class="form-check">

                                                <input
                                                    class="form-check-input tambahan"
                                                    type="checkbox"
                                                    data-harga="<?php echo $t['harga']; ?>"
                                                    id="tambahan<?php echo $t['id']; ?>">

                                                <label
                                                    class="form-check-label"
                                                    for="tambahan<?php echo $t['id']; ?>">

                                                    <?php echo $t['nama_barang']; ?>
                                                    -
                                                    Rp<?php echo number_format($t['harga']); ?>/Pcs

                                                </label>

                                            </div>

                                        </div>

                                        <!-- QTY -->
                                        <div class="col-5">

                                            <input
                                                type="number"
                                                min="1"
                                                value="1"
                                                class="form-control qty-tambahan"
                                                data-id="<?php echo $t['id']; ?>">

                                        </div>

                                    </div>

                                <?php } ?>

                            </div>

                            <!-- TOTAL -->
                            <div class="mb-3">

                                <!-- <label class="form-label">
                                Total
                            </label> -->

                                <input
                                    placeholder="Total Harga"
                                    type="text"
                                    id="total"
                                    class="form-control bg-light"
                                    readonly>

                            </div>

                            <!-- METODE PEMBAYARAN -->
                            <div class="mb-3">

                                <div class="d-flex gap-3">

                                    <label class="form-label">
                                        Pembayaran :
                                    </label>



                                    <!-- BAYAR SEKARANG -->
                                    <div class="form-check">

                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="status_bayar"
                                            id="bayar_sekarang"
                                            value="Bayar Sekarang"
                                            checked>

                                        <label
                                            class="form-check-label"
                                            for="bayar_sekarang">

                                            Bayar Sekarang

                                        </label>

                                    </div>

                                    <!-- BAYAR NANTI -->
                                    <div class="form-check">

                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="status_bayar"
                                            id="bayar_nanti"
                                            value="Bayar Nanti">

                                        <label
                                            class="form-check-label"
                                            for="bayar_nanti">

                                            Bayar Nanti

                                        </label>

                                    </div>

                                </div>

                            </div>

                            <!-- BAYAR -->
                            <div class="mb-3">

                                <!-- <label class="form-label">
                                Bayar
                            </label> -->

                                <input
                                    value="<?php
                                            echo isset($edit['bayar'])
                                                ? $edit['bayar']
                                                : '';
                                            ?>"
                                    placeholder="Jumlah Bayar"
                                    type="number"
                                    id="inputBayar"
                                    name="bayar"
                                    class="form-control"
                                    required>

                            </div>

                            <!-- KEMBALIAN -->
                            <div class="mb-4">

                                <!-- <label class="form-label">
                                Kembalian
                            </label> -->

                                <input
                                    placeholder="Kembalian"
                                    type="text"
                                    id="kembalian"
                                    class="form-control bg-light"
                                    readonly>

                            </div>

                            <div class="d-grid">

                                <button
                                    class="btn btn-primary btn-lg"
                                    name="<?php echo isset($edit) ? 'update' : 'simpan'; ?>">

                                    <?php
                                    echo isset($edit)
                                        ? 'Update Transaksi'
                                        : 'Simpan Transaksi';
                                    ?>

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

            <!-- TABLE -->
            <div class="col-lg-8">

                <div class="card shadow-sm">

                    <div class="card-body">

                        <h5 class="mb-4">
                            Transaksi Hari Ini
                        </h5>

                        <div class="table-responsive">

                            <table class="table table-hover align-middle">

                                <thead class="table-dark">

                                    <tr>

                                        <th>Invoice</th>
                                        <th>Deadline</th>
                                        <th>Customer</th>
                                        <th>Paket</th>
                                        <!-- <th>Berat</th>
                                    <th>Total</th> -->
                                        <th>Status</th>
                                        <th>Pembayaran</th>
                                        <!-- <th>WhatsApp</th> -->
                                        <th width="120">Aksi</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php while ($d = mysqli_fetch_array($data)) { ?>

                                        <tr>

                                            <td>
                                                <?php echo $d['kode_invoice']; ?>
                                            </td>

                                            <td>

                                                <?php

                                                $sekarang = new DateTime();

                                                $deadline = new DateTime($d['deadline']);

                                                $diff = $sekarang->diff($deadline);

                                                $hari = $diff->d;

                                                $jam  = $diff->h;

                                                // WARNA BADGE
                                                if ($hari >= 3) {

                                                    $badge = 'success';
                                                } elseif ($hari >= 2) {

                                                    $badge = 'warning';
                                                } else {

                                                    $badge = 'danger';
                                                }

                                                ?>

                                                <span class="badge bg-<?php echo $badge; ?> p-2">

                                                    <?php echo $hari; ?> Hari
                                                    (<?php echo $jam; ?> Jam)

                                                </span>

                                            </td>

                                            <td>
                                                <?php echo $d['nama_pelanggan']; ?>
                                            </td>

                                            <td>
                                                <?php echo $d['nama_paket']; ?>
                                            </td>

                                            <!-- <td>
                                        <?php echo $d['berat']; ?> Kg
                                    </td> -->

                                            <!-- <td>

                                        <?php echo number_format($d['total']); ?>

                                    </td> -->

                                            <td>

                                                <form method="POST">

                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?php echo $d['id']; ?>">

                                                    <select
                                                        name="status"
                                                        class="form-select form-select-sm"
                                                        onchange="submitStatus(this.form)">

                                                        <option
                                                            value="Diproses"
                                                            <?php if ($d['status'] == 'Diproses') echo 'selected'; ?>>

                                                            Diproses

                                                        </option>

                                                        <option
                                                            value="Dicuci"
                                                            <?php if ($d['status'] == 'Dicuci') echo 'selected'; ?>>

                                                            Dicuci

                                                        </option>

                                                        <option
                                                            value="Disetrika"
                                                            <?php if ($d['status'] == 'Disetrika') echo 'selected'; ?>>

                                                            Disetrika

                                                        </option>

                                                        <option
                                                            value="Selesai"
                                                            <?php if ($d['status'] == 'Selesai') echo 'selected'; ?>>

                                                            Selesai

                                                        </option>

                                                        <option
                                                            value="Diambil"
                                                            <?php if ($d['status'] == 'Diambil') echo 'selected'; ?>>

                                                            Diambil

                                                        </option>

                                                    </select>

                                                </form>

                                            </td>

                                            <td>

                                                <?php

                                                if ($d['bayar'] > 0) {

                                                    $badgeBayar = 'success';
                                                    $textBayar  = 'Lunas';
                                                } else {

                                                    $badgeBayar = 'danger';
                                                    $textBayar  = 'Belum Bayar';
                                                }

                                                ?>

                                                <span class="badge bg-<?php echo $badgeBayar; ?>">

                                                    <?php echo $textBayar; ?>

                                                </span>

                                            </td>

                                            <!-- <td>

                                            <?php
                                            if ($d['status'] == 'Selesai') {

                                                // FORMAT NOMOR
                                                $hp = $d['no_hp'];

                                                $hp = preg_replace('/^0/', '62', $hp);

                                                // PESAN
                                                // PESAN
                                                $pesan =
                                                "Hallo ".$d['nama_pelanggan'].",

                                                Laundry anda sudah selesai dan siap diambil 😊

                                                Invoice : ".$d['kode_invoice']."

                                                Silakan datang ke outlet untuk pengambilan laundry.

                                                Terima kasih 🙏";

                                                $pesan = urlencode($pesan);

                                                $link =
                                                    "https://wa.me/" .
                                                    $hp .
                                                    "?text=" .
                                                    $pesan;
                                            ?>

                                                <a
                                                    href="<?php echo $link; ?>"
                                                    target="_blank"
                                                    class="btn btn-success btn-sm">

                                                    WA

                                                </a>

                                            <?php } else { ?>

                                                <span class="text-muted">
                                                    -
                                                </span>

                                            <?php } ?>

                                            </td> -->

                                            <td>

                                                <div class="d-flex gap-2">

                                                    <!-- EDIT -->
                                                    <a
                                                        href="?edit=<?php echo $d['id']; ?>"
                                                        class="btn btn-warning btn-sm">

                                                        <i class="bi bi-pencil-square"></i>

                                                    </a>

                                                    <!-- HAPUS -->
                                                    <a
                                                        href="?hapus=<?php echo $d['id']; ?>"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Hapus transaksi ini?')">

                                                        <i class="bi bi-trash"></i>

                                                    </a>

                                                </div>

                                            </td>

                                        </tr>

                                    <?php } ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

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

                        <!--<div class="mb-3">

                        <label class="form-label">
                            Alamat
                        </label>

                         <textarea
                            name="alamat"
                            class="form-control"
                            rows="2"
                            ></textarea>

                    </div> -->

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

    <?php

$pesanAlert = "Transaksi berhasil disimpan";

if(isset($_GET['success'])){

    if($_GET['success'] == 'edit'){

        $pesanAlert =
            "Transaksi berhasil diupdate";

    }

}

?>

    <script>
        document
            .querySelectorAll('input[type=number]')
            .forEach(function(input) {

                input.addEventListener('wheel', function(e) {

                    e.preventDefault();

                });

            });
    </script>

    <!-- <script>
        setTimeout(function() {

            let alert =
                document.querySelector('.alert');

            if (alert) {

                let bsAlert =
                    new bootstrap.Alert(alert);

                bsAlert.close();

            }

        }, 2000);
    </script> -->

    <script>
        function submitStatus(form) {

            form.submit();

        }
    </script>

    <script>
        const customer =
            document.getElementById('customer');

        const nohp =
            document.getElementById('nohp');

        // FUNCTION TAMPIL HP
        function tampilHP() {

            let selected =
                customer.options[
                    customer.selectedIndex
                ];

            let hp =
                selected.getAttribute('data-hp');

            nohp.innerHTML =
                'No. HP: ' + (hp ?? '-');
        }

        // SAAT SELECT BERUBAH
        customer.addEventListener(
            'change',
            tampilHP
        );

        // SAAT HALAMAN DIBUKA
        tampilHP();

        customer.addEventListener('change', function() {

            let selected =
                customer.options[
                    customer.selectedIndex
                ];

            let hp =
                selected.getAttribute('data-hp');

            nohp.innerHTML =
                'No. HP: ' + hp;

        });

        const paket = document.getElementById('paket');
        const berat = document.getElementById('berat');
        const total = document.getElementById('total');
        const bayar = document.getElementById('inputBayar');
        const kembalian = document.getElementById('kembalian');

        function hitungTotal() {

            let selectedOption =
                paket.options[paket.selectedIndex];

            let harga =
                parseInt(
                    selectedOption.getAttribute('data-harga')
                ) || 0;

            let b = parseFloat(berat.value) || 0;

            // TOTAL LAUNDRY
            let totalLaundry = harga * b;

            // TOTAL TAMBAHAN
            let totalTambahan = 0;

            document
                .querySelectorAll('.tambahan')
                .forEach(function(item) {

                    if (item.checked) {

                        let hargaTambahan =
                            parseInt(
                                item.getAttribute('data-harga')
                            ) || 0;

                        // ambil ID checkbox
                        let id =
                            item.id.replace('tambahan', '');

                        // cari qty sesuai ID
                        let qtyInput =
                            document.querySelector(
                                '.qty-tambahan[data-id="' + id + '"]'
                            );

                        let qty =
                            parseInt(qtyInput.value) || 1;

                        totalTambahan +=
                            hargaTambahan * qty;
                    }

                });

            // GRAND TOTAL
            let grandTotal =
                totalLaundry + totalTambahan;

            // PEMBULATAN KE BAWAH 500
            grandTotal =
                Math.floor(grandTotal / 500) * 500;

            total.value =
                'Rp ' +
                grandTotal.toLocaleString('id-ID');

            return grandTotal;
        }

        document
            .querySelectorAll('.tambahan')
            .forEach(function(item) {

                item.addEventListener(
                    'change',
                    hitungTotal
                );

            });

        const bayarSekarang =
            document.getElementById('bayar_sekarang');

        const bayarNanti =
            document.getElementById('bayar_nanti');

        const inputBayar =
            document.getElementById('inputBayar');

        // DEFAULT
        inputBayar.required = true;

        // JIKA BAYAR SEKARANG
        bayarSekarang.addEventListener('change', function() {

            inputBayar.required = true;

            inputBayar.removeAttribute('readonly');

        });

        // JIKA BAYAR NANTI
        bayarNanti.addEventListener('change', function() {

            inputBayar.required = false;

            inputBayar.value = '';

            kembalian.value = '';

            inputBayar.setAttribute(
                'readonly',
                true
            );

        });

        document
            .querySelectorAll('.qty-tambahan')
            .forEach(function(item) {

                item.addEventListener(
                    'keyup',
                    hitungTotal
                );

                item.addEventListener(
                    'change',
                    hitungTotal
                );

            });

        document
            .querySelectorAll('.tambahan')
            .forEach(function(item) {

                item.addEventListener(
                    'change',
                    hitungTotal
                );

            });

        function hitungKembalian() {

            let t = hitungTotal();

            let uang =
                parseInt(bayar.value) || 0;

            let kembali = uang - t;

            kembalian.value =
                'Rp ' +
                kembali.toLocaleString('id-ID');
        }

        paket.addEventListener('change', hitungTotal);
        berat.addEventListener('keyup', hitungTotal);

        bayar.addEventListener('keyup', hitungKembalian);

        // JALANKAN SAAT MODE EDIT
hitungTotal();

hitungKembalian();

paket.addEventListener(
    'change',
    hitungTotal
);

berat.addEventListener(
    'keyup',
    hitungTotal
);

bayar.addEventListener(
    'keyup',
    hitungKembalian
);

// AUTO LOAD SAAT EDIT
hitungTotal();

hitungKembalian();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    

<?php if(isset($_GET['wa'])){ ?>

<script>

window.onload = function(){

    let kirim = confirm(
        "<?php echo $pesanAlert; ?>.\n\nKirim invoice ke WhatsApp?"
    );

    if(kirim){

        window.open(
            "<?php echo urldecode($_GET['wa']); ?>",
            "_blank"
        );

    }

}

</script>

<?php } ?>

<?php if(isset($_GET['wa'])){ ?>

<!-- MODAL WA -->
<div
    class="modal fade"
    id="modalWA"
    tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 rounded-4">

            <div class="modal-header">

                <h5 class="modal-title">

                    Notifikasi

                </h5>

            </div>

            <div class="modal-body">

                <?php echo $pesanAlert; ?>

                <br><br>

                Kirim invoice ke WhatsApp?

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">

                    Batal

                </button>

                <a
                    href="<?php echo urldecode($_GET['wa']); ?>"
                    target="_blank"
                    class="btn btn-success">

                    Kirim WhatsApp

                </a>

            </div>

        </div>

    </div>

</div>

<script>

document.addEventListener(
    'DOMContentLoaded',
    function(){

        let modal =
            new bootstrap.Modal(
                document.getElementById('modalWA')
            );

        modal.show();

    }
);

</script>

<?php } ?>
</body>

</html>