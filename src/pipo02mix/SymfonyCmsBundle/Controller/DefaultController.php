<?php

namespace pipo02mix\SymfonyCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use pipo02mix\SymfonyCmsBundle\Entity\Escuela;
use pipo02mix\SymfonyCmsBundle\Form\EscuelaType;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FrontendBundle:Default:index.html.twig', array('name' => 'fer'));
    }
}
