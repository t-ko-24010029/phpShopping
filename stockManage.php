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

  //在庫更新
  $errors = [];
  $addCode = $stock = $codeStock = '';
  if (@$_POST['addCode']) {
    $addCode = htmlspecialchars($_POST['addCode']);
    $codeStock = $addCode."stock";
    $stock = htmlspecialchars($_POST[$codeStock]);

    if (!preg_match('/^[0-9]+$/', $stock))
        $errors[$codeStock] = '数字のみで入力してください。';
    else if($stock == "") 
        $errors[$codeStock] = '在庫を入力してください。';

    if (empty($errors)) {
      $st = $pdo->prepare("UPDATE goods SET stock = :stock WHERE code = :code");
      $st->bindValue('stock', $stock);
      $st->bindValue('code', $addCode);
      $st->execute();
    }
  }

  //商品検索
  $rows = array();
  $sql = "SELECT code, name, stock FROM goods ";
  $stmt = $pdo->prepare($sql);
  if (@$_POST['goodsCode'] and @$_POST['goodsName']) {
    $sql .= "WHERE code = :goodsCode AND searchName LIKE :goodsName";
    $stmt = $pdo->prepare($sql);
    $name = mb_convert_kana($_POST['goodsName'], "Hc");
    $name = '%'.$name.'%';
    $stmt->bindValue('goodsCode', $_POST['goodsCode']);
    $stmt->bindValue('goodsName', $name);
  }
  else if(@$_POST['goodsCode']){
    $sql .= "WHERE code = :goodsCode ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('goodsCode', $_POST['goodsCode']);
  }
  else if(@$_POST['goodsName']){
    $sql .= "WHERE searchName LIKE :goodsName ";
    $stmt = $pdo->prepare($sql);
    $name = mb_convert_kana($_POST['goodsName'], "Hc");
    $name = '%'.$name.'%';
    $stmt->bindValue('goodsName', $name);
  }

  // $rows = array();
  // $sql = "SELECT code, name, stock FROM goods";
  
  // $stmt = $pdo->prepare($sql);
  $stmt->execute();
  $rows = $stmt;
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>在庫管理</title>
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
      background-color: #eee;
      border-collapse: collapse;
    }
    th {
      text-align: left;
    }
    th, td {
      margin: 0;
      padding: 6px 16px;
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
<h1>在庫管理</h1>
<form action="stockManage.php" method="post">
  <div style="margin-left:33%;">
    商品番号　<input type="text" size="10" class="form-control" id="goodsCode" name="goodsCode" value = <?php echo(ISSET($_POST['goodsCode'])) ? $_POST['goodsCode'] : "" ?>>
    　商品名　<input type="text" size="20" class="form-control" id="goodsName" name="goodsName" value = <?php echo(ISSET($_POST['goodsName'])) ? $_POST['goodsName'] : "" ?>>
    <input type="submit" name="search" class="btn btn-primary" value="検索">
  </div>
</form>
<form action="stockManage.php" method="post">
<table>
  <tr><th>商品番号</th><th>商品</th><th>在庫</th><th>在庫調整</th></tr>
  <?php foreach($rows as $r) { ?>
    <tr>
      <?php 
        $codeStock = $r['code']."stock";
        if(array_key_exists($codeStock, $_POST) and $_POST[$codeStock])
          $stock=htmlspecialchars($_POST[$codeStock]);
        else 
          $stock = "";
      ?>
      <td><?php echo $r['code'] ?></td>
      <td><?php echo $r['name'] ?></td>
      <td><?php echo $r['stock'] ?></td>
      <td>
        <input type="text" size="3" name="<?php echo $r['code'] ?>stock" class="form-control <?php echo (!empty(($errors[$codeStock]))) ? 'is-invalid' : ''; ?>" value="<?php echo $stock; ?>">
        <button type="submit" class="btn btn-primary" name="addCode" value=<?php echo $r['code'] ?>>調整</button>
      </td>
    </tr>
  <?php } ?>
</table>
</form>
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