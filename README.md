# XSJYA 短网址程序

## 基本信息

 * **Program Name**: XSJYA Short URL Program
 * **Author**: XSJYA + Ai
 * **Official Website**: http://www.xsjya.com/
 * **Description**: 一个高效、稳定、安全的短网址程序，帮助您轻松管理和共享网址！
 * **Tags**: 短网址，高效，稳定，安全，用户友好


![Alt](https://repobeats.axiom.co/api/embed/58cb53d65d230410f5788081beed17fc08dff11f.svg "Repobeats analytics image")



## 程序演示

### 主界面展示
<img src="https://s21.ax1x.com/2025/05/27/pVSqwrT.png" alt="主界面展示" width="400">

### 自定义后缀默认隐藏
 **点击底部2025展开自定义后缀输入框**

<img src="https://s21.ax1x.com/2025/05/27/pVSq0qU.png" alt="主界面展示" width="400">



## 伪静态配置

### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php;
}

location ~ /(.*) {
    try_files $uri $uri/ /redirect.php?short_url=$1;
}
```

### 更改配置信息
1. 打开 config.php 文件。
2. 修改数据库配置信息：
 - $host：数据库主机（通常是 localhost）
 - $dbname：数据库名称（如 XSJYA）
 - $username：数据库用户名（如 XSJYA）
 - $password：数据库密码（如 XSJYA）
 - $base_url：你的域名（如 https://www.xsjya.com/）

## 数据库导入

<p><code><del>short_urls.sql</del></code> <del>文件导入数据库。</del></p> 

 V1.0.3 已经不需要导入数据库文件了

## 再次确认

1. 确保所有文件（config.php、index.php、redirect.php 和 <del> short_urls.sql</del>）已上传到你的网站根目录。
2. 在浏览器中访问你的域名，确保网站可以正常加载。
3. 尝试生成一个短网址，检查是否能够正常重定向。



## 历史版本
V1.0：基础功能完善，支持网址缩短及隐藏自定义后缀，采用本地存储方式，无需数据库支持。

V1.0.1：改为数据库存储数据，优化自定义后缀功能。

V1.0.2：限制后缀仅可使用数字和字母，每个长网址最多可生成三个不同短网址（包括自定义后缀）。

V1.0.3：简化部署流程，无需手动导入数据库文件，进一步提升部署便捷性。


其实上面这些是瞎写的  凑合着看吧 ![000932FB](https://github.com/user-attachments/assets/846a9a36-7923-4aef-adfc-8851eafe1f85)











