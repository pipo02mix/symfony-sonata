<?php

namespace arrabassada\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use arrabassada\FrontendBundle\Util\CSVImporter;
use arrabassada\FrontendBundle\Util\FileUploader;
use arrabassada\FrontendBundle\Util\GoogleGeocoder;
use arrabassada\FrontendBundle\Entity\Inmueble;
use arrabassada\FrontendBundle\Entity\Promocion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\File;

class DefaultController extends Controller
{
    public function indexAction()
    {        
        $em = $this->getDoctrine()->getEntityManager(); 
        
        $zonas = $em->getRepository('FrontendBundle:Zona')->findBy(array('activa' => true));
        $inmuebles = $em->getRepository('FrontendBundle:Inmueble')->findBy(array('estado' => 'activo'));

        
        return $this->render('FrontendBundle:Inmueble:list_inmuebles.html.twig', array(
            'zonas' => $zonas,
            'inmuebles' => $inmuebles,
            '_NUM_INMUEBLES_PAGE' => Inmueble::NUM_INMUEBLES_PAGE
        ));
    }
    
    public function listObraNuevaAction()
    {
        $em = $this->getDoctrine()->getEntityManager(); 
        
        $promociones = $em->getRepository('FrontendBundle:Promocion')->findAll();
                
        return $this->render('FrontendBundle:ObraNueva:list_promociones.html.twig', array(
            'promociones' => $promociones
        ));
    }
    
    public function viewObraNuevaAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager(); 
        
        $promocion = $em->getRepository('FrontendBundle:Promocion')->findOneBySlugAndCountVisit($slug);
        
        return $this->render('FrontendBundle:ObraNueva:view_promocion.html.twig', array(
            'seccion' => $promocion->getTitulo(),
            'promocion' => $promocion
        ));
    }
    
    public function viewInmuebleAction($slug)
    {
        $em = $this->getDoctrine()->getEntityManager(); 
        
        $inmueble = $em->getRepository('FrontendBundle:Inmueble')->findOneBySlugAndCountVisit($slug);
        $inmueblesRelacionados = $em->getRepository('FrontendBundle:Inmueble')->findInmueblesRelacionados($inmueble);
        
        return $this->render('FrontendBundle:Inmueble:view_inmuebles.html.twig', array(
            'seccion' => $inmueble->getTitulo(),
            'inmueblesRelacionados' => $inmueblesRelacionados,
            'inmueble' => $inmueble
        ));
    }
    
    public function contactoInmuebleAction($slug)
    {
        $request = $this->get('request');
        
        if ($request->isMethod('POST')) {
            $data = $request->request;
            
            if (!$data->get('checker')) {
                
                $inmueble = $this->getDoctrine()->getRepository('FrontendBundle:Inmueble')->findOneBySlug($slug);
                
                $mensaje = \Swift_Message::newInstance()
                           ->setSubject('Contacto de la web por el piso '.$inmueble->getTitulo())
                           ->setFrom('pipo02mix@gmail.com')
                           ->setTo('pipo02mix@gmail.com')
                           ->setBody(" Nombre: ".$data->get('name')."\n Email o teléfono: ".$data->get('email')."\n Referente al piso: REF ".$inmueble->getReferencia()."  ".$inmueble->getTitulo()."Consulta: ".$data->get('message'));

                $result = $this->get('mailer')->send($mensaje);

                $this->get('session')->getFlashBag()->set('notice', 'Nos pondremos en contacto lo antes posible, gracias.');
            } else {
                $this->get('session')->getFlashBag()->set('notice', 'Ha sucedido un error, prueba de nuevo en unos minutos.');
            }
        }
        
        return $this->redirect($this->generateUrl('view_inmuebles', array('slug' => $slug)));
    }
    
    public function listInmueblesAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager(); 
    
        if ($request->getMethod() === 'POST') {
            $inmuebles = $em->getRepository('FrontendBundle:Inmueble')->findInmueblesSearch($request->request->get('search'));
        } else {
            $inmuebles = $em->getRepository('FrontendBundle:Inmueble')->findInmuebles();
        }
        
        return new Response(json_encode($inmuebles));
    }
        
    public function importInmueblesAction()
    {
        $filename = __DIR__.'/../../../../web/uploads/data.csv';
        $file = $this->getRequest()->files->get('csv');
        if (!is_file($file)) {
            throw $this->createNotFoundException("Archivo no adjunto o con errores");
        }
        
        $file->move(__DIR__.'/../../../../web/uploads/', 'data.csv');
                
        $em = $this->getDoctrine()->getEntityManager();
        $validator = $this->get('validator');
        
        //set all inmuebles as desactivate
        $activeInmuebles = $em->getRepository('FrontendBundle:Inmueble')->findBy(array('estado' => Inmueble::ESTADO_ACTIVO ));        
        foreach ($activeInmuebles as $activeInmueble) {
            $activeInmueble->setEstado(Inmueble::ESTADO_DEBAJA);
            $em->persist($activeInmueble);
            $em->flush();
        }
        
        $importer = new CSVImporter($filename, $em, 'arrabassada\FrontendBundle\Entity\Inmueble', $validator);
        
        $report = $importer->parse();
        
        //REFACTORIZAR
        
        $debajaInmuebles = $em->getRepository('FrontendBundle:Inmueble')->findBy(array('estado' => Inmueble::ESTADO_ACTIVO ));
        foreach ($debajaInmuebles as $debajaInmueble) {
            $direccion = $debajaInmueble->getDireccion();
            if ($direccion) {
                $debajaInmueble->setCoordenadas(GoogleGeocoder::getLocation($direccion));
            }
            $debajaInmueble->setTags($debajaInmueble->getTitulo().' REF'.$debajaInmueble->getReferencia().' '.$debajaInmueble->getTipologia().' '.$debajaInmueble->getZona());
            $em->persist($debajaInmueble);
            $em->flush();
        }
        
        if ($report) {
            $this->get('session')->setFlash('sonata_flash_error', $report);
        } else {
            $this->get('session')->setFlash('sonata_flash_success', 'La importación se ha realizado con exito');
        }        
        
        return $this->redirect($this->generateUrl('sonata_admin_dashboard'));
    }
    
    
    public function uploadImageAction($entity, $id = null)
    {
        $request = $this->get('request');
        $response = new Response();
        $options = array(
            "tmp_dir" => __DIR__.constant("arrabassada\FrontendBundle\Entity\\".$entity."::TMP_DIR"),
            "webpath_tmp" => constant("arrabassada\FrontendBundle\Entity\\".$entity."::WEBPATH_TMP"),
            "save_dir" => __DIR__.constant("arrabassada\FrontendBundle\Entity\\".$entity."::SAVE_DIR"),
            "webpath" => constant("arrabassada\FrontendBundle\Entity\\".$entity."::WEBPATH"),
            "entity" => strtolower($entity)
        );
     
        $uploader = new FileUploader($request, $response, $options);
        $uploader->uploadFiles();
        
        return $response;
    }
    
    
    public function deleteImageAction($entity, $id, $image)
    {
        $request = $this->get('request');
        $response = new Response();
        $fs = new Filesystem();
        $file = __DIR__.'/../../../../web/uploads/'.strtolower($entity).'_'.$id.'/'.$image;
        
        $em = $this->getDoctrine()->getEntityManager();
        $inmueble = $em->getRepository('FrontendBundle:Inmueble')->find($id);        
         
        $imagenes = $inmueble->getImagenes();
        
        if(($key = array_search($image, $imagenes)) !== false) {
            unset($imagenes[$key]);
            $inmueble->setImagenes($imagenes);
            $em->persist($inmueble);
            $em->flush();
        }
        
        if ($request->isMethod('DELETE') && $image !== null && $id !== null && $fs->exists($file)) {
            $fs->remove(array($file));
        }
        
        return $response;
    }
    
    public function viewOfrecePropiedadAction()
    {
        return $this->render('FrontendBundle:Estaticas:ofreceunapropiedad.html.twig');
    }
    
    public function staticAction($page)
    {
        return $this->render('FrontendBundle:Estaticas:'.$page.'.html.twig');
    }
}
