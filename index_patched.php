<?php
// dashboard.php
session_start();


//Mengecek apakah sesi $_SESSION['logged_in'] ada dan bernilai "true"
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true){

//KALO TIDAK ADA
	// Menghancurkan sesi yang berpotensi disalahgunakan
	session_unset();
	session_destroy();
	
	//Kemudian, Melakukan pengalihan secara paksa menuju halaman login
	header("location: /smart/login.php?error=unauthorized");	
	exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Web-SMARTHOME</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">	
	<link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
	<script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>
	<!---<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.js" type="text/javascript"></script>
	       <!--//Bagian ini harus dihapus -->
	
	
	<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.1.0/paho-mqtt.js" integrity="sha512-p8OrcnawEEpEiY7S96nMeaShY5AMcdRFYgnaR0ZmOgMSgL9pLNE4x5+MB0FTol7GeJGzEd9m4MAmmD8urOscvQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>-->
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="#">Smarthome</a>
    </div>
    <ul class="nav navbar-nav navbar-right">
      <li>
        <a href="#">
          <span class="glyphicon glyphicon-user"></span>
          <?php echo htmlspecialchars($_SESSION['username']); ?>
        </a>
      </li>
      <li>
        <a href="logout.php">
          <span class="glyphicon glyphicon-log-out"></span> Logout
        </a>
      </li>
    </ul>
  </div>
</nav>
	
<div class="container">	
	<div class="row">
		<div class="col-md-3">
			<div class="panel panel-warning">
				<div class="panel-heading">LAMPU KAMAR 1</div>
				<div class="panel-body">
					<center>
						<input type="checkbox" id="lampu" data-width="120" data-toggle="toggle">
						<br>
						<br>
						Status : <span id="status_lampu"> </span>
					<center>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="panel panel-success">
				<div class="panel-heading">LAMPU KAMAR 2</div>
				<div class="panel-body">
					<center>
						<input type="checkbox" id="lampu2" data-width="120" data-toggle="toggle">
						<br>
						<br>
						Status : <span id="status_lampu2"> </span>
					<center>
				</div>
			</div>
		</div>		
		<div class="col-md-6">
			<div class="panel panel-primary">
				<div class="panel-heading">Alarm Suhu – Speaker Peringatan Dini</div>
				<div class="panel-body">
					<small><i><strong>Status peringatan dini</strong> akan aktif apabila suhu melebihi <strong>30 °C.</strong><br>
							Fitur ini dapat <strong>diaktifkan atau dinonaktifkan</strong> sesuai kebutuhan.<br>
							Di bawahnya tersedia <strong>tombol uji suara</strong> untuk memastikan speaker berfungsi normal.
					</i><small>
					<center>
						
						<br>
						<input type="checkbox" id="alarmsuhu" data-width="120" data-toggle="toggle">
						<br>
						<br>
						Status : <span id="status-info"> </span>
						<br>
						<br>
						<button id="btn-uji-speaker" class="btn btn-warning">Uji Speaker</button>
					<center>
				</div>
			</div>
		</div>		
		
		<div class="col-md-12">
			<div class="panel panel-primary">
				<div class="panel-heading">SUHU KAMAR 1</div>
				<div class="panel-body">
					<center>
						Nilai Sensor : <span id="sensor"></span>
						<br>
						<div id="chart"></div>
					<center>
				</div>
			</div>
		</div>	

		<div class="col-md-12">
			<div class="panel panel-warning">
				<div class="panel-heading">KELEMBABAN KAMAR 1</div>
				<div class="panel-body">
					<center>
						Nilai Sensor : <span id="sensor_kelembaban"></span>
						<br>
						 <canvas id="chart_kelembaban" width="600" height="300"></canvas>
					<center>
				</div>
			</div>
		</div>			
	</div>
	
	<!--
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">Status</div>
				<div class="panel-body">
					<div id="status"></div>
				</div>
			 </div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">Log. Data</div>
				<div class="panel-body">
					<div id="ws" style="font-family: Courier New, Courier, monospace; font-size: 12px; font-weight: bold;"></div>
				</div>
			 </div>
		</div>
	</div>
	-->
</div>


<!--Morris JavaScript -->
<script src="js/grafik/raphael/raphael-min.js"></script>
<script src="js/grafik/morrisjs/morris.js"></script>
<script src="js/config_Defense Up.js" type="text/javascript"></script>

<script src="js/grafik.js" type="text/javascript"></script>
<!--<script src="js/websockets.js" type="text/javascript"></script>
     <!--Bagian ini harus dihapus--> 
     
<script>
    // Fungsi BARU yang aman untuk mengirim perintah ke Server PHP
    async function kirimPerintahKeServer(topic, perintah) {
        try {
            // Mengirim ke API backend, bukan ke broker MQTT.
            // Harusnya tidak ada kredensial yang terekspos.
            await fetch('/smart/api/perintah.php', { // Pastikan URL ini benar
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                // Mengirim data sebagai JSON
                body: JSON.stringify({ 
                    'topic': topic, 
                    'perintah': perintah 
                })
            });
            console.log("Perintah " + perintah + " terkirim ke server.");
        } catch (error) {
            console.error("Gagal mengirim perintah:", error);
        }
    }

    
    $("#lampu").change(function(){	
        if($('#lampu').prop('checked')) {
            // Memanggil fungsi BARU yang aman
            kirimPerintahKeServer("/hackthecity/lampu/perintah", "0"); 
        } else {			
            // Memanggil fungsi BARU yang aman
            kirimPerintahKeServer("/hackthecity/lampu/perintah", "1");
        }
    });
</script>

<script>
    const data_kelembaban = {
      labels: [],
      datasets: [{
        label: "Humidity (%)",
        borderColor: "orange",
        data: [],
        fill: false
      }]
    };

    const ctx_kelembaban = document.getElementById("chart_kelembaban").getContext("2d");
    const chart_kelembaban = new Chart(ctx_kelembaban, {
      type: 'line',
      data: data_kelembaban,
      options: {
        scales: {
          x: { title: { display: true, text: 'Time' } },
          y: { min: 0, max: 100, title: { display: true, text: 'Humidity (%)' } }
        }
      }
    });

  </script>
  
 	
	<script>
		$('#btn-uji-speaker')
		  .on('mousedown', function () {
		   $.ajax({
					url: "get_buzzer.php",   // file PHP
					type: "GET",
					dataType: "json",           // response diharapkan JSON
					success: function(data){
					   console.log(data);
					   sendmesg(data["perangkat_topik"],"a");
					   //console.log(data[3]);	   
					},
					error: function(xhr, status, error){
						console.error("AJAX Error:", error);
					}
				});
		  })
		  .on('mouseup', function () {
		   $.ajax({
					url: "get_buzzer.php",   // file PHP
					type: "GET",
					dataType: "json",           // response diharapkan JSON
					success: function(data){
					   console.log(data);
					   sendmesg(data["perangkat_topik"],"b");
					   //console.log(data[3]);	   
					},
					error: function(xhr, status, error){
						console.error("AJAX Error:", error);
					}
				});
		  });
	</script>
   
	<script>
		$('#alarmsuhu').prop('checked', 0);
		$("#alarmsuhu").change(function () {
			const status = $(this).prop('checked') ? 1 : 0; // 1=aktif, 0=nonaktif
			console.log(status);

			$.ajax({
				url: "status_buzzer.php?status="+status, // kirim status ke PHP
				type: "GET",
				dataType: "json",
				success: function (data) {
					console.log("Response:", data);
					const payload = status ? 'c' : 'd';
					sendmesg("/hackthecity/speaker/status",payload);
				},
				error: function (xhr, textStatus, errorThrown) {
					console.error("AJAX Error:", textStatus, errorThrown);
				}
			});
		});
	</script>

	<script>
		let isInit = true;

		$(document).ready(function () {

		  function loadStatusBuzzer() {
			$.ajax({
			  url: 'get_status_buzzer.php',
			  type: 'GET',
			  dataType: 'json',
			  success: function (res) {
				console.log(res);
				if (res && res.success === true) {
				  const aktif = Number(res.status) === 1; // 0/1 -> boolean
				  $('#alarmsuhu').prop('checked', aktif);
				  $('#status-info').text(aktif ? 'Aktif' : 'Nonaktif');
				} else {
				  console.warn(res?.message || 'Gagal mengambil status');
				  $('#status-info').text('Status tidak diketahui');
				}
			  },
			  error: function () {
				$('#status-info').text('Gagal memuat status');
			  },
			  complete: function () {
				isInit = false;
			  }
			});
		  }

		  // Jalankan pertama kali
		  loadStatusBuzzer();

		  // Ulangi setiap 3 detik (3000 ms)
		  setInterval(loadStatusBuzzer, 2000);
		});
	</script>
</body>
</html>
