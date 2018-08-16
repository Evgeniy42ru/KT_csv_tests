<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class FirstTest extends TestCase
{
    private $filePath;

    protected function setUp()
    {
        include __DIR__ . "/../tests/bootstrap.php";;
        $this->filePath = __DIR__ . "/../action.php";
    }

    public function testOptions()
    {

    }


    /**
     * @dataProvider dataEncodingProvider
     */
    public function testEncoding($encod, $fileInput)
    {
        $fp = $this->filePath;
        $fileConf = __DIR__ . "/files/FirstTest/config_good.php";
        $fileOutput = __DIR__ . "/files/FirstTest/tmp_output.csv";

        exec(
            "php " . $fp . " -i $fileInput -c $fileConf -o $fileOutput",
            $output,
            $return_var
        );

        $fileContent = file_get_contents($fileInput);
        $encodeFile = mb_check_encoding($fileContent, 'UTF-8') ? 'UTF-8' : 'Windows-1251';
        echo '
        ' . $encodeFile;
        $outputContent = file_get_contents($fileOutput);
        $encodeOutput = mb_check_encoding($outputContent, 'UTF-8') ? 'UTF-8' : 'Windows-1251';
        echo ' - ' . $encodeOutput;

        $this->assertEquals($encodeFile, $encodeOutput, 'Неверная кодировка ');
    }

    public function dataEncodingProvider()
    {
        return [
            ['UTF-8', __DIR__ . "/files/input_Utf8.csv"],
            ['Windows-1251', __DIR__ . "/files/input_W1251.csv"]
        ];
    }
}