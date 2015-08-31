<?php

require_once('../../../../../wp-load.php'); 
require_once('../SupraCsvChunker.php');

class testSupraCsvChunker extends PHPUnit_Framework_TestCase {

    private static $correct_number = 20;

    function setUp()
    {
        $supraCsvChunker = new \SupraCsvFree\SupraCsvChunker();

        $source = "test.csv";

        $supraCsvChunker->splitFile($source, 50);
    
        $this->scc = $supraCsvChunker;
    }

    function testChunkDirExists()
    {
        $this->assertFileExists($this->scc->getTargetPath());
    }

    function testChunkFileNamesAreCorrect()
    {
        $chunkedFiles = $this->scc->getChunkedFiles();

        $date = date("m-d-y"); 

        $firstFile = "/test.csv_part_{$date}_1";

        $lastFile = "/test.csv_part_{$date}_20"; 

        $this->assertStringEndsWith($firstFile, $chunkedFiles[0]);

        $this->assertStringEndsWith($lastFile, $chunkedFiles[950]);
    }

    function testCorrectNumberOfChunkedFiles()
    {
        $chunkedFiles = $this->scc->getChunkedFiles();

        $this->assertEquals(count($chunkedFiles), 20);
    }

    function testTmpChunkDirMatchesCorrectNumber()
    {
        
        $chunkedFiles = $this->scc->getChunkedFiles();
        
        $directoryContents = glob($this->scc->getTargetPath() . 'test.csv_part_*');


        $this->assertEquals(count($directoryContents), 20);
    }

    function tearDown()
    {
        //exec("rm -rf {$this->scc->getTargetPath()}/test.csv_part_*");
    }
}
