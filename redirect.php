<?php
/**
 * Program Name: XSJYA Short URL Program
 * Author: XSJYA
 * Official Website: http://www.xsjya.com/
 * Description: 一个高效、稳定、安全的短网址程序，帮助您轻松管理和共享网址！
 * Tags: 短网址，高效，稳定，安全，用户友好
 * Version: 1.0
 */
require_once 'config.php';

// 获取短网址参数
$shortUrl = isset($_GET['short_url']) ? trim($_GET['short_url'], '/') : '';

// 查询数据库获取原始长网址
try {
    $stmt = $pdo->prepare("SELECT long_url FROM short_urls WHERE short_url = :short_url");
    $stmt->execute(['short_url' => $shortUrl]);
    $longUrl = $stmt->fetchColumn();

    if ($longUrl) {
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
        <p>您访问的短网址不存在。页面将在8秒后自动跳转回首页。</p>
    </div>
    <div class="footer">
        <p>&copy; 2025 XSJYA. All rights reserved.</p>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = '/';
        }, 8000);
    </script>
</body>
</html>
EOT;
        exit;
    }
} catch (PDOException $e) {
    die("数据库操作失败: " . $e->getMessage());
}