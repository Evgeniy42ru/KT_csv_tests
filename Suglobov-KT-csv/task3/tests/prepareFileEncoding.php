<?php

prepareFileEncoding("files/input_Utf8.csv");

function prepareFileEncoding($filepath)
{
    $fileContent = file_get_contents($filepath);
    $fileContent2 = mb_convert_encoding($fileContent, 'Windows-1251', 'UTF-8');
    echo file_put_contents("files/input_W1251.csv", $fileContent2) . PHP_EOL;
}
