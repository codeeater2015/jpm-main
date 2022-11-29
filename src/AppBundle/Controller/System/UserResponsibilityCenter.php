<?php

namespace AppBundle\Controller\System;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/manage_user_rcenter")
 */
class UserResponsibilityCenter extends Controller
{
    const USER_RCENTER = "USER_RCENTER";
    /**
     * @Route("", name="manage_user_rcenter", options={"main"=true})
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(){
        $this->denyAccessUnlessGranted("entrance",self::USER_RCENTER);

        $em_default = $this->getDoctrine()->getManager();
        $em_pgpis = $this->getDoctrine()->getManager('pgpis');

        $users = $em_default->createQuery('SELECT u.id, u.username, u.name 
                                  FROM AppBundle:User u 
                                  ORDER BY u.id DESC')->getResult();

        $sql = "SELECT rc_code, rc_desc, rc_project 
                FROM PGP_RCENTER 
                ORDER BY rc_code ASC";
        $stmt = $em_pgpis->getConnection()->executeQuery($sql);
        $rcenters = array();
        while($row = $stmt->fetch()){
            $rcenters[] = $row;
        }

        return $this->render("template/system/manage_user_rcenter/index.html.twig",[
            "users" => $users,
            "rcenters" => $rcenters
        ]);
    }

    /**
     * @Route("/ajax_get_user_list", name="ajax_get_user_list", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetUserListAction(Request $request){
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array("success" => false, "message" => "Ajax Only"));
        }

        $em = $this->getDoctrine()->getManager();

        $user = $em->createQuery('SELECT u.id, u.username, u.name FROM AppBundle:User u ORDER BY u.id DESC')->getResult();

        $serializer = $this->get('serializer');
        $data = $serializer->normalize($user);
        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_save_user_rcenter", name="ajax_save_user_rcenter", options={"expose"=true})
     * @Method("POST")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxSaveUserRCenter(Request $request){
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array("success" => false, "message" => "Ajax Only"));
        }

        if(!$this->isGranted("add",self::USER_RCENTER)){
            return new JsonResponse(array("message" => $this->get('translator')->trans("permission.denied")),403);
        }

        $em = $this->getDoctrine()->getManager();
        $userid = intval($request->request->get('userid'));
        $rcenter = $request->request->get('rcenter');

        $q = $em->createQuery('DELETE FROM AppBundle:UserResponsibilityCenter ur WHERE ur.userid = :userid');
        $q->setParameter("userid", $userid);
        $q->execute();

        if($rcenter != null){
            $batchSize = 20;
            for($i=0;$i < count($rcenter);$i++){
                $user = $em->getRepository("AppBundle:User")->find($userid);
                $user_rcenter = new \AppBundle\Entity\UserResponsibilityCenter();
                $user_rcenter->setUserid($user);
                $user_rcenter->setRcCode($rcenter[$i]);
                $em->persist($user_rcenter);
                if (($i % $batchSize) === 0) {
                    $em->flush();
                    $em->clear();
                }
            }
        }

        $em->flush();
        $em->clear();

        return new JsonResponse(array("success" => true,"message" => "Successfully updated."));
    }

    /**
     *
     * @Route("/ajax_get_user_rcenter", name="ajax_get_user_rcenter", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetUserRCenterAction(Request $request){
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array("success" => false, "message" => "Ajax Only"));
        }

        $em = $this->getDoctrine()->getManager();
        $userid = $request->query->get('userid');

        $sql = "SELECT * FROM tbl_user_rcenter WHERE userid = ? ORDER BY rc_code ASC";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindParam(1,$userid);
        $stmt->execute();
        $user_rcenter = $stmt->fetchAll();

        return new JsonResponse($user_rcenter);
    }

    /**
     *
     * @Route("/ajax_get_rcenter", name="ajax_get_rcenter", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetRCenterAction(Request $request){
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(array("success" => false, "message" => "Ajax Only"));
        }

        $pgpis = $this->getDoctrine()->getManager('pgpis');

        $sql = "SELECT rc_code, rc_desc, rc_project FROM PGP_RCENTER ORDER BY rc_code ASC";
        $stmt = $pgpis->getConnection()->prepare($sql);
        $stmt->execute();
        $rcenter = $stmt->fetchAll();


        return new JsonResponse($rcenter);
    }

}