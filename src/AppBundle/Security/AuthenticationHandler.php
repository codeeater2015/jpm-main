<?php

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AuthenticationHandler implements AuthenticationSuccessHandlerInterface
{
    protected
        $container,
        $security;


    public function __construct(ContainerInterface $container, AuthorizationChecker $security)
    {
        $this->container = $container;
        $this->security = $security;

    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        // URL for redirect the user to where they were before the login process begun if you want.
        $url = $request->headers->get('referer');

        $currentUser = $this->container->get('security.token_storage')->getToken()->getUser();

        $em = $this->container->get('doctrine')->getManager();
        $account = $em->getRepository('AppBundle:User')->find($currentUser->getId());
        $account->setIsOnline('YES');
        $em->flush();

        $response = new RedirectResponse($url);

        return $response;
    }
}
