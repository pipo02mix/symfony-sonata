<?php

namespace arrabassada\FrontendBundle\Util;

use Symfony\Component\Finder\Finder;

/**
 * FileUploader class
 * 
 */
class FileUploader
{
    private $request;
    private $response;
    private $finder;
    private $current;
    private $options;
    
    public function __construct($request, $response, $options = array()){
        $this->request = $request;
        $this->response = $response;
        $this->finder = new Finder();
        $this->current = $request->get('id');
        $this->options = array_merge(array(
            "tmp_dir" => __DIR__.'/../../../../web/tmp',
            "webpath_tmp" => '/tmp/',
            "save_dir" => __DIR__.'/../../../../web/uploads/',
            "webpath" => '/uploads/',
            "entity" => '/entity/'
        ), $options);
    }
    
    public function uploadFiles()
    {
        switch ($this->request->getMethod()) {
            case 'HEAD':
            case 'OPTIONS':
                $this->respondInfo();                
                break;
            case 'POST':
                $this->storeFiles();
                break;
            case 'GET':
                $this->serveFiles();
                break;
        }
        
        return $this->response;
    }
    public function respondInfo()
    {
        $this->response->headers->set('Content-Disposition', 'inline; filename="files.json"');
        $this->response->headers->set('X-Content-Type-Options', 'nosniff');
        $this->response->headers->set('Pragma', 'no-cache');
        if ($this->request->isMethod('OPTIONS')) {
            //$this->response->headers->set('Pragma', 'no-cache');                
        }
        $this->response->headers->set('Vary', 'Accept');
        $this->response->headers->set('Content-type', 'text/html; charset=utf-8');
        $this->response->headers->set('Access-Control-Allow-Methods', 'OPTIONS, HEAD, GET, POST, PUT, DELETE');
        $this->response->headers->set('Access-Control-Allow-Origin', '*');
    }
    
    public function storeFiles()
    {
        $files = $this->request->files->all();
        $files = $files["files"];

        $files_response = array("files" => array());

        foreach ($files as $file) {
            $newFile = new \stdClass();
            $name = $file->getClientOriginalName();

            $newFile->name = $file->getClientOriginalName();
            $newFile->thumbnail = $this->options["webpath_tmp"].$name;
            $newFile->thumbnail_url = $this->options["webpath_tmp"].$name;
            $newFile->url = $this->options["webpath_tmp"].$name;
            $newFile->type = $file->getMimeType();
            $newFile->size = $file->getClientSize();
            $newFile->delete_url = $this->options["webpath_tmp"].$name;
            $newFile->delete_type = "DELETE";

            $file->move(
                $this->options["tmp_dir"],
                $name
            );

            $files_response["files"][] = $newFile;
            $content = json_encode($files_response); 

            $this->response->headers->set('Content-type', 'application/json');
            $this->response->setContent($content);    

        }
    }
    
    public function serveFiles()
    {
        $files_response = array("files" => array()); 
        $iterator = $this->finder->files()->in($this->options["save_dir"].$this->current);

        foreach ($iterator as $key=>$file) {
            $newFile = new \stdClass();

            $newFile->name = $file->getFilename();
            $newFile->thumbnail = $this->options["webpath"].$this->current.'/'.$file->getFilename();
            $newFile->thumbnail_url = $this->options["webpath"].$this->current.'/'.$file->getFilename();
            $newFile->url = $this->options["webpath"].$this->current.'/'.$file->getFilename();
            $newFile->type = $file->getType();
            $newFile->size = $file->getSize();
            $newFile->delete_url = '/admin/delete/images/'.$this->options["entity"].'/'.$this->current.'/'.$file->getFilename();
            $newFile->delete_type = "DELETE";

            $files_response["files"][] = $newFile;
        }

        $content = json_encode($files_response); 

        $this->response->headers->set('Content-type', 'application/json');
        $this->response->setContent($content);   
    }
}
