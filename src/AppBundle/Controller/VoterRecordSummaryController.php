<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use AppBundle\Entity\Voter;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Common\Type;
/**
* @Route("/voter-summary")
*/

class VoterRecordSummaryController extends Controller 
{
    const STATUS_ACTIVE = 'A';
    const STATUS_PENDING = 'PEN';
    const STATUS_INACTIVE = 'I';
    const MODULE_MAIN = "VOTER_SUMMARY";

	/**
    * @Route("", name="voter_record_summary_index", options={"main" = true })
    */

    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        return $this->render('template/voter-record-summary/index.html.twig',['user' => $user]);
    }
    
    /**
    * @Route("/ajax_get_province_data_summary", 
    *       name="ajax_get_province_data_summary",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetProvinceDataSummary(Request $request){
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $provinceCode  =  empty($request->get("provinceCode")) ? 53  : $request->get("provinceCode");
        $electId = empty($request->get("electId")) ? null : $request->get("electId");
        $proId = empty($request->get("proId")) ? null : $request->get("proId");

        $sql = "SELECT m.*,
        (SELECT coalesce( SUM(s.total_voters),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_voters,
        (SELECT coalesce( count(DISTINCT s.brgy_no),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_barangays,
        (SELECT coalesce( count(s.sum_id),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_precincts,
        (SELECT coalesce( SUM(s.total_recruited),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_recruits,
        (SELECT coalesce( SUM(s.total_leaders),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_leaders,
        (SELECT coalesce( SUM(s.total_members),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_members,
        (SELECT coalesce( SUM(s.total_voted),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_voted,
        (SELECT coalesce( SUM(s.total_voted_recruits),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_voted_recruits,
        (SELECT s.updated_at FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ORDER BY s.updated_at DESC LIMIT 1) as updated_at
        FROM  psw_municipality m 
        WHERE m.province_code = ? ";
        
        $accessFilter = "";        
        
        if(!$user->getIsAdmin()){
            $accessFilter = $this->getMunicipalityAccessFilter($user->getId());
        }
        
        $sql .= $accessFilter . " ORDER BY m.name ASC";
        
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$electId);
        $stmt->bindValue(3,$proId);
        $stmt->bindValue(4,$provinceCode);
        $stmt->bindValue(5,$electId);
        $stmt->bindValue(6,$proId);
        $stmt->bindValue(7,$provinceCode);
        $stmt->bindValue(8,$electId);
        $stmt->bindValue(9,$proId);
        $stmt->bindValue(10,$provinceCode);
        $stmt->bindValue(11,$electId);
        $stmt->bindValue(12,$proId);
        $stmt->bindValue(13,$provinceCode);
        $stmt->bindValue(14,$electId);
        $stmt->bindValue(15,$proId);
        $stmt->bindValue(16,$provinceCode);
        $stmt->bindValue(17,$electId);
        $stmt->bindValue(18,$proId);
        $stmt->bindValue(19,$provinceCode);
        $stmt->bindValue(20,$electId);
        $stmt->bindValue(21,$proId);
        $stmt->bindValue(22,$provinceCode);
        $stmt->bindValue(23,$electId);
        $stmt->bindValue(24,$proId);
        $stmt->bindValue(25,$provinceCode);
        $stmt->bindValue(26,$electId);
        $stmt->bindValue(27,$proId);
        $stmt->bindValue(28,$provinceCode);
        $stmt->execute();

        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }
        
        return new JsonResponse($data);
    }

    /**
    * @Route("/ajax_get_municipality_data_summary", 
    *       name="ajax_get_municipality_data_summary",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetMunicipalityDataSummary(Request $request){
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $electId = empty($request->get("electId")) ? null : $request->get("electId");
        $proId = empty($request->get("proId")) ? null : $request->get("proId");
        $provinceCode = empty($request->get("provinceCode")) ? 53 : $request->get('provinceCode');
        $municipalityNo = $request->get("municipalityNo");
    
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT b.*,
        (SELECT COALESCE(SUM(s.total_voters),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_voters,
        (SELECT COALESCE(COUNT(DISTINCT s.precinct_no),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ? AND s.elect_id = ?  AND s.pro_id = ? ) as total_precincts,
        (SELECT COALESCE(SUM(s.total_recruited),0)  FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ? AND s.elect_id = ?  AND s.pro_id = ? ) as total_recruits,
        (SELECT COALESCE(SUM(s.total_leaders),0)  FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_leaders,
        (SELECT COALESCE(SUM(s.total_members),0)  FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_members,
        (SELECT COALESCE(SUM(s.total_voted),0)  FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_voted,
        (SELECT COALESCE(SUM(s.total_voted_recruits),0)  FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ? AND s.elect_id = ?  AND s.pro_id = ? ) as total_voted_recruits,
        (SELECT COALESCE(SUM(s.total_has_cellphone),0)  FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_has_cellphone,
        (SELECT s.updated_at  FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ?  AND s.elect_id = ? AND s.pro_id = ?  ORDER BY s.updated_at DESC LIMIT 1) as updated_at
        FROM  psw_barangay b INNER JOIN psw_municipality m ON m.municipality_code = b.municipality_code
        WHERE b.municipality_code = ? ";
        
        $accessFilter  = "";

        if(!$user->getIsAdmin() && $user->getStrictAccess()){
            $accessFilter = $this->getBarangayAccessFilter($user->getId(),$municipalityNo);
        }
      
        $sql .= $accessFilter . " ORDER BY b.name ASC";
        
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $electId);
        $stmt->bindValue(3, $proId);
        $stmt->bindValue(4, $provinceCode);
        $stmt->bindValue(5, $electId);
        $stmt->bindValue(6, $proId);
        $stmt->bindValue(7, $provinceCode);
        $stmt->bindValue(8, $electId);
        $stmt->bindValue(9, $proId);
        $stmt->bindValue(10, $provinceCode);
        $stmt->bindValue(11, $electId);
        $stmt->bindValue(12, $proId);
        $stmt->bindValue(13, $provinceCode);
        $stmt->bindValue(14, $electId);
        $stmt->bindValue(15, $proId);
        $stmt->bindValue(16, $provinceCode);
        $stmt->bindValue(17, $electId);
        $stmt->bindValue(18, $proId);
        $stmt->bindValue(19, $provinceCode);
        $stmt->bindValue(20, $electId);
        $stmt->bindValue(21, $proId);
        $stmt->bindValue(22, $provinceCode);
        $stmt->bindValue(23, $electId);
        $stmt->bindValue(24, $proId);
        $stmt->bindValue(25, $provinceCode);
        $stmt->bindValue(26, $electId);
        $stmt->bindValue(27, $proId);
        $stmt->bindValue(28, $provinceCode . $municipalityNo);
        $stmt->execute();

        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }
                
        return new JsonResponse($data);
    }

    /**
    * @Route("/ajax_get_barangay_data_summary", 
    *       name="ajax_get_barangay_data_summary",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetBarangayDataSummary(Request $request){
        $electId = $request->get("electId");
        $proId = $request->get("proId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT s.* ,s.total_recruited as total_recruits FROM tbl_voter_summary s WHERE s.province_code = ? AND s.municipality_no = ? AND s.brgy_no = ? AND s.elect_id = ? AND s.pro_id = ? GROUP BY s.precinct_no ASC";
        
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->bindValue(3,$brgyNo);
        $stmt->bindValue(4,$electId);
        $stmt->bindValue(5,$proId);
        
        $stmt->execute();
        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        return new JsonResponse($data);
    }

    /**
    * @Route("/ajax_get_province/{provinceCode}", 
    *       name="ajax_get_province",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetProvince($provinceCode){
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_province WHERE province_code  = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->execute();

        $row  = $stmt->fetch(\PDO::FETCH_ASSOC);

        if(!$row)
            return new JsonResponse(null,404);
        
        return new JsonResponse($row);
    }

    /**
    * @Route("/ajax_get_municipality/{provinceCode}/{municipalityNo}", 
    *       name="ajax_get_municipality",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetMunicipality($provinceCode, $municipalityNo){
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_municipality WHERE province_code = ? AND municipality_no  = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->execute();

        $row  = $stmt->fetch(\PDO::FETCH_ASSOC);

        if(!$row)
            return new JsonResponse(null,404);
        
        return new JsonResponse($row);
    }


       /**
    * @Route("/ajax_get_municipality_alt/{name}", 
    *       name="ajax_get_municipality_alt",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetMunicipalityAlt($name){
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_municipality WHERE province_code = ? AND name like ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,53);
        $stmt->bindValue(2, '%' . $name . '%');
        $stmt->execute();

        $row  = $stmt->fetch(\PDO::FETCH_ASSOC);

        if(!$row)
            return new JsonResponse(null,404);
        
        return new JsonResponse($row);
    }


    /**
    * @Route("/ajax_get_barangay/{provinceCode}/{municipalityNo}/{brgyNo}", 
    *       name="ajax_get_barangay",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetBarangay($provinceCode,$municipalityNo,$brgyNo){
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_barangay WHERE municipality_code  = ? AND brgy_no = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode . $municipalityNo);
        $stmt->bindValue(2,$brgyNo);
        $stmt->execute();

        $row  = $stmt->fetch(\PDO::FETCH_ASSOC);

        if(!$row)
            return new JsonResponse(null,404);
        
        return new JsonResponse($row);
    }

   

    private function getRecordAccessFilter($userId){

        $em = $this->getDoctrine()->getManager();
        $currentDate = date('Y-m-d H:i:s');
        $sql = "SELECT u.municipality_no, u.brgy_no FROM tbl_user_access u WHERE u.user_id = ? AND u.valid_until > ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$userId);
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

            $sql .= "(v.municipality_no = {$municipalityNo} AND v.brgy_no = {$brgyNo}) OR";
        }

        $sql  = rtrim($sql,'OR');
        $sql .= ")";

        if($sql == " AND ()")
            $sql = " AND (v.municipality_no IS NULL AND v.brgy_no IS NULL)";

        return $sql;
    }

    private function getBarangayAccessFilter($userId,$municipalityNo){

        $em = $this->getDoctrine()->getManager();
        $currentDate = date('Y-m-d H:i:s');
        $sql = "SELECT u.municipality_no, u.brgy_no FROM tbl_user_access u WHERE u.user_id = ? AND u.valid_until > ? AND u.municipality_no = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$userId);
        $stmt->bindValue(2,$currentDate);
        $stmt->bindValue(3,$municipalityNo);
        $stmt->execute();

        $permissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if(count($permissions) <= 0){
            $permissions = [];
        }

        $sql = ' AND (';
        
        foreach($permissions as $permission){
            $brgyNo = $permission['brgy_no'];

            $sql .= "(b.brgy_no = {$brgyNo}) OR";
        }

        $sql  = rtrim($sql,'OR');
        $sql .= ")";

        if($sql == " AND ()")
            $sql = " AND (b.brgy_no IS NULL)";

        return $sql;
    }

    private function getMunicipalityAccessFilter($userId){

        $em = $this->getDoctrine()->getManager();
        $currentDate = date('Y-m-d H:i:s');
        $sql = "SELECT u.municipality_no, u.brgy_no FROM tbl_user_access u WHERE u.user_id = ? AND u.valid_until > ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$userId);
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

            $sql .= "(m.municipality_no = {$municipalityNo}) OR";
        }

        $sql  = rtrim($sql,'OR');
        $sql .= ")";

        if($sql == " AND ()")
            $sql = " AND (m.municipality_no IS NULL)";

        return $sql;
    }

     /**
     * @Route("/datatable",
     *     name="ajax_datatable_voter_summary_item_detail",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

    public function datatableVoterAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
      
        $filters = array();
        $filters['v.node_label'] = $request->get("voterName");
        
        $filters['v.elect_id'] = $request->get("electId");
        $filters['v.pro_id'] = $request->get('proId');

        $filters['v.municipality_no'] = $request->get("municipalityNo");
        $filters['v.province_code'] = $request->get('provinceCode');
        $filters['v.brgy_no'] = $request->get("brgyNo");
        $filters['v.precinct_no'] = $request->get("precinctNo");

        $columns = array(
            1 => 'v.node_label',
            2 => 'n.voted_2017',
            3 => 'b.name',
            4 => 'v.precinct_no'
        );

        $whereStmt = " AND (";

        foreach($filters as $field => $searchText){
            if($searchText != ""){
               if($field == 'v.voter_id'  || $field == 'v.elect_id' || $field == 'v.pro_id'){
                    $whereStmt .= "{$field} = '{$searchText}' AND "; 
               }if($field == 'v.municipality_no' || $field == 'v.brgy_no' || $field == 'v.precinct_no' || $field == 'v.province_code'){
                    $temp = $searchText == "" ? null : "'{$searchText}  '";
                    $whereStmt .= "({$field} = '{$searchText}' OR {$temp} IS NULL) AND ";
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
        
        $start = 0;
        $length = 1;

        if(null !== $request->query->get('start') && null !== $request->query->get('length')){
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(v.voter_id),0) FROM tbl_voter_network v ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(v.voter_id),0) FROM tbl_voter_network v
                INNER JOIN tbl_voter n ON n.voter_id = v.voter_id 
                INNER JOIN psw_municipality m ON m.municipality_no = v.municipality_no AND m.province_code = 53
                INNER JOIN psw_barangay b ON b.brgy_no = v.brgy_no AND b.municipality_code = m.municipality_code
                WHERE 1 ";
       
        if(!$user->getIsAdmin() && $user->getStrictAccess() != 0){
            $whereStmt .= $this->getDatatableRecordAccessFilter($user->getId());
        }elseif($user->getStrictAccess() != 1 && !$user->getIsAdmin()){
            $whereStmt .= $this->getDatatableMunicipalityAccessFilter($user->getId());
        }

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT v.* , b.name as barangay_name,v.node_label as voter_name,n.voted_2017,n.voter_no FROM tbl_voter_network v 
                INNER JOIN tbl_voter n ON n.voter_id = v.voter_id 
                INNER JOIN psw_municipality m ON m.municipality_no = v.municipality_no AND m.province_code = 53
                INNER JOIN psw_barangay b ON b.brgy_no = v.brgy_no AND b.municipality_code = m.municipality_code
                WHERE 1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while($row  = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $row['parent'] = $this->getParent($row['parent_id']);
            $data[] =  $row;
        }
       
        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] =  $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        $em->clear();

        return  new JsonResponse($res);
    }


    /**
     * @Route("/ajax_datatable_voter_summary_item_detail_download",
     *     name="ajax_datatable_voter_summary_item_detail_download",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

    public function datatableVoterDownloadAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        $electId = $request->get('electId');
        $proId = $request->get('proId');
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get('brgyNo');
        $precinctNo = $request->get("precinctNo");

        $filters = array();
        $filters['v.elect_id'] = $electId;
        $filters['v.pro_id'] = $proId;
        $filters['v.province_code'] = $provinceCode;
        $filters['v.municipality_no'] = $municipalityNo;
        $filters['v.brgy_no'] = $brgyNo;
        $filters['v.precinct_no'] = $request->get("precinctNo");
        $filters['v.node_label'] = $request->get("voterName");

        $columns = array(
            1 => 'v.node_label',
            2 => 'n.voted_2017',
            3 => 'b.name',
            4 => 'v.precinct_no'
        );

        $whereStmt = " AND (";

        foreach($filters as $field => $searchText){
            if($searchText != ""){
               if($field == 'v.voter_id'){
                    $whereStmt .= "{$field} = '{$searchText}' AND "; 
               }if($field == 'v.municipality_no' || $field == 'v.brgy_no' || $field == 'v.precinct_no' || $field == 'v.province_code'){
                    $temp = $searchText == "" ? null : "'{$searchText}  '";
                    $whereStmt .= "({$field} = '{$searchText}' OR {$temp} IS NULL) AND ";
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
        

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
       
        if(!$user->getIsAdmin() && $user->getStrictAccess() != 0){
            $whereStmt .= $this->getDatatableRecordAccessFilter($user->getId());
        }elseif($user->getStrictAccess() != 1 && !$user->getIsAdmin()){
            $whereStmt .= $this->getDatatableMunicipalityAccessFilter($user->getId());
        }

        $sql = "SELECT v.* , b.name as barangay_name,v.node_label as voter_name, n.voter_no,n.voted_2017 FROM tbl_voter_network v 
                INNER JOIN tbl_voter n ON n.voter_id = v.voter_id  
                INNER JOIN psw_municipality m ON m.municipality_no = v.municipality_no AND m.province_code = 53
                INNER JOIN psw_barangay b ON b.brgy_no = v.brgy_no AND b.municipality_code = m.municipality_code
                WHERE 1 " . $whereStmt . ' ' . $orderStmt;

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while($row  = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $row['parent'] = $this->getParent($row['parent_id']);
            $data[] =  $row;
        }
        
        $filename = $provinceCode . $municipalityNo . $brgyNo . $precinctNo . ".xlsx";
        $fileRoot = __DIR__.'/../../../web/uploads/';

        if(file_exists($fileRoot . $filename)){
            unlink($fileRoot . $filename);
        }

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($fileRoot . $filename);

        $style = (new StyleBuilder())
                    ->setFontBold()
                    ->build();

        $writer->addRowWithStyle([
            "Name",
            "2016",
            "Barangay",
            "Precinct No.",
            "Is Leader",
            "Leader"
        ],$style);

        foreach($data as $item){
            $writer->addRow([  
                $item['voter_name'],
                $item['voted_2017'],
                $item['barangay_name'],
                $item['precinct_no'],
                $item['node_level'] == 1 ? "YES" : "NO",
                $item['parent']['node_label']
            ]);
        }

        $writer->close();

        $response = new BinaryFileResponse($fileRoot . $filename);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }

    private function getParent($nodeId){
        $em = $this->getDoctrine();
        $voter = [
            "node_label" => ''
        ];

        if($nodeId == 0)
            return $voter;
            
        $sql = "SELECT * FROM tbl_voter_network n WHERE node_id = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$nodeId);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if(!$row);
            $voter = $row;

        return $voter;
    }

    private function getMunicipalities(){
        $name = '';
        $code = '';

        $em  = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_municipality WHERE province_code = '53'";
        $stmt = $em->getConnection()->query($sql);
        $municipalities = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $municipalities;
    }

    private function getBarangay($municipalityCode,$brgyNo){
        $name = '';

        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_barangay WHERE brgy_code LIKE '53%'";
        $stmt = $em->getConnection()->query($sql);
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

    private function getDatatableMunicipalityAccessFilter($userId){
        $em = $this->getDoctrine()->getManager();
        $currentDate = date('Y-m-d H:i:s');
        $sql = "SELECT DISTINCT u.municipality_no FROM tbl_user_access u WHERE u.user_id = ? AND u.valid_until > ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$userId);
        $stmt->bindValue(2,$currentDate);
        $stmt->execute();

        $municipalities = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if(count($municipalities) <= 0){
            $municipalities = [];
        }

        $sql = '';
        $accessible = '(';

        foreach($municipalities as $municipality){
            $municipalityNo = $municipality['municipality_no'];
            $accessible .= "'${municipalityNo}',";
        }

        $accessible  = rtrim($accessible,',');
        $accessible .= ')';

        if($accessible == '()'){
            $sql = ' AND v.municipality_no IN ("")';
        }else{
            $sql = ' AND v.municipality_no IN ' . $accessible;
        }

        return $sql;
    }

    private function getDatatableRecordAccessFilter($userId){

        $em = $this->getDoctrine()->getManager();
        $currentDate = date('Y-m-d H:i:s');
        $sql = "SELECT u.municipality_no, u.brgy_no FROM tbl_user_access u WHERE u.user_id = ? AND u.valid_until > ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$userId);
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

            $sql .= "(v.municipality_no = {$municipalityNo} AND v.brgy_no = {$brgyNo}) OR";
        }

        $sql  = rtrim($sql,'OR');
        $sql .= ")";

        if($sql == " AND ()")
            $sql = " AND (v.municipality_no IS NULL AND v.brgy_no IS NULL)";

        return $sql;
    }

    private function isAllowed($userId, $municipalityNo, $brgyNo){
        $em = $this->getDoctrine()->getManager();
        $currentDate = date('Y-m-d H:i:s');

        $sql = "SELECT DISTINCT u.municipality_no, u.brgy_no FROM tbl_user_access u WHERE u.user_id = ? AND u.valid_until > ? AND u.municipality_no = ? AND u.brgy_no = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$userId);
        $stmt->bindValue(2,$currentDate);
        $stmt->bindValue(3,$municipalityNo);
        $stmt->bindValue(4,$brgyNo);
        $stmt->execute();

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            return true;
        }

        return false;
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

}
