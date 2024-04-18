<?php
//XSS対策
function h($s){
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

//セッションにトークンセット
function setToken(){
    $token = sha1(uniqid(mt_rand(), true));
    $_SESSION['token'] = $token;
}

//セッション変数のトークンとPOSTされたトークンをチェック
function checkToken(){
    if(empty($_SESSION['token']) || ($_SESSION['token'] != $_POST['token'])){
        echo 'Invalid POST', PHP_EOL;
        exit;
    }
}

//POSTされた値のバリデーション
function validation($datas, $confirm = true)
{
    $errors = [];

    //ユーザーIDのチェック
    if(empty($datas['id'])) {
        $errors['id'] = 'ユーザーIDを入力してください。';
    }else if(mb_strlen($datas['id']) > 20) {
        $errors['id'] = '20文字以下入力してください。';
    }
    else if(!preg_match('/\A[a-z\d]{1,100}+\z/i',$datas["id"])){
        $errors['id'] = "英数字のみで入力してください。";
    }
    
    //パスワードのチェック（正規表現）
    if(empty($datas["password"])){
        $errors['password']  = "パスワードを入力してください。";
    }else if(!preg_match('/\A[a-z\d]{8,100}+\z/i',$datas["password"])){
        $errors['password'] = "8桁以上入力してください。";
    }
    //ユーザー新規登録時
    if($confirm){
        //名前のチェック
        if(empty($datas['name'])) {
            $errors['name'] = '名前を入力してください。';
        }else if(mb_strlen($datas['name']) > 20) {
            $errors['name'] = '20文字以下入力してください。';
        }
        //パスワード入力確認チェック
        if(empty($datas["confirm_password"])){
            $errors['confirm_password']  = "再確認を入力してください。";
        }else if(empty($errors['password']) && ($datas["password"] != $datas["confirm_password"])){
            $errors['confirm_password'] = "パスワード不一致です。";
        }
    }

    return $errors;
}

//商品画像
    function img_tag($code) {
        if (file_exists("images/$code.jpg")) $name = $code;
        else $name = 'noimage';
        return '<img src="images/' . $name . '.jpg" alt="">';
      }
