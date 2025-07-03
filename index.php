<?php
/**
 * Program Name: XSJYA Short URL Program
 * Author: XSJYA
 * Official Website: http://www.xsjya.com/
 * Description: 一个高效、稳定、安全的短网址程序，帮助您轻松管理和共享网址！
 * Tags: 短网址，高效，稳定，安全，用户友好
 * Version: 1.0.3
 */
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['long_url'])) {
    $longUrl = $_POST['long_url'];
    $customSuffix = isset($_POST['custom_suffix']) ? trim($_POST['custom_suffix']) : null;
    $expiresIn = isset($_POST['expires_in']) ? trim($_POST['expires_in']) : null;

    try {
        // 如果用户提供了自定义后缀，检查是否符合要求
        if ($customSuffix !== null && $customSuffix !== '') {
            if (!preg_match('/^[a-zA-Z0-9]+$/', $customSuffix)) {
                echo json_encode([
                    'error' => '自定义后缀只能包含字母和数字，请重新输入。'
                ]);
                exit;
            }

            // 检查自定义后缀是否已被占用
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM short_urls WHERE short_url = :custom_suffix");
            $stmt->execute(['custom_suffix' => $customSuffix]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode([
                    'error' => '自定义后缀已被占用，请选择其他后缀。'
                ]);
                exit;
            }
        }

        // 如果没有提供自定义后缀，则自动生成一个
        if ($customSuffix === null || $customSuffix === '') {
            $customSuffix = generateShortUrl($pdo);
        }

        // 检查是否已生成过多短网址
        $stmt = $pdo->prepare("SELECT short_url FROM short_urls WHERE long_url = :long_url");
        $stmt->execute(['long_url' => ensureProtocol($longUrl)]);
        $existingShortUrls = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($existingShortUrls) >= 3) {
            echo json_encode([
                'error' => '此链接已生成多个短网址，不再为您继续生成。请使用以下任意一网址。',
                'shortUrls' => array_map(function ($shortUrl) use ($base_url) {
                    return $base_url . $shortUrl;
                }, $existingShortUrls)
            ]);
            exit;
        }

        // 创建短网址
        $result = createShortUrl($pdo, $longUrl, $customSuffix, $expiresIn);
        echo json_encode(['shortUrl' => $result['shortUrl']]);
        exit;
    } catch (PDOException $e) {
        echo json_encode(['error' => '数据库操作失败: ' . $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XSJYA - 短网址生成平台</title>
    <link rel="icon" href="https://www.wnooo.cn/favicon.ico" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.staticfile.org/twitter-bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <meta name="keywords" content="47.plus, 47短网址, XSJYA, 短网址, 短网址生成器, URL缩短, 短链接, 网址缩短工具">
    <meta name="description" content="一个简单易用的短网址生成器，帮助您将长网址缩短为简洁的短链接。">
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
        .form-group {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form-group label {
            text-align: center;
            width: 100%;
        }
        .form-group input {
            width: 100%;
            margin-bottom: 10px;
        }
        .form-group button {
            width: 100%;
        }
        .result {
            margin-top: 20px;
            text-align: center;
        }
        .result p {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .result a {
            word-break: break-all;
            flex: 1;
            margin-right: 10px;
        }
        .copy-btn {
            margin-left: auto;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.8em;
            color: #6c757d;
        }
        .footer a {
            text-decoration: none;
            color: inherit;
        }
        .footer a:hover {
            text-decoration: none;
        }
        .advanced-options {
            display: none;
            margin-top: 10px;
        }
        .advanced-options .left, .advanced-options .right {
            width: 48%; /* 让输入框变小 */
        }
        .advanced-options .left {
            float: left;
        }
        .advanced-options .right {
            float: right;
        }
        @media (max-width: 768px) {
            .result p {
                flex-direction: column;
                align-items: center;
            }
            .result a {
                margin-bottom: 10px;
            }
            .copy-btn {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>47短网址</h2>
        <form id="urlForm">
            <div class="form-group">
                <label for="long_url">请输入要缩短的链接（包含 http:// 或 https://）：</label>
                <input type="text" class="form-control" id="long_url" name="long_url" required="自定义后缀">
            </div>
            <div class="advanced-options">
                <div class="form-group left">
                    <label for="custom_suffix">自定义后缀（可选）：</label>
                    <input type="text" class="form-control" id="custom_suffix" name="custom_suffix" placeholder="自定义后缀">
                </div>
                <div class="form-group right">
                    <label for="expires_in">有效期 /天：</label>
                    <input type="text" class="form-control" id="expires_in" name="expires_in" placeholder="留空默认不过期">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">生成短网址</button>
        </form>
        <div class="result" id="result"></div>
        <div class="footer">
            <p>
                © <span id="toggleAdvanced" style="cursor: pointer;">2025</span>
                <a href="https://www.xsjya.com/" target="_blank">XSJYA</a>. All rights reserved.
            </p>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://cdn.staticfile.org/jquery/3.5.1/jquery.min.js"></script>
    <!-- Popper.js -->
    <script src="https://cdn.staticfile.org/popper.js/1.16.1/umd/popper.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.staticfile.org/twitter-bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // 修复点击功能 - 使用纯JavaScript确保可靠
        document.addEventListener('DOMContentLoaded', function() {
            const toggleElement = document.getElementById('toggleAdvanced');
            const advancedOptions = document.querySelector('.advanced-options');
            
            toggleElement.addEventListener('click', function() {
                // 直接切换显示状态
                if (advancedOptions.style.display === 'none') {
                    advancedOptions.style.display = 'block';
                } else {
                    advancedOptions.style.display = 'none';
                }
            });
        });

        document.getElementById('urlForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const longUrl = document.getElementById('long_url').value;
            const customSuffix = document.getElementById('custom_suffix') ? document.getElementById('custom_suffix').value : '';
            const expiresIn = document.getElementById('expires_in') ? document.getElementById('expires_in').value : '';
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<p>正在生成...</p>';

            fetch('index.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `long_url=${encodeURIComponent(longUrl)}&custom_suffix=${encodeURIComponent(customSuffix)}&expires_in=${encodeURIComponent(expiresIn)}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        if (data.shortUrls) {
                            resultDiv.innerHTML = `
                                <p class="text-danger">${data.error}</p>
                                <p>已生成的短网址：</p>
                                <ul>
                                    ${data.shortUrls.map(url => `<li><a href="${url}" target="_blank">${url}</a></li>`).join('')}
                                </ul>
                            `;
                        } else {
                            resultDiv.innerHTML = `<p class="text-danger">${data.error}</p>`;
                        }
                    } else {
                        const shortUrl = data.shortUrl;
                        resultDiv.innerHTML = `
                            <p>
                                短网址：<a href="${shortUrl}" target="_blank" class="text-primary font-weight-bold">${shortUrl}</a>
                                <button class="btn btn-success copy-btn" onclick="copyText('${shortUrl}')">复制短网址</button>
                            </p>
                        `;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = '<p class="text-danger">生成短网址时出错。</p>';
                    console.error('Error:', error);
                });
        });

        function copyText(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('短网址已复制到剪贴板！');
            }, function(err) {
                console.error('无法复制文本：', err);
            });
        }
    </script>
</body>
</html>
