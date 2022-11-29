<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
use AppBundle\Entity\Voter;
use AppBundle\Entity\VoterHistory;
use AppBundle\Entity\VoterApprovalHdr;
use AppBundle\Entity\VoterApprovalDtl;
use AppBundle\Entity\VoterNetwork;
use AppBundle\Entity\VoterSummary;
use AppBundle\Entity\ProjectVoter;

/**
* @Route("/network")
*/

class VoterNetworkController extends Controller 
{
    const STATUS_ACTIVE = 'A';
    const STATUS_PENDING = 'PEN';
    const STATUS_INACTIVE = 'I';
    const MODULE_MAIN = "VOTER_NETWORK";
    const PROVINCE_DEFAULT = 53;
    const ON_NETWORK = 1;
    const NOT_ON_NETWORK = 0;
    const ROOT_NODE_LEVEL = 1;
    
	/**
    * @Route("", name="voter_network_index", options={"main" = true })
    */

    public function indexAction(Request $request)
    {   
        $this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');

        return $this->render('template/voter-network/index.html.twig',['user' => $user, 'hostIp' => $hostIp ]);
    }


    /**
    * @Route("/ajax_get_network_nodes", 
    *       name="ajax_get_network_nodes",
    *		options={ "expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetNetworkNodes(Request $request){

        $em = $this->getDoctrine()->getManager();

        $electId = $request->get("electId");
        $proId = $request->get("proId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");
        $rootId = $request->get("rootId");
        $nodeId = $request->get("nodeId");
            
        if(empty($rootId))
            return new JsonResponse([]);
        
        if(!$this->isAllowed($provinceCode,$municipalityNo,$brgyNo))
            return new JsonResponse(null,401);
        
        $sql = "SELECT n.*,v.voter_no,v.voted_2017 FROM tbl_voter_network n 
                INNER JOIN tbl_voter v ON v.voter_id = n.voter_id WHERE n.node_id = ? LIMIT 1";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$rootId);
        $stmt->execute();
        
        $parentNode = $stmt->fetch(\PDO::FETCH_ASSOC);

        if(empty($parentNode) || !$parentNode)
            return new JsonResponse([]);

        $data = [];
        $rows = [];
        $rows[] = $parentNode;
        
        $children = $this->getChildNodes2($parentNode['node_id']);
        $rows = array_merge($rows,$children);

        foreach($rows as $entity){
            $text = ($entity['voted_2017'] == 1 ? "*" : "") . $entity['node_label'] . ' - ' . $entity['precinct_no'] . ' ( <strong>' .  $entity['voter_group'] . ' , ' . $this->getChildrenCount($entity['node_id']) . '</strong> )';
            $icon = "fa fa-user  icon-lg jstree-themeicon-custom "  . ( $this->getChildrenCount($entity['node_id']) > 0 ? 'icon-state-success' : 'icon-state-danger');

            $data[] = [
                "id" => $entity['node_id'],
                "parent" => $entity['parent_id'] == 0 ? "#" : $entity['parent_id'], 
                "text" => $text,
                "state" => [
                    "opened" => true
                ],
                "icon" => $icon
            ];
        }

        $data[0]['parent'] = '#';
        
        return new JsonResponse($data);
    }

    private function getChildNodes2($nodeId){
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT n.*, v.voter_no, v.voted_2017 FROM tbl_voter_network n 
                INNER JOIN tbl_voter v ON v.voter_id = n.voter_id 
                WHERE n.parent_id = ?";
        
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$nodeId);
        $stmt->execute();

        $entities = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $temp = [];
        
        if(empty($entities) || count($entities) <= 0)
            return [];
        

        foreach($entities as $entity){
            $temp = array_merge($temp,$this->getChildNodes2($entity['node_id']));
        }

        return array_merge($entities,$temp);
    }


    private function getChildNodes($nodeId){
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository(VoterNetwork::class)->findBy(['parentId' => $nodeId]);
        $temp = [];
        
        if(empty($entities) || count($entities) <= 0)
            return [];
        
        $serializer = $this->get("serializer");
        $entities = $serializer->normalize($entities);

        foreach($entities as $entity){
            $temp = array_merge($temp,$this->getChildNodes($entity['nodeId']));
        }

        return array_merge($entities,$temp);
    }

    private function getChildrenCount($nodeId,$recursive = false){
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM tbl_voter_network WHERE parent_id = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$nodeId);
        $stmt->execute();

        $entities = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $count = count($entities);        

        if($recursive){
            foreach($entities as $entity){
                $count += $this->getChildrenCount($entity['node_id'],$recursive);
            }
        }
    
        return $count;
    }


    /**
    * @Route("/ajax_get_root_nodes", 
    *       name="ajax_get_root_nodes",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetRootNodes(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();
        
        $electId = $request->get('electId');
        $proId = $request->get('proId');
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");
        $nodeLevel = $request->get("nodeLevel");

        $searchText = $request->get("searchText");
        $orderBy = $request->get("orderBy");

        if(!$this->isAllowed($provinceCode,$municipalityNo,$brgyNo))
            return new JsonResponse(null,401);

        $sql = "SELECT n.*,v.voted_2017,v.voter_no FROM tbl_voter_network n 
                INNER JOIN tbl_voter v  ON v.voter_id = n.voter_id 
                WHERE n.province_code = ? AND n.municipality_no = ? AND (n.brgy_no = ? OR ? IS NULL) 
                AND (n.node_label LIKE ? OR ? IS NULL) AND n.elect_id = ? AND n.pro_id = ? AND n.node_level = ?";
        
        if($orderBy == 'entryNo'){
            $sql .= ' ORDER BY created_at DESC LIMIT 30';
        }else{
            $sql .= ' ORDER BY node_label ASC LIMIT 30';
        }

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->bindValue(3,$brgyNo);
        $stmt->bindValue(4,empty($brgyNo) ? null : $brgyNo);
        $stmt->bindValue(5,'%' . $searchText . '%');
        $stmt->bindValue(6, empty($searchText) ? NULL : $searchText);
        $stmt->bindValue(7,$electId);
        $stmt->bindValue(8,$proId);
        $stmt->bindValue(9,$nodeLevel);
        $stmt->execute();

        $entities = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if(empty($entities) || count($entities) <= 0)
            $entities = [];

        foreach($entities as &$entity){
            $entity['root_node'] = $this->getRootNode($entity['node_id']);
        }

        return new JsonResponse($entities,200);
    }

    private function getRootNode($nodeId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:VoterNetwork")->find($nodeId);
        $serializer = $this->get("serializer");

        if($entity != null && !empty($entity)){
            if($entity->getParentId() != 0){
                return $this->getRootNode($entity->getParentId());
            }else{
                return $nodeId;
            }
        }

        return 0;
    }

    /**
    * @Route("/ajax_post_root_node/{proId}/{voterId}", 
    *       name="ajax_post_root_node",
    *		options={ "expose" = true }
    * )
    * @Method("POST")
    */

    public function ajaxPostRootNode($proId, $voterId, Request $request){

        $em = $this->getDoctrine()->getManager();
        $voter = $em->getRepository(Voter::class)->find($voterId);
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $electId =  $voter->getElectId();
        $provinceCode  = $voter->getProvinceCode();
        $municipalityNo = $voter->getMunicipalityNo();
        $brgyNo = $voter->getBrgyNo();

        if(!$voter)
            return new JsonResponse(null,404);

        // if((!$this->isAllowed($provinceCode,$municipalityNo, $brgyNo) || !$user->getIsEncoder()) && !$user->getIsAdmin())
        //     return new JsonResponse(null,401);

        
        // if(!empty($request->get("cellphoneNo"))){
        //     if(strlen($request->get('cellphoneNo')) != 11){
        //         return new JsonResponse([
        //             'cellphoneNo' => 'Please use a valid cellphone number. Expecting 11 digits number.'
        //         ],400);
        //     }
        //     if(!preg_match("/^(09)\\d{9}/",$request->get("cellphoneNo"))){
        //         return new JsonResponse([
        //             'cellphoneNo' => "This is not a valid cellphone number."
        //         ],400);
        //     }
        // }

        $entity = new VoterNetwork();
        $entity->setVoterId($voterId);
        $entity->setParentId(0);
        $entity->setElectId($electId);
        $entity->setProId($proId);
        $entity->setProvinceCode($voter->getProvinceCode());
        $entity->setMunicipalityNo($voter->getMunicipalityNo());
        $entity->setCellphoneNo($request->get('cellphoneNo'));
        $entity->setVoterGroup($request->get('voterGroup'));
        $entity->setBrgyNo($voter->getBrgyNo());
        $entity->setPrecinctNo($voter->getPrecinctNo());
        $entity->setNodeLabel($voter->getVoterName());
        $entity->setNodeOrder(1);
        $entity->setNodeLevel(1);
        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());

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

        
        // $voter->setOnNetwork(self::ON_NETWORK);
        
        // if(!empty($request->get("cellphoneNo"))){
        //     $voter->setCellphoneNo($request->get("cellphoneNo"));
        // }
        
        // if(!empty($request->get("remarks"))){
        //     $voter->setRemarks($request->get("remarks"));
        // }
        
        $new = false;
        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => $proId,
            'voterId' => $voterId
        ]);
        
        if(!$projectVoter){
            $projectVoter = new ProjectVoter();
            $new = true;
        }

        $projectVoter->setVoterId($voterId);
        $projectVoter->setProId($proId);
        $projectVoter->setCellphone($request->get('cellphoneNo'));
        $projectVoter->setVoterGroup($request->get('voterGroup'));
        $projectVoter->setRemarks($request->get('remarks'));
        $projectVoter->setStatus(self::STATUS_ACTIVE);

        if($new)
            $em->persist($projectVoter);
        
        $em->flush();

       //$this->updateSummary($electId,$provinceCode,$proId,$municipalityNo,$brgyNo);
        
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity),200);
    }

    /**
    * @Route("/ajax_post_network_node", 
    *       name="ajax_post_network_node",
    *		options={ "expose" = true }
    * )
    * @Method("POST")
    */

    public function ajaxPostNetworkNode(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $voter = $em->getRepository(Voter::class)->find($request->get("voterId"));
        $parentNode = $em->getRepository(VoterNetwork::class)->find($request->get("parentId"));

        $voterId = $voter->getVoterId();
        $electId = $parentNode->getElectId();
        $proId = $parentNode->getProId();
        $provinceCode = $voter->getProvinceCode();
        $municipalityNo = $voter->getMunicipalityNo();
        $brgyNo = $voter->getBrgyNo();
        
        if(!$voter || !$parentNode)
            return new JsonResponse(null,404);

        // if((!$this->isAllowed($provinceCode,$municipalityNo, $brgyNo) || !$user->getIsEncoder()) && !$user->getIsAdmin())
        //     return new JsonResponse(null,401);
        
             
        if(!empty($request->get("cellphoneNo"))){
            if(strlen($request->get('cellphoneNo')) != 11){
                return new JsonResponse([
                    'cellphoneNo' => 'Please use a valid cellphone number. Expecting 11 digits number.'
                ],400);
            }
            if(!preg_match("/^(09)\\d{9}/",$request->get("cellphoneNo"))){
                return new JsonResponse([
                    'cellphoneNo' => "This is not a valid cellphone number."
                ],400);
            }
        }

        $entity = new VoterNetwork();
        $entity->setVoterId($voter->getVoterId());
        $entity->setParentId($parentNode->getNodeId());        
        $entity->setElectId($electId);
        $entity->setProvinceCode($provinceCode);
        $entity->setProId($proId);
        $entity->setMunicipalityNo($municipalityNo);
        $entity->setBrgyNo($brgyNo);
        $entity->setNodeLabel($voter->getVoterName());
        $entity->setPrecinctNo($voter->getPrecinctNo());
        
        $entity->setVoterGroup($request->get("voterGroup"));
        $entity->setCellphoneNo($request->get("cellphoneNo"));
        $entity->setEmailAddress($request->get("emailAddress"));

        $entity->setNodeOrder(1);
        $entity->setNodeLevel($this->getLevel($parentNode->getNodeId()));
        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
           
        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }
    
        
        $new = false;

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => $proId,
            'voterId' => $voterId
        ]);
        
        if(!$projectVoter){
            $projectVoter = new ProjectVoter();
            $new = true;
        }

        $projectVoter->setVoterId($voterId);
        $projectVoter->setProId($proId);
        $projectVoter->setCellphone($request->get('cellphoneNo'));
        $projectVoter->setVoterGroup($request->get('voterGroup'));
        $projectVoter->setRemarks($request->get('remarks'));
        $projectVoter->setStatus(self::STATUS_ACTIVE);

        if($new)
            $em->persist($projectVoter);
        
        $em->flush();

        // $voter->setOnNetwork(self::ON_NETWORK);

        // if(!empty($request->get("cellphoneNo"))){
        //     $voter->setCellphoneNo($request->get("cellphoneNo"));
        // }
        
        // if(!empty($request->get("remarks"))){
        //     $voter->setRemarks($request->get("remarks"));
        // }

        $em->persist($entity);
        $em->flush();

       // $this->updateSummary($provinceCode,$municipalityNo,$brgyNo);

        return new JsonResponse(null,200);
    }

    private function getLevel($nodeId, $level = 1){

        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM tbl_voter_network WHERE node_id = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$nodeId);
        $stmt->execute();
        $row  = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if(empty($row) || $row == null)
            return $level;

        return $this->getLevel((int)$row['parent_id'],++$level);
    }


     /**
    * @Route("/ajax_patch_network_node/{nodeId}", 
    *       name="ajax_patch_network_node",
    *		options={ "expose" = true }
    * )
    * @Method("PATCH")
    */

    public function ajaxPatchNetworkNode(Request $request, $nodeId){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $voter = $em->getRepository(Voter::class)->find($request->get("voterId"));
        $node = $em->getRepository(VoterNetwork::class)->find($nodeId);

        $proId = $node->getProId();
        $voterId = $voter->getVoterId();
        $provinceCode = $voter->getProvinceCode();
        $municipalityNo  = $voter->getMunicipalityNo();
        $brgyNo = $voter->getBrgyNo();

        if(!$voter || !$node)
            return new JsonResponse(null,404);

        // if((!$this->isAllowed($provinceCode,$municipalityNo, $brgyNo) || !$user->getIsEncoder()) && !$user->getIsAdmin())
        //     return new JsonResponse(null,401);
             
        if(!empty($request->get("cellphoneNo"))){
            if(strlen($request->get('cellphoneNo')) != 11){
                return new JsonResponse([
                    'cellphoneNo' => 'Please use a valid cellphone number. Expecting 11 digits number.'
                ],400);
            }
            if(!preg_match("/^(09)\\d{9}/",$request->get("cellphoneNo"))){
                return new JsonResponse([
                    'cellphoneNo' => "This is not a valid cellphone number."
                ],400);
            }
        }

        $node->setVoterGroup($request->get("voterGroup"));
        $node->setCellphoneNo($request->get("cellphoneNo"));
        $node->setEmailAddress($request->get("emailAddress"));
           
        $new = false;

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => $proId,
            'voterId' => $voterId
        ]);
        
        if(!$projectVoter){
            $projectVoter = new ProjectVoter();
            $new = true;
        }

        $projectVoter->setVoterId($voterId);
        $projectVoter->setProId($proId);
        $projectVoter->setCellphone($request->get('cellphoneNo'));
        $projectVoter->setVoterGroup($request->get('voterGroup'));
        $projectVoter->setRemarks($request->get('remarks'));
        $projectVoter->setStatus(self::STATUS_ACTIVE);

        if($new)
            $em->persist($projectVoter);
        
        $em->flush();

        return new JsonResponse(null,200);
    }

    /**
    * @Route("/ajax_delete_network_node/{nodeId}", 
    *       name="ajax_delete_network_node",
    *		options={ "expose" = true }
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteNetworkNode($nodeId){

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository(VoterNetwork::class)->find($nodeId);
        $user = $this->get("security.token_storage")->getToken()->getUser();
        
        if(!$entity){
            return new JsonResponse(null,404);
        }

        $voter = $em->getRepository(Voter::class)->find($entity->getVoterId());
        $provinceCode = $voter->getProvinceCode();
        $municipalityNo = $voter->getMunicipalityNo();
        $brgyNo = $voter->getBrgyNo();

        if(!$voter)
            return new JsonResponse(null,404);
            
        if((!$this->isAllowed($provinceCode,$municipalityNo, $brgyNo) || !$user->getIsEncoder()) && !$user->getIsAdmin())
            return new JsonResponse(null,401);

        $voter->setOnNetwork(self::NOT_ON_NETWORK);
        
        $em->remove($entity);
        $em->flush();

        $this->removeChildren($nodeId);
        //$this->updateSummary($provinceCode,$entity->getMunicipalityNo(),$entity->getBrgyNo());

        return new JsonResponse(null,200);
    }

    /**
    * @Route("/ajax_update_summary", 
    *       name="ajax_update_summary",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxUpdateSummaryAction(Request $request){
        $em = $this->getDoctrine()->getManager();

        $electId = $request->get("electId");
        $proId = $request->get("proId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");

        if(empty($provinceCode))
            return new JsonResponse(null,200);

        $sql = "SELECT * FROM psw_barangay WHERE municipality_code LIKE ? AND (brgy_no  = ? OR ? IS NULL)";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode . $municipalityNo . '%');
        $stmt->bindValue(2, $brgyNo);
        $stmt->bindValue(3, empty($brgyNo) ? null : $brgyNo);
        $stmt->execute();

        $barangays = [];

        while($barangay = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $provinceCode = subStr($barangay['municipality_code'],0,2);
            $municipalityNo = subStr($barangay['municipality_code'],2,2);
            $brgyNo = $barangay['brgy_no'];

            $this->updateSummary($electId, $proId, $provinceCode,$municipalityNo,$brgyNo);
        }

        return new JsonResponse(null,200);
    }

    private function updateSummary($electId, $proId, $provinceCode, $municipalityNo, $brgyNo){
        $em = $this->getDoctrine()->getManager();

        $sql = "UPDATE tbl_voter_summary SET 
                total_members = 0,
                total_leaders = 0,
                total_recruited = 0,
                total_voted_recruits = 0
                WHERE province_code = ? AND municipality_no = ? AND brgy_no = ? AND elect_id = ? AND pro_id = ?";
        
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->bindValue(3,$brgyNo);
        $stmt->bindValue(4,$electId);
        $stmt->bindValue(5,$proId);
        $stmt->execute();

        $sql = "SELECT DISTINCT brgy_no,precinct_no FROM tbl_voter WHERE province_code = ? AND municipality_no = ? AND brgy_no = ? AND elect_id = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->bindValue(3,$brgyNo);
        $stmt->bindValue(4,$electId);
        $stmt->execute();
        $data = [];
        
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        foreach($data as $row){

            $sql = "SELECT 
            COALESCE(COUNT(n.node_id),0) as total_recruited,
            COALESCE(COUNT(CASE WHEN n.parent_id = 0 then 1 end),0) as total_leaders,
            COALESCE(COUNT(CASE WHEN n.parent_id <> 0 then 1 end),0) as total_members,
            COALESCE(COUNT(CASE WHEN v.voted_2017  = 1 then 1 end),0) as total_voted_recruits,
            COALESCE(COUNT(CASE WHEN v.cellphone_no IS NOT NULL AND v.cellphone_no <> '' then 1 end),0) as total_has_cellphone
            FROM tbl_voter_network n 
            INNER JOIN tbl_voter v ON v.voter_id = n.voter_id
            WHERE n.province_code = ? AND n.municipality_no = ? AND n.brgy_no= ? AND n.precinct_no = ? AND n.elect_id = ? AND n.pro_id = ? ";
          
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1,$provinceCode);
            $stmt->bindValue(2,$municipalityNo);
            $stmt->bindValue(3,$brgyNo);
            $stmt->bindValue(4,$row['precinct_no']);
            $stmt->bindValue(5,$electId);
            $stmt->bindValue(6,$proId);
            $stmt->execute();
            
            $networkSummary = $stmt->fetch(\PDO::FETCH_ASSOC);
            $sql = "SELECT created_at FROM tbl_voter_network n 
                    WHERE n.province_code = ? AND n.municipality_no = ? AND n.brgy_no = ? 
                    AND n.precinct_no = ? AND n.elect_id = ? AND n.pro_id = ? 
                    ORDER BY n.created_at DESC limit 1";
            
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1,$provinceCode);
            $stmt->bindValue(2,$municipalityNo);
            $stmt->bindValue(3,$brgyNo);
            $stmt->bindValue(4,$row['precinct_no']);
            $stmt->bindValue(5,$electId);
            $stmt->bindValue(6,$proId);
            $stmt->execute();

            $updatedAt = $stmt->fetchColumn();

            $entity = $em->getRepository(VoterSummary::class)->findOneBy([
                "provinceCode" => $provinceCode,
                "municipalityNo" => $municipalityNo,
                "precinctNo" => $row['precinct_no'],
                "electId" => $electId,
                "proId" => $proId
            ]);
            
           if($entity && !empty($updatedAt)){
                $entity->setTotalRecruited($networkSummary['total_recruited']);
                $entity->setTotalLeaders($networkSummary['total_leaders']);
                $entity->setTotalMembers($networkSummary['total_members']);
                $entity->setTotalVotedRecruits($networkSummary['total_voted_recruits']);
                $entity->setTotalHasCellphone($networkSummary['total_has_cellphone']);
                $entity->setUpdatedAt(new \DateTime($updatedAt));
           }
        }

        $em->flush();
    }
   
    private function removeChildren($nodeId){
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository(VoterNetwork::class)->findBy([
            'parentId' => $nodeId
        ]);

        if(!$entities){
            return 0;
        }

        foreach($entities as $entity){
            $voter = $em->getRepository("AppBundle:Voter")->find($entity->getVoterId());
	        if($voter)
                $voter->setOnNetwork(self::NOT_ON_NETWORK);
                
            $em->remove($entity);
            $this->removeChildren($entity->getNodeId());
        }

        $em->flush();
    }

    /**
    * @Route("/ajax_get_network_node/{nodeId}", 
    *       name="ajax_get_network_node",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetNetworkNode($nodeId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository(VoterNetwork::class)->find($nodeId);
        
        if(!$entity)
            return new JsonResponse(null,404);

        $serializer = $this->get("serializer");

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
    * @Route("/ajax_select2_groupless_voter", 
    *       name="ajax_select2_groupless_voter",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2GrouplessVoter(Request $request){
    	$searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $electId = $request->get("electId");
        $proId = $request->get("proId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");

        if(!$this->isAllowed($provinceCode,$municipalityNo, $brgyNo))
            return new JsonResponse(null,401);

        $em = $this->getDoctrine()->getManager();
        
        $sql = "SELECT * FROM tbl_voter v 
                WHERE  v.province_code = ? AND v.municipality_no = ? AND ( v.brgy_no = ?  OR ? IS NULL)
                AND v.elect_id = ?  
                AND v.voter_name LIKE ? ORDER BY v.voter_name ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->bindValue(3,$brgyNo);
        $stmt->bindValue(4,empty($brgyNo) ? null : $brgyNo );
        $stmt->bindValue(5,$electId);
        $stmt->bindValue(6,$searchText);
     
        $stmt->execute();
        $voters = $stmt->fetchAll();

        if(count($voters) <= 0)
            return new JsonResponse(array());

        foreach($voters as &$voter){
            $sql = "SELECT * FROM tbl_voter_network WHERE voter_id = ? AND elect_id = ? AND pro_id = ? ";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1,$voter['voter_id']);
            $stmt->bindValue(2,$electId);
            $stmt->bindValue(3,$proId);
            $stmt->execute();

            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if(!$row){
                $voter['in_network'] = false;
                $voter['is_leader'] = false;
            }else{
                $voter['in_network'] = true;
                $voter['is_leader'] = $row['parent_id'] == 0  ? true : false;
            }

            // $sql = "SELECT * FROM tbl_project_voter WHERE pro_id = ? AND voter_id = ?";
            // $stmt = $em->getConnection()->prepare($sql);
            // $stmt->bindValue(1,$prodId);
            // $stmt->bindValue(2,$voter['voter_id']);
            // $stmt->execute();
            
            // $projectVoter = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // if(!$projectVoter){
            //     $voter['cellphone_no'] = $project['cellphone'];
            //     $voter['voter_group'] = $project['voter_group'];
            // }else{
            //     $voter['cellphone_no'] = "";
            //     $voter['voter_group'] = "";
            // }
        }

        $em->clear();

        return new JsonResponse($voters);
    }

    /**
    * @Route("/api/select2/province_strict", 
    *       name="ajax_select2_province_strict",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2Province(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';
       
        $sql = "SELECT * FROM psw_province p 
                WHERE p.name LIKE ? ";

        $sql .= " ORDER BY p.name ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$searchText);
     
        $stmt->execute();
        $provinces = $stmt->fetchAll();

        if(count($provinces) <= 0)
            return new JsonResponse(array());

        $em->clear();

        return new JsonResponse($provinces);
    }

    /**
    * @Route("/api/select2/municipality_strict", 
    *       name="ajax_select2_municipality_strict",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2Municipality(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $searchText = trim(strtoupper($request->get('searchText')));
        $provinceCode = $request->get("provinceCode");

        $sql = "SELECT * FROM psw_municipality m WHERE (m.name LIKE ?  OR ? IS NULL) ";
        $sql .= $this->getMunicipalityAccessFilter();
        $sql .= " AND m.province_code = ? ORDER BY m.name ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,'%'  . $searchText . '%');
        $stmt->bindValue(2, empty($searchText) ? null : $searchText);
        $stmt->bindValue(3, $provinceCode);
        $stmt->execute();

        $municipalities = $stmt->fetchAll();

        if(count($municipalities) <= 0)
            return new JsonResponse(array());

        $em->clear();

        return new JsonResponse($municipalities);
    }
  
    private function getMunicipalityAccessFilter(){
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        if($user->getIsAdmin())
            return " ";

        $currentDate = date('Y-m-d H:i:s');
        $sql = "SELECT DISTINCT u.municipality_no , u.province_code FROM tbl_user_access u WHERE u.user_id = ? AND u.valid_until > ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$user->getId());
        $stmt->bindValue(2,$currentDate);
        $stmt->execute();

        $permissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if(count($permissions) <= 0){
            $permissions = [];
        }

        $sql = ' AND (';

        foreach($permissions as $permission){
            $municipalityNo = $permission['municipality_no'];
            $provinceCode = $permission['province_code'];

            $sql .= "(m.municipality_no = {$municipalityNo} AND m.province_code = {$provinceCode}) OR ";
        }

        $sql  = rtrim($sql,'OR ');
        $sql .= ")";

        if($sql == " AND ()")
            $sql = " AND (m.municipality_no IS NULL AND m.province_code IS NULL)";
        
        return $sql;
    }

    /**
    * @Route("/api/select2/barangay_strict", 
    *       name="ajax_select2_barangay_strict",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2Barangay(Request $request){
        $em = $this->getDoctrine()->getManager();
    	$searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");

        $sql = "SELECT b.* FROM psw_barangay b INNER JOIN psw_municipality m ON m.municipality_code = b.municipality_code AND m.province_code = ?
                WHERE m.municipality_no = ? AND b.name LIKE ?";
        
        $sql .= $this->getBarangayAccessFilter();
        $sql .= " ORDER BY b.name ASC LIMIT 30";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->bindValue(3,$searchText);
     
        $stmt->execute();
        $barangays = $stmt->fetchAll();

        if(count($barangays) <= 0)
            return new JsonResponse(array());

        $em->clear();

        return new JsonResponse($barangays);
    }

    private function getBarangayAccessFilter(){
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if($user->getIsAdmin() || $user->getStrictAccess() != 1)
            return "";

        $em = $this->getDoctrine()->getManager();
        $currentDate = date('Y-m-d H:i:s');
        $sql = "SELECT DISTINCT u.brgy_no, u.municipality_no , u.province_code FROM tbl_user_access u WHERE u.user_id = ? AND u.valid_until > ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$user->getId());
        $stmt->bindValue(2,$currentDate);
        $stmt->execute();

        $permissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if(count($permissions) <= 0){
            $permissions = [];
        }

        $sql = ' AND (';
        
        foreach($permissions as $permission){
            $municipalityNo = $permission['municipality_no'];
            $brgyNo = $permission['brgy_no'];
            $provinceCode = $permission['province_code'];

            $sql .= "(b.municipality_code = '{$provinceCode}{$municipalityNo}' AND b.brgy_no = '{$brgyNo}') OR";
        }

        $sql  = rtrim($sql,'OR');
        $sql .= ")";

        if($sql == " AND ()")
            $sql = " AND (v.municipality_no IS NULL AND v.brgy_no IS NULL)";

        return $sql;        
    }

    /**
    * @Route("/ajax_select2_precinct_no_strict", 
    *       name="ajax_select2_precinct_no_strict",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2PrecinctNoStrict(Request $request){
    	$searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';
        
        $electId = $request->get("electId");
        $provinceCode = $request->get('provinceCode');
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get('brgyNo');

        $em = $this->getDoctrine()->getManager();
       
        $sql = "SELECT DISTINCT v.precinct_no FROM tbl_voter v 
                WHERE v.province_code = ? AND v.municipality_no = ? AND v.brgy_no = ? AND v.elect_id = ? AND (v.precinct_no LIKE ? OR ? IS NULL) ORDER BY v.precinct_no ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->bindValue(3, $brgyNo);
        $stmt->bindValue(4, $electId);
        $stmt->bindValue(5, $searchText);
        $stmt->bindValue(6, ($request->get("searchText") == "") ? null : $request->get("searchText"));
     
        $stmt->execute();
        $precincts = $stmt->fetchAll();

        if(count($precincts) <= 0)
            return new JsonResponse(array());

        $em->clear();

        return new JsonResponse($precincts);
    }


    /**
    * @Route("ajax_get_municipality_full/{municipalityNo}", 
    *       name="ajax_get_municipality_full",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetMunicipalityFull($municipalityNo){

        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT 
            (SELECT COALESCE(COUNT(n.node_id),0) FROM tbl_voter_network n WHERE n.municipality_no = ?) as total_recruits,
            (SELECT COALESCE(COUNT(v.voter_id),0) FROM tbl_voter v WHERE v.municipality_no = ?) AS total_voter,
            m.* FROM tbl_municipality m WHERE m.municipality_code = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$municipalityNo);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->bindValue(3,'53'.$municipalityNo);
        $stmt->execute();
        
        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
    * @Route("/ajax_get_barangay_full", 
    *       name="ajax_get_baranagy_full",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetBarangayFull(Request $request){
        
        $em = $this->getDoctrine()->getManager();

        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get('municipalityNo');
        $brgyNo = $request->get("brgyNo");

        $sql = "SELECT * FROM psw_municipality WHERE province_code = ? AND municipality_no = ?";
        $stmt  = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->execute();

        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if(!$municipality)
            return new JsonResponse([]);

        $sql = "SELECT 
            (SELECT COALESCE(node_level,0) FROM tbl_voter_network nn WHERE nn.province_code = ? AND nn.municipality_no = ? AND nn.brgy_no = ? ORDER BY node_level DESC LIMIT 1) max_deep,
            (SELECT COALESCE(COUNT(n.node_id),0) FROM tbl_voter_network n WHERE n.province_code = ? AND n.municipality_no = ? AND n.brgy_no = ?) as total_recruits,
            (SELECT COALESCE(COUNT(v.voter_id),0) FROM tbl_voter v WHERE v.province_code = ? AND v.municipality_no = ? AND v.brgy_no  = ? ) AS total_voter,
            b.* FROM psw_barangay b WHERE b.municipality_code = ? AND b.brgy_no = ?";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->bindValue(3,$brgyNo);
        $stmt->bindValue(4,$provinceCode);
        $stmt->bindValue(5,$municipalityNo);
        $stmt->bindValue(6,$brgyNo);
        $stmt->bindValue(7,$provinceCode);
        $stmt->bindValue(8,$municipalityNo);
        $stmt->bindValue(9,$brgyNo);
        $stmt->bindValue(10,$provinceCode . $municipalityNo);
        $stmt->bindValue(11,$brgyNo);
    
        $stmt->execute();
        
        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);

        if(empty($barangay))
            return new JsonResponse([
                "total_recruits" => 0 ,
                "total_voter" => 0,
                "percentage" => 0,
                "name" => "",
                "municipality" => "",
                "max_deep" => 0
            ]);

        $barangay['percentage'] = $barangay['total_voter'] != 0 ? ($barangay['total_recruits'] / $barangay['total_voter']) * 100 : 0;
        $barangay['municipality'] = $municipality['name'];

        return new JsonResponse($barangay);
    }


    private function isAllowed($provinceCode, $municipalityNo, $brgyNo){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        if($user->getIsAdmin())
          return true;
        
        $currentDate = date('Y-m-d H:i:s');

        $sql = "SELECT DISTINCT u.municipality_no, u.brgy_no FROM tbl_user_access u 
        WHERE u.user_id = ? AND u.valid_until > ? AND u.province_code = ? AND u.municipality_no = ? AND u.brgy_no = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$user->getId());
        $stmt->bindValue(2,$currentDate);
        $stmt->bindValue(3,$provinceCode);
        $stmt->bindValue(4,$municipalityNo);
        $stmt->bindValue(5,$brgyNo);
        $stmt->execute();

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            return true;
        }

        return false;
    }
}
