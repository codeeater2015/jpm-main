<?php
namespace AppBundle\Controller;

use AppBundle\Entity\SpecialOperationDetail;
use AppBundle\Entity\SpecialOperationPhotos;
use AppBundle\Entity\SpecialOperationHeader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @Route("/special-operation")
 */

class SpecialOperationController extends Controller
{
    const STATUS_ACTIVE = 'A';
    const STATUS_INACTIVE = 'I';
    const STATUS_BLOCKED = 'B';
    const STATUS_PENDING = 'PEN';

    /**
     * @Route("", name="special_operation_index", options={"main" = true })
     */

    public function indexAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/special-operation/index.html.twig', ['user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl]);
    }

    /**
     * @Route("/ajax_select2_organizations",
     *       name="ajax_select2_organizations",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Organizations(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT special_op_group FROM tbl_project_voter v WHERE v.special_op_group LIKE ? ORDER BY v.special_op_group ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $data = $stmt->fetchAll();

        if (count($data) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($data);
    }
    
   
    /**
     * @Route("/ajax_post_special_operation_header",
     *     name="ajax_post_special_operation_header",
     *    options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostSpecialOperationHeaderAction(Request $request)
    {
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $entity = new SpecialOperationHeader();
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
        $entity->setSpecialOpGroup($request->get('specialOpGroup'));

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
            $proVoter->setSpecialOpGroup(trim(strtoupper($request->get('specialOpGroup'))));
            $proVoter->setCellphone($request->get('cellphone'));

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
     * @Route("/ajax_get_datatable_special_operation_header", name="ajax_get_datatable_special_operation_header", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

    public function ajaxGetDatatableSpecialOperationHeaderAction(Request $request)
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
                    $sWhere .= 'AND ' . $key . '= "' . $searchValue . '" ';
                }
                $sWhere .= " AND " . $key . ' LIKE "%' . $searchValue . '%" ';
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

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_recruitment_special_hdr h ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_recruitment_special_hdr h WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.* FROM tbl_recruitment_special_hdr h 
            WHERE 1 " . $sWhere . ' ' . $sOrder . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        foreach ($data as &$row) {

            $sql = "SELECT COUNT(*) FROM tbl_recruitment_special_dtl WHERE rec_id = ? ";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $row['id']);
            $stmt->execute();

            $totalMembers = intval($stmt->fetchColumn());

            $sql = "SELECT 
                    COUNT(*) as total_photos,
                    COALESCE(COUNT(CASE WHEN (pro_voter_id IS NULL OR pro_voter_id = '') AND is_not_found <> 1 AND is_duplicate <> 1 then 1 end),0) AS total_unlinked,
                    COALESCE(COUNT(CASE WHEN is_not_found = 1 then 1 end),0) as total_not_found,
                    COALESCE(COUNT(CASE WHEN is_duplicate = 1 then 1 end),0) as total_duplicate 
                    FROM tbl_recruitment_special_photos WHERE rec_id = ? ";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $row['id']);
            $stmt->execute();

            $sum = $stmt->fetch(\PDO::FETCH_ASSOC);

            $totalPhotos = intval($sum['total_photos']);
            $totalUnlinked = intval($sum['total_unlinked']);
            $totalNotFound = intval($sum['total_not_found']);
            $totalDuplicate = intval($sum['total_duplicate']);
            
            $sql = "SELECT 
                    COUNT(*) as total_linked,
                    COALESCE(COUNT(CASE WHEN pv.has_id = 1 then 1 end),0) as total_with_id,
                    COALESCE(COUNT(CASE WHEN pv.has_photo = 1 AND (pv.has_id = 0 OR pv.has_id IS NULL ) then 1 end),0) as total_for_print
                    FROM tbl_recruitment_special_photos sp
                    INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = sp.pro_voter_id  
                    WHERE sp.rec_id = ? ";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $row['id']);
            $stmt->execute();

            $sum = $stmt->fetch(\PDO::FETCH_ASSOC);

            $totalLinked = intval($sum['total_linked']);
            $totalWithId = intval($sum['total_with_id']);
            $totalForPrint = intval($sum['total_for_print']);

            $row['total_members'] = $totalMembers;
            $row['total_photos'] = $totalPhotos;
            $row['total_not_found'] = $totalNotFound;
            $row['total_duplicate'] = $totalDuplicate;
            $row['total_unlinked'] = $totalUnlinked;

            $row['total_linked'] = $totalLinked;
            $row['total_with_id'] = $totalWithId;
            $row['total_for_print'] = $totalForPrint;
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        return new JsonResponse($res);
    }


      /**
     * @Route("/ajax_get_special_operation_header/{recId}",
     *       name="ajax_get_special_operation_header",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetSpecialOperationHeader($recId)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:SpecialOperationHeader")
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
     * @Route("/ajax_post_special_operation_detail",
     *     name="ajax_post_special_operation_detail",
     *    options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostSpecialDetailAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $header = $em->getRepository("AppBundle:SpecialOperationHeader")
        ->find($request->get('recId'));

        if(!$header)
            return new JsonResponse([],404);

        $entity = new SpecialOperationDetail();
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
            $proVoter->setDidChanged(1);
            $proVoter->setToSend(1);
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
            $proVoter->setSpecialOpGroup($header->getSpecialOpGroup());
            $proVoter->setSpecialOpProIdCode($header->getProIdCode());
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
     * @Route("/ajax_get_datatable_special_operation_detail", name="ajax_get_datatable_special_operation_detail", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

    public function ajaxGetDatatableSpecialOperationDetailAction(Request $request)
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

        $sql = "SELECT COALESCE(count(h.id),0) FROM tbl_recruitment_special_dtl h WHERE h.rec_id = ${recId}";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.id),0) FROM tbl_recruitment_special_dtl h WHERE 1 ";

        $sql .= $sWhere . ' ' . $sOrder;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.*, v.birthdate , v.cellphone, v.dialect, v.religion, v.voter_group ,v.generated_id_no
                FROM tbl_recruitment_special_dtl h INNER JOIN tbl_project_voter v ON v.pro_voter_id = h.pro_voter_id
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
     * @Route("/ajax_delete_special_operation_detail/{id}",
     *     name="ajax_delete_special_operation_detail",
     *    options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteSpecialOperationDetailAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:SpecialOperationDetail")->find($id);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $voter = $em->getRepository("AppBundle:ProjectVoter")->find($entity->getProVoterId());

        if($voter){
            $voter->setSpecialOpGroup(null);
            $voter->setSpecialOpProIdCode(null);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_delete_special_operation_header/{recId}",
     *     name="ajax_delete_special_operation_header",
     *    options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteSpecialOperationHeaderAction($recId)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:SpecialOperationHeader")->find($recId);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $entities = $em->getRepository('AppBundle:SpecialOperationDetail')->findBy([
            'recId' => $entity->getId(),
        ]);

        $photos = $em->getRepository('AppBundle:SpecialOperationPhotos')->findBy([
            'recId' => $entity->getId(),
        ]);

        $imgRoot = __DIR__ . '/../../../web/uploads/special-ops/';
        $municipalityDir = $imgRoot . '/' . $entity->getMunicipalityNo();
        $batchDir = $municipalityDir . '/' . $entity->getId();

        foreach ($entities as $detail) {

            $voter = $em->getRepository("AppBundle:ProjectVoter")->find($detail->getProVoterId());
            if($voter){
                $voter->setSpecialOpGroup(null);
                $voter->setSpecialOpProIdCode(null);
            }
           
            // $filePath = $batchDir . '/' . $detail->getFilename();

            // if(file_exists($filePath)) {
            //     unlink($filePath);
            // }
      
            $em->remove($detail);
        }

        foreach ($photos as $photo) {
           
            $filePath = $batchDir . '/' . $photo->getFilename();

            if(file_exists($filePath)) {
                unlink($filePath);
            }

            $em->remove($photo);
        }


        $leader = $em->getRepository("AppBundle:ProjectVoter")->find($entity->getProVoterId());

        if($leader){
            $leader->setSpecialOpGroup(null);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_special_ops_photo_upload/{recId}",
     *     name="ajax_special_ops_photo_upload",
     *     options={"expose" = true}
     *     )
     * @Method("POST")
     */

    public function ajaxAjaxSpecialOpsPhotoUpload(Request $request, $recId)
    {
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $header = $em->getRepository("AppBundle:SpecialOperationHeader")->find($recId);

        if(!$header)
            return new JsonResponse(['message' => 'not found.'], 404);

        $file = $request->files->get('file');
        $displayName = $file->getClientOriginalName();
        $imgRoot = __DIR__ . '/../../../web/uploads/special-ops/';
        
        $municipalityDir = $imgRoot . '/' . $header->getMunicipalityNo();
        $batchDir = $municipalityDir . '/' . $header->getId();

        $fileEntity = $em->getRepository("AppBundle:SpecialOperationPhotos")->findOneBy([
            'recId' => $header->getId(),
            'filename' => $displayName,
        ]);

        if (!$fileEntity) {
            $fileEntity = new SpecialOperationPhotos();
            $fileEntity->setRecId($header->getId());
            $fileEntity->setFilename($displayName);
            $fileEntity->setCreatedAt(new \DateTime());
            $fileEntity->setCreatedBy($user->getUsername());
            $fileEntity->setStatus('A');

            $em->persist($fileEntity);
            $em->flush();
        }

        if (!file_exists($municipalityDir)) {
            mkdir($municipalityDir, 0777, true);
        }

        if (!file_exists($batchDir)) {
            mkdir($batchDir, 0777, true);
        }

        $imagePath = $batchDir . '/' . $displayName;
        $tmpName = $file->getRealPath();

        $this->compress($tmpName, $imagePath, 50);

        return new JsonResponse(['message' => 'ok']);
    }

    public function compress($source, $destination, $quality)
    {

        $info = getimagesize($source);

        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
        }

        imagejpeg($image, $destination, $quality);

        return $destination;
    }

     /**
     * @Route("/ajax_datatable_special_ops_upload_items", name="ajax_datatable_special_ops_upload_items", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

    public function ajaxGetDatatableFieldItemsUploadAction(Request $request)
    {
        $columns = array(
            0 => "h.id",
            1 => "h.username",
            2 => "h.municipality_name",
            3 => "h.upload_date",
        );

        $sWhere = "";

        $select['h.id'] = $request->get("recId");
        $recId = $request->get('recId');
        $select['d.filename'] = $request->get("filename");
        $uploadFilter = $request->get('uploadFilter');
        $hasIdNo = intval($request->get('hasGeneratedIdNo'));
        
        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {

                if ($key == 'h.id' ) {
                    $sWhere .= "AND ( {$key} = '{$searchValue}' ) ";
                }else{
                    $sWhere .= " AND " . $key . ' LIKE "%' . $searchValue . '%" ';
                }
            }
        }


        switch($uploadFilter){
            case "LINKED" : 
                $sWhere .= " AND (d.pro_voter_id IS NOT NULL) ";
                break;
            case "UNLINKED" : 
                $sWhere .= " AND (d.pro_voter_id IS NULL OR d.pro_voter_id = '' ) AND (d.is_not_found <> 1 OR d.is_not_found IS NULL) AND (d.is_duplicate <> 1 OR d.is_duplicate IS NULL) ";
                break;
            case "NOT_FOUND" : 
                $sWhere .= " AND d.is_not_found = 1 ";
                break;
            case "DUPLICATE" : 
                $sWhere .= " AND d.is_duplicate = 1 ";
                break;
        }

        // if($hasIdNo == 1){
        //     $sWhere .= " AND (d.generated_id_no IS NOT NULL AND d.generated_id_no <> 0 and d.generated_id_no <> '') ";
        // }elseif($hasIdNo == 0){
        //     $sWhere .= " AND (d.generated_id_no IS  NULL OR d.generated_id_no = 0 OR d.generated_id_no = '') ";
        // }

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

        $sql = "SELECT COALESCE( COUNT(d.id),0)
                FROM tbl_recruitment_special_photos  d
                INNER JOIN tbl_recruitment_special_hdr h
                ON h.id  = d.rec_id 
                WHERE h.id = {$recId} ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE( COUNT(d.id),0)
                FROM tbl_recruitment_special_photos d 
                INNER JOIN tbl_recruitment_special_hdr h
                ON h.id  = d.rec_id 
                WHERE h.id = {$recId} ";

        $sql .= $sWhere . ' ';
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT  h.municipality_name, h.barangay_no, h.voter_group , d.*
                FROM tbl_recruitment_special_photos d 
                INNER JOIN tbl_recruitment_special_hdr h ON h.id  = d.rec_id 
                INNER JOIN psw_municipality m ON h.municipality_name = m.name AND m.province_code = 53
                WHERE 1 " . $sWhere . ' ORDER BY  d.filename ASC ' . " LIMIT {$length} OFFSET {$start}";

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

     /**
     * @Route("/photo/{id}",
     *   name="ajax_get_special_ops_upload_photo",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxGetSpecialOperationPhotoAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:SpecialOperationPhotos")
            ->find($id);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $hdr = $em->getRepository("AppBundle:SpecialOperationHeader")
            ->find($entity->getRecId());

        $rootDir = __DIR__ . '/../../../web/uploads/special-ops/';
        $municipalityDir = $rootDir . '/' . $hdr->getMunicipalityNo();
        $batchDir = $municipalityDir . '/' . $hdr->getId();

        $imagePath = $batchDir . '/' . $entity->getFilename();

        if (!file_exists($imagePath)) {
            $imagePath = $rootDir . 'default.jpg';
        }

        $response = new BinaryFileResponse($imagePath);
        $response->headers->set('Content-Type', 'image/jpeg');

        return $response;
    }

      /**
     * @Route("/item_detail/{id}",
     *   name="ajax_get_special_operation_upload_item_detail",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxGetSpecialOperationItemDetailAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:SpecialOperationPhotos")
            ->find($id);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $serializer = $this->get("serializer");
        $entity = $serializer->normalize($entity);

        return new JsonResponse($entity);
    }

    /**
     * @Route("/item_detail/{id}",
     *   name="ajax_patch_special_operation_upload_item",
     *   options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchSpecialOperationPhotoAction($id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:SpecialOperationPhotos")
            ->find($id);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $filename = $request->get('filename');
        $proVoterId = $request->get('proVoterId');
        $proIdCode = $request->get('proIdCode');
        $voterName = $request->get('voterName');
        $voterGroup = $request->get('voterGroup');
        $cellphone = $request->get('cellphoneNo');
        $isNotFound = $request->get('isNotFound');
        $isDuplicate = $request->get('isDuplicate');
        $remarks = $request->get('remarks');

        $hdr = $em->getRepository("AppBundle:SpecialOperationHeader")
            ->find($entity->getRecId());

        $rootDir = __DIR__ . '/../../../web/uploads/special-ops/';
        $municipalityDir = $rootDir . '/' . $hdr->getMunicipalityNo();
        $batchDir = $municipalityDir . '/' . $hdr->getId();

        $imagePath = $batchDir . '/' . $entity->getFilename();
        $newImagePath = $batchDir . '/' . $filename;

        
        if(!empty($filename)){
            $entity->setFilename($filename);
        }
        
        if(!empty($proIdCode)){
            $entity->setProIdCode($proIdCode);
           
        }

        if(!empty($proVoterId)){
            $generatedIdNo = $this->_generateIdNo(3, $proVoterId);

            if($generatedIdNo != null){
                $entity->setGeneratedIdNo($generatedIdNo);
            }

            $entity->setProVoterId($proVoterId);

            $voter =  $em->getRepository("AppBundle:ProjectVoter")
                         ->find($proVoterId);
            
            if($voter != null){
                if(!empty($cellphone)){
                    $voter->setCellphone($cellphone);
                }

                if(!empty($voterGroup)){
                    $voter->setVoterGroup($voterGroup);
                }

                $voter->setDidChanged(1);
                $voter->setToSend(1);
            }
        }
        
        if(!empty($voterName)){
            $entity->setDisplayName($voterName);
        }
        
        $entity->setIsNotFound(empty($isNotFound) ? 0 : $isNotFound);
        $entity->setIsDuplicate(empty($isDuplicate) ? 0 : $isDuplicate);
        $entity->setRemarks($remarks);

        $em->flush();

        if (file_exists($imagePath)) {
            rename($imagePath, $newImagePath);
        }
        
        $serializer = $this->get("serializer");
        $entity = $serializer->normalize($entity);

        return new JsonResponse($entity);
    }

    public function _generateIdNo($proId, $proVoterId)
    {
        $em = $this->getDoctrine()->getManager();

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => $proId,
            'proVoterId' => $proVoterId
        ]);
        
        $voterName = $proVoter->getVoterName();
        $munNo = $proVoter->getMunicipalityNo();
        $generatedIdNo = $proVoter->getGeneratedIdNo();

        if($proVoter->getGeneratedIdNo() == '' || $proVoter->getGeneratedIdNo() == null){
            $proIdCode = !empty($proVoter->getProIdCode()) ? $proVoter->getProIdCode() : $this->generateProIdCode($proId, $voterName, $munNo) ;
            $generatedIdNo = date('Y-m-d') . '-' . $proVoter->getMunicipalityNo() .'-' . $proVoter->getBrgyNo() .'-'. $proIdCode;

            $proVoter->setProIdCode($proIdCode);
            $proVoter->setGeneratedIdNo($generatedIdNo);
            $proVoter->setDateGenerated(date('Y-m-d'));
        }

        $proVoter->setDidChanged(1);
        $proVoter->setToSend(1);
        $proVoter->setUpdatedAt(new \DateTime());
        $proVoter->setUpdatedBy('android-app');
        $proVoter->setStatus('A');

    	$validator = $this->get('validator');
        $violations = $validator->validate($proVoter);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }

        $em->flush();
        
        return $generatedIdNo;
    }


    private function generateProIdCode($proId, $voterName, $municipalityNo)
    {
        $proIdCode = '000001';

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT CAST(RIGHT(pro_id_code ,6) AS UNSIGNED ) AS order_num FROM tbl_project_voter
        WHERE pro_id = ? AND municipality_no = ? ORDER BY order_num DESC LIMIT 1 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $proId);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->execute();

        $request = $stmt->fetch();

        if ($request) {
            $proIdCode = sprintf("%06d", intval($request['order_num']) + 1);
        }

        $namePart = explode(' ', $voterName);
        $uniqueId = uniqid('PHP');

        $prefix = '';

        foreach ($namePart as $name) {
            $prefix .= substr($name, 0, 1);
        }

        return $prefix . $municipalityNo . $proIdCode;
    }
    
    /**
     * @Route("/ajax_delete_special_operation_photo/{id}",
     *     name="ajax_delete_special_operation_photo",
     *    options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteSpecialOperationPhotoAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:SpecialOperationPhotos")->find($id);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $header = $em->getRepository("AppBundle:SpecialOperationHeader")->find($entity->getRecId());

        if(!$header){
            return new JsonResponse($header);
        }

        $imgRoot = __DIR__ . '/../../../web/uploads/special-ops/';
        $municipalityDir = $imgRoot . '/' . $header->getMunicipalityNo();
        $batchDir = $municipalityDir . '/' . $header->getId();
        $filePath = $batchDir . '/' . $entity->getFilename();

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }


    /**
    * @Route("/ajax_get_so_no_id/{id}", 
    *   name="ajax_get_so_no_id",
    *   options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetSONoId(Request $request,$id){
        $em = $this->getDoctrine()->getManager();
        $municipalityName = $request->get('municipalityName');
        $brgyNo = $request->get('brgyNo');

        $sql = "SELECT * FROM tbl_recruitment_special_photos sp 
                INNER JOIN tbl_recruitment_special_hdr sh ON sh.id = sp.rec_id 
                INNER JOIN tbl_project_voter pv ON sp.pro_voter_id = pv.pro_voter_id
                WHERE pv.has_photo = 1 AND (pv.has_id = 0 OR  pv.has_id IS NULL OR pv.has_id = '' )
                AND sh.id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$id);
        $stmt->execute();
        
        $data = array();
        
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        $em->clear();

        return new JsonResponse($data);
    }
    

    /**
     * @Route("/ajax_select2_so_reason",
     *       name="ajax_select2_so_reason",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Reason(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT remarks FROM tbl_recruitment_special_photos r WHERE r.remarks LIKE ? ORDER BY r.remarks ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $data = $stmt->fetchAll();

        if (count($data) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($data);
    }

}
