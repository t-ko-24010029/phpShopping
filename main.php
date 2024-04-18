<?php
  //ファイルの読み込み
  require_once "db_connect.php";
  require_once "common.php";
  //セッション開始
  session_set_cookie_params(60 * 30 * 1);
  ini_set( 'session.gc_maxlifetime', 60 * 30 * 1);
  session_start();

  // セッション変数 $_SESSION["loggedin"]を確認。ログイン済じゃなかったらだったらログインページへリダイレクト
  if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
  }

  if (@$_POST['searchName']) {
    $st = $pdo->prepare("SELECT * FROM goods WHERE searchName LIKE :searchName");
    $name = mb_convert_kana($_POST['searchName'], "Hc");
    $name = '%'.$name.'%';
    $st->bindValue('searchName', $name);
    $st->execute();
    $goods = $st->fetchAll();
  }
  else{
    $st = $pdo->query("SELECT * FROM goods");
    $goods = $st->fetchAll();
  }

  $st = $pdo->prepare("SELECT COUNT(code) AS total FROM cart WHERE id = :id");
  $st->bindValue('id', $_SESSION["id"]);
  $st->execute();
  $row = $st->fetch();

  $_SESSION['goodsCount'] = $row['total'];


  $stock = array();
  foreach ($goods as $g) {
    $stock[$g['code']] = $g['stock'];
  }
?>

<!DOCTYPE html>
  <html>
    <head>
      <link rel="stylesheet" href="shopping.css">
      <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css"/>
      <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
      <meta charset="utf-8">
      <title>売り場</title>
      <!-- jqueryを読み込み -->
      <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
      <style type="text/css">
        h1 {
          margin: 10px 0;
          padding: 0;
          font-size: 32px;
          text-align: center;
        }
        .container {
          margin-inline: auto;
          max-width: 600px;
          position: relative;
        }
        .swiper {
          width: 600px;
          height: 180px;
          /* overflow: visible; */
        }
        /* 前への矢印 */
        .swiper-button-prev {
          left: -30px;
        }
        /* 次への矢印 */
        .swiper-button-next {
          right: -30px;
        }
        /* ページネーション */
        .swiper-pagination-bullets.swiper-pagination-horizontal {
          bottom: -25px;
        }
        table {
          width: 500px; 
          height: 150px;
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
        }
      </style>
    </head>
    <body>
      <div class="wrapper">
        <h1>商品</h1>
        <?php if($_SESSION["administrator"] == 1){ ?>
        <a href="administratorPage.php">管理者画面</a>　
        <?php } ?>
        <form action="cart.php" method="post">
          <div style="margin-left:15%;">
          <div style="margin-right:15%;float:right;" id="reloader">
          <font color="red"><?php echo($_SESSION['goodsCount'] != 0) ? $_SESSION['goodsCount'] : "" ?></font>
          <input type="submit" class="btn btn-primary" name="cart" value="カートへ">
        </form>
        <form action="logout.php" method="post">
          </div><input type="submit" class="btn btn-primary" name="logout" value="ログアウト"></div>
        </form>
        <form action="main.php" method="post">
          <div style="margin-left:40%;">
            <input type="text" size="30" class="form-control" id="searchName" name="searchName" value = <?php echo(ISSET($_POST['searchName'])) ? $_POST['searchName'] : "" ?>>
            <input type="submit" name="search" class="btn btn-primary" value="検索">
          </div>
        </form>
        <div class="container">
        <!-- 前へ / 次へボタン -->
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
        <!-- ページネーション -->
        <div class="swiper-pagination"></div>
        <!-- スクロールバー -->
        <!-- <div class="swiper-scrollbar"></div> -->
        <div class="swiper">
        <!-- Additional required wrapper -->
        <div class="swiper-wrapper">
          <?php foreach ($goods as $g) { ?>
          <div class="swiper-slide">
            <table>
              <tr>
                <td>
                  <?php echo img_tag($g['code']) ?>
                </td>
                <td>
                  <p class="goods"><?php echo $g['name'] ?>　(在庫：<?php echo $g['stock'] ?>)</p>
                  <p><?php echo nl2br($g['comment']) ?></p>
                </td>
                <td width="80">
                  <p><?php echo $g['price'] ?> 円</p>
                  <form id="goods_form_<?php echo $g['code'] ?>">
                    <select name="num">
                      <?php
                        for ($i = 0; $i <= $stock[$g['code']]; $i++) {
                          echo "<option>$i</option>";
                        }
                      ?>
                    </select>
                    <input type="hidden" name="code" value="<?php echo $g['code'] ?>">
                    <input type="button" class="btn btn-primary" name="cartIn" value="カートに入れる" id="cartInBtn_<?php echo $g['code'] ?>">
                  </form>
                </td>
              </tr>
            </table>
          </div>
          <?php } ?>
        </div>
      </div>
    </body>
    <script>
    //カートに入れる押下時のAjax通信処理
    function cartIn(code) {
      //formセレクタを取得
      const formData = new FormData(document.getElementById("goods_form_" + code));

      //ajax起動。post通信でformDataを指定のcart_in.phpに送る。
      $.ajax({
          type: "POST",
          dataType: "HTML",
          data: formData,
          url: "cart_in.php",
          processData: false,  //ajaxがdataを整形しない指定
          contentType: false  //contentTypeもfalseに指定
      //通信が成功したら表示切り替え。
      }).done(function (response) {
          let htmlString = '<font color="red">' + response + ' </font>'  + 
                            '<input type="submit" class="btn btn-primary" name="cart" value="カートへ"></form>' +
                            '<form action="logout.php" method="post">';
          
          $('#reloader').html(htmlString)
      //通信が失敗したらエラー表示。
      }).fail(function (XMLHttpRequest, textStatus, errorThrown) {
          console.log(XMLHttpRequest);
          console.log(textStatus);
          console.log(errorThrown);
          return;
      })
    }

    //カートに入れるボタンイベント登録
    <?php foreach ($goods as $g) { ?>
      $(document).on('click', '#cartInBtn_<?php echo $g['code'] ?>', function() {
        cartIn(<?php echo $g['code'] ?>);
      });
    <?php } ?>
    </script>
    <script type="module">
    const swiper = new Swiper('.swiper', {
    // Optional parameters
    // direction: 'vertical',
    loop: true,

    // If we need pagination
    pagination: {
        el: '.swiper-pagination',
    },

    // Navigation arrows
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },

    // And if we need scrollbar
    scrollbar: {
        el: '.swiper-scrollbar',
    },
    });
    </script>
  </html>