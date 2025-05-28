<?php
/**
 * Program Name: XSJYA Short URL Program
 * Author: XSJYA
 * Official Website: http://www.xsjya.com/
 * Description: 一个高效、稳定、安全的短网址程序，帮助您轻松管理和共享网址！
 * Tags: 短网址，高效，稳定，安全，用户友好
 * Version: 1.0.3  简化部署流程 
 */
// 数据库配置
$host = 'localhost'; // 数据库主机
$dbname = 'XSJYA'; // 数据库名称
$username = 'XSJYA'; // 数据库用户名
$password = 'XSJYA'; // 数据库密码
$base_url = 'https://www.xsjya.com/'; // 你的域名

try {
    // 检查数据库是否存在
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 检查数据库是否存在
    $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :dbname");
    $stmt->execute(['dbname' => $dbname]);
    if (!$stmt->fetchColumn()) {
        // 如果数据库不存在，则创建数据库
        $pdo->exec("CREATE DATABASE $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    }

    // 连接到指定的数据库
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 动态创建表
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS short_urls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    short_url VARCHAR(255) NOT NULL UNIQUE,
    long_url TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL;
    $pdo->exec($sql);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

function ensureProtocol($url) {
    if (substr($url, 0, 7) !== 'http://' && substr($url, 0, 8) !== 'https://') {
        return 'http://' . $url;
    }
    return $url;
}

function generateShortUrl($pdo) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $shortUrl = '';
    $time = microtime(true); // 获取当前时间戳（带小数）
    $timePart = substr(str_replace('.', '', $time), -6); // 取时间戳的后6位
    $randomPart = rand(100000, 999999); // 生成6位随机数
    $combined = $timePart . $randomPart;
    $shortUrl = substr(base_convert($combined, 10, 36), 0, 6); // 转换为36进制并取前6位

    return $shortUrl;
}

function createShortUrl($pdo, $longUrl, $customSuffix = null) {
    global $base_url;

    $shortUrl = '';

    // 如果提供了自定义后缀
    if ($customSuffix !== null) {
        // 检查自定义后缀是否符合要求
        if (!preg_match('/^[a-zA-Z0-9]+$/', $customSuffix)) {
            return ['error' => '自定义后缀只能包含字母和数字，请重新输入。'];
        }

        // 检查自定义后缀是否已被占用
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM short_urls WHERE short_url = :custom_suffix");
        $stmt->execute(['custom_suffix' => $customSuffix]);
        if ($stmt->fetchColumn() > 0) {
            return ['error' => '自定义后缀已被占用，请选择其他后缀。'];
        }

        $shortUrl = $customSuffix;
    } else {
        // 如果没有提供自定义后缀，则生成一个唯一的短网址后缀
        do {
            $shortUrl = generateShortUrl($pdo);
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM short_urls WHERE short_url = :short_url");
            $stmt->execute(['short_url' => $shortUrl]);
        } while ($stmt->fetchColumn() > 0);
    }

    $longUrl = ensureProtocol($longUrl);

    // 插入新的短网址记录
    $stmt = $pdo->prepare("INSERT INTO short_urls (short_url, long_url) VALUES (:short_url, :long_url)");
    $stmt->execute(['short_url' => $shortUrl, 'long_url' => $longUrl]);

    return ['shortUrl' => $base_url . $shortUrl];
}
