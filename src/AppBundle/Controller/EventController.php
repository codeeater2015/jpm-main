<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;

use AppBundle\Entity\ProjectEventHeader;
use AppBundle\Entity\ProjectEventDetail;

/**
* @Route("/event")
*/

class EventController extends Controller 
{   
    const STATUS_ACTIVE = 'A';
    const MODULE_MAIN = "PROJECT_EVENT";

	/**
    * @Route("", name="event_index", options={"main" = true})
    */

    public function indexAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/project-event/index.html.twig',[ 'user' => $user, 'hostIp' => $hostIp , 'imgUrl' => $imgUrl ]);
    }

    /**
     * @Route("/ajax_get_datatable_project_event", name="ajax_get_datatable_project_event", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
	public function ajaxGetDatatableEventAction(Request $request)
	{	
        $columns = array(
            0 => "e.event_id",
            1 => "e.event_name",
            2 => "e.event_desc"
        );

        $sWhere = "";
    
        $select['e.event_name'] = $request->get('eventName');
        $proId = $request->get("proId");

        foreach($select as $key => $value){
            $searchValue = $select[$key];
            if($searchValue != null || !empty($searchValue)) {
                $sWhere .= " AND " . $key . " LIKE '%" . $searchValue . "%'";
            }
        }
        
        
        $sWhere .= " AND e.pro_id = '{$proId}' ";

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

        $sql = "SELECT COALESCE(count(e.event_id),0) FROM tbl_project_event_header e WHERE e.pro_id = {$proId}";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(e.event_id),0) FROM tbl_project_event_header e
                WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT e.* ,
            (SELECT COALESCE(COUNT(ed.event_detail_id),0) FROM tbl_project_event_detail ed WHERE ed.event_id = e.event_id) AS total_expected,
            (SELECT COALESCE(COUNT(ed.event_detail_id),0) FROM tbl_project_event_detail ed WHERE ed.event_id = e.event_id AND ed.has_attended = 1) AS total_attended,
            (SELECT COALESCE(COUNT(ed.event_detail_id),0) FROM tbl_project_event_detail ed WHERE ed.event_id = e.event_id AND ed.has_new_id = 1) AS total_new_id,
            (SELECT COALESCE(COUNT(ed.event_detail_id),0) FROM tbl_project_event_detail ed WHERE ed.event_id = e.event_id AND ed.has_claimed = 1) AS total_claimed
            FROM tbl_project_event_header e 
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
    * @Route("/ajax_post_project_event_header", 
    * 	name="ajax_post_project_event_header",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostProjectEventHeaderAction(Request $request){

        $eventDate = empty($request->get("eventDate")) ? null : new \DateTime($request->get("eventDate"));

        $entity = new ProjectEventHeader();
        $entity->setProId($request->get('proId'));
    	$entity->setEventName($request->get('eventName'));
        $entity->setEventDesc($request->get('eventDesc'));
        $entity->setEventDate($eventDate);
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

        // $sql = "UPDATE tbl_project_event_header SET status = 'I' WHERE pro_id = ? AND status = ? ";
        // $stmt = $em->getConnection()->prepare($sql);
        // $stmt->bindValue(1,$request->get('proId'));
        // $stmt->bindValue(2,'A');
        // $stmt->execute();

        $em->persist($entity);
        $em->flush();
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }

    /**
    * @Route("/ajax_patch_project_event_header/{eventId}", 
    * 	name="ajax_patch_project_event_header",
    *	options={"expose" = true}
    * )
    * @Method("PATCH")
    */

    public function ajaxPatchProjectEventHeaderAction(Request $request,$eventId){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository("AppBundle:ProjectEventHeader")->find($eventId);

        if(!$entity)
            return new JsonResponse(null,404);

        $eventDate = empty($request->get("eventDate")) ? null : new \DateTime($request->get("eventDate"));
        
        $entity->setEventName($request->get('eventName'));
        $entity->setEventDesc($request->get('eventDesc'));
        $entity->setEventDate($eventDate);
        $entity->setStatus($request->get("status"));
        
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
    * @Route("/ajax_get_project_event_headers", 
    * 	name="ajax_get_project_event_headers",
    *	options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetProjectEventHeadersAction(){
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM tbl_project_event_header ORDER BY event_date ASC";
        $stmt = $em->getConnection()->query($sql);

        $events = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return new JsonResponse($events);
    }

    /**
    * @Route("/ajax_get_project_event_header/{eventId}", 
    * 	name="ajax_get_project_event_header",
    *	options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetProjectEventHeaderAction($eventId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:ProjectEventHeader")->find($eventId);

        if(!$entity)
            return new JsonResponse(null,404);

        $serializer = $this->get("serializer");
        
        return new JsonResponse($serializer->normalize($entity));
    }

    /**
    * @Route("/ajax_delete_project_event_header/{eventId}", 
    * 	name="ajax_delete_project_event_header",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteProjectEventHeaderAction($eventId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:ProjectEventHeader")->find($eventId);

        if(!$entity)
            return new JsonResponse(null,404);

        if(!$this->allowUpdate($entity->getEventId()))
            return new JsonResponse(null,400);

        $entities = $em->getRepository('AppBundle:ProjectEventDetail')->findBy([
            'eventId' => $entity->getEventId()
        ]);

        foreach($entities as $detail){
            $em->remove($detail);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null,200);
    }

    /**
    * @Route("/ajax_post_project_event_detail", 
    * 	name="ajax_post_project_event_detail",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostProjectEventDetailAction(Request $request){

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $eventDetail = $em->getRepository("AppBundle:ProjectEventDetail")->findOneBy([
            'proVoterId' => $request->get('proVoterId'),
            'eventId' => $request->get('eventId'),
            'proId' => $request->get('proId')
        ]);

        if(!$eventDetail){
            $entity = new ProjectEventDetail();
            $entity->setProVoterId($request->get('proVoterId'));
            $entity->setVoterId(0);
            $entity->setEventId($request->get("eventId"));
            $entity->setProId($request->get('proId'));
            $entity->setProIdCode($request->get('proIdCode'));
            $entity->setHasAttended(1);
            $entity->setHasClaimed(0);
            $entity->setHasNewId(0);
            $entity->setCreatedAt(new \DateTime());
            $entity->setCreatedBy($user->getUsername());
            $entity->setAttendedAt(new \DateTime());
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
        }else{
            $eventDetail->setHasAttended(1);
            $eventDetail->setAttendedAt(new \DateTime());
        }
      
        $em->flush();
        $em->clear();

    	return new JsonResponse(null,200);
    }


    /**
    * @Route("/ajax_post_project_event_attendee", 
    * 	name="ajax_post_project_event_attendee",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostProjectEventAttendeeAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->find($request->get('proVoterId'));
        
        if(!$projectVoter)
            return new JsonResponse(['message' => 'voter not found...'],404);

        $entity = new ProjectEventDetail();
        $entity->setProVoterId($request->get('proVoterId'));
        $entity->setEventId($request->get("eventId"));
        $entity->setProId($request->get('proId'));
        $entity->setHasAttended(0);
        $entity->setHasClaimed(0);
        $entity->setHasNewId(0);
        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
    	$entity->setStatus(self::STATUS_ACTIVE);

        if($projectVoter) {
                $proIdCode = !empty($projectVoter->getProIdCode()) ? $projectVoter->getProIdCode() : $this->generateProIdCode($projectVoter->getProId(), $projectVoter->getVoterName(), $projectVoter->getMunicipalityNo()) ;
                $entity->setProIdCode($proIdCode);
                $projectVoter->setProIdCode($proIdCode);
        }

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
        $projectVoter->setVoterGroup(trim(strtoupper($request->get('voterGroup'))));
        $projectVoter->setPosition(trim(strtoupper($request->get('position'))));
        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setUpdatedBy($user->getUsername());
        $projectVoter->setDidChanged(1);
        $projectVoter->setToSend(1);

        $em->persist($entity);
        $em->flush();
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
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
    * @Route("/ajax_post_project_event_batch_attendee", 
    * 	name="ajax_post_project_event_batch_attendee",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostProjectEventBatchAttendeeAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $voters = $request->get("voters");
        $eventId = $request->get("eventId");
        $proId = $request->get("proId");
    
        $event  = $em->getRepository("AppBundle:ProjectEventHeader")->find($eventId);

        if(!$event)
            return new JsonResponse(['message' => 'Event not found.'],404);


        if(!$this->allowUpdate($eventId)){
            return new JsonResponse(null,400);
        }
    
        foreach($voters as $proVoterId){
            $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->find($proVoterId);

            if($projectVoter){
                
                $entity = new ProjectEventDetail();
                $entity->setProVoterId($proVoterId);
                $entity->setEventId($eventId);
                $entity->setProId($projectVoter->getProId());
                $entity->setHasAttended(0);
                $entity->setHasClaimed(0);
                $entity->setHasNewId(0);
                $entity->setCreatedAt(new \DateTime());
                $entity->setCreatedBy($user->getUsername());
                $entity->setStatus(self::STATUS_ACTIVE);

                $validator = $this->get('validator');
                $violations = $validator->validate($entity);

                $errors = [];

                if(count($violations) <= 0){
                    foreach( $violations as $violation ){
                        $errors[$violation->getPropertyPath()] =  $violation->getMessage();
                    }
                    $em->persist($entity);
                }
            }
        }
    
        $em->flush();
    	$em->clear();

    	return new JsonResponse(['success' => true]);
    }


    /**
    * @Route("/ajax_delete_project_event_detail/{eventDetailId}", 
    * 	name="ajax_delete_project_event_detail",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteProjectEventDetailAction($eventDetailId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:ProjectEventDetail")->find($eventDetailId);

        if(!$entity)
            return new JsonResponse(null,404);

        if(!$this->allowUpdate($entity->getEventId())){
            return new JsonResponse(null,400);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null,200);
    }

    /**
    * @Route("/ajax_patch_event_detail_status/{eventDetailId}", 
    * 	name="ajax_patch_event_detail_status",
    *	options={"expose" = true}
    * )
    * @Method("PATCH")
    */

    public function patchEventDetailStatus($eventDetailId,Request $request){
        $em = $this->getDoctrine()->getManager();
        $eventDetail = $em->getRepository("AppBundle:ProjectEventDetail")->find($eventDetailId);


        if(!$eventDetail)
            return new JsonResponse(null,404);

        if(!$this->allowUpdate($eventDetail->getEventId())){
            return new JsonResponse(null,400);
        }

        if($this->isTogglable($request->get("hasAttended"))){
            $eventDetail->setHasAttended($request->get('hasAttended'));
            $eventDetail->setAttendedAt(new \DateTime());
        }
        
        if($this->isTogglable($request->get("hasNewId"))){
            $eventDetail->setHasNewId($request->get('hasNewId'));
        }
   
        if($this->isTogglable($request->get("hasClaimed"))){
            $eventDetail->setHasClaimed($request->get('hasClaimed'));
            $eventDetail->setClaimedAt(new \DateTime());
        }

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
     * @Route("/ajax_datatable_event_member",
     *     name="ajax_datatable_event_member",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

    public function datatableEventMemberAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $eventId = $request->get("eventId");
        
        $filters = array();
        $filters['d.event_id'] = $request->get('eventId');
        $filters['pv.province_code']  = $request->get('provinceCode');
       
        $filters['d.has_attended'] = $request->get('hasAttended');
        $filters['pv.has_photo'] = $request->get('hasNewId');
        $filters['d.has_claimed'] = $request->get('hasClaimed');
        $filters['b.name'] = strtoupper(trim($request->get('barangayName')));
        $filters['pv.voter_name'] = strtoupper(trim($request->get('voterName')));
        $filters['pv.voter_group'] = strtoupper(trim($request->get('voterGroup')));
        $filters['pv.precinct_no'] = strtoupper(trim($request->get('precinctNo')));
        $filters['pv.assigned_precinct'] = strtoupper($request->get('assignedPrecinct'));

        $columns = array(
            0 => 'pv.voter_id',
            1 => 'pv.voter_name',
            2 => 'pv.voter_group',
            3 => 'd.has_attended',
            4 => 'd.has_new_id',
            5 => 'd.has_claimed',
            6 => 'pv.precinct_no'
        );

        $exactFields = [
            'd.event_id',
            'd.has_attended',
            'd.has_claimed'
        ];

        $optionalFields = [
            'pv.municipality_no',
            'pv.assigned_precinct',
            'pv.precinct_no',
            'pv.voter_group',
            'pv.brgy_no',
            'pv.precinct_no',
            'pv.province_code'
        ];

        $whereStmt = " AND (";
        
        foreach($filters as $field => $searchText){
            if($searchText != ""){
                if(in_array($field,$exactFields)){
                    $whereStmt .= "{$field} = '{$searchText}' AND "; 
                }elseif($field == 'pv.has_photo'){
                   if($searchText == 0 ){
                        $whereStmt .= "({$field} = '{$searchText}' OR {$field} IS NULL) AND "; 
                   }else{
                        $whereStmt .= "{$field} = '{$searchText}' AND "; 
                   }
                }elseif(in_array($field,$optionalFields)){
                    $temp = $searchText == "" ? null : "'{$searchText}'";

                    if($searchText == 'NOPOS'){
                        $whereStmt .= "( {$field} IS NULL OR {$field} = '' ) AND ";
                    }else{
                        $whereStmt .= "({$field} = '{$searchText}' OR {$temp} IS NULL) AND ";
                    }
               }else{
                    $whereStmt .= "{$field} LIKE '%{$searchText}%' AND "; 
               }
            }
        }

        $whereStmt = substr_replace($whereStmt,"",-4);

        if($whereStmt == " A"){
            $whereStmt = "";
        }else{
            $whereStmt .= ")";
        }

        $orderStmt = "";

        if(null !== $request->query->get('order'))
            $orderStmt = $this->genOrderStmt($request,$columns);

        $orderStmt = " ORDER BY d.attended_at DESC";
        
        $start = 0;
        $length = 1;

        if(null !== $request->query->get('start') && null !== $request->query->get('length')){
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(d.event_detail_id),0) FROM tbl_project_event_detail d
                INNER JOIN tbl_project_voter pv ON d.pro_voter_id = pv.pro_voter_id WHERE d.event_id = {$eventId} ";
        $stmt = $em->getConnection()->query($sql);
        
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(d.event_detail_id),0) FROM tbl_project_event_detail d
                INNER JOIN tbl_project_voter pv ON d.pro_voter_id = pv.pro_voter_id 
                WHERE 1 ";
       

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();
        
        $sql = "SELECT pv.*, d.event_detail_id,d.has_attended, d.has_new_id, d.has_claimed FROM tbl_project_event_detail d
                INNER JOIN tbl_project_voter pv ON d.pro_voter_id = pv.pro_voter_id
                WHERE 1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while($row =  $stmt->fetch(\PDO::FETCH_ASSOC))
        {   
            $data[] = $row;
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] =  $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        $em->clear();

        return  new JsonResponse($res);
    }

    private function getLastEvent($proIdCode){
        $em  = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM tbl_project_event_detail ed 
        INNER JOIN tbl_project_event_header hd ON hd.event_id = ed.event_id 
        INNER JOIN tbl_project_voter pv ON ed.pro_voter_id = pv.pro_voter_id 
        WHERE pv.pro_id_code = ? AND ed.has_attended = 1 AND hd.status <> 'A' ORDER BY ed.attended_at DESC ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$proIdCode);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row == null ? null : $row;
    }

    private function genOrderStmt($request,$columns){

        $orderStmt = "ORDER BY  ";

        for ( $i=0 ; $i<intval(count($request->query->get('order'))); $i++ )
        {
            if ( $request->query->get('columns')[$request->query->get('order')[$i]['column']]['orderable'] )
            {
                $orderStmt .= " ".$columns[$request->query->get('order')[$i]['column']]." ".
                    ($request->query->get('order')[$i]['dir']==='asc' ? 'ASC' : 'DESC') .", ";
            }
        }

        $orderStmt = substr_replace( $orderStmt, "", -2 );
        if ( $orderStmt == "ORDER BY" )
        {
            $orderStmt = "";
        }

        return $orderStmt;
    }

    private function getProjectVoter($proId,$voterId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(
            [
                "voterId" => $voterId,
                "proId" => $proId
            ]
        );

        return $entity;
    }
    
    private function getMunicipalities($provinceCode){
        $name = '';
        $code = '';

        $em  = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_municipality WHERE province_code = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->execute();

        $municipalities = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $municipalities;
    }

    private function getBarangay($provinceCode,$municipalityCode,$brgyNo){
        $name = '';

        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_barangay WHERE brgy_code LIKE ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode . '%');
        $stmt->execute();

        $barangays = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach($barangays as $barangay){
            $barangayCode = $municipalityCode . $brgyNo;
            if( $barangayCode == $barangay['brgy_code']){
                $name = $barangay['name'];
            }
        }

        if(empty($name))
            $name = '- - - - -';

        return $name;
    }

    /**
     * @Route("/ajax_project_event_attendee_multiselect",
     *   name="ajax_project_event_attendee_multiselect",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxProjectEventAttendeeMultiselectAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        
        $electId = $request->get('electId');
        $provinceCode = empty($request->get('provinceCode')) ? null : $request->get('provinceCode');
        $municipalityNo = empty($request->get('municipalityNo')) ? null : $request->get('municipalityNo');
        $brgyNo = empty($request->get('brgyNo')) ? null : $request->get('brgyNo');
        $voterGroup = empty($request->get('voterGroup')) ? null : $request->get('voterGroup');
        $project = empty($request->get('project')) ? null : $request->get('project');

        $withId = empty($request->get("withId")) ? null : $request->get('withId');
        $withNoId = empty($request->get('withNoId')) ? null : $request->get('withNoId');
        
        $withCellphone = empty($request->get('withCellphone')) ? null  : $request->get('withCellphone');
        $witnNoCellphone = empty($request->get('withNoCellphone')) ? null : $request->get('withNoCellphone');

        $hasDropped = empty($request->get('hasDrooped')) ? null : $request->get('hasDropped');

        $is1 = empty($request->get('is1')) ? null : $request->get('is1');
        $is2 = empty($request->get('is2')) ? null : $request->get('is2');
        $is3 = empty($request->get('is3')) ? null : $request->get('is3');
        $is4 = empty($request->get('is4')) ? null : $request->get('is4');
        $is5 = empty($request->get('is5')) ? null : $request->get('is5');
        $is6 = empty($request->get('is6')) ? null : $request->get('is6');
        $is7 = empty($request->get('is7')) ? null : $request->get('is7');
        $is8 = empty($request->get('is8')) ? null : $request->get('is8');
        $is9 = empty($request->get('is9')) ? null : $request->get('is9');
        $is10 = empty($request->get('is10')) ? null : $request->get('is10');

        $isCh = empty($request->get('isCh')) ? null : $request->get('isCh');
        $isKcl = empty($request->get('isKcl')) ? null : $request->get('isKcl');
        $isKcl0 = empty($request->get('isKcl0')) ? null : $request->get('isKcl0');
        $isKcl1 = empty($request->get('isKcl1')) ? null : $request->get('isKcl1');
        $isKcl2 = empty($request->get('isKcl2')) ? null : $request->get('isKcl2');
        $isKcl3 = empty($request->get('isKcl3')) ? null : $request->get('isKcl3');
        $isKfc = empty($request->get('isKfc')) ? null : $request->get('isKfc');
        $isDao = empty($request->get('isDao')) ? null : $request->get('isDao');
        $isKjr = empty($request->get('isKjr')) ? null : $request->get('isKjr');

        $sop = empty($request->get('sop')) ? null : $request->get('sop');
        $notSop = empty($request->get('notSop')) ? null : $request->get('notSop');

        $sql = "SELECT pv.* FROM tbl_project_voter pv WHERE 
             pv.elect_id = ? AND 
             (pv.province_code = ? OR ? IS NULL)  AND
             (pv.municipality_no = ? OR ? IS NULL) AND
             (pv.brgy_no = ? OR ? IS NULL) AND
             (pv.pro_id = ? ) AND
             (pv.voter_group IN ('KFC','CH','KCL','KCL0','KCL1','KCL2','KCL3','KJR')) AND ";

        if($withId)
            $sql .= " (pv.has_id = 1) AND ";
        if($withNoId)
            $sql .= " (pv.has_id <> 1 || pv.has_id IS NULL) AND ";
        if($is1)
            $sql .= " (pv.is_1 = 1 ) AND ";
        if($is2)
            $sql .= " (pv.is_2 = 1 ) AND ";
        if($is3)
            $sql .= " (pv.is_3 = 1 ) AND ";
        if($is4)
            $sql .= " (pv.is_4 = 1 ) AND ";
        if($is5)
            $sql .= " (pv.is_5 = 1 ) AND ";
        if($is6)
            $sql .= " (pv.is_6 = 1 ) AND ";
        if($is7)
            $sql .= " (pv.is_7 = 1 ) AND ";
        if($is8)
            $sql .= " (pv.is_8 = 1 ) AND ";
        if($is9)
            $sql .= " (pv.is_9 = 1 ) AND ";
        if($is10)
            $sql .= " (pv.is_10 = 1 ) AND ";
        if($isCh)
            $sql .= " (pv.voter_group = 'CH' ) AND ";
        if($isKcl)
            $sql .= " (pv.voter_group = 'KCL' ) AND ";
        if($isKcl0)
            $sql .= " (pv.voter_group = 'KCL0' ) AND ";
        if($isKcl1)
            $sql .= " (pv.voter_group = 'KCL1' ) AND ";
        if($isKcl2)
            $sql .= " (pv.voter_group = 'KCL2' ) AND ";
        if($isKcl3)
            $sql .= " (pv.voter_group = 'KCL3' ) AND ";
        if($isKfc)
            $sql .= " (pv.voter_group = 'KFC' ) AND ";
        if($isDao)
            $sql .= " (pv.voter_group = 'DAO' ) AND ";
        if($isKjr)
            $sql .= " (pv.voter_group = 'KJR' ) AND ";
        if($notSop)
            $sql .= " (pv.is_2 <> 1 OR pv.is_9 <> 1) AND ";
        if($sop)
            $sql .= " (pv.is_2 = 1 OR pv.is_9 = 1) AND ";

        $sql = substr_replace($sql,"",-4);        
        $sql .= " ORDER BY pv.voter_name ASC ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$electId);
        $stmt->bindValue(2,$provinceCode);
        $stmt->bindValue(3,empty($provinceCode) ? null : $provinceCode);
        $stmt->bindValue(4,$municipalityNo);
        $stmt->bindValue(5,empty($municipalityNo) ? null : $municipalityNo);
        $stmt->bindValue(6,$brgyNo);
        $stmt->bindValue(7,empty($brgyNo) ? null : $brgyNo);
        $stmt->bindValue(8,$project);
        $stmt->execute();

        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        return new JsonResponse($data);
    }


    private function allowUpdate($eventId){
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("AppBundle:ProjectEventHeader")->find($eventId);

        return true;
        return $event->getStatus() == 'A'; 
    }


    
    /**
    * @Route("/ajax_post_project_event_header_append", 
    * 	name="ajax_post_project_event_header_append",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostProjectEventHeaderAppendAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $eventId = $request->get("eventId");
        $currentEventId = $request->get("currentEventId");

        $header = $em->getRepository("AppBundle:ProjectEventHeader")->find($eventId);
        
        if(!$header)
            return new JsonResponse(['message' => 'not found'],404);

        $details = $em->getRepository("AppBundle:ProjectEventDetail")->findBy(['eventId' => $eventId]);

        if(count($details) > 0){
          foreach($details as $detail){

                $entity = new ProjectEventDetail();
                $entity->setProVoterId($detail->getProVoterId());
                $entity->setEventId($currentEventId);
                $entity->setProId($detail->getProId());
                $entity->setHasAttended(0);
                $entity->setHasClaimed(0);
                $entity->setHasNewId(0);
                $entity->setCreatedAt(new \DateTime());
                $entity->setCreatedBy($user->getUsername());
                $entity->setAttendedAt(new \DateTime());

                $entity->setStatus(self::STATUS_ACTIVE);

                $validator = $this->get('validator');
                $violations = $validator->validate($entity);

                $errors = [];

                if(count($violations) > 0){
                    foreach( $violations as $violation ){
                        $errors[$violation->getPropertyPath()] =  $violation->getMessage();
                    }
                    
                }else{
                    $em->persist($entity);
                }
          }

          $em->flush();
          $em->clear();
        }

        return new JsonResponse(['message' => 'completed']);
    }

     /**
     * @Route("/ajax_import_event_to_remote/{eventId}",
     *       name="ajax_import_jpm_event_to_remote",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

     public function ajaxImportEventToRemote($eventId){
        $em = $this->getDoctrine()->getManager();
        $emRemote = $this->getDoctrine()->getManager("remote");
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $eventHeader = $em->getRepository("AppBundle:ProjectEventHeader")->find($eventId);
        
        if($eventHeader == null){
            return new JsonResponse(["message" => 'not found'],404);
        }

        $sql = "SELECT * FROM tbl_project_event_detail ed 
                INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = ed.pro_voter_id 
                WHERE ed.event_id = ? AND pv.has_id = 1 AND pv.is_10 <> 10 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$eventId);
        $stmt->execute();
        
        $remoteEventHeader = new ProjectEventHeader();
        $remoteEventHeader->setProId($eventHeader->getProId());
    	$remoteEventHeader->setEventName($eventHeader->getEventName());
        $remoteEventHeader->setEventDesc($eventHeader->getEventDesc());
        $remoteEventHeader->setEventDate($eventHeader->getEventDate());
        $remoteEventHeader->setRemarks($eventHeader->getRemarks());
        $remoteEventHeader->setStatus(self::STATUS_ACTIVE);
        
        $emRemote->persist($remoteEventHeader);
        $emRemote->flush();
        
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){

            $remoteEventDetail = new ProjectEventDetail();
            $remoteEventDetail->setProVoterId($row['pro_voter_id']);
            $remoteEventDetail->setEventId($remoteEventHeader->getEventId());
            $remoteEventDetail->setProId($row['pro_id']);
            $remoteEventDetail->setHasAttended(0);
            $remoteEventDetail->setHasClaimed(0);
            $remoteEventDetail->setHasNewId(0);
            $remoteEventDetail->setCreatedAt(new \DateTime());
            $remoteEventDetail->setCreatedBy($user->getUsername());
            $remoteEventDetail->setAttendedAt(new \DateTime());
            $remoteEventDetail->setStatus(self::STATUS_ACTIVE);

            $emRemote->persist($remoteEventDetail);
            $emRemote->flush();
        }

        return new JsonResponse(['message' => 'test'],200);
     }
}
