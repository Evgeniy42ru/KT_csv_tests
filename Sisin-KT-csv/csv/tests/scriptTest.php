<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    private $path;

    protected function setUp()
    {
        $this->path = __DIR__ . "/../index1.php";
    }

    /**
     * @dataProvider additionProvider
     */
    public function testParam($expected, $arrayParams)
    {
        $pathToScript = $this->path;

        exec("php " . $pathToScript . " " . implode(" ", $arrayParams), $someArr, $res);

        $this->assertEquals($expected, $res == 0);
    }

    public function testCoding()
    {

        $pathToScript = $this->path;


        $value1 = file_get_contents('tests/files/inputUtf.csv');
        $isUTFinput = mb_check_encoding($value1, 'UTF-8');

        exec("php " . $pathToScript . ' -i tests/files/inputUtf.csv -c tests/files/confNotChange.php -o tests/files/output.csv',
            $someArr, $res);

        $value2 = file_get_contents('tests/files/output.csv');
        $isUTFoutput = mb_check_encoding($value2, 'UTF-8');
        $this->assertEquals($isUTFinput, $isUTFoutput);


        $value1 = file_get_contents('tests/files/inputWin1251.csv');
        $isUTFinput = mb_check_encoding($value1, 'UTF-8');

        exec("php " . $pathToScript . ' -i tests/files/inputWin1251.csv -c tests/files/confNotChange.php -o tests/files/output.csv',
            $someArr, $res);

        $value2 = file_get_contents('tests/files/output.csv');
        $isUTFoutput = mb_check_encoding($value2, 'UTF-8');
        $this->assertEquals($isUTFinput, $isUTFoutput);

        $isEqual = true;
        if (is_file('tests/files/inputWin1251.csv') && is_file('tests/files/output.csv')) {
            if ((($handle = fopen('tests/files/inputWin1251.csv', "r")) !== false) &&
                (($handle2 = fopen('tests/files/output.csv', "r")) !== false)) {
                while ((($data = fgetcsv($handle, 0, ",")) !== false) &&
                    (($data2 = fgetcsv($handle2, 0, ",")) !== false)) {
                    if (!empty(array_diff($data, $data2))) {
                        $isEqual = false;
                    }
                }
                fclose($handle);
                fclose($handle2);
            }
        }


        $this->assertTrue($isEqual);
    }

    public function additionProvider()
    {
        $pathToInput = __DIR__ . "/files/inputUtf.csv";
        $pathToConfig = __DIR__ . "/files/conf.php";
        $pathToOutput = __DIR__ . "/files/output.csv";
        $badPathToConfig = __DIR__ . "/files/conf.txt";
        $badConfig = __DIR__ . "/files/badConf.php";
        $badConfig2 = __DIR__ . "/files/badConf2.php";
        $badInput = __DIR__ . "/files/badInput.csv";
        $badOutput = __DIR__ . "/files/notWritableOut.csv";


        return [
            [false, []],
            [false, ["-i"]],
            [false, ["--h"]],
            [false, ["-h", "-h"]],
            [false, ["-h", "--help"]],
            [false, ["-c"]],
            [false, ["-o"]],
            [false, ["--input"]],
            [false, ["--config"]],
            [false, ["--output"]],
            [false, ["-i $pathToInput", "-c $pathToConfig", "--strict"]],
            [false, ["-i $pathToInput", "-c $badConfig", "-o $pathToOutput", "--strict"]],
            [false, ["-i $badInput", "-c $badConfig", "-o $pathToOutput", "--strict"]],
            [false, ["-i $pathToInput", "-c $badPathToConfig", "-o $pathToOutput"]],
            [false, ["-h", "-i $pathToInput", "-c $pathToConfig", "-o $pathToOutput"]],
            [false, ["-i $pathToInput", "-c $pathToConfig", "-o $pathToOutput", "--help"]],
            [false, ["-i $pathToInput", "-c $pathToConfig", "-o $pathToOutput", "-h"]],
            [false, ["-i $pathToInput", "-c $pathToConfig", "-o $pathToOutput", "--input $pathToInput"]],
            [false, ["-ii $pathToInput", "-c $pathToConfig", "-o $pathToOutput"]],
            [false, ["-i $pathToInput", "-c $pathToConfig", "-o $badOutput"]],
            [false, ["-i $pathToInput", "-c $badConfig2", "-o $badOutput"]],
            [false, ["-i $pathToInput", "-c $pathToConfig", "-o $pathToOutput", '-d ";;"']],

            [true, ["-h"]],
            [true, ["--help"]],
            [true, ["-i $pathToInput", "-c $pathToConfig", "-o $pathToOutput"]],
            [true, ["-i $pathToInput", "-c $pathToConfig", "-o $pathToOutput", "--strict"]],
            [true, ["-i $pathToInput", "-c $pathToConfig", "-o $pathToOutput", "--skip-first"]],
            [true, ["-i $pathToInput", "-c $pathToConfig", "-o $pathToOutput", "--skip-first", "--strict"]],
            [true, ["-i $pathToInput", "-c $pathToConfig", "-o $pathToOutput", "--strict", "--skip-firsst"]],
            [true, ["-i $pathToInput", "-c $pathToConfig", "-o $pathToOutput", '-d ","']],
        ];
    }
}
