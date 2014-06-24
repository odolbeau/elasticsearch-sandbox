<?php

namespace Bab\Bundle\ElasticsearchSandboxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Bab\Bundle\ElasticsearchSandboxBundle\Parsing\Lexer;
use Symfony\Component\Form\FormError;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        // Create search form
        $form = $this->createFormBuilder()
            ->add('query', 'textarea', ['label' => 'Run a query'])
            ->add('submit', 'submit')
            ->getForm()
        ;

        // Retrieve query or use define one
        $query = new \Elastica\Query();
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();

            try {
                $query = $this->container->get('parsing.parser')->parse($data['query']);
            } catch (\Exception $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        $query->setSize(5);
        $query->setSort(array('created_at' => 'desc'));

        // Make the search
        $search = new \Elastica\Search($this->container->get('client.elastica'));
        $response = $search->addIndex('twitter_river')->search($query);
        $responseResults = $response->getResults();

        // Clean results
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
