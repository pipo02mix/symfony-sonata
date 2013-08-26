<?php

namespace pipo02mix\SymfonyCmsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
        private $repo;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->repo = $kernel->getContainer()
                             ->get('doctrine.orm.entity_manager')
                             ->getRepository('CmsBundle:Escuela');
    }

    public function testSearchByCategoryName()
    {
        $this->assertNotEquals(0, count($this->repo->find(2)));
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
