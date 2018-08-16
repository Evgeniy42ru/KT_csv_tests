<?php

require_once 'vendor/fzaninotto/faker/src/autoload.php';

function checkParams($argv)
{
    foreach (array_count_values($argv) as $value) {
        if ($value > 1) {
            showError();
            exit(1);
        }
    }
}

function showHelp($options)
{

    if (isset($options['h']) || isset($options['help'])) {
        if (count($options) == 1) {
            echo "Usage: 
    php index.php   (-i | --input)      <filepath> 
                    (-c | --config)     <filepath> 
                    (-o | --output)     <filepath> 
                    [-d | --delimiter]  <delimiter>
                    [--skip-first] 
                    [--strict] 
    php index.php   [-h | --help]
Options:
    -i --input      путь до исходного файла
    -c --config     путь до файла конфигурации
    -o --output     путь до файла с результатом
    -d --delimiter  задать разделитель (по умолчанию “,”)
    --skip-first    пропускать модификацию первой строки исходного csv
    --strict        проверять, что исходный файл содержит необходимое количество описанных в конфигурационном файле 
                    столбцов. При несоответствии выдавать ошибку.
    -h --help       вывести справку\n";
            exit(0);
        } else {
            showError();
            exit(1);
        }
    }
}

function checkRequiredParams($options)
{
    if (!((isset($options['i']) || isset($options['input']))
        && (isset($options['c']) || isset($options['config']))
        && (isset($options['o']) || isset($options['output'])))) {
        showError();
        exit(1);
    }
}

function showParams($options)
{
    echo "Список заданных параметров: \n";
    foreach ($options as $k => $v) {
        echo $k . " ";
    }
    echo "\n";
}

function checkConfigFile($config)
{
    if (file_exists($config)) {
        if (is_readable($config)) {
            if (pathinfo($config, PATHINFO_EXTENSION) !== 'php') {
                echo "Расширение конфигурационного файла не 'php'\n";
                exit(1);
            }
        } else {
            echo "Файл " . $config . " не доступен для чтения!\n\n";
            exit(1);
        }
    } else {
        echo "Файл " . $config . " не существует!\n\n";
        exit(1);
    }
}

function checkOutputFile($output)
{
    if (!file_exists($output)) {
        $fp = fopen($output, "w");
        echo "Файл " . $output . " создан\n";
        fclose($fp);
    } else {
        if (!is_writable($output)) {
            echo "Файл " . $output . " не доступен для записи\n";
            exit(1);
        }
    }
}

function checkInputFile($input)
{

    if (file_exists($input)) {
        if (is_readable($input)) {
            if (!file_get_contents($input)) {
                echo "Исходный файл " . $input . " пустой!\n";
                exit(1);
            }
            return true;
        } else {
            echo "Файл " . $input . " не доступен для чтения!\n";
            exit(1);
        }
    } else {
        echo "Файл " . $input . " не существует!\n";
        exit(1);
    }
}

function parseInputFile($input, $output, $arrFromConf, $options)
{
    $faker = Faker\Factory::create();

    if (checkInputFile($input)) {
        $row = 1;
        $handle = fopen($input, "r");

        mb_check_encoding(file_get_contents($input), 'UTF-8');
        $fz = fopen($output, "w");

        SetDelimiter($options);
        while (($data = fgetcsv($handle, 0, SetDelimiter($options))) !== false) {
            if ($row == 1) {
                if (isset($options['skip-first'])) {
                    $row++;
                    continue;
                }
            }
            foreach ($data as $k => $v) {
                if (!array_key_exists($k, $arrFromConf)) {
                    $dataFileOutput[$k] = $v;
                } else {
                    $newconf = $arrFromConf[$k];
                    if (is_null($newconf)) {
                        $dataFileOutput[$k] = "";
                    } elseif (gettype($newconf) == "string") {
                        $dataFileOutput[$k] = $faker->$newconf;
                    } elseif (gettype($newconf) == "object") {
                        $dataFileOutput[$k] = $newconf($v, $data, $row, $faker);
                    } else {
                        $dataFileOutput[$k] = $v;
                    }
                }
            }
            $sizeInput = count($data);
            $sizeOfData[] = $sizeInput;

            if (!mb_check_encoding($dataFileOutput, 'UTF-8')) {
                foreach ($dataFileOutput as $v) {
                    mb_convert_encoding($v, 'Windows-1251', 'UTF-8');
                }
            }


            fputcsv($fz, $dataFileOutput, SetDelimiter($options));

            $row++;
        }


        if (count(array_count_values($sizeOfData)) > 1) {
            echo "Ошибка: во входном файле разное число полей в строках\nНаберите 'php index.php -h' или ";
            echo "'php index.php --help' для дополнительной информации.\n";
            exit(1);
        }
        echo "Результат записан в " . $output . "\nПРОГРАММА ВЫПОЛНЕНА УСПЕШНО!\n";
        fclose($fz);
        fclose($handle);
    }
    return $sizeInput;
}

function checkStrict($arrFromConf, $sizeInput, $options)
{
    if (isset($options['strict'])) {
        $keys = array_keys($arrFromConf);
        $maxKeyConf = $keys[0];
        for ($i = 1; $i < sizeof($keys); $i++) {
            if ($keys[$i] > $maxKeyConf) {
                $maxKeyConf = $keys[$i];
            }
        }
        if ($maxKeyConf > $sizeInput) {
            echo "Ошибка: исходный файл содержит неверное количество описанных в конфигурационном файле столбцов\n";
            exit(1);
        } else {
            echo "Исходный файл содержит корректное число описанных в конфигурационном файле столбцов\n";
        }
    }
}

function SetDelimiter($options)
{
    $delimiter = ",";
    if (isset($options['d'])) {
        if (iconv_strlen($options['d']) > 1) {
            echo "Разделитель содержит больше одного символа!\n";
            exit(1);
        }
        $delimiter = $options['d'];
        return $delimiter;
    }
    if (isset($options['delimiter'])) {
        if (iconv_strlen($options['delimiter']) > 1) {
            echo "Разделитель содержит больше одного символа!\n";
            exit(1);
        }
        $delimiter = $options['delimiter'];
        return $delimiter;
    }
    return $delimiter;
}

function checkTypeReturnedFile($arrFromConf)
{
    if (gettype($arrFromConf) !== 'array') {
        echo "Тип конфигурации должен быть массивом!\n";
        exit(1);
    }
}

function checkDelimiter($options)
{
    if (isset($options['d'])) {
        if (iconv_strlen($options['d']) > 1) {
            echo "Разделитель содержит больше одного символа!\n";
            echo(1);
        }
    }
}

function showError()
{
    echo "Ошибка: введены неверные параметры!\nНаберите 'php index.php -h' или 'php index.php --help' для";
    echo "дополнительной информации.\n";
}
