<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;

use AppBundle\Entity\UserAccess;

/**
* @Route("/user-access")
*/

class UserAccessController extends Controller 
{   
    const STATUS_ACTIVE = 'A';
    const MODULE_MAIN = "USER_ACCESS";

	/**
    * @Route("", name="user_access_index", options={"main" = true})
    */

    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        return $this->render('template/user-access/index.html.twig',['user' => $user]);
    }
    
    /**
     * @Route("/ajax_get_datatable_users_access_list/{userId}", name="ajax_get_datatable_users_access_list", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
	public function ajaxGetDatatableUsersListAction($userId, Request $request)
	{	

        $columns = array(
            0 => "u.access_id",
            1 => "u.municipality_no",
            2 => "u.brgy_no",
            2 => "u.status",
        );

        $sWhere = "";
        
        $select['u.province_code'] = $request->get("provinceCode");
        $select['u.municipality_no'] = $request->get('municipalityNo');
        $select['u.brgy_no'] = $request->get('brgyNo');

        foreach($select as $key => $value){
            $searchValue = $select[$key];
            if($searchValue != null || !empty($searchValue)) {
                $sWhere .= " AND " . $key . " = '" . $searchValue . "'";
            }
        }

        $sWhere .= " AND u.user_id = {$userId} ";

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

        $sql = "SELECT COALESCE(count(u.access_id),0) FROM tbl_user_access u";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(u.access_id),0) FROM tbl_user_access u
                WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT u.*,p.name AS province_name , m.name AS municipality_name, b.name AS brgy_name FROM tbl_user_access u
                INNER JOIN psw_province p ON p.province_code = u.province_code
                INNER JOIN psw_municipality m ON m.municipality_no = u.municipality_no AND m.province_code = p.province_code
                INNER JOIN psw_barangay b ON b.brgy_no = u.brgy_no AND m.municipality_code = b.municipality_code
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

    /**
    * @Route("/ajax_delete_user_access/{accessId}", 
    * 	name="ajax_delete_user_access",
    *	options={"expose" = true}
    * )
    * @Method("DELETE")
    */  

    public function ajaxDeleteUserAccessAction($accessId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:UserAccess")->find($accessId);

        if(!$entity)
            return new JsonResponse(null,404);

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null,200);
    }

    /**
    * @Route("/ajax_post_user_access/{userId}", 
    * 	name="ajax_post_user_access",
    *	options={"expose" = true}
    * )
    * @Method("POST")
    */

    public function ajaxPostUserAccessAction($userId,Request $request){

        $entity = new UserAccess();

        $entity->setProvinceCode($request->get("provinceCode"));
    	$entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBrgyNo($request->get('brgyNo'));
        $entity->setUserId($userId);
        $entity->setRemarks($request->get('remarks'));
        $entity->setValidUntil( new \DateTime());
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
        
        if(empty($request->get("brgyNo"))){
            
            $barangays = $this->getBarangays($entity->getProvinceCode(),$entity->getMunicipalityNo());
            
            foreach($barangays as $barangay){
                $entity = new UserAccess();

                $entity->setProvinceCode($request->get("provinceCode"));
                $entity->setMunicipalityNo($request->get('municipalityNo'));
                $entity->setBrgyNo($barangay['brgy_no']);
                $entity->setUserId($userId);
                $entity->setRemarks($request->get('remarks'));
                $entity->setValidUntil( new \DateTime());
                $entity->setStatus(self::STATUS_ACTIVE);

                $violations = $validator->validate($entity);

                if(count($violations) <= 0){
                    $em->persist($entity);
                }
            }
            $em->flush();
        }else{
            $em->persist($entity);    
            $em->flush();
        }
    	
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }

    private function getBarangays($provinceCode, $municipalityNo){
        $em = $this->getDoctrine()->getManager();
        $sql  = "SELECT b.* FROM psw_municipality m 
                 LEFT JOIN psw_barangay b ON b.municipality_code = m.municipality_code 
                 WHERE m.province_code = ? AND m.municipality_no = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->execute();

        $barangays = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if(count($barangays) <= 0 ){
            $barangays = [];
        }
        return $barangays;
    }
}
