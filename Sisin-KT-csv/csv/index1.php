<?php
require_once 'functions.php';

// Задаем возможные параметры
$shortopts = "";
$shortopts .= "o:";
$shortopts .= "i:";
$shortopts .= "c:";
$shortopts .= "d:";
$shortopts .= "h";

$longopts = array(
    "skip-first",
    "strict",
    "input:",
    "help",
    "config:",
    "output:",
    "delimiter:"
);
// Считываем введенные параметры
$options = getopt($shortopts, $longopts);

// Проверка на корректность введенных параметров
checkParams($argv);

// Показ справки
showHelp($options);

// Проверка на обязательные параметры
checkRequiredParams($options);

// Присваиваем: input = входной файл, config = конф. файл, output = выходной файл
$input = $options['i'] ?? $options['input'] ?? null;
$config = $options['c'] ?? $options['config'] ?? null;
$output = $options['o'] ?? $options['output'] ?? null;

// Показ введенных параметров
showParams($options);

// Проверка конфигурационного файла
checkConfigFile($config);

// Считываем конфигурационный файл
$arrFromConf = include $config;

// Считываем тип конфигурационного файла
checkTypeReturnedFile($arrFromConf);

// Проверка выходного файла
checkOutputFile($output);

// Проверка входного файла
checkInputFile($input);

// Парсер входного файла. Запись в выходной файл
parseInputFile($input, $output, $arrFromConf, $options);

// Проверка на параметр --strict
$file_array = file($input);
$num_str =  count($file_array);
checkStrict($arrFromConf, $num_str, $options);

// Проверка на разделитель
checkDelimiter($options);

exit(0);
