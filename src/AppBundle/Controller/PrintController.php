<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;

use AppBundle\Entity\ProjectPrintHeader;
use AppBundle\Entity\ProjectPrintDetail;

/**
* @Route("/print")
*/

class PrintController extends Controller 
{   
    const STATUS_ACTIVE = 'A';
    const MODULE_MAIN = "ID_MAKER";

	/**
    * @Route("", name="print_index", options={"main" = true})
    */

    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $reportIp = $this->getParameter('report_ip');

        return $this->render('template/project-print/index.html.twig',[ 'user' => $user, 'hostIp' => $hostIp, 'reportIp' => $reportIp ]);
    }

    
     /**
     * @Route("/ajax_get_project_print",
     *     name="ajax_get_project_print",
     *     options={"expose" = true})
     *
     * @Method("GET")
     */
    
    public function datatablePrintAction(Request $request){

        $filters = [];
        $filters['h.print_id'] = $request->get('search')['value'];
        $filters['h.print_date'] = $request->get('search')['value'];

        $columns = [
            0 => 'h.print_id',
            1 => 'h.print_date'
        ];
        
        $whereStmt = " AND (";

        foreach($filters as $field => $searchText)
        {
            if($searchText != "")
            {
                $whereStmt .= "{$field} LIKE '%$searchText%' OR ";
            }
        }

        $whereStmt = substr_replace($whereStmt,"",-4);

        if($whereStmt == " A"){
            $whereStmt = "";
        }else{
            $whereStmt .= ")";
        }

        $orderStmt = " ORDER BY h.print_id DESC";

        $start = 1;
        $length = 1;

        if(null !== $request->query->get('start') && null !== $request->query->get('length')){
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }
       
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.print_id),0) FROM tbl_project_print_header h";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.print_id),0) FROM tbl_project_print_header h WHERE 1=1 ";

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.*, (SELECT COALESCE(COUNT(d.print_detail_id),0) FROM tbl_project_print_detail d WHERE d.print_id = h.print_id) AS total_members FROM tbl_project_print_header h WHERE 1=1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

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
    * @Route("/ajax_get_project_voter_no_id/{proId}/{electId}", 
    *   name="ajax_get_project_voter_no_id",
    *   options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetProjectVoterNoId(Request $request,$proId,$electId){
        $em = $this->getDoctrine()->getManager();

        $municipalityName = $request->get('municipalityName');
        $brgyNo = $request->get('brgyNo');
        
        $data = array();
        
        $sql = "SELECT pv.* FROM tbl_project_voter pv 
                WHERE (pv.has_id IS NULL OR pv.has_id = 0 ) AND pv.pro_id_code IS NOT NULL AND 
                pv.pro_id_code <> '' AND pv.has_photo = 1 AND pv.pro_id = ? AND pv.elect_id = ? 
                AND (pv.municipality_name = ? OR ? IS NULL) 
                AND (pv.brgy_no = ? OR ? IS NULL  )
                ORDER BY pv.date_generated ASC, pv.municipality_name ASC LIMIT 100";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$proId);
        $stmt->bindValue(2,$electId);
        $stmt->bindValue(3,empty($municipalityName) ? null : $municipalityName);
        $stmt->bindValue(4,empty($municipalityName) ? null : $municipalityName);
        $stmt->bindValue(5,empty($brgyNo) ? null : $brgyNo);
        $stmt->bindValue(6,empty($brgyNo) ? null : $brgyNo);
        $stmt->execute();
        
        $data = array();
        
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        $em->clear();

        return new JsonResponse($data);
    }
    
    /**
    * @Route("/ajax_get_active_event_voter_no_id/{proId}/{electId}", 
    *   name="ajax_get_active_event_voter_no_id",
    *   options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetActiveEventVoterNoId(Request $request,$proId,$electId){
        $em = $this->getDoctrine()->getManager();
        $activeEvent = $em->getRepository("AppBundle:ProjectEventHeader")
                          ->findOneBy(['status' => self::STATUS_ACTIVE ]);

        $municipalityName = $request->get('municipalityName');
        $brgyNo = $request->get('brgyNo');
        

        if(!$activeEvent){
            return new JsonResponse([]);
        }

        $sql = "SELECT pv.* FROM tbl_project_event_detail d 
                INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = d.pro_voter_id  
                WHERE (pv.has_id IS NULL OR pv.has_id = 0 ) AND pv.pro_id_code IS NOT NULL 
                AND pv.pro_id_code <> '' AND pv.has_photo = 1 AND pv.pro_id = ? 
                AND pv.elect_id = ? AND d.event_id = ? 
                AND (pv.municipality_name = ? OR ? IS NULL)
                AND (pv.brgy_no = ? OR ? IS NULL )
                ORDER BY pv.municipality_name ASC, pv.barangay_name ASC, pv.voter_name ASC ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$proId);
        $stmt->bindValue(2,$electId);
        $stmt->bindValue(3,$activeEvent->getEventId());
        $stmt->bindValue(4, empty($municipalityName) ? null : $municipalityName );
        $stmt->bindValue(5, empty($municipalityName) ? null : $municipalityName);
        $stmt->bindValue(6, empty($brgyNo) ? null : $brgyNo);
        $stmt->bindValue(7, empty($brgyNo) ? null : $brgyNo);
        $stmt->execute();
        
        $data = array();
        
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        $em->clear();

        return new JsonResponse($data);
    }

    /**
    * @Route("/ajax_post_project_print/{proId}",
    *     name="ajax_post_project_print",
    *     options={"expose" = true})
    *
    * @Method("POST")
    */

    public function postPrintAction(Request $request,$proId){
        
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $hdr = new ProjectPrintHeader();
        
        $hdr->setProfiles($request->get('profiles'));
        $hdr->setPrintDate(new \DateTime());
        $hdr->setProId($proId);
        $hdr->setEntryBy($user->getUsername());
        $hdr->setPrintOrigin($request->get("printOrigin"));
        $hdr->setPrintDesc($request->get("printDesc"));
        $hdr->setOriginId($request->get("originId"));

        $validator = $this->get('validator');
        $violations = $validator->validate($hdr);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($hdr);
        $em->flush();

        $profileNumbers = $hdr->getProfiles();

        foreach($profileNumbers as $profileNo){
            $profile = $em->getRepository('AppBundle:ProjectVoter')
                          ->findOneBy(['proVoterId' => $profileNo]);

            $profile->setHasId(1);
            $profile->setDidChanged(1);
            $profile->setToSend(1);
            $profile->setUpdatedAt(new \DateTime());
            $profile->setUpdatedBy($user->getUsername());

            $dtl = new ProjectPrintDetail();
            $dtl->setPrintId($hdr->getPrintId());
            $dtl->setProVoterId($profileNo);
            $dtl->setVoterId($profile->getVoterId());
            
            $em->persist($dtl);
            $em->flush();
        }
        
        $em->clear();
        $serializer = $this->get("serializer");

        return new JsonResponse($serializer->normalize($hdr));
    }

     /**
     * @Route("/ajax_delete_project_print/{printId}",
     *     name="ajax_delete_project_print",
     *     options={"expose" = true})
     *
     * @Method("DELETE")
     */

     public function ajaxDeleteProjectPrint($printId){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $printHeader = $em->getRepository("AppBundle:ProjectPrintHeader")->find($printId);
        $printDetails = $em->getRepository("AppBundle:ProjectPrintDetail")->findBy([
            "printId" => $printId
        ]);
        $serializer = $this->get("serializer");

        foreach($printDetails as $detail){
            $voter = $em->getRepository("AppBundle:ProjectVoter")->find($detail->getProVoterId());
            
            $voter->setHasId(null);
            $voter->setUpdatedAt(new \DateTime());
            $voter->setUpdatedBy($user->getUsername());

            $em->remove($detail);
        }

        $em->remove($printHeader);
        $em->flush();
        
        return new JsonResponse($serializer->normalize($printDetails));


        return new JsonResponse($serializer->normalize($printHeader));
     }  
    
}
