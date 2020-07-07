<?php
function mysqli_fetch_rows($koneksi, $query)
{
    $result = array();
    $query = mysqli_query($koneksi, $query);
    while($data = mysqli_fetch_assoc($query))
    {
        $result[] = $data;
    }
    return $result;
}

function PerhitunganTopsis($koneksi, $id_bantuan)
{
    // variabel penampung hasil perhitungan
    $hasil = array(
        "normalisasi" => array(),
        "normalisasi_terbobot" => array(),
        "ideal_plus" => array(),
        "ideal_min" => array(),
        "jarak_ideal_plus" => array(),
        "jarak_ideal_min" => array(),
        "nilai_c" => array()
    );

    // variabel untuk menampung daftar kolom format c
    $daftar_kolom_keseluruhan = array();

    // buat tabel perhitungan terlebih dahulu dari tabel kriteria
    $daftar_kriteria = mysqli_fetch_rows($koneksi, "SELECT * FROM tbl_kriteria ORDER BY id_kriteria");

    // buat tabel perhitungan. tabel ini sifatnya sekali pakai. nama tabelnya harus unik
    $nama_tabel = "tbl_perhitungan_" . time();

    // untuk menampung hasil akhir kueri membuat tabel perhitungan dinamis
    $kueri_buat_tabel = "";
    // untuk menampung hasil akhir kueri yang berisi daftar kolom
    $kueri_kolom_tabel = "";
    // untuk menampung daftar kolom dalam bentuk array
    $daftar_kolom = array();

    // sematkan data kriteria ke daftar kolom agar bisa digabungkan jadi string dengan pemisah koma nantinya
    foreach ($daftar_kriteria as $kriteria) {
        $daftar_kolom[] = "`c" . $kriteria['id_kriteria'] . "` float NOT NULL";
        $daftar_kolom_keseluruhan[] = "c" . $kriteria['id_kriteria'];
    }

    // ubah daftar kolom berupa array td ke string
    $kueri_kolom_tabel = implode(",", $daftar_kolom);

    // sematkan hasil kueri kolom tadi ke kueri buat tabel
    $kueri_buat_tabel = "CREATE TABLE `" . $nama_tabel . "` (
        `id_perhitungan` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `id_bantuan` int NOT NULL,
        `id_alternatif` int NOT NULL,
        " . $kueri_kolom_tabel . "
      );";

    // jalankan kueri buat tabel
    mysqli_query($koneksi, $kueri_buat_tabel);


    // INSERT DATA NILAI KE TABEL PERHITUNGAN DIATAS
    // ...............................................
    $data_nilai_tmp = mysqli_fetch_rows($koneksi, "Select
                          tbl_bantuan.id_bantuan,
                      tbl_alternatif.id_alternatif,
                      GROUP_CONCAT(tbl_kriteria.id_kriteria ORDER BY tbl_kriteria.id_kriteria) AS id_kriteria,
                      GROUP_CONCAT(tbl_pilihan_kriteria.nilai ORDER BY tbl_kriteria.id_kriteria) AS nilai
                      From
                          tbl_bantuan Inner Join
                          tbl_alternatif On tbl_bantuan.id_bantuan = tbl_alternatif.id_bantuan Inner Join
                          tbl_pilihan_alternatif On tbl_pilihan_alternatif.id_alternatif = tbl_alternatif.id_alternatif Inner Join
                          tbl_pilihan_kriteria On tbl_pilihan_alternatif.id_pilihan_kriteria = tbl_pilihan_kriteria.id_pilihan_kriteria
                          Inner Join
                          tbl_kriteria On tbl_pilihan_kriteria.id_kriteria = tbl_kriteria.id_kriteria WHERE tbl_bantuan.id_bantuan = " . $id_bantuan . " GROUP BY tbl_alternatif.id_alternatif ORDER BY tbl_alternatif.id_alternatif, tbl_kriteria.id_kriteria");

    $data_nilai = array(); // DATA NILAI ALTERNATIF


    foreach($data_nilai_tmp as $data)
    {
        // // lakukan insert ke tabel perhitungan
        mysqli_query($koneksi, "INSERT INTO " . $nama_tabel . "(id_bantuan, id_alternatif," . implode(",", $daftar_kolom_keseluruhan) . ") VALUES ('" . $data['id_bantuan'] . "','" . $data['id_alternatif'] . "'," . $data['nilai'] . ")");
    }


    // 1. MATRIKS KEPUTUSAN TERNORMALISASI
    $kolom_matriks_ternormalisasi = array();
    foreach ($daftar_kolom_keseluruhan as $kolom)
    {
        $kolom_matriks_ternormalisasi[] = $kolom."/(SELECT SQRT(SUM(".$kolom."*".$kolom.")) FROM ".$nama_tabel.") AS ".$kolom;
    }
    $sql_data_matriks_ternormalisasi = "SELECT id_alternatif, ".implode(",", $kolom_matriks_ternormalisasi)." FROM ".$nama_tabel;
    $data_matriks_ternormalisasi = mysqli_fetch_rows($koneksi, $sql_data_matriks_ternormalisasi);
    $hasil["normalisasi"] = $data_matriks_ternormalisasi[0];


    // 2. MATRIKS HASIL KEPUTUSAN TERBOBOT
    $kolom_terbobot = array();
    foreach ($daftar_kriteria as $index => $kriteria)
    {
        $kolom_terbobot[] = "(".$kriteria["bobot"]." * (c".$kriteria["id_kriteria"]."/(SELECT SQRT(SUM(c".$kriteria["id_kriteria"]."*c".$kriteria["id_kriteria"].")) FROM ".$nama_tabel."))) AS c".$kriteria["id_kriteria"];
    }

    $sql_data_matriks_ternormalisasi_terbobot = "SELECT id_alternatif, ".implode(",", $kolom_terbobot)." FROM ".$nama_tabel;
    $data_matriks_ternormalisasi_terbobot = mysqli_fetch_rows($koneksi, $sql_data_matriks_ternormalisasi_terbobot);
    $hasil["normalisasi_terbobot"] = $data_matriks_ternormalisasi_terbobot[0];


    // 3. MATRIKS IDEAL POSITIF
    $kolom_ideal_plus = array();
    foreach ($daftar_kriteria as $index => $kriteria)
    {
        $kolom_ideal_plus[] = "MAX(ideal_plus.c".$kriteria["id_kriteria"].") AS c".$kriteria["id_kriteria"];
    }
    $sql_ideal_plus = "SELECT ".implode(",", $kolom_ideal_plus)." FROM (".$sql_data_matriks_ternormalisasi_terbobot.") ideal_plus";
    $data_ideal_plus = mysqli_fetch_rows($koneksi, $sql_ideal_plus);
    $hasil["ideal_plus"] = $data_ideal_plus[0];

    // 4. MATRIKS IDEAL NEGATIF
    $kolom_ideal_min = array();
    foreach ($daftar_kriteria as $index => $kriteria)
    {
        $kolom_ideal_min[] = "MIN(ideal_min.c".$kriteria["id_kriteria"].") AS c".$kriteria["id_kriteria"];
    }
    $sql_ideal_min = "SELECT ".implode(",", $kolom_ideal_min)." FROM (".$sql_data_matriks_ternormalisasi_terbobot.") ideal_min";
    $data_ideal_min = mysqli_fetch_rows($koneksi, $sql_ideal_min);
    $hasil["ideal_min"] = $data_ideal_min[0];


    // 5. JARAK IDEAL POSITIF
    $kolom_terbobot = array();
    $sql_jarak_ideal_plus = "";
    foreach ($daftar_kriteria as $index => $kriteria)
    {
        $kolom_terbobot[] = "POW((".$kriteria["bobot"]." * (c".$kriteria["id_kriteria"]."/(SELECT SQRT(SUM(c".$kriteria["id_kriteria"]."*c".$kriteria["id_kriteria"].")) FROM ".$nama_tabel.")) - ".$hasil["ideal_plus"]["c".$kriteria["id_kriteria"]]."), 2)";
    }
    $sql_jarak_ideal_plus = "SELECT id_alternatif, SQRT(".implode(" + ", $kolom_terbobot).") AS jarak FROM ".$nama_tabel;
    $hasil["jarak_ideal_plus"] = mysqli_fetch_rows($koneksi, $sql_jarak_ideal_plus);

    // 6. JARAK IDEAL NEGATIF
    $kolom_terbobot = array();
    $sql_jarak_ideal_min = "";
    foreach ($daftar_kriteria as $index => $kriteria)
    {
        $kolom_terbobot[] = "POW((".$kriteria["bobot"]." * (c".$kriteria["id_kriteria"]."/(SELECT SQRT(SUM(c".$kriteria["id_kriteria"]."*c".$kriteria["id_kriteria"].")) FROM ".$nama_tabel.")) - ".$hasil["ideal_min"]["c".$kriteria["id_kriteria"]]."), 2)";
    }
    $sql_jarak_ideal_min = "SELECT id_alternatif, SQRT(".implode(" + ", $kolom_terbobot).") AS jarak FROM ".$nama_tabel;
    $hasil["jarak_ideal_min"] = mysqli_fetch_rows($koneksi, $sql_jarak_ideal_min);


    // 7. KEDEKATAN JARAK ALIAS C
    $c = array();
    foreach($hasil["jarak_ideal_plus"] as $no => $jarak_plus)
    {
        $c[] = array(
            "id_alternatif" => $jarak_plus["id_alternatif"],
            "nilai" => $hasil["jarak_ideal_min"][$no]["jarak"] / ($hasil["jarak_ideal_min"][$no]["jarak"] + $jarak_plus["jarak"])
        );
    }
    $hasil["nilai_c"] = $c;

    // 8. HAPUS PERHITUNGAN SEBELUMNYA
    mysqli_query($koneksi, "DELETE FROM tbl_hasil_perhitungan WHERE id_bantuan = ".$id_bantuan);

    // 9. SIMPAN HASIL PERHITUNGAN KEDALAM TABEL tbl_hasil_perhitungan
    foreach($hasil["nilai_c"] as $nilai)
    {
        mysqli_query($koneksi, "INSERT INTO tbl_hasil_perhitungan (id_bantuan, id_alternatif, nilai) VALUES (".$id_bantuan.", ".$nilai["id_alternatif"].", '".$nilai["nilai"]."')");
    }

    // 10. HAPUS TABEL PERHITUNGAN SEMENTARA
    mysqli_query($koneksi, "DROP TABLE ".$nama_tabel);

    return $hasil;
}

/*
CONTOH PEMAKAIAN :
$koneksi = mysqli_connect('localhost', 'root', 'mysql', 'db_topsis');

PerhitunganTopsis($koneksi, 7);

*/
