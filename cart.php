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

  $rows = array();
  $sum = 0;
  // if (!isset($_SESSION['cart']))
  //   $_SESSION['cart'] = array();

  // foreach($_SESSION['cart'] as $code => $num) {
  //   $st = $pdo->prepare("SELECT * FROM goods WHERE code=?");
  //   $st->execute(array($code));
  //   $row = $st->fetch();
  //   $st->closeCursor();
  //   $row['num'] = strip_tags($num);
  //   $sum += $num * $row['price'];
  //   $rows[] = $row;
  // }

  $sql = "SELECT code, number FROM cart WHERE id = :id";
  $stmt = $pdo->prepare($sql);
  $stmt->bindValue('id',$_SESSION["id"]);
  $stmt->execute();

  //カート情報があれば変数に格納
  foreach ($stmt as $data) {
    $st = $pdo->prepare("SELECT * FROM goods WHERE code = :code");
    $st->bindValue('code', $data['code']);
    $st->execute();
    $row = $st->fetch();
    $st->closeCursor();
    $row['num'] = strip_tags($data['number']);
    $sum += $data['number'] * $row['price'];
    $rows[] = $row;
  }
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>カート</title>
<link rel="stylesheet" href="shopping.css">
  <style>
    h1 {
      margin: 10px 0;
      padding: 0;
      font-size: 32px;
      text-align: center;
    }
    table {
      width: 500px; 
      margin: 15px auto;
      padding: 0;
      /* background-color: #fff; */
      background-color: #eee;
      border-collapse: collapse;
    }
    th {
      text-align: left;
    }
    th, td {
      margin: 0;
      padding: 6px 16px;
      /* border-bottom: 12px solid #bbb; */
      border-bottom: 12px solid #fff;
    }
    .goods {
      font-weight: bold;
    }
    .base {
      width: 480px; 
      margin: 15px auto;
      padding: 10px;
    } 
  </style>
</head>
<body>
<h1>カート</h1>
<table>
  <tr><th>商品名</th><th>単価</th><th>数量</th><th>小計</th></tr>
  <?php foreach($rows as $r) { ?>
    <tr>
      <td><?php echo $r['name'] ?></td>
      <td><?php echo $r['price'] ?></td>
      <td><?php echo $r['num'] ?></td>
      <td><?php echo $r['price'] * $r['num'] ?> 円</td>
    </tr>
  <?php } ?>
  <tr><td colspan='2'> </td><td><strong>合計</strong></td><td><?php echo $sum ?> 円</td></tr>
</table>
<div class="base">
  <a href="main.php">お買い物に戻る</a>　
  <a href="cart_empty.php">カートを空にする</a>　
  <a href="buy.php">購入する</a>　
  <a href="buyHistory.php">購入履歴</a>
</div>
</body>
</html>