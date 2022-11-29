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

/**
* @Route("/voter-assistance-summary")
*/

class VoterAssistanceSummaryController extends Controller 
{
    const STATUS_ACTIVE = 'A';
    const STATUS_PENDING = 'PEN';
    const STATUS_INACTIVE = 'I';
    const MODULE_MAIN = "VOTER_SUMMARY";

	/**
    * @Route("", name="voter_assistance_summary_index", options={"main" = true })
    */

    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->render('template/voter-assistance-summary/index.html.twig',['user' => $user]);
    }


    /**
    * @Route("/ajax_get_municipality_data_assistance_summary", 
    *       name="ajax_get_municipality_data_assistance_summary",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetMunicipalityDataAssistanceSummary(Request $request){
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $provinceCode = empty($request->get("provinceCode")) ? 53 : $request->get('provinceCode');
        $municipalityNo = $request->get("municipalityNo");
    
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT b.*,
        (SELECT aa.total_amount FROM tbl_assistance_summary aa WHERE aa.province_code = m. AND aa.municipality_no = m.municipality_no AND aa.brgy_no = b.brgy_no ) AS total_amount  
        FROM  psw_barangay b 
        INNER JOIN psw_municipality m ON m.municipality_code = b.municipality_code
        WHERE b.municipality_code = ? ";
        
        $accessFilter  = "";

        // if(!$user->getIsAdmin() && $user->getStrictAccess()){
        //     $accessFilter = $this->getBarangayAccessFilter($user->getId(),$municipalityNo);
        // }
      
        $sql .= $accessFilter . " ORDER BY b.name ASC";
        
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode . $municipalityNo);
        $stmt->execute();

        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }
                
        return new JsonResponse($data);
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
}
