<?php

namespace AppBundle\Controller\System;

use AppBundle\Utils\Kenn;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\MenuItem;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * @Route("/manage_menu")
 */
class MenuController extends Controller
{
    const MODULE = "MENU";

	/**
	 * @Route("", name="manage_menu", options={"main"=true})
	 */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted("entrance",self::MODULE);

        return $this->render('template/system/manage_menu/index.html.twig', []);
    }


    /**
     * @Route("/ajax_get_menu_by_group", name="ajax_get_menu_by_group",options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @param Kenn $kenn
     * @return JsonResponse
     */
 	public function ajaxGetAction(Request $request, Kenn $kenn)
 	{

        $groupId = $request->get('group');

 	    $data = $this->getDoctrine()
 	    	->getManager()
 	    	->getRepository("AppBundle:MenuItem")
 	    	->findBy(["groupId" => $groupId],["menuOrder" => "ASC"]);

 	    $normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());

 	    $res = ["data" => []];

        if(count($data) > 0 ){
            foreach($data as $item){
                $res['data'][] = $normalizer->normalize($item);
            }
        }
        
 	    return new JsonResponse($kenn->toTree($res['data']));
 	}


    /**
     * @Route("/ajax_save", name="ajax_menu_save", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
    
    public function ajaxSaveAction(Request $request)
    {
        if(!$this->isGranted("edit",self::MODULE)){
            return new JsonResponse(array("message" => $this->get('translator')->trans("permission.denied")),403);
        }

        $data = json_decode($request->getContent(), true);
        $menu = $data['menu'];
        $groupId = $data['group_id'];

        $em = $this->getDoctrine()
            ->getManager();

        $model = $this->getDoctrine()
            ->getRepository('AppBundle:Groups')
            ->find($groupId);

        if(!$model){
            throw $this->createNotFoundException('No group was found');
        }

        $em->createQuery("DELETE FROM AppBundle:MenuItem mi WHERE mi.groupId = :groupId")
            ->setParameter(":groupId",$groupId)
            ->execute();

        $this->updateMenu($menu, 0, $groupId);

        return new JsonResponse(['message' => 'Menu has been successfully updated.']);
    }

    /**
     * @Route("/ajax_get_menu_module", name="ajax_get_menu_module",options={"expose"= true})
     * @Method("GET")
     */
    public function ajaxGetMenuModuleAction(){
        $serializer = $this->get('serializer');

        $data = $this->getDoctrine()
            ->getRepository('AppBundle:Module')
            ->findBy([],['sortOrder' => 'ASC']);

        $serialize = $serializer->normalize($data);

        return new JsonResponse($serialize);
    }

    /**
     * @Route("/ajax_get_menu_group", name="ajax_get_menu_group",options={"expose"= true})
     * @Method("GET")
     */
    public function ajaxGetMenuGroupAction(){
        $serializer = $this->get('serializer');

        $data = $this->getDoctrine()
            ->getRepository('AppBundle:Groups')
            ->findBy(["status" => 1],['groupName' => 'ASC']);

        $serialize = $serializer->normalize($data);

        return new JsonResponse($serialize);
    }


    function updateMenu($menu,$parent_id,$groupId){

        $order = 0;
        $em = $this->getDoctrine()
            ->getManager();

        foreach($menu as $row){
            $order += 10;
            $menu_link = ($row['menu_link'] == null || $row['menu_link'] == "") ? "javascript:;" : $row['menu_link'];
            $menu_target = ($row['menu_target'] == 'none') ? null : $row['menu_target'];

            $menuItem = new MenuItem();
            $menuItem->setMenuLabel($row['menu_label']);
            $menuItem->setMenuLink($menu_link);
            $menuItem->setMenuIcon($row['menu_icon']);
            $menuItem->setMenuTarget($menu_target);
            $menuItem->setMenuOrder($order);
            $menuItem->setGroupId($groupId);
            $menuItem->setParentId($parent_id);
            $menuItem->setMenuType($row['menu_type']);

            $em->persist($menuItem);
            $em->flush();

            if(isset($row['children']) && count($row['children']) > 0 ){
                $this->updateMenu($row['children'], $menuItem->getMenuId(), $groupId);
            }
        }

    }


   
}
