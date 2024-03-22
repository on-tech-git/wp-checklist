<?php

function memory_limit_to_bytes($value)
{
    $value = trim($value);
    $last = strtolower($value[strlen($value) - 1]);
    switch ($last) {
        case 'g':
            @$value *= 1024;
        case 'm':
            @$value *= 1024;
        case 'k':
            @$value *= 1024;
    }
    return (int) $value;
}

$compatibility = true;

$phpVersion = phpversion();
$phpVersionClass = version_compare($phpVersion, '7.4', '<') ? 'fail' : (version_compare($phpVersion, '8.0', '<') ? 'warn' : 'pass');
if ($phpVersionClass == 'fail') {
    $compatibility = false;
}

$memoryLimitBytes = memory_limit_to_bytes(ini_get('memory_limit'));
$memoryCheckClass = $memoryLimitBytes < 256 * 1024 * 1024 ? 'fail' : 'pass';
if ($memoryCheckClass == 'fail') {
    $compatibility = false;
}

$curlCheckClass = extension_loaded('curl') ? 'pass' : 'fail';
if ($curlCheckClass == 'fail') {
    $compatibility = false;
}

$wpConfigPath = 'wp-config.php';
$mysqlCheckClass = 'pass';
if (!file_exists($wpConfigPath)) {
    $mysqlCheckClass = 'warn';
} else {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $fileLines = file($wpConfigPath);
    $pattern = "/define\s*\(\s*'DB_/i";

    foreach ($fileLines as $line) {
        if (preg_match($pattern, $line)) {
            eval($line);
        }
    }

    try {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    } catch (mysqli_sql_exception $e) {
        $msqlerror = $e;
        $mysqlCheckClass = 'fail';
        $compatibility = false;
    }
}

?>
<html>
    <head>
        <title>Проверка сервера на совместимость с ВП</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
            }
            table {
                border-collapse: collapse;
                width: 100%;
            }
            th,
            td {
                border: 1px solid #ddd;
                text-align: left;
                padding: 8px;
            }
            th {
                background-color: #f2f2f2;
            }
            .pass {
                color: green;
            }
            .fail {
                color: red;
            }
            .warn {
                color: orange;
            }
            .verdict {
                margin-top: 20px;
                font-size: 24px;
            }
        </style>
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" /> 
        <meta http-equiv="Pragma" content="no-cache" /> <meta http-equiv="Expires" content="0" />
        <link href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAR0dHcJqbm5KVlpb7paWl/6Wlpf+Vlpb7mpubkkdHR3AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAhoeH5sPDw///////o6Sk/3t8fP95enr/i4yM//z8/P/Dw8P/hoeH5gAAAAAAAAAAAAAAAAAAAAAAAAAAb3Bw/vb29v+6u7v//////5ucnP9mZ2f/Zmdn/2hpaf//////2tvb//b29v9vcHD+AAAAAAAAAAAAAAAAhoeH5vb29v9mZ2f/ycrK///////n5+f/Zmdn/2ZnZ/+goaH///////39/f9mZ2f/9vb2/4aHh+YAAAAAR0dHcMPDw/9oaWn/Zmdn//7+/v//////tbW1/2pra/9mZ2f/7+/v//////+6urr/goOD/2doaP/Dw8P/R0dHcJqbm5LOzs7/Zmdn/4iJif//////3d3d/2tsbP+jpKT/cXJy///////19fX/Z2ho/9DQ0P9mZ2f/zs7O/5qbm5KVlpb7jI2N/2ZnZ//c3Nz//////5CRkf9mZ2f/o6Oj/7u8vP//////tre3/2ZnZ//l5eX/Zmdn/4yNjf+Vlpb7paWl/3t8fP9naGj///////////9mZ2f/Zmdn/6Kjo////////////3N0dP9mZ2f/rKys/4iJif97fHz/paWl/6Wlpf96e3v/mpub///////d3d3/Zmdn/2ZnZ/+IiYn///////Pz8/9mZ2f/Zmdn/56env/Y2Nj/e3x8/6Wlpf+Vlpb7i4yM/+vr6///////j4+P/2ZnZ/9mZ2f/0tPT//////+0tLT/Zmdn/2ZnZ//IyMj//Pz8/4yNjf+Vlpb7mpubku7u7v///////////2ZnZ/9mZ2f/Zmdn////////////cnNz/2ZnZ/9naGj////////////V1dX/mpubkkdHR3DDw8P/8fLy/83Nzf+jpKT/b3Bw/87Ozv/c3Nz/3t7e/7S0tP9mZ2f/tba2////////////w8PD/0dHR3AAAAAAhoeH5vb29v9mZ2f/Zmdn/2ZnZ/9mZ2f/Zmdn/2ZnZ/9mZ2f/Zmdn/4SFhf///////v7+/4aHh+YAAAAAAAAAAAAAAABvcHD+9fX1/2doaP9mZ2f/Zmdn/2ZnZ/9mZ2f/Zmdn/2ZnZ/9oaWn/+Pj4/29wcP4AAAAAAAAAAAAAAAAAAAAAAAAAAIaHh+bDw8P/z8/P/4yNjf97fHz/ent7/4uLi//Nzc3/w8PD/4aHh+YAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAR0dHcJqbm5KVlpb7paWl/6Wlpf+Vlpb7mpubkkdHR3AAAAAAAAAAAAAAAAAAAAAA+B8AAOAHAADAAwAAgAEAAIABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACAAQAAgAEAAMADAADgBwAA+B8AAA==" rel="icon" type="image/x-icon">
    </head>
    <body>
        <h1>Проверка сервера на совместимость с ВП</h1>
        <hr>
        <table>
            <tr>
                <th>Проверка</th>
                <th>Статус</th>
                <th>Детали</th>
            </tr>
            <tr>
                <td>Версия PHP</td>
                <td class="<?php echo $phpVersionClass; ?>"><?php echo strtoupper($phpVersionClass); ?></td>
                <td><?php echo $phpVersion; if(strtoupper($phpVersionClass) === "WARN") { echo " | 7.4 это минимальная версия для WP. Если можешь - обновись до 8+"; } ?></td>
            </tr>
            <tr>
                <td>PHP Memory</td>
                <td class="<?php echo $memoryCheckClass; ?>"><?php echo strtoupper($memoryCheckClass); ?></td>
                <td><?php echo ini_get('memory_limit'); if(strtoupper($memoryCheckClass) === "FAIL") { echo " | Мало памяти. Сделай минимум 256M"; } ?></td>
            </tr>
            <tr>
                <td>cURL</td>
                <td class="<?php echo $curlCheckClass; ?>"><?php echo strtoupper($curlCheckClass); ?></td>
                <td><?php echo $curlCheckClass == 'pass' ? 'Модуль установлен' : 'Модуль не найден'; ?></td>
            </tr>
            <tr>
                <td>MySQL</td>
                <td class="<?php echo $mysqlCheckClass; ?>"><?php echo $mysqlCheckClass == 'warn' ? 'WARN' : strtoupper($mysqlCheckClass); ?></td>
                <td><?php echo $mysqlCheckClass == 'warn' ? 'wp-config.php не найден, не могу подключится к базе проверить её.' : ($mysqlCheckClass == 'pass' ? 'БД работает' : $msqlerror); ?></td>
            </tr>
        </table>

        <div class="verdict <?php echo $compatibility ? 'pass' : 'fail'; ?>" style="text-align: center !important;">
               <i><b><?php echo $compatibility ? '<span style="color: green;">ГОТОВ ДЛЯ WORDPRESS\'А</span>' : '<span style="color: red;">НЕ ГОТОВ ДЛЯ WORDPRESS\'А</span>'; ?></b></i>
        </div>
        <hr>
        <div style="width: 100%; height: 200px;">
            <img src="https://on-tech.tech/images/photo_2023-03-17_13-38-00.jpg" style="object-fit: contain; width:100%; height:100%" >
        </div>
    </body>
</html>
