<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arrabassada\FrontendBundle\Block\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Form;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Doctrine\ORM\EntityManager;

/**
 *
 * @author     Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class ImporterBlockService extends BaseBlockService
{
    
    /**
     * @param string                                            $name
     * @param \Symfony\Component\Templating\EngineInterface     $templating
     */
    public function __construct($name, EngineInterface $templating)
    {
        parent::__construct($name, $templating);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Importer module';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultSettings()
    {
        return array(
            'stats' => array(),
            'title'   => 'Importar inmuebles'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('title', 'text', array('required' => false)),
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    function validateBlock(ErrorElement $errorElement, BlockInterface $block)
    {
        $errorElement
            ->with('settings.title')
                ->assertNotNull(array())
                ->assertNotBlank()
                ->assertMaxLength(array('limit' => 50))
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockInterface $block, Response $response = null)
    {
        // merge settings
        $settings = array_merge($this->getDefaultSettings(), $block->getSettings());

        return $this->renderResponse('FrontendBundle:Block:block_importer.html.twig', array(
            'form'     => 'hola',
            'block'     => $block,
            'settings'  => $settings
        ), $response);
    }
}