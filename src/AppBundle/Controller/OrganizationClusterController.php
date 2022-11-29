<?php
namespace AppBundle\Controller;

use AppBundle\Entity\LocationAssignment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/organization-cluster")
 */

class OrganizationClusterController extends Controller
{
    const STATUS_ACTIVE = 'A';
    const MODULE_MAIN = "ORGANIZATION_CLUSTER";

    /**
     * @Route("", name="cluster_index", options={"main" = true})
     */

    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted("entrance", self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');

        return $this->render('template/organization-cluster/index.html.twig', ['user' => $user, 'hostIp' => $hostIp]);
    }

    /**
     * @Route("/ajax_select2_cluster_lgc",
     *       name="ajax_select2_cluster_lgc",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2ClusterLgc(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $electId = $request->get("electId");
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT p.* FROM tbl_project_voter p
                WHERE p.voter_name LIKE ?
                AND p.elect_id = ?
                AND p.voter_group = 'LGC'
                ORDER BY p.voter_name ASC LIMIT 10";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->bindValue(2, $electId);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        if (count($data) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_post_organization_cluster/{proIdCode}",
     *     name="ajax_post_organization_cluster",
     *    options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostOrganizationClusterAction($proIdCode, Request $request)
    {

        $entity = new LocationAssignment();
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            "proIdCode" => $proIdCode,
        ]);

        if (!$proVoter) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT * FROM psw_municipality
        WHERE province_code = ?
        AND municipality_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53);
        $stmt->bindValue(2, $request->get('municipalityNo'));
        $stmt->execute();

        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);

        $brgyCodes = $request->get('barangays');

        if (count($brgyCodes) == 0) {
            return new JsonResponse(['barangays' => 'Cannot be empty.'], 404);
        }

        $currBarangays = $em->getRepository("AppBundle:LocationAssignment")->findBy([
            "proIdCode" => $proIdCode,
        ]);

        foreach($currBarangays as $barangay){
            $sql = "UPDATE tbl_project_voter
                    SET brgy_cluster = ?
                    WHERE elect_id = ?
                    AND pro_id = ?
                    AND municipality_no = ?
                    AND brgy_no = ? ";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $request->get('clusterNo'));
            $stmt->bindValue(2, $proVoter->getElectId());
            $stmt->bindValue(3, $proVoter->getProId());
            $stmt->bindValue(4, $barangay->getMunicipalityNo());
            $stmt->bindValue(5, $barangay->getBarangayNo());
            $stmt->execute();
        }

        foreach ($brgyCodes as $brgyNo) {

            $sql = "SELECT * FROM psw_barangay
            WHERE brgy_code = ? ";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, 53 . $request->get('municipalityNo') . $brgyNo);
            $stmt->execute();

            $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($barangay == null) {
                new JsonResponse(['barangays' => "Barangay not found."]);
            }

            $entity = new LocationAssignment();
            $entity->setMunicipalityNo($request->get('municipalityNo'));
            $entity->setMunicipalityName($municipality['name']);
            $entity->setBarangayNo($barangay['brgy_no']);
            $entity->setBarangayName($barangay['name']);
            $entity->setProVoterId($proVoter->getProVoterId());
            $entity->setVoterName($proVoter->getVoterName());
            $entity->setProIdCode($proVoter->getProIdCode());
            $entity->setCreatedAt(new \DateTime());
            $entity->setCreatedBy($user->getUsername());
            $entity->setRemarks($request->get('remarks'));
            $entity->setStatus('A');

            $validator = $this->get('validator');
            $violations = $validator->validate($entity);

            if (count($violations) <= 0) {
                $em->persist($entity);
            }

            $sql = "UPDATE tbl_project_voter
                    SET brgy_cluster = ?
                    WHERE elect_id = ?
                    AND pro_id = ?
                    AND municipality_no = ?
                    AND brgy_no = ? ";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $request->get('clusterNo'));
            $stmt->bindValue(2, $proVoter->getElectId());
            $stmt->bindValue(3, $proVoter->getProId());
            $stmt->bindValue(4, $request->get('municipalityNo'));
            $stmt->bindValue(5, $brgyNo);
            $stmt->execute();
        }

        $proVoter->setClusterNo($request->get('clusterNo'));

        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_delete_organization_cluster/{proVoterId}",
     *     name="ajax_delete_organization_cluster",
     *    options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteOrganizationClusterAction($proVoterId)
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository("AppBundle:LocationAssignment")->findBy(['proVoterId' => $proVoterId]);
        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->find($proVoterId);

        if (count($entities) <= 0) {
            return new JsonResponse(null, 404);
        }

        foreach ($entities as $entity) {

            $sql = "UPDATE tbl_project_voter
                    SET brgy_cluster = ?
                    WHERE elect_id = ?
                    AND pro_id = ?
                    AND municipality_no = ?
                    AND brgy_no = ? ";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, 0);
            $stmt->bindValue(2, 3);
            $stmt->bindValue(3, 3);
            $stmt->bindValue(4, $entity->getMunicipalityNo());
            $stmt->bindValue(5, $entity->getBarangayNo());
            $stmt->execute();

            $em->remove($entity);
        }

        $proVoter->setClusterNo(0);
        $em->flush();

        return new JsonResponse(null, 200);
    }


      /**
     * @Route("/ajax_datatable_organization_cluster",
     *     name="ajax_datatable_organization_cluster",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

    public function datatableVoterAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $filters = array();
        $filters['v.province_code'] = $request->get("provinceCode");
        $filters['v.municipality_no'] = $request->get("municipalityNo");
        $filters['v.brgy_no'] = $request->get("brgyNo");
        $filters['v.precinct_no'] = $request->get("precinctNo");

        $filters['v.voter_name'] = $request->get("voterName");
        $filters['v.birthdate'] = $request->get("birthdate");
        $filters['v.cellphone'] = $request->get("cellphone");
        $filters['v.voter_group'] = $request->get("voterGroup");
        $filters['v.house_form_sub'] = $request->get("recFormSub");
        $filters['v.rec_form_sub'] = $request->get("houseFormSub");

        $filters['v.elect_id'] = $request->get('electId');

        $columns = array(
            0 => 'v.voter_id',
            1 => 'v.voter_name',
            2 => 'v.on_network',
            3 => 'v.voted_2017',
            6 => 'v.precinct_no',
        );

        $whereStmt = " AND (";

        foreach ($filters as $field => $searchText) {
            if ($searchText != "") {
                if ($field == 'v.voter_id' || $field == 'v.elect_id' || $field == 'v.voter_group'|| $field == 'v.rec_form_sub' || $field == 'v.house_form_sub'  ) {
                    $whereStmt .= "{$field} = '{$searchText}' AND ";
                }if ($field == 'v.municipality_no' || $field == 'v.brgy_no' || $field == 'v.precinct_no' || $field == 'v.province_code' ) {
                    $temp = $searchText == "" ? null : "'{$searchText}  '";
                    $whereStmt .= "({$field} = '{$searchText}' OR {$temp} IS NULL) AND ";
                } else {
                    $whereStmt .= "{$field} LIKE '%{$searchText}%' AND ";
                }
            }
        }

        $whereStmt = substr_replace($whereStmt, "", -4);

        if ($whereStmt == " A") {
            $whereStmt = "";
        } else {
            $whereStmt .= ")";
        }

        $orderStmt = "";

        if (null !== $request->query->get('order')) {
            $orderStmt = $this->genOrderStmt($request, $columns);
        }

        $start = 0;
        $length = 1;

        if (null !== $request->query->get('start') && null !== $request->query->get('length')) {
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(v.pro_voter_id),0) FROM tbl_project_voter v WHERE elect_id = 3 AND voter_group = 'LGC' ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(v.pro_voter_id),0) FROM tbl_project_voter v
                WHERE cluster_no <> 0 ";

        $sql .= $whereStmt . ' ' . $orderStmt;

        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT v.* FROM tbl_project_voter v WHERE 1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        foreach($data as &$item){
            $sql = "SELECT COALESCE(COUNT(id),0) FROM tbl_location_assignment WHERE pro_id_code = ? ";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $item['pro_id_code']);
            $stmt->execute();
            
            $totalBarangay = $stmt->fetchColumn();
            $item['total_barangay'] = $totalBarangay;
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        $em->clear();

        return new JsonResponse($res);
    }

    private function genOrderStmt($request, $columns)
    {

        $orderStmt = "ORDER BY  ";

        for ($i = 0; $i < intval(count($request->query->get('order'))); $i++) {
            if ($request->query->get('columns')[$request->query->get('order')[$i]['column']]['orderable']) {
                $orderStmt .= " " . $columns[$request->query->get('order')[$i]['column']] . " " .
                    ($request->query->get('order')[$i]['dir'] === 'asc' ? 'ASC' : 'DESC') . ", ";
            }
        }

        $orderStmt = substr_replace($orderStmt, "", -2);
        if ($orderStmt == "ORDER BY") {
            $orderStmt = "";
        }

        return $orderStmt;
    }
}
