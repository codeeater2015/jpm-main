<?php
namespace AppBundle\Security;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManager;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    private $em;
    private $container;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function onLogoutSuccess(Request $request)
    {
        $referer = $request->headers->get('referer');

        if ($this->container->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $currentUser = $this->container->get('security.token_storage')->getToken()->getUser();
            $now = new \DateTime('now');

            $account = $this->em->getRepository('AppBundle:User')->find($currentUser->getId());
            $account->setLastLogin($now);
            $account->setIsOnline('NO');
            $this->em->flush();
        }


        return new RedirectResponse($referer);
    }
}
