<?php

namespace arrabassada\FrontendBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {

        $this->assertTrue(1 > 0);
    }
}