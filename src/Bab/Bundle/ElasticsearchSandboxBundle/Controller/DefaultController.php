<?php

namespace Bab\Bundle\ElasticsearchSandboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Bab\Bundle\ElasticsearchSandboxBundle\Parsing\Lexer;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('query', 'textarea', ['label' => 'Run a query'])
            ->add('submit', 'submit')
            ->getForm()
        ;

        $query = new \Elastica\Query();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();

            $query = $this->container->get('parsing.parser')->parse($data['query']);
        }

        $query->setSize(5);

        $search = new \Elastica\Search($this->container->get('client.elastica'));
        $response = $search->addIndex('twitter_river')->search($query);
        $responseResults = $response->getResults();

        $results = array();
        array_walk($responseResults, function ($value) use (&$results) {
            $results[$value->getId()] = $value->getData();
        });

        return $this->render('BabElasticsearchSandboxBundle:Default:index.html.twig', array(
            'form'    => $form->createView(),
            'results' => $results,
            'query'   => json_encode($query->toArray(), true)
        ));
    }
}
