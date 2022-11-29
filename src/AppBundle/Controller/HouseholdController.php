<?php
namespace AppBundle\Controller;

use AppBundle\Entity\ProjectVoter;
use AppBundle\Entity\HouseholdHeader;
use AppBundle\Entity\HouseholdDetail;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/household")
 */

class HouseholdController extends Controller
{
    const STATUS_ACTIVE = 'A';
    const STATUS_INACTIVE = 'I';
    const STATUS_BLOCKED = 'B';
    const STATUS_PENDING = 'PEN';
    const MODULE_MAIN = "VOTER";

    /**
     * @Route("", name="household_index", options={"main" = true })
     */

    public function indexAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/household/index.html.twig', ['user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl]);
    }

    /**
    * @Route("/ajax_post_household_header", 
    * 	name="ajax_post_household_header",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostHouseholdHeaderAction(Request $request){
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $entity = new HouseholdHeader();
        $entity->setElectId($request->get('electId'));
    	$entity->setProVoterId($request->get('proVoterId'));
        $entity->setHouseholdNo($this->getNewHouseholdNo());
        $entity->setHouseholdCode(sprintf("%06d", $entity->getHouseholdNo()));
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));

        $entity->setFirstname($request->get('firstname'));
        $entity->setLastname($request->get('lastname'));
        $entity->setMiddlename($request->get('middlename'));
        $entity->setExtName($request->get('extName'));
        $entity->setGender($request->get('gender'));
        
        $entity->setIsTagalog($request->get('isTagalog'));
        $entity->setIsCuyonon($request->get('isCuyonon'));
        $entity->setIsBisaya($request->get('isBisaya'));
        $entity->setIsIlonggo($request->get('isIlonggo'));

        $entity->setIsCatholic($request->get('isCatholic'));
        $entity->setIsInc($request->get('isInc'));
        $entity->setIsIslam($request->get('isIslam'));
        $entity->setCellphone($request->get('cellphoneNo'));
        $entity->setPosition($request->get('position'));

        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
        $entity->setRemarks($request->get('remarks'));
    	$entity->setStatus(self::STATUS_ACTIVE);

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('proVoterId'))]);

        if($proVoter){
            if(!empty($request->get('cellphoneNo')))
                $proVoter->setCellphone($request->get('cellphoneNo'));
            
            $proVoter->setFirstname($entity->getFirstname());
            $proVoter->setMiddlename($entity->getMiddlename());
            $proVoter->setLastname($entity->getLastname());
            $proVoter->setExtname($entity->getExtName());
            $proVoter->setGender($entity->getGender());
            $proVoter->setBirthdate(trim($request->get('birthdate')));
            $proVoter->setCivilStatus(trim(strtoupper($request->get('civilStatus'))));
            $proVoter->setBloodtype(trim(strtoupper($request->get('bloodtype'))));
            $proVoter->setOccupation(trim(strtoupper($request->get('occupation'))));
            $proVoter->setReligion(trim(strtoupper($request->get('religion'))));
            $proVoter->setDialect(trim(strtoupper($request->get('dialect'))));
            $proVoter->setIpGroup(trim(strtoupper($request->get('ipGroup'))));
            $proVoter->setVoterGroup(trim(strtoupper($request->get('voterGroup'))));
            
            $proVoter->setIsTagalog($entity->getIsTagalog());
            $proVoter->setIsCuyonon($entity->getIsCuyonon());
            $proVoter->setIsBisaya($entity->getIsBisaya());
            $proVoter->setIsIlonggo($entity->getIsIlonggo());

            $proVoter->setIsCatholic($entity->getIsCatholic());
            $proVoter->setIsInc($entity->getIsInc());
            $proVoter->setIsIslam($entity->getIsIslam());
            $proVoter->setPosition($entity->getPosition());

            $entity->setVoterName($proVoter->getVoterName());
            $entity->setProIdCode($proVoter->getProIdCode());
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

    private function getNewHouseholdNo(){
        $householdNo = 1;
       
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT household_no FROM tbl_household_hdr ORDER BY household_no DESC LIMIT 1 ";

        $stmt = $em->getConnection()->query($sql);

        $request = $stmt->fetch();

        if($request){
            $householdNo = intval($request['household_no']) + 1;
        }

        return $householdNo;
    }

    /**
    * @Route("/ajax_patch_household_header/{householdId}", 
    * 	name="ajax_patch_household_header",
    *	options={"expose" = true}
    * )
    * @Method("PATCH")
    */

    public function ajaxPatchHouseholdHeaderAction(Request $request,$householdId){
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository("AppBundle:HouseholdHeader")
                    ->find($householdId);
        
        if(!$entity)
            return new JsonResponse([],404);
        
    	$entity->setProVoterId($request->get('proVoterId'));
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));

        $entity->setFirstname($request->get('firstname'));
        $entity->setLastname($request->get('lastname'));
        $entity->setMiddlename($request->get('middlename'));
        $entity->setExtName($request->get('extName'));
        $entity->setGender($request->get('gender'));
        $entity->setPosition($request->get('position'));

        $entity->setIsTagalog($request->get('isTagalog'));
        $entity->setIsCuyonon($request->get('isCuyonon'));
        $entity->setIsBisaya($request->get('isBisaya'));
        $entity->setIsIlonggo($request->get('isIlonggo'));

        $entity->setIsCatholic($request->get('isCatholic'));
        $entity->setIsInc($request->get('isInc'));
        $entity->setIsIslam($request->get('isIslam'));
        $entity->setCellphone($request->get('cellphoneNo'));

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


        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('proVoterId'))]);

        if($proVoter){
            if(!empty($request->get('cellphoneNo')))
                $proVoter->setCellphone($request->get('cellphoneNo'));
            
            $proVoter->setFirstname($entity->getFirstname());
            $proVoter->setMiddlename($entity->getMiddlename());
            $proVoter->setLastname($entity->getLastname());
            $proVoter->setExtName($entity->getExtName());
            $proVoter->setGender($entity->getGender());
            $proVoter->setBirthdate(trim($request->get('birthdate')));
            $proVoter->setCivilStatus(trim(strtoupper($request->get('civilStatus'))));
            $proVoter->setBloodtype(trim(strtoupper($request->get('bloodtype'))));
            $proVoter->setOccupation(trim(strtoupper($request->get('occupation'))));
            $proVoter->setReligion(trim(strtoupper($request->get('religion'))));
            $proVoter->setDialect(trim(strtoupper($request->get('dialect'))));
            $proVoter->setIpGroup(trim(strtoupper($request->get('ipGroup'))));
            $proVoter->setVoterGroup(trim(strtoupper($request->get('voterGroup'))));
            $proVoter->setPosition(trim(strtoupper($request->get('position'))));

                        
            $proVoter->setIsTagalog($entity->getIsTagalog());
            $proVoter->setIsCuyonon($entity->getIsCuyonon());
            $proVoter->setIsBisaya($entity->getIsBisaya());
            $proVoter->setIsIlonggo($entity->getIsIlonggo());

            $proVoter->setIsCatholic($entity->getIsCatholic());
            $proVoter->setIsInc($entity->getIsInc());
            $proVoter->setIsIslam($entity->getIsIslam());
            
            $entity->setVoterName($proVoter->getVoterName());
            $entity->setProIdCode($proVoter->getProIdCode());
        }

        $em->flush();
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }

    
    /**
     * @Route("/ajax_get_datatable_household_header", name="ajax_get_datatable_household_header", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    
	public function ajaxGetDatatableHouseholdHeaderAction(Request $request)
	{	
        $columns = array(
            0 => "h.household_code",
            1 => "h.voter_name",
            2 => "h.municipality_name",
            3 => "h.barangay_name"
        );

        $sWhere = "";
    
        $select['h.voter_name'] = $request->get("voterName");
        $select['h.municipality_name'] = $request->get("municipalityName");
        $select['h.barangay_name'] = $request->get("barangayName");
        $select['h.elect_id'] = $request->get("electId");
        $select['h.municipality_no'] = $request->get("municipalityNo");
        $select['h.brgy_no'] = $request->get("barangayNo");

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {

                if ($key == 'h.elect_id' || $key == 'h.municipality_no' || $key == 'h.barangay_no') {
                    $sWhere .= "AND  {$key} = '{$searchValue}' ";
                }
                $sWhere .= " AND " . $key . " LIKE '%" . $searchValue . "%' ";
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

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_household_hdr h ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_household_hdr h WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.* FROM tbl_household_hdr h 
            WHERE 1 " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        foreach($data as &$row){
            $sql = "SELECT COUNT(*) FROM tbl_household_dtl WHERE household_id = ? ";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $row['id']);
            $stmt->execute();

            $totalMembers = intval($stmt->fetchColumn());

            $row['total_members'] = $totalMembers;
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
		$res['data'] =  $data;
	    $res['recordsTotal'] = $recordsTotal;
	    $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

	    return new JsonResponse($res);
    }


    
    /**
     * @Route("/ajax_get_datatable_household_header_no_recruitment", name="ajax_get_datatable_household_header_no_recruitment", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    
	public function ajaxGetDatatableHouseholdHeaderNoRecruitmentAction(Request $request)
	{	
        $columns = array(
            0 => "h.household_code",
            1 => "h.voter_name",
            2 => "h.municipality_name",
            3 => "h.barangay_name"
        );

        $sWhere = "";
    
        $select['h.voter_name'] = $request->get("voterName");
        $select['h.municipality_name'] = $request->get("municipalityName");
        $select['h.barangay_name'] = $request->get("barangayName");
        $select['h.elect_id'] = $request->get("electId");
        $select['h.municipality_no'] = $request->get("municipalityNo");
        $select['h.barangay_no'] = $request->get("barangayNo");
        
        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {

                if ($key == 'h.elect_id' || $key == 'h.municipality_no' || $key == 'h.barangay_no') {
                    $sWhere .= "AND  {$key} = '{$searchValue}' ";
                }
                $sWhere .= " AND " . $key . " LIKE '%" . $searchValue . "%' ";
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

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_household_hdr h WHERE h.pro_id_code NOT IN (SELECT r.pro_id_code FROM tbl_recruitment_hdr r ) ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_household_hdr h WHERE h.pro_id_code NOT IN (SELECT r.pro_id_code FROM tbl_recruitment_hdr r ) ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.* FROM tbl_household_hdr h 
            WHERE h.pro_id_code NOT IN (SELECT r.pro_id_code FROM tbl_recruitment_hdr r ) " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";

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
    * @Route("/ajax_delete_household_header/{householdId}", 
    * 	name="ajax_delete_household_header",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteHouseholdHeaderAction($householdId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:HouseholdHeader")->find($householdId);

        if(!$entity)
            return new JsonResponse(null,404);

        $entities = $em->getRepository('AppBundle:HouseholdDetail')->findBy([
            'householdId' => $entity->getId()
        ]);

        foreach($entities as $detail){
            $em->remove($detail);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null,200);
    }

    /**
     * @Route("/ajax_get_household_header/{id}",
     *       name="ajax_get_household_header",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetHouseholdHeader($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:HouseholdHeader")
            ->find($id);

        if(!$entity){
            return new JsonResponse(['message' => 'not found']);
        }

        $serializer = $this->get("serializer");
        $entity = $serializer->normalize($entity);

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->find($entity['proVoterId']);

        if($proVoter != null){
            $entity['cellphone'] = $proVoter->getCellphone();
            $entity['lgc'] = $this->getLGC($proVoter->getMunicipalityNo(), $proVoter->getBrgyNo());
        }else{
            $entity['cellphone'] = "VOTER MISSING";
            $entity['lgc'] = [
                "voter_name" => "VOTER MISSING",
                "cellphone" => "VOTER MISSING"
            ];
        }
        
        return new JsonResponse($entity);
    }

    
    private function getLGC($municipalityNo, $barangayNo){
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT pv.voter_name, pv.cellphone, la.municipality_name, la.barangay_name FROM tbl_location_assignment la INNER JOIN tbl_project_voter pv 
                ON pv.pro_voter_id = la.pro_voter_id  
                WHERE la.municipality_no = ? AND la.barangay_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityNo);
        $stmt->bindValue(2, $barangayNo);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row == null ? ['voter_name' => "No LGC"] : $row;
    }


     /**
     * @Route("/ajax_get_household_header_full/{id}",
     *       name="ajax_get_household_header_full",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetHouseholdFullHeader($id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $sql = "SELECT h.*, pv.cellphone, pv.pro_voter_id, pv.birthdate, pv.gender,
                pv.firstname, pv.middlename, pv.lastname, pv.ext_name, pv.civil_status, pv.bloodtype,
                pv.occupation, pv.religion, pv.dialect, pv.ip_group
                FROM tbl_household_hdr h 
                INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = h.pro_voter_id 
                WHERE h.id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$id);
        $stmt->execute();

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse($data);
    }

    /**
    * @Route("/ajax_post_household_detail", 
    * 	name="ajax_post_household_detail",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostHouseholdDetailAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $entity = new HouseholdDetail();
        $entity->setHouseholdId($request->get('householdId'));
    	$entity->setProVoterId($request->get('proVoterId'));
        $entity->setRelationship(trim(strtoupper($request->get('relationship'))));
        $entity->setGender($request->get('gender'));
        $entity->setBirthDate($request->get('birthdate'));
        $entity->setFirstname(trim(strtoupper($request->get('firstname'))));
        $entity->setMiddlename(trim(strtoupper($request->get('middlename'))));
        $entity->setLastname(trim(strtoupper($request->get('lastname'))));
        $entity->setExtName(trim(strtoupper($request->get('extName'))));
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));
        $entity->setCellphone(trim($request->get('cellphone')));
        $entity->setPosition(trim(strtoupper($request->get('position'))));

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('proVoterId'))]);

        if($proVoter) {
            $entity->setVoterName($proVoter->getVoterName());
            $entity->setProIdCode($proVoter->getProIdCode());
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

        if($proVoter){
            if(!empty($entity->getCellphone()))
                $proVoter->setCellphone($entity->getCellphone());
            
            $proVoter->setFirstname($entity->getFirstname());
            $proVoter->setMiddlename($entity->getMiddlename());
            $proVoter->setLastname($entity->getLastname());
            $proVoter->setExtname($entity->getExtName());
            $proVoter->setGender($entity->getGender());
            $proVoter->setBirthdate($entity->getBirthdate());
            $proVoter->setPosition(trim(strtoupper($entity->getPosition())));
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
     * @Route("/ajax_select2_relationship",
     *       name="ajax_select2_relationship",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Relationship(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT relationship FROM tbl_household_dtl h WHERE h.relationship LIKE ? ORDER BY h.relationship ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $data = $stmt->fetchAll();

        if (count($data) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($data);
    }


    /**
     * @Route("/ajax_get_datatable_household_detail", name="ajax_get_datatable_household_detail", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    
	public function ajaxGetDatatableHouseholdDetailAction(Request $request)
	{	
        $columns = array(
            0 => "h.household_id",
            1 => "h.voter_name",
            2 => "h.relationship",
            3 => "h.barangay_name",
            4 => "h.cellphone"
        );

        $sWhere = "";
    
        $select['h.household_id'] = $request->get('householdCode');
        $select['h.voter_name'] = $request->get("voterName");
        $householdId = $request->get('householdId');
        
        foreach($select as $key => $value){
            $searchValue = $select[$key];
            if($searchValue != null || !empty($searchValue)) {
                $sWhere .= " AND " . $key . " LIKE '%" . $searchValue . "%'";
            }
        }
        
        
        $sWhere .= " AND h.household_id = ${householdId} ";

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

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_household_dtl h WHERE h.household_id = ${householdId}";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_household_dtl h WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.*, v.birthdate , v.cellphone FROM tbl_household_dtl h INNER JOIN tbl_project_voter v ON v.pro_voter_id = h.pro_voter_id 
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
    * @Route("/ajax_delete_household_detail/{householdDetailId}", 
    * 	name="ajax_delete_household_detail",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteHouseholdDetailAction($householdDetailId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:HouseholdDetail")->find($householdDetailId);

        if(!$entity)
            return new JsonResponse(null,404);

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null,200);
    }

      /**
    * @Route("/ajax_fill_household_summary", 
    * 	name="ajax_fill_household_summary",
    *	options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxFillHouseholdSummaryAction(Request $request){
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM psw_municipality 
                WHERE province_code = 53 AND municipality_no <> 16  AND municipality_no IN (SELECT DISTINCT municipality_no FROM tbl_project_voter pv 
                WHERE pv.elect_id = 3 AND pv.pro_id = 3 AND voter_group IN ('LOPP','LPPP','LGO','LGC')) ORDER BY NAME ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53);
        $stmt->execute();

        $municipalities = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $data = [];

        foreach($municipalities as $municipality){        

            $sql = "DELETE FROM tbl_household_summary WHERE municipality_no = ? ";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $municipality['municipality_no']);
            $stmt->execute();
            
            $sql = "SELECT * FROM psw_barangay 
                    WHERE municipality_code = ? ORDER BY name ASC";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $municipality['municipality_code']);
            $stmt->execute();

            $barangays = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $electId = 3;
            $proId = 3;

            foreach($barangays as $barangay){
                $totalHousehold = 0;
                $totalMembers = 0;
                $totalDuplicates = 0;
                $totalVoter = 0;
                $totalNonVoter = 0;
                
                $sql = "SELECT COALESCE(COUNT(hh.id),0) as total_household 
                        FROM tbl_household_hdr hh 
                        WHERE hh.elect_id = ? 
                        AND hh.municipality_no = ? 
                        AND hh.barangay_no = ? ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->bindValue(3, $barangay['brgy_no']);
                $stmt->execute();

                $totalHousehold = $stmt->fetch(\PDO::FETCH_ASSOC)['total_household'];
                

                $sql = "SELECT COALESCE(COUNT(DISTINCT hd.pro_voter_id),0) AS total_member 
                        FROM tbl_household_dtl hd 
                        INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hd.pro_voter_id 
                        WHERE pv.elect_id = ? AND pv.municipality_no = ? AND pv.brgy_no = ? ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->bindValue(3, $barangay['brgy_no']);
                $stmt->execute();

                $totalMembers = $stmt->fetch(\PDO::FETCH_ASSOC)['total_member'];

                $sql = "SELECT COUNT(pv.pro_voter_id) AS exists_count 
                FROM tbl_household_dtl hd INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hd.pro_voter_id 
                WHERE pv.elect_id = ? AND pv.municipality_no = ? AND pv.brgy_no = ? 
                GROUP BY pv.pro_voter_id
                HAVING exists_count > 1";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->bindValue(3, $barangay['brgy_no']);
                $stmt->execute();

                $totalDuplicates = count($stmt->fetchAll(\PDO::FETCH_ASSOC));

                $sql = "SELECT COUNT(pv.pro_voter_id) AS exists_count 
                FROM tbl_household_dtl hd INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hd.pro_voter_id 
                WHERE pv.elect_id = ? AND pv.municipality_no = ? 
                GROUP BY pv.pro_voter_id
                HAVING exists_count > 1 
                ORDER BY exists_count DESC
                LIMIT 1";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->execute();

                $maxDuplicate = $stmt->fetch(\PDO::FETCH_ASSOC)['exists_count'];
                
                $sql = "SELECT COALESCE(count(DISTINCT pv.pro_voter_id),0) AS total_count
                        FROM tbl_household_dtl hd 
                        INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hd.pro_voter_id
                        WHERE pv.is_non_voter = 1 AND pv.elect_id = ? AND pv.municipality_no = ? AND pv.brgy_no = ?  ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->bindValue(3, $barangay['brgy_no']);
                $stmt->execute();

                $totalMemberNonVoter = intval($stmt->fetch(\PDO::FETCH_ASSOC)['total_count']);

                $sql = "SELECT COALESCE(count(DISTINCT pv.pro_voter_id),0) AS total_count
                        FROM tbl_household_dtl hd 
                        INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hd.pro_voter_id
                        WHERE pv.is_non_voter <> 1 AND pv.elect_id = ? AND pv.municipality_no = ? AND pv.brgy_no = ? ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->bindValue(3, $barangay['brgy_no']);
                $stmt->execute();

                $totalMemberVoter = intval($stmt->fetch(\PDO::FETCH_ASSOC)['total_count']);

                $sql = "SELECT COALESCE(count(DISTINCT pv.pro_voter_id),0) AS total_count
                FROM tbl_household_hdr hh 
                INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hh.pro_voter_id
                WHERE pv.is_non_voter = 1 AND pv.elect_id = ? AND pv.municipality_no = ? AND pv.brgy_no = ? ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->bindValue(3, $barangay['brgy_no']);
                $stmt->execute();

                $totalLeaderNonVoter = intval($stmt->fetch(\PDO::FETCH_ASSOC)['total_count']);

                
                $sql = "SELECT COALESCE(count(DISTINCT pv.pro_voter_id),0) AS total_count
                FROM tbl_household_hdr hh 
                INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = hh.pro_voter_id
                WHERE pv.is_non_voter <> 1 AND pv.elect_id = ? AND pv.municipality_no = ? AND pv.brgy_no = ? ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $municipality['municipality_no']);
                $stmt->bindValue(3, $barangay['brgy_no']);
                $stmt->execute();

                $totalLeaderVoter = intval($stmt->fetch(\PDO::FETCH_ASSOC)['total_count']);
                
                $sql = "INSERT INTO tbl_household_summary(
                    elect_id,pro_id,municipality_no,municipality_name,
                    barangay_no, barangay_name, total_household, total_members,
                    total_duplicates, max_duplicate_count, total_member_voter,
                    total_member_non_voter, total_leader_voter, total_leader_non_voter
                )
                VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $electId);
                $stmt->bindValue(2, $proId);
                $stmt->bindValue(3, $municipality['municipality_no']);
                $stmt->bindValue(4, $municipality['name']);
                $stmt->bindValue(5, $barangay['brgy_no']);
                $stmt->bindValue(6, $barangay['name']);
                $stmt->bindValue(7, $totalHousehold);
                $stmt->bindValue(8, $totalMembers);
                $stmt->bindValue(9, $totalDuplicates);
                $stmt->bindValue(10, $maxDuplicate);
                $stmt->bindValue(11, $totalMemberVoter);
                $stmt->bindValue(12, $totalMemberNonVoter);
                $stmt->bindValue(13, $totalLeaderVoter);
                $stmt->bindValue(14, $totalLeaderNonVoter);
                $stmt->execute();   

                // $stmt->bindValue(8, $totalMembers);
                // $stmt->bindValue(9, $totalDuplicates);
                // $stmt->bindValue(10, $maxDuplicate);
                // $stmt->bindValue(11, $totalMemberVoter);
                // $stmt->bindValue(12, $totalMemberNonVoter);
                // $stmt->bindValue(13, $totalLeaderVoter);
                // $stmt->bindValue(14, $totalLeaderNonVoter);

                $data[] = [
                    'municipality_name' => $municipality['name'],
                    'barangay_name' => $barangay['name'],
                    'total_household' => $totalHousehold,
                    'total_members' => $totalMembers,
                    'total_duplicates' => $totalDuplicates,
                    'max_duplicate' => $maxDuplicate,
                    'total_member_non_voter' => $totalMemberNonVoter,
                    'total_member_voter' => $totalMemberVoter,
                    'total_leader_non_voter' => $totalLeaderNonVoter,
                    'total_leader_voter' => $totalLeaderVoter
                ];

            }
        }

    	return new JsonResponse($data);
    }
}
