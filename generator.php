<?php
session_start();
$config = json_decode(file_get_contents("config.json"), true);
$auth_pass = $config['admin_password'] ?? '1234';

function isAdmin() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}

// Обработка входа в админку
if (isset($_GET['admin'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['password'] === $GLOBALS['auth_pass']) {
            $_SESSION['authenticated'] = true;
            header("Location: ?admin");
            exit();
        } else {
            $error = "Неверный пароль";
        }
    }
    if (!isAdmin()) {
        echo "<html><head><title>Вход</title><link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta2/css/all.min.css'><style>body{font-family:sans-serif;text-align:center;background:#1e1e2f;color:white;margin-top:10%}input{padding:10px}button{padding:10px 20px}</style></head><body><h2>Вход в админку</h2>";
        if (isset($error)) echo "<p style='color:red'>$error</p>";
        echo "<form method='POST'><input type='password' name='password' placeholder='Пароль'><br><br><button type='submit'>Войти</button></form></body></html>";
        exit();
    }

    // Обработка сохранения
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode'], $_POST['winner'])) {
        $config['mode'] = $_POST['mode'];
        $config['winner'] = $_POST['winner'];
        file_put_contents("config.json", json_encode($config));
        header("Location: ?admin");
        exit();
    }

    // Админка HTML
    echo "<html><head><title>Админка</title><style>body{background:#1e1e2f;color:white;font-family:sans-serif;text-align:center;margin-top:5%}select,input{padding:10px;margin:5px}button{padding:10px 20px;margin-top:10px}</style></head><body>";
    echo "<h2>Панель управления</h2>";
    echo "<form method='POST'>";
    echo "<label>Режим: <select name='mode'><option value='fall'".($config['mode']==='fall'?' selected':'').">Выпадение</option><option value='wheel'".($config['mode']==='wheel'?' selected':'').">Колесо</option></select></label><br>";
    echo "<label>Выигрышное значение: <input name='winner' value='".htmlspecialchars($config['winner'])."'></label><br>";
    echo "<button type='submit'>Сохранить</button></form></body></html>";
    exit();
}

// Основной экран
$mode = $config['mode'] ?? 'fall';
$winner = $config['winner'] ?? '42';
$values = $config['values'] ?? ['1','2','3','4','5'];
if (!in_array($winner, $values)) $values[] = $winner;

?><!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Генератор</title>
<style>
body { font-family: sans-serif; text-align: center; background: linear-gradient(#141e30, #243b55); color: white; }
#wheel { margin: 30px auto; transform: rotate(0deg); transition: transform 5s ease-out; }
#wheel-canvas { border-radius: 50%; border: 4px solid white; }
#arrow { position: absolute; top: 5px; left: 50%; transform: translateX(-50%); width: 0; height: 0; border-left: 20px solid transparent; border-right: 20px solid transparent; border-bottom: 40px solid red; }
.hidden { display: none; }
#fall-box { font-size: 48px; height: 100px; line-height: 100px; margin-top: 30px; animation: fall 2s ease-in forwards; }
@keyframes fall {
  0% { transform: translateY(-200px); opacity: 0; }
  100% { transform: translateY(0); opacity: 1; }
}
</style>
</head>
<body>
<h1>Генератор</h1>
<?php if ($mode === 'wheel'): ?>
  <div style="position:relative;width:400px;height:400px;margin:0 auto">
    <canvas id="wheel-canvas" width="400" height="400"></canvas>
    <div id="arrow"></div>
  </div>
  <script>
    const values = <?= json_encode($values) ?>;
    const winner = <?= json_encode($winner) ?>;
    const canvas = document.getElementById('wheel-canvas');
    const ctx = canvas.getContext('2d');
    const total = values.length;
    const angle = 2 * Math.PI / total;

    values.forEach((v, i) => {
      ctx.beginPath();
      ctx.moveTo(200, 200);
      ctx.arc(200, 200, 200, i * angle, (i + 1) * angle);
      ctx.fillStyle = i % 2 === 0 ? '#00c6ff' : '#0088cc';
      ctx.fill();
      ctx.save();
      ctx.translate(200, 200);
      ctx.rotate(i * angle + angle / 2);
      ctx.textAlign = "right";
      ctx.fillStyle = "white";
      ctx.font = "16px sans-serif";
      ctx.fillText(v, 180, 10);
      ctx.restore();
    });

    const index = values.indexOf(winner);
    const rotation = 360 * 5 + (360 - (index + 0.5) * (360 / total));
    setTimeout(() => canvas.style.transform = `rotate(${rotation}deg)`, 500);
  </script>
<?php else: ?>
  <div id="fall-box">...</div>
  <script>
    const winner = <?= json_encode($winner) ?>;
    setTimeout(() => document.getElementById('fall-box').textContent = winner, 1000);
  </script>
<?php endif; ?>
</body>
</html>

