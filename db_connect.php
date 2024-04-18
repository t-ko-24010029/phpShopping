<!-- <?php
function dbConnect(){
    $dsn = 'mysql:host=localhost;dbname=shopping';
    $username = 'wei';
    $password = '1234567';
    try {
        $dbh = new PDO($dsn, $username, $password);
        // echo "<p>Succeed!</p>";
        return $dbh;
    } catch (PDOException $e) {
        // echo "<p>Failed : " . $e->getMessage()."</p>";
        // exit();
        return $db = null;
    }
}
?> -->
<?php
/* ①　データベースの接続情報を定数に格納する */
const DB_HOST = 'mysql:host=localhost;dbname=shopping';
const DB_USER = 'wei';
const DB_PASSWORD = '1234567';

//②　例外処理を使って、DBにPDO接続する
try {
    $pdo = new PDO(DB_HOST,DB_USER,DB_PASSWORD,[
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES =>false
    ]);
} catch (PDOException $e) {
    echo 'ERROR: Could not connect.'.$e->getMessage()."\n";
    exit();
}