<?php

namespace Bab\Bundle\ElasticsearchSandboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $response = $this->container->get('client.elasticsearch')->search(array(
            'index' => 'twitter_river',
            'type'  => 'status'
        ));

        return $this->render('BabElasticsearchSandboxBundle:Default:index.html.twig', array(
            'results' => $response['hits']['hits']
        ));
    }
}
