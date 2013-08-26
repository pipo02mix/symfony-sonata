<?php

namespace arrabassada\FrontendBundle\Tests\Util;

use arrabassada\FrontendBundle\Util\FileUploader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FileUploaderTest extends \PHPUnit_Framework_TestCase
{   
    private $request;
    private $response;
    private $options;
    private $dir;
    
    protected function setUp()
    {
        $this->dir = 1;
        $this->request = new Request(array('id' => $this->dir));
        $this->response = new Response();
        $this->options = array(
            "tmp_dir" => __DIR__.'/../../../../../web/tmp',
            "webpath_tmp" => '/tmp/',
            "save_dir" => __DIR__.'/../../../../../web/tmp/a',
            "webpath" => '/tmp/a'
        );
        mkdir($this->options["save_dir"].$this->dir, 0777, true);
    }
    
    public function testUpload()
    {
     
        $uploader = new FileUploader($this->request, $this->response, $this->options);
        $response = $uploader->uploadFiles();   
        
        $this->assertEquals("Symfony\Component\HttpFoundation\Response", get_class($response));
    }
    
    protected function tearDown()
    {
        rmdir($this->options["save_dir"].$this->dir);
    }
}
