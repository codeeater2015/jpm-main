<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;

use AppBundle\Entity\SpecialOptHeader;
use AppBundle\Entity\SpecialOptDetail;
use AppBundle\Entity\ProjectVoter;

/**
* @Route("/special-opt")
*/

class SpecialOptController extends Controller 
{   
    const STATUS_ACTIVE = 'A';
    const STATUS_RELEASED = 'R';
    const MODULE_MAIN = "SPECIAL_OPERATIONS";

	/**
    * @Route("", name="special_opt_index", options={"main" = true})
    */

    public function indexAction(Request $request)
    {
        // $this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/special-opt/index.html.twig',[ 'user' => $user, 'hostIp' => $hostIp , 'imgUrl' => $imgUrl ]);
    }

    /**
    * @Route("/ajax_post_special_opt_header", 
    * 	name="ajax_post_special_opt_header",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostSpecialOptHeaderAction(Request $request){
        
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $entity = new SpecialOptHeader();
        $entity->setProId($request->get('proId'));
        $entity->setElectId($request->get('electId'));

        $entity->setProvinceCode($request->get('provinceCode'));
    	$entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBrgyNo($request->get('brgyNo'));

        $entity->setVoterId($request->get('voterId'));
        $entity->setProVoterId($request->get('proVoterId'));
        $entity->setOptType($request->get('optType'));

        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());

        $entity->setUpdatedAt(new \DateTime());
        $entity->setUpdatedBy($user->getUsername());

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
        
        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")
                            ->find($request->get("proVoterId"));

        if(!$projectVoter)
            return new JsonResponse(['voterId' => 'not found'], 404);
        
        $projectVoter->setCellphone($request->get('cellphone'));
        $projectVoter->setVoterGroup($request->get('voterGroup'));
        
        $projectVoter->setIs1($request->get("is1"));
        $projectVoter->setIs2($request->get("is2"));
        $projectVoter->setIs3($request->get("is3"));
        $projectVoter->setIs4($request->get("is4"));
        $projectVoter->setIs5($request->get("is5"));
        $projectVoter->setIs6($request->get("is6"));
        $projectVoter->setIs7($request->get("is7"));
        $projectVoter->setIs8($request->get("is8"));
        $projectVoter->setIs9($request->get("is9"));
        $projectVoter->setIs10($request->get("is10"));

        $projectVoter->setDidChange(1);
        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setUpdatedBy($user->getUsername());

        $validator = $this->get('validator');
        $violations = $validator->validate($projectVoter);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }

        $em->persist($entity);
        $em->flush();
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }

    /**
    * @Route("/ajax_get_special_opt_header/{hdrId}", 
    * 	name="ajax_get_special_opt_header",
    *	options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetSpecialOptHeader($hdrId){
        $em  = $this->getDoctrine()->getManager();

        $sql = "SELECT h.*, pv.voter_name, pv.municipality_name, pv.barangay_name, pv.voter_group, pv.cellphone 
                FROM tbl_recruitment_special_hdr h INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = h.pro_voter_id 
                WHERE h.hdr_id = ?";
        
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$hdrId);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if(empty($row) || $row == null)
            return new JsonResponse(['message' => 'not found'],404);
        
        return new JsonResponse($row);
    }


    /**
    * @Route("/ajax_delete_special_opt_header/{hdrId}", 
    * 	name="ajax_delete_special_opt_header",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteSpecialOptHeader($hdrId){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository("AppBundle:SpecialOptHeader")->find($hdrId);

        if(!$entity)
            return new JsonResponse(['message' => 'not found'],404);


        $details = $em->getRepository("AppBundle:SpecialOptDetail")->findBy(['hdrId' => $hdrId]);


        if(count($details) > 0 ){

            foreach($details as $detail){
                $em->remove($detail);
            }
        }

        $em->remove($entity);
        $em->flush();

        $em->clear();

        return new JsonResponse(null,200);
    }

    /**
     * @Route("/ajax_get_special_opt_datatable",
     *     name="ajax_get_special_opt_datatable",
     *     options={"expose" = true})
     *
     * @Method("GET")
     */
    
    public function dataIdRequestDatatableAction(Request $request){

        $filters = [];
        
        $filters['h.pro_id'] = $request->get("proId");
        $filters['h.elect_id'] = $request->get("electId");
        $filters['h.province_code'] = $request->get("provinceCode");
        
        $filters['pv.voter_name'] = $request->get("voterName");
        $filters['pv.municipalityName'] = $request->get("municipalityName");
        $filters['pv.barangayName'] = $request->get("barangayName");

        $columns = [
            0 => 'h.hdr_id',
            1 => 'pv.voter_name',
            2 => 'pv.voter_group',
            3 => 'pv.muninicipality_name',
            4 => 'pv.barangay_name',
            5 => 'pv.cellphone'
        ];
        
        $exactFilters = [
            'h.elect_id',
            'h.pro_id'
        ];
        
        $whereStmt = " AND (";

        foreach($filters as $field => $searchText)
        {
            if($searchText != "")
            {
                if(in_array($field, $exactFilters)){
                    $whereStmt .= "{$field} = '$searchText' AND ";
                }else{
                    $whereStmt .= "{$field} LIKE '%$searchText%' AND ";
                }
            }
        }

        $whereStmt = substr_replace($whereStmt,"",-4);

        if($whereStmt == " A"){
            $whereStmt = "";
        }else{
            $whereStmt .= ")";
        }

        $orderStmt = " ORDER BY pv.voter_name ASC";

        $start = 1;
        $length = 1;

        if(null !== $request->query->get('start') && null !== $request->query->get('length')){
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }
       
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.hdr_id),0) FROM tbl_recruitment_special_hdr h";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.hdr_id),0) FROM tbl_recruitment_special_hdr h 
                INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = h.pro_voter_id 
                WHERE 1=1 ";

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT 
                h.*, pv.voter_name,pv.municipality_name,pv.barangay_name,pv.cellphone,pv.voter_group,pv.pro_voter_id,pv.pro_id,pv.voter_id,
                (SELECT COALESCE(COUNT(*), 0 ) FROM tbl_recruitment_special_dtl d WHERE d.hdr_id = h.hdr_id) AS total_members
                FROM tbl_recruitment_special_hdr h 
                INNER JOIN tbl_project_voter pv ON  pv.pro_voter_id = h.pro_voter_id 
                WHERE 1=1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] =  $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        $em->clear();

        return  new JsonResponse($res);
    }

    /**
     * @Route("/ajax_get_special_opt_detail_datatable",
     *     name="ajax_get_special_opt_detail_datatable",
     *     options={"expose" = true})
     *
     * @Method("GET")
     */
    
    public function dataSpecialOptDatatableAction(Request $request){

        $filters = [];
        $filters['d.hdr_id'] = $request->get("hdrId");
        $filters['pv.voter_name'] = $request->get("voterName");
        $filters['pv.barangay_name'] = $request->get("barangayName");
        $filters['pv.has_id'] = $request->get("hasId");
        $filters['pv.has_photo'] = $request->get("hasPhoto");
        $filters['d.status'] = $request->get("status");

        $columns = [
            0 => 'd.dtl_id',
            1 => 'pv.voter_name',
            2 => 'pv.barangay_name',
            3 => 'pv.cellphone'
        ];
        
        $whereStmt = " AND (";

        foreach($filters as $field => $searchText)
        {
            if($searchText != "")
            {
                if($field == 'd.hdr_id'){
                    $whereStmt .= "{$field} = '$searchText' AND ";
                }else{
                    $whereStmt .= "{$field} LIKE '%$searchText%' AND ";
                }
            }
        }

        $whereStmt = substr_replace($whereStmt,"",-4);

        if($whereStmt == " A"){
            $whereStmt = "";
        }else{
            $whereStmt .= ")";
        }

        $orderStmt = " ORDER BY pv.voter_name ASC";

        $start = 1;
        $length = 1;

        if(null !== $request->query->get('start') && null !== $request->query->get('length')){
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }
       
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(d.dtl_id),0) FROM tbl_recruitment_special_dtl d";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(d.dtl_id),0) FROM tbl_recruitment_special_dtl d INNER  JOIN tbl_project_voter pv ON pv.pro_voter_id = d.pro_voter_id WHERE 1=1 ";

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT d.*,pv.voter_name,pv.cellphone,pv.voter_group,pv.barangay_name,pv.municipality_name,pv.has_id,pv.has_photo,pv.pro_id_code
                FROM tbl_recruitment_special_dtl d  INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = d.pro_voter_Id 
                WHERE 1=1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] =  $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        $em->clear();

        return  new JsonResponse($res);
    }

    /**
    * @Route("/ajax_post_special_opt_detail", 
    * 	name="ajax_post_special_opt_detail",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxSpecialOptDetailAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->find($request->get('proVoterId'));
        
        if(!$projectVoter)
            return new JsonResponse(['message' => 'voter not found...'],404);

        $entity = new SpecialOptDetail();
        $entity->setHdrId($request->get("hdrId"));
        $entity->setVoterId($request->get('voterId'));
        $entity->setProVoterId($request->get('proVoterId'));
        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
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
        
        $projectVoter->setCellphone($request->get('cellphone'));
        $projectVoter->setVoterGroup($request->get('voterGroup'));

        $projectVoter->setIs1($request->get("is1"));
        $projectVoter->setIs2($request->get("is2"));
        $projectVoter->setIs3($request->get("is3"));
        $projectVoter->setIs4($request->get("is4"));
        $projectVoter->setIs5($request->get("is5"));
        $projectVoter->setIs6($request->get("is6"));
        $projectVoter->setIs7($request->get("is7"));
        $projectVoter->setIs8($request->get("is8"));
        $projectVoter->setIs9($request->get("is9"));
        $projectVoter->setIs10($request->get("is10"));

        $projectVoter->setDidChange(1);
        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setUpdatedBy($user->getUsername());
        
        $validator = $this->get('validator');
        $violations = $validator->validate($projectVoter);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }

        $em->persist($entity);
        $em->flush();
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }
    
    /**
    * @Route("/ajax_delete_special_opt_detail/{dtlId}", 
    * 	name="ajax_delete_special_opt_detail",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteSpecialOpDetail($dtlId){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository("AppBundle:SpecialOptDetail")->find($dtlId);

        if(!$entity)
            return new JsonResponse(['message' => 'not found'],404);

        $em->remove($entity);
        $em->flush();

        $em->clear();

        return new JsonResponse(null,200);
    }

}
