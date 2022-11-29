<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;

use AppBundle\Entity\ProjectClaimStubHeader;
use AppBundle\Entity\ProjectClaimStubDetail;

/**
* @Route("/manage-claim-stub")
*/

class ClaimStubController extends Controller 
{   
    const STATUS_ACTIVE = 'A';
    const MODULE_MAIN = "PROJECT_ClAIM_STUB_MODULE";

	/**
    * @Route("", name="event_claim_stub_index", options={"main" = true})
    */

    public function indexAction(Request $request)
    {

        $this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');
        $reportUrl = $this->getParameter('report_url');

        return $this->render('template/project-claim-stub/index.html.twig',[ 'user' => $user, 'hostIp' => $hostIp , 'imgUrl' => $imgUrl , 'reportUrl' => $reportUrl ]);
    }
    
    /**
     * @Route("/ajax_project_claim_stub_multiselect",
     *   name="ajax_project_claim_stub_multiselect",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxProjectEClaimStubMultiselectAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $electId = empty($request->get('electId')) ? null : $request->get('electId');
        $proId = empty($request->get('proId')) ? null : $request->get('proId');
        $provinceCode = empty($request->get('provinceCode')) ? null : $request->get('provinceCode');
        $municipalityNo = empty($request->get('municipalityNo')) ? null : $request->get('municipalityNo');
        $brgyNo = empty($request->get('brgyNo')) ? null : $request->get('brgyNo');
        $purok = empty($request->get('purok')) ? null : $request->get('purok');
        $addressAlt = empty($request->get('addressAlt')) ? null : $request->get('addressAlt');
        $hasClaimStub = empty($request->get('hasClaimStub')) ? null : $request->get('hasClaimStub');

        $sql = "SELECT pv.* FROM tbl_project_voter pv WHERE 
        pv.elect_id = ? AND 
        pv.pro_id = ?  AND
        (pv.province_code = ? OR ? IS NULL)  AND
        (pv.municipality_no = ? OR ? IS NULL) AND
        (pv.brgy_no = ? OR ? IS NULL) AND
        (pv.purok = ? OR ? IS NULL) AND 
        (pv.address_alt = ? OR ? IS NULL) AND
        (pv.voter_group IS NOT NULL AND pv.voter_group <> '') AND 
        pv.is_1  = 1 ";
    
        if($hasClaimStub == 0){
            $sql .= " AND (pv.has_claim_stub IS NULL or pv.has_claim_stub <> 1) ";
        }
        
        $sql .= " ORDER BY pv.voter_name ASC ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$electId);
        $stmt->bindValue(2,$proId);
        $stmt->bindValue(3,$provinceCode);
        $stmt->bindValue(4,empty($provinceCode) ? null : $provinceCode);
        $stmt->bindValue(5,$municipalityNo);
        $stmt->bindValue(6,empty($municipalityNo) ? null : $municipalityNo);
        $stmt->bindValue(7,$brgyNo);
        $stmt->bindValue(8,empty($brgyNo) ? null : $brgyNo);
        $stmt->bindValue(9,$purok);
        $stmt->bindValue(10,empty($purok) ? null : $purok);
        $stmt->bindValue(11,$addressAlt);
        $stmt->bindValue(12,empty($addressAlt) ? null : $addressAlt);
      
        $stmt->execute();

        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        return new JsonResponse($data);
    }

    /**
    * @Route("/ajax_post_project_claim_stub", 
    * 	name="ajax_post_project_claim_stub",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostProjectClaimStubAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $voters = $request->get("voters");
        $proId = $request->get("proId");
    
        $header  = new ProjectClaimStubHeader();
        $header->setProId($request->get("proId"));
        $header->setMunicipalityNo($request->get('municipalityNo'));
        $header->setBrgyNo($request->get("brgyNo"));
        $header->setTemplateDesc($request->get('templateDesc'));
        $header->setCreatedAt(new \DateTime());
        $header->setCreatedBy($user->getUsername());
        $header->setStatus('A');

        if(count($voters) <= 0){
            return new JsonResponse(['voters' => "This value cannot be empty"],400);
        }

        $validator = $this->get('validator');
        $violations = $validator->validate($header);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }

        $em->persist($header);
        $em->flush();

        foreach($voters as $proVoterId){
            $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->find($proVoterId);

            if($projectVoter){
                
                $entity = new ProjectClaimStubDetail();
                $entity->setProVoterId($proVoterId);
                $entity->setVoterId($projectVoter->getVoterId());
                $entity->setBatchId($header->getBatchId());
                $entity->setProId($request->get("proId"));
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

                $projectVoter->setHasClaimStub(1);
            }
        }
    
        $em->flush();
    	$em->clear();

    	return new JsonResponse(['success' => true]);
    }


      /**
     * @Route("/ajax_get_project_claim_stub_datatable",
     *     name="ajax_get_project_claim_stub_datatable",
     *     options={"expose" = true})
     *
     * @Method("GET")
     */
    
    public function datatableClaimStubAction(Request $request){

        $filters = [];
        $filters['h.template_desc'] = $request->get('templateDesc');

        $columns = [
            0 => 'h.batch_id',
            1 => 'h.template_desc',
            2 => 'h.created_by',
            3 => 'h.created_at'
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

        $orderStmt = " ORDER BY h.batch_id DESC";

        $start = 1;
        $length = 1;

        if(null !== $request->query->get('start') && null !== $request->query->get('length')){
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }
       
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.batch_id),0) FROM tbl_project_claim_stub_header h";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.batch_id),0) FROM tbl_project_claim_stub_header h WHERE 1=1 ";

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.*, (SELECT COALESCE(COUNT(d.batch_detail_id),0) FROM tbl_project_claim_stub_detail d WHERE d.batch_id = h.batch_id) AS total_members FROM tbl_project_claim_stub_header h WHERE 1=1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

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
     * @Route("/ajax_delete_project_claim_stub/{batchId}",
     *     name="ajax_delete_project_claim_stub",
     *     options={"expose" = true})
     *
     * @Method("DELETE")
     */

    public function ajaxDeleteProjectClaimStub($batchId){
        $em = $this->getDoctrine()->getManager();
        $printHeader = $em->getRepository("AppBundle:ProjectClaimStubHeader")->find($batchId);
        $printDetails = $em->getRepository("AppBundle:ProjectClaimStubDetail")->findBy([
            "batchId" => $batchId
        ]);
        $serializer = $this->get("serializer");


        foreach($printDetails as $detail){
            $voter = $em->getRepository("AppBundle:ProjectVoter")->find($detail->getProVoterId());
            $voter->setHasClaimStub(null);
            $em->remove($detail);
        }

        $em->remove($printHeader);
        $em->flush();

        return new JsonResponse($serializer->normalize($printHeader));
     }  
}
