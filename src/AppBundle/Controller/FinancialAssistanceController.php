<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\FinancialAssistanceHeader;
use AppBundle\Entity\FinancialMedRequirements;
use AppBundle\Entity\FinancialAssistanceDailyClosingHdr;
use AppBundle\Entity\FinancialAssistanceDailyClosingDtl;

/**
* @Route("/fa")
*/

class FinancialAssistanceController extends Controller 
{   
    const STATUS_ACTIVE = 'A';
    const MODULE_MAIN = "FINANCIAL_ASSISTANCE";

	/**
    * @Route("", name="fa_index", options={"main" = true})
    */

    public function indexAction(Request $request)
    {
        //$this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        return $this->render('template/financial-assistance/index.html.twig',['user' => $user]);
    }

     /**
     * @Route("/ajax_select2_fa_hospital",
     *       name="ajax_select2_fa_hospital",
     *       options={ "expose" = true }
     * )f
     * @Method("GET")
     */

    public function ajaxSelect2Hospital(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT hospital_name FROM tbl_fa_hdr h WHERE h.hospital_name LIKE ? ORDER BY h.hospital_name ASC LIMIT 30";
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
     * @Route("/ajax_select2_fa_applicant",
     *       name="ajax_select2_fa_applicant",
     *       options={ "expose" = true }
     * )f
     * @Method("GET")
     */

    public function ajaxSelect2Applicant(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT applicant_name FROM tbl_fa_hdr h WHERE h.applicant_name LIKE ? ORDER BY h.applicant_name ASC LIMIT 30";
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
     * @Route("/ajax_select2_fa_beneficiary",
     *       name="ajax_select2_fa_beneficiary",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Beneficiary(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT beneficiary_name FROM tbl_fa_hdr h WHERE h.beneficiary_name LIKE ? ORDER BY h.beneficiary_name ASC LIMIT 30";
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
     * @Route("/ajax_select2_fa_type_of_assistance",
     *       name="ajax_select2_fa_type_of_assistance",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2TypeOfAssistance(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT type_of_asst FROM tbl_fa_hdr h WHERE h.type_of_asst LIKE ? ORDER BY h.type_of_asst ASC LIMIT 30";
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
     * @Route("/ajax_select2_fa_endorser",
     *       name="ajax_select2_fa_endorser",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Endorser(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT endorsed_by FROM tbl_fa_hdr h WHERE h.endorsed_by LIKE ? ORDER BY h.endorsed_by ASC LIMIT 30";
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
     * @Route("/ajax_select2_fa_receiver",
     *       name="ajax_select2_fa_receiver",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Receiver(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT received_by FROM tbl_fa_hdr h WHERE h.received_by LIKE ? ORDER BY h.received_by ASC LIMIT 30";
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
     * @Route("/ajax_select2_fa_personnel",
     *       name="ajax_select2_fa_personnel",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Personnel(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';
        
        $sql = "SELECT DISTINCT personnel FROM tbl_fa_hdr h WHERE h.personnel LIKE ? ORDER BY h.personnel ASC LIMIT 30";
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
     * @Route("/ajax_select2_fa_office",
     *       name="ajax_select2_fa_office",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Office(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';
        
        $sql = "SELECT DISTINCT releasing_office FROM tbl_fa_hdr h WHERE h.releasing_office LIKE ? ORDER BY h.releasing_office ASC LIMIT 30";
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
    * @Route("/ajax_post_fa_header", 
    * 	name="ajax_post_fa_header",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostFaHeaderAction(Request $request){

        $trnNo = $this->generateTrnNo(2022);

        $entity = new FinancialAssistanceHeader();
        $entity->setTrnNo($trnNo);
    	$entity->setTrnDate($request->get("trnDate"));
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));
        $entity->setApplicantName(strtoupper($request->get('applicantName')));
        $entity->setBeneficiaryName(strtoupper($request->get('beneficiaryName')));
        $entity->setJpmIdNo(strtoupper($request->get('jpmIdNo')));
        $entity->setContactNo(strtoupper($request->get('contactNo')));
        $entity->setTypeOfAsst(strtoupper($request->get('typeOfAsst')));
        $entity->setEndorsedBy(strtoupper($request->get('endorsedBy')));
        $entity->setHospitalName(strtoupper($request->get('hospitalName')));
        $entity->setProjectedAmt($request->get('projectedAmt'));
        $entity->setGrantedAmt($request->get('grantedAmt'));
        $entity->setReceivedBy(strtoupper($request->get('receivedBy')));
        $entity->setReleaseDate($request->get('releaseDate'));
        $entity->setReleasingOffice(strtoupper($request->get('releasingOffice')));
        $entity->setPersonnel(strtoupper($request->get('personnel')));
        $entity->setApplicantProVoterId($request->get('applicantProVoterId'));
        $entity->setIsClosed(0);
        $entity->setRemarks(strtoupper($request->get('remarks')));
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
        
        $requirements = new FinancialMedRequirements(); 
        $requirements->setTrnId($entity->getTrnId());
        $requirements->setTrnNo($trnNo);
        $requirements->setHasReqLetter($request->get('hasReqLetter'));
        $requirements->setHasBrgyClearance($request->get('hasBrgyClearance'));
        $requirements->setHasPatientId($request->get('hasPatientId'));
        $requirements->setHasMedCert($request->get('hasMedCert'));
        $requirements->setHasMedAbst($request->get('hasMedAbst'));
        $requirements->setHasPromisoryNote($request->get('hasPromisoryNote'));
        $requirements->setHasBillStatement($request->get('hasBillStatement'));
        $requirements->setHasPriceQuot($request->get('hasPriceQuot'));
        $requirements->setHasReqOfPhysician($request->get('hasReqOfPhysician'));
        $requirements->setHasReseta($request->get('hasReseta'));
        $requirements->setHasSocialCastReport($request->get('hasSocialCastReport'));
        $requirements->setHasPoliceReport($request->get('hasPoliceReport'));
        $requirements->setHasDeathCert($request->get('hasDeathCert'));

        $requirements->setIsDswdMedical($request->get('isDswdMedical'));
        $requirements->setIsDswdOpd($request->get('isDswdOpd'));
        $requirements->setIsDohMaipMedical($request->get('isDohMaipMedical'));
        $requirements->setIsDohMaipOpd($request->get('isDohMaipOpd'));
    
        
        $em->persist($requirements);
        $em->flush();
    	$em->clear();


    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }

    private function generateTrnNo($year)
    {
        $trnNo = '000001';

        $em = $this->getDoctrine()->getManager();

        // $sql = "SELECT CAST(RIGHT(trn_no ,6) AS UNSIGNED ) AS order_num FROM tbl_fa_hdr
        // WHERE created_at LIKE ? ORDER BY order_num DESC LIMIT 1 ";

        // $stmt = $em->getConnection()->prepare($sql);
        // $stmt->bindValue(1, $year . '%');
        // $stmt->execute();


        $sql = "SELECT CAST(RIGHT(trn_no ,6) AS UNSIGNED ) AS order_num FROM tbl_fa_hdr
         ORDER BY order_num DESC LIMIT 1 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $request = $stmt->fetch();

        if ($request) {
            $trnNo = sprintf("%06d", intval($request['order_num']) + 1);
        }

        return $trnNo;
    }


    /**
     * @Route("/ajax_get_datatable_financial_assistance", name="ajax_get_datatable_financial_assistance", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
	public function ajaxGetDatatableFinancialAssistanceAction(Request $request)
	{	
        $columns = array(
            0 => "h.trn_id",
            1 => "h.trn_no",
            2 => "h.trn_date",
            3 => "h.applicant_name",
            4 => "h.beneficiary_name",
            5 => "h.endorsed_by",
            6 => "h.municipality_no",
            7 => "h.barangay_no"
        );

        $sWhere = "";
    
        $select['h.trn_no'] = $request->get('trnNo');
        $select['h.trn_date'] = $request->get('trnDate');
        $select['h.applicant_name'] = $request->get('applicantName');
        $select['h.beneficiary_name'] = $request->get('beneficiaryName');
        $select['h.endorsed_by'] = $request->get('endorsedBy');
        $select['h.municipality_no'] = $request->get('municipalityNo');
        $select['h.barangay_no'] = $request->get('barangayNo');
        $select['m.name'] = $request->get('municipalityName');
        $select['b.name'] = $request->get('barangayName');

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

        $sql = "SELECT COALESCE(count(h.trn_id),0) FROM tbl_fa_hdr h 
                INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = h.municipality_no
                INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = h.barangay_no 
                WHERE 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.trn_id),0) FROM tbl_fa_hdr h 
                INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = h.municipality_no
                INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = h.barangay_no 
                WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.*, m.name AS municipality_name , b.name AS barangay_name
            FROM tbl_fa_hdr h 
            INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = h.municipality_no
            INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = h.barangay_no 
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
     * @Route("/ajax_get_datatable_financial_assistance_daily_summary", name="ajax_get_datatable_financial_assistance_daily_summary", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
	public function ajaxGetDatatableFinancialAssistanceDailySummaryAction(Request $request)
	{	
        $columns = array(
            0 => "h.id",
            1 => "h.closing_date",
            2 => "h.total_released",
            3 => "h.released_amt",
            4 => "h.total_pending",
            5 => "h.pending_amt",
            6 => "h.created_by",
            7 => "h.created_amt"
        );

        $sWhere = "";
        $select = [];

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

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_fa_daily_closing_hdr h WHERE 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_fa_daily_closing_hdr h WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.* FROM tbl_fa_daily_closing_hdr h 
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
     * @Route("/ajax_get_datatable_financial_assistance_daily_summary_detail", name="ajax_get_datatable_financial_assistance_daily_summary_detail", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
	public function ajaxGetDatatableFinancialAssistanceDailySummaryDetailAction(Request $request)
	{	
        $columns = array(
            0 => "d.id",
            1 => "hh.closing_date",
            2 => "hh.total_released",
            3 => "hh.released_amt",
            4 => "hh.total_pending",
            5 => "hh.pending_amt",
            6 => "hh.created_by",
            7 => "hh.created_amt"
        );

        $sWhere = "";
        
        $id = $request->get('id');
        $select = [];

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

        $sql = "SELECT COALESCE(count(d.id),0) FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN tbl_fa_daily_closing_hdr hh ON hh.id = d.hdr_id
                INNER JOIN tbl_fa_hdr fa ON fa.trn_id = d.trn_id 
                WHERE d.hdr_id = {$id} AND 1 ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(d.id),0) FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN tbl_fa_daily_closing_hdr hh ON hh.id = d.hdr_id
                INNER JOIN tbl_fa_hdr fa ON fa.trn_id = d.trn_id 
                WHERE d.hdr_id = {$id} AND 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT fa.*, m.name AS municipality_name , b.name AS barangay_name,d.id as dtl_id FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN tbl_fa_daily_closing_hdr hh ON hh.id = d.hdr_id
                INNER JOIN tbl_fa_hdr fa ON fa.trn_id = d.trn_id 
                INNER JOIN psw_municipality m ON province_code = 53 AND m.municipality_no = fa.municipality_no
                INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = fa.barangay_no 
                WHERE d.hdr_id = {$id} AND 1 " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";

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
     * @Route("/ajax_get_financial_assistance_full/{trnId}",
     *       name="ajax_get_financial_assistance_full",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetFinancialAssistanceFull($trnId)
    {
        $em = $this->getDoctrine()->getManager();
        
        $sql = "SELECT h.*, r.req_type,r.med_id,r.has_req_letter, r.has_brgy_clearance, r.has_patient_id, r.has_med_cert,
                r.has_med_abst, r.has_promisory_note, r.has_bill_statement, r.has_price_quot, r.has_req_of_physician, r.has_reseta, r.has_social_cast_report ,
                r.has_police_report, r.has_death_cert, r.is_dswd_medical, r.is_dswd_opd, r.is_doh_maip_medical, r.is_doh_maip_opd,
                m.name AS municipality_name, 
                b.name AS barangay_name
                FROM tbl_fa_hdr h
                INNER JOIN tbl_fa_med_req r ON h.trn_id = r.trn_id 
                INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = h.municipality_no 
                INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = h.barangay_no
                WHERE h.trn_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$trnId);
        $stmt->execute();

        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse($data);
    }

    /**
    * @Route("/ajax_patch_financial_assistance_full/{trnId}", 
    * 	name="ajax_patch_financial_assistance_full",
    *	options={"expose" = true}
    * )
    * @Method("PATCH")
    */

    public function ajaxPatchFinancialAssistanceAction(Request $request,$trnId){
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository("AppBundle:FinancialAssistanceHeader")->find($trnId);

        if(!$entity)
            return new JsonResponse(null,404);

        $eventDate = empty($request->get("eventDate")) ? null : new \DateTime($request->get("eventDate"));
        
        $entity->setTrnDate($request->get("trnDate"));
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));
        $entity->setApplicantName(strtoupper($request->get('applicantName')));
        $entity->setApplicantProVoterId($request->get('applicantProVoterId'));
        $entity->setBeneficiaryName(strtoupper($request->get('beneficiaryName')));
        $entity->setJpmIdNo(strtoupper($request->get('jpmIdNo')));
        $entity->setContactNo(strtoupper($request->get('contactNo')));
        $entity->setTypeOfAsst(strtoupper($request->get('typeOfAsst')));
        $entity->setEndorsedBy(strtoupper($request->get('endorsedBy')));
        $entity->setProjectedAmt(empty($request->get('projectedAmt')) ? 0 : $request->get('projectedAmt'));
        $entity->setGrantedAmt(empty($request->get('grantedAmt'))? 0 : $request->get('grantedAmt'));
        $entity->setReceivedBy(strtoupper($request->get('receivedBy')));
        $entity->setReleaseDate(strtoupper($request->get('releaseDate')));
        $entity->setReleasingOffice(strtoupper($request->get('releasingOffice')));
        $entity->setPersonnel(strtoupper($request->get('personnel')));
        $entity->setHospitalName(strtoupper($request->get('hospitalName')));
        $entity->setRemarks(strtoupper($request->get('remarks')));

        if(!empty($request->get('releaseDate'))  && $request->get('grantedAmt') > 0 ){
            $entity->setIsReleased(1);
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
        
        $em->flush();
    	$em->clear();

        $requirements = $em->getRepository("AppBundle:FinancialMedRequirements")->findOneBy(['trnId' => $entity->getTrnId()]);

        if(!$requirements)
            return new JsonResponse(['message' => 'Requirement not found.'],404);
      
        $requirements->setHasReqLetter($request->get('hasReqLetter'));
        $requirements->setHasBrgyClearance($request->get('hasBrgyClearance'));
        $requirements->setHasPatientId($request->get('hasPatientId'));
        $requirements->setHasMedCert($request->get('hasMedCert'));
        $requirements->setHasMedAbst($request->get('hasMedAbst'));
        $requirements->setHasPromisoryNote($request->get('hasPromisoryNote'));
        $requirements->setHasBillStatement($request->get('hasBillStatement'));
        $requirements->setHasPriceQuot($request->get('hasPriceQuot'));
        $requirements->setHasReqOfPhysician($request->get('hasReqOfPhysician'));
        $requirements->setHasReseta($request->get('hasReseta'));
        $requirements->setHasSocialCastReport($request->get('hasSocialCastReport'));
        $requirements->setHasPoliceReport($request->get('hasPoliceReport'));
        $requirements->setHasDeathCert($request->get('hasDeathCert'));

        $requirements->setIsDswdMedical($request->get('isDswdMedical'));
        $requirements->setIsDswdOpd($request->get('isDswdOpd'));
        $requirements->setIsDohMaipMedical($request->get('isDohMaipMedical'));
        $requirements->setIsDohMaipOpd($request->get('isDohMaipOpd'));
    
        $em->flush();
    	$em->clear();


    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }

    /**
    * @Route("/ajax_delete_financial_assistance/{trnId}", 
    * 	name="ajax_delete_financial_assistance",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteFinancialAssistanceAction($trnId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:FinancialAssistanceHeader")->find($trnId);

        if(!$entity)
            return new JsonResponse(null,404);

        $entities = $em->getRepository('AppBundle:FinancialMedRequirements')->findBy([
            'trnId' => $entity->getTrnId()
        ]);

        foreach($entities as $detail){
            $em->remove($detail);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null,200);
    }

    /**
    * @Route("/ajax_get_unclosed_transactions", 
    *   name="ajax_get_unclosed_transactions",
    *   options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetFieldUploadNoId(Request $request){
        $em = $this->getDoctrine()->getManager();
        
        $sql = "SELECT fa.* , m.name AS municipality_name, b.name AS barangay_name FROM tbl_fa_hdr fa 
                INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = fa.municipality_no
                INNER JOIN psw_barangay b ON b.municipality_code = m.municipality_code AND b.brgy_no = fa.barangay_no 
                WHERE fa.is_released = 1 AND fa.granted_amt > 0  AND (fa.is_closed <> 1 OR fa.is_closed = 0) ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        
        $data = array();
        
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        $em->clear();

        return new JsonResponse($data);
    }
    

    /**
    * @Route("/ajax_post_close_transactions",
    *     name="ajax_post_close_transactions",
    *     options={"expose" = true})
    *
    * @Method("POST")
    */

    public function postCloseTransactionsAction(Request $request){
        
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $hdr = new FinancialAssistanceDailyClosingHdr();
        
        $hdr->setClosingDate($request->get('closingDate'));
        $hdr->setReleasedAmt(0);
        $hdr->setPendingAmt(0);
        $hdr->setTotalReleased(0);
        $hdr->setTotalPending(0);
        $hdr->setCreatedAt(new \DateTime());
        $hdr->setCreatedBy($user->getUsername());
        $hdr->setStatus('A');

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

        $transactions = $request->get('profiles');

        foreach($transactions as $trnId){
            $trn = $em->getRepository('AppBundle:FinancialAssistanceHeader')
                          ->findOneBy(['trnId' => $trnId]);
            $medReq =  $em->getRepository('AppBundle:FinancialMedRequirements')
                          ->findOneBy(['trnId' => $trnId]);

            $dtl = new FinancialAssistanceDailyClosingDtl();
            $dtl->setHdrId($hdr->getId());
            $dtl->setTrnId($trn->getTrnId());
            $dtl->setTrnNo($trn->getTrnNo());
            $dtl->setCreatedAt(new \DateTime());
            $dtl->setCreatedBy($user->getUsername());
            $dtl->setStatus('A');

            $dtl->setMunicipalityNo($trn->getMunicipalityNo());
            $dtl->setBarangayNo($trn->getBarangayNo());
            $dtl->setApplicantName($trn->getApplicantName());
            $dtl->setBeneficiaryName($trn->getBeneficiaryName());
            $dtl->setJpmIdNo($trn->getJpmIdNo());
            $dtl->setContactNo($trn->getContactNo());
            $dtl->setTypeOfAsst($trn->getTypeOfAsst());
            $dtl->setEndorsedBy($trn->getEndorsedBy());
            $dtl->setHospitalName($trn->getHospitalName());
            $dtl->setProjectedAmt($trn->getProjectedAmt());
            $dtl->setGrantedAmt($trn->getGrantedAmt());
            $dtl->setReceivedBy($trn->getReceivedBy());
            $dtl->setReleaseDate($trn->getReleaseDate());
            $dtl->setIsReleased($trn->getIsReleased());
            $dtl->setReleasingOffice($trn->getReleasingOffice());
            $dtl->setPersonnel($trn->getPersonnel());
            $dtl->setApplicantProVoterId($trn->getApplicantProVoterId());
            $dtl->setBeneficiaryProVoterId($trn->getBeneficiaryProVoterId());
            $dtl->setClosedDate($hdr->getClosingDate());
            $dtl->setIsClosed(1);

            $dtl->setReqType($medReq->getReqType());
            $dtl->setIsDswdMedical($medReq->getReqType());
            $dtl->setIsDswdOpd($medReq->getReqType());
            $dtl->setIsDohMaipMedical($medReq->getReqType());
            $dtl->setIsDohMaipOpd($medReq->getReqType());

            $dtl->setIsDswdMedical($medReq->getIsDswdMedical());
            $dtl->setIsDswdOpd($medReq->getIsDswdOpd());
            $dtl->setIsDohMaipMedical($medReq->getIsDohMaipMedical());
            $dtl->setIsDohMaipOpd($medReq->getIsDohMaipOpd());
        
            $trn->setIsClosed(1);
            $trn->setClosedDate($hdr->getClosingDate());

            $em->persist($dtl);
            $em->flush();
        }
        

        $sql = "SELECT COUNT(*) AS total_released_transaction FROM tbl_fa_hdr WHERE is_closed = 1 AND closed_date = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$hdr->getClosingDate());
        $stmt->execute();
        
        $totalReleasedTransaction = $stmt->fetchColumn();

        $sql = "SELECT SUM(granted_amt) AS  total_released_amt FROM tbl_fa_hdr WHERE is_closed = 1 AND closed_date = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$hdr->getClosingDate());
        $stmt->execute();
        
        $totalReleasedAmt = $stmt->fetchColumn();


        $sql = "SELECT COUNT(*) AS total_pending_transaction FROM tbl_fa_hdr WHERE (is_closed <> 1 OR is_closed = 0 ) AND (is_released = 0 OR is_released <> 1 ) ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        
        $totalPendingTransaction = $stmt->fetchColumn();


        $sql = "SELECT COALESCE(SUM(projected_amt),0) AS total_pending_amt FROM tbl_fa_hdr WHERE (is_closed <> 1 OR is_closed = 0 ) AND (is_released = 0 OR is_released <> 1 )  ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        
        $totalPendingAmt = $stmt->fetchColumn();

        $hdr->setTotalReleased($totalReleasedTransaction);
        $hdr->setReleasedAmt($totalReleasedAmt);
        $hdr->setTotalPending($totalPendingTransaction);
        $hdr->setPendingAmt($totalPendingAmt);
        
        $em->flush();
        $em->clear();

        $serializer = $this->get("serializer");

        return new JsonResponse($serializer->normalize($hdr));
    }
    
    /**
    * @Route("/ajax_delete_financial_assistance_daily_summary/{id}", 
    * 	name="ajax_delete_financial_assistance_daily_summary",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteFinancialAssistanceDailySummaryAction($id){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:FinancialAssistanceDailyClosingHdr")->find($id);

        if(!$entity)
            return new JsonResponse(null,404);

        $entities = $em->getRepository('AppBundle:FinancialAssistanceDailyClosingDtl')->findBy([
            'hdrId' => $entity->getId()
        ]);

        foreach($entities as $detail){
            $hdr = $em->getRepository("AppBundle:FinancialAssistanceHeader")->find($detail->getTrnId());
            $hdr->setClosedDate("");
            $hdr->setIsClosed(0);
            $em->remove($detail);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null,200);
    }

    /**
    * @Route("/ajax_delete_financial_assistance_daily_summary_detail/{id}", 
    * 	name="ajax_delete_financial_assistance_daily_summary_detail",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */

    public function ajaxDeleteFinancialAssistanceDailySummaryDetailAction($id){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:FinancialAssistanceDailyClosingDtl")->find($id);

        if(!$entity)
            return new JsonResponse(null,404);

        $hdr = $em->getRepository("AppBundle:FinancialAssistanceHeader")->find($entity->getTrnId());
        $hdr->setClosedDate("");
        $hdr->setIsClosed(0);

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null,200);
    }

      /**
     * @Route("/ajax_get_datatable_financial_assistance_daily_summary_report", name="ajax_get_datatable_financial_assistance_daily_summary_report", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
	public function ajaxGetDatatableFinancialAssistanceDailySummaryReportAction(Request $request)
	{	
        $columns = array(
            0 => "h.id",
            1 => "h.closing_date",
            2 => "h.total_released",
            3 => "h.released_amt",
            4 => "h.total_pending",
            5 => "h.pending_amt",
            6 => "h.created_by",
            7 => "h.created_amt"
        );

        $sWhere = "";
        $select = [];

        $start_date = $request->get('startDate');
        $end_date = $request->get('endDate');

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

        $sql = "SELECT COALESCE(count(h.id),0) 
                FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN tbl_fa_daily_closing_hdr h ON h.id = d.hdr_id 
                INNER JOIN tbl_fa_hdr fa ON d.trn_id = fa.trn_id 
                INNER JOIN tbl_fa_med_req fr ON fr.trn_id = fa.trn_id
                WHERE h.closing_date >=  '{$start_date}'  AND h.closing_date <=  '{$end_date}'
                GROUP BY h.id  ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN tbl_fa_daily_closing_hdr h ON h.id = d.hdr_id 
                INNER JOIN tbl_fa_hdr fa ON d.trn_id = fa.trn_id 
                INNER JOIN tbl_fa_med_req fr ON fr.trn_id = fa.trn_id
                WHERE h.closing_date >=  {$start_date}  AND h.closing_date <=  {$end_date}  ";

        $sql .= $sWhere . ' GROUP BY h.id ' . $sOrder  ;

        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.closing_date ,
                SUM(fa.granted_amt) total_granted_amt, 
                COALESCE(COUNT(CASE WHEN fr.is_dswd_medical = 1 THEN 1 END), 0) AS total_dswd_medical,
                COALESCE(COUNT(CASE WHEN fr.is_dswd_opd = 1 THEN 1 END), 0) AS total_dswd_opd,
                COALESCE(COUNT(CASE WHEN fr.is_doh_maip_medical = 1 THEN 1 END), 0) AS total_doh_maip_medical,
                COALESCE(COUNT(CASE WHEN fr.is_doh_maip_opd = 1 THEN 1 END), 0) AS total_doh_maip_opd,
                (SELECT COUNT(DISTINCT beneficiary_name) FROM tbl_fa_hdr ffa WHERE ffa.closed_date = h.closing_date ) AS total_beneficiary
                FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN tbl_fa_daily_closing_hdr h ON h.id = d.hdr_id 
                INNER JOIN tbl_fa_hdr fa ON d.trn_id = fa.trn_id 
                INNER JOIN tbl_fa_med_req fr ON fr.trn_id = fa.trn_id
                WHERE h.closing_date >=  '{$start_date}'  AND h.closing_date <=  '{$end_date}'   " . $sWhere . ' GROUP BY h.id ' . $sOrder . " LIMIT {$length} OFFSET {$start}";

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
     * @Route("/ajax_get_datatable_financial_assistance_municipality_summary_report", name="ajax_get_datatable_financial_assistance_municipality_summary_report", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
	public function ajaxGetDatatableFinancialAssistanceMunicipalitySummaryReportAction(Request $request)
	{	
        $columns = array(
            0 => "h.id",
            1 => "h.closing_date",
            2 => "h.total_released",
            3 => "h.released_amt",
            4 => "h.total_pending",
            5 => "h.pending_amt",
            6 => "h.created_by",
            7 => "h.created_amt"
        );

        $sWhere = "";
        $select = [];

        $start_date = $request->get('startDate');
        $end_date = $request->get('endDate');

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

        $sql = "SELECT COALESCE(count(h.id),0) 
                FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN tbl_fa_daily_closing_hdr h ON h.id = d.hdr_id 
                INNER JOIN tbl_fa_hdr fa ON d.trn_id = fa.trn_id 
                INNER JOIN tbl_fa_med_req fr ON fr.trn_id = fa.trn_id
                INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = fa.municipality_no 
                WHERE h.closing_date >=  '{$start_date}'  AND h.closing_date <=  '{$end_date}'
                GROUP BY h.id  ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) 
                FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN tbl_fa_daily_closing_hdr h ON h.id = d.hdr_id 
                INNER JOIN tbl_fa_hdr fa ON d.trn_id = fa.trn_id 
                INNER JOIN tbl_fa_med_req fr ON fr.trn_id = fa.trn_id
                INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = fa.municipality_no 
                WHERE h.closing_date >=  '{$start_date}'  AND h.closing_date <=  '{$end_date}'  ";

        $sql .= $sWhere . ' GROUP BY m.name ' . $sOrder  ;

        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.closing_date ,
                SUM(fa.granted_amt) total_granted_amt, 
                COALESCE(COUNT(CASE WHEN fr.is_dswd_medical = 1 THEN 1 END), 0) AS total_dswd_medical,
                COALESCE(COUNT(CASE WHEN fr.is_dswd_opd = 1 THEN 1 END), 0) AS total_dswd_opd,
                COALESCE(COUNT(CASE WHEN fr.is_doh_maip_medical = 1 THEN 1 END), 0) AS total_doh_maip_medical,
                COALESCE(COUNT(CASE WHEN fr.is_doh_maip_opd = 1 THEN 1 END), 0) AS total_doh_maip_opd,
                (SELECT COUNT(DISTINCT beneficiary_name) FROM tbl_fa_hdr ffa WHERE ffa.closed_date >= '{$start_date}'  AND ffa.closed_date <=  '{$end_date}'    AND ffa.municipality_no = m.municipality_no ) AS total_beneficiary,
                m.name AS municipality_name 
                FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN tbl_fa_daily_closing_hdr h ON h.id = d.hdr_id 
                INNER JOIN tbl_fa_hdr fa ON d.trn_id = fa.trn_id 
                INNER JOIN tbl_fa_med_req fr ON fr.trn_id = fa.trn_id
                INNER JOIN psw_municipality m ON m.province_code = 53 AND m.municipality_no = fa.municipality_no 
                WHERE h.closing_date >=  '{$start_date}'  AND h.closing_date <=  '{$end_date}'   " . $sWhere . ' GROUP BY m.name ' . $sOrder . " LIMIT {$length} OFFSET {$start}";

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
     * @Route("/ajax_get_datatable_financial_assistance_monthly_summary_report", name="ajax_get_datatable_financial_assistance_monthly_summary_report", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
	public function ajaxGetDatatableFinancialAssistanceMonthlySummaryReportAction(Request $request)
	{	
        $columns = array(
            0 => "h.id",
            1 => "h.closing_date",
            2 => "h.total_released",
            3 => "h.released_amt",
            4 => "h.total_pending",
            5 => "h.pending_amt",
            6 => "h.created_by",
            7 => "h.created_amt"
        );

        $sWhere = "";
        $select = [];

        $start_date = $request->get('startDate');
        $end_date = $request->get('endDate');

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

        $sql = "SELECT COALESCE(count(h.id),0) 
                FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN tbl_fa_daily_closing_hdr h ON h.id = d.hdr_id 
                INNER JOIN tbl_fa_hdr fa ON d.trn_id = fa.trn_id 
                INNER JOIN tbl_fa_med_req fr ON fr.trn_id = fa.trn_id 
                WHERE h.closing_date >=  '{$start_date}'  AND h.closing_date <=  '{$end_date}'
                GROUP BY YEAR(fa.closed_date), MONTH(fa.closed_date) ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) 
                FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN tbl_fa_daily_closing_hdr h ON h.id = d.hdr_id 
                INNER JOIN tbl_fa_hdr fa ON d.trn_id = fa.trn_id 
                INNER JOIN tbl_fa_med_req fr ON fr.trn_id = fa.trn_id 
                WHERE h.closing_date >=  '{$start_date}'  AND h.closing_date <=  '{$end_date}'  ";

        $sql .= $sWhere . ' GROUP BY YEAR(fa.closed_date), MONTH(fa.closed_date) ' . $sOrder  ;

        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT 
                h.closing_date,
                SUM(fa.granted_amt) total_granted_amt, 
                MONTHNAME(fa.closed_date) as month_name, YEAR(fa.closed_date) as  year_name,
                COALESCE(COUNT(CASE WHEN fr.is_dswd_medical = 1 THEN 1 END), 0) AS total_dswd_medical,
                COALESCE(COUNT(CASE WHEN fr.is_dswd_opd = 1 THEN 1 END), 0) AS total_dswd_opd,
                COALESCE(COUNT(CASE WHEN fr.is_doh_maip_medical = 1 THEN 1 END), 0) AS total_doh_maip_medical,
                COALESCE(COUNT(CASE WHEN fr.is_doh_maip_opd = 1 THEN 1 END), 0) AS total_doh_maip_opd,
                (SELECT COUNT(DISTINCT beneficiary_name) FROM tbl_fa_hdr ffa WHERE YEAR(ffa.closed_date) = YEAR(fa.closed_date) AND MONTH(ffa.closed_date) =  MONTH(fa.closed_date) ) AS total_beneficiary 
                FROM tbl_fa_daily_closing_dtl d 
                INNER JOIN tbl_fa_daily_closing_hdr h ON h.id = d.hdr_id 
                INNER JOIN tbl_fa_hdr fa ON d.trn_id = fa.trn_id 
                INNER JOIN tbl_fa_med_req fr ON fr.trn_id = fa.trn_id 
                WHERE h.closing_date >=  '{$start_date}'  AND h.closing_date <=  '{$end_date}'   " . $sWhere . ' GROUP BY YEAR(fa.closed_date), MONTH(fa.closed_date) ' . $sOrder . " LIMIT {$length} OFFSET {$start}";

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
     * @Route("/ajax_select2_fa_unposted_closed_dates",
     *       name="ajax_select2_fa_unposted_closed_dates",
     *       options={ "expose" = true }
     * )f
     * @Method("GET")
     */

    public function ajaxSelect2UnpostedClosedDates(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT h.id , h.closing_date , h.total_released FROM tbl_fa_daily_closing_hdr h WHERE h.is_posted <> 1 ORDER BY h.closing_date ASC LIMIT 30 ";
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
    * @Route("/ajax_get_unposted_transactions/{hdrId}", 
    *   name="ajax_get_unposted_transactions",
    *   options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetUnpostedTransactions(Request $request,$hdrId){
        $em = $this->getDoctrine()->getManager();
        
        $sql = " SELECT d.*, m.name AS municipality_name, b.name AS barangay_name FROM tbl_fa_daily_closing_dtl d 
                 INNER JOIN tbl_fa_daily_closing_hdr h 
                 ON  d.hdr_id = h.id 
                 INNER JOIN psw_municipality m 
                 ON m.province_code = 53 AND m.municipality_no = d.municipality_no
                 INNER JOIN psw_barangay b 
                 ON b.municipality_code = m.municipality_code AND b.brgy_no = d.barangay_no  
                 WHERE h.is_posted <> 1 AND h.id = ?  ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$hdrId);
        $stmt->execute();
        
        $data = array();
        
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        $em->clear();

        return new JsonResponse($data);
    }
    
    /**
    * @Route("/ajax_post_send_posted_transactions",
    *     name="ajax_post_send_posted_transactions",
    *     options={"expose" = true})
    *
    * @Method("POST")
    */

    public function postSendPostedTransactionsAction(Request $request){
        
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $hdr = new FinancialAssistanceDailyClosingHdr();
        
        $hdr->setClosingDate($request->get('closingDate'));
        $hdr->setReleasedAmt(0);
        $hdr->setPendingAmt(0);
        $hdr->setTotalReleased(0);
        $hdr->setTotalPending(0);
        $hdr->setCreatedAt(new \DateTime());
        $hdr->setCreatedBy($user->getUsername());
        $hdr->setStatus('A');

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

        $transactions = $request->get('profiles');

        foreach($transactions as $trnId){
            $trn = $em->getRepository('AppBundle:FinancialAssistanceHeader')
                          ->findOneBy(['trnId' => $trnId]);
            $medReq =  $em->getRepository('AppBundle:FinancialMedRequirements')
                          ->findOneBy(['trnId' => $trnId]);

            $dtl = new FinancialAssistanceDailyClosingDtl();
            $dtl->setHdrId($hdr->getId());
            $dtl->setTrnId($trn->getTrnId());
            $dtl->setTrnNo($trn->getTrnNo());
            $dtl->setCreatedAt(new \DateTime());
            $dtl->setCreatedBy($user->getUsername());
            $dtl->setStatus('A');

            $dtl->setMunicipalityNo($trn->getMunicipalityNo());
            $dtl->setBarangayNo($trn->getBarangayNo());
            $dtl->setApplicantName($trn->getApplicantName());
            $dtl->setBeneficiaryName($trn->getBeneficiaryName());
            $dtl->setJpmIdNo($trn->getJpmIdNo());
            $dtl->setContactNo($trn->getContactNo());
            $dtl->setTypeOfAsst($trn->getTypeOfAsst());
            $dtl->setEndorsedBy($trn->getEndorsedBy());
            $dtl->setHospitalName($trn->getHospitalName());
            $dtl->setProjectedAmt($trn->getProjectedAmt());
            $dtl->setGrantedAmt($trn->getGrantedAmt());
            $dtl->setReceivedBy($trn->getReceivedBy());
            $dtl->setReleaseDate($trn->getReleaseDate());
            $dtl->setIsReleased($trn->getIsReleased());
            $dtl->setReleasingOffice($trn->getReleasingOffice());
            $dtl->setPersonnel($trn->getPersonnel());
            $dtl->setApplicantProVoterId($trn->getApplicantProVoterId());
            $dtl->setBeneficiaryProVoterId($trn->getBeneficiaryProVoterId());
            $dtl->setClosedDate($hdr->getClosingDate());
            $dtl->setIsClosed(1);

            $dtl->setReqType($medReq->getReqType());
            $dtl->setIsDswdMedical($medReq->getReqType());
            $dtl->setIsDswdOpd($medReq->getReqType());
            $dtl->setIsDohMaipMedical($medReq->getReqType());
            $dtl->setIsDohMaipOpd($medReq->getReqType());

            $dtl->setIsDswdMedical($medReq->getIsDswdMedical());
            $dtl->setIsDswdOpd($medReq->getIsDswdOpd());
            $dtl->setIsDohMaipMedical($medReq->getIsDohMaipMedical());
            $dtl->setIsDohMaipOpd($medReq->getIsDohMaipOpd());
        
            $trn->setIsClosed(1);
            $trn->setClosedDate($hdr->getClosingDate());

            $em->persist($dtl);
            $em->flush();
        }
        

        $sql = "SELECT COUNT(*) AS total_released_transaction FROM tbl_fa_hdr WHERE is_closed = 1 AND closed_date = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$hdr->getClosingDate());
        $stmt->execute();
        
        $totalReleasedTransaction = $stmt->fetchColumn();

        $sql = "SELECT SUM(granted_amt) AS  total_released_amt FROM tbl_fa_hdr WHERE is_closed = 1 AND closed_date = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$hdr->getClosingDate());
        $stmt->execute();
        
        $totalReleasedAmt = $stmt->fetchColumn();


        $sql = "SELECT COUNT(*) AS total_pending_transaction FROM tbl_fa_hdr WHERE (is_closed <> 1 OR is_closed = 0 ) AND (is_released = 0 OR is_released <> 1 ) ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        
        $totalPendingTransaction = $stmt->fetchColumn();


        $sql = "SELECT COALESCE(SUM(projected_amt),0) AS total_pending_amt FROM tbl_fa_hdr WHERE (is_closed <> 1 OR is_closed = 0 ) AND (is_released = 0 OR is_released <> 1 )  ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        
        $totalPendingAmt = $stmt->fetchColumn();

        $hdr->setTotalReleased($totalReleasedTransaction);
        $hdr->setReleasedAmt($totalReleasedAmt);
        $hdr->setTotalPending($totalPendingTransaction);
        $hdr->setPendingAmt($totalPendingAmt);
        
        $em->flush();
        $em->clear();

        $serializer = $this->get("serializer");

        return new JsonResponse($serializer->normalize($hdr));
    }
    
}
