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
* @Route("/failed-transfers")
*/

class FailedTransfersController extends Controller 
{   
    const STATUS_ACTIVE = 'A';
    const MODULE_MAIN = "FAILED_TRANSFERS_MODULE";

	/**
    * @Route("", name="failed_transfer_index", options={"main" = true})
    */

    public function indexAction(Request $request)
    {

        //$this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');
        $reportUrl = $this->getParameter('report_url');

        return $this->render('template/failed-transfers/index.html.twig',[ 'user' => $user, 'hostIp' => $hostIp , 'imgUrl' => $imgUrl , 'reportUrl' => $reportUrl ]);
    }

    
    /**
     * @Route("/ajax_get_datatable_failed_transfers", name="ajax_get_datatable_failed_transfers", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    
	public function ajaxGetDatatableFailedTransfersAction(Request $request)
	{	
        $columns = array(
            0 => "pv.id",
            1 => "pv.municipality_name",
            2 => "pv.barangay_name",
            3 => "pv.voter_name",
        );

        $sWhere = "";
    
        $select['pv.voter_name'] = $request->get("voterName");
        $select['pv.voter_group'] = $request->get("voterGroup");
        $select['pv.municipality_name'] = $request->get("municipalityName");
        $select['pv.barangay_name'] = $request->get("barangayName");
        
        foreach($select as $key => $value){
            $searchValue = $select[$key];
            if($searchValue != null || !empty($searchValue)) {
                $sWhere .= " AND " . $key . " LIKE '%" . $searchValue . "%'";
            }
        }
        
        $sWhere .= " AND pv.elect_id = 3 AND pv.pro_id = 3 AND pv.to_migrate = 1 AND pv.is_migration_failed = 1 AND pv.is_transfered <> 1 AND has_photo = 1  ";
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

        $sql = "SELECT COALESCE(count(pv.pro_voter_id),0) FROM tbl_project_voter pv WHERE pv.is_migration_failed = 1 AND is_transfered <> 1 AND has_photo = 1 ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(pv.pro_voter_id),0) FROM tbl_project_voter pv WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT pv.* FROM tbl_project_voter pv 
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
    * @Route("/ajax_select2_failed_transfer_voters", 
    *       name="ajax_select2_failed_transfer_voters",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2FailedTransferVoters(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

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
                AND to_migrate = 1 
                AND is_migration_failed = 1
                AND is_transfered <> 1 
                AND is_failed_transfer <> 1
                AND has_photo = 1
                ORDER BY p.voter_name ASC LIMIT 10";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$searchText);
        $stmt->bindValue(2,53);
        $stmt->bindValue(3,3);
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
    * @Route("/ajax_select2_failed_transfer_new_list", 
    *       name="ajax_select2_failed_transfer_new_list",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2FailedTransferNewList(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $municipalityNo = $request->get("municipalityNo");

        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';
        
        $sql = "SELECT p.* FROM tbl_project_voter p 
                WHERE p.voter_name LIKE ? 
                AND p.elect_id = ? 
                AND (municipality_no = ? OR ? IS NULL)
                ORDER BY p.voter_name ASC LIMIT 10";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$searchText);
        $stmt->bindValue(2,4);
        $stmt->bindValue(3, $municipalityNo);
        $stmt->bindValue(4, empty($municipalityNo) ? null : $municipalityNo);
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
    * @Route("/ajax_post_transfer", 
    * 	name="ajax_post_transfer",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostTransferAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $proVoterId = $request->get('proVoterId');
        $targetProVoterId = $request->get('targetProVoterId');

        if(empty($proVoterId))
            return new JsonResponse(['proVoterId' => "source voter cannot be empty."],400);

        
        if(empty($targetProVoterId))
            return new JsonResponse(['targetproVoterId' => "target voter cannot be empty."],400);


    
        $sql = "SELECT * FROM tbl_project_voter pv 
        WHERE pv.elect_id = ? AND pv.pro_voter_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 3);
        $stmt->bindValue(2, $proVoterId);
        $stmt->execute();

        $sourceRecord = $stmt->fetch(\PDO::FETCH_ASSOC);
        $sourceFound = $sourceRecord == null ? false : true;

        
        $sql = "SELECT * FROM tbl_project_voter pv 
        WHERE pv.elect_id = ? AND pv.pro_voter_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 4);
        $stmt->bindValue(2, $targetProVoterId);
        $stmt->execute();

        $targetRecord = $stmt->fetch(\PDO::FETCH_ASSOC);
        $targetFound = $targetRecord == null ? false : true;

        if($sourceFound && $targetFound){

            $sql = "UPDATE tbl_project_voter pv
            SET pv.pro_id_code = ?, pv.cellphone = ?, pv.birthdate = ?, pv.has_photo = ?, pv.has_id = ?,
                pv.photo_at = ?, pv.is_jpm = ?, pv.is_others = ?, pv.is_bisaya = ?, pv.is_cuyonon = ?, pv.others_specify = ?,
                pv.firstname = ?, pv.middlename = ?, pv.lastname = ?, pv.ext_name = ?, pv.gender = ? , pv.civil_status = ?,
                pv.bloodtype = ?, pv.occupation = ?, pv.religion = ?, pv.dialect = ?, pv.ip_group = ? , pv.is_non_voter = ? ,
                pv.generated_id_no = ?, pv.date_generated = ?, pv.is_migrated = ? , pv.voter_group = ? , pv.special_op_group = ? , pv.special_op_pro_id_code = ? ,
                pv.brgy_cluster = ? , pv.brgy_cluster = ? 
            WHERE pv.elect_id = ? AND pv.pro_id = ? AND pv.pro_voter_id = ? ";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $sourceRecord['pro_id_code']);
            $stmt->bindValue(2, $sourceRecord['cellphone']);
            $stmt->bindValue(3, $sourceRecord['birthdate']);
            $stmt->bindValue(4, $sourceRecord['has_photo']);
            $stmt->bindValue(5, $sourceRecord['has_id']);
            $stmt->bindValue(6, $sourceRecord['photo_at']);
            $stmt->bindValue(7, $sourceRecord['is_jpm']);
            $stmt->bindValue(8, $sourceRecord['is_others']);
            $stmt->bindValue(9, $sourceRecord['is_bisaya']);
            $stmt->bindValue(10, $sourceRecord['is_cuyonon']);
            $stmt->bindValue(11, $sourceRecord['others_specify']);
            $stmt->bindValue(12, $sourceRecord['firstname']);
            $stmt->bindValue(13, $sourceRecord['middlename']);
            $stmt->bindValue(14, $sourceRecord['lastname']);
            $stmt->bindValue(15, $sourceRecord['ext_name']);
            $stmt->bindValue(16, $sourceRecord['gender']);
            $stmt->bindValue(17, $sourceRecord['civil_status']);
            $stmt->bindValue(18, $sourceRecord['bloodtype']);
            $stmt->bindValue(19, $sourceRecord['occupation']);
            $stmt->bindValue(20, $sourceRecord['religion']);
            $stmt->bindValue(21, $sourceRecord['dialect']);
            $stmt->bindValue(22, $sourceRecord['ip_group']);
            $stmt->bindValue(23, $sourceRecord['is_non_voter']);
            $stmt->bindValue(24, $sourceRecord['generated_id_no']);
            $stmt->bindValue(25, $sourceRecord['date_generated']);
            $stmt->bindValue(26, 1);
            $stmt->bindValue(27, $sourceRecord['voter_group']);
            $stmt->bindValue(28, $sourceRecord['special_op_group']);
            $stmt->bindValue(29, $sourceRecord['special_op_pro_id_code']);
            $stmt->bindValue(30, $sourceRecord['brgy_cluster']);
            $stmt->bindValue(31, $sourceRecord['brgy_cluster']);
            $stmt->bindValue(32, $targetRecord['elect_id']);
            $stmt->bindValue(33, $targetRecord['pro_id']);
            $stmt->bindValue(34, $targetRecord['pro_voter_id']);
            $stmt->execute();

            $sql = "UPDATE tbl_project_voter pv
                    SET pv.is_transfered =  1
                    WHERE pv.elect_id = ? AND pv.pro_id = ? AND pv.pro_voter_id = ? ";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $sourceRecord['elect_id']);
            $stmt->bindValue(2, $sourceRecord['pro_id']);
            $stmt->bindValue(3, $sourceRecord['pro_voter_id']);
            $stmt->execute();
        }

        return new JsonResponse(200);
    }

     /**
    * @Route("/ajax_post_failed_transfer_not_found", 
    * 	name="ajax_post_failed_transfer_not_found",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostFailedTransferNotFoundAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $proVoterId = $request->get('proVoterId');

        if(empty($proVoterId))
            return new JsonResponse(['proVoterId' => "source voter cannot be empty."],400);
    
        $sql = "SELECT * FROM tbl_project_voter pv 
        WHERE pv.elect_id = ? AND pv.pro_voter_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 3);
        $stmt->bindValue(2, $proVoterId);
        $stmt->execute();

        $sourceRecord = $stmt->fetch(\PDO::FETCH_ASSOC);
        $sourceFound = $sourceRecord == null ? false : true;

        if($sourceFound){

            $sql = "UPDATE tbl_project_voter pv
                    SET pv.is_failed_transfer =  1
                    WHERE pv.elect_id = ? AND pv.pro_id = ? AND pv.pro_voter_id = ? ";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $sourceRecord['elect_id']);
            $stmt->bindValue(2, $sourceRecord['pro_id']);
            $stmt->bindValue(3, $sourceRecord['pro_voter_id']);
            $stmt->execute();
        }

        return new JsonResponse(200);
    }

}
