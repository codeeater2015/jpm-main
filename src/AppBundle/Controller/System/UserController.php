<?php
namespace AppBundle\Controller\System;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use AppBundle\Entity\Groups;

/**
* @Route("/manage_user")
*/

class UserController extends Controller
{

    const MODULE = 'USER';

    /**
	 * @Route("", name="manage_user", options={"main"=true})
     * @Method("GET")
	 */
	public function indexAction()
	{
        $this->denyAccessUnlessGranted("entrance", self::MODULE);

        $groups = $this->_getGroups();

		return $this->render('template/system/manage_user/index.html.twig',[
		    "groups" => $groups
        ]);
	}

    /**
     * @Route("/ajax_get_datatable_users_list", name="ajax_get_datatable_users_list", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
	public function ajaxGetDatatableUsersListAction(Request $request)
	{	

        $columns = array(
            0 => "u.id",
            1 => "u.name",
            2 => "u.username",
            3 => "u.gender",
            4 => "g.groupName",
            5 => "isActive"
        );

        $sWhere = "";
        if (null !== $request->query->get('action') && $request->query->get('action') == "filter") {
            $filter['u.name'] = $request->query->get('name');
            $filter['u.username'] = $request->query->get('username');

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

            $select['u.gender'] = $request->query->get('gender');
            $select['g.id'] = $request->query->get('group');
            $select['u.isActive'] = $request->query->get('status');
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
                    $selected_column = $columns[$request->query->get('order')[$i]['column']];
                    $sOrder .= " ".$selected_column." ".
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
        $recordsTotal = $em->createQuery("SELECT count(u) FROM AppBundle:User u JOIN u.group g")
            ->getSingleScalarResult();

        $query = $em->createQuery("SELECT u.id,u.name,u.username,u.gender,u.validUntil, COALESCE(u.contactNo,'') as contactNo,
                                    COALESCE(u.email,'') as email,g.groupName, g.id as groupId, p.provinceCode, p.name as provinceName, u.description,
                                    (case when (u.isActive = 1) then 'Active' else 'Inactive' end) as isActive,
                                    u.isDefault
                                  FROM AppBundle:User u
                                  JOIN u.group g
                                  JOIN u.province p
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
     * @Route("/modal-view-user", name="modal_view_user", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modalViewUserAction(Request $request){
        $id = $request->query->get('id');

        $user = $this->_getUser($id);

        return $this->render("template/system/manage_user/_modal_view_user.html.twig",[
            "user" => $user
        ]);
    }

    /**
     * @Route("/modal-create-new-user", name="modal_create_new_user", options={"expose"=true})
     * @return \Symfony\Component\HttpFoundation\Response
     */
	public function modalCreateUserAction(){
        $groups = $this->_getGroups();
        $projects = $this->_getProjects();

        return $this->render("template/system/manage_user/_modal_create_user.html.twig",[
            "groups" => $groups,
            "projects" => $projects
        ]);
    }

    /**
     * @Route("/modal-edit-user", name="modal_edit_user", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modalEditUserAction(Request $request){
        $id = $request->query->get('id');

        $user = $this->_getUser($id);
        $groups = $this->_getGroups();
        $projects = $this->_getProjects();

        return $this->render("template/system/manage_user/_modal_edit_user.html.twig",[
            "groups" => $groups,
            "projects" => $projects,
            "user" => $user
        ]);
    }

    /**
     * @Route("/modal-change-user-password", name="modal_change_user_password", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function modalChangeUserPasswordAction(Request $request){
        $id = $request->query->get('id');

        return $this->render("template/system/manage_user/_modal_change_user_password.html.twig",[
            "user_id" => $id
        ]);
    }

    /**
     * @Route("/ajax-save-new-user", name="ajax_save_new_user", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
	public function ajaxSaveNewUserAction(Request $request)
	{
	    if(!$this->isGranted("add",self::MODULE)){
            return new JsonResponse(array("message" => $this->get('translator')->trans("permission.denied")),403);
        }

	    $username = (null == $request->request->get('username') && $request->request->get('username') == "") ? null : $request->request->get('username');
	    $password = (null == $request->request->get('password') && $request->request->get('password') == "") ? null : $request->request->get('password');
	    $passwordRepeat = (null == $request->request->get('passwordRepeat') && $request->request->get('passwordRepeat') == "") ? null : $request->request->get('passwordRepeat');
	    $name = (null == $request->request->get('name') && $request->request->get('name') == "") ? null : $request->request->get('name');
        $description = (null == $request->request->get('description') && $request->request->get('description') == "") ? null : $request->request->get('description');
	    $gender = (null == $request->request->get('gender') && $request->request->get('gender') == "") ? null : $request->request->get('gender');
	    $email = (null == $request->request->get('email') && $request->request->get('email') == "") ? null : $request->request->get('email');
	    $contactNo = (null == $request->request->get('contact_no') && $request->request->get('contact_no') == "") ? null : $request->request->get('contact_no');
        $group_id = (null == $request->request->get('group') && $request->request->get('group') == "") ? "" : $request->request->get('group');
        $pro_id = (null == $request->request->get('project') && $request->request->get('project') == "") ? "" : $request->request->get('project');
        $status = (null == $request->request->get('status') && $request->request->get('status') == "") ? 0 : 1;
        $strictAccess = ($request->request->get('strictAccess') == "" || $request->request->get('strictAccess') == "1") ? 1 : 0;
        $requireApproval = ($request->request->get('requireApproval') == "" || $request->request->get('requireApproval') == "1") ? 1 : 0;
        
	    $dateRegistered = date('Y-m-d H:i:s');

        $group = $this->getDoctrine()
            ->getRepository('AppBundle:Groups')
            ->find($group_id);
        
        $project = $this->getDoctrine()
                         ->getRepository('AppBundle:Project')
                         ->find($pro_id);
        $province = $this->getDoctrine()
                         ->getRepository("AppBundle:Province")
                         ->find(53);
                
	    $user = new User();
	    $user->setUsername($username);
	    $user->setPassword($password);
	    $user->setPasswordRepeat($passwordRepeat);
	    $user->setName($name);
        $user->setDescription($description);
	    $user->setGender($gender);
	    $user->setEmail($email);
	    $user->setContactNo($contactNo);
        $user->setRoles("ROLE_PRIVATE_USER");
        $user->setGroup($group);
        $user->setProject($project);
        $user->setIsActive($status);
        $user->setStrictAccess($strictAccess);
        $user->setRequireApproval($requireApproval);
        $user->setDateRegistered(new \DateTime($dateRegistered));
        $user->setProvince($province);
	    
	    $validator = $this->get('validator');
	    $violations = $validator->validate($user,null,["create"]);
	    $errors = [];

	    if(count($violations) > 0){
            foreach( $violations as $violation ){
               $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse(array("validation_error" => $errors, "message" => "You have some form errors. Please check below."),400);
        }

        $encoder = $this->get('security.password_encoder');
        $encodedPassword = $encoder->encodePassword($user,$password);
      	$user->setPassword($encodedPassword);

        $em = $this->getDoctrine()->getManager();
		$em->persist($user);
		$em->flush();

	    return new JsonResponse(array("message" => "New user was created successfully."));
	}

    /**
     * @Route("/ajax-save-update-user", name="ajax_save_update_user", options={"expose"=true})
     * @Method("PATCH")
     * @param Request $request
     * @return JsonResponse
     */

	public function ajaxSaveUpdateUserAction(Request $request)
	{
        if(!$this->isGranted("edit",self::MODULE)){
            return new JsonResponse(array("message" => $this->get('translator')->trans("permission.denied")),403);
        }

        $username = (null == $request->request->get('username') && $request->request->get('username') == "") ? null : $request->request->get('username');
        $name = (null == $request->request->get('name') && $request->request->get('name') == "") ? null : $request->request->get('name');
        $description = (null == $request->request->get('description') && $request->request->get('description') == "") ? null : $request->request->get('description');
        $gender = (null == $request->request->get('gender') && $request->request->get('gender') == "") ? null : $request->request->get('gender');
        $email = (null == $request->request->get('email') && $request->request->get('email') == "") ? null : $request->request->get('email');
        $contactNo = (null == $request->request->get('contact_no') && $request->request->get('contact_no') == "") ? null : $request->request->get('contact_no');
        $group_id = (null == $request->request->get('group') && $request->request->get('group') == "") ? "" : $request->request->get('group');
        $pro_id = (null == $request->request->get('project') && $request->request->get('project') == "") ? "" : $request->request->get('project');
        $status = (null == $request->request->get('status') && $request->request->get('status') == "") ? 0 : 1;
        $strictAccess = ($request->request->get('strictAccess') == "" || $request->request->get('strictAccess') == "1") ? 1 : 0;
        $requireApproval = ($request->request->get('requireApproval') == "" || $request->request->get('requireApproval') == "1") ? 1 : 0;

        $id = $request->request->get('user_id');

        $group = $this->getDoctrine()
            ->getRepository('AppBundle:Groups')
            ->find($group_id);

        $project = $this->getDoctrine()
                         ->getRepository('AppBundle:Project')
                         ->find($pro_id);

	    $user = $this->_getUser($id);

        if(!$user){
            return new JsonResponse(array("message" => "Unable to update, User not exist."),404);
        }

        $user->setUsername($username);
	    $user->setName($name);
        $user->setDescription($description);
	    $user->setGender($gender);
	    $user->setEmail($email);
	    $user->setContactNo($contactNo);
        $user->setGroup($group);
        $user->setProject($project);
        $user->setIsActive($status);
        $user->setStrictAccess($strictAccess);
        $user->setRequireApproval($requireApproval);
	 
	    $validator = $this->get('validator');
	    $violations = $validator->validate($user,null,['edit']);
	    $errors = [];

	    if(count($violations) > 0){
            foreach( $violations as $violation ){
               $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }

            return new JsonResponse(array("validation_error" => $errors, "message" => "You have some form errors. Please check below."),400);
        }

        $em = $this->getDoctrine()->getManager();
        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets();
        $aChangeSet =  $uow->isEntityScheduled($user);

        if(!$aChangeSet){
            return new JsonResponse(array("message" => "No changes have been made."));
        }

		$em->flush();

	    return new JsonResponse(['message' => "User has been updated successfully."]);
	}

    /**
     * @Route("/ajax-save-change-user-password", name="ajax_save_change_user_password", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
	public function ajaxSaveChangeUserPasswordAction(Request $request)
	{
        if(!$this->isGranted("edit",self::MODULE)){
            return new JsonResponse(array("message" => $this->get('translator')->trans("permission.denied")),403);
        }

		$id = (null == $request->request->get('user_id') && $request->request->get('user_id') == "") ? "" : $request->request->get('user_id');
		$user = $this->_getUser($id);

        if(!$user){
            return new JsonResponse(array("message" => "Unable to change password, User not exist."),404);
        }

        $password = (null == $request->request->get('password') && $request->request->get('password') == "") ? null : $request->request->get('password');
        $passwordRepeat = (null == $request->request->get('passwordRepeat') && $request->request->get('passwordRepeat') == "") ? null : $request->request->get('passwordRepeat');

        $user->setPassword($password);
        $user->setPasswordRepeat($passwordRepeat);

        $validator = $this->get('validator');
        $violations = $validator->validate($user,null,['user_change_password']);
        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse(array("validation_error" => $errors, "message" => "You have some form errors. Please check below."),400);
        }

		$encoder = $this->get('security.password_encoder');
        $encodedPassword = $encoder->encodePassword($user,$password);
        $user->setPassword($encodedPassword);
        $em = $this->getDoctrine()->getManager();
        $em->flush();

		return new JsonResponse(['message' => 'Password has been successfully changed.']);
	}

    /**
     * @Route("/ajax_delete_user/{id}", name="ajax_delete_user", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("DELETE")
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
	public function ajaxDeleteUserAction(Request $request,$id)
	{
        if(!$this->isGranted("delete",self::MODULE)){
            return new JsonResponse(array("message" => $this->get('translator')->trans("permission.denied")),403);
        }

		$user = $this->_getUser($id);

        if(!$user){
            return new JsonResponse(['message' => 'Unable to delete, User not exist'],404);
        }

		$em = $this->getDoctrine()->getManager();
		$em->remove($user);
		$em->flush();

		return new JsonResponse(['message' => 'User has been deleted successfully']);
    }
    
    
    /**
     * @Route("/ajax_get_user/{id}", name="ajax_get_user", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
	public function ajaxGetUserAction(Request $request,$id)
	{
		$user = $this->_getUser($id);
        if(!$user){
            return new JsonResponse(['message' => 'User not exist'],404);
        }

        $serializer = $this->get('serializer');
		return new JsonResponse($serializer->normalize($user));
    }
    
    
    /**
     * @Route("/ajax_generate_access_code/{id}", name="ajax_generate_access_code", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */

     public function ajaxGenerateAccessCode($id, Request $request){
        $em = $this->getDoctrine()->getManager();
        
        $validUntil = new \DateTime();
        $validUntil->modify('+' . $request->get("numDays") . ' day');

        $user = $em->getRepository("AppBundle:User")->find($id);        
        $user->setAccessCode($this->generateRandomString(10));
        $user->setValidUntil($validUntil);
        $em->flush();


        $permissions  = $em->getRepository("AppBundle:UserAccess")
                            ->findBy(['userId' => $user->getId()]);
        
        if(count($permissions) > 0){
            foreach($permissions as $permission){
                $permission->setValidUntil(new \DateTime());
            }
        }

        $em->flush();

        $serializer = $this->get("serializer");
        return new JsonResponse($serializer->normalize($user));
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @Route("/ajax_batch_delete_user", name="ajax_batch_delete_user", defaults={"_format"="json"}, options={"expose"=true})
     * @Method("DELETE")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxBatchDeleteUserAction(Request $request)
    {
        if(!$this->isGranted("delete",self::MODULE)){
            return new JsonResponse(array("message" => $this->get('translator')->trans("permission.denied")),403);
        }

        $em = $this->getDoctrine()->getManager();

        $id = $request->get('id');

        for($i=0;$i<count($id);$i++){
            $user = $this->_getUser($id[$i]);
            if(!$user){
                return new JsonResponse(['message' => 'Unable to delete batch request, some user not exist'],404);
            }

            $em->remove($user);
        }

        $em->flush();
        $em->clear();

        return new JsonResponse(array("message" => "Selected batch user has been deleted successfully."));

    }

	private function _getUser($id){
		$user = $this->getDoctrine()
			->getRepository('AppBundle:User')
			->find($id);

		return $user;
	}

	public function _getGroups(){
        $data = $this->getDoctrine()
            ->getRepository('AppBundle:Groups')
            ->findBy(["status" => 1],['groupName' => 'ASC']);

        return $data;
    }

    private function _getProvinces(){
        $data = $this->getDoctrine()
                     ->getRepository('AppBundle:Province')
                     ->findAll();
        
        return $data;
    }

    private function _getProjects(){
        $data = $this->getDoctrine()
                     ->getRepository('AppBundle:Project')
                     ->findAll();
        
        return $data;
    }

}