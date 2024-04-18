<?php
  //ファイルの読み込み
  require_once "db_connect.php";

  //セッション開始
  session_set_cookie_params(60 * 30 * 1);
  ini_set( 'session.gc_maxlifetime', 60 * 30 * 1);
  session_start();

  // セッション変数 $_SESSION["loggedin"]を確認。ログイン済じゃなかったらだったらログインページへリダイレクト
  if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
  }
  
  $_SESSION['cart'] = null;

  $sql = "DELETE FROM cart WHERE id = :id";
  $st = $pdo->prepare($sql);
  $st->bindValue('id', $_SESSION["id"]);
  $st->execute();

  header('Location: cart.php');
?>