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

use AppBundle\Entity\IdRequestHeader;
use AppBundle\Entity\IdRequestDetail;
use AppBundle\Entity\ProjectVoter;

/**
* @Route("/manage-id-requests")
*/

class IdInhouseRequestsController extends Controller 
{   
    const STATUS_ACTIVE = 'A';
    const STATUS_RELEASED = 'R';
    const MODULE_MAIN = "ID_INHOUSE_REQUESTS";

	/**
    * @Route("", name="id_request_index", options={"main" = true})
    */

    public function indexAction(Request $request)
    {
        // $this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/id-inhouse-requests/index.html.twig',[ 'user' => $user, 'hostIp' => $hostIp , 'imgUrl' => $imgUrl ]);
    }

    /**
    * @Route("/ajax_get_id_request_header/{hdrId}", 
    * 	name="ajax_get_id_request_header",
    *	options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetIdRequestHeaderAction(Request $request,$hdrId){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository("AppBundle:IdRequestHeader")->find($hdrId);

        if(!$entity)
            return new JsonResponse(['message' => 'not found'],404);
        
    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }

    /**
    * @Route("/ajax_post_id_request_header", 
    * 	name="ajax_post_id_request_header",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostIdRequestHeaderAction(Request $request){
        
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $submittedAt = empty($request->get("submittedAt")) ? null : new \DateTime($request->get("submittedAt"));

        $entity = new IdRequestHeader();
        $entity->setProId($request->get('proId'));
        $entity->setElectId($request->get('electId'));

        $entity->setProvinceCode($request->get('provinceCode'));
    	$entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBrgyNo($request->get('brgyNo'));

        $entity->setSubmittedAt($submittedAt);
        $entity->setSubmittedBy($request->get("submittedBy"));
        $entity->setTotalReceived($request->get("totalReceived"));

        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());

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
    
        $em->persist($entity);
        $em->flush();
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }
    
    /**
    * @Route("/ajax_delete_id_request_header/{hdrId}", 
    * 	name="ajax_delete_id_request_header",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteIdRequestHeader($hdrId){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository("AppBundle:IdRequestHeader")->find($hdrId);

        if(!$entity)
            return new JsonResponse(['message' => 'not found'],404);


        $details = $em->getRepository("AppBundle:IdRequestDetail")->findBy(['hdrId' => $hdrId]);


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
     * @Route("/ajax_get_id_request_datatable",
     *     name="ajax_get_id_request_datatable",
     *     options={"expose" = true})
     *
     * @Method("GET")
     */
    
    public function dataIdRequestDatatableAction(Request $request){

        $filters = [];
        $filters['h.submitted_at'] = $request->get("submittedAt");
        $filters['h.submitted_by'] = $request->get("submittedBy");
        $filters['h.pro_id'] = $request->get("proId");
        $filters['h.elect_id'] = $request->get("electId");
        $filters['h.province_code'] = $request->get("provinceCode");
        $filters['h.municipality_no'] = $request->get("municipalityNo");
        $filters['m.name'] = $request->get("municipalityName");
        $filters['b.name'] = $request->get("barangayName");
        $filters['h.brgy_no'] = $request->get("brgyNo");

        $columns = [
            0 => 'h.hdr_id',
            1 => 'h.submitted_by',
            2 => 'h.submitted_at',
            3 => 'h.total_received'
        ];
        
        $whereStmt = " AND (";

        foreach($filters as $field => $searchText)
        {
            if($searchText != "")
            {
                if($field == 'h.pro_id' || $field == 'h.elect_id' || $field == 'h.province_code' || $field == 'h.municipality_no' || $field == 'h.brgy_no'){
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

        $orderStmt = " ORDER BY h.created_at DESC";

        $start = 1;
        $length = 1;

        if(null !== $request->query->get('start') && null !== $request->query->get('length')){
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }
       
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.hdr_id),0) FROM tbl_id_request_header h 
        INNER JOIN psw_municipality m ON m.province_code = h.province_code AND m.municipality_no = h.municipality_no 
        INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = h.brgy_no";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.hdr_id),0) FROM tbl_id_request_header h  
        INNER JOIN psw_municipality m ON m.province_code = h.province_code AND m.municipality_no = h.municipality_no 
        INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = h.brgy_no 
        WHERE 1=1 ";

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT 
        (SELECT COALESCE(COUNT(pv.pro_voter_id),0) FROM tbl_id_request_detail d INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = d.pro_voter_id WHERE d.hdr_id = h.hdr_id AND pv.has_photo = 1 ) AS total_photo,
        (SELECT COALESCE(COUNT(pv.pro_voter_id),0) FROM tbl_id_request_detail d INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = d.pro_voter_id WHERE d.hdr_id = h.hdr_id AND pv.has_id) AS total_id,
        (SELECT COALESCE(COUNT(pv.pro_voter_id),0) FROM tbl_id_request_detail d INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = d.pro_voter_id WHERE d.hdr_id = h.hdr_id AND d.status = 'R' ) AS total_released,
        h.*,b.name as barangay_name ,m.name as municipality_name FROM tbl_id_request_header h 
        INNER JOIN psw_municipality m ON m.province_code = h.province_code AND m.municipality_no = h.municipality_no 
        INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = h.brgy_no
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
    * @Route("/ajax_post_id_request_detail", 
    * 	name="ajax_post_id_request_detail",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostIdRequestDetailAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->find($request->get('proVoterId'));
        
        if(!$projectVoter)
            return new JsonResponse(['message' => 'voter not found...'],404);

        $entity = new IdRequestDetail();
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
        
        
        $projectVoter->setCellphone($request->get('cellphoneNo'));
        $projectVoter->setVoterGroup($request->get('voterGroup'));
        $projectVoter->setDidChange(1);
        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setUpdatedBy($user->getUsername());
       
        $em->persist($entity);
        $em->flush();
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }
    
    /**
     * @Route("/ajax_get_id_request_detail_datatable",
     *     name="ajax_get_id_request_detail_datatable",
     *     options={"expose" = true})
     *
     * @Method("GET")
     */
    
    public function dataIdRequestDetailDatatableAction(Request $request){

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
                if($field == 'd.hdr_id' || $field == 'd.status'){
                    $whereStmt .= "{$field} = '$searchText' AND ";
                }elseif($field == 'pv.has_id' || $field == 'pv.has_photo'){
                    if((int)$searchText == 0){
                        $whereStmt .= "({$field} = '$searchText' OR {$field} IS NULL) AND ";
                    }else{
                        $whereStmt .= "{$field} = '$searchText' AND ";
                    }
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

        $orderStmt = " ORDER BY d.created_at DESC";

        $start = 1;
        $length = 1;

        if(null !== $request->query->get('start') && null !== $request->query->get('length')){
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }
       
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(d.dtl_id),0) FROM tbl_id_request_detail d";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(d.dtl_id),0) FROM tbl_id_request_detail d INNER  JOIN tbl_project_voter pv ON pv.pro_voter_id = d.pro_voter_id WHERE 1=1 ";

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT d.*,pv.voter_name,pv.cellphone,pv.voter_group,pv.barangay_name,pv.municipality_name,pv.has_id,pv.has_photo,pv.pro_id_code
                FROM tbl_id_request_detail d  INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = d.pro_voter_Id 
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
    * @Route("/ajax_delete_id_request_detail/{dtlId}", 
    * 	name="ajax_delete_id_request_detail",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteIdRequestDetail($dtlId){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository("AppBundle:IdRequestDetail")->find($dtlId);

        if(!$entity)
            return new JsonResponse(['message' => 'not found'],404);

        $em->remove($entity);
        $em->flush();

        $em->clear();

        return new JsonResponse(null,200);
    }


    
    /**
    * @Route("/ajax_get_id_request_for_release/{hdrId}", 
    *   name="ajax_get_id_request_for_release",
    *   options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetIdRequestForRelease(Request $request,$hdrId){
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT d.dtl_id, pv.barangay_name,pv.voter_name,pv.pro_id_code,pv.voter_id,pv.pro_voter_id,pv.updated_at 
                FROM tbl_id_request_detail d INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = d.pro_voter_id 
                WHERE d.status = ? AND pv.has_photo = ? AND d.hdr_id = ? AND (pv.has_id IS NULL OR  pv.has_id = '' OR pv.has_id = 0 )";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,'A');
        $stmt->bindValue(2,1);
        $stmt->bindValue(3,$hdrId);
        $stmt->execute();
        $data = array();

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        $em->clear();

        return new JsonResponse($data);
    }

    /**
    * @Route("/ajax_post_id_request_for_release", 
    *   name="ajax_post_id_request_for_release",
    *   options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostUpdatedRecords(Request $request){

        $self = $this;
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $projectVoters = $request->get('projectVoters');

        if(count($projectVoters) <= 0){
            return new JsonResponse(['projectVoters' => "Action denied. You cannot proceed on importing  data with an empty list."],400);
        }

        foreach($projectVoters as $itemId){
            $entity = $em->getRepository("AppBundle:IdRequestDetail")->find($itemId);
            
            if($entity){
                $entity->setStatus(self::STATUS_RELEASED);
                $entity->setReleasedBy($user->getUsername());
                $entity->setReleasedAt(new \DateTime());
            }
        }

        $em->flush();
        $em->clear();

        return new JsonResponse(['message' => 'ok'],200);
    }
    
    /**
    * @Route("/ajax_get_id_request_status", 
    * 	name="ajax_get_id_request_status",
    *	options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetIdRequestStatusAction(Request $request){
        
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $today = date('Y-m-d');

        $sql = "SELECT COALESCE(COUNT(*),0) AS total_count 
        FROM tbl_project_print_header h 
        LEFT JOIN tbl_project_print_detail d ON h.print_id = d.print_id 
        WHERE print_date LIKE ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$today . '%');
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    	return new JsonResponse($row);
    }


    /**
    * @Route("/ajax_select2_printed_dates", 
    *       name="ajax_select2_printed_dates",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2PrintedDates(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $proId = $request->get('proId');
        $requestId = $request->get('requestId');
        
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT DATE_FORMAT(print_date,'%Y-%m-%d') as print_date
                FROM tbl_project_print_header 
                WHERE (print_date LIKE ? OR ? IS NULL) AND pro_id = ? AND origin_id = ?
                ORDER BY print_date DESC LIMIT 20";
        
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$searchText);
        $stmt->bindValue(2,empty($request->get('searchText')) ? null : $request->get('searchText'));
        $stmt->bindValue(3,$proId);
        $stmt->bindValue(4,$requestId);

        $stmt->execute();

        $dates = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $dates[] = $row;
        }

        return new JsonResponse($dates);
    }
}
