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

//   $errors = ['name', 'address', 'tel'];
  $errors = [];
  $name = $address = $tel = '';
  if (@$_POST['buy']) {
    $name = htmlspecialchars($_POST['name']);
    $address = htmlspecialchars($_POST['address']);
    $tel = htmlspecialchars($_POST['tel']);

    if (!$name) 
      $errors['name'] = 'お名前を入力してください。';
    if (!$address) 
      $errors['address'] = 'ご住所を入力してください。';
    if (!$tel) 
      $errors['tel'] = '電話番号を入力してください。';
    if (preg_match('/[^\d-]/', $tel)) 
    $errors['tel'] = '電話番号が正しくありません。';

    if (empty($errors)) {

      //在庫を減らす
      $st = $pdo->prepare("UPDATE goods SET stock = stock - (SELECT number FROM cart WHERE cart.code = goods.code)
                           WHERE code IN (SELECT code FROM cart WHERE id = :id);");
      $st->bindValue('id', $_SESSION["id"]);
      $st->execute();

      //購入履歴に登録
      $st = $pdo->prepare("INSERT INTO buyhistory (customerId, goodsCode, number, buyTime) SELECT id, code, number, NOW() FROM cart WHERE id = :id");
      $st->bindValue('id', $_SESSION["id"]);
      $st->execute();

      //カートをクリア
      $st = $pdo->prepare("DELETE FROM cart WHERE id = :id");
      $st->bindValue('id', $_SESSION["id"]);
      $st->execute();

      header("location: buy_complete.php");
      exit();
    }
  }
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>購入</title>
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
<h1>購入</h1>
<div class="base">
  <!-- <?php if ($error) echo "<span class=\"error\">$error</span>" ?> -->
  <form action="buy.php" method="post">
    <p>
      お名前<br>
      <!-- <input type="text" name="name" value="<?php echo $name ?>"> -->
      <input type="text" size="50" name="name" class="form-control <?php echo (!empty(($errors['name']))) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
      <br><span class="invalid-feedback"><?php if (!empty(($errors['name']))) echo ($errors['name']); ?></span>
    </p>
    <p>
      ご住所<br>
      <!-- <input type="text" name="address" size="60" value="<?php echo $address ?>"> -->
      <input type="text" size="50" name="address" class="form-control <?php echo (!empty(($errors['address']))) ? 'is-invalid' : ''; ?>" value="<?php echo $address; ?>">
      <br><span class="invalid-feedback"><?php if (!empty(($errors['address']))) echo ($errors['address']); ?></span>
    </p>
    <p>
      電話番号<br>
      <!-- <input type="text" name="tel" value="<?php echo $tel ?>"> -->
      <input type="text" size="50" name="tel" class="form-control <?php echo (!empty(($errors['tel']))) ? 'is-invalid' : ''; ?>" value="<?php echo $tel; ?>">
      <br><span class="invalid-feedback"><?php if (!empty(($errors['tel']))) echo ($errors['tel']); ?></span>
    </p>
    <p>
      <input type="submit" class="btn btn-primary" name="buy" value="購入">
    </p>
  </form>
</div>
<div class="base">
  <a href="index.php">お買い物に戻る</a>　
  <a href="cart.php">カートに戻る</a>
</div>
</body>
</html>