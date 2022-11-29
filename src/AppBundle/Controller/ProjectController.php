<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;
use AppBundle\Entity\Project;
use AppBundle\Entity\ProjectVoter;

/**
* @Route("/project")
*/

class ProjectController extends Controller 
{   
    const STATUS_ACTIVE = 'A';
    const MODULE_MAIN = "PROJECT";

	/**
    * @Route("", name="project_index", options={"main" = true})
    */

    public function indexAction(Request $request)
    {
        //$this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        return $this->render('template/project/index.html.twig',['user' => $user]);
    }
    
    /**
     * @Route("/ajax_get_datatable_project", name="ajax_get_datatable_project", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
	public function ajaxGetDatatableProjectAction(Request $request)
	{	

        $columns = array(
            0 => "p.pro_id",
            1 => "p.pro_name",
            2 => "p.province_code",
            2 => "p.pro_desc",
            3 => "p.status",
        );

        $sWhere = "";
        
        $select['p.pro_name'] = $request->get("proName");
        $select['p.pro_desc'] = $request->get('proDesc');
        $select['p.province_code'] = $request->get('provinceCode');
        

        foreach($select as $key => $value){
            $searchValue = $select[$key];
            if($searchValue != null || !empty($searchValue)) {
                $sWhere .= " AND " . $key . " = '" . $searchValue . "'";
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
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(p.pro_id),0) FROM tbl_project p";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(p.pro_id),0) FROM tbl_project p
                WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT p.*, pp.name AS province_name FROM tbl_project p 
                INNER JOIN psw_province pp ON p.province_code = pp.province_code 
                WHERE 1 " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
		$res['data'] =  $data;
	    $res['recordsTotal'] = $recordsTotal;
	    $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

	    return new JsonResponse($res);
    }

    /**
    * @Route("/ajax_delete_project/{proId}", 
    * 	name="ajax_delete_project",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */ 

    public function ajaxDeleteProjectAction($proId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:Project")->find($proId);

        if(!$entity)
            return new JsonResponse(null,404);

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null,200);
    }

    /**
    * @Route("/ajax_get_project_voter_generate_id_no/{proId}/{proVoterId}", 
    * 	name="ajax_get_project_voter_generate_id_no",
    *	options={"expose" = true}
    * )
    * @Method("GET")
    */  

    public function ajaxGenerateIdNoAction(Request $request, $proId,$proVoterId){

        $em = $this->getDoctrine()->getManager();

        $user = $this->get('security.token_storage')->getToken()->getUser();

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => $proId,
            'proVoterId' => $proVoterId
        ]);

        $munNo = $proVoter->getMunicipalityNo();
        $voterName = $proVoter->getVoterName();
            
        if($proVoter->getGeneratedIdNo() == '' || $proVoter->getGeneratedIdNo() == null){
            $proIdCode = !empty($proVoter->getProIdCode()) ? $proVoter->getProIdCode() : $this->generateProIdCode($proId,$voterName,$munNo) ;
            $generatedIdNo = date('Y-m-d') . '-' . $proVoter->getMunicipalityNo() .'-' . $proVoter->getBrgyNo() .'-'. $proIdCode;
            $proVoter->setProIdCode($proIdCode);
            $proVoter->setGeneratedIdNo($generatedIdNo);
            $proVoter->setDateGenerated(date('Y-m-d'));
        }

        $proVoter->setDidChanged(1);
        $proVoter->setUpdatedAt(new \DateTime());
        $proVoter->setUpdatedBy($user->getUsername());
        $proVoter->setRemarks($request->get('remarks'));
        $proVoter->setStatus(self::STATUS_ACTIVE);

    	$validator = $this->get('validator');
        $violations = $validator->validate($proVoter);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }

        $em->flush();

        $serializer = $this->get('serializer');
        
        return new JsonResponse($serializer->normalize($proVoter),200);
    }

    private function generateProIdCode($proId, $voterName, $municipalityNo)
    {
        $proIdCode = '000001';

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT CAST(RIGHT(pro_id_code ,6) AS UNSIGNED ) AS order_num FROM tbl_project_voter
        WHERE pro_id = ? AND municipality_no = ? ORDER BY order_num DESC LIMIT 1 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $proId);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->execute();

        $request = $stmt->fetch();

        if ($request) {
            $proIdCode = sprintf("%06d", intval($request['order_num']) + 1);
        }

        $namePart = explode(' ', $voterName);
        $uniqueId = uniqid('PHP');

        $prefix = '';

        foreach ($namePart as $name) {
            $prefix .= substr($name, 0, 1);
        }

        return $prefix . $municipalityNo . $proIdCode;
    }

    /**
    * @Route("/ajax_get_project_voter_reset_id/{proId}/{proVoterId}", 
    * 	name="ajax_get_project_voter_reset_id",
    *	options={"expose" = true}
    * )
    * @Method("GET")
    * @return JsonResponse|Response
    */  

    public function ajaxResetIdAction(Request $request, $proId,$proVoterId){
        $em = $this->getDoctrine()->getManager();
        
        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->find($proVoterId);

        if(!$proVoter){
            return new JsonResponse(null,404);
        }

        $proVoter->setHasId(null);
        $proVoter->setHasPhoto(1);
        $em->flush();
        $em->clear();
        
        return new JsonResponse(null,200);
    }

    /**
    * @Route("/ajax_get_project/{proId}", 
    * 	name="ajax_get_project",
    *	options={"expose" = true}
    * )
    * @Method("GET")
    */  

    public function ajaxGetProjectAction($proId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:Project")->find($proId);

        if(!$entity)
            return new JsonResponse(null,404);

        $serializer = $this->get("serializer");

        return new JsonResponse($serializer->normalize($entity),200);
    }

    /**
    * @Route("/ajax_post_project", 
    * 	name="ajax_post_project",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostProjectAction(Request $request){

        $entity = new Project();

        $entity->setProvinceCode($request->get("provinceCode"));
    	$entity->setProName($request->get('proName'));
        $entity->setProDesc($request->get('proDesc'));
        $entity->setRemarks($request->get('remarks'));
    	$entity->setStatus(self::STATUS_ACTIVE);

    	$validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }

    /**
    * @Route("/ajax_patch_project/{proId}", 
    * 	name="ajax_patch_project",
    *	options={"expose" = true}
    * )
    * @Method("PATCH")
    */

    public function ajaxPatchProjectAction($proId, Request $request){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository("AppBundle:Project")->find($proId);

        if(!$entity)
            return new JsonResponse(null,404);

        $entity->setProvinceCode($request->get("provinceCode"));
    	$entity->setProName($request->get('proName'));
        $entity->setProDesc($request->get('proDesc'));
      
    	$validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }
        
        $em->flush();
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }

    /**
    * @Route("/ajax_select2_project_voters", 
    *       name="ajax_select2_project_voters",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2ProjectVoters(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $electId = $request->get("electId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");

        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';
        
        $sql = "SELECT p.* FROM tbl_project_voter p 
                WHERE p.voter_name LIKE ? 
                AND p.province_code = ? 
                AND p.elect_id = ? 
                AND (municipality_no = ? OR ? IS NULL)
                AND (brgy_no = ? OR ? IS NULL)
                ORDER BY p.voter_name ASC LIMIT 10";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$searchText);
        $stmt->bindValue(2,$provinceCode);
        $stmt->bindValue(3,$electId);
        $stmt->bindValue(4, $municipalityNo);
        $stmt->bindValue(5, empty($municipalityNo) ? null : $municipalityNo);
        $stmt->bindValue(6, $brgyNo);
        $stmt->bindValue(7, empty($brgyNo) ? null : $brgyNo );
        $stmt->execute();

        $projectVoters = [];
    
        while( $row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $projectVoters[] = $row;
        }

        if(count($projectVoters) <= 0)
            return new JsonResponse(array());

        $em->clear();

        return new JsonResponse($projectVoters);
    }

    
    /**
    * @Route("/ajax_select2_project_voters_alt", 
    *       name="ajax_select2_project_voters_alt",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2ProjectVotersAlt(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $electId = $request->get("electId");
        $provinceCode = $request->get("provinceCode");
        $municipalityName = $request->get("municipalityName");
        $brgyNo = $request->get("brgyNo");

        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';
        
        $sql = "SELECT p.* FROM tbl_project_voter p 
                WHERE p.voter_name LIKE ? 
                AND p.province_code = ? 
                AND p.elect_id = ? 
                AND (municipality_name = ? OR ? IS NULL)
                AND (brgy_no = ? OR ? IS NULL)
                AND ((p.elect_id = 3 AND p.is_non_voter <> 1) OR p.elect_id = 4 ) 
                ORDER BY p.voter_name ASC LIMIT 10";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$searchText);
        $stmt->bindValue(2,$provinceCode);
        $stmt->bindValue(3,$electId);
        $stmt->bindValue(4, $municipalityName);
        $stmt->bindValue(5, empty($municipalityName) ? null : $municipalityName);
        $stmt->bindValue(6, $brgyNo);
        $stmt->bindValue(7, empty($brgyNo) ? null : $brgyNo );
        $stmt->execute();

        $projectVoters = [];
    
        while( $row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $projectVoters[] = $row;
        }

        if(count($projectVoters) <= 0)
            return new JsonResponse(array());

        $em->clear();

        return new JsonResponse($projectVoters);
    }

    /**
    * @Route("/ajax_select2_project_voters_member_only", 
    *       name="ajax_select2_project_voters_member_only",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2ProjectVotersMemberOnly(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $electId = $request->get("electId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");

        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';
        
        $sql = "SELECT p.* FROM tbl_project_voter p 
                WHERE p.voter_name LIKE ? 
                AND p.province_code = ? 
                AND p.elect_id = ? 
                AND municipality_no = ? 
                AND p.has_id = 1
                AND p.has_photo = 1
                ORDER BY p.voter_name ASC LIMIT 10";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$searchText);
        $stmt->bindValue(2,$provinceCode);
        $stmt->bindValue(3,$electId);
        $stmt->bindValue(4, $municipalityNo);
        $stmt->execute();

        $projectVoters = [];
    
        while( $row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $projectVoters[] = $row;
        }

        if(count($projectVoters) <= 0)
            return new JsonResponse(array());

        $em->clear();

        return new JsonResponse($projectVoters);
    }
    

    /**
    * @Route("/ajax_select2_project_voters_strict", 
    *       name="ajax_select2_project_voters_strict",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2ProjectVotersStrict(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $electId = $request->get("electId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = empty($request->get('municipalityNo')) ? null :  $request->get('municipalityNo');
        $brgyNo = empty($request->get('brgyNo')) ? null :  $request->get('brgyNo');
    
        $sql = "SELECT p.* FROM tbl_project_voter p 
                WHERE p.voter_name LIKE ? AND p.province_code = ? AND p.elect_id = ? AND (p.municipality_no = ? OR ? IS NULL) AND (p.brgy_no = ? OR ? IS NULL) LIMIT 10";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$searchText);
        $stmt->bindValue(2,$provinceCode);
        $stmt->bindValue(3,$electId);
        $stmt->bindValue(4,$municipalityNo);
        $stmt->bindValue(5,$municipalityNo);
        $stmt->bindValue(6,$brgyNo);
        $stmt->bindValue(7,$brgyNo);
        $stmt->execute();

        $projectVoters = [];
    
        while( $row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $projectVoters[] = $row;
        }

        if(count($projectVoters) <= 0)
            return new JsonResponse(array());

        $em->clear();

        return new JsonResponse($projectVoters);
    }

    private function getBarangay($municipalityCode,$brgyNo){
        $em = $this->getDoctrine()->getManager();
        
        $sql = "SELECT * FROM psw_barangay b WHERE b.municipality_code = ? AND b.brgy_no = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$municipalityCode);
        $stmt->bindValue(2,$brgyNo);
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $barangay;
    }

    private function getMunicipalities($provinceCode){
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_municipality m WHERE m.province_code = ? ORDER BY m.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->execute();

        $municipalities = [];

        
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $municipalities[] = $row;
        }

        if(empty($municipalities)){
            $municipalities = [];
        }
        
        return $municipalities;
    }

    /**
    * @Route("/ajax_patch_project_voter_tag_status/{proVoterId}", 
    * 	name="ajax_patch_project_voter_tag_status",
    *	options={"expose" = true}
    * )
    * @Method("PATCH")
    */

    public function patchProjectVoterTagStatus($proVoterId,Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->find($proVoterId);

        if(!$projectVoter)
            return new JsonResponse(null,404);


        if($this->isTogglable($request->get("isKalaban"))){
            $projectVoter->setIsKalaban($request->get('isKalaban'));
        }
        
        $projectVoter->setDidChanged(1);
        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setUpdatedBy($user->getUsername());

        $em->flush();
        $em->clear();

        return new JsonResponse([
            "success" => true
        ]);
    }
      
    private function isTogglable($value){
        return $value != null && $value != "" && ($value == 0 ||  $value == 1);
    }


     /**
    * @Route("/ajax_patch_project_voter_alt", 
    * 	name="ajax_patch_project_voter_alt",
    *	options={"expose" = true}
    * )
    * @Method("PATCH")
    */

    public function ajaxPatchProjectVoterAltAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $entity = $em->getRepository("AppBundle:ProjectVoter")->find($request->get('proVoterId'));
        $new = false;

        if(!$entity)
            return new JsonResponse(null,404);

        $entity->setCellphone($request->get('cellphone'));
        $entity->setVoterGroup($request->get('voterGroup'));
        $entity->setAssignedPrecinct($request->get('assignedPrecinct'));
        $entity->setUpdatedAt(new \DateTime());
        $entity->setUpdatedBy($user->getUsername());

        $entity->setIs1($request->get('is1'));
        $entity->setIs2($request->get('is2'));
        $entity->setIs3($request->get('is3'));
        $entity->setIs4($request->get('is4'));
        $entity->setIs5($request->get('is5'));
        $entity->setIs6($request->get('is6'));
        $entity->setIs7($request->get('is7'));
        $entity->setIs8($request->get('is8'));
        $entity->setIs9($request->get('is9'));
        $entity->setIs10($request->get('is10'));
        $entity->setDidChange(1);
        $entity->setUpdatedAt(new \DateTime());
        $entity->setUpdatedBy($user->getUsername());

    	$validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }

        $em->flush();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }   

}
