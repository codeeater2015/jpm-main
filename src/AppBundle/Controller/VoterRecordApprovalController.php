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

/**
* @Route("/voter-approval-section")
*/

class VoterRecordApprovalController extends Controller 
{
    const STATUS_ACTIVE = 'A';
    const STATUS_PENDING = 'PEN';
    const STATUS_INACTIVE = 'I';
    const MODULE_MAIN = "VOTER_UPDATE_APPROVAL";

	/**
    * @Route("", name="voter_record_approval_index", options={"main" = true })
    */

    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        return $this->render('template/voter-record-approval/index.html.twig',['user' => $user]);
    }

    /**
     * @Route("/datatable",
     *     name="ajax_datatable_voter_approval",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

    public function datatableVoterAction(Request $request)
    {
        $filters = array();
        $filters['v.voter_name'] = $request->get("voterName");
        $filters['v.municipality_no'] = $request->get("municipalityNo");
        $filters['v.brgy_no'] = $request->get("brgyNo");
        $filters['v.precinct_no'] = $request->get("precinctNo");
        $filters['v.status'] = self::STATUS_PENDING;

        $user = $this->get('security.token_storage')->getToken()->getUser();

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
               }if($field == 'v.municipality_no' || $field == 'v.brgy_no' || $field == 'v.precinct_no'){
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

        $sql = "SELECT COALESCE(count(v.hist_id),0) FROM tbl_voter_history v";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(v.hist_id),0) FROM tbl_voter_history v
                WHERE 1 ";

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT v.* FROM tbl_voter_history v 
                WHERE 1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];
        $municipalities = $this->getMunicipalities();

        while($row =  $stmt->fetch(\PDO::FETCH_ASSOC))
        {   
            $municipality = [];
            foreach($municipalities as $mun){
                if($mun['municipality_no'] == $row['municipality_no']){
                    $municipality = $mun;
                }
            }
            
            $row['municipality_name'] = $municipality['name'];
            $row['barangay_name'] = $this->getBarangay($municipality['municipality_code'],$row['brgy_no']);

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

    /**
     * @Route("ajax_post_voter_approval",
     *   name="ajax_post_voter_approval",
     *   options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostVoterApproval(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $voters = $request->get('voters');

        $hdr = new VoterApprovalHdr();
        $hdr->setMunicipalityNo($request->get("municipalityNo"));
        $hdr->setBrgyNo($request->get("brgyNo"));
        $hdr->setTotalRecords(count($voters));
        $hdr->setCreatedAt(new \DateTime());
        $hdr->setCreatedBy($user->getUsername());
        $hdr->setRemarks($request->get("remarks"));
        $hdr->setStatus(self::STATUS_ACTIVE);

        $validator = $this->get('validator');
        $violations = $validator->validate($hdr);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }

        $em->persist($hdr);
        $em->flush();
        
        foreach($voters as $histId){
            $history = $em->getRepository(VoterHistory::class)->find($histId);
            $voter = $em->getRepository(Voter::class)->find($history->getVoterId());

            $history->setStatus(self::STATUS_ACTIVE);
                
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

            $voter->setUpdatedBy($user->getUsername());
            $voter->setUpdatedAt(new \DateTime());

            $detail = new VoterApprovalDtl();
            $detail->setApprId($hdr->getApprId());
            $detail->setHistId($histId);
            $detail->setVoterId($voter->getVoterId());
            $detail->setCreatedAt(new \DateTime());
            $detail->setCreatedBy($user->getUsername());
            $detail->setRemarks($request->get("remarks"));
            $detail->setStatus(self::STATUS_ACTIVE);

            $em->persist($detail);
        }

        $em->flush();

        $serializer = $this->get("serializer");

        return new JsonResponse($serializer->normalize($hdr),200);
    }
     
    /**
     * @Route("ajax_multiselect_voter",
     *   name="ajax_multiselect_voter",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxMultiselectVoter(Request $request){

        $em = $this->getDoctrine()->getManager();

        $municipalityNo = empty($request->get('municipalityNo')) ? null : $request->get('municipalityNo');
        $brgyNo = empty($request->get('brgyNo')) ? null : $request->get('brgyNo');

        $sql = "SELECT DISTINCT v.voter_name, v.voter_id, v.hist_id
                FROM tbl_voter_history v
                WHERE 
                  v.municipality_no = ?  AND
                  v.brgy_no = ? AND
                  v.status = ?
                ORDER BY v.voter_name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$municipalityNo);
        $stmt->bindValue(2,$brgyNo);
        $stmt->bindValue(3,self::STATUS_PENDING);
        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if(!is_array($data) || count($data) <= 0 )
            $data = [];

        return new JsonResponse($data);
    }

}
