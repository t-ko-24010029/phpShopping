<?php
//ファイルの読み込み
require_once "db_connect.php";
require_once "common.php";
//セッション開始
session_set_cookie_params(60 * 30 * 1);
ini_set( 'session.gc_maxlifetime', 60 * 30 * 1);
session_start();

// セッション変数 $_SESSION["loggedin"]を確認。ログイン済だったら   メインページへリダイレクト
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: main.php");
    exit;
}

//POSTされてきたデータを格納する変数の定義と初期化
$datas = [
    'id'  => '',
    'name'  => '',
    'password'  => '',
];
$login_err = "";

//GET通信だった場合はセッション変数にトークンを追加
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    setToken();
}

//POST通信だった場合はログイン処理を開始
if($_SERVER["REQUEST_METHOD"] == "POST"){
    ////CSRF対策
    checkToken();

    // POSTされてきたデータを変数に格納
    foreach($datas as $key => $value) {
        if($value = filter_input(INPUT_POST, $key, FILTER_DEFAULT)) {
            $datas[$key] = $value;
        }
    }

    // バリデーション
    $errors = validation($datas,false);
    if(empty($errors)){
        //ユーザーネームから該当するユーザー情報を取得
        $sql = "SELECT id,name,password,administrator FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue('id',$datas['id']);
        $stmt->execute();

        //ユーザー情報があれば変数に格納
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            //パスワードがあっているか確認
            if (password_verify($datas['password'],$row['password'])) {
                //セッションIDをふりなおす
                session_regenerate_id(true);
                //セッション変数にログイン情報を格納
                $_SESSION["loggedin"] = true;
                $_SESSION["id"] = $row['id'];
                $_SESSION["name"] = $row['name'];
                $_SESSION["administrator"] = $row['administrator'];
                //メインページへリダイレクト
                if($_SESSION["administrator"] == 1){
                    header("location:administratorPage.php");
                }
                else {
                    header("location:main.php");
                }
                exit();
            } else {
                $login_err = 'ユーザー名もしくはパスワードが間違っています。';
            }
        }else {
            $login_err = 'ユーザー名もしくはパスワードが間違っています。';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>

    <link rel="stylesheet" href="shopping.css">
    <style>
        body{
            font: 14px sans-serif;
        }
        .wrapper{
            width: 400px;
            padding: 20px;
            margin: 0 auto;
        }
        .form-control{
            display:block; 
            width:100%;
        }
        .alert{
            position:relative;
            padding:.75rem 1.25rem;
            margin-bottom:1rem;
            border:1px solid transparent;
            border-radius:.25rem
        }
        .alert-danger{
            color:#721c24;
            background-color:#f8d7da;
            border-color:#f5c6cb
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>ログイン</h2>
        <p>ユーザーIDとパスワードを入力してください。</p>

        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
            // echo '<div class="alert">' . $login_err . '</div>';
        }        
        ?>

        <form action="<?php echo $_SERVER ['SCRIPT_NAME']; ?>" method="post">
            <div class="form-group">
                <label>ユーザーID</label>
                <input type="text"  name="id" class="form-control <?php echo (!empty(($errors['id']))) ? 'is-invalid' : ''; ?>" value="<?php echo ($datas['id']); ?>">
                <span class="invalid-feedback"><?php echo ($errors['id']); ?></span>
            </div>    
            <div class="form-group">
                <label>パスワード</label>
                <input type="password" name="password" class="form-control <?php echo (!empty(($errors['password']))) ? 'is-invalid' : ''; ?>" value="<?php echo ($datas['password']); ?>">
                <span class="invalid-feedback"><?php echo ($errors['password']); ?></span>
            </div>
            <div class="form-group">
                <input type="hidden" name="token" value="<?php echo ($_SESSION['token']); ?>">
                <input type="submit" class="btn btn-primary" value="ログイン">
            </div>
            <p>また登録してない方は<a href="register.php">こちら</a></p>
        </form>
    </div>
</body>
</html>
