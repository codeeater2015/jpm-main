<?php
namespace AppBundle\Controller;

use AppBundle\Entity\RecruitmentDetail;
use AppBundle\Entity\RecruitmentHeader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/recruitment")
 */

class ProjectRecruitmentController extends Controller
{
    const STATUS_ACTIVE = 'A';
    const STATUS_INACTIVE = 'I';
    const STATUS_BLOCKED = 'B';
    const STATUS_PENDING = 'PEN';

    /**
     * @Route("", name="recruitment_index", options={"main" = true })
     */

    public function indexAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/recruitment/index.html.twig', ['user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl]);
    }

    /**
     * @Route("/sub", name="recruitment2_index", options={"main" = true })
     */

    public function index2Action(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/recruitment/index2.html.twig', ['user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl]);
    }

    /**
     * @Route("/ajax_get_datatable_recruitment_header", name="ajax_get_datatable_recruitment_header", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

    public function ajaxGetDatatableRecruitmentHeaderAction(Request $request)
    {
        $columns = array(
            0 => "h.id",
            1 => "h.voter_name",
            2 => "h.municipality_name",
            3 => "h.barangay_name",
        );

        $sWhere = "";

        $select['h.voter_name'] = $request->get("voterName");
        $select['h.municipality_name'] = $request->get("municipalityName");
        $select['h.barangay_name'] = $request->get("barangayName");
        $select['h.elect_id'] = $request->get("electId");
        $select['h.municipality_no'] = $request->get("municipalityNo");
        $select['h.barangay_no'] = $request->get("brgyNo");

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {

                if ($key == 'h.elect_id' || $key == 'h.municipality_no' || $key == 'h.barangay_no') {
                    $sWhere .= "AND  {$key} = '{$searchValue}' ";
                }
                $sWhere .= " AND " . $key . " LIKE '%" . $searchValue . "%' ";
            }
        }

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

        $start = 1;
        $length = 1;

        if (null !== $request->query->get('start') && null !== $request->query->get('length')) {
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_recruitment_hdr h ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_recruitment_hdr h WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.* FROM tbl_recruitment_hdr h 
            WHERE 1 " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        foreach ($data as &$row) {
            $sql = "SELECT COUNT(*) FROM tbl_recruitment_dtl WHERE rec_id = ? ";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $row['id']);
            $stmt->execute();

            $totalMembers = intval($stmt->fetchColumn());

            $row['total_members'] = $totalMembers;
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        return new JsonResponse($res);
    }

    /**
     * @Route("/ajax_post_recruitment_header",
     *     name="ajax_post_recruitment_header",
     *    options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostRecruitmentHeaderAction(Request $request)
    {
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $entity = new RecruitmentHeader();
        $entity->setElectId($request->get('electId'));
        $entity->setProVoterId($request->get('proVoterId'));
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));

        $entity->setFirstname($request->get('firstname'));
        $entity->setLastname($request->get('lastname'));
        $entity->setMiddlename($request->get('middlename'));
        $entity->setExtName($request->get('extName'));
        $entity->setVoterGroup($request->get('voterGroup'));
        $entity->setPosition($request->get('position'));

        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
        $entity->setRemarks($request->get('remarks'));
        $entity->setStatus(self::STATUS_ACTIVE);

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('proVoterId'))]);

        if ($proVoter) {
            if (!empty($request->get('cellphoneNo'))) {
                $proVoter->setCellphone($request->get('cellphoneNo'));
            }

            $proVoter->setFirstname($entity->getFirstname());
            $proVoter->setMiddlename($entity->getMiddlename());
            $proVoter->setLastname($entity->getLastname());
            $proVoter->setExtname($entity->getExtName());
            $proVoter->setVoterGroup(trim(strtoupper($request->get('voterGroup'))));
            $proVoter->setPosition(trim(strtoupper($request->get('position'))));

            $entity->setVoterName($proVoter->getVoterName());
            $entity->setProIdCode($proVoter->getProIdCode());
        }

        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $sql = "SELECT * FROM psw_municipality
        WHERE province_code = ?
        AND municipality_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53);
        $stmt->bindValue(2, $entity->getMunicipalityNo());
        $stmt->execute();

        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($municipality != null) {
            $entity->setMunicipalityName($municipality['name']);
        }

        $sql = "SELECT * FROM psw_barangay
        WHERE brgy_code = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53 . $entity->getMunicipalityNo() . $entity->getBarangayNo());
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($barangay != null) {
            $entity->setBarangayName($barangay['name']);
        }

        $em->persist($entity);
        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_post_recruitment_detail",
     *     name="ajax_post_recruitment_detail",
     *    options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostRecruitmentDetailAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $entity = new RecruitmentDetail();
        $entity->setProVoterId($request->get('proVoterId'));
        $entity->setRecId($request->get('recId'));
        $entity->setFirstname(trim(strtoupper($request->get('firstname'))));
        $entity->setMiddlename(trim(strtoupper($request->get('middlename'))));
        $entity->setLastname(trim(strtoupper($request->get('lastname'))));
        $entity->setExtName(trim(strtoupper($request->get('extName'))));

        $entity->setGender($request->get('gender'));
        $entity->setBirthDate($request->get('birthdate'));

        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBarangayNo($request->get('barangayNo'));
        $entity->setVoterGroup(trim(strtoupper($request->get('voterGroup'))));
        $entity->setDialect(trim(strtoupper($request->get('dialect'))));
        $entity->setReligion(trim(strtoupper($request->get('religion'))));
        $entity->setCellphone(trim(strtoupper($request->get('cellphone'))));

        $entity->setIsTagalog($request->get('isTagalog'));
        $entity->setIsCuyonon($request->get('isCuyonon'));
        $entity->setIsBisaya($request->get('isBisaya'));
        $entity->setIsIlonggo($request->get('isIlonggo'));

        $entity->setIsCatholic($request->get('isCatholic'));
        $entity->setIsInc($request->get('isInc'));
        $entity->setIsIslam($request->get('isIslam'));
        $entity->setPosition($request->get('position'));

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => intval($request->get('proVoterId'))]);

        if ($proVoter) {
            $entity->setVoterName($proVoter->getVoterName());
            $entity->setProIdCode($proVoter->getProIdCode());
        }

        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
        $entity->setRemarks($request->get('remarks'));
        $entity->setStatus(self::STATUS_ACTIVE);

        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        if ($proVoter) {
            if (!empty($request->get('cellphone'))) {
                $proVoter->setCellphone($request->get('cellphone'));
            }

            $proVoter->setFirstname($entity->getFirstname());
            $proVoter->setMiddlename($entity->getMiddlename());
            $proVoter->setLastname($entity->getLastname());
            $proVoter->setExtname($entity->getExtName());
            $proVoter->setGender($entity->getGender());
            $proVoter->setBirthdate($entity->getBirthdate());
            $proVoter->setReligion($entity->getReligion());
            $proVoter->setDialect($entity->getDialect());
            $proVoter->setVoterGroup($entity->getVoterGroup());

            $proVoter->setIsTagalog($entity->getIsTagalog());
            $proVoter->setIsCuyonon($entity->getIsCuyonon());
            $proVoter->setIsBisaya($entity->getIsBisaya());
            $proVoter->setIsIlonggo($entity->getIsIlonggo());

            $proVoter->setIsCatholic($entity->getIsCatholic());
            $proVoter->setIsInc($entity->getIsInc());
            $proVoter->setIsIslam($entity->getIsIslam());
            $proVoter->setPosition($entity->getPosition());
        }

        $sql = "SELECT * FROM psw_municipality
        WHERE province_code = ?
        AND municipality_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53);
        $stmt->bindValue(2, $entity->getMunicipalityNo());
        $stmt->execute();

        $municipality = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($municipality != null) {
            $entity->setMunicipalityName($municipality['name']);
        }

        $sql = "SELECT * FROM psw_barangay
        WHERE brgy_code = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53 . $entity->getMunicipalityNo() . $entity->getBarangayNo());
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($barangay != null) {
            $entity->setBarangayName($barangay['name']);
        }

        $em->persist($entity);
        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_delete_recruitment_header/{recId}",
     *     name="ajax_delete_recruitment_header",
     *    options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteRecruitmentHeaderAction($recId)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:RecruitmentHeader")->find($recId);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $entities = $em->getRepository('AppBundle:RecruitmentDetail')->findBy([
            'recId' => $entity->getId(),
        ]);

        foreach ($entities as $detail) {
            $em->remove($detail);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_get_recruitment_header/{recId}",
     *       name="ajax_get_recruitment_header",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetRecruitmentHeader($recId)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:RecruitmentHeader")
            ->find($recId);

        if (!$entity) {
            return new JsonResponse(['message' => 'not found']);
        }

        $serializer = $this->get("serializer");
        $entity = $serializer->normalize($entity);

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->find($entity['proVoterId']);

        if ($proVoter != null) {
            $entity['cellphone'] = $proVoter->getCellphone();
            $entity['position'] = $proVoter->getPosition();
            $entity['lgc'] = $this->getLGC($proVoter->getMunicipalityNo(), $proVoter->getBrgyNo());
        } else {
            $entity['cellphone'] = "VOTER MISSING";
            $entity['lgc'] = [
                "voter_name" => "VOTER MISSING",
                "cellphone" => "VOTER MISSING",
            ];
        }

        return new JsonResponse($entity);
    }

    private function getLGC($municipalityNo, $barangayNo)
    {
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT pv.voter_name, pv.cellphone, la.municipality_name, la.barangay_name FROM tbl_location_assignment la INNER JOIN tbl_project_voter pv
                ON pv.pro_voter_id = la.pro_voter_id
                WHERE la.municipality_no = ? AND la.barangay_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityNo);
        $stmt->bindValue(2, $barangayNo);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row == null ? ['voter_name' => "No LGC"] : $row;
    }

    /**
     * @Route("/ajax_get_recruitment_header_full/{recId}",
     *       name="ajax_get_recruitment_header_full",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetRecruitmentFullHeader($recId)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:RecruitmentHeader")
            ->find($recId);

        if (!$entity) {
            return new JsonResponse(['message' => 'not found']);
        }

        $serializer = $this->get("serializer");
        $entity = $serializer->normalize($entity);

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->find($entity['proVoterId']);

        if ($proVoter != null) {
            $entity['cellphone'] = $proVoter->getCellphone();
            $entity['position'] = $proVoter->getPosition();
            $entity['lgc'] = $this->getLGC($proVoter->getMunicipalityNo(), $proVoter->getBrgyNo());

            $proVoter = $serializer->normalize($proVoter);
            //$proVoter['householdInfo'] = $this->getHouseholdInfo($proVoter['proIdCode']);
            //$proVoter['recInfo'] = $this->getRecruitmentInfo($proVoter['proIdCode']);

            $entity = array_merge($entity, $proVoter);
        } else {
            $entity['cellphone'] = "VOTER MISSING";
            $entity['lgc'] = [
                "voter_name" => "VOTER MISSING",
                "cellphone" => "VOTER MISSING",
            ];
        }

        return new JsonResponse($entity);
    }

    /**
     * @Route("/ajax_get_recruitment_other_info/{proIdCode}",
     *       name="ajax_get_recruitment_other_info",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetRecruitmentOtherInfo($proIdCode)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:ProjectVoter")
            ->findOneBy(['proIdCode' => $proIdCode]);

        if (!$entity) {
            return new JsonResponse(['message' => 'not found']);
        }

        $serializer = $this->get("serializer");
        $entity = $serializer->normalize($entity);

        
        $household =  $this->getHouseholdInfo($proIdCode);
        $recruitment = $this->getRecruitmentInfo($proIdCode);

        return new JsonResponse([
            'householdInfo' => $household,
            'recInfo' => $recruitment
        ]);
    }

    private function getHouseholdInfo($proIdCode)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = 'SELECT
                COALESCE(COUNT(pv.pro_voter_id),0) AS totalMembers,
                COALESCE(COUNT(CASE WHEN pv.is_non_voter = 1 THEN 1 END),0) AS totalNonVoter,
                COALESCE(COUNT(CASE WHEN pv.is_non_voter <> 1 THEN 1 END),0) AS totalVoter,
                COALESCE(COUNT(CASE WHEN pv.cellphone IS NOT NULL AND pv.cellphone <> "" THEN 1 END),0) AS totalWithCp
                FROM tbl_household_dtl hd
                INNER JOIN tbl_household_hdr hh ON hh.id = hd.household_id
                INNER JOIN tbl_project_voter pv ON pv.pro_id_code = hd.pro_id_code
                WHERE hh.pro_id_code = ? ';

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $proIdCode);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row;
    }

    private function getRecruitmentInfo($proIdCode)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = 'SELECT
                COALESCE(COUNT(pv.pro_voter_id),0) AS totalMembers,
                COALESCE(COUNT(CASE WHEN pv.is_non_voter = 1 THEN 1 END),0) AS totalNonVoter,
                COALESCE(COUNT(CASE WHEN pv.is_non_voter <> 1 THEN 1 END),0) AS totalVoter,
                COALESCE(COUNT(CASE WHEN pv.cellphone IS NOT NULL AND pv.cellphone <> "" THEN 1 END),0) AS totalWithCp

                FROM tbl_recruitment_dtl hd
                INNER JOIN tbl_recruitment_hdr hh ON hh.id = hd.rec_id
                INNER JOIN tbl_project_voter pv ON pv.pro_id_code = hd.pro_id_code
                WHERE hh.pro_id_code = ? ';

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $proIdCode);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row;
    }

    /**
     * @Route("/ajax_get_datatable_recruitment_detail", name="ajax_get_datatable_recruitment_detail", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

    public function ajaxGetDatatableHouseholdDetailAction(Request $request)
    {
        $columns = array(
            0 => "h.rec_id",
            1 => "h.voter_name",
            2 => "h.birthdate",
            3 => "h.voter_group",
            4 => "h.barangay_name",
            4 => "h.cellphone",
        );

        $sWhere = "";
        $select['h.voter_name'] = $request->get("voterName");

        $recId = $request->get('recId');

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {
                $sWhere .= " AND " . $key . " LIKE '%" . $searchValue . "%'";
            }
        }

        $sWhere .= " AND h.rec_id = ${recId} ";

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

        $start = 1;
        $length = 1;

        if (null !== $request->query->get('start') && null !== $request->query->get('length')) {
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_recruitment_dtl h WHERE h.rec_id = ${recId}";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_recruitment_dtl h WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.*, v.birthdate , v.cellphone, v.dialect, v.religion, v.voter_group FROM tbl_recruitment_dtl h INNER JOIN tbl_project_voter v ON v.pro_voter_id = h.pro_voter_id
            WHERE 1 " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['total_members'] = 0;
            $data[] = $row;
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        return new JsonResponse($res);
    }

    /**
     * @Route("/ajax_delete_recruitment_detail/{id}",
     *     name="ajax_delete_recruitment_detail",
     *    options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteRecruitmentDetailAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:RecruitmentDetail")->find($id);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }


    
    /**
     * @Route("/ajax_get_datatable_recruitment_not_submitted", name="ajax_get_datatable_recruitment_not_submitted", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

    public function ajaxGetDatatableRecruitmentNotSubmittedAction(Request $request)
    {
        $columns = array(
            0 => "pv.pro_voter_id",
            1 => "pv.voter_name",
            2 => "pv.municipality_name",
            3 => "pv.barangay_name",
        );

        $sWhere = "";

        $select = [];

        // $select['h.voter_name'] = $request->get("voterName");
        // $select['h.municipality_name'] = $request->get("municipalityName");
        // $select['h.barangay_name'] = $request->get("barangayName");
        // $select['h.elect_id'] = $request->get("electId");
        // $select['h.municipality_no'] = $request->get("municipalityNo");
        // $select['h.barangay_no'] = $request->get("brgyNo");

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {

                if ($key == 'h.elect_id' || $key == 'h.municipality_no' || $key == 'h.barangay_no') {
                    $sWhere .= "AND  {$key} = '{$searchValue}' ";
                }
                $sWhere .= " AND " . $key . " LIKE '%" . $searchValue . "%' ";
            }
        }

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

        $start = 1;
        $length = 1;

        if (null !== $request->query->get('start') && null !== $request->query->get('length')) {
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(pv.pro_voter_id),0) FROM tbl_project_voter pv ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(pv.pro_voter_id),0) FROM tbl_project_voter pv WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT pv.* FROM tbl_project_voter pv   
            WHERE pv.elect_id = 3 AND pv.pro_id = 3 AND pro_id_code NOT IN (
                SELECT h.pro_id_code FROM tbl_recruitment_hdr h WHERE h.elect_id = 3 
            )
            AND pv.voter_group IN ('LOPP','LPPP')  " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        // foreach ($data as &$row) {
        //     $sql = "SELECT COUNT(*) FROM tbl_recruitment_dtl WHERE rec_id = ? ";
        //     $stmt = $em->getConnection()->prepare($sql);
        //     $stmt->bindValue(1, $row['id']);
        //     $stmt->execute();

        //     $totalMembers = intval($stmt->fetchColumn());

        //     $row['total_members'] = $totalMembers;
        // }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        return new JsonResponse($res);
    }
}
