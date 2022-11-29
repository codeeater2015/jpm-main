<?php
/**
 * Created by PhpStorm.
 * User: Yoh Kenn
 * Date: 3/1/2017
 * Time: 12:09 AM
 */

namespace AppBundle\EventListener;


use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTAuthenticationListener
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $users = $this->em->getRepository('AppBundle:User')->findOneBy(['username' => $user->getUsername()]);

        $udata = array(
            'username' => $users->getUsername(),
            'name'  => $users->getName(),
            'email' => $users->getEmail()
        );

        $udata["token"] = $data["token"];

        $event->setData($udata);
    }

    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $data = [
            'code'  => 401,
            'message' => 'Bad credentials, please verify that your username/password are correctly set',
        ];

        $response = new JsonResponse($data,401);

        $event->setResponse($response);
    }
}