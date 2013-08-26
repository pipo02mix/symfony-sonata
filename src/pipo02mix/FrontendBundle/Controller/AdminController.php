<?php

namespace arrabassada\FrontendBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\Response;
/**
 * Description of AdminController
 *
 * @author pipo02mix
 */
class AdminController extends Controller
{
    public function batchActionActivate(ProxyQueryInterface $query)
    {
        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $modelManager = $this->admin->getModelManager();
        try {
            $models = $modelManager->executeQuery($query);
            
            foreach ($models as $model) {
                $model->setEstado('activo');
                $model->setTags($model->getTitulo().' REF'.$model->getReferencia().' '.$model->getTipologia().' '.$model->getZona());
                $modelManager->update($model);
            }
            
            $this->get('session')->setFlash('sonata_flash_success', 'Inmuebles activados');
        } catch ( ModelManagerException $e ) {
            $this->get('session')->setFlash('sonata_flash_error', 'Error al activar inmuebles');
        }

        return $this->redirect($this->admin->generateUrl('list', array('filter' => $this->admin->getFilterParameters())));
    }
}