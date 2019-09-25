<?php

namespace Abc\JobBundle\Controller;

use Abc\Job\HttpServer;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JobController extends AbstractController
{
    /**
     * @var HttpServer
     */
    private $httpServer;

    public function __construct(HttpServer $httpServer)
    {
        $this->httpServer = $httpServer;
    }

    /**
     * @Route("/job", methods="GET")
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        return $this->createResponse($this->httpServer->index($request->getQueryString(), $request->getUri()));
    }

    /**
     * @Route("/job", methods="POST")
     *
     * @param Request $request
     * @return Response
     */
    public function process(Request $request)
    {
        return $this->createResponse($this->httpServer->process($request->getContent(), $request->getUri()));
    }

    /**
     * @Route("/job/{id}", methods="GET")
     *
     * @param string $id
     * @return Response
     */
    public function result(string $id, Request $request): Response
    {
        return $this->createResponse($this->httpServer->result($id, $request->getUri()));
    }

    /**
     * @Route("/job/{id}/restart", methods="PUT")
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    public function restart(string $id, Request $request): Response
    {
        return $this->createResponse($this->httpServer->restart($id, $request->getUri()));
    }

    /**
     * @Route("/job/{id}/cancel", methods="PUT")
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    public function cancel(string $id, Request $request): Response
    {
        return $this->createResponse($this->httpServer->cancel($id, $request->getUri()));
    }

    /**
     * @Route("/job/{id}", methods="DELETE")
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    public function delete(string $id, Request $request): Response
    {
        return $this->createResponse($this->httpServer->delete($id, $request->getUri()));
    }

    private function createResponse(ResponseInterface $response): Response
    {
        return new Response($response->getBody(), $response->getStatusCode(), $response->getHeaders());
    }
}
