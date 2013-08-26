<?php

namespace arrabassada\FrontendBundle\Tests\Util;

use arrabassada\FrontendBundle\Util\CSVImporter;
require_once __DIR__.'/../../../../../app/AppKernel.php';

class CSVImporterTest extends \PHPUnit_Framework_TestCase
{   
    private $filename;
    
    protected static $kernel;
    protected static $container;

    public static function setUpBeforeClass()
    {
        self::$kernel = new \AppKernel('dev', true);
        self::$kernel->boot();

        self::$container = self::$kernel->getContainer();
    }

    public function get($serviceId)
    {
        return self::$kernel->getContainer()->get($serviceId);
    }
    
    protected function setUp()
    {
        $this->filename = __DIR__.'/prueba.csv';
        file_put_contents($this->filename, '');
    }
    
    public function testCSVImporter()
    {
        $importer = new CSVImporter($this->filename, null, '', $this->get('validator'));
        
        $value = $importer->filterValue("180.000,00 €");
        
        $this->assertEquals('180.000,00', $value);
    }
    
    protected function tearDown()
    {
        unlink($this->filename);
    }
}
