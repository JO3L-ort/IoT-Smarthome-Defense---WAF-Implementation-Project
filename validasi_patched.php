<?php
session_start();


// Koneksi database
$koneksi = new mysqli("localhost", "admin_db", "password123", "smarthome_hackthecity");

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

// PERBAIKAN #1 : Hilangkan fungsi real_escape_string
$username = $_POST['username'];
$password = $_POST['password'];

// Query cek user (PERBAIKAN #1) 
$sql = "SELECT * FROM tb_user WHERE username=?";
$stmt = $koneksi->prepare($sql);

//PERBAIKAN #2 : BIND 
$stmt-> bind_param("s",$username);  //inputnya diikat sebagai "string" ke placeholder

// PERBAIKAN #3 
$stmt->execute(); 
$result=$stmt->get_result();

// PERBAIKAN #4 : VERIFIKASI

$password_plaintext = $_POST['password'];


if ($result->num_rows > 0){
	$user = $result->fetch_assoc();
	$hash_dari_db = $user['password'];
	
	//1. Dilakukan verifikasi katasandi terlebih dahulu
	if (password_verify($password_plaintext, $hash_dari_db)){
		
	// JIKA LOGIN NYA BERHASIL 
		
		//ID session perlu diregenerasi agar mencegah Session Fixation
		session_regenerate_id(true);
		
		//buat keterangan kalo loginnya valid 
		$_SESSION['logged_in'] = true;
		
		//Dibuat sesi yang diisi dengan data pengguna yang relevan
		$_SESSION['username'] = $user['username'];
		
		//Setelah sesi selesai dibuat, maka dapat dialihkan ke dalam halaman admin
		header("location: index_patched.php");
		exit(); 
	} else{
	// JIKA LOGIN GAGAL 
		//Semua variabel sesi yang kemungkinan muncul, akan dihapus 
		session_unset(); 
		
		//Menghancurkan sesi sepenuhnya yang dibuat pada saat proses login
		session_destroy(); 
		
		echo "<script>alert('username atau password Salah!');window.location='login.php'</script>";
	}
}else{
	echo "<script>alert('username atau password Salah!');window.location='login.php'</script>";
}

$stmt->close();
$koneksi->close();


?>

