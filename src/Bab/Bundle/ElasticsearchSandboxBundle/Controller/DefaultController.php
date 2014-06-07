<?php

namespace Bab\Bundle\ElasticsearchSandboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('BabElasticsearchSandboxBundle:Default:index.html.twig');
    }
}
