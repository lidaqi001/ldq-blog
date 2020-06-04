<?php

function getTitle(&$title = '')
{
    // 获取文章标题
    if (PHP_OS == 'WINNT') {
        // windows 当输入中文时 获取不到STDIN输入值

        $params = getopt('t:');
        if (!isset($params['t'])) {
            exit("Error : 标题不能为空\r\nExample : php create.php -t 标题");
        }
        $title = $params['t'];
    } else {

        while ($line = fopen('php://stdin', 'r')) {
            fwrite(STDOUT, '文章标题 => ');
            $title = trim(fgets($line, 1000));
            if (strlen($title)) {
                fclose($line);
                break;
            }
        }
    }
}

function createFile($title, $time, &$path)
{
    $path = './' . date('Ymdhis', $time) . '.md';
    writeFile($path, function () use ($title, $time) {
        return sprintf(
            "# %s\n\r> %s",
            $title,
            date('Y.m.d H:i', $time)
        );
    });
}
function syncSidebar($title, $path)
{
    writeFile('./_sidebar.md', function () use ($title, $path) {
        return "\r* [$title]($path)";
    }, 'a+');
}

function writeFile($path, Closure $callback, $model = 'w')
{
    $file = fopen($path, $model);
    fwrite($file, call_user_func($callback));
    fclose($file);
}

// 获取标题
getTitle($title);

// 打印系统、时间参数
echo 'os: ' . PHP_OS . PHP_EOL;
$time = time();
echo 'DateTime: ' . date('Y-m-d H:i:s', $time) . PHP_EOL;

// 生成文件
createFile($title, $time, $path);

// 同步_sidebar.md
syncSidebar($title, $path);

fwrite(STDOUT, 'File Path : ' . $path);
