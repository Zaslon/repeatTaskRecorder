<?php echo '<?xml version="1.0" encoding="UTF-8"?>'?>
<?php
	require 'func.php';

	//json読み込み
	$fname = 'record.json';
	$json = file_get_contents($fname);
	$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
	$json = json_decode($json,true);
    $records = $json["records"];
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ja" xml:lang="ja" dir="ltr">
<head>
<meta name="robots" content="noindex">
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=yes" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="Content-Style-Type" content="text/css" /> 
<link rel="stylesheet" type="text/css" href="css.css" />
<link rel="shortcut icon" href="favicon.ico" />
<link rel="icon" href="favicon.ico" />
<title>タスク処理</title>
</head>
<body>
<?php
    foreach($records as $record){
        echo("<form method='post'>"); // 各レコードごとにフォームを分ける
        echo("<ul><li>");
        echo_h($record["name"]);
        echo("</li><li>");
        $date = new DateTime($record["lastExecuted"]);
        echo_h($date->format('Y/m/d'));
        echo("</li><li>");
        $now = new DateTime();
        $diff = $now->diff($date);
        $diff = $diff->format('%d日前');
        echo_h($diff);
        echo("</li>");

        echo("<li class='textAndSubmit'>");
        echo("<input type='hidden' name='taskName' value='" . htmlspecialchars($record["name"], ENT_QUOTES) . "' />");
        echo("<input type='submit' name='update' value='更新' />");
        echo("</li></ul>");
        echo("</form>");
    }

    // POST処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        $taskName = $_POST['taskName'];
        $now = date('c');

        foreach ($records as &$record) {
            if ($record['name'] === $taskName) {
                $record['lastExecuted'] = $now;
                break;
            }
        }

        // 保存用に $data を更新
        $data['records'] = $records;
        file_put_contents('record.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        // 🔁 リダイレクトして画面を更新
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
?>

</body>
</html>