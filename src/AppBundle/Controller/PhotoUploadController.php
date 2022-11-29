<?php
namespace AppBundle\Controller;

use AppBundle\Entity\FieldUploadDtl;
use AppBundle\Entity\FieldUploadHdr;
use AppBundle\Entity\PhotoUploadSummary;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/photo-upload")
 */

class PhotoUploadController extends Controller
{
    /**
     * @Route("", name="photo_upload_index", options={"main" = true})
     */

    public function indexAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/photo-upload/index.html.twig', ['user' => $user, 'hostIp' => $hostIp, 'imgUrl' => $imgUrl]);
    }

    /**
     * @Route("/ajax_field_photo_upload",
     *     name="ajax_field_photo_upload",
     *     options={"expose" = true}
     *     )
     * @Method("POST")
     */

    public function ajaxAjaxFieldPhotoUpload(Request $request)
    {
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $file = $request->files->get('file');
        $brgyNo = $request->get('brgyNo');
        $voterGroup = $request->get('voterGroup');

        if ($file == null || empty($brgyNo) || empty($voterGroup)) {
            return new JsonResponse('Please supply all the required information.', 404);
        }

        $displayName = $file->getClientOriginalName();
        $imgRoot = __DIR__ . '/../../../web/uploads/field-images/';
        $userDir = $imgRoot . $user->getUsername();
        $currDate = date('Y-m-d');
        $batchDir = $userDir . '/' . $brgyNo . '/' . $voterGroup;

        $entity = $em->getRepository("AppBundle:FieldUploadHdr")->findOneBy([
            'username' => $user->getUsername(),
            'voterGroup' => $voterGroup,
            'brgyNo' => $brgyNo,
        ]);

        if (!$entity) {
            //create entity

            $entity = new FieldUploadHdr();
            $entity->setUsername($user->getUsername());
            $entity->setUploadDate($currDate);
            $entity->setMunicipalityName($user->getDescription());
            $entity->setBrgyNo($brgyNo);
            $entity->setVoterGroup($voterGroup);
            $entity->setCreatedAt(new \DateTime());
            $entity->setStatus('A');

            $em->persist($entity);
            $em->flush();
        }

        $fileEntity = $em->getRepository("AppBundle:FieldUploadDtl")->findOneBy([
            'hdrId' => $entity->getId(),
            'filename' => $displayName,
        ]);

        if (!$fileEntity) {
            $fileEntity = new FieldUploadDtl();
            $fileEntity->setHdrId($entity->getId());
            $fileEntity->setFilename($displayName);
            $fileEntity->setFileDisplayName($displayName);
            $fileEntity->setCreatedAt(new \DateTime());
            $fileEntity->setCreatedBy($user->getUsername());
            $fileEntity->setStatus('A');

            $em->persist($fileEntity);
            $em->flush();
        }

        if (!file_exists($userDir)) {
            mkdir($userDir, 0777, true);
        }

        if (!file_exists($batchDir)) {
            mkdir($batchDir, 0777, true);
        }

        $imagePath = $batchDir . '/' . $displayName;
        $tmpName = $file->getRealPath();

        $this->compress($tmpName, $imagePath, 50);

        return new JsonResponse(['message' => 'ok']);

        // $em = $this->getDoctrine()->getManager();
        // $projectVoter = $em->getRepository("AppBundle:ProjectVoter")
        //     ->findOneBy(['proId' => $proId, 'proVoterId' => $proVoterId]);

        // if (!$projectVoter) {
        //     return new JsonResponse(['message' => 'not found'], 404);
        // }

        // if ($projectVoter->getGeneratedIdNo() == null || $projectVoter->getGeneratedIdNo() == '') {
        //     return new JsonResponse(['message' => 'Please generate id'], 400);
        // }

        // $images = $request->files->get('files');

        // $filename = $proId . '_' . $projectVoter->getGeneratedIdNo() . '.jpg';
        // $imgRoot = __DIR__ . '/../../../web/uploads/images/';patch
        // $imagePath = $imgRoot . $filename;

        // foreach ($images as $image) {
        //     $tmpName = $image->getRealPath();
        //     $this->compress($tmpName, $imagePath, 30);
        // }

        // $projectVoter->setHasPhoto(1);
        // $projectVoter->setDidChanged(1);
        // $projectVoter->setPhotoAt(new \DateTime());
        // $projectVoter->setUpdatedAt(new \DateTime());
        // $projectVoter->setUpdatedBy($user->getUsername());

        // $em->flush();
        // $em->clear();

        // return new JsonResponse(null, 200);
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
     * @Route("/ajax_datatable_field_upload", name="ajax_datatable_field_upload", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return JsonResponse
     */

    public function ajaxGetDatatableFieldUploadAction(Request $request)
    {
        $columns = array(
            0 => "h.id",
            1 => "h.username",
            2 => "h.municipality_name",
            3 => "h.upload_date",
        );

        $sWhere = "";

        $select['m.name'] = $request->get("municipalityName");
        $select['h.voter_group'] = $request->get("voterGroup");

        foreach ($select as $key => $value) {
            $searchValue = $select[$key];
            if ($searchValue != null || !empty($searchValue)) {

                if ($key == 'h.voter_group' ) {
                    $sWhere .= "AND ( {$key} = '{$searchValue}' ) ";
                }else{
                    $sWhere .= " AND " . $key . ' LIKE "%' . $searchValue . '%" ';
                }
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

        $sql = "SELECT COALESCE( COUNT(b.brgy_code),0)
                FROM psw_barangay b 
                INNER JOIN psw_municipality m 
                ON m.municipality_code = b.municipality_code AND m.province_code = 53
                LEFT JOIN tbl_field_upload_hdr h 
                ON m.name = h.municipality_name AND b.brgy_no = h.brgy_no";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE( COUNT(b.brgy_code),0)
                FROM psw_barangay b 
                INNER JOIN psw_municipality m 
                ON m.municipality_code = b.municipality_code AND m.province_code = 53
                LEFT JOIN tbl_field_upload_hdr h 
                ON m.name = h.municipality_name AND b.brgy_no = h.brgy_no
                WHERE 1 ";

        $sql .= $sWhere . ' ';
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        // $sql = "SELECT h.*,b.name as barangay_name , b.total_precincts
        //         FROM tbl_field_upload_hdr h
        //         INNER JOIN psw_municipality m ON m.province_code = 53 AND m.name = h.municipality_name
        //         INNER JOIN psw_barangay b ON m.municipality_code = b.municipality_code AND b.brgy_no = h.brgy_no
        //         WHERE 1 " . $sWhere . ' ORDER BY  h.municipality_name ASC, b.name ASC ' . " LIMIT {$length} OFFSET {$start} ";

        // $sql = "SELECT  h.*,b.name as barangay_name , b.total_precincts
        //         FROM psw_barangay b
        //         INNER JOIN psw_municipality m
        //         ON m.municipality_code = b.municipality_code AND m.province_code = 53
        //         LEFT JOIN tbl_field_upload_hdr h
        //         ON m.name = h.municipality_name AND b.brgy_no = h.brgy_no
        //         WHERE 1 " . $sWhere . ' ORDER BY  h.municipality_name ASC, b.name ASC ' . " LIMIT {$length} OFFSET {$start}
        //         ";

        $sql = "SELECT  h.*,b.name as barangay_name , b.total_precincts
                FROM psw_barangay b 
                INNER JOIN psw_municipality m 
                ON m.municipality_code = b.municipality_code AND m.province_code = 53
                LEFT JOIN tbl_field_upload_hdr h 
                ON m.name = h.municipality_name AND b.brgy_no = h.brgy_no
                WHERE 1 " . $sWhere . ' ORDER BY  m.name ASC, b.name ASC ' . " LIMIT {$length} OFFSET {$start}";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        foreach ($data as &$row) {
            $sql = "SELECT COUNT(*) AS total_photos,
                    COALESCE(COUNT(CASE WHEN pro_id_code IS NOT NULL AND pro_id_code <> ''  then 1 end),0) as total_linked,
                    COALESCE(COUNT(CASE WHEN generated_id_no IS NOT NULL AND generated_id_no <> '' then 1 end),0) as total_linked_photo,
                    COALESCE(COUNT(CASE WHEN (pro_voter_id IS NULL OR pro_voter_id = '' ) AND (is_not_found IS NULL OR is_not_found <> 1 ) AND (is_duplicate IS NULL OR is_duplicate <> 1 ) then 1 end),0) as total_unlinked_photo
                    FROM tbl_field_upload_dtl WHERE hdr_id = ? ";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $row['id']);
            $stmt->execute();

            $counts =  $stmt->fetch(\PDO::FETCH_ASSOC);

            $row['total_photos'] = $counts['total_photos'];
            $row['total_linked'] = $counts['total_linked'];
            $row['total_linked_photo'] = $counts['total_linked_photo'];
            $row['total_unlinked_photo'] = $counts['total_unlinked_photo'];

            $sql = "SELECT COUNT(*) AS total_linked,
                    COALESCE(COUNT(CASE WHEN pv.has_id = 1  then 1 end),0) as total_has_id,
                    COALESCE(COUNT(CASE WHEN pv.has_photo then 1 end),0) as total_has_photo
                    FROM tbl_field_upload_dtl ud INNER JOIN tbl_project_voter pv On ud.pro_voter_id = pv.pro_voter_id 
                    WHERE hdr_id = ? ";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $row['id']);
            $stmt->execute();
            $counts =  $stmt->fetch(\PDO::FETCH_ASSOC);

            $row['total_has_id'] = $counts['total_has_id'];
            $row['total_has_photo'] = $counts['total_has_photo'];
        }

        // foreach ($data as &$row) {
        //     $sql = "SELECT 
        //             COALESCE(COUNT(CASE WHEN pv.has_photo = 1 then 1 end),0) as total_linked_photo,
        //             COALESCE(COUNT(CASE WHEN pv.has_id = 1 then 1 end),0) as total_linked_id,
        //             COALESCE(COUNT(ud.id),0) AS total_linked
        //             FROM tbl_field_upload_dtl ud 
        //             INNER JOIN tbl_project_voter pv on pv.pro_id_code = ud.pro_id_code 
        //             WHERE ud.hdr_id = ? ";

        //     $stmt = $em->getConnection()->prepare($sql);
        //     $stmt->bindValue(1, $row['id']);
        //     $stmt->execute();

        //     $counts = $stmt->fetch(\PDO::FETCH_ASSOC);

        //     $row['total_linked_photo'] = $counts['total_linked_photo'];
        //     $row['total_linked_id'] = $counts['total_linked_id'];
        //     $row['total_linked'] = $counts['total_linked'];
        // }

        
        $sql = "SELECT h.*, b.name AS barangay_name , b.total_precincts
        FROM psw_barangay b 
        INNER JOIN psw_municipality m 
        ON m.municipality_code = b.municipality_code AND m.province_code = 53
        LEFT JOIN tbl_field_upload_hdr h 
        ON m.name = h.municipality_name AND b.brgy_no = h.brgy_no
        WHERE m.name = ? 
        AND b.brgy_no NOT IN 
        (SELECT hh.brgy_no FROM tbl_field_upload_hdr hh WHERE hh.municipality_name = ? AND hh.voter_group = ? ) GROUP BY b.name";


        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$select['m.name']);
        $stmt->bindValue(2,$select['m.name']);
        $stmt->bindValue(3,$select['h.voter_group']);
        $stmt->execute();

        $noData = [];

        while ($row2 = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $noData[] = $row2;
        }

        foreach ($noData as &$row2) {
            $row2['total_photos'] = 0;
            $row2['total_linked'] = 0;
            $row2['total_linked_photo'] =0;
            $row2['total_unlinked_photo'] =0;

            $row2['total_has_id'] =0;
            $row2['total_has_photo'] = 0;
        }
     

        $data = array_merge($data, $noData);

        $barangayName = array_column($data, 'barangay_name');

        array_multisort($barangayName, SORT_ASC, $data);
                
        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        return new JsonResponse($res);
    }

    
    /**
     * @Route("/ajax_datatable_field_upload_items", name="ajax_datatable_field_upload_items", options={"expose"=true})
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

        $select['h.id'] = $request->get("id");
        $hdrId = $request->get('id');
        $select['d.filename'] = $request->get("filename");
        $hasIdNo = intval($request->get('hasGeneratedIdNo'));
        $uploadFilter = $request->get('uploadFilter');


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
                $sWhere .= " AND (d.pro_voter_id IS NULL OR d.pro_voter_id = '' ) AND (d.is_not_found <> 1 OR d.is_not_found IS NULL) ";
                break;
            case "NOT_FOUND" : 
                $sWhere .= " AND d.is_not_found = 1 ";
                break;
            case "DUPLICATE" : 
                    $sWhere .= " AND d.is_duplicate = 1 ";
                    break;
        }

        if($hasIdNo == 1){
            $sWhere .= " AND (d.generated_id_no IS NOT NULL AND d.generated_id_no <> 0 and d.generated_id_no <> '') ";
        }elseif($hasIdNo == 0){
            $sWhere .= " AND (d.generated_id_no IS  NULL OR d.generated_id_no = 0 OR d.generated_id_no = '') ";
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

        $sql = "SELECT COALESCE( COUNT(d.id),0)
                FROM tbl_field_upload_dtl  d
                INNER JOIN tbl_field_upload_hdr h
                ON h.id  = d.hdr_id 
                WHERE h.id = {$hdrId} ";

        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE( COUNT(d.id),0)
                FROM tbl_field_upload_dtl d 
                INNER JOIN tbl_field_upload_hdr h
                ON h.id  = d.hdr_id 
                WHERE h.id = {$hdrId} ";

        $sql .= $sWhere . ' ';
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT  h.municipality_name, h.brgy_no, b.name as barangay_name , h.voter_group , d.*
                FROM tbl_field_upload_dtl d 
                INNER JOIN tbl_field_upload_hdr h ON h.id  = d.hdr_id 
                INNER JOIN psw_municipality m ON h.municipality_name = m.name AND m.province_code = 53
                INNER JOIN psw_barangay b ON m.municipality_code = b.municipality_code AND b.brgy_no = h.brgy_no
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
     * @Route("/ajax_get_field_upload_images/{hdrId}",
     *       name="ajax_get_field_upload_images",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetFieldUploadImages($hdrId)
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository("AppBundle:FieldUploadDtl")
            ->findBy(['hdrId' => $hdrId],['filename' => 'ASC']);

        $serializer = $this->get("serializer");
        $entities = $serializer->normalize($entities);

        return new JsonResponse($entities);
    }

    /**
     * @Route("/photo/{id}",
     *   name="ajax_get_field_upload_photo",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxGetFieldUploadPhotoAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:FieldUploadDtl")
            ->find($id);

        $rootDir = __DIR__ . '/../../../web/uploads/field-images/';

        if (!$entity) {
           
            $imagePath = $rootDir . 'default.jpg';
    
            $response = new BinaryFileResponse($imagePath);
            $response->headers->set('Content-Type', 'image/jpeg');

            return $response;
        }

        $hdr = $em->getRepository("AppBundle:FieldUploadHdr")
            ->find($entity->getHdrId());

        $rootDir = __DIR__ . '/../../../web/uploads/field-images/';

        $uploadDate = $entity->getCreatedAt()->format('Y-m-d');

        $imagePath = $rootDir . '/' . $entity->getCreatedBy() . '/' . $hdr->getBrgyNo() . '/' . $hdr->getVoterGroup() . '/' . $entity->getFilename();

        if (!file_exists($imagePath)) {
            $imagePath = $rootDir . 'default.jpg';
        }

        $response = new BinaryFileResponse($imagePath);
        $response->headers->set('Content-Type', 'image/jpeg');

        return $response;
    }


     /**
     * @Route("/item_detail/{id}",
     *   name="ajax_patch_photo_upload_item",
     *   options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchFieldUploadPhotoAction($id, Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:FieldUploadDtl")
            ->find($id);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $fileDisplayName = $request->get('fileDisplayName');
        
        $proVoterId = $request->get('proVoterId');
        $proIdCode = $request->get('proIdCode');
        $voterName = $request->get('voterName');
        $cellphone = $request->get('cellphone');
        $isNotFound = $request->get('isNotFound');
        $isDuplicate = $request->get('isDuplicate');
        $remarks = $request->get('remarks');

        $hdr = $em->getRepository("AppBundle:FieldUploadHdr")
            ->find($entity->getHdrId());

        // $rootDir = __DIR__ . '/../../../web/uploads/field-images/';

        // $imagePath = $rootDir . '/' . $entity->getCreatedBy() . '/' . $hdr->getBrgyNo() . '/' . $hdr->getVoterGroup() . '/' . $entity->getFilename();
        // $newImagePath = $rootDir . '/' . $entity->getCreatedBy() . '/' . $hdr->getBrgyNo() . '/' . $hdr->getVoterGroup() . '/' . $filename;
        
        
        if(!empty($fileDisplayName)){
            $entity->setFileDisplayName($fileDisplayName);
        }
        
        if(!empty($proIdCode)){
            $entity->setProIdCode($proIdCode);
        }

        if(!empty($proVoterId) && $proVoterId != 0){
            $entity->setProVoterId($proVoterId);
        }else{
            $entity->setProVoterId(null);
        }

        $entity->setIsNotFound(empty($isNotFound) ? 0 : $isNotFound);
        $entity->setIsDuplicate(empty($isDuplicate) ? 0 : $isDuplicate);
        $entity->setRemarks($remarks);

        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }

        if(!empty($proVoterId)){
            $generatedIdNo = $this->_generateIdNo(3, $proVoterId);

            if($generatedIdNo != null){
                $entity->setGeneratedIdNo($generatedIdNo);
            }

        

            $voter = $em->getRepository("AppBundle:ProjectVoter")
                        ->find($proVoterId);

            if($voter && !empty($cellphone)){
                $voter->setCellphone($cellphone);
                $em->flush();
            }
        }
        
        if(!empty($voterName)){
            $entity->setDisplayName($voterName);
        }
        
        $em->flush();

        // if (file_exists($imagePath)) {
        //     rename($imagePath, $newImagePath);
        // }
        
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
     * @Route("/item_detail/{id}",
     *   name="ajax_get_field_upload_item_detail",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxGetFieldItemDetailAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:FieldUploadDtl")
            ->find($id);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $serializer = $this->get("serializer");
        $entity = $serializer->normalize($entity);

        return new JsonResponse($entity);
    }


    /**
     * @Route("/album/{id}",
     *   name="ajax_delete_field_upload",
     *   options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteFieldUploadAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:FieldUploadHdr")
            ->find($id);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $images = $em->getRepository("AppBundle:FieldUploadDtl")
            ->findBy([
                'hdrId' => $entity->getId(),
            ]);

        $rootDir = __DIR__ . '/../../../web/uploads/field-images/';

        foreach ($images as $img) {
            $imagePath = $rootDir . '/' . $img->getCreatedBy() . '/' . $entity->getBrgyNo() .  '/' .  $entity->getVoterGroup() . '/' . $img->getFilename();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            $em->remove($img);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null);
    }

    
    /**
     * @Route("/album/item/{id}",
     *   name="ajax_delete_field_upload_item",
     *   options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteFieldUploadItemAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:FieldUploadDtl")
            ->find($id);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $header = $em->getRepository("AppBundle:FieldUploadHdr")
                     ->find($entity->getHdrId());

        if (!$header) {
            return new JsonResponse(null, 404);
        }

        $rootDir = __DIR__ . '/../../../web/uploads/field-images/';

        $imagePath = $rootDir . '/' . $entity->getCreatedBy() . '/' . $header->getBrgyNo() .  '/' .  $header->getVoterGroup() . '/' . $entity->getFilename();
     
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null);
    }

    /**
     * @Route("/download-photo-album/{id}",
     *   name="ajax_get_download_photo_album",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxDownloadPhotoAlbumAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:FieldUploadHdr")
            ->find($id);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $rootDir = __DIR__ . '/../../../web/uploads/field-images/';
        $imagePath = $rootDir . '/' . $entity->getUsername() . '/' . $entity->getBrgyNo() . '/' . $entity->getVoterGroup() . '/';

        $realImagePath = realpath($imagePath);

        $date = date('Y-m-d');
        $zip = new \ZipArchive();

        $zipFile = $realImagePath . '/' . $entity->getBrgyNo() . '_' . $entity->getVoterGroup() . '_' . $date . '.zip';
        $zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($realImagePath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($realImagePath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();

        if (!file_exists($zipFile)) {
            return new JsonResponse(['message' => 'not found'], 404);
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($zipFile));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);

        unlink($zipFile);
    }

    /**
    * @Route("/ajax_get_field_upload_no_id", 
    *   name="ajax_get_field_upload_no_id",
    *   options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetFieldUploadNoId(Request $request){
        $em = $this->getDoctrine()->getManager();
        $municipalityName = $request->get('municipalityName');
        $brgyNo = $request->get('brgyNo');
        
        $sql = "SELECT * FROM tbl_field_upload_dtl ud 
                INNER JOIN tbl_field_upload_hdr uh ON uh.id = ud.hdr_id 
                INNER JOIN tbl_project_voter pv ON ud.pro_voter_id = pv.pro_voter_id
                WHERE pv.has_photo = 1 AND pv.has_id <> 1
                AND uh.municipality_name = ? AND uh.brgy_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$municipalityName);
        $stmt->bindValue(2,$brgyNo);
        $stmt->execute();
        
        $data = array();
        
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        $em->clear();

        return new JsonResponse($data);
    }
    
    /**
    * @Route("/ajax_get_rename_no_file_extension", 
    *   name="ajax_get_rename_no_file_extension",
    *   options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxRenameNoFileExtension(Request $request){
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM tbl_field_upload_dtl ud WHERE filename NOT LIKE '%.jpg' AND filename NOT LIKE '%.jpeg' AND filename NOT LIKE '%.png' LIMIT 500";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        
        $data = array();
        
        $rootDir = __DIR__ . '/../../../web/uploads/field-images/';

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $entity = $em->getRepository("AppBundle:FieldUploadDtl")
                         ->find($row['id']);

            $hdr = $em->getRepository("AppBundle:FieldUploadHdr")
                      ->find($entity->getHdrId());

            $imagePath = $rootDir . '/' . $entity->getCreatedBy() . '/' . $hdr->getBrgyNo() . '/' . $hdr->getVoterGroup() . '/' . $entity->getFilename();
            $newImagePath = $rootDir . '/' . $entity->getCreatedBy() . '/' . $hdr->getBrgyNo() . '/' . $hdr->getVoterGroup() . '/' . $entity->getFilename() . '.jpg' ;

            if (file_exists($imagePath)) {
                rename($imagePath, $newImagePath);
            }
            
            $entity->setFilename($entity->getFilename() . '.jpg');
            $em->flush();
            
            $data[] = $row;
        }

        $em->clear();

        return new JsonResponse($data);
    }
    
    /**
    * @Route("/ajax_get_rename_with_spaces", 
    *   name="ajax_get_rename_with_spaces",
    *   options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxRenameWithSpaces(Request $request){
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM tbl_field_upload_dtl WHERE filename LIKE '%	.jpg'";

        $stmt = $em->getConnection()->query($sql);
        
        $data = array();
        
        $rootDir = __DIR__ . '/../../../web/uploads/field-images/';

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $entity = $em->getRepository("AppBundle:FieldUploadDtl")
                         ->find($row['id']);

            $hdr = $em->getRepository("AppBundle:FieldUploadHdr")
                      ->find($entity->getHdrId());

            $tempName = str_replace('.jpg','', $entity->getFilename());
            $tempName = trim($tempName) . '.jpg';
      
            $imagePath = $rootDir . '/' . $entity->getCreatedBy() . '/' . $hdr->getBrgyNo() . '/' . $hdr->getVoterGroup() . '/' . $entity->getFilename();
            $newImagePath = $rootDir . '/' . $entity->getCreatedBy() . '/' . $hdr->getBrgyNo() . '/' . $hdr->getVoterGroup() . '/' . $tempName;

            if (file_exists($imagePath)) {
                rename($imagePath, $newImagePath);
             }

            $entity->setFilename($tempName);
            $em->flush();

            $data[] = $row;
        }

        $em->clear();

        return new JsonResponse($data);
    }


     /**
     * @Route("/ajax_select2_upload_reason",
     *       name="ajax_select2_upload_reason",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Reason(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT remarks FROM tbl_field_upload_dtl ud WHERE ud.remarks LIKE ? ORDER BY ud.remarks ASC LIMIT 30";
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
     * @Route("/ajax_upload_generate_summary",
     *       name="ajax_upload_generate_summary",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxUploadGenerateSummary(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $municipalityCodes = [
            '5301',
            '5302',
            '5303',
            '5304',
            '5305',
            '5306',
            '5307',
            '5308',
            '5309',
            '5310',
            '5311',
            '5312',
            '5313',
            '5314',
            '5315',
            '5317',
            '5318',
            '5319',
            '5320',
            '5321',
            '5322',
            '5323',
            '5324'
        ];
        
        $data = [];

        foreach($municipalityCodes as $municipalityCode ){
            $data = $this->generateSummaryByMunicipality($municipalityCode);
        }
        
        return new JsonResponse($data);
    }

    
    /**
     * @Route("/ajax_upload_generate_summary_by_municipality/{municipalityCode}",
     *       name="ajax_upload_generate_summary_by_municipality",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxUploadGenerateSummaryByMunicipality(Request $request,$municipalityCode)
    {
       
        $data = $this->generateSummaryByMunicipality($municipalityCode);
        return new JsonResponse($data);
    }

    private function generateSummaryByMunicipality($municipalityCode){
        $em = $this->getDoctrine()->getManager();

        $voterGroups = ['LGC','LOPP','LPPP','LPPP1','LPPP2','LPPP3','JPM'];
        
        $data = [];

        foreach($voterGroups as $voterGroup ){
            $data = $this->generateSummary($municipalityCode,$voterGroup);
        }
        
      $data;
    }

    private function generateSummary($municipalityCode,$voterGroup){
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT  h.*,b.name as barangay_name , b.total_precincts
                FROM psw_barangay b 
                INNER JOIN psw_municipality m 
                ON m.municipality_code = b.municipality_code AND m.province_code = 53
                LEFT JOIN tbl_field_upload_hdr h 
                ON m.name = h.municipality_name AND b.brgy_no = h.brgy_no
                WHERE m.municipality_code = ? AND h.voter_group = ? ";
    
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityCode);
        $stmt->bindValue(2, $voterGroup);
        $stmt->execute();

        $data = [];
        $municipalityName = "";

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        foreach($data as &$row){
            
            $sql = "SELECT COUNT(*) AS total_photos,
                    COALESCE(COUNT(CASE WHEN pro_id_code IS NOT NULL AND pro_id_code <> ''  then 1 end),0) as total_linked,
                    COALESCE(COUNT(CASE WHEN generated_id_no IS NOT NULL AND generated_id_no <> '' then 1 end),0) as total_linked_photo,
                    COALESCE(COUNT(CASE WHEN (pro_voter_id IS NULL OR pro_voter_id = '' ) AND (is_not_found IS NULL OR is_not_found <> 1 ) then 1 end),0) as total_unlinked_photo
                    FROM tbl_field_upload_dtl WHERE hdr_id = ? ";
                    
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $row['id']);
            $stmt->execute();

            $counts =  $stmt->fetch(\PDO::FETCH_ASSOC);

            $row['total_photos'] = $counts['total_photos'];
            $row['total_linked'] = $counts['total_linked'];
            $row['total_linked_photo'] = $counts['total_linked_photo'];
            $row['total_unlinked_photo'] = $counts['total_unlinked_photo'];

            $sql = "SELECT COUNT(*) AS total_linked,
                    COALESCE(COUNT(CASE WHEN pv.has_id = 1  then 1 end),0) as total_has_id,
                    COALESCE(COUNT(CASE WHEN pv.has_photo = 1 then 1 end),0) as total_has_photo,
                    COALESCE(COUNT(CASE WHEN pv.has_photo = 1 AND (pv.has_id = 0 OR pv.has_id IS NULL or pv.has_id = '' ) then 1 end),0) as total_for_printing
                    FROM tbl_field_upload_dtl ud INNER JOIN tbl_project_voter pv On ud.pro_voter_id = pv.pro_voter_id 
                    WHERE hdr_id = ? ";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $row['id']);
            $stmt->execute();
            $counts =  $stmt->fetch(\PDO::FETCH_ASSOC);

            $row['total_has_id'] = $counts['total_has_id'];
            $row['total_has_photo'] = $counts['total_has_photo'];
            $row['total_for_printing'] = $counts['total_for_printing'];

            $municipalityName = $row['municipality_name'];
        }

        $sql = "DELETE FROM tbl_photo_upload_summary WHERE municipality_no = ? AND sum_date = ?  AND voter_group = ? ";

        $municipalityNo = str_replace("53","", $municipalityCode);
        $currDate = date('Y-m-d');

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityNo);
        $stmt->bindValue(2, $currDate);
        $stmt->bindValue(3, $voterGroup);
        $stmt->execute();

        foreach($data as $row3){
            $entity = new PhotoUploadSummary();
            $entity->setMunicipalityNo($municipalityNo);
            $entity->setMunicipalityName($row3['municipality_name']);
            $entity->setBrgyNo($row3['brgy_no']);
            $entity->setBrgyName($row3['barangay_name']); 
            $entity->setTotalPrecincts($row3['total_precincts']);
            $entity->setSumDate($currDate);
            $entity->setVoterGroup($voterGroup);
            $entity->setTotalUploads($row3['total_photos']);
            $entity->setTotalLinked($row3['total_linked_photo']);
            $entity->setTotalUnlinked($row3['total_unlinked_photo']);
            $entity->setTotalHasPhoto($row3['total_has_photo']);
            $entity->setTotalHasId($row3['total_has_id']);
            $entity->setTotalForPrinting($row3['total_for_printing']);
            $entity->setStatus('A');

            $em->persist($entity);
            $em->flush();
        }


        $sql = "SELECT h.*, b.name AS barangay_name , b.total_precincts
        FROM psw_barangay b 
        INNER JOIN psw_municipality m 
        ON m.municipality_code = b.municipality_code AND m.province_code = 53
        LEFT JOIN tbl_field_upload_hdr h 
        ON m.name = h.municipality_name AND b.brgy_no = h.brgy_no
        WHERE m.name = ? 
        AND b.brgy_no NOT IN 
        (SELECT hh.brgy_no FROM tbl_field_upload_hdr hh WHERE hh.municipality_name = ? AND hh.voter_group = ? ) GROUP BY b.name";


        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$municipalityName);
        $stmt->bindValue(2,$municipalityName);
        $stmt->bindValue(3,$voterGroup);
        $stmt->execute();

        $noData = [];

        while ($row2 = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $noData[] = $row2;
        }

        foreach ($noData as $row2) {

            $entity = new PhotoUploadSummary();
            $entity->setMunicipalityNo($municipalityNo);
            $entity->setMunicipalityName($row2['municipality_name']);
            $entity->setBrgyNo($row2['brgy_no']);
            $entity->setBrgyName($row2['barangay_name']); 
            $entity->setTotalPrecincts($row2['total_precincts']);
            $entity->setVoterGroup($voterGroup);
            $entity->setSumDate($currDate);
            $entity->setTotalUploads(0);
            $entity->setTotalLinked(0);
            $entity->setTotalUnlinked(0);
            $entity->setTotalHasPhoto(0);
            $entity->setTotalHasId(0);
            $entity->setTotalForPrinting(0);
            $entity->setStatus('A');

            $em->persist($entity);
            $em->flush();
        }
     

        return $data;
    }
}
