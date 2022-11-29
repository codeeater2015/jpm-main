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

/**
* @Route("/voter-update-section")
*/

class VoterRecordUpdateController extends Controller 
{
    const STATUS_ACTIVE = 'A';
    const STATUS_PENDING = 'PEN';
    const STATUS_INACTIVE = 'I';
    const MODULE_MAIN = "VOTER";

	/**
    * @Route("", name="voter_record_update_index", options={"main" = true })
    */

    public function indexAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        return $this->render('template/voter-record-update/index.html.twig',['user' => $user]);
    }

   
    /**
    * @Route("/ajax_patch_voter/{voterId}", 
    *       name="ajax_patch_voter",
    *		options={ "expose" = true }
    * )
    * @Method("PATCH")
    */

    public function patchVoterAction($voterId,Request $request){
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        
        $entity = $em->getRepository(VoterHistory::class)
                     ->findOneBy(['voterId' => $voterId],['createdAt' => 'DESC']);

        if(!$entity){
            $entity = $em->getRepository(Voter::class)->find($voterId);
        }

        $didChange = false;

        if(!$entity)
            return new JsonResponse(null,404);
        
        if($this->isToggable($request->get("is1"))){
            $entity->setIs1($request->get("is1"));
            $didChange  = true;
        }

        if($this->isToggable($request->get("is2"))){
            $entity->setIs2($request->get("is2"));
            $didChange  = true;
        }

        if($this->isToggable($request->get("is3"))){
            $entity->setIs3($request->get("is3"));
            $didChange = true;
        }

        if($this->isToggable($request->get("is4"))){
            $entity->setIs4($request->get("is4"));
            $didChange = true;
        }

        if($this->isToggable($request->get("is5"))){
            $entity->setIs5($request->get("is5"));
            $didChange = true;
        }

        if($this->isToggable($request->get("is6"))){
            $entity->setIs6($request->get("is6"));
            $didChange = true;
        }

        if($this->isToggable($request->get("is7"))){
            $entity->setIs7($request->get("is7"));
            $didChange = true;
        }

        if($this->isToggable($request->get("voted2017"))){
            $entity->setVoted2017($request->get("voted2017"));
            $didChange = true;
        }

        if($this->isToggable($request->get("hasAst"))){
            $entity->setHasAst($request->get("hasAst"));
            $didChange = true;
        }

        if($this->isToggable($request->get("hasA"))){
            $entity->setHasA($request->get("hasA"));
            $didChange = true;
        }

        if($this->isToggable($request->get("hasB"))){
            $entity->setHasB($request->get("hasB"));
            $didChange = true;
        }

        if($this->isToggable($request->get("hasC"))){
            $entity->setHasC($request->get("hasC"));
            $didChange = true;
        }

        if($request->get("cellphoneNo") != $entity->getCellphoneNo()){
            $entity->setCellphoneNo($request->get("cellphoneNo"));
            $didChange = true;
        }

        if($request->get("category") != $entity->getCategory()){
            $entity->setCategory($request->get("category"));
            $didChange = true;
        }

        if($request->get("organization") != $entity->getOrganization()){
            $entity->setOrganization($request->get("organization"));
            $didChange = true;
        }

        if($request->get("position") != $entity->getPosition()){
            $entity->setPosition($request->get("position"));
            $didChange = true;
        }

        if($didChange){
            $history = new VoterHistory();
            $history->setVoterId($entity->getVoterId());
            $history->setVoterNo($entity->getVoterNo());
            $history->setVoterName($entity->getVoterName());
            $history->setPrecinctNo($entity->getPrecinctNo());
            $history->setMunicipalityNo($entity->getMunicipalityNo());
            $history->setBrgyNo($entity->getBrgyNo());
            
            $history->setHasAst($entity->getHasAst());
            $history->setHasA($entity->getHasA());
            $history->setHasB($entity->getHasB());
            $history->setHasC($entity->getHasC());
            $history->setVoted2017($entity->getVoted2017());
            $history->setVoterStatus($entity->getVoterStatus());
            $history->setAddress($entity->getAddress());
            $history->setIs1($entity->getIs1());
            $history->setIs2($entity->getIs2());
            $history->setIs3($entity->getIs3());
            $history->setIs4($entity->getIs4());
            $history->setIs5($entity->getIs5());
            $history->setIs6($entity->getIs6());
            $history->setIs7($entity->getIs7());
            $history->setVoted2017($entity->getVoted2017());
            $history->setCellphoneNo($entity->getCellphoneNo());
            $history->setCategory($entity->getCategory());
            $history->setOrganization($entity->getOrganization());
            $history->setPosition($entity->getPosition());

            $history->setCreatedBy($user->getUsername());
            $history->setCreatedAt(new \DateTime);
            $history->setStatus(self::STATUS_PENDING);
            $history->setRemarks($entity->getRemarks());

            $sql = "UPDATE tbl_voter_history SET status  = ? WHERE status = ? OR status = ? AND voter_id  = ?";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, self::STATUS_INACTIVE);
            $stmt->bindValue(2, self::STATUS_ACTIVE);
            $stmt->bindValue(3, self::STATUS_PENDING);
            $stmt->bindValue(4, $entity->getVoterId()); 
            $stmt->execute();
            
            $em->clear(); 

            if($user->getRequireApproval() == 0 || $user->getIsAdmin()){
                $voter = $em->getRepository(Voter::class)->find($voterId);
                $voter->setIs1($history->getIs1());
                $voter->setIs2($history->getIs2());
                $voter->setIs3($history->getIs3());
                $voter->setIs4($history->getIs4());
                $voter->setIs5($history->getIs5());
                $voter->setIs6($history->getIs6());
                $voter->setIs7($history->getIs7());
                $voter->setVoted2017($history->getVoted2017());
                $voter->setHasAst($history->getHasAst());
                $voter->setHasA($history->getHasA());
                $voter->setHasB($history->getHasB());
                $voter->setHasC($history->getHasC());
                $voter->setCellphoneNo($history->getCellphoneNo());
                $voter->setCategory($history->getCategory());
                $voter->setOrganization($history->getOrganization());
                $voter->setPosition($history->getPosition());
                $voter->setUpdatedBy($user->getUsername());
                $voter->setUpdatedAt(new \DateTime());
                
                $history->setStatus(self::STATUS_ACTIVE);                   
            }

            $em->persist($history);
            $em->flush();
        }

        return new JsonResponse(null,200);
    }

    private function isToggable($value){
        return $value != null && $value != "" && ($value == 0 ||  $value == 1);
    }

     /**
     * @Route("/datatable",
     *     name="ajax_datatable_voter_update",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

    public function datatableVoterAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
      
        $filters = array();
        $filters['v.voter_name'] = $request->get("voterName");
        $filters['v.municipality_no'] = $request->get("municipalityNo");
        $filters['v.brgy_no'] = $request->get("brgyNo");
        $filters['v.precinct_no'] = $request->get("precinctNo");
        $filters['v.province_code'] = $request->get("provinceCode");

        $columns = array(
            0 => 'v.voter_id',
            1 => 'v.voter_name',
            2 => 'v.precinct_no'
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
        
        $start = 0;
        $length = 1;

        if(null !== $request->query->get('start') && null !== $request->query->get('length')){
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(v.voter_id),0) FROM tbl_voter v";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(v.voter_id),0) FROM tbl_voter v
                WHERE 1 ";

        if(!$user->getIsAdmin()){
            $whereStmt .= $this->getRecordAccessFilter($user->getId());
        }

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT v.* FROM tbl_voter v 
                WHERE 1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];
        $provinceCode = $request->get("provinceCode");
        $municipalities = $this->getMunicipalities($provinceCode);

        if(!empty($provinceCode)){
            while($row =  $stmt->fetch(\PDO::FETCH_ASSOC))
            {   
                $municipality = [];
                foreach($municipalities as $mun){
                    if($mun['municipality_no'] == $row['municipality_no']){
                        $municipality = $mun;
                    }
                }
                
                $row['municipality_name'] = $municipality['name'];
                $row['barangay_name'] = $this->getBarangay($provinceCode, $municipality['municipality_code'], $row['brgy_no']);
    
                $data[] = $row;
            }
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] =  $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        $em->clear();

        return  new JsonResponse($res);
    }

    private function getRecordAccessFilter($userId){

        $em = $this->getDoctrine()->getManager();
        $currentDate = date('Y-m-d H:i:s');
        $sql = "SELECT u.municipality_no, u.brgy_no, u.province_code FROM tbl_user_access u WHERE u.user_id = ? AND u.valid_until > ?";
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
            $provinceCode = $permission['province_code'];

            $sql .= "(v.municipality_no = {$municipalityNo} AND v.brgy_no = {$brgyNo} AND v.province_code = {$provinceCode}) OR";
        }

        $sql  = rtrim($sql,'OR');
        $sql .= ")";

        if($sql == " AND ()")
            $sql = " AND (v.municipality_no IS NULL AND v.brgy_no IS NULL)";

        return $sql;
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

}
