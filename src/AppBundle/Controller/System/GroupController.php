<?php

namespace AppBundle\Controller\System;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Groups;
use AppBundle\Entity\Permission;
use AppBundle\Entity\GroupPermission;

/**
* @Route("/manage_group")
*/

class GroupController extends Controller
{
    const MODULE = "GROUP";

    /**
     * @Route("", name="manage_group", options={"main"=true})
     */
    public function indexAction()
    {

        $this->denyAccessUnlessGranted("entrance",self::MODULE);

        return $this->render("template/system/manage_group/index.html.twig", []);
    }

    /**
     * @Route("/ajax_get", name="ajax_get_group", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetAction(Request $request)
    {

        $columns = array(
            0 => "g.Id",
            1 => "g.groupName",
            2 => "g.groupDesc",
            3 => "status"
        );

        $sWhere = "";
        if (null !== $request->query->get('action') && $request->query->get('action') == "filter") {
            $filter['g.groupName'] = $request->query->get('group_name');
            $filter['g.groupDesc'] = $request->query->get('group_desc');
            $sWhere = "AND (";
            foreach($filter as $key => $value){
                $searchValue = $filter[$key];
                if($searchValue != null || !empty($searchValue)) {
                    $sWhere .= " " . $key . " LIKE '%" . $searchValue . "%' OR ";
                }
            }
            $sWhere = substr_replace($sWhere, "", -3);
            $sWhere .= ')';

            if($sWhere == "AN)"){
                $sWhere = "";
            }

            $select['g.status'] = $request->query->get('status');
            foreach($select as $key => $value){
                $searchValue = $select[$key];
                if($searchValue != null || !empty($searchValue)) {
                    $sWhere .= " AND " . $key . " = '" . $searchValue . "'";
            }
            }
        }

        $sOrder = "";
        if(null !== $request->query->get('order')){
            $sOrder = "ORDER BY  ";
            for ( $i=0 ; $i<intval(count($request->query->get('order'))); $i++ )
            {
                if ( $request->query->get('columns')[$request->query->get('order')[$i]['column']]['orderable'] )
                {
                    $sOrder .= " ".$columns[$request->query->get('order')[$i]['column']]." ".
                        ($request->query->get('order')[$i]['dir']==='asc' ? 'ASC' : 'DESC') .", ";
                }
            }

            $sOrder = substr_replace( $sOrder, "", -2 );
            if ( $sOrder == "ORDER BY" )
            {
                $sOrder = "";
            }
        }

        $start = 1;
        $length = 1;
        if(null !== $request->query->get('start') && null !== $request->query->get('length')){
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager();
        $recordsTotal = $em->createQuery("SELECT count(g) FROM AppBundle:Groups g ")
            ->getSingleScalarResult();

        $query = $em->createQuery("SELECT g.id, g.groupName, g.groupDesc, g.accessLevel, g.allowRead, g.allowWrite, (case when(g.status = 1) then 'Active' else 'Inactive' end) as status 
                                  FROM AppBundle:Groups g 
                                  WHERE 1=1 ".$sWhere." ".$sOrder)
            ->setFirstResult($start)
            ->setMaxResults($length);

        $paginator = new Paginator($query, $fetchJoinCollection = false);
        $paginator ->setUseOutputWalkers(false);

        $serializer = $this->get('serializer');
        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] =  $serializer->normalize($paginator->getIterator());
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $paginator->count();
        $res['draw'] = $draw;

        return new JsonResponse($res);

    }

    /**
     * @Route("/ajax_create", name="ajax_create_group", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxCreateAction(Request $request)
    {
        if(!$this->isGranted("add",self::MODULE)){
            return new JsonResponse(array("message" => $this->get('translator')->trans("permission.denied")),403);
        }

        $em = $this->getDoctrine()->getManager();
        $validator = $this->get('validator');
       
        $data = json_decode($request->getContent(),true);
        $params = [];
        $params['group_name'] = ($data['name'] != null || $data['name'] != "") ? $data['name'] : null;
        $params['group_description'] = ($data['description'] != null || $data['description'] != "") ? $data['description'] : null;
        $params['status'] = $data['status'];
        $params['access_level'] = ($data['accessLevel'] != null && $data['accessLevel']) ? $data['accessLevel'] : null;
        $params['allow_read'] = ($data['allowRead'] != null && $data['allowRead'] != "") ? $data['allowRead'] : null;
        $params['allow_write'] = ($data['allowWrite'] != null && $data['allowRead'] != "") ? $data['allowRead'] : null;

        $group = new Groups();
        $group->setGroupName($params['group_name']);
        $group->setAccessLevel($params['access_level']);
        $group->setAllowRead($params['allow_read']);
        $group->setAllowWrite($params['allow_write']);
        $group->setGroupDesc($params['group_description']);
        $group->setStatus($params['status']);

        $violations = $validator->validate($group);
        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
               $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }

            return new JsonResponse(array("validation_error" => $errors, "message" => "You have some form errors. Please check below."),400);
        }

        $em->persist($group);
        $em->flush();

        return new JsonResponse(array("message" => "Group added successfully."));
    }

    /**
     * @Route("/ajax_update", name="ajax_update_group", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("PATCH")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxUpdateAction(Request $request)
    {
        if(!$this->isGranted("edit",self::MODULE)){
            return new JsonResponse(array("message" => $this->get('translator')->trans("permission.denied")),403);
        }

        $em = $this->getDoctrine()->getManager();
        $validator = $this->get('validator');
       
        $data = json_decode($request->getContent(),true);
        $params = [];
        $params['group_name'] = ($data['name'] != null || $data['name'] != "") ? $data['name'] : null;
        $params['group_description'] = ($data['description']) ? $data['description'] : null;
        $params['access_level'] = ($data['accessLevel']) ? $data['accessLevel'] : null;
        $params['allow_read'] = $data['allowRead'] ? $data['allowRead'] : 0;
        $params['allow_write'] = $data['allowWrite'] ? $data['allowWrite'] : 0;
        $params['status'] = $data['status'];
        $params['id'] = $data['id'];

        $group = $this->_getModel($params['id']);
        if(!$group){
            return new JsonResponse(['message' => 'Unable to update, group not exist'],404);
        }

        $group->setGroupName($params['group_name']);
        $group->setGroupDesc($params['group_description']);
        $group->setAccessLevel($params['access_level']);
        $group->setAllowRead($params['allow_read']);
        $group->setAllowWrite($params['allow_write']);
        $group->setStatus($params['status']);

        $violations = $validator->validate($group);
        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
               $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse(array("validation_error" => $errors, "message" => "You have some form errors. Please check below."),400);
        }

        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets();
        $aChangeSet =  $uow->isEntityScheduled($group);

        if(!$aChangeSet){
            return new JsonResponse(array("message" => "No changes have been made."));
        }

        $em->flush();

        return new JsonResponse(array("message" => "Group updated successfully."));
    }

    /**
     * @Route("/ajax_delete", name="ajax_delete_group",defaults={"_format"="json"}, options={"expose"=true})
     * @Method("DELETE")
     * @param Request $request
     * @return JsonResponse
     */
    
    public function deleteAction(Request $request)
    {
        if(!$this->isGranted("delete",self::MODULE)){
            return new JsonResponse(array("message" => $this->get('translator')->trans("permission.denied")),403);
        }

        $em = $this->getDoctrine()->getManager();

        $id = $request->get('id');
        
        $group = $this->_getModel($id);
        if(!$group){
            return new JsonResponse(['message' => 'Unable to delete, user not exist'],404);
        }


        $em->remove($group);
        $em->flush();

        return new JsonResponse(array("message" => "Group deleted successfully."));
    }

    /**
     * @Route("/ajax_batch_delete", name="ajax_batch_delete_group",defaults={"_format"="json"}, options={"expose"=true})
     * @Method("DELETE")
     * @param Request $request
     * @return JsonResponse
     */

    public function batchDeleteAction(Request $request)
    {

        if(!$this->isGranted("delete",self::MODULE)){
            return new JsonResponse(array("message" => $this->get('translator')->trans("permission.denied")),403);
        }

        $em = $this->getDoctrine()->getManager();

        $id = $request->get('id');

        for($i=0;$i<count($id);$i++){
            $group = $this->_getModel($id[$i]);
            if(!$group){
                return new JsonResponse(['message' => 'Unable to delete batch request, some user not exist'],404);
            }
            $em->remove($group);
        }

        $em->flush();
        $em->clear();

        return new JsonResponse(array("message" => "Selected Group deleted successfully."));
    }

    private function _getModel($id){
        $model = $this->getDoctrine()
            ->getRepository('AppBundle:Groups')
            ->find($id);

        return $model;
    }

    /**
     * @Route("/ajax_select2_group_module", name="ajax_select2_group_module",options={"expose"= true})
     * @Method("GET")
     */
    public function ajaxSelect2GroupModuleAction(){

        $result = $this->getDoctrine()
            ->getRepository('AppBundle:Module')
            ->findBy([],['sortOrder' => 'ASC']);

        $data[] = array("id" => "","text" => "");
        foreach($result as $row){
            $data[] = array(
                "id" => $row->getId(),
                "text" => $row->getModuleLabel()
            );
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_get_permission_by_module/{groupId}/{moduleId}", name="ajax_get_permission_by_module", options={"expose"=true})
     * @Method("GET")
     * @param $groupId
     * @param $moduleId
     * @return JsonResponse
     */
    public function ajaxGetPermissionByModuleAction($groupId,$moduleId){
        $serializer = $this->get('serializer');

        $permission = $this->getDoctrine()
            ->getRepository('AppBundle:Permission')
            ->findBy(['moduleId' => $moduleId],['id' => 'ASC']);

        $em = $this->getDoctrine()->getManager();
        $group_permission = $em->createQuery("SELECT gp FROM AppBundle:GroupPermission gp JOIN gp.permission p WHERE gp.groupId = :groupId AND p.moduleId = :moduleId")
            ->setParameters(array('groupId' => $groupId, "moduleId" => $moduleId))
            ->getResult();

        $serialize_p = $serializer->normalize($permission);
        $serialize_gp = $serializer->normalize($group_permission);

        $data = array(
            "permission" => $serialize_p,
            "group_permission" => $serialize_gp
        );
        return new JsonResponse($data);
    }


    /**
     * @Route("/ajax_save_group_permission", name="ajax_save_group_permission", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxSaveGroupPermission(Request $request){

        if(!$this->isGranted("edit",self::MODULE)){
            return new JsonResponse(array("message" => $this->get('translator')->trans("permission.denied")),403);
        }

        $group = (null === $request->request->get('group')) ? "" : $request->request->get('group');
        $module = (null === $request->request->get('module')) ? "" : $request->request->get('module');
        $permission = (null === $request->request->get('permission')) ? [] : $request->request->get('permission');

        $em = $this->getDoctrine()->getManager();
        $gp = $em->createQuery("SELECT gp FROM AppBundle:GroupPermission gp JOIN gp.permission p WHERE gp.groupId = :groupId AND p.moduleId = :moduleId")
            ->setParameters(array('groupId' => $group, "moduleId" => $module))
            ->getResult();

        foreach($gp as $gps) {
            $em->remove($gps);
        }

        for($i=0;$i<count($permission);$i++){
            $current_permission = $em->getRepository("AppBundle:Permission")->find($permission[$i]);
            $group_permission = new GroupPermission();
            $group_permission->setGroupId($group);
            $group_permission->setPermission($current_permission);
            $em->persist($group_permission);
        }

        $em->flush();

        return new JsonResponse(array("message" => "Group permission updated successfully."));
    }
}
