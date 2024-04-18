<?php
//ファイルの読み込み
require_once "db_connect.php";
require_once "common.php";

//セッションの開始
session_set_cookie_params(60 * 30 * 1);
ini_set( 'session.gc_maxlifetime', 60 * 30 * 1);
session_start();

//POSTされてきたデータを格納する変数の定義と初期化
$datas = [
    'id'  => '',
    'name'  => '',
    'password'  => '',
    'confirm_password'  => ''
];

//GET通信だった場合はセッション変数にトークンを追加
if($_SERVER['REQUEST_METHOD'] != 'POST'){
    setToken();
}
//POST通信だった場合はDBへの新規登録処理を開始
if($_SERVER["REQUEST_METHOD"] == "POST"){
    //CSRF対策
    checkToken();

    // POSTされてきたデータを変数に格納
    foreach($datas as $key => $value) {
        if($value = filter_input(INPUT_POST, $key, FILTER_DEFAULT)) {
            $datas[$key] = $value;
        }
    }

    // バリデーション
    $errors = validation($datas);

    // $pdo = dbConnect();

    //データベースの中に同一ユーザー名が存在していないか確認
    if(empty($errors['id'])){
        $sql = "SELECT id FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        // $stmt->bindValue(':name', $datas['name'], PDO::PARAM_INT);
        $stmt->bindValue(':id', $datas['id']);
        $stmt->execute();
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $errors['id'] = '同じユーザー名が存在します。';
        }
    }
    //エラーがなかったらDBへの新規登録を実行
    if(empty($errors)){
        $params = [
            'id'=>$datas['id'],
            'name'=>$datas['name'],
            'password'=>password_hash($datas['password'], PASSWORD_DEFAULT)
        ];

        $count = 0;
        $columns = '';
        $values = '';
        foreach (array_keys($params) as $key) {
            if($count > 0){
                $columns .= ',';
                $values .= ',';
            }
            $columns .= $key;
            $values .= ':'.$key;
            $count++;
        }

        $pdo->beginTransaction();//トランザクション処理
        try {
            $sql = 'insert into users ('.$columns .')values('.$values.')';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $pdo->commit();
            header("location: login.php");
            exit;
        } catch (PDOException $e) {
            echo 'エラー: 登録失敗';
            $pdo->rollBack();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>登録</title>
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
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>登録</h2>
        <p>IDとパスワードを入力、登録してください。</p>
        <form action="<?php echo $_SERVER ['SCRIPT_NAME']; ?>" method="post">
            <div class="form-group">
                <label>ID</label>
                <input type="text" name="id" class="form-control <?php echo (!empty(($errors['id']))) ? 'is-invalid' : ''; ?>" value="<?php echo ($datas['id']); ?>">
                <span class="invalid-feedback"><?php echo ($errors['id']); ?></span>
            </div>    
            <div class="form-group">
                <label>名前</label>
                <input type="text" name="name" class="form-control <?php echo (!empty(($errors['name']))) ? 'is-invalid' : ''; ?>" value="<?php echo ($datas['name']); ?>">
                <span class="invalid-feedback"><?php echo ($errors['name']); ?></span>
            </div>  
            <div class="form-group">
                <label>パスワード</label>
                <input type="password" name="password" class="form-control <?php echo (!empty(($errors['password']))) ? 'is-invalid' : ''; ?>" value="<?php echo ($datas['password']); ?>">
                <span class="invalid-feedback"><?php echo ($errors['password']); ?></span>
            </div>
            <div class="form-group">
                <label>再確認</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty(($errors['confirm_password']))) ? 'is-invalid' : ''; ?>" value="<?php echo ($datas['confirm_password']); ?>">
                <span class="invalid-feedback"><?php echo ($errors['confirm_password']); ?></span>
            </div>
            <div class="form-group">
                <input type="hidden" name="token" value="<?php echo ($_SESSION['token']); ?>">
                <input type="submit" class="btn btn-primary" value="登録">
            </div>
            <p>すでに登録済みの方は<a href="login.php">こちら</a>.</p>
        </form>
    </div>    
</body>
</html>
