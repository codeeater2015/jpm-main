<?php
namespace AppBundle\Controller;

use AppBundle\Entity\ProjectVoter;
use AppBundle\Entity\SendSms;
use AppBundle\Entity\Voter;
use AppBundle\Entity\VoterAssistance;
use AppBundle\Entity\VoterAssistanceSummary;
use AppBundle\Entity\VoterSummary;
use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/voter")
 */

class VoterController extends Controller
{
    const STATUS_ACTIVE = 'A';
    const STATUS_INACTIVE = 'I';
    const STATUS_BLOCKED = 'B';
    const STATUS_PENDING = 'PEN';
    const MODULE_MAIN = "VOTER";

    /**
     * @Route("", name="voter_index", options={"main" = true })
     */

    public function indexAction(Request $request)
    {
        $this->denyAccessUnlessGranted("entrance", self::MODULE_MAIN);

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/voter/index.html.twig', ['user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl]);
    }

    
    /**
     * @Route("/public", name="voter_public", options={"main" = true })
     */

    public function PublicAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');

        return $this->render('template/voter/public.html.twig', ['user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl]);
    }

    /********************************************
     ************ SELECT2 FUNCTIONS *************
     ********************************************/

    /**
     * @Route("/api/select2/province",
     *       name="ajax_select2_province",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Province(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT * FROM psw_province p
                WHERE p.name LIKE ? ";

        $sql .= " ORDER BY p.name ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);

        $stmt->execute();
        $provinces = $stmt->fetchAll();

        if (count($provinces) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($provinces);
    }

    /**
     * @Route("/api/select2/municipality",
     *       name="ajax_select2_municipality",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetMunicipality(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';
        $provinceCode = empty($request->get("provinceCode")) ? 53 : $request->get("provinceCode");

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM psw_municipality m
                WHERE m.province_code = ? AND m.name LIKE ? ORDER BY m.name ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $searchText);

        $stmt->execute();
        $municipalities = $stmt->fetchAll();

        if (count($municipalities) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($municipalities);
    }

    /**
     * @Route("/ajax_select2_elections",
     *       name="ajax_select2_elections",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Elections(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';
        $entities = [];

        $em = $this->getDoctrine()->getManager();
        $sql = "Select * from tbl_election where (elect_name LIKE ? OR ? IS NULL) ORDER BY elect_name DESC";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->bindValue(2, empty($request->get('searchText')) ? null : $request->get("searchText"));
        $stmt->execute();

        $entities = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!$entities) {
            $entities = [];
        }

        $serializer = $this->get("serializer");

        return new JsonResponse($entities);
    }

    /**
     * @Route("/ajax_select2_projects",
     *       name="ajax_select2_projects",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Projects(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';
        $entities = [];

        $em = $this->getDoctrine()->getManager();
        $sql = "Select * from tbl_project where (pro_name LIKE ? OR ? IS NULL) ORDER BY pro_name DESC";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->bindValue(2, empty($request->get('searchText')) ? null : $request->get("searchText"));
        $stmt->execute();

        $entities = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!$entities) {
            $entities = [];
        }

        $serializer = $this->get("serializer");

        return new JsonResponse($entities);
    }

    /**
     * @Route("/ajax_get_active_election",
     *       name="ajax_get_active_election",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function getActiveElection()
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:Election")->findOneBy([
            "status" => self::STATUS_ACTIVE,
        ]);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/api/select2/barangay",
     *       name="ajax_select2_barangay",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetBarangay(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $provinceCode = empty($request->get("provinceCode")) ? 53 : $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT b.* FROM psw_barangay b
                INNER JOIN psw_municipality m ON m.municipality_code = b.municipality_code AND m.province_code = ?
                WHERE m.municipality_no = ? AND b.name LIKE ? ORDER BY b.name ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->bindValue(3, $searchText);

        $stmt->execute();
        $barangays = $stmt->fetchAll();

        if (count($barangays) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($barangays);
    }

    /**
     * @Route("/api/select2/barangay-alt",
     *       name="ajax_select2_barangay_alt",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetBarangayAlt(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $provinceCode = empty($request->get("provinceCode")) ? 53 : $request->get("provinceCode");
        $municipalityName = $request->get("municipalityName");

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT b.* FROM psw_barangay b
                INNER JOIN psw_municipality m ON m.municipality_code = b.municipality_code AND m.province_code = ?
                WHERE m.name = ? AND b.name LIKE ? ORDER BY b.name ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityName);
        $stmt->bindValue(3, $searchText);

        $stmt->execute();
        $barangays = $stmt->fetchAll();

        if (count($barangays) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($barangays);
    }

    /**
     * @Route("/ajax_select2_precinct_no",
     *       name="ajax_select2_precinct_no",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2PrecinctNo(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT v.precinct_no FROM tbl_voter v
                WHERE  (v.precinct_no LIKE ? OR ? IS NULL) ORDER BY v.precinct_no ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->bindValue(2, ($request->get("searchText") == "") ? null : $request->get("searchText"));

        $stmt->execute();
        $municipalities = $stmt->fetchAll();

        if (count($municipalities) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($municipalities);
    }

    /**
     * @Route("/ajax_select2_civil_status",
     *       name="ajax_select2_civil_status",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2CivilStatus(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT v.civil_status FROM tbl_project_voter v
                WHERE  (v.civil_status LIKE ? OR ? IS NULL) ORDER BY v.civil_status ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->bindValue(2, ($request->get("searchText") == "") ? null : $request->get("searchText"));

        $stmt->execute();
        $data = $stmt->fetchAll();

        if (count($data) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_select2_bloodtype",
     *       name="ajax_select2_bloodtype",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Bloodtype(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT v.bloodtype FROM tbl_project_voter v
                WHERE  (v.bloodtype LIKE ? OR ? IS NULL) ORDER BY v.bloodtype ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->bindValue(2, ($request->get("searchText") == "") ? null : $request->get("searchText"));

        $stmt->execute();
        $data = $stmt->fetchAll();

        if (count($data) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_select2_occupation",
     *       name="ajax_select2_occupation",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Occupation(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT v.occupation FROM tbl_project_voter v
                WHERE  (v.occupation LIKE ? OR ? IS NULL) ORDER BY v.occupation ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->bindValue(2, ($request->get("searchText") == "") ? null : $request->get("searchText"));

        $stmt->execute();
        $data = $stmt->fetchAll();

        if (count($data) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_select2_religion",
     *       name="ajax_select2_religion",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Religion(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT v.religion FROM tbl_project_voter v
                WHERE  (v.religion LIKE ? OR ? IS NULL) ORDER BY v.religion ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->bindValue(2, ($request->get("searchText") == "") ? null : $request->get("searchText"));

        $stmt->execute();
        $data = $stmt->fetchAll();

        if (count($data) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_select2_dialect",
     *       name="ajax_select2_dialect",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2Dialect(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT v.dialect FROM tbl_project_voter v
                WHERE  (v.dialect LIKE ? OR ? IS NULL) ORDER BY v.dialect ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->bindValue(2, ($request->get("searchText") == "") ? null : $request->get("searchText"));

        $stmt->execute();
        $data = $stmt->fetchAll();

        if (count($data) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_select2_ip_group",
     *       name="ajax_select2_ip_group",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2IpGroup(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT v.ip_group FROM tbl_project_voter v
                WHERE  (v.ip_group LIKE ? OR ? IS NULL) ORDER BY v.ip_group ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->bindValue(2, ($request->get("searchText") == "") ? null : $request->get("searchText"));

        $stmt->execute();
        $data = $stmt->fetchAll();

        if (count($data) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_select2_voting_center",
     *       name="ajax_select2_voting_center",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2VotingCenter(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $electId = $request->get('electId');
        $provinceCode = $request->get('provinceCode');
        $municipalityNo = $request->get('municipalityNo');
        $brgyNo = $request->get('brgyNo');

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT pv.voting_center FROM tbl_project_voter pv
                WHERE pv.elect_id = ? AND pv.province_code = ? AND pv.municipality_no = ? AND pv.brgy_no = ?
                AND (pv.voting_center LIKE ? OR ? IS NULL) ORDER BY pv.voting_center ASC LIMIT 30";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $electId);
        $stmt->bindValue(2, $provinceCode);
        $stmt->bindValue(3, $municipalityNo);
        $stmt->bindValue(4, $brgyNo);
        $stmt->bindValue(5, $searchText);
        $stmt->bindValue(6, ($request->get("searchText") == "") ? null : $request->get("searchText"));

        $stmt->execute();
        $votingCenters = $stmt->fetchAll();

        if (count($votingCenters) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($votingCenters);
    }

    /**
     * @Route("/upload",
     *       name="ajax_upload_voters",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxUploadVoters(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        if (!$user->getIsAdmin()) {
            return new JsonResponse(null, 401);
        }

        $file = $request->files->get('excelFile');

        $electId = $request->get("electId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");
        $errors = [];

        if (empty($file) || $file == 'null') {
            $errors["excelFile"] = "File cannot be empty.";
        }

        if (empty($electId) || $electId == 'null') {
            $errors['electId'] = 'This value cannot be empty.';
        }

        if (empty($provinceCode) || $provinceCode == 'null') {
            $errors['provinceCode'] = 'This value cannot be empty.';
        }

        if (empty($municipalityNo) || $municipalityNo == 'null') {
            $errors["municipalityNo"] = 'This value cannot be empty.';
        }

        if (empty($brgyNo) || $brgyNo == 'null') {
            $errors["brgyNo"] = 'This value cannot be empty.';
        }

        if (count($errors) > 0) {
            return new JsonResponse($errors, 400);
        }

        $sql = "DELETE FROM tbl_voter WHERE province_code  = ? AND municipality_no = ? AND brgy_no = ? AND elect_id = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->bindValue(3, $brgyNo);
        $stmt->bindValue(4, $electId);
        $stmt->execute();

        $sql = "DELETE FROM tbl_voter_history WHERE province_code = ? AND municipality_no = ? AND brgy_no = ? AND elect_id = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->bindValue(3, $brgyNo);
        $stmt->bindValue(4, $electId);
        $stmt->execute();

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($em, $file, $user, $provinceCode, $municipalityNo, $brgyNo, $electId) {

            $filename = md5(uniqid(rand(), true)) . ".xlsx";
            $fileRoot = __DIR__ . '/../../../web/uploads/';
            $file->move($fileRoot, $filename);

            $reader = ReaderFactory::create(Type::XLSX); // for XLSX files
            $reader->open($fileRoot . $filename);

            $counter = 0;
            $totalRow = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $totalRow++;
                }
            }

            $totalRow--;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {

                    if ($counter > 0) {
                        $sql = "INSERT INTO tbl_voter(
                            voter_no,voter_name,precinct_no,voter_status,address,
                            has_ast,has_a,has_b,has_c,voter_class,voted_2017,created_by,
                            created_at,updated_by,updated_at,remarks,province_code,municipality_no,brgy_no,status,elect_id
                        )
                        VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                        ";

                        $stmt = $em->getConnection()->prepare($sql);
                        $stmt->bindValue(1, $row[1]);
                        $stmt->bindValue(2, $row[2]);
                        $stmt->bindValue(3, $row[3]);
                        $stmt->bindValue(4, $row[4]);
                        $stmt->bindValue(5, $row[5]);
                        $stmt->bindValue(6, empty($row[6]) ? 0 : $row[6]);
                        $stmt->bindValue(7, empty($row[7]) ? 0 : $row[7]);
                        $stmt->bindValue(8, empty($row[8]) ? 0 : $row[8]);
                        $stmt->bindValue(9, empty($row[9]) ? 0 : $row[9]);
                        $stmt->bindValue(10, 0);
                        $stmt->bindValue(11, 0);
                        $stmt->bindValue(12, $user->getUsername());
                        $stmt->bindValue(13, date('Y-m-d H:i:s'));
                        $stmt->bindValue(14, $user->getUsername());
                        $stmt->bindValue(15, date('Y-m-d H:i:s'));
                        $stmt->bindValue(16, "");
                        $stmt->bindValue(17, $provinceCode);
                        $stmt->bindValue(18, $municipalityNo);
                        $stmt->bindValue(19, $brgyNo);
                        $stmt->bindValue(20, self::STATUS_ACTIVE);
                        $stmt->bindValue(21, $electId);
                        $stmt->execute();

                        echo json_encode([
                            'totalRows' => $totalRow,
                            'currentRow' => $counter,
                            'percentage' => (int) (($counter / $totalRow) * 100),
                        ]);

                        ob_flush();
                        flush();
                    }

                    $counter++;
                }
            }
        });
        return $response;
    }

    /**
     * @Route("/upload-voting-status",
     *       name="ajax_upload_voters_voting_status",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxUploadVotersVotingStatus(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        if (!$user->getIsAdmin()) {
            return new JsonResponse(null, 401);
        }

        $file = $request->files->get('excelFile');

        $electId = $request->get("electId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");

        $errors = [];

        if (empty($file) || $file == 'null') {
            $errors["excelFile"] = "File cannot be empty.";
        }

        if (empty($electId) || $electId == 'null') {
            $errors['electId'] = 'This value cannot be empty.';
        }

        if (empty($provinceCode) || $provinceCode == 'null') {
            $errors['provinceCode'] = 'This value cannot be empty.';
        }

        if (empty($municipalityNo) || $municipalityNo == 'null') {
            $errors["municipalityNo"] = 'This value cannot be empty.';
        }

        if (empty($brgyNo) || $brgyNo == 'null') {
            $errors["brgyNo"] = 'This value cannot be empty.';
        }

        if (count($errors) > 0) {
            return new JsonResponse($errors, 400);
        }

        $sql = "UPDATE tbl_voter SET voted_2017 = 0 WHERE province_code = ? AND municipality_no = ? AND brgy_no = ? AND elect_id = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->bindValue(3, $brgyNo);
        $stmt->bindValue(4, $electId);
        $stmt->execute();

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($em, $file, $user, $provinceCode, $municipalityNo, $brgyNo, $electId) {

            $filename = md5(uniqid(rand(), true)) . ".xlsx";
            $fileRoot = __DIR__ . '/../../../web/uploads/';
            $file->move($fileRoot, $filename);

            $reader = ReaderFactory::create(Type::XLSX); // for XLSX files
            $reader->open($fileRoot . $filename);

            $counter = 0;
            $totalRow = 0;

            $totalRow--;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $totalRow++;
                }
            }

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {

                    if ($counter > 0) {
                        $sql = "UPDATE tbl_voter SET voted_2017 = 1 WHERE voter_name = ? AND province_code = ? AND municipality_no = ? AND brgy_no = ? AND elect_id = ? ";

                        $stmt = $em->getConnection()->prepare($sql);
                        $stmt->bindValue(1, $row[2]);
                        $stmt->bindValue(2, $provinceCode);
                        $stmt->bindValue(3, $municipalityNo);
                        $stmt->bindValue(4, $brgyNo);
                        $stmt->bindValue(5, $electId);
                        $stmt->execute();

                        echo json_encode([
                            'totalRows' => $totalRow,
                            'currentRow' => $counter,
                            'percentage' => (int) (($counter / $totalRow) * 100),
                        ]);

                        ob_flush();
                        flush();
                    }

                    $counter++;
                }
            }
        });

        return $response;
    }

    /**
     * @Route("/upload-birthdate",
     *       name="ajax_upload_voters_birthdate",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxUploadVotersBirthdate(Request $request)
    {

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        if (!$user->getIsAdmin()) {
            return new JsonResponse(null, 401);
        }

        $file = $request->files->get('excelFile');

        $electId = $request->get("electId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");

        $errors = [];

        if (empty($file) || $file == 'null') {
            $errors["excelFile"] = "File cannot be empty.";
        }

        if (empty($electId) || $electId == 'null') {
            $errors["electId"] = "This value cannot be empty.";
        }

        if (empty($provinceCode) || $provinceCode == 'null') {
            $errors['provinceCode'] = 'This value cannot be empty.';
        }

        if (empty($municipalityNo) || $municipalityNo == 'null') {
            $errors["municipalityNo"] = 'This value cannot be empty.';
        }

        if (count($errors) > 0) {
            return new JsonResponse($errors, 400);
        }

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($em, $file, $user, $provinceCode, $municipalityNo, $electId) {

            $filename = md5(uniqid(rand(), true)) . ".xlsx";
            $fileRoot = __DIR__ . '/../../../web/uploads/';
            $file->move($fileRoot, $filename);

            $reader = ReaderFactory::create(Type::XLSX); // for XLSX files
            $reader->open($fileRoot . $filename);

            $counter = 0;
            $totalRow = 0;

            $totalRow--;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $totalRow++;
                }
            }

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {

                    if ($counter > 0) {
                        $sql = "UPDATE tbl_voter SET birthdate = ? WHERE voter_name = ? AND province_code = ? AND municipality_no = ? AND elect_id = ? ";

                        $stmt = $em->getConnection()->prepare($sql);
                        $stmt->bindValue(1, $row[3]);
                        $stmt->bindValue(2, $row[2]);
                        $stmt->bindValue(3, $provinceCode);
                        $stmt->bindValue(4, $municipalityNo);
                        $stmt->bindValue(5, $electId);
                        $stmt->execute();

                        echo json_encode([
                            'totalRows' => $totalRow,
                            'currentRow' => $counter,
                            'percentage' => (int) (($counter / $totalRow) * 100),
                        ]);

                        ob_flush();
                        flush();
                    }

                    $counter++;
                }
            }
        });

        return $response;
    }

    /**
     * @Route("/datatable",
     *     name="ajax_datatable_voter",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

    public function datatableVoterAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $filters = array();
        $filters['v.province_code'] = $request->get("provinceCode");
        $filters['v.voter_group'] = $request->get("voterGroup");
        $filters['v.municipality_no'] = $request->get("municipalityNo");
        $filters['v.brgy_no'] = $request->get("brgyNo");
        $filters['v.precinct_no'] = $request->get("precinctNo");

        $filters['v.voter_name'] = $request->get("voterName");
        $filters['v.birthdate'] = $request->get("birthdate");
        $filters['v.cellphone'] = $request->get("cellphone");
        $filters['v.house_form_sub'] = $request->get("recFormSub");
        $filters['v.rec_form_sub'] = $request->get("houseFormSub");
        $filters['v.brgy_cluster'] = $request->get("brgyCluster");
        $filters['v.is_non_voter'] = $request->get("isNonVoter");
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
                if ($field == 'v.voter_id' || $field == 'v.elect_id' || $field == 'v.voter_group'|| $field == 'v.rec_form_sub' || $field == 'v.house_form_sub' || $field == 'v.is_non_voter' ) {
                    $whereStmt .= "{$field} = '{$searchText}' AND ";
                }if ($field == 'v.municipality_no' || $field == 'v.brgy_no' || $field == 'v.precinct_no' || $field == 'v.province_code' || $field == 'v.brgy_cluster' || $field == 'v.voter_group' ) {
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


        // $hasId = intval($request->get('hasId'));
        // $hasPhoto = intval($request->get('hasPhoto'));

        // if($hasId == 0 || $hasId == 1){
        //     $whereStmt .= " AND v.has_id = " . $hasId . " ";
        // }

        // if($hasPhoto == 0 || $hasPhoto == 1){
        //     $whereStmt .= " AND v.has_photo = " . $hasPhoto . " ";
        // }

        // $voterGroup  = $request->get("voterGroup");
        
        // if(!empty($voterGroup)){
        //     if(strtoupper($voterGroup) == 'ALL'){
        //         $whereStmt .= " AND v.voter_group IS NOT NULL AND v.voter_group <> '' ";
        //     }else{
        //         $whereStmt .= " AND v.voter_group = '" .  $voterGroup  . "' ";
        //     }
        // }

        $start = 0;
        $length = 1;

        if (null !== $request->query->get('start') && null !== $request->query->get('length')) {
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(v.pro_voter_id),0) FROM tbl_project_voter v";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(v.pro_voter_id),0) FROM tbl_project_voter v
                WHERE 1 ";

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

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        $em->clear();

        return new JsonResponse($res);
    }

    private function getProjectVoter($proId, $voterId)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(
            [
                "voterId" => $voterId,
                "proId" => $proId,
            ]
        );

        return $entity;
    }

    private function getNetworkNode($voterId)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:VoterNetwork")->findOneBy(['voterId' => $voterId]);

        $node = [
            'node_level' => "",
            'node_id' => 0,
            'parent_node' => 0,
        ];

        if (!$entity) {
            return $node;
        }

        $parent = $this->getParentNode($entity->getParentId());

        if ($parent) {
            $node['parent_node'] = $parent->getNodeLabel();
        }

        $node['node_level'] = $entity->getNodeLevel();
        $node['node_id'] = $entity->getNodeId();

        return $node;
    }

    private function getParentNode($nodeId)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:VoterNetwork")->find($nodeId);

        if ($entity) {
            $parentNode = $this->getParentNode($entity->getParentId());

            if ($parentNode) {
                return $parentNode;
            } else {
                return $entity;
            }

        }

        return false;
    }

    private function getMunicipalities($provinceCode)
    {
        $name = '';
        $code = '';

        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_municipality WHERE province_code = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->execute();

        $municipalities = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $municipalities;
    }

    private function getBarangay($provinceCode, $municipalityCode, $brgyNo)
    {
        $name = '';

        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_barangay WHERE brgy_code LIKE ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode . '%');
        $stmt->execute();

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
     * @Route("/history-datatable/{voterId}",
     *     name="ajax_datatable_voter_history",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

    public function datatableVoterHistoryAction($voterId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $voter = $em->getRepository("AppBundle:Voter")->find($voterId);

        if (!$voter) {
            return new JsonResponse([], 404);
        }

        $filters = array();
        $filters['v.voter_name'] = $request->get("voterName");
        $filters['v.municipality_no'] = $request->get("municipalityNo");
        $filters['v.province_code'] = $request->get("provinceCode");
        $filters['v.brgy_no'] = $request->get("brgyNo");
        $filters['v.precinct_no'] = $request->get("precinctNo");
        $filters['v.voter_id'] = $voterId;

        $columns = array(
            0 => 'v.voter_id',
            1 => 'v.precinct_no',
            2 => 'v.municipality_no',
            3 => 'v.brgy_no',
            4 => 'v.voter_status',
            5 => 'v.voter_class',
            6 => 'v.voted_2017',
            7 => 'v.cellphone_no',
            8 => 'v.created_by',
            9 => 'v.created_at',
        );

        $whereStmt = " AND (";

        foreach ($filters as $field => $searchText) {
            if ($searchText != "") {
                if ($field == 'v.voter_id') {
                    $whereStmt .= "{$field} = '{$searchText}' AND ";
                }if ($field == 'v.province_code' && $field == 'v.municipality_no' || $field == 'v.brgy_no' || $field == 'v.precinct_no') {
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

        $sql = "SELECT COALESCE(count(v.hist_id),0) FROM tbl_voter_history v";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(v.hist_id),0) FROM tbl_voter_history v
                WHERE 1 ";

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT v.*FROM tbl_voter_history v
                WHERE 1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        $provinceCode = $voter->getProvinceCode();
        $municipalities = $this->getMunicipalities($provinceCode);

        if (!empty($provinceCode)) {
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $municipality = [];

                foreach ($municipalities as $mun) {
                    if ($mun['municipality_no'] == $row['municipality_no']) {
                        $municipality = $mun;
                    }
                }

                $row['municipality_name'] = $municipality['name'];
                $row['barangay_name'] = $this->getBarangay($provinceCode, $municipality['municipality_code'], $row['brgy_no']);

                $data[] = $row;
            }
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

    private function getMunicipalityAccessFilter($userId)
    {
        $em = $this->getDoctrine()->getManager();
        $currentDate = date('Y-m-d H:i:s');
        $sql = "SELECT DISTINCT u.municipality_no , u.province_code FROM tbl_user_access u WHERE u.user_id = ? AND u.valid_until > ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $userId);
        $stmt->bindValue(2, $currentDate);
        $stmt->execute();

        $permissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($permissions) <= 0) {
            $permissions = [];
        }

        $sql = 'AND (';

        foreach ($permissions as $permission) {
            $municipalityNo = $permission['municipality_no'];
            $provinceCode = $permission['province_code'];

            $sql .= "(v.municipality_no = {$municipalityNo} AND v.province_code = {$provinceCode}) OR ";
        }

        $sql = rtrim($sql, 'OR ');
        $sql .= ")";

        if ($sql == " AND ()") {
            $sql = " AND (v.municipality_no IS NULL AND v.province_code IS NULL)";
        }

        return $sql;
    }

    private function getRecordAccessFilter($userId)
    {

        $em = $this->getDoctrine()->getManager();
        $currentDate = date('Y-m-d H:i:s');
        $sql = "SELECT u.municipality_no, u.brgy_no, u.province_code FROM tbl_user_access u WHERE u.user_id = ? AND u.valid_until > ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $userId);
        $stmt->bindValue(2, $currentDate);
        $stmt->execute();

        $permissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($permissions) <= 0) {
            $permissions = [];
        }

        $sql = ' AND (';

        foreach ($permissions as $permission) {
            $municipalityNo = $permission['municipality_no'];
            $brgyNo = $permission['brgy_no'];
            $provinceCode = $permission['province_code'];

            $sql .= "(v.municipality_no = {$municipalityNo} AND v.brgy_no = {$brgyNo} AND v.province_code = {$provinceCode}) OR";
        }

        $sql = rtrim($sql, 'OR');
        $sql .= ")";

        if ($sql == " AND ()") {
            $sql = " AND (v.municipality_no IS NULL AND v.brgy_no IS NULL)";
        }

        return $sql;
    }

    private function isAllowed($provinceCode, $municipalityNo, $brgyNo)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        if ($user->getIsAdmin()) {
            return true;
        }

        $currentDate = date('Y-m-d H:i:s');

        $sql = "SELECT DISTINCT u.municipality_no, u.brgy_no FROM tbl_user_access u
        WHERE u.user_id = ? AND u.valid_until > ? AND u.province_code = ? AND u.municipality_no = ? AND u.brgy_no = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $user->getId());
        $stmt->bindValue(2, $currentDate);
        $stmt->bindValue(3, $provinceCode);
        $stmt->bindValue(4, $municipalityNo);
        $stmt->bindValue(5, $brgyNo);
        $stmt->execute();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return true;
        }

        return false;
    }

    /**
     * @Route("/ajax_get_voter/{voterId}",
     *       name="ajax_get_voter",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetVoter($voterId)
    {
        $em = $this->getDoctrine()->getManager();
        $sql = "Select v.*, b.name AS barangay_name, m.name AS municipality_name FROM tbl_voter v
                INNER JOIN psw_municipality m ON m.municipality_no = v.municipality_no AND m.province_code = v.province_code
                INNER JOIN psw_barangay b ON b.brgy_no = v.brgy_no AND b.municipality_code = m.municipality_code
                WHERE v.voter_id = ?";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $voterId);
        $stmt->execute();

        $voter = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$voter) {
            return new JsonResponse(null, 404);
        }

        $voter = [
            'voterId' => $voter['voter_id'],
            'voterName' => $voter['voter_name'],
            'municipalityNo' => $voter['municipality_no'],
            'provinceCode' => $voter['province_code'],
            'brgyNo' => $voter['brgy_no'],
            'address' => $voter['address'],
            'precinctNo' => $voter['precinct_no'],
            'voterNo' => $voter['voter_no'],
            'cellphoneNo' => $voter['cellphone_no'],
            'voterClass' => $voter['voter_class'],
            'voterStatus' => $voter['voter_status'],
            'voted2017' => $voter['voted_2017'],
            'createdAt' => $voter['created_at'],
            'createdBy' => $voter['created_by'],
            'updatedAt' => $voter['updated_at'],
            'updatedBy' => $voter['updated_by'],
            'hasAst' => $voter['has_ast'],
            'hasA' => $voter['has_a'],
            'hasB' => $voter['has_b'],
            'hasC' => $voter['has_c'],
            'remarks' => $voter['remarks'],
            'status' => $voter['status'],
            'is1' => $voter['is_1'],
            'is2' => $voter['is_2'],
            'is3' => $voter['is_3'],
            'is4' => $voter['is_4'],
            'is5' => $voter['is_5'],
            'is6' => $voter['is_6'],
            'is7' => $voter['is_7'],
            'onNetwork' => $voter['on_network'],
            'category' => $voter['category'],
            'barangayName' => $voter['barangay_name'],
            'municipalityName' => $voter['municipality_name'],
            'birthdate' => $voter['birthdate'],
            'organization' => $voter['organization'],
            'position' => $voter['position'],
        ];

        return new JsonResponse($voter);
    }

    /**
     * @Route("/ajax_get_project_voter/{proId}/{proVoterId}",
     *       name="ajax_get_project_voter",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProjectVoter($proId, $proVoterId)
    {
        $em = $this->getDoctrine()->getManager();
        $proVoter = $em->getRepository("AppBundle:ProjectVoter")
            ->findOneBy([
                'proId' => $proId,
                'proVoterId' => $proVoterId,
            ]);

        if (!$proVoter) {
            return new JsonResponse(['message' => 'not found']);
        }

        $serializer = $this->get("serializer");
        $proVoter = $serializer->normalize($proVoter);
        $proVoter['cellphoneNo'] = $proVoter['cellphone'];
        $lgc = $this->getLGC($proVoter['municipalityNo'], $proVoter['brgyNo']);
        $proVoter['lgc'] = [
            'voter_name' => $lgc['voter_name'],
            'cellphone' => $lgc['cellphone']
        ];

        return new JsonResponse($proVoter);
    }

    private function getLGC($municipalityNo, $barangayNo){
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT pv.* FROM tbl_location_assignment l INNER JOIN tbl_project_voter pv ON pv.pro_id_code = l.pro_id_code 
        WHERE l.municipality_no = ? AND l.barangay_no = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityNo);
        $stmt->bindValue(2, $barangayNo);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row == null ? ['voter_name' => "No LGC", 'cellphone' => 'No LGC'] : $row;
    }

    private function getLastEvent($proIdCode)
    {
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM tbl_project_event_detail ed
        INNER JOIN tbl_project_event_header hd ON hd.event_id = ed.event_id
        INNER JOIN tbl_project_voter pv ON ed.pro_voter_id = pv.pro_voter_id
        WHERE pv.pro_id_code = ? AND ed.has_attended = 1 AND hd.status <> 'A' ORDER BY ed.attended_at DESC ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $proIdCode);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row == null ? ['event_name' => "No Event"] : $row;
    }

    /**
     * @Route("/ajax_get_project_voter_alt/{proId}/{proIdCode}",
     *       name="ajax_get_project_voter_alt",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProjectVoterAlt($proId, $proIdCode)
    {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository("AppBundle:Project")->find($proId);
        $sql = "SELECT * FROM tbl_project_voter WHERE pro_id_code = ? AND pro_id = ? AND elect_id = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $proIdCode);
        $stmt->bindValue(2, $proId);
        $stmt->bindValue(3, 3);
        $stmt->execute();

        $projectVoter = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$projectVoter) {
            return new JsonResponse(null, 404);
        }

        return new JsonResponse($projectVoter);
    }

    /**
     * @Route("/ajax_apply_access_code/{accessCode}",
     *       name="ajax_apply_access_code",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function applyCodeAction($accessCode)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        if ($user->getAccessCode() == $accessCode) {
            $permissions = $em->getRepository("AppBundle:UserAccess")->findBy(['userId' => $user->getId()]);
            if (count($permissions) > 0) {
                foreach ($permissions as $permission) {
                    $permission->setValidUntil($user->getValidUntil());
                }
            }
            $em->flush();
        } else {
            return new JsonResponse(['message' => 'Invalid access code'], 400);
        }

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_activate_access_code/{id}",
     *       name="ajax_activate_access_code",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function applyActivateAccessCodeAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($id);

        $permissions = $em->getRepository("AppBundle:UserAccess")->findBy(['userId' => $user->getId()]);

        if (count($permissions) > 0) {
            foreach ($permissions as $permission) {
                $permission->setValidUntil($user->getValidUntil());
            }
        }

        $em->flush();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_clear_access/{id}",
     *       name="ajax_clear_access",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function clearAccessAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("AppBundle:User")->find($id);

        $permissions = $em->getRepository("AppBundle:UserAccess")->findBy(['userId' => $user->getId()]);
        if (count($permissions) > 0) {
            foreach ($permissions as $permission) {
                $em->remove($permission);
            }
        }

        $em->flush();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_get_voter_status",
     *       name="ajax_get_voter_status",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetVoterStatus()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository("AppBundle:VoterStatus")->findAll();

        if (!$entities) {
            return new JsonResponse([], 200);
        }

        $serializer = $this->get("serializer");

        return new JsonResponse($serializer->normalize($entities), 200);
    }

    /**
     * @Route("/ajax_patch_project_voter/{proId}/{proVoterId}",
     *     name="ajax_patch_project_voter",
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
        $proVoter->setDidChanged(1);
        $proVoter->setToSend(1);
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
     * @Route("/ajax_update_voter_summary_by_municipality/{electId}/{provinceCode}/{municipalityNo}",
     *     name="ajax_update_voter_summary_by_municipality",
     *    options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxUpdateVoterSummaryByMunicipality($electId, $provinceCode, $municipalityNo)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM psw_barangay WHERE municipality_code = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode . $municipalityNo);
        $stmt->execute();

        $barangays = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($barangays as $barangay) {
            $this->updateVoterSummary($electId, $provinceCode, $municipalityNo, $barangay['brgy_no']);
        }

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_update_voter_summary/{electId}/{provinceCode}/{municipalityNo}/{brgyNo}",
     *     name="ajax_update_voter_summary",
     *    options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxUpdateVoterSummary($electId, $provinceCode, $municipalityNo, $brgyNo)
    {
        $this->updateVoterSummary($electId, $provinceCode, $municipalityNo, $brgyNo);

        return new JsonResponse(null, 200);
    }

    private function updateVoterSummary($electId, $provinceCode, $municipalityNo, $brgyNo)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM tbl_project";
        $stmt = $em->getConnection()->query($sql);
        $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($projects as $project) {

            $sql = "DELETE FROM tbl_voter_summary WHERE province_code = ? AND municipality_no = ? AND brgy_no = ? AND elect_id = ? AND pro_id = ? ";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $provinceCode);
            $stmt->bindValue(2, $municipalityNo);
            $stmt->bindValue(3, $brgyNo);
            $stmt->bindValue(4, $electId);
            $stmt->bindValue(5, $project['pro_id']);
            $stmt->execute();

            $sql = "SELECT DISTINCT brgy_no,precinct_no FROM tbl_voter WHERE province_code = ? AND municipality_no = ? AND brgy_no = ? AND elect_id = ?";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $provinceCode);
            $stmt->bindValue(2, $municipalityNo);
            $stmt->bindValue(3, $brgyNo);
            $stmt->bindValue(4, $electId);

            $stmt->execute();
            $data = [];

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }

            foreach ($data as $row) {

                $sql = "SELECT COALESCE(COUNT(voter_id),0) FROM tbl_voter WHERE province_code = ? AND municipality_no = ? AND brgy_no = ? AND precinct_no = ? AND elect_id = ?";
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $provinceCode);
                $stmt->bindValue(2, $municipalityNo);
                $stmt->bindValue(3, $brgyNo);
                $stmt->bindValue(4, $row['precinct_no']);
                $stmt->bindValue(5, $electId);
                $stmt->execute();

                $totalVoters = $stmt->fetchColumn();

                $sql = "SELECT COALESCE(COUNT(voter_id),0) FROM tbl_voter WHERE province_code = ? AND municipality_no = ? AND brgy_no = ? AND precinct_no = ? AND voted_2017 = 1 AND elect_id = ? ";
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $provinceCode);
                $stmt->bindValue(2, $municipalityNo);
                $stmt->bindValue(3, $brgyNo);
                $stmt->bindValue(4, $row['precinct_no']);
                $stmt->bindValue(5, $electId);
                $stmt->execute();

                $totalVoted = $stmt->fetchColumn();

                $sql = "SELECT
                COALESCE(COUNT(n.node_id),0) as total_recruited,
                COALESCE(COUNT(CASE WHEN n.parent_id = 0 then 1 end),0) as total_leaders,
                COALESCE(COUNT(CASE WHEN n.parent_id <> 0 then 1 end),0) as total_members,
                COALESCE(COUNT(CASE WHEN v.voted_2017 = 1 then 1 end),0) as total_voted_recruits
                FROM tbl_voter_network n
                INNER JOIN  tbl_voter v ON v.voter_id = n.voter_id
                WHERE n.province_code = ? AND n.municipality_no = ? AND n.brgy_no= ? AND n.precinct_no = ? AND n.elect_id = ? AND n.pro_id = ?";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $provinceCode);
                $stmt->bindValue(2, $municipalityNo);
                $stmt->bindValue(3, $brgyNo);
                $stmt->bindValue(4, $row['precinct_no']);
                $stmt->bindValue(5, $electId);
                $stmt->bindValue(6, $project['pro_id']);
                $stmt->execute();

                $networkSummary = $stmt->fetch(\PDO::FETCH_ASSOC);

                $entity = new VoterSummary();
                $entity->setProvinceCode($provinceCode);
                $entity->setMunicipalityNo($municipalityNo);
                $entity->setBrgyNo($brgyNo);
                $entity->setPrecinctNo($row['precinct_no']);
                $entity->setTotalRecruited($networkSummary['total_recruited']);
                $entity->setTotalLeaders($networkSummary['total_leaders']);
                $entity->setTotalMembers($networkSummary['total_members']);
                $entity->setTotalVoters($totalVoters);
                $entity->setTotalVoted($totalVoted);
                $entity->setTotalVotedRecruits($networkSummary['total_voted_recruits']);
                $entity->setElectId($electId);
                $entity->setProId($project['pro_id']);
                $entity->setUpdatedAt(new \DateTime());
                $em->persist($entity);
            }

            $em->flush();
        }
    }

    /**
     * @Route("/ajax_update_voter_assistance_summaries/{provinceCode}/{municipalityNo}",
     *     name="ajax_update_voter_assistance_summaries",
     *    options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxUpdateVoterAssistanceSummaries($provinceCode, $municipalityNo)
    {
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT DISTINCT province_code,municipality_no,brgy_no FROM tbl_assistance WHERE province_code = ? AND municipality_no = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->execute();

        $barangays = [];

        while ($barangay = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $barangays[] = $barangay;
        }

        foreach ($barangays as $barangay) {
            $this->updateVoterAssistanceSummary($barangay['province_code'], $barangay['municipality_no'], $barangay['brgy_no']);
        }

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_update_voter_assistance_summary/{provinceCode}/{municipalityNo}/{brgyNo}",
     *     name="ajax_update_voter_assistance_summary",
     *    options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxUpdateVoterAssistanceSummary($provinceCode, $municipalityNo, $brgyNo)
    {
        $this->updateVoterAssistanceSummary($provinceCode, $municipalityNo, $brgyNo);

        return new JsonResponse(null, 200);
    }

    private function updateVoterAssistanceSummary($provinceCode, $municipalityNo, $brgyNo)
    {

        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $sql = "DELETE FROM tbl_assistance_summary WHERE province_code = ? AND municipality_no = ? AND brgy_no = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->bindValue(3, $brgyNo);
        $stmt->execute();

        $sql = "SELECT * , COALESCE(sum(a.amount),0) AS total_amount FROM tbl_assistance a
        WHERE a.province_code = ? AND a.municipality_no = ? AND a.brgy_no= ? GROUP BY a.category";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityNo);
        $stmt->bindValue(3, $brgyNo);
        $stmt->execute();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        foreach ($data as $row) {
            $entity = new VoterAssistanceSummary();
            $entity->setProvinceCode($provinceCode);
            $entity->setMunicipalityNo($municipalityNo);
            $entity->setBrgyNo($brgyNo);
            $entity->setCategory($row['category']);
            $entity->setTotalAmount($row['total_amount']);
            $entity->setCreatedAt(new \DateTime());
            $entity->setCreatedBy($user->getUsername());
            $em->persist($entity);
        }

        $em->flush();
    }

    /**
     * @Route("/assistance-datatable/{voterId}",
     *     name="ajax_datatable_voter_assistance",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

    public function datatableVoterAssistanceAction($voterId, Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $filters = array();
        $filters['v.voter_name'] = $request->get("voterName");
        $filters['v.municipality_no'] = $request->get("municipalityNo");
        $filters['v.province_code'] = $request->get("provinceCode");
        $filters['v.brgy_no'] = $request->get("brgyNo");
        $filters['v.precinct_no'] = $request->get("precinctNo");
        $filters['v.voter_id'] = $voterId;
        $filters['v.created_by'] = $user->getUsername();

        $columns = array(
            0 => 'v.voter_id',
            1 => 'v.description',
            2 => 'v.category',
            3 => 'v.amount',
            4 => 'v.remarks',
            5 => 'v.created_at',
        );

        if ($user->getIsAdmin() || $user->getIsTopLevel()) {
            $filters['v.created_by'] = "";
        }

        $whereStmt = " AND (";

        foreach ($filters as $field => $searchText) {
            if ($searchText != "") {
                if ($field == 'v.voter_id' || $field == 'v.created_by') {
                    $whereStmt .= "{$field} = '{$searchText}' AND ";
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

        $sql = "SELECT COALESCE(count(v.ast_id),0) FROM tbl_assistance v";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(v.ast_id),0) FROM tbl_assistance v
                WHERE 1 ";

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT v.* FROM tbl_assistance v
                WHERE 1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

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

        $em->clear();

        return new JsonResponse($res);
    }

    /**
     * @Route("/ajax_post_voter_assistance/{voterId}",
     *     name="ajax_post_voter_assistance",
     *    options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostVoterAssistanceAction($voterId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $voter = $em->getRepository("AppBundle:Voter")->find($voterId);
        $issuedAt = empty($request->get("issuedAt")) ? new \DateTime() : new \DateTime($request->get("issuedAt"));

        if (!$voter) {
            return new JsonResponse(null, 404);
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$this->isAllowed($voter->getProvinceCode(), $voter->getMunicipalityNo(), $voter->getBrgyNo())) {
            return new JsonResponse(null, 401);
        }

        $entity = new VoterAssistance();
        $entity->setVoterId($voterId);
        $entity->setProvinceCode($voter->getProvinceCode());
        $entity->setMunicipalityNo($voter->getMunicipalityNo());
        $entity->setBrgyNo($voter->getBrgyNo());
        $entity->setDescription($request->get("description"));
        $entity->setCategory($request->get("category"));
        $entity->setAmount($request->get("amount"));
        $entity->setCreatedBy($user->getUsername());
        $entity->setIssuedAt($issuedAt);
        $entity->setCreatedAt(new \DateTime);
        $entity->setStatus(self::STATUS_ACTIVE);
        $entity->setRemarks($request->get("remarks"));

        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->persist($entity);
        $em->flush();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_delete_voter_assistance/{astId}",
     *     name="ajax_delete_voter_assistance",
     *    options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteVoterAssistanceAction($astId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $assistance = $em->getRepository("AppBundle:VoterAssistance")->find($astId);
        $user = $this->get("security.token_storage")->getToken()->getUser();

        if (!$assistance) {
            return new JsonResponse(null, 404);
        }

        if (!$user->getIsAdmin()) {
            return new JsonResponse(null, 401);
        }

        $em->remove($assistance);
        $em->flush();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_sms_multiselect_voters",
     *   name="ajax_sms_multiselect_voters",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxSmsMultiselectVotersAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $provinceCode = empty($request->get('provinceCode')) ? null : $request->get('provinceCode');
        $municipalityNo = empty($request->get('municipalityNo')) ? null : $request->get('municipalityNo');
        $brgyNo = empty($request->get('brgyNo')) ? null : $request->get('brgyNo');
        $voterGroup = empty($request->get('voterGroup')) ? null : $request->get('voterGroup');
        $project = empty($request->get('project')) ? null : $request->get('project');

        $withBirthdate = empty($request->get('withBirthdate')) ? null : $request->get('withBirthdate');
        $withNoBirthdate = empty($request->get('withNoBirthdate')) ? null : $request->get('withNoBirthdate');

        $withId = empty($request->get("withId")) ? null : $request->get('withId');
        $withNoId = empty($request->get('withNoId')) ? null : $request->get('withNoId');

        $withCellphone = empty($request->get('withCellphone')) ? null : $request->get('withCellphone');
        $witnNoCellphone = empty($request->get('withNoCellphone')) ? null : $request->get('withNoCellphone');

        $isVoter = empty($request->get('isVoter')) ? null : $request->get('isVoter');
        $isNonVoter = empty($request->get('isNonVoter')) ? null : $request->get('isNonVoter');

        $sql = "SELECT pv.* FROM tbl_project_voter pv WHERE
              pv.elect_id = 3 AND
             (pv.province_code = ? OR ? IS NULL)  AND
             (pv.municipality_no = ? OR ? IS NULL) AND
             (pv.brgy_no = ? OR ? IS NULL) AND
             (pv.voter_group = ? OR ? IS NULL) AND
             (pv.pro_id = ? ) AND
             (pv.cellphone <> '' AND pv.cellphone IS NOT NULL) AND ";

        $todayDate = date('m-d');

        if ($withBirthdate) {
            $sql .= " (pv.birthdate LIKE '%{$todayDate}%') AND ";
        }

        if ($withNoBirthdate) {
            $sql .= " (pv.birthdate IS NULL || pv.birthdate = '' ) AND ";
        }

        if ($withId) {
            $sql .= " (pv.has_id = 1) AND ";
        }

        if ($withNoId) {
            $sql .= " (pv.has_id <> 1 || pv.has_id IS NULL) AND ";
        }

        if ($isNonVoter) {
            $sql .= " (pv.is_non_voter = 1) AND ";
        }

        if ($isVoter) {
            $sql .= " (pv.is_non_voter = 0 || pv.is_non_voter IS NULL) AND ";
        }

        $sql = substr_replace($sql, "", -4);

        $sql .= " ORDER BY pv.voter_name ASC ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, empty($provinceCode) ? null : $provinceCode);
        $stmt->bindValue(3, $municipalityNo);
        $stmt->bindValue(4, empty($municipalityNo) ? null : $municipalityNo);
        $stmt->bindValue(5, $brgyNo);
        $stmt->bindValue(6, empty($brgyNo) ? null : $brgyNo);
        $stmt->bindValue(7, $voterGroup);
        $stmt->bindValue(8, empty($voterGroup) ? null : $voterGroup);
        $stmt->bindValue(9, $project);
        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!is_array($data) || count($data) <= 0) {
            $data = [];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_post_sms",
     *       name="ajax_post_sms",
     *       options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostSms(Request $request)
    {
        $self = $this;
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $currentRow = 0;
        $totalRows = 0;
        $percentage = 0;
        $currentVoter = "";

        $messageBody = $request->get("messageBody");
        $voters = $request->get("voters");

        if (!$user->getIsAdmin()) {
            return new JsonResponse(null, 401);
        }

        $errors = [];

        if (empty($messageBody)) {
            $errors['messageBody'] = 'Your message cannot be empty...';
        }

        if (count($voters) <= 0) {
            $errors['voters'] = 'Please select 1 or more message recipient...';
        }

        if (count($errors) > 0) {
            return new JsonResponse($errors, 400);
        }

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($self, $em, $user, $request) {
            $voters = $request->get("voters");
            $totalRows = count($voters);
            $counter = 0;

            foreach ($voters as $proVoterId) {
                $counter++;

                $sql = "SELECT pv.* FROM tbl_project_voter pv WHERE pv.pro_voter_id = ? ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $proVoterId);
                $stmt->execute();
                $voter = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$voter) {
                    echo json_encode([
                        'totalRows' => $totalRows,
                        'currentRowIndex' => $counter,
                        'currentRow' => [
                            'voter_name' => $proVoterId,
                        ],
                        'status' => false,
                        'percentage' => (int) (($counter / $totalRows) * 100),
                        'message' => 'Message failed Voter Id : ' . $proVoterId,
                    ]);
                }

                $messageText = $request->get('messageBody');
                $name2 = strtolower($voter['firstname']) . ' ' . strtolower($voter['middlename']) . ' ' . strtolower($voter['lastname']) . strtolower($voter['ext_name']);
                $transArr = array(
                    '{name1}' => ucwords(strtolower($voter['voter_name'])),
                    '{name2}' => ucwords($name2),
                    '{name3}' => ucwords(strtolower($voter['firstname'])),
                    '{precinctNo}' => $voter['precinct_no'],
                    '{brgy}' => $voter['barangay_name'],
                    '{mun}' => $voter['municipality_name'],
                    '{voterNo}' => $voter['voter_no'],
                    '{pos}' => $voter['voter_group']
                );

                $messageText = strtr($messageText, $transArr);

                if ($voter) {
                    if (preg_match("/^(09)\\d{9}$/", $voter['cellphone'])) {
                        $msgEntity = new SendSms();
                        $msgEntity->setMessageText($messageText);
                        $msgEntity->setMessageTo($voter['cellphone']);
                        $em->persist($msgEntity);
                        $em->flush();
                    }
                }

                $em->clear();

                //sleep(1);

                echo json_encode([
                    'totalRows' => $totalRows,
                    'currentRowIndex' => $counter,
                    'currentRow' => $voter,
                    'message' => $messageText,
                    'percentage' => (int) (($counter / $totalRows) * 100),
                    'status' => true,
                ]);

                ob_flush();
                flush();
            }
        });

        return $response;
    }

    /**
     * @Route("/ajax_send_new_year_greetings/{municipalityNo}",
     *       name="ajax_send_new_year_greetings",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSendNewYearGreetings($municipalityNo)
    {
        $self = $this;
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $rescue = $this->getDoctrine()->getManager("rescue");

        $sql = "SELECT pd.* ,ph.municipality_no, ph.brgy_no FROM psw_profile_hdr ph LEFT JOIN psw_profile_dtl pd ON pd.profile_no = ph.profile_no
        WHERE ph.municipality_no = ? AND pd.cellphone IS NOT NULL and pd.cellphone <> '' GROUP BY cellphone";

        $stmt = $rescue->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityNo);
        $stmt->execute();

        $voters = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $currentRow = 0;
        $totalRows = 0;
        $percentage = 0;
        $currentVoter = "";

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($self, $em, $user, $voters) {

            $messageBody = "Kapayapaan, Kasaganahan at Kasiyahan sa Bagong Taon!" . PHP_EOL . "Isang pagbati mula sa ating pamahalaang panlalawigan sa pangunguna ng ating Gobernador Jose Chaves Alvarez.";

            $totalRows = count($voters);
            $counter = 0;

            foreach ($voters as $voter) {
                $counter++;

                if (!$voter) {
                    echo json_encode([
                        'totalRows' => $totalRows,
                        'currentRowIndex' => $counter,
                        'currentRow' => [
                            'voter_name' => $voter['name'],
                        ],
                        'status' => false,
                        'percentage' => (int) (($counter / $totalRows) * 100),
                        'message' => 'Message failed Voter Id : ' . $voter['name'],
                    ]);
                }

                if ($voter) {
                    if (preg_match("/^(09)\\d{9}$/", $voter['cellphone'])) {
                        $msgEntity = new SendSms();
                        $msgEntity->setMessageText($messageBody);
                        $msgEntity->setMessageTo($voter['cellphone']);
                        $em->persist($msgEntity);
                        $em->flush();
                    }
                }

                $em->clear();

                // echo json_encode([
                //     'totalRows' => $totalRows,
                //     'currentRowIndex' => $counter,
                //     'currentRow' => $voter['name'],
                //     'message' => $messageBody,
                //     'percentage' => (int)(($counter / $totalRows) * 100),
                //     'status' => true
                // ]);

                echo $counter . '. ' . $voter['name'] . ' ' . $voter['cellphone'] . '<br/>';

                ob_flush();
                flush();
            }
        });

        return $response;
    }

    /**
     * @Route("/ajax_send_single_sms/{voterId}",
     *       name="ajax_send_single_sms",
     *       options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostMessageSingle(Request $request, $voterId)
    {
        $em = $this->getDoctrine()->getManager();

        $isSpecial = empty($request->get("isSpecial")) ? false : (bool) $request->get("isSpecial");
        $sql = "SELECT v.*,b.name AS barangay_name, m.name AS municipality_name FROM tbl_voter v
                INNER JOIN psw_municipality m ON v.municipality_no = m.municipality_no AND v.province_code = m.province_code
                INNER JOIN psw_barangay b ON  b.brgy_no = v.brgy_no AND b.municipality_code = m.municipality_code
                WHERE v.voter_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $voterId);
        $stmt->execute();
        $voter = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$voter) {
            return new JsonResponse(null, 404);
        }

        $messageText = $request->get('messageBody');

        $transArr = array(
            '{name}' => $voter['voter_name'],
            '{precinctNo}' => $voter['precinct_no'],
            '{brgy}' => $voter['barangay_name'],
            '{mun}' => $voter['municipality_name'],
            '{voterNo}' => $voter['voter_no'],
        );

        $messageText = strtr($messageText, $transArr);

        if ($voter) {
            if (preg_match("/^(09)\\d{9}$/", $voter['cellphone_no'])) {
                $msgEntity = new SendSms();
                $msgEntity->setMessageText($messageText);
                $msgEntity->setMessageTo($voter['cellphone_no']);
                $em->persist($msgEntity);
                $em->flush();
            }
        }

        $em->clear();

        if ($isSpecial) {
            $this->textMembers($voterId, $request->get("messageBody"));
        }

        return new JsonResponse(['message' => 'ok']);
    }

    private function textMembers($voterId, $messageBody)
    {
        $em = $this->getDoctrine()->getManager();
        $em = $this->getDoctrine()->getManager('sms');

        $sql = "SELECT v.*,n.node_id,b.name AS barangay_name, m.name AS municipality_name FROM tbl_voter v
                INNER JOIN tbl_voter_network n ON n.voter_id = v.voter_id
                INNER JOIN psw_municipality m ON v.municipality_no = m.municipality_no AND v.province_code = m.province_code
                INNER JOIN psw_barangay b ON  b.brgy_no = v.brgy_no AND b.municipality_code = m.municipality_code
                WHERE v.voter_id = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $voterId);
        $stmt->execute();

        $parentNode = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$parentNode) {
            return null;
        }

        $sql = "SELECT v.*,n.*,b.name AS barangay_name, m.name AS municipality_name FROM tbl_voter v
                INNER JOIN tbl_voter_network n ON n.voter_id = v.voter_id
                INNER JOIN psw_municipality m ON v.municipality_no = m.municipality_no AND v.province_code = m.province_code
                INNER JOIN psw_barangay b ON  b.brgy_no = v.brgy_no AND b.municipality_code = m.municipality_code
                WHERE n.parent_id = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $parentNode['node_id']);
        $stmt->execute();

        $children = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if (preg_match("/^(09)\\d{9}$/", $row['cellphone_no']) || preg_match("/^(09)\\d{9}$/", $parentNode['cellphone_no'])) {
                $cellphoneNo = !empty($row['cellphone_no']) ? $row['cellphone_no'] : $parentNode['cellphone_no'];
                $transArr = array(
                    '{name}' => $row['voter_name'],
                    '{precinctNo}' => $row['precinct_no'],
                    '{brgy}' => $row['barangay_name'],
                    '{mun}' => $row['municipality_name'],
                    '{voterNo}' => $row['voter_no'],
                );

                $messageText = $messageBody;
                $messageText = strtr($messageText, $transArr);
                $msgEntity = new SendSms();
                $msgEntity->setMessageText($messageText);
                $msgEntity->setMessageTo($cellphoneNo);
                $em->persist($msgEntity);
                $em->flush();
            }
        }
    }

    /**
     * @Route("/ajax_select2_voter_category",
     *       name="ajax_select2_voter_category",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2VoterCategory(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT category FROM tbl_voter v WHERE v.category LIKE ? ORDER BY v.category ASC LIMIT 30";
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

    /**
     * @Route("/ajax_select2_voter_group",
     *       name="ajax_select2_voter_group",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2VoterGroup(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT voter_group FROM tbl_project_voter p WHERE p.voter_group LIKE ? ORDER BY p.voter_group ASC LIMIT 30";
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

    /**
     * @Route("/ajax_select2_voter_organization",
     *       name="ajax_select2_voter_organization",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2VoterOrganization(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT organization FROM tbl_voter v WHERE v.organization LIKE ? ORDER BY v.organization ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $organizations = $stmt->fetchAll();

        if (count($organizations) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($organizations);
    }

    /**
     * @Route("/ajax_select2_voter_position",
     *       name="ajax_select2_voter_position",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2VoterPosition(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT position FROM tbl_project_voter v WHERE v.position LIKE ? ORDER BY v.position ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $positions = $stmt->fetchAll();

        if (count($positions) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($positions);
    }

    /**
     * @Route("/ajax_select2_assistance_category",
     *       name="ajax_select2_assistance_category",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2AssistanceCategory(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT category FROM tbl_assistance a WHERE a.category LIKE ? ORDER BY a.category ASC LIMIT 30";
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

    /**
     * @Route("/photo/{filename}",
     *   name="ajax_get_project_voter_photo",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxGetProjectVoterPhotoAction($filename)
    {

        $rootDir = __DIR__ . '/../../../web/uploads/images/';
        $imagePath = $rootDir . $filename . '.jpg';

        if (!file_exists($imagePath)) {
            $imagePath = $rootDir . 'default.jpg';
        }

        $response = new BinaryFileResponse($imagePath);
        $response->headers->set('Content-Type', 'image/jpeg');

        return $response;
    }

    /**
     * @Route("/photo/{proId}/{proVoterId}",
     *     name="ajax_upload_project_voter_photo",
     *     options={"expose" = true}
     *     )
     * @Method("POST")
     */

    public function ajaxUploadProjectVoterPhoto(Request $request, $proId, $proVoterId)
    {

        $user = $this->get("security.token_storage")->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();
        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")
            ->findOneBy(['proId' => $proId, 'proVoterId' => $proVoterId]);

        if (!$projectVoter) {
            return new JsonResponse(['message' => 'not found'], 404);
        }

        if ($projectVoter->getGeneratedIdNo() == null || $projectVoter->getGeneratedIdNo() == '') {
            return new JsonResponse(['message' => 'Please generate id'], 400);
        }

        $images = $request->files->get('files');

        $filename = $proId . '_' . $projectVoter->getGeneratedIdNo() . '.jpg';
        $imgRoot = __DIR__ . '/../../../web/uploads/images/';
        $imagePath = $imgRoot . $filename;

        foreach ($images as $image) {
            $tmpName = $image->getRealPath();
            $this->compress($tmpName, $imagePath, 30);
        }

        $projectVoter->setHasPhoto(1);
        $projectVoter->setDidChanged(1);
        $projectVoter->setToSend(1);
        $projectVoter->setPhotoAt(new \DateTime());
        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setUpdatedBy($user->getUsername());

        $em->flush();
        $em->clear();

        return new JsonResponse(null, 200);
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
     * @Route("/pretify",
     *       name="ajax_get_pretify",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function pretifyVoterName()
    {
        $em = $this->getDoctrine()->getManager();

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($em) {
            $batchSize = 5000;
            $batchNo = 0;
            $totalBatch = 0;
            $totalRecords = 0;
            $counter = 0;

            $sql = "SELECT COUNT(*) FROM tbl_project_voter WHERE voter_firstname IS NULL AND elect_id = 3";
            $stmt = $em->getConnection()->query($sql);
            $totalRecords = (int) $stmt->fetchColumn();
            $totalBatch = $totalRecords / $batchSize;
            $rem = $totalBatch - (int) $totalBatch;

            if ($rem > 0) {
                $totalBatch = $totalBatch - $rem + 1;
            }

            for ($i = 0; $i < $totalBatch; $i++) {

                $start = $i * $batchSize;

                echo "<br/><br/>";
                echo "<br/><br/>";
                echo "TOTAL BATCH : " . $totalBatch . '<br/>';
                echo "BATCH NO : " . $i . '<br/>';
                echo "BATCH START : " . $start . '<br/>';

                $sql = "SELECT * FROM tbl_project_voter
                        WHERE voter_firstname IS NULL AND elect_id = 3
                        LIMIT {$batchSize} OFFSET {$start}";

                echo "Query : " . $sql;
                echo "<br/><br/>";
                echo "<br/><br/>";

                $stmt = $em->getConnection()->query($sql);
                $voters = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($voters as $row) {
                    $counter++;
                    $proVoterId = $row['pro_voter_id'];
                    $temp = explode(',', $row['voter_name']);
                    $pretified = trim($temp[1]) . ' ' . trim($temp[0]);
                    $firstname = explode(' ', trim($temp[1]))[0];

                    $sql = "UPDATE tbl_project_voter
                            SET voter_name_pretified = ?, voter_firstname = ?
                            WHERE pro_voter_id = ? AND voter_firstname IS NULL AND elect_id = 3";
                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->bindValue(1, $pretified);
                    $stmt->bindValue(2, $firstname);
                    $stmt->bindValue(3, $proVoterId);
                    $stmt->execute();

                    echo $counter . '. Voter name : ' . $row['voter_name'] . '<br/>';
                    echo 'Pretty : ' . $pretified . '<br/>';
                    echo 'Firstname : ' . $firstname . '<br/>';

                    flush();
                }

            }

        });

        return $response;
    }

    /**
     * @Route("/update_birthdate/{municipalityNo}",
     *       name="ajax_update_birthdate",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function updateBirthdate($municipalityNo)
    {
        $em = $this->getDoctrine()->getManager();
        $emLive = $this->getDoctrine()->getManager("voter_live");

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($em, $emLive, $municipalityNo) {
            $batchSize = 1000;
            $batchNo = 0;
            $totalBatch = 0;
            $totalRecords = 0;
            $counter = 0;

            $sql = "SELECT COUNT(*) FROM tbl_voter WHERE birthdate IS NOT NULL AND province_code = ? AND municipality_no = ? ";
            $stmt = $emLive->getConnection()->prepare($sql);
            $stmt->bindValue(1, 53);
            $stmt->bindValue(2, $municipalityNo);
            $stmt->execute();

            $totalRecords = (int) $stmt->fetchColumn();
            $totalBatch = $totalRecords / $batchSize;
            $rem = $totalBatch - (int) $totalBatch;

            if ($rem > 0) {
                $totalBatch = $totalBatch - $rem + 1;
            }

            for ($i = 0; $i < $totalBatch; $i++) {

                $start = $i * $batchSize;

                echo "<br/><br/>";
                echo "<br/><br/>";
                echo "TOTAL BATCH : " . $totalBatch . '<br/>';
                echo "BATCH NO : " . $i . '<br/>';
                echo "BATCH START : " . $start . '<br/>';
                echo "<br/><br/>";
                echo "<br/><br/>";

                $sql = "SELECT * FROM tbl_voter WHERE birthdate IS NOT NULL AND province_code = ? AND municipality_no = ? ORDER BY voter_name LIMIT {$batchSize} OFFSET {$start}";

                $stmt = $emLive->getConnection()->prepare($sql);
                $stmt->bindValue(1, 53);
                $stmt->bindValue(2, $municipalityNo);
                $stmt->execute();

                $voters = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($voters as $row) {
                    $counter++;
                    $voterName = $row['voter_name'];

                    $sql = "UPDATE tbl_voter SET birthdate = ? WHERE voter_id = ?";
                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->bindValue(1, $row['birthdate']);
                    $stmt->bindValue(2, $row['voter_id']);
                    $stmt->execute();

                    echo $counter . '. Voter name : ' . $row['voter_name'] . '<br/>';
                    echo 'Birthdate : ' . $row['birthdate'] . '<br/>';

                    flush();
                }

            }

        });

        return $response;
    }

    /**
     * @Route("/ajax_fill_organization/{proId}/{municipalityNo}",
     *       name="ajax_fill_organization",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function fillOrganization($proId, $municipalityNo)
    {
        $em = $this->getDoctrine()->getManager();

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($em, $proId, $municipalityNo) {
            $batchSize = 1000;
            $batchNo = 0;
            $totalBatch = 0;
            $totalRecords = 0;
            $counter = 0;

            $sql = "SELECT COUNT(*)
            FROM tbl_voter v
            WHERE v.voter_id NOT IN (SELECT pv.voter_id FROM tbl_project_voter pv WHERE pv.pro_id = ? AND pv.municipality_no = ? )
            AND v.province_code = 53 AND v.municipality_no = ? ORDER BY v.voter_name ASC";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $proId);
            $stmt->bindValue(2, $municipalityNo);
            $stmt->bindValue(3, $municipalityNo);
            $stmt->execute();

            $totalRecords = (int) $stmt->fetchColumn();

            $totalBatch = $totalRecords / $batchSize;
            $rem = $totalBatch - (int) $totalBatch;

            if ($rem > 0) {
                $totalBatch = $totalBatch - $rem + 1;
            }

            for ($i = 0; $i < $totalBatch; $i++) {

                $start = $i * $batchSize;

                echo "<br/><br/>";
                echo "<br/><br/>";
                echo "TOTAL BATCH : " . $totalBatch . '<br/>';
                echo "BATCH NO : " . $i . '<br/>';
                echo "BATCH START : " . $start . '<br/>';

                $sql = "SELECT v.*
                FROM tbl_voter v
                WHERE v.voter_id NOT IN (SELECT pv.voter_id FROM tbl_project_voter pv WHERE pv.pro_id = ? AND pv.municipality_no = ? )
                AND v.province_code = 53 AND v.municipality_no = ? ORDER BY v.voter_name ASC
                LIMIT {$batchSize} ";

                echo "Query : " . $sql;
                echo "<br/><br/>";
                echo "<br/><br/>";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $proId);
                $stmt->bindValue(2, $municipalityNo);
                $stmt->bindValue(3, $municipalityNo);
                $stmt->execute();

                $voters = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($voters as $row) {
                    $counter++;

                    $proVoter = new ProjectVoter();
                    $proVoter->setCreatedAt(new \Datetime());
                    $proVoter->setCreatedBy('script');
                    $proVoter->setVoterId($row['voter_id']);
                    $proVoter->setProId($proId);
                    $proVoter->setElectId($row['elect_id']);
                    $proVoter->setProvinceCode($row['province_code']);
                    $proVoter->setMunicipalityNo($row['municipality_no']);
                    $proVoter->setBrgyNo($row['brgy_no']);
                    $proVoter->setAddress($row['address']);
                    $proVoter->setPrecinctNo($row['precinct_no']);
                    $proVoter->setVoterName($row['voter_name']);
                    $proVoter->setVoterNamePretified($row['voter_name_pretified']);
                    $proVoter->setVoterFirstname($row['voter_firstname']);
                    $proVoter->setBirthdate($row['birthdate']);
                    $proVoter->setVoterNo($row['voter_no']);
                    $proVoter->setVoted2016($row['voted_2017']);
                    $proVoter->setCellphone($row['cellphone_no']);
                    $proVoter->setVoterGroup(null);
                    $proVoter->setProIdCode($this->generateProIdCode($proId));
                    $proVoter->setUpdatedAt(new \DateTime());
                    $proVoter->setUpdatedBy('script');
                    $proVoter->setRemarks('');
                    $proVoter->setStatus(self::STATUS_ACTIVE);

                    $validator = $this->get('validator');
                    $violations = $validator->validate($proVoter);

                    $errors = [];

                    if (count($violations) > 0) {
                        foreach ($violations as $violation) {
                            $errors[$violation->getPropertyPath()] = $violation->getMessage();
                        }
                        echo "There is an error <br/> Failed to save record </br>";
                        var_dump($errors);
                        echo "<br/>";
                    } else {
                        $em->persist($proVoter);
                        $em->flush();
                        echo $counter . '. Voter name : ' . $row['voter_name'] . '<br/>';
                    }

                    flush();
                }

                $em->clear();
            }

        });

        return $response;
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
     * @Route("/ajax_generate_id_no/{electId}/{proId}/{municipalityNo}",
     *       name="ajax_generate_id_no",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGenerateIdNo($electId, $proId, $municipalityNo)
    {
        $em = $this->getDoctrine()->getManager();

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($em, $electId, $proId, $municipalityNo) {

            $sql = "SELECT pv.* FROM tbl_project_voter pv WHERE pv.elect_id = ? AND pv.pro_id = ? AND pv.municipality_no = ? AND (pv.pro_id_code IS NULL OR pv.pro_id_code = '') ORDER BY pv.voter_name ASC LIMIT 10000";

            echo "Query : " . $sql;
            echo "<br/><br/>";
            echo "<br/><br/>";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $electId);
            $stmt->bindValue(2, $proId);
            $stmt->bindValue(3, $municipalityNo);
            $stmt->execute();

            $voters = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $counter = 40001;

            foreach ($voters as $row) {
                $counter++;

                $proIdCode = sprintf("%06d", $counter);
                $namePart = explode(' ', $row['voter_name']);
                $prefix = '';

                foreach ($namePart as $name) {
                    $prefix .= substr($name, 0, 1);
                }
        
                $sql = "UPDATE tbl_project_voter pv SET pv.pro_id_code = ? WHERE pv.pro_voter_id = ? ";
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $prefix . $municipalityNo . $proIdCode);
                $stmt->bindValue(2, $row['pro_voter_id']);
                $stmt->execute();

                echo $counter . '. Voter name : ' . $row['voter_name'] . '<br/>';

                flush();
            }
        });

        $em->clear();

        return $response;
    }

    ################################################################
    ########################## SELECT2 JPM #########################
    ################################################################

    /**
     * @Route("ajax_select2_jpm_municipality",
     *       name="ajax_select2_jpm_municipality",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetJpmMunicipality(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT municipality_name FROM tbl_jpm WHERE municipality_name LIKE ? ORDER BY municipality_name ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);

        $stmt->execute();
        $municipalities = $stmt->fetchAll();

        if (count($municipalities) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($municipalities);
    }

    /**
     * @Route("ajax_select2_jpm_barangay",
     *       name="ajax_select2_jpm_barangay",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetJpmBarangay(Request $request)
    {
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';
        $municipalityName = $request->get('municipalityName');

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT barangay_name FROM tbl_jpm WHERE municipality_name = ? AND barangay_name LIKE ? ORDER BY municipality_name ASC LIMIT 30 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityName);
        $stmt->bindValue(2, $searchText);

        $stmt->execute();
        $municipalities = $stmt->fetchAll();

        if (count($municipalities) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($municipalities);
    }

    /**
     * @Route("/ajax_transfer_to_new_voterslist",
     *       name="ajax_transfer_to_new_voterslist",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function transferToNewVoterslist()
    {
        $em = $this->getDoctrine()->getManager();

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($em) {
            $batchSize = 10000;
            $counter = 0;

            $sql = "SELECT * FROM tbl_project_voter
            WHERE (is_processed IS NULL OR is_processed = 0 ) AND
            voter_group in ('CH','KCL','KCL0','KCL1','KCL2','KCL3','KFC','DAO','KJR') AND elect_id = 2 AND pro_id = 2 LIMIT {$batchSize}";

            echo "Query : " . $sql;
            echo "<br/><br/>";
            echo "<br/><br/>";

            $stmt = $em->getConnection()->query($sql);
            $voters = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($voters as $row) {
                $counter++;
                $record = null;

                $sql = "SELECT * FROM tbl_project_voter WHERE voter_name = ? AND pro_id = ? AND elect_id = ?";
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $row['voter_name']);
                $stmt->bindValue(2, 2);
                $stmt->bindValue(3, 3);
                $stmt->execute();

                $record = $stmt->fetch(\PDO::FETCH_ASSOC);
                $isCopied = 0;

                if (!empty($record) && $record != null) {
                    $isCopied = 1;

                    $sql = "UPDATE tbl_project_voter SET pro_id_code = ?, voter_group = ?, cellphone = ?,
                    has_photo = ?, has_id = ?, has_submitted = ?, voter_name_pretified = ?, voter_firstname = ?,
                    is_1 = ?, is_2 = ?, is_3 = ?, is_4 = ?, is_5 = ?, is_6 = ?, is_7 = ?, is_8 = ?
                    WHERE voter_name = ? AND elect_id = ? AND pro_id = ?";

                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->bindValue(1, $row['pro_id_code']);
                    $stmt->bindValue(2, $row['voter_group']);
                    $stmt->bindValue(3, $row['cellphone']);
                    $stmt->bindValue(4, $row['has_photo']);
                    $stmt->bindValue(5, $row['has_id']);
                    $stmt->bindValue(6, $row['has_submitted']);
                    $stmt->bindValue(7, $row['voter_name_pretified']);
                    $stmt->bindValue(8, $row['voter_firstname']);
                    $stmt->bindValue(9, $row['is_1']);
                    $stmt->bindValue(10, $row['is_2']);
                    $stmt->bindValue(11, $row['is_3']);
                    $stmt->bindValue(12, $row['is_4']);
                    $stmt->bindValue(13, $row['is_5']);
                    $stmt->bindValue(14, $row['is_6']);
                    $stmt->bindValue(15, $row['is_7']);
                    $stmt->bindValue(16, $row['is_8']);
                    $stmt->bindValue(17, $row['voter_name']);
                    $stmt->bindValue(18, 3);
                    $stmt->bindValue(19, 2);

                    $stmt->execute();
                }

                $sql = "UPDATE tbl_project_voter SET is_processed = 1 , is_copied = ? WHERE pro_voter_id = ? ";
                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $isCopied);
                $stmt->bindValue(2, $row['pro_voter_id']);
                $stmt->execute();

                echo $counter . '. Voter name : ' . $row['voter_name'] . '<br/>';

                flush();
            }
        });

        return $response;
    }

    /**
     * @Route("/ajax_transfer_to_new_voter",
     *       name="ajax_transfer_to_new_voter",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function transferToNewVoter()
    {
        $em = $this->getDoctrine()->getManager();

        $sourceName = "ZULUETA, JULIE SAMILLINO";
        $targetName = "ZULUETA, JULIE SAMILLANO";

        $sql = "SELECT * FROM tbl_project_voter
        WHERE elect_id = 2 AND pro_id = 2 AND voter_name = ? AND voter_group IN ('CH','KCL','KCL0','KCL1','KCL2','KCL3','KFC','DAO') AND has_id= 1";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $sourceName);
        $stmt->execute();

        $voter = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($voter == null || empty($voter)) {
            return new JsonResponse(['message' => 'source not found']);
        }

        $record = null;

        $sql = "SELECT * FROM tbl_project_voter WHERE voter_name = ? AND pro_id = ? AND elect_id = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $targetName);
        $stmt->bindValue(2, 2);
        $stmt->bindValue(3, 3);
        $stmt->execute();

        $record = $stmt->fetch(\PDO::FETCH_ASSOC);
        $isCopied = 0;

        if (!empty($record) && $record != null) {
            $isCopied = 1;

            $sql = "UPDATE tbl_project_voter SET pro_id_code = ?, voter_group = ?, cellphone = ?,
            has_photo = ?, has_id = ?, has_submitted = ?, voter_name_pretified = ?, voter_firstname = ?,
            is_1 = ?, is_2 = ?, is_3 = ?, is_4 = ?, is_5 = ?, is_6 = ?, is_7 = ?, is_8 = ?
            WHERE voter_name = ? AND elect_id = ? AND pro_id = ?";

            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1, $voter['pro_id_code']);
            $stmt->bindValue(2, $voter['voter_group']);
            $stmt->bindValue(3, $voter['cellphone']);
            $stmt->bindValue(4, $voter['has_photo']);
            $stmt->bindValue(5, $voter['has_id']);
            $stmt->bindValue(6, $voter['has_submitted']);
            $stmt->bindValue(7, $voter['voter_name_pretified']);
            $stmt->bindValue(8, $voter['voter_firstname']);
            $stmt->bindValue(9, $voter['is_1']);
            $stmt->bindValue(10, $voter['is_2']);
            $stmt->bindValue(11, $voter['is_3']);
            $stmt->bindValue(12, $voter['is_4']);
            $stmt->bindValue(13, $voter['is_5']);
            $stmt->bindValue(14, $voter['is_6']);
            $stmt->bindValue(15, $voter['is_7']);
            $stmt->bindValue(16, $voter['is_8']);
            $stmt->bindValue(17, $targetName);
            $stmt->bindValue(18, 3);
            $stmt->bindValue(19, 2);

            $stmt->execute();
        } else {
            return new JsonResponse(['message' => 'not found']);
        }

        $sql = "UPDATE tbl_project_voter SET is_processed = 1 , is_copied = ? WHERE pro_voter_id = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $isCopied);
        $stmt->bindValue(2, $voter['pro_voter_id']);
        $stmt->execute();

        return new JsonResponse(['message' => "done", 'voter_name' => $targetName]);
    }

    /**
     * @Route("/ajax_import_jpm/{electId}/{proId}/{provinceCode}/{municipalityNo}",
     *       name="ajax_import_jpm",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxImportJpm($electId, $proId, $provinceCode, $municipalityNo)
    {
        $em = $this->getDoctrine()->getManager();
        $emProvince = $this->getDoctrine()->getManager('province');

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($em, $emProvince, $electId, $proId, $provinceCode, $municipalityNo) {
            $batchSize = 10000;
            $counter = 0;

            $sql = "SELECT * FROM tbl_project_voter
            WHERE (is_processed IS NULL OR  is_processed = 0) AND is_jpm = 1
            AND municipality_name = ?
            LIMIT {$batchSize}";

            echo "Query : " . $sql;
            echo "<br/><br/>";
            echo "<br/><br/>";

            $stmt = $emProvince->getConnection()->prepare($sql);
            $stmt->bindValue(1, 'ABORLAN');
            $stmt->execute();
            $voters = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($voters as $row) {

                $counter++;
                $record = null;

                $sql = "SELECT * FROM tbl_project_voter WHERE pro_id = ?
                AND elect_id = ? AND province_code = ?
                AND municipality_no = ? AND voter_no = ? AND voter_name = ? AND precinct_no = ? ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $proId);
                $stmt->bindValue(2, $electId);
                $stmt->bindValue(3, $provinceCode);
                $stmt->bindValue(4, $municipalityNo);
                $stmt->bindValue(5, $row['voter_no']);
                $stmt->bindValue(6, $row['voter_name']);
                $stmt->bindValue(7, $row['precinct_no']);
                $stmt->execute();

                $record = $stmt->fetch(\PDO::FETCH_ASSOC);

                $isCopied = 0;

                if (!empty($record) && $record != null) {
                    $isCopied = 1;

                    $sql = "UPDATE tbl_project_voter SET is_9 = 1
                    WHERE  pro_id = ? AND elect_id = ? AND pro_voter_id = ? ";

                    $stmt = $em->getConnection()->prepare($sql);
                    $stmt->bindValue(1, $proId);
                    $stmt->bindValue(2, $electId);
                    $stmt->bindValue(3, $record['pro_voter_id']);
                    $stmt->execute();
                }

                $sql = "UPDATE tbl_project_voter SET is_processed = 1 , is_copied = ? WHERE pro_voter_id = ? ";
                $stmt = $emProvince->getConnection()->prepare($sql);
                $stmt->bindValue(1, $isCopied);
                $stmt->bindValue(2, $row['pro_voter_id']);
                $stmt->execute();

                echo $counter . '. Voter name : ' . $row['voter_name'] . '- JPM : ' . $row['is_jpm'] . ' <br/>';

                flush();
            }
        });

        return $response;
    }

    /**
     * @Route("/ajax_project_voter_block/{voterId}",
     *       name="ajax_project_voter_block",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxProjectVoterBlock(Request $request, $voterId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")
            ->findOneBy(['voterId' => $voterId]);

        if (!$projectVoter) {
            return new JsonResponse(['message' => "Not found..."], 404);
        }

        $projectVoter->setIs1(0);
        $projectVoter->setIs3(0);
        $projectVoter->setIs4(0);
        $projectVoter->setIs5(0);
        $projectVoter->setIs6(0);
        $projectVoter->setIs7(0);
        $projectVoter->setIs10(0);
        $projectVoter->setVoterGroup('');
        $projectVoter->setBlockedReason($request->get('reason'));
        $projectVoter->setBlockedAt(new \DateTime());
        $projectVoter->setBlockedBy($user->getUsername());
        $projectVoter->setStatus(self::STATUS_BLOCKED);

        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setDidChange(1);
        $projectVoter->setUpdatedBy($user->getUsername());

        $em->flush();

        return new JsonResponse(['message' => 'done']);
    }

    /**
     * @Route("/ajax_project_voter_unblock/{voterId}",
     *       name="ajax_project_voter_unblock",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxProjectVoterUnblock(Request $request, $voterId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")
            ->findOneBy(['voterId' => $voterId]);

        if (!$projectVoter) {
            return new JsonResponse(['message' => "Not found..."], 404);
        }

        $projectVoter->setUnblockedReason($request->get('reason'));
        $projectVoter->setUnblockedAt(new \DateTime());
        $projectVoter->setUnblockedBy($user->getUsername());
        $projectVoter->setStatus(self::STATUS_ACTIVE);

        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setDidChange(1);
        $projectVoter->setUpdatedBy($user->getUsername());

        $em->flush();

        return new JsonResponse(['message' => 'done']);
    }

    /**
     * @Route("/ajax_project_voter_activate/{voterId}",
     *       name="ajax_project_voter_activate",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxProjectVoterActivate(Request $request, $voterId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")
            ->findOneBy(['voterId' => $voterId]);

        if (!$projectVoter) {
            return new JsonResponse(['message' => "Not found..."], 404);
        }

        $projectVoter->setActivatedReason($request->get('reason'));
        $projectVoter->setActivatedAt(new \DateTime());
        $projectVoter->setActivatedBy($user->getUsername());
        $projectVoter->setStatus(self::STATUS_ACTIVE);

        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setDidChange(1);
        $projectVoter->setUpdatedBy($user->getUsername());

        $em->flush();

        return new JsonResponse(['message' => 'done']);
    }

    /**
     * @Route("/ajax_project_voter_deactivate/{voterId}",
     *       name="ajax_project_voter_deactivate",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxProjectVoterDeactivate(Request $request, $voterId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")
            ->findOneBy(['voterId' => $voterId]);

        if (!$projectVoter) {
            return new JsonResponse(['message' => "Not found..."], 404);
        }

        $projectVoter->setDeactivatedReason($request->get('reason'));
        $projectVoter->setDeactivatedAt(new \DateTime());
        $projectVoter->setDeactivatedBy($user->getUsername());
        $projectVoter->setStatus(self::STATUS_INACTIVE);

        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setDidChange(1);
        $projectVoter->setUpdatedBy($user->getUsername());

        $em->flush();

        return new JsonResponse(['message' => 'done']);
    }

    /**
     * @Route("/ajax_project_voter_reset_image/{voterId}",
     *       name="ajax_project_voter_reset_image",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxProjectVoterResetImage(Request $request, $voterId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get("security.token_storage")->getToken()->getUser();

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")
            ->findOneBy(['voterId' => $voterId]);

        if (!$projectVoter) {
            return new JsonResponse(['message' => "Not found..."], 404);
        }

        $rootDir = __DIR__ . '/../../../web/uploads/images/';
        $filename = $projectVoter->getProId() . '_' . $projectVoter->getGeneratedIdNo() . '.jpg';

        if (file_exists($rootDir . $filename)) {
            unlink($rootDir . $filename);
        }

        $projectVoter->setHasPhoto(0);
        $projectVoter->setHasId(0);
        $projectVoter->setPhotoAt(null);

        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setDidChange(1);
        $projectVoter->setUpdatedBy($user->getUsername());

        $em->flush();

        return new JsonResponse(['message' => 'done']);
    }

    /**
     * DSWD SMS Functions
     */

    /**
     * @Route("/ajax_select2_dswd_address",
     *       name="ajax_select2_dswd_address",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2DswdAddress(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT d.address FROM tbl_dswd d WHERE d.address LIKE ? ORDER BY d.address ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $address = $stmt->fetchAll();

        if (count($address) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($address);
    }

    /**
     * @Route("/ajax_select2_dswd_sex",
     *       name="ajax_select2_dswd_sex",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2SexAddress(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT d.sex FROM tbl_dswd d WHERE d.sex LIKE ? ORDER BY d.sex ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $sex = $stmt->fetchAll();

        if (count($sex) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($sex);
    }

    /**
     * @Route("/ajax_select2_dswd_remarks",
     *       name="ajax_select2_dswd_remarks",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2RemarksAddress(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT d.remarks FROM tbl_dswd d WHERE d.remarks LIKE ? ORDER BY d.remarks ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $remarks = $stmt->fetchAll();

        if (count($remarks) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($remarks);
    }

    /**
     * @Route("/ajax_sms_multiselect_dswd_member",
     *   name="ajax_sms_multiselect_dswd_member",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxSmsMultiselectDswdMemberAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $address = empty($request->get('address')) ? null : $request->get('address');
        $sex = empty($request->get('sex')) ? null : $request->get('sex');
        $remarks = empty($request->get('remarks')) ? null : $request->get('remarks');

        $sql = "SELECT d.* FROM tbl_dswd d WHERE
             (d.address = ? OR ? IS NULL)  AND
             (d.sex = ? OR ? IS NULL) AND
             (d.remarks = ? OR ? IS NULL) AND
             (d.contact_no <> '' AND d.contact_no IS NOT NULL) ";

        $sql .= " ORDER BY d.firstname ASC, d.middlename ASC, d.lastname ASC ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $address);
        $stmt->bindValue(2, empty($address) ? null : $address);
        $stmt->bindValue(3, $sex);
        $stmt->bindValue(4, empty($sex) ? null : $sex);
        $stmt->bindValue(5, $remarks);
        $stmt->bindValue(6, empty($remarks) ? null : $remarks);
        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (!is_array($data) || count($data) <= 0) {
            $data = [];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_post_dswd_sms",
     *       name="ajax_post_dswd_sms",
     *       options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostDswdSms(Request $request)
    {
        $self = $this;
        $user = $this->get("security.token_storage")->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $currentRow = 0;
        $totalRows = 0;
        $percentage = 0;
        $currentVoter = "";

        $messageBody = $request->get("messageBody");
        $voters = $request->get("voters");

        if (!$user->getIsAdmin()) {
            return new JsonResponse(null, 401);
        }

        $errors = [];

        if (empty($messageBody)) {
            $errors['messageBody'] = 'Your message cannot be empty...';
        }

        if (count($voters) <= 0) {
            $errors['voters'] = 'Please select 1 or more message recipient...';
        }

        if (count($errors) > 0) {
            return new JsonResponse($errors, 400);
        }

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($self, $em, $user, $request) {
            $voters = $request->get("voters");
            $totalRows = count($voters);
            $counter = 0;

            foreach ($voters as $id) {
                $counter++;

                $sql = "SELECT d.* FROM tbl_dswd d WHERE d.id = ? ";

                $stmt = $em->getConnection()->prepare($sql);
                $stmt->bindValue(1, $id);
                $stmt->execute();
                $member = $stmt->fetch(\PDO::FETCH_ASSOC);

                if (!$member) {
                    echo json_encode([
                        'totalRows' => $totalRows,
                        'currentRowIndex' => $counter,
                        'currentRow' => [
                            'voter_name' => $id,
                        ],
                        'status' => false,
                        'percentage' => (int) (($counter / $totalRows) * 100),
                        'message' => 'Message failed Id : ' . $id,
                    ]);
                }

                $messageText = $request->get('messageBody');

                $transArr = array(
                    '{firstname}' => ucwords(strtolower($member['firstname'])),
                    '{middlename}' => ucwords(strtolower($member['middlename'])),
                    '{lastname}' => ucwords(strtolower($member['middlename'])),
                    '{address}' => $member['address'],
                    '{sex}' => $member['sex'],
                    '{age}' => $member['age'],
                    '{remarks}' => $member['remarks'],
                );

                $messageText = strtr($messageText, $transArr);

                if ($member) {
                    $contactNo = '0' . $member['contact_no'];

                    if (preg_match("/^(09)\\d{9}$/", $contactNo)) {
                        $msgEntity = new SendSms();
                        $msgEntity->setMessageText($messageText);
                        $msgEntity->setMessageTo($contactNo);
                        $em->persist($msgEntity);
                        $em->flush();
                    }
                }

                $em->clear();

                //sleep(1);

                echo json_encode([
                    'totalRows' => $totalRows,
                    'currentRowIndex' => $counter,
                    'currentRow' => $member,
                    'message' => $messageText,
                    'percentage' => (int) (($counter / $totalRows) * 100),
                    'status' => true,
                ]);

                ob_flush();
                flush();
            }
        });

        return $response;
    }

    /**
     * CAPITOL SMS Functions
     */

    /**
     * @Route("/ajax_select2_capitol_municipality",
     *       name="ajax_select2_capitol_municipality",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2CapitolMunicipality(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT d.municipality FROM tbl_employee_directory d WHERE d.municipality LIKE ? ORDER BY d.municipality ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $address = $stmt->fetchAll();

        if (count($address) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($address);
    }

    /**
     * @Route("/ajax_select2_capitol_barangay",
     *       name="ajax_select2_capitol_barangay",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSelect2CapitolBarangay(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT d.barangay FROM tbl_employee_directory d WHERE d.barangay LIKE ? ORDER BY d.barangay ASC LIMIT 30";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $searchText);
        $stmt->execute();

        $address = $stmt->fetchAll();

        if (count($address) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($address);
    }

    /** NEWLY ADDED FUNCTIONS */

    /**
     * @Route("/ajax_post_new_voter",
     *     name="ajax_post_new_voter",
     *    options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostNewVoterAction($voterId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $voter = $em->getRepository("AppBundle:Voter")->find($voterId);
        $issuedAt = empty($request->get("issuedAt")) ? new \DateTime() : new \DateTime($request->get("issuedAt"));

        if (!$voter) {
            return new JsonResponse(null, 404);
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$this->isAllowed($voter->getProvinceCode(), $voter->getMunicipalityNo(), $voter->getBrgyNo())) {
            return new JsonResponse(null, 401);
        }

        $entity = new VoterAssistance();
        $entity->setVoterId($voterId);
        $entity->setProvinceCode($voter->getProvinceCode());
        $entity->setMunicipalityNo($voter->getMunicipalityNo());
        $entity->setBrgyNo($voter->getBrgyNo());
        $entity->setDescription($request->get("description"));
        $entity->setCategory($request->get("category"));
        $entity->setAmount($request->get("amount"));
        $entity->setCreatedBy($user->getUsername());
        $entity->setIssuedAt($issuedAt);
        $entity->setCreatedAt(new \DateTime);
        $entity->setStatus(self::STATUS_ACTIVE);
        $entity->setRemarks($request->get("remarks"));

        $validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->persist($entity);
        $em->flush();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_post_project_temporary_voter",
     *     name="ajax_post_project_temporary_voter",
     *    options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostProjectTemporaryVoterAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entity = new ProjectVoter();
        $entity->setProId($request->get('proId'));
        $entity->setElectId($request->get('electId'));
        $entity->setFirstname(trim(strtoupper($request->get('firstname'))));
        $entity->setMiddlename(trim(strtoupper($request->get('middlename'))));
        $entity->setLastname(trim(strtoupper($request->get('lastname'))));
        $entity->setExtname(trim(strtoupper($request->get('extName'))));

        $voterName = $entity->getLastname() . ', ' . $entity->getFirstname() . ' ' . $entity->getMiddlename() . ' ' . $entity->getExtname();
        $entity->setVoterName(trim(strtoupper($voterName)));
        $entity->setGender($request->get('gender'));
        $entity->setCivilStatus(trim(strtoupper($request->get('civilStatus'))));
        $entity->setBloodtype(trim(strtoupper($request->get('bloodtype'))));
        $entity->setOccupation(trim(strtoupper($request->get('occupation'))));
        $entity->setReligion(trim(strtoupper($request->get('religion'))));
        $entity->setDialect(trim(strtoupper($request->get('dialect'))));
        $entity->setIpGroup(trim(strtoupper($request->get('ipGroup'))));
        $entity->setVoterGroup(trim(strtoupper($request->get('voterGroup'))));
        $entity->setPosition(trim(strtoupper($request->get('position'))));
        
        $entity->setBirthdate(trim($request->get('birthdate')));
        $entity->setIsNonVoter(1);
        $entity->setHasId(0);
        $entity->setHasPhoto(0);
        $entity->setDidChanged(1);
        $entity->setToSend(1);
        $entity->setCellphone($request->get('cellphoneNo'));
        $entity->setVoterGroup(trim(strtoupper($request->get('voterGroup'))));

        $entity->setProvinceCode(53);
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBrgyNo($request->get('brgyNo'));

        $proId = $request->get('proId');
        $munNo = $request->get('municipalityNo');

        $entity->setProIdCode($this->generateProIdCode($proId, $voterName, $munNo));

        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
        $entity->setStatus(self::STATUS_ACTIVE);

        $validator = $this->get('validator');
        $violations = $validator->validate($entity, null, ['create']);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->persist($entity);
        $em->flush();

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
        $stmt->bindValue(1, 53 . $entity->getMunicipalityNo() . $entity->getBrgyNo());
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($barangay != null) {
            $entity->setBarangayName($barangay['name']);
        }

        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_patch_project_temporary_voter/{proVoterId}",
     *     name="ajax_patch_project_temporary_voter",
     *    options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchProjectTemporaryVoterAction(Request $request, $proVoterId)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $entity = $em->getRepository("AppBundle:ProjectVoter")->find($proVoterId);

        if (!$entity) {
            return new JsonResponse([], 404);
        }

        $entity->setFirstname(trim(strtoupper($request->get('firstname'))));
        $entity->setMiddlename(trim(strtoupper($request->get('middlename'))));
        $entity->setLastname(trim(strtoupper($request->get('lastname'))));
        $entity->setExtname(trim(strtoupper($request->get('extName'))));

        $voterName = $entity->getLastname() . ', ' . $entity->getFirstname() . ' ' . $entity->getMiddlename() . ' ' . $entity->getExtname();
        $entity->setVoterName(trim(strtoupper($voterName)));
        $entity->setGender($request->get('gender'));
        $entity->setCivilStatus(trim(strtoupper($request->get('civilStatus'))));
        $entity->setBloodtype(trim(strtoupper($request->get('bloodtype'))));
        $entity->setOccupation(trim(strtoupper($request->get('occupation'))));
        $entity->setReligion(trim(strtoupper($request->get('religion'))));
        $entity->setDialect(trim(strtoupper($request->get('dialect'))));
        $entity->setIpGroup(trim(strtoupper($request->get('ipGroup'))));
        $entity->setBirthdate(trim($request->get('birthdate')));
        $entity->setCellphone($request->get('cellphoneNo'));
        $entity->setVoterGroup(trim(strtoupper($request->get('voterGroup'))));
        $entity->setPosition(trim(strtoupper($request->get('position'))));

        $entity->setProvinceCode(53);
        $entity->setMunicipalityNo($request->get('municipalityNo'));
        $entity->setBrgyNo($request->get('brgyNo'));

        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy($user->getUsername());
        $entity->setStatus(self::STATUS_ACTIVE);

        $validator = $this->get('validator');
        $violations = $validator->validate($entity, null, ['edit']);

        $errors = [];

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 400);
        }

        $em->flush();

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
        $stmt->bindValue(1, 53 . $entity->getMunicipalityNo() . $entity->getBrgyNo());
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($barangay != null) {
            $entity->setBarangayName($barangay['name']);
        }

        $em->flush();
        $em->clear();

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($entity));
    }

    /**
     * @Route("/ajax_delete_temporary_voter/{proVoterId}",
     *     name="ajax_delete_temporary_voter",
     *    options={"expose" = true}
     * )
     * @Method("DELETE")
     */

    public function ajaxDeleteTemporaryVoterAction($proVoterId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:ProjectVoter")->find($proVoterId);
        $em->remove($entity);
        $em->flush();

        return new JsonResponse(null, 200);
    }
}
