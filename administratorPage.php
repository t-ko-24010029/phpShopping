<?php
  //セッション開始
  session_set_cookie_params(60 * 30 * 1);
  ini_set( 'session.gc_maxlifetime', 60 * 30 * 1);
  session_start();

  // セッション変数 $_SESSION["loggedin"]を確認。ログイン済じゃなかったらだったらログインページへリダイレクト
  if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
  }
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>管理者</title>
<link rel="stylesheet" href="shopping.css">
  <style>
    .base {
      width: 480px; 
      margin: 15px auto;
      padding: 10px;
      text-align: center;
    } 
  </style>
</head>
<body>
<div class="base">
  <a href="goodsRegister.php">新商品登録</a>　
  <a href="stockManage.php">在庫管理</a>　
  <a href="buyHistory.php">購入履歴</a>　
  <a href="main.php">一般画面</a>　
</div>
</body>
</html>