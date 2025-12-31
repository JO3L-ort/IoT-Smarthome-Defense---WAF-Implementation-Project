# IoT-Smarthome-Defense---WAF-Implementation-Project
IoT Security Lab: Simulating SQL Injection attacks on a Smart Home Control Panel and implementing defense using ModSecurity WAF &amp; Secure Coding.

This Project simulates _cyber attacks_ into IoT Smarthome panel website and builds some defender system using _ModSecurity_ (WAF). The objective is to compare the effectiveness of Native PHP code versus WAF in mitigating OWASP Top 10 vulnerabilities (specifically SQL Injection).

Tech Stack: 
<p align="center">
  <img src="https://img.shields.io/badge/OS-Kali_Linux-268BEE?style=for-the-badge&logo=kalilinux&logoColor=white" />
  <img src="https://img.shields.io/badge/Server-Apache-D22128?style=for-the-badge&logo=apache&logoColor=white" />
  <img src="https://img.shields.io/badge/Database-MariaDB-003545?style=for-the-badge&logo=mariadb&logoColor=white" />
  <img src="https://img.shields.io/badge/Code-PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <br>
  <img src="https://img.shields.io/badge/Security-ModSecurity_WAF-006d77?style=for-the-badge&logo=security&logoColor=white" />
  <img src="https://img.shields.io/badge/Tool-SQLMap-red?style=for-the-badge&logo=hackthebox&logoColor=white" />
</p>

## Attacks Simulation (Before Defense)
Instead of a blind attack, I executed a structured 3-stage exploitation process to compromise the IoT panel:
1.  **Discovery:** Identified an unsanitized input parameter (`id`) in the device configuration page.
2.  **Enumeration:** Mapped the database structure to find the specific table storing admin credentials (`tb_user`).
3.  **Exfiltration (The Final Blow):** Dumped the username and hashed passwords to gain unauthorized access.

**Execution Command**
sqlmap -u "http://localhost/Dumb/validasi.php" --data="username=test&password=test" -D smarthome_hackthecity -T tb_user -C "username,password" --dump --batch --dbs  --random-agent --tamper=space2comment
<img width="522" height="184" alt="Leaked Smarthome Dump Database" src="https://github.com/user-attachments/assets/ab4e44b7-313e-4ca8-ab98-42ac52fbf455" />

## Defense Implementation
To mitigate the identified vulnerabilities, I implemented a two-layer security approach:

### Layer 1 : WAF Implementation 
I deployed **ModSecurity** as a Web Application Firewall (WAF) for blocking some malicious traffic immediately before handles the source code

* **Action:**
   Set the Configuration from **Passive Detection** to **Active Blocking** mode. This Ensure that any traffic request matching the attack signature immediately dropped with a 403 error, rather than just being logged.
*  **Configuration Change**
     ```
     apache
    # Example of Custom Rule to Block SQLi
    SecRule ARGS "UNION SELECT" "id:1001,deny,status:403,msg:'SQLi Detected'"
    ```
*  **Result**
     The server now responds with **403 Forbidden** when SQLMap attempts to scan the target.
   <img width="429" height="160" alt="SQLi payload_has been blocked by Firewall" src="https://github.com/user-attachments/assets/3911abf5-068d-4147-895a-0ebad53536fa" />

## Layer 2 : Secure Code Hardening 
A WAF shields the system from attacks, but secure code cures the vulnerability. I transitioned the codebase to Prepared Statements, effectively inoculating the system against SQL Injection rather than just blocking the symptoms.

#### Patched Code
##### Replace with Prepared Statement 
* **Vulnerable Code (Before)**
  ```
  // Ambil data dari form
  $username = $koneksi->real_escape_string($_POST['username']);
  $password = $koneksi->real_escape_string($_POST['password']);

  // Query cek user
  $sql = "SELECT * FROM tb_user WHERE username='$username' AND password=MD5('$password')";
  $result = $koneksi->query($sql);
  ```
* **Patched Code**
  ```
  // PERBAIKAN #1 : Hilangkan fungsi real_escape_string
  $username = $_POST['username'];
  $password = $_POST['password'];
  
  // Query cek user (patched #1) 
  $sql = "SELECT * FROM tb_user WHERE username=?";
  $stmt = $koneksi->prepare($sql);
  
  //Patched #2 : BINDING THE INPUT 
  $stmt-> bind_param("s",$username);  
  ```
