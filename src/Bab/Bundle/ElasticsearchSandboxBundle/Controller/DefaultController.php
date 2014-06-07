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

        $results = array();
        array_walk($response['hits']['hits'], function ($value) use (&$results) {
            $results[$value['_id']] = $value['_source'];
        });

        //$search = new \Elastica\Search($this->container->get('client.elastica'));
        //$response = $search->addIndex('twitter_river')->search();
        //$responseResults = $response->getResults();

        //$results = array();
        //array_walk($responseResults, function ($value) use (&$results) {
            //$results[$value->getId()] = $value->getData();
        //});

        return $this->render('BabElasticsearchSandboxBundle:Default:index.html.twig', array(
            'results' => $results
        ));
    }
}
