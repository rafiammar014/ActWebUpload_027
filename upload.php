<?php
$target_dir = "uploads/";
$pesan_sistem = "";

// Buat folder uploads jika belum ada
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if (isset($_GET['hapus'])) {
    // Menggunakan basename() sangat PENTING untuk keamanan agar user tidak bisa 
    // melakukan directory traversal (misal: hapus=../../index.php)
    $file_to_delete = basename($_GET['hapus']); 
    $path_to_delete = $target_dir . $file_to_delete;
    
    if (file_exists($path_to_delete) && is_file($path_to_delete)) {
        if (unlink($path_to_delete)) {
            $pesan_sistem = "<div class='alert success'>Berkas <b>" . htmlspecialchars($file_to_delete) . "</b> berhasil dihapus.</div>";
        } else {
            $pesan_sistem = "<div class='alert error'>Gagal menghapus berkas.</div>";
        }
    } else {
        $pesan_sistem = "<div class='alert error'>Berkas tidak ditemukan.</div>";
    }
}

if(isset($_POST["submit"])) {
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $pesan_error = "";

    // Periksa apakah berkas sebenarnya adalah gambar
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $pesan_error .= "Berkas yang dipilih bukan gambar. ";
        $uploadOk = 0;
    }

    // Periksa apakah berkas sudah ada
    if (file_exists($target_file)) {
        $pesan_error .= "Berkas dengan nama tersebut sudah ada. ";
        $uploadOk = 0;
    }

    // Periksa ukuran berkas (500000 byte = 500KB)
    if ($_FILES["fileToUpload"]["size"] > 500000) {
        $pesan_error .= "Ukuran berkas terlalu besar (Maks 500KB). ";
        $uploadOk = 0;
    }

    // Hanya izinkan format tertentu
    if($fileType != "jpg" && $fileType != "png" && $fileType != "jpeg" && $fileType != "gif" ) {
        $pesan_error .= "Hanya format JPG, JPEG, PNG & GIF yang diperbolehkan. ";
        $uploadOk = 0;
    }

    // Jika uploadOk = 0, tampilkan pesan error
    if ($uploadOk == 0) {
        $pesan_sistem = "<div class='alert error'>Maaf, berkas gagal diunggah: " . $pesan_error . "</div>";
    } else {
        // Jika semua oke, coba unggah berkas
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $pesan_sistem = "<div class='alert success'>Berkas <b>" . htmlspecialchars( basename( $_FILES["fileToUpload"]["name"])) . "</b> berhasil diunggah.</div>";
        } else {
            $pesan_sistem = "<div class='alert error'>Maaf, terjadi kesalahan sistem saat memindahkan berkas.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Galeri & Unggah Gambar</title>
    <style>
        body { font-family: sans-serif; margin: 20px; background-color: #f4f4f9; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-group { margin-bottom: 15px; }
        #imagePreview { max-width: 300px; margin-top: 10px; display: none; border: 1px solid #ddd; padding: 5px; border-radius: 4px; }
        .gallery { display: flex; flex-wrap: wrap; gap: 15px; margin-top: 20px; }
        .card { border: 1px solid #ddd; padding: 10px; border-radius: 4px; text-align: center; background: #fff; width: 200px; }
        .card img { max-width: 100%; height: 150px; object-fit: cover; border-radius: 4px; }
        .actions { margin-top: 10px; }
        .actions a { text-decoration: none; padding: 5px 10px; border-radius: 3px; font-size: 14px; margin: 0 5px; display: inline-block;}
        .btn-download { background-color: #007bff; color: white; }
        .btn-delete { background-color: #dc3545; color: white; }
    </style>
</head>
<body>

<div class="container">
    <h2>Manajer Unggah Gambar</h2>

    <?= $pesan_sistem; ?>

    <fieldset>
        <legend>Unggah Gambar Baru</legend>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fileToUpload">Pilih gambar untuk diunggah:</label><br><br>
                <input type="file" name="fileToUpload" id="fileToUpload" accept="image/png, image/jpeg, image/gif" onchange="previewImage(event)" required>
            </div>
            
            <div class="form-group">
                <img id="imagePreview" alt="Preview Gambar" style="max-width: 300px; margin-top: 15px; display: none; border: 1px solid #ddd; padding: 5px; border-radius: 4px;"/>
            </div>

            <input type="submit" value="Unggah Sekarang" name="submit" style="padding: 10px 15px; cursor: pointer; margin-top: 10px;">
        </form>
    </fieldset>

    <hr style="margin: 30px 0;">

    <h3>Berkas yang Tersimpan</h3>
    <div class="gallery">
        <?php
        // Membaca semua file di dalam folder uploads/
        $files = scandir($target_dir);
        $has_files = false;

        foreach ($files as $file) {
            // Abaikan direktori . dan ..
            if ($file !== '.' && $file !== '..') {
                $has_files = true;
                $file_path = $target_dir . $file;
                
                echo "<div class='card'>";
                // Menampilkan Gambar
                echo "<img src='" . htmlspecialchars($file_path) . "' alt='" . htmlspecialchars($file) . "'>";
                // Menampilkan Nama File
                echo "<p style='font-size:12px; word-wrap:break-word;'>" . htmlspecialchars($file) . "</p>";
                
                echo "<div class='actions'>";
                // Tombol Unduh (Menggunakan atribut download HTML5)
                echo "<a href='" . htmlspecialchars($file_path) . "' class='btn-download' download>Unduh</a>";
                // Tombol Hapus (Mengirimkan parameter GET ?hapus=namafile)
                echo "<a href='?hapus=" . urlencode($file) . "' class='btn-delete' onclick='return confirm(\"Yakin ingin menghapus gambar ini?\")'>Hapus</a>";
                echo "</div>";
                echo "</div>";
            }
        }

        if (!$has_files) {
            echo "<p style='color: gray;'>Belum ada berkas yang diunggah.</p>";
        }
        ?>
    </div>
</div>

<script>
    function previewImage(event) {
        var reader = new FileReader();
        var imageField = document.getElementById("imagePreview");
        
        reader.onload = function() {
            if (reader.readyState === 2) {
                imageField.src = reader.result;
                imageField.style.display = "block";
            }
        }
        
        if (event.target.files && event.target.files[0]) {
            reader.readAsDataURL(event.target.files[0]);
        }
    }
</script>

</body>
</html>