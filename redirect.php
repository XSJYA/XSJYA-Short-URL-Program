<?php
/**
 * Program Name: XSJYA Short URL Program
 * Author: XSJYA
 * Official Website: http://www.xsjya.com/
 * Description: 一个高效、稳定、安全的短网址程序，帮助您轻松管理和共享网址！
 * Tags: 短网址，高效，稳定，安全，用户友好
 * Version: 1.0.5  添加短网址有效期 到期以后三天后释后缀 可再次申请
 */
require_once 'config.php';

// 删除过期的短网址
deleteExpiredShortUrls($pdo);

// 获取短网址参数
$shortUrl = isset($_GET['short_url']) ? trim($_GET['short_url'], '/') : '';

// 查询数据库获取原始长网址
try {
    $stmt = $pdo->prepare("SELECT long_url, expires_at, available_at FROM short_urls WHERE short_url = :short_url");
    $stmt->execute(['short_url' => $shortUrl]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $longUrl = $row['long_url'];
        $expiresAt = $row['expires_at'];
        $availableAt = $row['available_at'];

        // 检查是否已到期
        if ($expiresAt && strtotime($expiresAt) < time()) {
            if ($availableAt && strtotime($availableAt) > time()) {
                echo <<<EOT
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>短网址已到期</title>
    <link href="https://cdn.staticfile.org/twitter-bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }
        .container {
            text-align: center;
            width: 100%;
            max-width: 600px;
            padding: 20px;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.8em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>短网址已到期</h1>
        <b>您访问的短网址设置了有效期 现已到期</b><p>后缀将在 {$availableAt} 后释放 之后可重新申请</p>
        
    </div>
    <div class="footer">
        <p>&copy; 2025 XSJYA. All rights reserved.</p>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = '/';
        }, 30000); // 30秒后跳转回首页
    </script>
</body>
</html>
EOT;
                exit;
            } else {
                // 如果可用时间已过，删除该记录
                $deleteStmt = $pdo->prepare("DELETE FROM short_urls WHERE short_url = :short_url");
                $deleteStmt->execute(['short_url' => $shortUrl]);
                echo <<<EOT
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>短网址已释放</title>
    <link href="https://cdn.staticfile.org/twitter-bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }
        .container {
            text-align: center;
            width: 100%;
            max-width: 600px;
            padding: 20px;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.8em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>短网址已释放</h1>
        <p>您访问的短网址，后缀已释放，现在可以重新申请。</p>
    </div>
    <div class="footer">
        <p>&copy; 2025 XSJYA. All rights reserved.</p>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = '/';
        }, 20000); // 20秒后跳转回首页
    </script>
</body>
</html>
EOT;
                exit;
            }
        }

        // 重定向到原始长网址
        header("Location: " . $longUrl);
        exit;
    } else {
        // 显示“网址未找到”页面
        echo <<<EOT
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>网址未找到</title>
    <link href="https://cdn.staticfile.org/twitter-bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }
        .container {
            text-align: center;
            width: 100%;
            max-width: 600px;
            padding: 20px;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.8em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>网址未找到</h1>
        <p>您访问的短网址不存在。页面将在20秒后自动跳转回首页。</p>
    </div>
    <div class="footer">
        <p>&copy; 2025 XSJYA. All rights reserved.</p>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = '/';
        }, 20000); // 20秒后跳转回首页
    </script>
</body>
</html>
EOT;
        exit;
    }
} catch (PDOException $e) {
    die("数据库操作失败: " . $e->getMessage());
}
