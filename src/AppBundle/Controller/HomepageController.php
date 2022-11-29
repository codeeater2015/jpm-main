<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HomepageController extends Controller
{
    /**
     * @Route("", name="homepage",options={"expose"=true,"main"=true})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT m FROM AppBundle:MenuItem m WHERE m.groupId = :groupid AND m.menuLink <> 'javascript:;' ORDER BY m.parentId,m.menuOrder ASC")
                    ->setParameter("groupid", $user->getGroup()->getId());
        $result = $query->getResult();

        $data = array();
        foreach($result as $row){
            $data[] = array(
                "menuLabel" => $row->getMenuLabel(),
                "menuIcon"  => $row->getMenuIcon(),
                "menuLink" => ($this->routeExists($row->getMenuLink())) ? $this->generateUrl($row->getMenuLink(),[],UrlGeneratorInterface::ABSOLUTE_URL) : "javascript:;"
            );
        }

        return $this->render('template/homepage/index.html.twig', [
            "menus" => $data
        ]);
    }

    function routeExists($name)
    {
        $router = $this->get('router');
        return (null === $router->getRouteCollection()->get($name)) ? false : true;
    }
}
