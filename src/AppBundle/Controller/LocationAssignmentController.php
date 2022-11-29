<?php
namespace AppBundle\Controller;

use AppBundle\Entity\LocationAssignment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/location-assignment")
 */

class LocationAssignmentController extends Controller
{

    /**
     * @Route("/ajax_get_datatable_location_assignment/{proIdCode}", name="ajax_get_datatable_location_assignment", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxGetDatatableLocationAssignmentAction($proIdCode, Request $request)
    {

        $columns = array(
            0 => "l.id",
            1 => "l.municipality_name",
            2 => "l.barangay_name",
            2 => "l.status",
        );

        $sWhere = "";

        $select['l.municipality_name'] = $request->get('municipalityName');
        $select['l.barangay_name'] = $request->get('barangayName');

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {
                $sWhere .= " AND " . $key . " = '" . $searchValue . "'";
            }
        }

        $sWhere .= " AND l.pro_id_code = {$proIdCode} ";

        $sOrder = "";
        if (null !== $request->query->get('order')) {
            $sOrder = "ORDER BY  ";
            for ($i = 0; $i < intval(count($request->query->get('order'))); $i++) {
                if ($request->query->get('columns')[$request->query->get('order')[$i]['column']]['orderable']) {
                    $selected_column = $columns[$request->query->get('order')[$i]['column']];
                    $sOrder .= " " . $selected_column . " " .
                        ($request->query->get('order')[$i]['dir'] === 'asc' ? 'ASC' : 'DESC') . ", ";
                }
            }

            $sOrder = substr_replace($sOrder, "", -2);
            if ($sOrder == "ORDER BY") {
                $sOrder = "";
            }
        }

        $sOrder = " ORDER BY municipality_name, barangay_name ";

        $start = 1;
        $length = 1;

        if (null !== $request->query->get('start') && null !== $request->query->get('length')) {
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(l.id),0) FROM tbl_location_assignment l";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(l.id),0) FROM tbl_location_assignment l
                WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT * FROM tbl_location_assignment l
                WHERE 1 " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        return new JsonResponse($res);
    }

    private function getMunicipalities()
    {
        $name = '';
        $code = '';

        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_municipality WHERE province_code = '53'";
        $stmt = $em->getConnection()->query($sql);
        $municipalities = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $municipalities;
    }

    private function getBarangay($municipalityCode, $brgyNo)
    {
        $name = '';

        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_barangay WHERE brgy_code LIKE '53%'";
        $stmt = $em->getConnection()->query($sql);
        $barangays = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($barangays as $barangay) {
            $barangayCode = $municipalityCode . $brgyNo;
            if ($barangayCode == $barangay['brgy_code']) {
                $name = $barangay['name'];
            }
        }

        if (empty($name)) {
            $name = '- - - - -';
        }

        return $name;
    }

    /**
     * @Route("/ajax_delete_location_assignment/{id}",
     *     name="ajax_delete_location_assignment",
     *    options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteLocationAssignmentAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:LocationAssignment")->find($id);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

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
        $em->flush();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_post_location_assignment/{proIdCode}",
     *     name="ajax_post_location_assignment",
     *    options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostLocationAssignmentAction($proIdCode, Request $request)
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
        }

        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    private function getBarangays($provinceCode, $municipalityNo)
    {
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT b.* FROM psw_municipality m
                 LEFT JOIN psw_barangay b ON b.municipality_code = m.municipality_code
                 WHERE m.province_code = ? AND m.municipality_no = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->execute();

        $barangays = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($barangays) <= 0) {
            $barangays = [];
        }
        return $barangays;
    }

    /**
     * @Route("/ajax_location_assignment_multiselect_municipality",
     *   name="ajax_location_assignment_multiselect_municipality",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxLocationAssignmentMultiselectMunicipalityAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT b.* FROM psw_barangay b
                INNER JOIN psw_municipality m ON m.municipality_code = b.municipality_code
                WHERE m.province_code = ?
                AND m.municipality_no = ?
                AND b.brgy_no NOT IN (SELECT s.barangay_no FROM tbl_location_assignment s WHERE s.municipality_no = ? ) ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53);
        $stmt->bindValue(2, $request->get('municipalityNo'));
        $stmt->bindValue(3, $request->get('municipalityNo'));
        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!is_array($data) || count($data) <= 0) {
            $data = [];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_get_municipality_loc",
     *       name="ajax_get_municipality_loc",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetMunicipality(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $provinceCode = 53;
        $municipalityNo = trim(strtoupper($request->get('municipalityNo')));

        $sql = "SELECT DISTINCT name FROM psw_municipality m WHERE m.province_code = ? AND  m.municipality_no = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->execute();

        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$municipality) {
            return new JsonResponse(null, 404);
        }

        return new JsonResponse($municipality);
    }

    /**
     * @Route("/ajax_get_barangay_loc",
     *       name="ajax_get_barangay_loc",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetBarangay(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $provinceCode = 53;
        $municipalityNo = trim(strtoupper($request->get('municipalityNo')));
        $brgyNo = trim(strtoupper($request->get('brgyNo')));

        $sql = "SELECT DISTINCT name FROM psw_barangay b WHERE b.municipality_code = ? AND  b.brgy_no = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode . $municipalityNo);
        $stmt->bindValue(2, $brgyNo);
        $stmt->execute();

        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$municipality) {
            return new JsonResponse(null, 404);
        }

        return new JsonResponse($municipality);
    }

}
