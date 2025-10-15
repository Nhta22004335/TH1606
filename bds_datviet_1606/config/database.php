<?php
    
    function ketnoicsdl() {
        $host = "host.docker.internal";
        // $host = "localhost"; 
       
        $port = "5432";
        $dbname = "csdl_bds";
        $user = "postgres";
<<<<<<< HEAD

        $pass = "22004335";
        //  $pass = "123456";
=======
        $pass = "123456";
>>>>>>> 486f23af8933f6c2ad0429ade0a98d7706dd1e15

        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            return $pdo;
        } catch (PDOException $e) {
            die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
        }
    }
?>