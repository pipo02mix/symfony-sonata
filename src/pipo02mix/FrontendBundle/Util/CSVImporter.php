<?php

namespace arrabassada\FrontendBundle\Util;

use Doctrine\DBAL\DBALException;

/**
 * CSVImporter class
 * 
 */
class CSVImporter
{
    protected $file;
    protected $filename;
    protected $em;
    protected $validator;
    protected $entity_name;
    protected $report;
    
    /**
     *
     * @param type $filename 
     * @param type $em Entity Manager
     * @param type $entity_name 
     */
    public function __construct($filename, $em, $entity_name, $validator)
    {
        if (!file_exists($filename)) {
            throw new \Exception("File not found");
        }
        if (!$this->file = fopen($filename, "r")) {
            throw new \Exception("Error open the file ".$filename);            
        }
        $this->filename = $filename;
        $this->em = $em;
        $this->validator = $validator;
        $this->entity_name = $entity_name;
        $this->report = '';
    }
    
    public function parse()
    {
        
        $properties = fgetcsv($this->file);
        $properties = $this->filterProperties($properties);
        
        while ($values = fgetcsv($this->file)) {
            $errors= '';
            $aux = array();
            $entity = null;
            
            foreach ($properties as $key=>$property) {
                $aux[$property] = $values[$key];
            }
            
            $entity = $this->em->getRepository($this->entity_name)->findOneByReferencia($aux['Referencia']);
            
            if (!$entity) {
                $entity = new $this->entity_name();    
            } else {
                $entity->setEstado('activo');
            }
            
            $metadata = $this->em->getClassMetadata(get_class($entity));
            
            foreach ($aux as $property=>$value) {
                
                if (!method_exists(get_class($entity), 'set'.$property)) {
                    continue;
                }
                if ($metadata->hasAssociation(strtolower($property))) {
                    $value = $this->em->getRepository('FrontendBundle:'.$property)->find($value);
                } else {
                    $value = $this->filterValue($value);
                }
                
                call_user_func(array($entity, 'set'.$property), $value);
            }
            
            $errors = $this->validator->validate($entity);
            if (count($errors) > 0) {
                $this->report .= 'Referencia: '.$aux["Referencia"].' tiene '.count($errors).' errores: '.PHP_EOL;
                foreach ($errors as $error) {
                    $this->report .= $error->getMessage().PHP_EOL;
                }
                $this->report .= PHP_EOL;
            } else {
                $this->em->persist($entity);
                $this->em->flush();                
            }
            
        }
        
        return $this->report;
    }
    
    public function filterProperties($properties)
    {
        
        foreach ($properties as &$property) {
                $property = preg_replace("/[^a-zA-Z0-9.\/_|+ -]/", '', str_replace(' ', '', ucwords(str_ireplace(array('á', 'é', 'í', 'ó', 'ú'), array('a', 'e', 'i', 'o', 'u'), $property))));
        }
            
        return $properties;
    }
    
    public function filterValue($value)
    {       
        
        if (self::isPrice($value)) {
            return self::convertStringToNumber($value);
        }
        
        return trim(preg_replace("/[^a-zA-Z0-9ÁÉÍÓÚáüéçïàèòíóúñº.,\/_|+ \-]/", '', $value));
    }
    
    private function isPrice($value)
    {
        return preg_match("#^([0-9.\s]*,?[0-9]{0,2})[\W]*€$#", $value);
    }
    
    private function convertStringToNumber($value)
    {
        return substr(preg_replace("/[ .€]/", '', $value), 0, -2);
    }


    public function __destruct() {
        fclose($this->file);
    }
}
