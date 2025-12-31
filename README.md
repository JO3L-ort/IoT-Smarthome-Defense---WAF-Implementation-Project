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

### Layer 2 : Secure Code Hardening 
A WAF shields the system from attacks, but secure code cures the vulnerability. I transitioned the codebase to Prepared Statements, effectively inoculating the system against SQL Injection rather than just blocking the symptoms.

#### Patched Code
#### Patching 1 : Replace with Prepared Statement 
* **Vulnerable Code (Before)**
  ```<php
  // Ambil data dari form
  $username = $koneksi->real_escape_string($_POST['username']);
  $password = $koneksi->real_escape_string($_POST['password']);

  // Query cek user
  $sql = "SELECT * FROM tb_user WHERE username='$username' AND password=MD5('$password')";
  $result = $koneksi->query($sql);
  ?>
  ```
* **Patched Code**
  ```<php
  // PATCHED #1 : Removes real_escape_string function
  $username = $_POST['username'];
  $password = $_POST['password'];
  
  // PATCHED #2 : Query cek user  
  $sql = "SELECT * FROM tb_user WHERE username=?";
  $stmt = $koneksi->prepare($sql);
  
  //Patched #3 : BINDING THE INPUT 
  $stmt-> bind_param("s",$username);
  ?>
  ```
#### Patching 2 : Upgrading Password Security (MD5 to Bcrypt) 
Modernized the password storage mechanism by migrating from weak MD5 hashing to Bcrypt to align with industry-standard cryptographic practices.

* **Vulnerable Code (After )**
  ```
  $sql = "SELECT * FROM tb_user WHERE username='$username' AND password=MD5('$password')";
  ```
* **Patched Code**
  ```<php
  // Secure: Verifying input against the stored Bcrypt hash
  // Note: $hash_dari_db was fetched safely via Prepared Statement

  if (password_verify($password_plaintext, $hash_dari_db)){
	//Login is valid, proceed to login 
		//buat keterangan kalo loginnya valid 
		$_SESSION['logged_in'] = true;
   ?>
  ```
#### Patching 3 : Preventing Session FIxation 
Implemented `session_regenerate_id(true)` for mitigating **Session Fixation Attacks**

* **Vulnerable Code (Before Patching)**
  ```<php
  if ($result->num_rows > 0) {
    $_SESSION['username'] = $username;
    header("Location: index.php");
  ?>
  ```

* **Patched Code**
  ```<php
    $user = $result->fetch_assoc();
	$hash_dari_db = $user['password'];
	
	if (password_verify($password_plaintext, $hash_dari_db)){
		
	// If the login is succeed
		//FIX: Destroy the old anonymous session ID and create a fresh one
		session_regenerate_id(true);
  
		//Now safe to assign privileges
		$_SESSION['username'] = $user['username'];
  		$_SESSION['logged_in'] = true;
		header("location: index_Defense Up.php");
   ?>

  
## 🛠️ Installation & Setup
To replicate this lab environment locally, follow these steps:

**1. Clone the Repository**
```bash
git clone [https://github.com/JO3L-ort/IoT-Smarthome-Defense---WAF-Implementation-Project.git](https://github.com/JO3L-ort/IoT-Smarthome-Defense---WAF-Implementation-Project.git)
cd IoT-Smarthome-Defense---WAF-Implementation-Project
  ```

**2. Database Setup**
The project requires a MariaDB/MySQL database. You can set it up using either the command line or a GUI tool like phpMyAdmin. You could import the database with this database file 

* **Step 1:** Download or locate the file `smarthome_hackthecity_Fixed.sql` in this repository.
* **Step 2:** Import the database.
    * **Via CLI:**
        ```bash
        mysql -u root -p < smarthome_hackthecity_Fixed.sql
        ```
    * **Via phpMyAdmin:**
        1. Open phpMyAdmin.
        2. Create a database named `smarthome_hackthecity_Fixed` (if not created automatically).
        3. Go to the **Import** tab.
        4. Select `smarthome_hackthecity_Fixed.sql` and click **Go**.

* **Step 3:** Configure Connection
    Open the PHP connection file (e.g., `validasi.php` or `config.php`) and ensure the credentials match:
    ```php
    $koneksi = new mysqli("localhost", "root", "", "smarthome_hackthecity_Fixed");
    ```
## Key Takeaways
This project emphasizing the difference in defensive strength between **Network Security (WAF)** and **Appplication Security (Secure Code)**:

1. **WAF is an External Shield, Not an Internal Cure**
   
	WAF acts only as a perimeter defense and couldn't resolve system's logic flaws, even thought modsecurity could blocked standard SQLMap attacks 		                succesfully. Total reliance on WAF could makes the risk of bypass attacks.
		
2. **Root Cause vs. Symptom Treatment:**
   
	The only way to fundamentally eliminate _SQL Injection_ risks is patching the code with **Prepared Statement**. It treats the root cause by separating SQL         syntax from user data, whereas WAF only treats the symptom (malicious traffic).
		
3. **The Value of Offensive Knowledge:**
    Simulating the attack first (Red Teaming) was crucial to understanding exactly *what* needed to be protected. You cannot effectively defend what you do 		 not know how to break.

## ⚠️ Legal Disclaimer
**FOR EDUCATIONAL PURPOSES ONLY.**

The attacks, tools, and techniques demonstrated in this project were performed in a controlled, isolated lab environment created specifically for this research.
* **Target:** Localhost/Virtual Machine owned by the author.
* **Intent:** To analyze vulnerabilities and implement defensive measures (Blue Team focus).

The author is not responsible for any misuse of the information provided. All activities comply with ethical hacking guidelines and standard legal frameworks.
