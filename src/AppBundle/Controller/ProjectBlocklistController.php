<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
* @Route("/blocklist")
*/

class ProjectBlocklistController extends Controller 
{   
    const STATUS_ACTIVE = 'A';
    const MODULE_MAIN = "BLOCKLIST_MODULE";

	/**
    * @Route("", name="blocklist_index", options={"main" = true})
    */

    public function indexAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/project-blocklist/index.html.twig',[ 'user' => $user, 'hostIp' => $hostIp , 'imgUrl' => $imgUrl ]);
    }


     /**
     * @Route("/ajax_get_datatable_project_blocklist", name="ajax_get_datatable_project_blocklist", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    
	public function ajaxGetDatatableProjectBlocklistAction(Request $request)
	{	
        $columns = array(
            0 => "pv.voter_id",
            1 => "pv.voter_name",
            2 => "pv.voter_group"
        );

        $sWhere = "";
    
        $select['pv.rec_id'] = $request->get('proVoterId');
        $select['pv.voter_name'] = $request->get("voterName");
        $select['pv.voter_group'] = $request->get("voterGroup");

        $proId = $request->get("proId");

        foreach($select as $key => $value){
            $searchValue = $select[$key];
            if($searchValue != null || !empty($searchValue)) {
                $sWhere .= " AND " . $key . " LIKE '%" . $searchValue . "%'";
            }
        }
        
    
        $sWhere .= " AND pv.pro_id = '{$proId}' ";

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

        $sql = "SELECT COALESCE(count(pv.pro_voter_id),0) FROM tbl_project_voter pv WHERE pv.pro_id = {$proId}";
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

}
