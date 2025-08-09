<?php echo '<?xml version="1.0" encoding="UTF-8"?>'?>
<?php
	require 'func.php';

	// POST処理を先に実行（リダイレクト前に処理）
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
		//json読み込み
		$fname = 'record.json';
		$json = file_get_contents($fname);
		$json = mb_convert_encoding($json, 'UTF8', 'ASCII,JIS,UTF-8,EUC-JP,SJIS-WIN');
		$json = json_decode($json,true);
		
		$taskName = $_POST['taskName'];
		$now = date('c'); // ISO 8601形式で保存
		
		// レコードを更新
		foreach ($json["records"] as &$record) {
			if ($record['name'] === $taskName) {
				$record['lastExecuted'] = $now;
				break;
			}
		}
		
		// JSONファイルに保存
		file_put_contents($fname, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
		
		// リダイレクトして画面を更新
		header("Location: " . $_SERVER['REQUEST_URI']);
		exit;
	}

	//json読み込み（表示用）
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
		
		// 最終実行日の日付部分のみを取得（0時基準）
		$lastExecutedDateTime = new DateTime($record["lastExecuted"]);
		$lastExecutedDate = $lastExecutedDateTime->setTime(0, 0, 0); // 時刻を0:00:00にリセット
		echo_h($lastExecutedDate->format('Y/m/d'));
		echo("</li>");
		
		// 現在日時の日付部分のみを取得（0時基準）
		$nowDateTime = new DateTime();
		$nowDate = $nowDateTime->setTime(0, 0, 0); // 時刻を0:00:00にリセット
		
		// 日付の差分を計算
		$diff = $nowDate->diff($lastExecutedDate);
		$daysDiff = $diff->days;
		
		if ($daysDiff == 0) {
			echo("<li>");
			echo_h("今日");
		} elseif ($daysDiff < 5) {
			echo("<li>");
			echo_h($daysDiff . "日前");
		} elseif ($daysDiff <10) {
			echo("<li class='color1'>");
			echo_h($daysDiff . "日前");
		} else {
			echo("<li class='color2'>");
			echo_h($daysDiff . "日前");
		}
		echo("</li>");

		echo("<li class='textAndSubmit'>");
		echo("<input type='hidden' name='taskName' value='" . htmlspecialchars($record["name"], ENT_QUOTES) . "' />");
		echo("<input type='submit' name='update' value='更新' />");
		echo("</li></ul>");
		echo("</form>");
	}
?>

</body>
</html>