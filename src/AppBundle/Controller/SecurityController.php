<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
    * @Route("/login", name="login")
    */
    public function loginAction(AuthenticationUtils $authenticationUtils)
    {

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
           return $this->redirectToRoute('homepage');
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render("template/login/index.html.twig",
                            ['last_username' => $lastUsername,
                              'error'         => $error ]);
    }

    /**
    * @Route("/login_check", name="login_check")
    */
    public function loginCheckAction()
    {
        throw new \Exception('This should never be reached!');
    }

    /**
    * @Route("/logout", name="logout")
    */
    public function logoutAction()
    {
        throw new \Exception('This should never be reached!');
    }


}
