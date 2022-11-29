<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
* @Route("/kalaban")
*/

class PulahanController extends Controller 
{   
    const STATUS_ACTIVE = 'A';
    const MODULE_MAIN = "PULAHAN_MODULE";

	/**
    * @Route("", name="pulahan_index", options={"main" = true})
    */

    public function indexAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/pulahan/index.html.twig',[ 'user' => $user, 'hostIp' => $hostIp , 'imgUrl' => $imgUrl ]);
    }

     /**
     * @Route("/datatable",
     *     name="ajax_pulahan_datatable",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

    public function pulahanDatatableAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
      
        $filters = array();
        $filters['pv.province_code'] = $request->get("provinceCode");
        
        $filters['pv.municipality_name'] = $request->get("municipalityName");
        $filters['pv.barangay_name'] = $request->get("barangayName");

        $filters['pv.municipality_no'] = $request->get("municipalityNo");
        $filters['pv.brgy_no'] = $request->get("brgyNo");
        $filters['pv.precinct_no'] = $request->get("precinctNo");

        $filters['pv.voter_name'] = $request->get("voterName");
        $filters['pv.birthdate'] = $request->get("birthdate");
        $filters['pv.on_network'] = $request->get("onNetwork");
        $filters['pv.voted_2017'] = $request->get("voted2017");
        
        $filters['pv.elect_id'] = $request->get('electId');
        
        $filters['pv.is_kalaban'] = 1;

        $columns = array(
            0 => 'pv.voter_id',
            1 => 'pv.voter_name',
            2 => 'pv.municipality_name',
            3 => 'pv.barangay_name',
            4 => 'pv.cellphone'
        );

        $whereStmt = " AND (";

        foreach($filters as $field => $searchText){
            if($searchText != ""){
               if($field == 'pv.elect_id' || $field == 'pv.is_kalaban' ){
                    $whereStmt .= "{$field} = '{$searchText}' AND "; 
               }if($field == 'pv.municipality_no' || $field == 'pv.brgy_no' || $field == 'pv.precinct_no' || $field == 'pv.province_code'){
                    $temp = $searchText == "" ? null : "'{$searchText}'";
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

        $sql = "SELECT COALESCE(count(pv.pro_voter_id),0) FROM tbl_project_voter pv WHERE  is_kalaban = 1 ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(pv.pro_voter_id),0) FROM tbl_project_voter pv
                WHERE 1 ";

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT pv.* FROM tbl_project_voter pv
                WHERE 1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while($row =  $stmt->fetch(\PDO::FETCH_ASSOC))
        {   
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
     * @Route("/ajax_patch_project_voter_pulahan/{proId}/{proVoterId}",
     *     name="ajax_patch_project_voter_pulahan",
     *    options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchProjectVoterAction($proId, $proVoterId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->find($proVoterId);

        if (!$proVoter) {
            return new JsonResponse([], 404);
        }

        $proVoter->setCellphone($request->get('cellphone'));
        $proVoter->setVoterGroup($request->get('voterGroup'));
        $proVoter->setIsKalaban($request->get('isKalaban'));
        $proVoter->setIsKalabanReason($request->get('isKalabanReason'));
        $proVoter->setDidChanged(1);
        $proVoter->setUpdatedAt(new \DateTime());
        $proVoter->setUpdatedBy($user->getUsername());
        $proVoter->setRemarks($request->get('remarks'));
        $proVoter->setStatus(self::STATUS_ACTIVE);

        $validator = $this->get('validator');
        $violations = $validator->validate($proVoter);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->flush();
        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($proVoter));
    }
    
    /**
     * @Route("/ajax_select2_is_kalaban_reason",
     *       name="ajax_select2_is_kalaban_reason",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2VoterCategory(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT is_kalaban_reason FROM tbl_project_voter v WHERE v.is_kalaban_reason LIKE ? ORDER BY v.is_kalaban_reason ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $categories = $stmt->fetchAll();

        if (count($categories) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($categories);
    }
}
