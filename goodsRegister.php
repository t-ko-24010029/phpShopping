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
  $errors = [];
  $name = $searchName = $price = $comment = $stock = $photo = '';
  if (@$_POST['register']) {
    $name = htmlspecialchars($_POST['name']);
    $searchName = htmlspecialchars($_POST['searchName']);
    $price = htmlspecialchars($_POST['price']);
    $stock = htmlspecialchars($_POST['stock']);
    $comment = htmlspecialchars($_POST['comment']);

    if (!$name) 
      $errors['name'] = '商品名を入力してください。';
    if (!$searchName) 
      $errors['searchName'] = '商品検索名を入力してください。';
    if (!$price) 
      $errors['price'] = '価格を入力してください。';
    else if(!is_numeric($price))
      $errors['price'] = '数字のみで入力してください。';
    if (!$stock) 
      $errors['stock'] = '在庫を入力してください。';
    else if(!preg_match('/^[0-9]+$/', $stock))
      $errors['stock'] = '数字のみで入力してください。';
    if (!$comment) 
      $errors['comment'] = '説明を入力してください。';
    // if (!is_uploaded_file($_FILES['file_upload']['tmp_name'])) {
    //   $errors['photo'] = '画像ファイルを選択してください。';
    // }
    // else if($_FILES['file_upload']['error']){
    //   $errors['photo'] = $_FILES['file_upload']['error'];
    // }
    // else{ 
    //   $photo = $_FILES['file_upload'];

    //   $rest = array('jpg', 'jpeg', 'png', 'gif' );
    //   $ext = strtolower(pathinfo($_FILES['file_upload']['name'], PATHINFO_EXTENSION) );
    //   if(!in_array( $ext, $rest )){
    //     $errors['photo'] = '許可されていないファイル形式です。';
    //   }

    //   if($_FILES['file_upload']['size'] > 1024 * 1024 * 10){
    //     $errors['photo'] = '10MB以下のファイルを選択してください。';
    //   }
    // }

    if (empty($errors)) {
      //商品名重複チェック
      $st = $pdo->prepare("SELECT COUNT(code) AS total FROM goods WHERE name = :name");
      $st->bindValue('name', $name);
      $st->execute();
      $row = $st->fetch();
    
      if($row['total'] > 0){
        $errors['name'] = '登録したことある商品名です。';
      }
      else{
        // //商品をDBに登録
        // $st = $pdo->prepare("INSERT INTO goods(name, searchName, price, comment, stock) VALUES (:name, :searchName, :price, :comment, :stock)");
        // $st->bindValue('name', $name);
        // $st->bindValue('searchName', $searchName);
        // $st->bindValue('price', $price);
        // $st->bindValue('stock', $stock);
        // $st->bindValue('comment', $comment);
        // $st->execute();

        // //商コードを取得
        // $st = $pdo->prepare("SELECT code FROM goods WHERE name = :name");
        // $st->bindValue('name', $name);
        // $st->execute();
        // $goods = $st->fetch();

        // //アップロードした画像を保存先に移動
        // $storeDir = '../shopping/images/';
        // $filename = $goods['code'].'.jpg';
        
        // move_uploaded_file($_FILES['file_upload']['tmp_name'], $storeDir.$filename);

        // header("location: goodsRegisterComplete.php");
        // exit();

        $_SESSION["registerName"] = $name;
        $_SESSION["registerSearchName"] = $searchName;
        $_SESSION["registerPrice"] = $price;
        $_SESSION["registerStock"] = $stock;
        $_SESSION["registerComment"] = $comment;

        header("location: goodsRegisterForPicture.php");
        exit();
      }
    }
  }
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>商品登録</title>
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
    .feedback{
      width:100%;
      margin-top:.50rem;
      font-size:80%;
      color:#dc3545
    }
    input[type=file]::file-selector-button {
      border: 2px solid #6c5ce7;
      border: 2px solid #007bff;
      padding: 0.2em 0.4em;
      border-radius: 0.2em;
      /* background-color: #a29bfe; */
      background-color: #007bff;
      transition: 1s;
      color:#fff;
    }
    input[type=file]::file-selector-button:hover{
      color:#fff;
      background-color:#0069d9;
      border-color:#0062cc;
      cursor:pointer;
    }
    input[type=file]::file-selector-button:focus{
      color:#fff;
      background-color:#0069d9;
      border-color:#0062cc;
      box-shadow:0 0 0 .2rem rgba(38,143,255,.5);
    }
</style>
</head>
<body>
<h1>商品登録</h1>
<div class="base">
  <!-- <?php if ($error) echo "<span class=\"error\">$error</span>" ?> -->
  <form enctype="multipart/form-data" action="goodsRegister.php" method="post">
    <p>
      商品名<br>
      <input type="text" size="50" name="name" class="form-control <?php echo (!empty(($errors['name']))) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
      <br><span class="invalid-feedback"><?php if (!empty(($errors['name']))) echo ($errors['name']); ?></span>
    </p>
    <p>
      商品検索用名<br>
      <input type="text" size="50" name="searchName" class="form-control <?php echo (!empty(($errors['searchName']))) ? 'is-invalid' : ''; ?>" value="<?php echo $searchName; ?>">
      <br><span class="invalid-feedback"><?php if (!empty(($errors['searchName']))) echo ($errors['searchName']); ?></span>
    </p>
    <p>
      価格<br>
      <input type="text" size="50" name="price" class="form-control <?php echo (!empty(($errors['price']))) ? 'is-invalid' : ''; ?>" value="<?php echo $price; ?>">
      <br><span class="invalid-feedback"><?php if (!empty(($errors['price']))) echo ($errors['price']); ?></span>
    </p>
    <p>
      在庫<br>
      <input type="text" size="50" name="stock" class="form-control <?php echo (!empty(($errors['stock']))) ? 'is-invalid' : ''; ?>" value="<?php echo $stock; ?>">
      <br><span class="invalid-feedback"><?php if (!empty(($errors['stock']))) echo ($errors['stock']); ?></span>
    </p>
    <p>
      説明<br>
      <textarea row = "50" cols="52" name="comment" class="form-control <?php echo (!empty(($errors['comment']))) ? 'is-invalid' : ''; ?>"><?php echo $comment; ?></textarea>
      <br><span class="invalid-feedback"><?php if (!empty(($errors['comment']))) echo ($errors['comment']); ?></span>
    </p>
    <!-- <p>
      画像<br>
      <input name="file_upload" type="file" value="<?php echo $photo; ?>">
      <br><span class="feedback"><?php if (!empty(($errors['photo']))) echo ($errors['photo']); ?></span>
    </p> -->
    <p><br>
      <input type="submit" class="btn btn-primary" name="register" value="登録">
    </p>
  </form>
</div>
<div class="base">
  <a href="administratorPage.php">管理者画面に戻る</a>　
</div>
</body>
</html>