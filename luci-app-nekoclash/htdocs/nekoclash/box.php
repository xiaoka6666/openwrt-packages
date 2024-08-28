<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>订阅转换模板</title>
    <link rel="stylesheet" href="./assets/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #87ceeb;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            padding: 20px;
            box-sizing: border-box;
            margin-top: 50px;
        }
        h1 {
            color: #007BFF;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], textarea {
            width: calc(100% - 16px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="radio"] {
            margin-right: 5px;
        }
        .radio-group {
            margin-bottom: 15px;
        }
        .radio-group label {
            margin: 0 10px 0 0;
            display: inline-block;
            font-weight: normal;
        }
        input[type="submit"], button {
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            width: 150px;
            height: 40px;
        }
        input[type="submit"] {
            background-color: #007BFF;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        button.return-button {
            background-color: #dc3545;
        }
        button.return-button:hover {
            background-color: #c82333;
        }
        button.return-danger {
            background-color: #17a2b8;
        }
        button.copy-button {
            background-color: #17a2b8;
        }
        button.copy-button:hover {
            background-color: #138496;
        }
        button.save-button {
            background-color: #28a745;
        }
        button.save-button:hover {
            background-color: #218838;
        }
        textarea {
            height: 200px;
            resize: vertical;
            overflow-y: auto;
        }
        .result-container {
            margin-top: 20px;
        }
        .button-group {
            display: flex;
            justify-content: center;
            margin-top: 10px;
            gap: 10px;
        }
        .log-container {
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
            padding: 10px;
            height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
            font-family: monospace;
            box-sizing: border-box;
        }
        .saved-data-container {
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
            padding: 10px;
            box-sizing: border-box;
        }
    </style>
</head>
<body>
    <div class="container">
    <h1>Sing-box 订阅转换模板</h1>
    <form method="post" action="">
        <label for="subscribeUrl">订阅链接地址:</label>
        <input type="text" id="subscribeUrl" name="subscribeUrl" required>

        <div class="radio-group">
            <input type="radio" id="useDefaultTemplate" name="templateOption" value="default" checked>
            <label for="useDefaultTemplate">使用默认模板</label>
            
            <input type="radio" id="useCustomTemplate" name="templateOption" value="custom">
            <label for="useCustomTemplate">使用自定义模板URL:</label>
            <input type="text" id="customTemplateUrl" name="customTemplateUrl" placeholder="输入自定义模板URL">
        </div>

        <div class="button-group">
            <input type="submit" name="generateConfig" class="submit-button" value="生成配置文件">
            <button type="button" class="return-button" onclick="window.location.href='/nekoclash/upload_sb.php';">返回上一级</button>
            <button type="button" class="return-danger" onclick="window.location.href='/nekoclash';">返回主菜单</button>
        </div>
    </form>

    <?php
    $dataFilePath = '/tmp/subscription_data.txt';
    $configFilePath = '/etc/neko/config/config.json';

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generateConfig'])) {
        $subscribeUrl = trim($_POST['subscribeUrl']);
        $customTemplateUrl = trim($_POST['customTemplateUrl']);

        $dataContent = "订阅链接地址: " . $subscribeUrl . "\n" . "自定义模板URL: " . $customTemplateUrl . "\n";
        file_put_contents($dataFilePath, $dataContent, FILE_APPEND);

        $subscribeUrlEncoded = urlencode($subscribeUrl);

        if ($_POST['templateOption'] === 'custom' && !empty($customTemplateUrl)) {
            $templateUrlEncoded = urlencode($customTemplateUrl);
        } else {
            $templateUrlEncoded = urlencode("https://cdn.jsdelivr.net/gh/Thaolga/Rules@main/Clash/json/config_mixed.json");
        }

        $completeSubscribeUrl = "https://sing-box-subscribe-doraemon.vercel.app/config/{$subscribeUrlEncoded}&file={$templateUrlEncoded}";
        $tempFilePath = '/tmp/config.json';

        $command = "wget -O " . escapeshellarg($tempFilePath) . " " . escapeshellarg($completeSubscribeUrl);
        exec($command, $output, $returnVar);

        $logMessages = [];

        if ($returnVar !== 0) {
            $logMessages[] = "无法下载内容: " . htmlspecialchars($completeSubscribeUrl);
        }

        $downloadedContent = file_get_contents($tempFilePath);
        if ($downloadedContent === false) {
            $logMessages[] = "无法读取下载的文件内容";
        }

        $updatedContent = $downloadedContent;

        if (file_put_contents($configFilePath, $updatedContent) === false) {
            $logMessages[] = "无法保存修改后的内容到: " . $configFilePath;
        } else {
            $logMessages[] = "配置文件生成并保存成功: " . $configFilePath;
            $logMessages[] = "生成并下载的订阅URL: " . htmlspecialchars($completeSubscribeUrl);
        }

        echo "<div class='result-container'>";
        echo "<form method='post' action=''>";
        echo "<textarea id='configContent' name='configContent'>" . htmlspecialchars($updatedContent) . "</textarea>";
        echo "<div class='button-group'>";
        echo "<button class='copy-button' type='button' onclick='copyToClipboard()'><i class='fas fa-copy'></i> 复制到剪贴</button>";
        echo "<input type='hidden' name='saveContent' value='1'>";
        echo "<button class='save-button' type='submit'>保存修改</button>";
        echo "</div>";
        echo "</form>";
        echo "</div>";

        echo "<div class='log-container'>";
        foreach ($logMessages as $message) {
            echo htmlspecialchars($message) . "<br>";
        }
        echo "</div>";
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['saveContent'])) {
        if (isset($_POST['configContent'])) {
            $editedContent = trim($_POST['configContent']);
            if (file_put_contents($configFilePath, $editedContent) === false) {
                echo "<div class='log-container'>无法保存修改后的内容到: " . htmlspecialchars($configFilePath) . "</div>";
            } else {
                echo "<div class='log-container'>内容已成功保存到: " . htmlspecialchars($configFilePath) . "</div>";
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clearData'])) {
        if (file_exists($dataFilePath)) {
            file_put_contents($dataFilePath, '');
            echo "<div class='log-container'>保存的数据已清空。</div>";
        }
    }

    if (file_exists($dataFilePath)) {
        $savedData = file_get_contents($dataFilePath);
        echo "<div class='saved-data-container'>";
        echo "<h2>保存的数据</h2>";
        echo "<pre>" . htmlspecialchars($savedData) . "</pre>";
        echo "<form method='post' action=''>";
        echo "<button class='clear-button' type='submit' name='clearData'>清空数据</button>";
        echo "</form>";
        echo "</div>";
    }
    ?>

    <script>
        function copyToClipboard() {
            const copyText = document.getElementById("configContent");
            copyText.select();
            document.execCommand("copy");
            alert("已复制到剪贴板");
        }
    </script>
</div>

<style>
    .clear-button {
        background-color: #ff4c4c; 
        color: white; 
        border: none;
        padding: 10px 20px;
        cursor: pointer;
        border-radius: 5px;
        font-size: 16px;
    }

    .clear-button:hover {
        background-color: #ff1a1a; 
    }

    .button-group button, .button-group input[type="submit"] {
        margin: 5px;
    }

    .saved-data-container {
        margin-top: 20px;
        padding: 10px;
        border: 1px solid #ccc;
    }
</style>

