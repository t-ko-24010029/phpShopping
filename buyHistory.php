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

  if($_SESSION["administrator"] == 1){
    $firstAddition = true;

    //購入履歴検索
    $sql = "SELECT customerId, name, number, buyTime FROM buyhistory 
           JOIN goods ON buyhistory.goodsCode = goods.code ";
    if (@$_POST['buyer']) {
      if($firstAddition){
        $firstAddition = false;
        $sql .= "WHERE ";
      }
      else{
        $sql .= "AND ";
      }
      $sql .= "customerId = :buyer ";
    }
    if(@$_POST['goods']){
      if($firstAddition){
        $firstAddition = false;
        $sql .= "WHERE ";
      }
      else{
        $sql .= "AND ";
      }
      $sql .= "name LIKE :goods ";
    }
    if(@$_POST['dateStart']){
      if($firstAddition){
        $firstAddition = false;
        $sql .= "WHERE ";
      }
      else{
        $sql .= "AND ";
      }
      $sql .= "buyTime >= :dateStart ";
    }
    if(@$_POST['dateEnd']){
      if($firstAddition){
        $firstAddition = false;
        $sql .= "WHERE ";
      }
      else{
        $sql .= "AND ";
      }
      $sql .= "buyTime <= :dateEnd ";
    }
  }
  else{
    $sql = "SELECT customerId, name, number, buyTime FROM buyhistory 
            JOIN goods ON buyhistory.goodsCode = goods.code WHERE customerId = :id";
  }

  $stmt = $pdo->prepare($sql);
  if($_SESSION["administrator"] == 1){
    if (@$_POST['buyer']) {
      $stmt->bindValue('buyer', $_POST['buyer']);
    }
    if(@$_POST['goods']){
      $name = mb_convert_kana($_POST['goods'], "Hc");
      $name = '%'.$name.'%';
      $stmt->bindValue('goods', $name);
    }
    if(@$_POST['dateStart']){
      $stmt->bindValue('dateStart', $_POST['dateStart']);
    }
    if(@$_POST['dateEnd']){
      $stmt->bindValue('dateEnd', $_POST['dateEnd']);
    }
  }
  else{
    $stmt->bindValue('id',$_SESSION["id"]);
  }
  // $stmt = $pdo->prepare($sql);
  // if($_SESSION["administrator"] != 1){
  //   $stmt->bindValue('id',$_SESSION["id"]);
  // }
  $stmt->execute();
  $rows = $stmt;
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>購入履歴</title>
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
<h1>購入履歴</h1>
<?php if($_SESSION["administrator"] == 1){ ?>
<form action="buyHistory.php" method="post">
  <div style="margin-left:21%;">
    購入者　<input type="text" size="10" class="form-control" id="buyer" name="buyer" value = <?php echo(ISSET($_POST['buyer'])) ? $_POST['buyer'] : "" ?>>
    　商品　<input type="text" size="20" class="form-control" id="goods" name="goods" value = <?php echo(ISSET($_POST['goods'])) ? $_POST['goods'] : "" ?>>
    　購入期間　<input type="date" class="form-control" id="dateStart" name="dateStart" value = <?php echo(ISSET($_POST['dateStart'])) ? $_POST['dateStart'] : "" ?>>
    ～ <input type="date" class="form-control" id="dateEnd" name="dateEnd" value = <?php echo(ISSET($_POST['dateEnd'])) ? $_POST['dateEnd'] : "" ?>>
    <input type="submit" name="search" class="btn btn-primary" value="検索">
  </div>
</form>
<?php } ?>
<table>
  <tr><th>購入者</th><th>商品</th><th>数量</th><th>購入日</th></tr>
  <?php foreach($rows as $r) { ?>
    <tr>
      <td><?php echo $r['customerId'] ?></td>
      <td><?php echo $r['name'] ?></td>
      <td><?php echo $r['number'] ?></td>
      <td><?php echo $r['buyTime'] ?></td>
    </tr>
  <?php } ?>
  <!-- <tr><td colspan='2'> </td><td><strong>合計</strong></td><td><?php echo $sum ?> 円</td></tr> -->
</table>
<div class="base">
  <?php if($_SESSION["administrator"] == 1){ ?>
    <a href="administratorPage.php">管理者画面に戻る</a>　
  <?php }else{ ?>
    <a href="main.php">お買い物に戻る</a>　
    <a href="cart.php">カートに戻る</a>　
  <?php } ?>
</div>
</body>
</html>