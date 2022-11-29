<?php
namespace AppBundle\Controller;

use AppBundle\Entity\ProjectVoter;
use AppBundle\Entity\FormStatus;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/form-status")
 */

class FormStatusController extends Controller
{
    const STATUS_ACTIVE = 'A';
    const STATUS_INACTIVE = 'I';
    const STATUS_BLOCKED = 'B';
    const STATUS_PENDING = 'PEN';
    const MODULE_MAIN = "VOTER";

    /**
     * @Route("", name="form_status_index", options={"main" = true })
     */

    public function indexAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/form-status/index.html.twig', ['user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl]);
    }


      /**
     * @Route("/checklist", name="form_status_checklist", options={"main" = true })
     */

    public function checklistAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/form-status-checklist/index.html.twig', ['user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl]);
    }

    /**
    * @Route("/ajax_post_form_status", 
    * 	name="ajax_post_form_status",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostFormStatusAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $entity = new FormStatus();
    	$entity->setProVoterId($request->get('proVoterId'));
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));
        $entity->setRecFormSub($request->get('recFormSub'));
        $entity->setHouseFormSub($request->get('houseFormSub'));
        $entity->setRecFormSubCount($request->get('recFormSubCount'));
        $entity->setHouseFormSubCount($request->get('houseFormSubCount'));
        $entity->setRecFormSubDate($request->get('recFormSubDate'));
        $entity->setHouseFormSubDate($request->get('houseFormSubDate'));
        $entity->setVoterGroup(trim(strtoupper($request->get("voterGroup"))));

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('proVoterId'))]);

        if($proVoter) {
            $entity->setVoterName($proVoter->getVoterName());
            $entity->setProIdCode($proVoter->getProIdCode());
            $proVoter->setVoterGroup($entity->getVoterGroup());
        }

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

        $sql = "SELECT * FROM psw_municipality 
        WHERE province_code = ? 
        AND municipality_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53);
        $stmt->bindValue(2,$entity->getMunicipalityNo());
        $stmt->execute();

        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);

        if($municipality != null)
            $entity->setMunicipalityName($municipality['name']);

        $sql = "SELECT * FROM psw_barangay 
        WHERE brgy_code = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,53 . $entity->getMunicipalityNo() . $entity->getBarangayNo());
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if($barangay != null)
            $entity->setBarangayName($barangay['name']);

        $em->persist($entity);
        $em->flush();
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }

    /**
    * @Route("/ajax_patch_form_status/{id}", 
    * 	name="ajax_patch_form_status",
    *	options={"expose" = true}
    * )
    * @Method("PATCH")
    */

    public function ajaxPatchFormStatusAction($id, Request $request){
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:FormStatus")
            ->find($id);

        if(!$entity){
            return new JsonResponse(['message' => 'not found']);
        }

        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));
        $entity->setRecFormSub($request->get('recFormSub'));
        $entity->setHouseFormSub($request->get('houseFormSub'));
        $entity->setRecFormSubCount($request->get('recFormSubCount'));
        $entity->setHouseFormSubCount($request->get('houseFormSubCount'));
        $entity->setRecFormSubDate($request->get('recFormSubDate'));
        $entity->setHouseFormSubDate($request->get('houseFormSubDate'));
        $entity->setVoterGroup(trim(strtoupper($request->get('voterGroup'))));

    	$validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }


        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->find($entity->getProVoterId());

        if($proVoter) {
            $proVoter->setVoterGroup($entity->getVoterGroup());
        }

        $sql = "SELECT * FROM psw_municipality 
        WHERE province_code = ? 
        AND municipality_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53);
        $stmt->bindValue(2,$entity->getMunicipalityNo());
        $stmt->execute();

        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);

        if($municipality != null)
            $entity->setMunicipalityName($municipality['name']);

        $sql = "SELECT * FROM psw_barangay 
        WHERE brgy_code = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,53 . $entity->getMunicipalityNo() . $entity->getBarangayNo());
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if($barangay != null)
            $entity->setBarangayName($barangay['name']);

        $em->flush();
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }


    /**
     * @Route("/ajax_get_datatable_form_status", name="ajax_get_datatable_form_status", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    
	public function ajaxGetDatatableFormStatusAction(Request $request)
	{	
        $columns = array(
            0 => "h.id",
            1 => "h.voter_name",
            2 => "h.municipality_name",
            3 => "h.barangay_name",
            4 => "h.rec_form_sub",
            5 => "h.house_form_sub",
        );

        $sWhere = "";
    
        $select['h.voter_name'] = $request->get("voterName");
        $select['h.municipality_name'] = $request->get("municipalityName");
        $select['h.barangay_name'] = $request->get("barangayName");
        
        foreach($select as $key => $value){
            $searchValue = $select[$key];
            if($searchValue != null || !empty($searchValue)) {
                $sWhere .= " AND " . $key . " LIKE '%" . $searchValue . "%'";
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

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_form_status h";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_form_status h WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.* FROM tbl_form_status h 
            WHERE 1 " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $row['total_members'] = 0;
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
    * @Route("/ajax_delete_form_status/{id}", 
    * 	name="ajax_delete_form_status",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteFormStatusAction($id){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:FormStatus")->find($id);

        if(!$entity)
            return new JsonResponse(null,404);

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null,200);
    }

    /**
     * @Route("/ajax_get_form_status/{id}",
     *       name="ajax_get_form_status",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetFormStatus($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:FormStatus")
            ->find($id);

        if(!$entity){
            return new JsonResponse(['message' => 'not found']);
        }

        $serializer = $this->get("serializer");
        $entity = $serializer->normalize($entity);
        
        return new JsonResponse($entity);
    }

 
    /**
    * @Route("/ajax_select2_form_status_voters", 
    *       name="ajax_select2_form_status_voters",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2FormStatusVoters(Request $request){
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
                AND p.voter_group IS NOT NULL 
                AND p.voter_group <> '' 
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
    * @Route("/ajax_select2_member_summary_dates", 
    *       name="ajax_select2_member_summary_dates",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2SummaryDates(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';
        
        $sql = "SELECT distinct s.generated_at FROM tbl_project_member_summary s 
                WHERE s.generated_at LIKE ? 
                ORDER BY s.generated_at DESC LIMIT 10";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$searchText);
        $stmt->execute();

        $data =  $stmt->fetchAll(\PDO::FETCH_ASSOC);
      
        return new JsonResponse($data);
    }

    /**
    * @Route("/ajax_patch_form_status_tag/{proVoterId}", 
    * 	name="ajax_patch_form_status_tag",
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


        if($this->isTogglable($request->get("recFormSub"))){
            $projectVoter->setRecFormSub($request->get('recFormSub'));
        }
        
        if($this->isTogglable($request->get("houseFormSub"))){
            $projectVoter->setHouseFormSub($request->get('houseFormSub'));
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
}
