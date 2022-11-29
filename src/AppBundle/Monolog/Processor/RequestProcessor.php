<?php
namespace AppBundle\Monolog\Processor;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class RequestProcessor
 * @package AppBundle\Monolog\Processor
 */
class RequestProcessor
{
    /**
     * @var RequestStack
     */
    protected $request;
    private $tokenStorage;

    /**
     * RequestProcessor constructor.
     * @param RequestStack $request
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(RequestStack $request, TokenStorageInterface $tokenStorage)
    {

        $this->request = $request;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param array $record
     * @return array
     */
    public function processRecord(array $record)
    {

        $req = $this->request->getCurrentRequest();

        $record['extra']['client_ip']       = $req->getClientIp();
        $record['extra']['client_port']     = $req->getPort();
        $record['extra']['uri']             = $req->getUri();
        $record['extra']['query_string']    = $req->getQueryString();
        $record['extra']['method']          = $req->getMethod();
        $record['extra']['request']         = $req->request->all();

        return $record;
    }

}