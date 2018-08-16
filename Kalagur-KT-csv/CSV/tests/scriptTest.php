<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class scriptTest extends TestCase
{
    private $path;

    protected function setUp()
    {
        $this->path = __DIR__ . "/../index.php";
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


    public function additionProvider()
    {
        $pathInput = __DIR__ . "/files/goodInput.csv";
        $pathConfig = __DIR__ . "/files/goodConf.php";
        $pathOutput = __DIR__ . "/files/output.csv";
        $badPathConfig = __DIR__ . "/files/conf.txt";


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
            [false, ["-i $pathInput", "-c $badPathConfig", "-o $pathOutput"]],
            [false, ["-h", "-i $pathInput", "-c $pathConfig", "-o $pathOutput"]],
            [false, ["-i $pathInput", "-c $pathConfig", "-o $pathOutput", "--help"]],
            [false, ["-i $pathInput", "-c $pathConfig", "-o $pathOutput", "-h"]],


            [true, ["-i $pathInput", "-c $pathConfig", "-o $pathOutput"]],
            [true, ["-i $pathInput", "-c $pathConfig", "-o $pathOutput", "--strict"]],
            [true, ["-i $pathInput", "-c $pathConfig", "-o $pathOutput", "--skip-first"]],
            [true, ["-i $pathInput", "-c $pathConfig", "-o $pathOutput", "--skip-first", "--strict"]],
            [true, ["-i $pathInput", "-c $pathConfig", "-o $pathOutput", "--strict", "--skip-firsst"]],
            [true, ["-i $pathInput", "-c $pathConfig", "-o $pathOutput", '-d ";"']],


        ];
    }


}