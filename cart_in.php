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
  
  if (isset($_POST['code'])){
    // @$_SESSION['cart'][$_POST['code']] += $_POST['num'];
    @$_SESSION['cart'][$_POST['code']] += 0;

    // $count = 0;
    // foreach ($_SESSION['cart'] as $key => $value) {
    //   if($value != 0)
    //   $count++;
    // }
    // $_SESSION["goodsCount"] = $count;

    // if($_SESSION['cart'][$_POST['code']] > 0){
    if($_POST['num']> 0){
      $st = $pdo->prepare("SELECT stock FROM goods WHERE code = :code");
      $st->bindValue('code', $_POST['code']);
      $st->execute();
      $row = $st->fetch();
      $stock = $row['stock'];

      //購入数が在庫数を超えない場合
      if($_SESSION['cart'][$_POST['code']] + $_POST['num'] <= $stock ){
        @$_SESSION['cart'][$_POST['code']] += $_POST['num'];
      }
      else {
        @$_SESSION['cart'][$_POST['code']] = $stock;
      }

      $st = $pdo->prepare("SELECT number FROM cart WHERE id = :id AND code = :code");
      $st->bindValue('id', $_SESSION["id"]);
      $st->bindValue('code', $_POST['code']);
      $st->execute();

      if($row = $st->fetch()){
        $sql = "UPDATE cart SET number = :number WHERE id = :id AND code = :code";
        $st = $pdo->prepare($sql);
        $st->bindValue('id', $_SESSION["id"]);
        $st->bindValue('code', $_POST['code']);
        $st->bindValue('number', $_SESSION['cart'][$_POST['code']]);
        $st->execute();
      }
      else {
        $sql = "INSERT INTO cart(id, code, number) VALUES (:id, :code, :number)";
        $st = $pdo->prepare($sql);
        $st->bindValue('id', $_SESSION["id"]);
        $st->bindValue('code', $_POST['code']);
        $st->bindValue('number', $_SESSION['cart'][$_POST['code']]);
        $st->execute();
      }
    }

    $count = 0;
    foreach ($_SESSION['cart'] as $key => $value) {
      if($value != 0)
      $count++;
    }
    $_SESSION["goodsCount"] = $count;

    //$return_dataをjson形式で返す。
    echo json_encode($count);
  }
?>