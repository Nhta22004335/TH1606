<?php
    function ketnoicsdl() {
        $host = "host.docker.internal"; 
        $port = "5432";
        $dbname = "csdl_bds";
        $user = "postgres";
        $pass = "22004335";

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