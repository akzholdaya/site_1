<?php
session_start();

// --- Кіру тексерісі ---
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    if ($username === 'media' && $password === '1234') {
        $_SESSION['logged_in'] = true;
    } else {
        $error = "Қате логин немесе құпия сөз!";
    }
}

// --- Шығу ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: news_panel.php");
    exit;
}

// --- Жаңалықты сақтау ---
if (isset($_POST['add_news']) && $_SESSION['logged_in']) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image = "";

    if ($_FILES['image']['name']) {
        $upload_dir = "uploads/";
        if (!file_exists($upload_dir)) mkdir($upload_dir);
        $image = $upload_dir . time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    $new = [
        "title" => $title,
        "content" => $content,
        "image" => $image,
        "date" => date("Y-m-d H:i")
    ];

    $newsData = file_exists("news.json") ? json_decode(file_get_contents("news.json"), true) : [];
    $newsData[] = $new;
    file_put_contents("news.json", json_encode($newsData, JSON_PRETTY_PRINT));
    header("Location: news_panel.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="kk">
<head>
  <meta charset="UTF-8">
  <title>Жаңалықтар панелі</title>
  <style>
    body { font-family: Arial; background: #f4f4f4; padding: 20px; color: #333; }
    .card { background: white; padding: 15px; margin: 10px 0; box-shadow: 0 0 10px #ccc; border-radius: 8px; }
    img { max-width: 300px; border-radius: 8px; }
    form { background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 0 10px #bbb; }
    input, textarea, button { width: 100%; padding: 10px; margin: 8px 0; }
    h2 { color: #005bbb; }
  </style>
</head>
<body>

<?php if (!isset($_SESSION['logged_in'])): ?>
  <h2>Медиа офицерге кіру</h2>
  <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
  <form method="POST">
    <input type="text" name="username" placeholder="Логин" required>
    <input type="password" name="password" placeholder="Құпия сөз" required>
    <button name="login">Кіру</button>
  </form>

<?php else: ?>
  <h2>Жаңалық қосу</h2>
  <form method="POST" enctype="multipart/form-data">
    <input type="text" name="title" placeholder="Жаңалық тақырыбы" required>
    <textarea name="content" placeholder="Мәтін" rows="5" required></textarea>
    <input type="file" name="image">
    <button name="add_news">Жаңалықты сақтау</button>
  </form>

  <p><a href="?logout=true">🔒 Шығу</a></p>

  <h2>📢 Жаңалықтар</h2>
  <?php
    if (file_exists("news.json")) {
      $newsList = array_reverse(json_decode(file_get_contents("news.json"), true));
      foreach ($newsList as $news) {
        echo "<div class='card'>";
        if ($news['image']) echo "<img src='{$news['image']}'><br>";
        echo "<h3>{$news['title']}</h3>";
        echo "<p>{$news['content']}</p>";
        echo "<small>{$news['date']}</small>";
        echo "</div>";
      }
    } else {
      echo "<p>Әзірге жаңалықтар жоқ.</p>";
    }
  ?>
<?php endif; ?>

</body>
</html>
