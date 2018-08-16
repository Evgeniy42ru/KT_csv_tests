<?php

require_once 'functions.php';
require_once __DIR__ . '/vendor/autoload.php';
$faker = Faker\Factory::create('en_EN');

$shortopts = "";
$shortopts .= "i:";
$shortopts .= "c:";
$shortopts .= "o:";
$shortopts .= "d:";
$shortopts .= "k:";
$shortopts .= "s";
$shortopts .= "h";

$longopts = array(
    "input:",
    "config:",
    "output:",
    "delimiter:",
    "skip:",
    "strict",
    "help",
);

$options = getopt($shortopts, $longopts);

$input = $options['i'] ?? $options['input'] ?? null;
$config = $options['c'] ?? $options['config'] ?? null;
$output = $options['o'] ?? $options['output'] ?? null;

if (count($options) == 0) {
     echo "Передайте все необходимые параметры. \n";
     exit(1);
}

if (isset($options['h']) || isset($options['help'])) {
     echo "Опции:
-i|--input       путь до исходного файла
-c|--config      путь до файла конфигурации
-o|--output      путь до файла с результатом
-d|--delimiter   задать разделитель (по умолчанию “,”)
-k|--skip        пропускать модификацию первой строки исходного csv
-s|--strict      проверять, что исходный файл содержит необходимое количество описанных 
                 в конфигурационном файле  столбцов. При несоответствии выдавать ошибку.
-h|--help        вывести справку \n";
    exit(1);
}


if (isset($options['s']) || isset($options['strict'])) {
    $strictOpen = fopen($input, 'r');
    while ($stData = fgetcsv($strictOpen, 1000, ',')) {
        $stNum = count($stData);
    }
    fclose($strictOpen);
    if ($stNum == 4) {
         echo "Исходный файл содержит необходимое количество столбцов. \n";
    } else {
         echo "Ошибка несоответствия столбцов во входном и конфигурационном файлах. \n";
         exit();
    }
}


if ((isset($options['i']) || isset($options['input'])) && (isset($options['o']) || isset($options['output'])) &&
	(isset($options['c']) || isset($options['config']))) {


if (!is_readable($input)) {
     echo "Входной файл недоступен для чтения или его не существует. \n";
     exit(1);
}

if (!is_readable($config)) {
     echo "Конфигурационный файл недоступен для чтения или его не существует. \n";
     exit(1);
}

if (!file_exists($config)) {
     echo "Файл конфигурации отсутсвует или указан неверно. \n";
     exit(1);
}

if(!file_exists($input)) {
     echo "Входной файл отсутсвует. \n";
     exit(1);
}
$expansionIn = substr(strrchr($input, '.'), 1);
if ($expansionIn != "csv") {
     echo "Расширение входного файла не соответсвует условиям. \n";
     exit(1);
}

$expansionConf = substr(strrchr($config, '.'), 1);
if ($expansionConf != "php") {
     echo "Расширение файла конфигурации не соответсвует условиям. \n";
     exit(1);
}

$basenameConf = basename('Conf.php');
if ($basenameConf != "Conf.php") {
     echo "Неправильное имя файла конфигурации. \n";
     exit(1);
}

$arrFromConf = include $config;


$inputRead = fopen($input, "r") or die("Ошибка");
$outWrite = fopen($output, "w") or die("Ошибка");

if (!is_writable($output)) {
echo "Выходной файл недоступен для записи. \n";
     exit(1);
}

$row = 1;
$encodingIn = mb_detect_encoding('input.csv');
$encodingOut = mb_detect_encoding('output.csv');

for ($i = 0; $data = fgetcsv($inputRead, 1000, Delimiter($options)); $i++) {

     if ($row == 1) {
          if (isset($options['skip'])) {
			 $row++;
			 continue;


	 }
		}

			foreach ($data as $key => $value) {
				if (!array_key_exists($key, $arrFromConf)) {
					$dataFileOutput[$key] = $value;
				} else {
					$newconf = $arrFromConf[$key];
					if (is_null($newconf)) {
						$dataFileOutput[$key] = "";
					} elseif (strtolower(gettype($newconf)) == "string") {
						try {
							$dataFileOutput[$key] = $faker->$newconf;
						} catch (Exception $e) {
							echo 'Выброшено исключение: ', $e->getMessage(), "\n";
						}
					} elseif (gettype($newconf) == "object") {
						try {
							$dataFileOutput[$key] = $newconf($value, $data, $row, $faker);
						} catch (Exception $e) {
							echo 'Выброшено исключение: ', $e->getMessage(), "\n";
						}
					} else {
						try {
							$dataFileOutput[$key] = $value;
						} catch (Exception $e) {
							echo 'Выброшено исключение: ', $e->getMessage(), "\n";
						}
					}
				}
			}
			fputcsv($outWrite, $dataFileOutput, Delimiter($options));
	}

	echo "Запись в файл успешно произведена. \n";
	echo "Входной файл имеет кодировку: $encodingIn \n";
	echo "Выходной файл имеет кодировку: $encodingOut \n";
}

fclose($inputRead);
fclose($outWrite);







