<?php

namespace AppBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTInvalidEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;

class JWTListener
{

    public function onJWTInvalid(JWTInvalidEvent $event)
    {
        $data = [
            'code'  => 401,
            'message' => 'Your token is invalid, please login again to get a new one',
        ];

        $response = new JsonResponse($data, 401);

        $event->setResponse($response);
    }

    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        $data = [
            'code'  => 401,
            'message' => 'Missing Token',
        ];

        $response = new JsonResponse($data, 401);

        $event->setResponse($response);
    }

}