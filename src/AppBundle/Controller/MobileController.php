<?php
namespace AppBundle\Controller;

use AppBundle\Entity\PendingVoter;
use AppBundle\Entity\ProjectEventDetail;
use AppBundle\Entity\ProjectVoter;
use AppBundle\Entity\Voter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/mobi")
 */

class MobileController extends Controller
{
    const ACTIVE_ELECTION = 4;
    const ACTIVE_PROJECT = 3;
    const ACTIVE_STATUS = 'A';

    /**
     * @Route("/ajax_m_get_municipalities",
     *       name="ajax_m_get_municipalities",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetMunicipality(Request $request)
    {
        $provinceCode = 53;

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM psw_municipality m WHERE m.province_code = ? AND m.municipality_no IN ('01','16') ORDER BY m.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);

        $stmt->execute();
        $municipalities = $stmt->fetchAll();

        if (count($municipalities) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($municipalities);
    }

    /**
     * @Route("/ajax_m_get_barangays/{municipalityCode}",
     *       name="ajax_m_get_barangays",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetBarangays(Request $request, $municipalityCode)
    {

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM psw_barangay b
                WHERE b.municipality_code = ? ORDER BY b.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityCode);

        $stmt->execute();
        $barangays = $stmt->fetchAll();

        if (count($barangays) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($barangays);
    }

    /**
     * @Route("/ajax_m_get_project_voters",
     *       name="ajax_m_get_project_voters",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProjectVoters(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $provinceCode = substr($request->get("municipalityCode"), 0, 2);
        $municipalityNo = substr($request->get("municipalityCode"), -2);
        $brgyNo = $request->get("brgyNo");
        $voterName = $request->get("voterName");
        $imgUrl = $this->getParameter('img_url');
        $batchSize = 3;
        $batchNo = $request->get("batchNo");

        $batchOffset = $batchNo * $batchSize;

        $sql = "SELECT pv.* FROM tbl_project_event_header eh 
                LEFT JOIN tbl_project_event_detail ed
                ON  ed.event_id = eh.event_id
                INNER JOIN tbl_project_voter pv 
                ON pv.pro_voter_id = ed.pro_voter_id 
                WHERE eh.status = 'A' AND pv.has_id <> 1 AND pv.has_photo <> 1 AND ";

        if (!is_numeric($voterName)) {
            $sql .= " (pv.voter_name LIKE ? OR ? IS NULL ) ";
        } else {
            $sql .= " (pv.generated_id_no LIKE ? OR ? IS NULL ) ";
        }

        $sql .= "AND pv.elect_id = ? ORDER BY pv.voter_name ASC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, '%' . $voterName . '%');
        $stmt->bindValue(2, empty($voterName) ? null : '%' . $voterName . '%');
        $stmt->bindValue(3, self::ACTIVE_ELECTION);
        $stmt->execute();

        $municipalities = $this->getMunicipalities(53);


        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl .  '3_' . $row['generated_id_no'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        foreach($data as &$row){
             //$lgc = $this->getLGC($row['municipality_no'], $row['brgy_no']);
             $row['lgc'] = [
                'voter_name' => '- disabled -',//$lgc['voter_name'],
                'cellphone' => '- disabled -'//$lgc['cellphone']
            ];
        }

        return new JsonResponse($data);
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


 /**
     * @Route("/ajax_m_get_project_voters_all",
     *       name="ajax_m_get_project_voters_all",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProjectVotersAll(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $provinceCode = substr($request->get("municipalityCode"), 0, 2);
        $municipalityNo = substr($request->get("municipalityCode"), -2);
        $municipalityName  = $request->get('municipalityName');
        $barangayName = $request->get('barangayName');
        $voterGroup = $request->get('voterGroup');

        $brgyNo = $request->get("brgyNo");
        $voterName = $request->get("voterName");
        $imgUrl = $this->getParameter('img_url');
        $batchSize = 3;
        $batchNo = $request->get("batchNo");

        $batchOffset = $batchNo * $batchSize;

        $sql = "SELECT pv.* FROM tbl_project_voter pv WHERE 1 AND ";

        if (!is_numeric($voterName)) {
            $sql .= " (pv.voter_name LIKE ? OR ? IS NULL ) ";
        } else {
            $sql .= " (pv.generated_id_no LIKE ? OR ? IS NULL ) ";
        }

        $sql .= "AND pv.elect_id = ? 
        AND (pv.municipality_name = ? OR ? IS NULL) 
        AND (pv.barangay_name = ? OR ? IS NULL) 
        AND (pv.voter_group = ? OR ? IS NULL) 
        ORDER BY pv.voter_name ASC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, '%' . $voterName . '%');
        $stmt->bindValue(2, empty($voterName) ? null : '%' . $voterName . '%');
        $stmt->bindValue(3, self::ACTIVE_ELECTION);
        $stmt->bindValue(4, $municipalityName);
        $stmt->bindValue(5, empty($municipalityName) ? null : $municipalityName );
        $stmt->bindValue(6, $barangayName);
        $stmt->bindValue(7, empty($barangayName) ? null : $barangayName );
        $stmt->bindValue(8, $voterGroup);
        $stmt->bindValue(9, empty($voterGroup) ? null : $voterGroup );
        $stmt->execute();

        $municipalities = $this->getMunicipalities(53);


        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl .  '3_' . $row['generated_id_no'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        foreach($data as &$row){
             $lgc = $this->getLGC($row['municipality_no'], $row['brgy_no']);
             $row['lgc'] = [
                'voter_name' => '- disabled -',//$lgc['voter_name'],
                'cellphone' => '- disabled -'//$lgc['cellphone']
            ];
        }

        return new JsonResponse($data);
    }


    /**
     * @Route("/ajax_m_summary_dates",
     *       name="ajax_m_summary_dates",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxSummaryDates(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $electId = $request->get('electId');
        $proId = $request->get('proId');

        $sql = "SELECT DISTINCT created_at
                FROM tbl_project_voter_summary
                WHERE elect_id = ? AND pro_id = ?
                ORDER BY created_at DESC LIMIT 10";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $electId);
        $stmt->bindValue(2, $proId);
        $stmt->execute();

        $dates = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $dates[] = $row;
        }

        return new JsonResponse($dates);
    }

    /**
     * @Route("/ajax_get_m_get_prev_summary_date",
     *       name="ajax_get_m_get_prev_summary_date",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function getPrevSummaryDate(Request $request)
    {
        $em = $this->getDoctrine();
        $electId = $request->get('electId');
        $proId = $request->get('proId');
        $createdAt = $request->get("createdAt");

        $sql = "SELECT DISTINCT created_at
        FROM tbl_project_voter_summary
        WHERE elect_id = ? AND pro_id = ? AND created_at < ?
        ORDER BY created_at DESC LIMIT 1";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $electId);
        $stmt->bindValue(2, $proId);
        $stmt->bindValue(3, $createdAt);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row == null ? new JsonResponse(['created_at' => null]) : new JsonResponse($row);
    }

    /**
     * @Route("/ajax_m_get_project_voter/{proId}/{generatedIdNo}",
     *       name="ajax_m_get_project_voter",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProjectVoter($proId, $generatedIdNo)
    {
        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'generatedIdNo' => $generatedIdNo,
            'proId' => $proId,
            'electId' => self::ACTIVE_ELECTION,
        ]);

        if (!$projectVoter) {
            return new JsonResponse(null, 404);
        }

        $serializer = $this->get('serializer');

        $projectVoter = $serializer->normalize($projectVoter);
        $projectVoter['imgUrl'] = $imgUrl . $proId . '_' . $projectVoter['generatedIdNo'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
        $projectVoter['cellphoneNo'] = $projectVoter['cellphone'];

        $lgc = $this->getLGC($projectVoter['municipalityNo'], $projectVoter['brgyNo']);
        $projectVoter['lgc'] = [
            'voter_name' => $lgc['voter_name'],
            'cellphone' => $lgc['cellphone']
        ];

        return new JsonResponse($projectVoter);
    }

    /**
     * @Route("/ajax_m_get_project_voter_alt/{proId}/{proVoterId}",
     *       name="ajax_m_get_project_voter_alt",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProjectVoterAlt($proId, $proVoterId)
    {

        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proVoterId' => $proVoterId,
            'proId' => $proId,
            'electId' => self::ACTIVE_ELECTION,
        ]);

        if (!$projectVoter) {
            return new JsonResponse(null, 404);
        }

        $serializer = $this->get('serializer');

        $projectVoter = $serializer->normalize($projectVoter);
        $projectVoter['imgUrl'] = $imgUrl . $proId . '_' . $projectVoter['generatedIdNo'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
        $projectVoter['cellphoneNo'] = $projectVoter['cellphone'];

        $lgc = $this->getLGC($projectVoter['municipalityNo'], $projectVoter['brgyNo']);
        $projectVoter['lgc'] = [
            'voter_name' => $lgc['voter_name'],
            'cellphone' => $lgc['cellphone']
        ];

        return new JsonResponse($projectVoter);
    }

    /**
     * @Route("/ajax_m_get_active_event/{proId}",
     *       name="ajax_m_active_event",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEvent($proId)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'proId' => $proId,
            'status' => self::ACTIVE_STATUS,
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $serializer = $this->get('serializer');

        return new JsonResponse($serializer->normalize($event));
    }

    /**
     * @Route("/ajax_m_get_active_event_barangays/{proId}",
     *       name="ajax_m_active_event_barangays",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventBarangays($proId)
    {
        $em = $this->getDoctrine()->getManager();
        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'proId' => $proId,
            'status' => self::ACTIVE_STATUS,
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }
        
        $sql = "SELECT DISTINCT pv.barangay_name FROM tbl_project_event_detail ed 
                INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = ed.pro_voter_id 
                WHERE ed.event_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $event->getEventid());
        $stmt->execute();

        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $serializer = $this->get('serializer');

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_m_get_active_event_attendees",
     *       name="ajax_m_active_event_attendees",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventAttendees(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');

        $batchSize = 5;
        $batchNo = $request->get("batchNo");
        $voterName = $request->get("voterName");
        $eventId = $request->get('eventId');
        $barangayName = $request->get('barangayName');

        $batchOffset = $batchNo * $batchSize;

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => self::ACTIVE_STATUS,
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT
        COALESCE(SUM( CASE WHEN ed.has_attended = 1 THEN 1 ELSE 0 END),0) AS total_attended,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
        COALESCE(COUNT(ed.event_detail_id),0) AS total_expected
        FROM tbl_project_event_detail ed INNER JOIN tbl_project_voter pv 
        ON pv.pro_voter_id = ed.pro_voter_id 
        WHERE ed.event_id = ? AND (pv.barangay_name = ? OR ? IS NULL )";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, strtoupper(trim($barangayName)));
        $stmt->bindValue(3, empty($barangayName) ? null : $barangayName);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        $sql = "SELECT pv.*
        FROM tbl_project_event_detail ed
        INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = ed.pro_voter_id
        WHERE ed.event_id  = ? AND (pv.voter_name LIKE ? OR ? IS NULL ) 
        AND (pv.barangay_name = ? OR ? IS NULL) 
        ORDER BY ed.attended_at DESC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, '%' . strtoupper(trim($voterName)) . '%');
        $stmt->bindValue(3, empty($voterName) ? null : $voterName);
        $stmt->bindValue(4, strtoupper(trim($barangayName)) );
        $stmt->bindValue(5, empty($barangayName) ? null : $barangayName);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl . self::ACTIVE_PROJECT . '_' . $row['generated_id_no'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        foreach($data as &$row){
            //$lgc = $this->getLGC($row['municipality_no'], $row['brgy_no']);
            $row['lgc'] = [
                'voter_name' => '- disabled -',//$lgc['voter_name'],
                'cellphone' => '- disabled -'//$lgc['cellphone']
            ];
        }

        return new JsonResponse([
            "data" => $data,
            "totalExpected" => $summary['total_expected'],
            "totalAttended" => $summary['total_attended'],
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm']
        ]);
    }

    /**
     * @Route("/ajax_m_get_active_event_attendees_summary",
     *       name="ajax_m_get_active_event_attendees_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventAttendeesSummary(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');

        $batchSize = 5;
        $batchNo = $request->get("batchNo");
        $voterName = $request->get("voterName");
        $eventId = $request->get('eventId');
        $barangayName = $request->get('barangayName');

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => self::ACTIVE_STATUS,
        ]);

        
        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT
        COALESCE(SUM( CASE WHEN ed.has_attended = 1 THEN 1 ELSE 0 END),0) AS total_attended,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
        COALESCE(SUM( CASE WHEN pv.voter_group IS NULL OR pv.voter_group = '' THEN 1 ELSE 0 END),0) AS total_no_position,
        COALESCE(COUNT(ed.event_detail_id),0) AS total_expected
        FROM tbl_project_event_detail ed INNER JOIN tbl_project_voter pv 
        ON pv.pro_voter_id = ed.pro_voter_id 
        WHERE ed.event_id = ? AND (pv.barangay_name = ? OR ? IS NULL )";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, strtoupper(trim($barangayName)));
        $stmt->bindValue(3, empty($barangayName) ? null : $barangayName);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse([
            "totalExpected" => $summary['total_expected'],
            "totalAttended" => $summary['total_attended'],
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm'],
            'totalNoPosition' => $summary['total_no_position']
        ]);
    }

     /**
     * @Route("/ajax_m_get_jpm_province_summary",
     *       name="ajax_m_get_jpm_province_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetJpmProvinceSummary(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT 
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.is_non_voter = 1 THEN 1 ELSE 0 END),0) AS total_lgc_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lgo_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lopp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_jpm_non_voter,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.has_id = 1 THEN 1 ELSE 0 END),0) AS total_lgc_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lgo_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lopp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_jpm_has_id,
            (SELECT COALESCE(SUM(m.total_precincts),0)  FROM psw_municipality m WHERE m.province_code = 53 AND m.municipality_no <> 16 ) AS total_precincts
            FROM tbl_project_voter pv
            WHERE pv.elect_id = ? AND pro_id = ? AND pv.has_id = 1 AND  pv.voter_group IN ('LGC','LGO','LOPP','LPPP','LPPP1','LPPP2','LPPP3','JPM')";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, self::ACTIVE_ELECTION);
        $stmt->bindValue(2, 3);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse([
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm'],

            "totalLgcHasId" => $summary['total_lgc_has_id'],
            'totalLgoHasId' => $summary['total_lgo_has_id'],
            "totalLoppHasId" => $summary['total_lopp_has_id'],
            "totalLpppHasId" => $summary['total_lppp_has_id'],
            "totalLppp1HasId" => $summary['total_lppp1_has_id'],
            'totalLppp2HasId' => $summary['total_lppp2_has_id'],
            'totalLppp3HasId' => $summary['total_lppp3_has_id'],
            'totalJpmHasId' => $summary['total_jpm_has_id'],

            "totalLgcNonVoter" => $summary['total_lgc_non_voter'],
            'totalLgoNonVoter' => $summary['total_lgo_non_voter'],
            "totalLoppNonVoter" => $summary['total_lopp_non_voter'],
            "totalLpppNonVoter" => $summary['total_lppp_non_voter'],
            "totalLppp1NonVoter" => $summary['total_lppp1_non_voter'],
            'totalLppp2NonVoter' => $summary['total_lppp2_non_voter'],
            'totalLppp3NonVoter' => $summary['total_lppp3_non_voter'],
            'totalJpmNonVoter' => $summary['total_jpm_non_voter'],
            'totalPrecincts' => $summary['total_precincts']
        ]);
    }

    /**
     * @Route("/ajax_m_get_jpm_district_summary/{district}",
     *       name="ajax_m_get_jpm_district_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetJpmDistrictSummary($district, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT 
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.is_non_voter = 1 THEN 1 ELSE 0 END),0) AS total_lgc_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lgo_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lopp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_jpm_non_voter,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.has_id = 1 THEN 1 ELSE 0 END),0) AS total_lgc_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lgo_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lopp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_jpm_has_id,
            (SELECT COALESCE(SUM(m2.total_precincts),0)  FROM psw_municipality m2 WHERE m2.province_code = 53 AND m2.municipality_no <> 16 AND m2.district = m.district ) AS total_precincts
            FROM tbl_project_voter pv
            INNER JOIN psw_municipality m on m.municipality_no = pv.municipality_no AND m.province_code = 53
            WHERE pv.elect_id = ? AND pv.pro_id = ? AND m.district = ? AND pv.has_id = 1 AND pv.voter_group IN ('LGC','LGO','LOPP','LPPP','LPPP1','LPPP2','LPPP3','JPM')";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, self::ACTIVE_ELECTION);
        $stmt->bindValue(2, 3);
        $stmt->bindValue(3, $district);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse([
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm'],

            "totalLgcHasId" => $summary['total_lgc_has_id'],
            'totalLgoHasId' => $summary['total_lgo_has_id'],
            "totalLoppHasId" => $summary['total_lopp_has_id'],
            "totalLpppHasId" => $summary['total_lppp_has_id'],
            "totalLppp1HasId" => $summary['total_lppp1_has_id'],
            'totalLppp2HasId' => $summary['total_lppp2_has_id'],
            'totalLppp3HasId' => $summary['total_lppp3_has_id'],
            'totalJpmHasId' => $summary['total_jpm_has_id'],

            "totalLgcNonVoter" => $summary['total_lgc_non_voter'],
            'totalLgoNonVoter' => $summary['total_lgo_non_voter'],
            "totalLoppNonVoter" => $summary['total_lopp_non_voter'],
            "totalLpppNonVoter" => $summary['total_lppp_non_voter'],
            "totalLppp1NonVoter" => $summary['total_lppp1_non_voter'],
            'totalLppp2NonVoter' => $summary['total_lppp2_non_voter'],
            'totalLppp3NonVoter' => $summary['total_lppp3_non_voter'],
            'totalJpmNonVoter' => $summary['total_jpm_non_voter'],
            'totalPrecincts' => $summary['total_precincts']
        ]);
    }

    /**
     * @Route("/ajax_m_get_jpm_municipality_summary/{municipalityName}",
     *       name="ajax_m_get_jpm_municipality_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetJpmMunicipalitySummary(Request $request, $municipalityName)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT 
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.is_non_voter = 1 THEN 1 ELSE 0 END),0) AS total_lgc_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lgo_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lopp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_jpm_non_voter,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.has_id = 1 THEN 1 ELSE 0 END),0) AS total_lgc_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lgo_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lopp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_jpm_has_id,
            (SELECT m2.total_precincts  FROM psw_municipality m2 WHERE m2.province_code = 53 AND m2.municipality_no = pv.municipality_no ) AS total_precincts 
            FROM tbl_project_voter pv
            WHERE pv.elect_id = ? 
            AND pv.pro_id = ?
            AND pv.municipality_name = ? 
            AND pv.has_id = 1
            AND pv.voter_group IN ('LGC','LGO','LOPP','LPPP','LPPP1','LPPP2','LPPP3','JPM')";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, self::ACTIVE_ELECTION);
        $stmt->bindValue(2, 3);
        $stmt->bindValue(3, $municipalityName);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse([
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm'],

            "totalLgcHasId" => $summary['total_lgc_has_id'],
            'totalLgoHasId' => $summary['total_lgo_has_id'],
            "totalLoppHasId" => $summary['total_lopp_has_id'],
            "totalLpppHasId" => $summary['total_lppp_has_id'],
            "totalLppp1HasId" => $summary['total_lppp1_has_id'],
            'totalLppp2HasId' => $summary['total_lppp2_has_id'],
            'totalLppp3HasId' => $summary['total_lppp3_has_id'],
            'totalJpmHasId' => $summary['total_jpm_has_id'],

            "totalLgcNonVoter" => $summary['total_lgc_non_voter'],
            'totalLgoNonVoter' => $summary['total_lgo_non_voter'],
            "totalLoppNonVoter" => $summary['total_lopp_non_voter'],
            "totalLpppNonVoter" => $summary['total_lppp_non_voter'],
            "totalLppp1NonVoter" => $summary['total_lppp1_non_voter'],
            'totalLppp2NonVoter' => $summary['total_lppp2_non_voter'],
            'totalLppp3NonVoter' => $summary['total_lppp3_non_voter'],
            'totalJpmNonVoter' => $summary['total_jpm_non_voter'],
            'totalPrecincts' => $summary['total_precincts']
        ]);
    }
    
    /**
     * @Route("/ajax_m_get_jpm_barangay_summary/{municipalityName}/{barangayName}",
     *       name="ajax_m_get_jpm_barangay_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetJpmBarangaySummary(Request $request, $municipalityName, $barangayName )
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT 
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.is_non_voter = 1 THEN 1 ELSE 0 END),0) AS total_lgc_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lgo_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lopp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_non_voter,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.is_non_voter = 1  THEN 1 ELSE 0 END),0) AS total_jpm_non_voter,
            
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' AND pv.has_id = 1 THEN 1 ELSE 0 END),0) AS total_lgc_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lgo_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lopp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp1_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp2_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_lppp3_has_id,
            COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' AND pv.has_id = 1  THEN 1 ELSE 0 END),0) AS total_jpm_has_id,
            (
                SELECT SUM(b.total_precincts)
                FROM 
                psw_barangay b INNER JOIN 
                psw_municipality m ON m.municipality_code = b.municipality_code AND m.province_code = 53
                WHERE 
                m.name = ? AND b.name = ?
            ) AS total_precincts

            FROM tbl_project_voter pv
            WHERE pv.elect_id = ? 
            AND pv.pro_id = ?
            AND pv.municipality_name = ?  
            AND pv.barangay_name = ?
            AND pv.has_id = 1
            AND pv.voter_group IN ('LGC','LGO','LOPP','LPPP','LPPP1','LPPP2','LPPP3','JPM')";

        $stmt = $em->getConnection()->prepare($sql);
        
        $stmt->bindValue(1, $municipalityName);
        $stmt->bindValue(2, $barangayName);
        $stmt->bindValue(3, self::ACTIVE_ELECTION);
        $stmt->bindValue(4, 3);
        $stmt->bindValue(5, $municipalityName);
        $stmt->bindValue(6, $barangayName);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse([
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm'],

            "totalLgcHasId" => $summary['total_lgc_has_id'],
            'totalLgoHasId' => $summary['total_lgo_has_id'],
            "totalLoppHasId" => $summary['total_lopp_has_id'],
            "totalLpppHasId" => $summary['total_lppp_has_id'],
            "totalLppp1HasId" => $summary['total_lppp1_has_id'],
            'totalLppp2HasId' => $summary['total_lppp2_has_id'],
            'totalLppp3HasId' => $summary['total_lppp3_has_id'],
            'totalJpmHasId' => $summary['total_jpm_has_id'],

            "totalLgcNonVoter" => $summary['total_lgc_non_voter'],
            'totalLgoNonVoter' => $summary['total_lgo_non_voter'],
            "totalLoppNonVoter" => $summary['total_lopp_non_voter'],
            "totalLpppNonVoter" => $summary['total_lppp_non_voter'],
            "totalLppp1NonVoter" => $summary['total_lppp1_non_voter'],
            'totalLppp2NonVoter' => $summary['total_lppp2_non_voter'],
            'totalLppp3NonVoter' => $summary['total_lppp3_non_voter'],
            'totalJpmNonVoter' => $summary['total_jpm_non_voter'],
            'totalPrecincts' => $summary['total_precincts']
        ]);
    }
    

     /**
     * @Route("/ajax_m_active_event_attendees_summary_by_position",
     *       name="ajax_m_active_event_attendees_summary_by_position",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventAttendeesSummaryPosition(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $eventId = $request->get('eventId');
        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'status' => self::ACTIVE_STATUS,
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT
        COALESCE(SUM( CASE WHEN ed.has_attended = 1 THEN 1 ELSE 0 END),0) AS total_attended,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
        COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
        COALESCE(COUNT(ed.event_detail_id),0) AS total_expected
        FROM tbl_project_event_detail ed INNER JOIN tbl_project_voter pv 
        ON pv.pro_voter_id = ed.pro_voter_id 
        WHERE ed.event_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $event->getEventId());
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new JsonResponse([
            "totalExpected" => $summary['total_expected'],
            "totalAttended" => $summary['total_attended'],
            "totalLgc" => $summary['total_lgc'],
            'totalLgo' => $summary['total_lgo'],
            "totalLopp" => $summary['total_lopp'],
            "totalLppp" => $summary['total_lppp'],
            "totalLppp1" => $summary['total_lppp1'],
            'totalLppp2' => $summary['total_lppp2'],
            'totalLppp3' => $summary['total_lppp3'],
            'totalJpm' => $summary['total_jpm']
        ]);
    }

     /**
     * @Route("/ajax_m_active_event_attendees_summary_by_barangay",
     *       name="ajax_m_active_event_attendees_summary_by_barangay",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventAttendeesSummaryByBarangay(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $eventId = $request->get('eventId');
        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'status' => self::ACTIVE_STATUS,
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT
                COALESCE(SUM( CASE WHEN ed.has_attended = 1 THEN 1 ELSE 0 END),0) AS total_attended,
                COALESCE(COUNT(ed.event_detail_id),0) AS total_expected,
                COALESCE(COUNT(pv.pro_voter_id),0) AS total_attendees_per_barangay,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'LGC' THEN 1 ELSE 0 END),0) AS total_lgc,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'LGO' THEN 1 ELSE 0 END),0) AS total_lgo,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'LOPP' THEN 1 ELSE 0 END),0) AS total_lopp,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP' THEN 1 ELSE 0 END),0) AS total_lppp,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP1' THEN 1 ELSE 0 END),0) AS total_lppp1,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP2' THEN 1 ELSE 0 END),0) AS total_lppp2,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'LPPP3' THEN 1 ELSE 0 END),0) AS total_lppp3,
                COALESCE(SUM( CASE WHEN pv.voter_group = 'JPM' THEN 1 ELSE 0 END),0) AS total_jpm,
                pv.barangay_name,
                pv.municipality_name
                FROM tbl_project_event_detail ed INNER JOIN tbl_project_voter pv 
                ON pv.pro_voter_id = ed.pro_voter_id 
                WHERE ed.event_id = ? AND pv.voter_group IS NOT NULL AND pv.voter_group <> ''
                GROUP BY pv.municipality_no, pv.brgy_no
                ORDER BY pv.municipality_name, pv.barangay_name ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $event->getEventId());
        $stmt->execute();

        $summary = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return new JsonResponse($summary);
    }


    /**
     * @Route("/ajax_m_get_active_event_expected_attendees",
     *       name="ajax_m_active_event_expected_attendees",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventExpectedAttendees(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');

        $batchSize = 5;
        $batchNo = $request->get("batchNo");
        $voterName = $request->get("voterName");
        $eventId = $request->get('eventId');
        $displayAll = $request->get('displayAll');

        $batchOffset = $batchNo * $batchSize;

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => 'A',
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT
        COALESCE(SUM( CASE WHEN ed.has_attended = 1 THEN 1 ELSE 0 END),0) AS total_attended,
        COALESCE(COUNT(ed.event_detail_id),0) AS total_expected
        FROM tbl_project_event_detail ed WHERE ed.event_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        $sql = "SELECT pv.*
        FROM tbl_project_event_detail ed
        INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = ed.pro_voter_id
        WHERE ed.event_id  = ? ";

        if (!is_numeric($voterName)) {
            $sql .= " AND (pv.voter_name LIKE ? OR ? IS NULL ) ";
        } else {
            $sql .= " AND (pv.pro_id_code LIKE ? OR ? IS NULL ) ";
        }

        if ($displayAll == 0) {
            $sql .= " AND (pv.has_photo = 0 OR pv.has_photo IS NULL) ";
        }

        $sql .= " ORDER BY pv.voter_name ASC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, '%' . strtoupper(trim($voterName)) . '%');
        $stmt->bindValue(3, empty($voterName) ? null : $voterName);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl . self::ACTIVE_PROJECT . '_' . $row['pro_id_code'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        return new JsonResponse([
            "data" => $data,
            "totalExpected" => $summary['total_expected'],
            "totalAttended" => $summary['total_attended'],
        ]);
    }

    /**
     * @Route("/ajax_m_get_active_event_claimed_attendees",
     *       name="ajax_m_active_event_claimed_attendees",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventClaimedAttendees(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');

        $batchSize = 3;
        $batchNo = $request->get("batchNo");
        $voterName = $request->get("voterName");
        $eventId = $request->get('eventId');

        $batchOffset = $batchNo * $batchSize;

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => 'A',
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT
        COALESCE(SUM( CASE WHEN ed.has_attended = 1 THEN 1 ELSE 0 END),0) AS total_expected,
        COALESCE(SUM( CASE WHEN ed.has_claimed = 1 THEN 1 ELSE 0 END),0) AS total_claimed
        FROM tbl_project_event_detail ed WHERE ed.event_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->execute();

        $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

        $sql = "SELECT pv.*
        FROM tbl_project_event_detail ed
        INNER JOIN tbl_project_voter pv ON pv.pro_voter_id = ed.pro_voter_id
        WHERE ed.event_id  = ? AND ed.has_claimed = 1 AND (pv.voter_name LIKE ? OR ? IS NULL ) ORDER BY ed.claimed_at DESC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, '%' . strtoupper(trim($voterName)) . '%');
        $stmt->bindValue(3, empty($voterName) ? null : $voterName);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl . self::ACTIVE_PROJECT . '_' . $row['generated_id_no'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        return new JsonResponse([
            "data" => $data,
            "totalExpected" => $summary['total_expected'],
            "totalClaimed" => $summary['total_claimed'],
        ]);
    }

    private function getBarangay($municipalityCode, $brgyNo)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM psw_barangay b WHERE b.municipality_code = ? AND b.brgy_no = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityCode);
        $stmt->bindValue(2, $brgyNo);
        $stmt->execute();

        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $barangay;
    }

    /**
     * @Route("/ajax_m_get_jpm_municipalities",
     *       name="ajax_m_get_jpm_municipalities",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function getJpmMunicipalities(){
        return new JsonResponse($this->getMunicipalities(53));
    }

    private function getMunicipalities($provinceCode)
    {
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM psw_municipality m WHERE m.province_code = ? ORDER BY m.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->execute();

        $municipalities = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $municipalities[] = $row;
        }

        if (empty($municipalities)) {
            $municipalities = [];
        }

        return $municipalities;
    }

    /**
     * @Route("/ajax_m_get_jpm_districts",
     *       name="ajax_m_get_jpm_districts",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function getJpmDistricts(){
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT DISTINCT district FROM psw_municipality m WHERE m.province_code = ? ORDER BY m.district ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 53);
        $stmt->execute();

        $districts = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $districts[] = $row;
        }

        if (empty($districts)) {
            $districts = [];
        }

        return new JsonResponse($districts);
    }
    
    /**
     * @Route("/ajax_m_get_jpm_barangays/{municipalityName}",
     *       name="ajax_m_get_jpm_barangays",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetJpmBarangays(Request $request, $municipalityName)
    {

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT b.* FROM psw_barangay b 
                INNER JOIN psw_municipality m ON m.municipality_code = b.municipality_code AND m.province_code = 53
                WHERE m.name = ? ORDER BY b.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityName);

        $stmt->execute();
        $barangays = $stmt->fetchAll();

        if (count($barangays) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($barangays);
    }


    /**
     * @Route("/ajax_m_get_active_event_new_attendees",
     *       name="ajax_m_active_event_new_attendees",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetActiveEventNewAttendees(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $imgUrl = $this->getParameter('img_url');

        $batchSize = 3;
        $batchNo = $request->get("batchNo");
        $voterName = $request->get("voterName");
        $eventId = $request->get('eventId');

        $batchOffset = $batchNo * $batchSize;

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => 'A',
        ]);

        if (!$event) {
            return new JsonResponse(null, 404);
        }

        $sql = "SELECT COALESCE(COUNT(ed.event_detail_id)) FROM tbl_project_event_detail ed WHERE ed.event_id = ? AND ed.has_attended = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, 1);
        $stmt->execute();

        $totalExpected = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(ed.event_detail_id)) FROM tbl_project_event_detail ed WHERE ed.event_id = ? AND ed.has_new_id = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, 1);
        $stmt->execute();

        $totalNewMember = $stmt->fetchColumn();

        $sql = "SELECT v.*
        FROM tbl_project_event_detail ed
        INNER JOIN tbl_project_voter v ON v.voter_id = ed.voter_id
        WHERE ed.event_id  = ? AND ed.has_new_id = 1 AND (v.voter_name LIKE ? OR ? IS NULL ) ORDER BY ed.verify_at DESC LIMIT {$batchSize} OFFSET {$batchOffset}";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $eventId);
        $stmt->bindValue(2, '%' . strtoupper(trim($voterName)) . '%');
        $stmt->bindValue(3, empty($voterName) ? null : $voterName);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['imgUrl'] = $imgUrl . '3_' . $row['pro_id_code'] . '?' . strtotime((new \DateTime())->format('Y-m-d H:i:s'));
            $row['cellphone_no'] = $row['cellphone'];
            $data[] = $row;
        }

        return new JsonResponse([
            "data" => $data,
            "totalExpected" => $totalExpected,
            "totalNewMember" => $totalNewMember,
        ]);
    }

    /**
     * @Route("/ajax_m_post_event_attendee",
     *       name="ajax_m_post_attendee",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostEventAttendee(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);
        $eventId = $request->get('eventId');
        $proVoterId = $request->get("proVoterId");

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => 'A',
        ]);

        $eventDetail = $em->getRepository("AppBundle:ProjectEventDetail")->findOneBy([
            'proVoterId' => $proVoterId,
            'eventId' => $eventId,
        ]);

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy(['proVoterId' => $proVoterId]);

        if (!$event || !$projectVoter) {
            return new JsonResponse(['message' => 'Event not found. Please contact the system administrator.'], 400);
        }

        if (!$eventDetail) {

            if ($projectVoter->getStatus() != 'A') {
                return new JsonResponse(['message' => "Opps! Action denied... Voter either blocked or deactivated..."], 400);
            }

            $entity = new ProjectEventDetail();
            $entity->setProVoterId($proVoterId);
            $entity->setEventId($eventId);
            $entity->setProId(3);
            $entity->setHasAttended(1);
            $entity->setHasClaimed(0);
            $entity->setHasNewId(0);
            $entity->setCreatedAt(new \DateTime());
            $entity->setCreatedBy('android_app');
            $entity->setAttendedAt(new \DateTime());
            $em->persist($entity);

        } else {

            if ($eventDetail->getHasAttended()) {
                return new JsonResponse(['message' => 'Opps! Attendee already registered'], 400);
            }

            $eventDetail->setHasAttended(1);
            $eventDetail->setAttendedAt(new \DateTime());
        }

        $em->flush();
        $em->clear();

        return new JsonResponse(null);
    }

    /**
     * @Route("/ajax_m_post_event_attendee_claim_turon",
     *       name="ajax_m_post_event_attendee_claim_turon",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostEventAttendeeClaimToron(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);
        $eventId = $request->get('eventId');
        $proVoterId = $request->get("proVoterId");

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => 'A',
        ]);

        $eventDetail = $em->getRepository("AppBundle:ProjectEventDetail")->findOneBy([
            'proVoterId' => $proVoterId,
            'eventId' => $eventId,
        ]);

        if (!$event) {
            return new JsonResponse(['message' => 'Event not found. Please contact the system administrator.'], 400);
        }

        if (!$eventDetail) {
            $entity = new ProjectEventDetail();
            $entity->setProVoterId($proVoterId);
            $entity->setEventId($eventId);
            $entity->setProId(3);
            $entity->setHasClaimed(1);
            $entity->setHasNewId(0);
            $entity->setCreatedAt(new \DateTime());
            $entity->setCreatedBy('android_app');
            $entity->setAttendedAt(new \DateTime());
            $entity->setClaimedAt(new \DateTime());
            $em->persist($entity);

        } else {
            if ($eventDetail->getHasClaimed()) {
                return new JsonResponse(['message' => 'Opps! Ang ID na ito ay na scan na. Maaring na claim na ang pamasahe. Please contact the system administrator.'], 400);
            }

            $eventDetail->setHasClaimed(1);
            $eventDetail->setClaimedAt(new \DateTime());
        }

        $em->flush();
        $em->clear();

        return new JsonResponse(null);
    }

    /**
     * @Route("/ajax_m_post_event_attendee_cancel_claim",
     *       name="ajax_m_post_event_attendee_cancel_claim",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostEventAttendeeCancelClaim(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);
        $eventId = $request->get('eventId');
        $proVoterId = $request->get("proVoterId");

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => 'A',
        ]);

        $eventDetail = $em->getRepository("AppBundle:ProjectEventDetail")->findOneBy([
            'proVoterId' => $proVoterId,
            'eventId' => $eventId,
        ]);

        if (!$event) {
            return new JsonResponse(['message' => 'Event not found. Please contact the system administrator.'], 400);
        }

        $em->remove($eventDetail);
        $em->flush();
        $em->clear();

        return new JsonResponse(null);
    }

    /**
     * @Route("/ajax_m_post_event_new_attendee",
     *       name="ajax_m_post_event_new_attendee",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostEventNewAttendee(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);
        $eventId = $request->get('eventId');
        $proVoterId = $request->get("proVoterId");
        $voterId = $request->get('voterId');

        $event = $em->getRepository("AppBundle:ProjectEventHeader")->findOneBy([
            'eventId' => $eventId,
            'status' => 'A',
        ]);

        $eventDetail = $em->getRepository("AppBundle:ProjectEventDetail")->findOneBy([
            'proVoterId' => $proVoterId,
            'eventId' => $eventId,
        ]);

        if (!$event) {
            return new JsonResponse(['message' => 'Event not found. Please contact the system administrator.'], 400);
        }

        if (!$eventDetail) {
            $entity = new ProjectEventDetail();
            $entity->setProVoterId($proVoterId);
            $entity->setEventId($eventId);
            $entity->setProId(2);
            $entity->setHasAttended(1);
            $entity->setHasNewId(1);
            $entity->setHasClaimed(0);
            $entity->setCreatedAt(new \DateTime());
            $entity->setCreatedBy('android_app');
            $entity->setAttendedAt(new \DateTime());
            $entity->setVerifyAt(new \DateTime());
            $em->persist($entity);

        } else {
            if ($eventDetail->getHasNewId()) {
                return new JsonResponse(['message' => 'Opps! Attendee\'s ID already been verified.'], 400);
            }

            $eventDetail->setHasNewId(1);
            $eventDetail->setHasAttended(1);
            $eventDetail->setVerifyAt(new \DateTime());
        }

        $em->flush();
        $em->clear();

        return new JsonResponse(null);
    }

    /**
     * @Route("/ajax_m_get_project_voter_groups/{proId}",
     *       name="ajax_m_get_project_voter_groups",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProjectVoterGroups($proId)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT voter_group FROM tbl_project_voter
                WHERE voter_group IS NOT NULL AND voter_group <> '' AND pro_id = ? ORDER BY voter_group ASC";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $proId);
        $stmt->execute();

        $voterGroups = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $voterGroups[] = $row['voter_group'];
        }

        return new JsonResponse($voterGroups);
    }

    /**
     * @Route("/ajax_m_patch_project_voter/{proId}/{proVoterId}",
     *     name="ajax_m_patch_project_voter",
     *    options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchProjectVoterAction($proId, $proVoterId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
     
        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => $proId,
            'proVoterId' => $proVoterId,
        ]);

        $proVoter->setCellphone($request->get('cellphone'));
        $proVoter->setVoterGroup($request->get('voterGroup'));
        $proVoter->setUpdatedAt(new \DateTime());
        $proVoter->setUpdatedBy('android_app');
        $proVoter->setStatus('A');

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
     * @Route("/ajax_upload_m_project_voter_photo/{proId}/{proVoterId}",
     *     name="ajax_upload_m_project_voter_photo",
     *     options={"expose" = true}
     *     )
     * @Method("POST")
     */

    public function ajaxUploadProjectVoterPhoto(Request $request, $proId, $proVoterId)
    {
        $em = $this->getDoctrine()->getManager();

        $projectVoter = $em->getRepository("AppBundle:ProjectVoter")
            ->findOneBy(['proId' => $proId, 'proVoterId' => $proVoterId]);

        if (!$projectVoter) {
            return new JsonResponse(['message' => 'not found'], 404);
        }

        if($projectVoter->getGeneratedIdNo() == null || $projectVoter->getGeneratedIdNo() == '')
            return new JsonResponse(['message' => 'Please generate id'],400);
        
        $serializer = $this->get('serializer');

        $images = $request->files->get('files');
        $filename = $proId . '_' . $projectVoter->getGeneratedIdNo() . '.jpg';
        $imgRoot = __DIR__ . '/../../../web/uploads/images/';
        $imagePath = $imgRoot . $filename;

        $data = json_decode($request->getContent(), true);
        $this->compress(base64_decode($data['photo']), $imagePath, 30);

        $projectVoter->setHasPhoto(1);
        $projectVoter->setDidChanged(1);
        $projectVoter->setToSend(1);
        $projectVoter->setPhotoAt(new \DateTime());
        $projectVoter->setUpdatedAt(new \DateTime());
        $projectVoter->setUpdatedBy("android_app");

        $em->flush();
        $em->clear();

        return new JsonResponse(null, 200);
    }

    public function compress($source, $destination, $quality)
    {
        $image = imagecreatefromstring($source);

        imagejpeg($image, $destination, $quality);

        return $destination;
    }

    /**
     * @Route("/ajax_get_m_project_voter_generate_id_no/{proId}/{proVoterId}",
     *     name="ajax_get_m_project_voter_generate_id_no",
     *    options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxGenerateIdNoAction(Request $request, $proId, $proVoterId)
    {
        $em = $this->getDoctrine()->getManager();

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => $proId,
            'proVoterId' => $proVoterId
        ]);
        
        $voterName = $proVoter->getVoterName();
        $munNo = $proVoter->getMunicipalityNo();

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
        $proVoter->setRemarks($request->get('remarks'));
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

        $serializer = $this->get('serializer');
        
        return new JsonResponse($serializer->normalize($proVoter),200);
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
     * @Route("/ajax_get_m_project_voter_reprint_id/{proId}/{voterId}",
     *     name="ajax_get_m_project_voter_reprint_id",
     *    options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxResetIdAction(Request $request, $proId, $voterId)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository("AppBundle:Voter")->find($voterId);

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => $proId,
            'voterId' => $voterId,
        ]);

        if (!$proVoter) {
            return new JsonResponse(null, 404);
        }

        $proVoter->setHasId(null);
        $proVoter->setHasPhoto(1);
        $proVoter->setDidChange(1);
        $proVoter->setUpdatedAt(new \DateTime());
        $proVoter->setUpdatedBy('android_app');

        $em->flush();
        $em->clear();

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/ajax_get_m_province_organization_summary",
     *       name="ajax_get_m_province_organization_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetProvinceOrganizationSummary(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $provinceCode = empty($request->get("provinceCode")) ? 53 : $request->get("provinceCode");
        $electId = empty($request->get("electId")) ? null : $request->get("electId");
        $proId = empty($request->get("proId")) ? null : $request->get("proId");
        $createdAt = empty($request->get('createdAt')) ? null : $request->get('createdAt');

        if ($createdAt == null || $createdAt == 'null') {
            $createdAt = $this->getLastDateComputed($electId, $proId);
        }

        $sql = "SELECT m.*,
        (SELECT coalesce( SUM(s.total_voters),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_voters,
        (SELECT coalesce( count(DISTINCT s.brgy_no),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_barangays,
        (SELECT coalesce( count(s.sum_id),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_precincts,

        (SELECT coalesce( count(DISTINCT pv.clustered_precinct),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_clustered_precincts,
        (SELECT coalesce(sum(pv.total_member),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_recruits,
        (SELECT coalesce(sum(pv.total_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_ch,
        (SELECT coalesce(sum(pv.total_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl,
        (SELECT coalesce(sum(pv.total_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl0,
        (SELECT coalesce(sum(pv.total_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl1,
        (SELECT coalesce(sum(pv.total_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl2,
        (SELECT coalesce(sum(pv.total_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl3,
        (SELECT coalesce(sum(pv.total_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kjr,
        (SELECT coalesce(sum(pv.total_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_staff,
        (SELECT coalesce(sum(pv.total_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_others,

        (SELECT coalesce(sum(pv.total_with_id_member),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_recruits,
        (SELECT coalesce(sum(pv.total_with_id_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_ch,
        (SELECT coalesce(sum(pv.total_with_id_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl,
        (SELECT coalesce(sum(pv.total_with_id_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl0,
        (SELECT coalesce(sum(pv.total_with_id_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl1,
        (SELECT coalesce(sum(pv.total_with_id_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl2,
        (SELECT coalesce(sum(pv.total_with_id_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl3,
        (SELECT coalesce(sum(pv.total_with_id_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kjr,
        (SELECT coalesce(sum(pv.total_with_id_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_staff,
        (SELECT coalesce(sum(pv.total_with_id_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_others,

        (SELECT coalesce(sum(pv.total_submitted),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_submitted,
        (SELECT coalesce(sum(pv.total_has_submitted_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_ch,
        (SELECT coalesce(sum(pv.total_has_submitted_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl,
        (SELECT coalesce(sum(pv.total_has_submitted_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl0,
        (SELECT coalesce(sum(pv.total_has_submitted_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl1,
        (SELECT coalesce(sum(pv.total_has_submitted_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl2,
        (SELECT coalesce(sum(pv.total_has_submitted_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl3,
        (SELECT coalesce(sum(pv.total_has_submitted_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kjr,
        (SELECT coalesce(sum(pv.total_has_submitted_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_others,
        (SELECT coalesce(sum(pv.total_has_submitted_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_cellphone,

        (SELECT coalesce(sum(pv.total_has_ast),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast,
        (SELECT coalesce(sum(pv.total_has_ast_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_ch,
        (SELECT coalesce(sum(pv.total_has_ast_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl,
        (SELECT coalesce(sum(pv.total_has_ast_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl0,
        (SELECT coalesce(sum(pv.total_has_ast_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl1,
        (SELECT coalesce(sum(pv.total_has_ast_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl2,
        (SELECT coalesce(sum(pv.total_has_ast_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl3,
        (SELECT coalesce(sum(pv.total_has_ast_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kjr,
        (SELECT coalesce(sum(pv.total_has_ast_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_others,
        (SELECT coalesce(sum(pv.total_has_ast_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_staff,
        (SELECT coalesce(sum(pv.total_has_ast_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_cellphone,

        (SELECT coalesce(sum(pv.total_has_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_cellphone,
        (SELECT coalesce(sum(pv.total_with_id_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_cellphone

        FROM  psw_municipality m
        WHERE m.province_code = ? AND m.municipality_no IN ('01','16') ";

        $sql .= " ORDER BY m.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $electId);
        $stmt->bindValue(3, $proId);

        $stmt->bindValue(4, $provinceCode);
        $stmt->bindValue(5, $electId);
        $stmt->bindValue(6, $proId);

        $stmt->bindValue(7, $provinceCode);
        $stmt->bindValue(8, $electId);
        $stmt->bindValue(9, $proId);

        $stmt->bindValue(10, $provinceCode);
        $stmt->bindValue(11, $electId);
        $stmt->bindValue(12, $proId);
        $stmt->bindValue(13, $createdAt);

        $stmt->bindValue(14, $provinceCode);
        $stmt->bindValue(15, $electId);
        $stmt->bindValue(16, $proId);
        $stmt->bindValue(17, $createdAt);

        $stmt->bindValue(18, $provinceCode);
        $stmt->bindValue(19, $electId);
        $stmt->bindValue(20, $proId);
        $stmt->bindValue(21, $createdAt);

        $stmt->bindValue(22, $provinceCode);
        $stmt->bindValue(23, $electId);
        $stmt->bindValue(24, $proId);
        $stmt->bindValue(25, $createdAt);

        $stmt->bindValue(26, $provinceCode);
        $stmt->bindValue(27, $electId);
        $stmt->bindValue(28, $proId);
        $stmt->bindValue(29, $createdAt);

        $stmt->bindValue(30, $provinceCode);
        $stmt->bindValue(31, $electId);
        $stmt->bindValue(32, $proId);
        $stmt->bindValue(33, $createdAt);

        $stmt->bindValue(34, $provinceCode);
        $stmt->bindValue(35, $electId);
        $stmt->bindValue(36, $proId);
        $stmt->bindValue(37, $createdAt);

        $stmt->bindValue(38, $provinceCode);
        $stmt->bindValue(39, $electId);
        $stmt->bindValue(40, $proId);
        $stmt->bindValue(41, $createdAt);

        $stmt->bindValue(42, $provinceCode);
        $stmt->bindValue(43, $electId);
        $stmt->bindValue(44, $proId);
        $stmt->bindValue(45, $createdAt);

        $stmt->bindValue(46, $provinceCode);
        $stmt->bindValue(47, $electId);
        $stmt->bindValue(48, $proId);
        $stmt->bindValue(49, $createdAt);

        $stmt->bindValue(50, $provinceCode);
        $stmt->bindValue(51, $electId);
        $stmt->bindValue(52, $proId);
        $stmt->bindValue(53, $createdAt);

        $stmt->bindValue(54, $provinceCode);
        $stmt->bindValue(55, $electId);
        $stmt->bindValue(56, $proId);
        $stmt->bindValue(57, $createdAt);

        $stmt->bindValue(58, $provinceCode);
        $stmt->bindValue(59, $electId);
        $stmt->bindValue(60, $proId);
        $stmt->bindValue(61, $createdAt);

        $stmt->bindValue(62, $provinceCode);
        $stmt->bindValue(63, $electId);
        $stmt->bindValue(64, $proId);
        $stmt->bindValue(65, $createdAt);

        $stmt->bindValue(66, $provinceCode);
        $stmt->bindValue(67, $electId);
        $stmt->bindValue(68, $proId);
        $stmt->bindValue(69, $createdAt);

        $stmt->bindValue(70, $provinceCode);
        $stmt->bindValue(71, $electId);
        $stmt->bindValue(72, $proId);
        $stmt->bindValue(73, $createdAt);

        $stmt->bindValue(74, $provinceCode);
        $stmt->bindValue(75, $electId);
        $stmt->bindValue(76, $proId);
        $stmt->bindValue(77, $createdAt);

        $stmt->bindValue(78, $provinceCode);
        $stmt->bindValue(79, $electId);
        $stmt->bindValue(80, $proId);
        $stmt->bindValue(81, $createdAt);

        $stmt->bindValue(82, $provinceCode);
        $stmt->bindValue(83, $electId);
        $stmt->bindValue(84, $proId);
        $stmt->bindValue(85, $createdAt);

        $stmt->bindValue(86, $provinceCode);
        $stmt->bindValue(87, $electId);
        $stmt->bindValue(88, $proId);
        $stmt->bindValue(89, $createdAt);

        $stmt->bindValue(90, $provinceCode);
        $stmt->bindValue(91, $electId);
        $stmt->bindValue(92, $proId);
        $stmt->bindValue(93, $createdAt);

        $stmt->bindValue(94, $provinceCode);
        $stmt->bindValue(95, $electId);
        $stmt->bindValue(96, $proId);
        $stmt->bindValue(97, $createdAt);

        $stmt->bindValue(98, $provinceCode);
        $stmt->bindValue(99, $electId);
        $stmt->bindValue(100, $proId);
        $stmt->bindValue(101, $createdAt);

        $stmt->bindValue(102, $provinceCode);
        $stmt->bindValue(103, $electId);
        $stmt->bindValue(104, $proId);
        $stmt->bindValue(105, $createdAt);

        $stmt->bindValue(106, $provinceCode);
        $stmt->bindValue(107, $electId);
        $stmt->bindValue(108, $proId);
        $stmt->bindValue(109, $createdAt);

        $stmt->bindValue(110, $provinceCode);
        $stmt->bindValue(111, $electId);
        $stmt->bindValue(112, $proId);
        $stmt->bindValue(113, $createdAt);

        $stmt->bindValue(114, $provinceCode);
        $stmt->bindValue(115, $electId);
        $stmt->bindValue(116, $proId);
        $stmt->bindValue(117, $createdAt);

        $stmt->bindValue(118, $provinceCode);
        $stmt->bindValue(119, $electId);
        $stmt->bindValue(120, $proId);
        $stmt->bindValue(121, $createdAt);

        $stmt->bindValue(122, $provinceCode);
        $stmt->bindValue(123, $electId);
        $stmt->bindValue(124, $proId);
        $stmt->bindValue(125, $createdAt);

        $stmt->bindValue(126, $provinceCode);
        $stmt->bindValue(127, $electId);
        $stmt->bindValue(128, $proId);
        $stmt->bindValue(129, $createdAt);

        $stmt->bindValue(130, $provinceCode);
        $stmt->bindValue(131, $electId);
        $stmt->bindValue(132, $proId);
        $stmt->bindValue(133, $createdAt);

        $stmt->bindValue(134, $provinceCode);
        $stmt->bindValue(135, $electId);
        $stmt->bindValue(136, $proId);
        $stmt->bindValue(137, $createdAt);

        $stmt->bindValue(138, $provinceCode);
        $stmt->bindValue(139, $electId);
        $stmt->bindValue(140, $proId);
        $stmt->bindValue(141, $createdAt);

        $stmt->bindValue(142, $provinceCode);
        $stmt->bindValue(143, $electId);
        $stmt->bindValue(144, $proId);
        $stmt->bindValue(145, $createdAt);

        $stmt->bindValue(146, $provinceCode);
        $stmt->bindValue(147, $electId);
        $stmt->bindValue(148, $proId);
        $stmt->bindValue(149, $createdAt);

        $stmt->bindValue(150, $provinceCode);
        $stmt->bindValue(151, $electId);
        $stmt->bindValue(152, $proId);
        $stmt->bindValue(153, $createdAt);

        $stmt->bindValue(154, $provinceCode);
        $stmt->bindValue(155, $electId);
        $stmt->bindValue(156, $proId);
        $stmt->bindValue(157, $createdAt);

        $stmt->bindValue(158, $provinceCode);
        $stmt->bindValue(159, $electId);
        $stmt->bindValue(160, $proId);
        $stmt->bindValue(161, $createdAt);

        $stmt->bindValue(162, $provinceCode);
        $stmt->bindValue(163, $electId);
        $stmt->bindValue(164, $proId);
        $stmt->bindValue(165, $createdAt);

        $stmt->bindValue(166, $provinceCode);
        $stmt->bindValue(167, $electId);
        $stmt->bindValue(168, $proId);
        $stmt->bindValue(169, $createdAt);

        $stmt->bindValue(170, $provinceCode);
        $stmt->bindValue(171, $electId);
        $stmt->bindValue(172, $proId);
        $stmt->bindValue(173, $createdAt);

        $stmt->bindValue(174, $provinceCode);
        $stmt->bindValue(175, $electId);
        $stmt->bindValue(176, $proId);
        $stmt->bindValue(177, $createdAt);

        $stmt->bindValue(178, $provinceCode);
        $stmt->bindValue(179, $electId);
        $stmt->bindValue(180, $proId);
        $stmt->bindValue(181, $createdAt);

        $stmt->bindValue(182, $provinceCode);
        $stmt->bindValue(183, $electId);
        $stmt->bindValue(184, $proId);
        $stmt->bindValue(185, $createdAt);

        $stmt->bindValue(186, $provinceCode);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            $temp = $row;

            $temp['total_no_id_recruits'] = $row['total_recruits'] - $row['total_with_id_recruits'];
            $temp['total_no_id_ch'] = $row['total_ch'] - $row['total_with_id_ch'];
            $temp['total_no_id_kcl'] = $row['total_kcl'] - $row['total_with_id_kcl'];
            $temp['total_no_id_kcl0'] = $row['total_kcl0'] - $row['total_with_id_kcl0'];
            $temp['total_no_id_kcl1'] = $row['total_kcl1'] - $row['total_with_id_kcl1'];
            $temp['total_no_id_kcl2'] = $row['total_kcl2'] - $row['total_with_id_kcl2'];
            $temp['total_no_id_kcl3'] = $row['total_kcl3'] - $row['total_with_id_kcl3'];
            $temp['total_no_id_kjr'] = $row['total_kjr'] - $row['total_with_id_kjr'];
            $temp['total_no_id_staff'] = $row['total_staff'] - $row['total_with_id_staff'];
            $temp['total_no_id_others'] = $row['total_others'] - $row['total_with_id_others'];
            $temp['total_no_id_cellphone'] = $row['total_has_cellphone'] - $row['total_with_id_cellphone'];

            $temp['total_not_submitted_recruits'] = $row['total_recruits'] - $row['total_submitted'];
            $temp['total_not_submitted_ch'] = $row['total_ch'] - $row['total_has_submitted_ch'];
            $temp['total_not_submitted_kcl'] = $row['total_kcl'] - $row['total_has_submitted_kcl'];
            $temp['total_not_submitted_kcl0'] = $row['total_kcl0'] - $row['total_has_submitted_kcl0'];
            $temp['total_not_submitted_kcl1'] = $row['total_kcl1'] - $row['total_has_submitted_kcl1'];
            $temp['total_not_submitted_kcl2'] = $row['total_kcl2'] - $row['total_has_submitted_kcl2'];
            $temp['total_not_submitted_kcl3'] = $row['total_kcl3'] - $row['total_has_submitted_kcl3'];
            $temp['total_not_submitted_kjr'] = $row['total_kjr'] - $row['total_has_submitted_kjr'];
            $temp['total_not_submitted_others'] = $row['total_others'] - $row['total_has_submitted_others'];
            $temp['total_not_submitted_cellphone'] = $row['total_submitted'] - $row['total_has_submitted_cellphone'];

            $temp['total_no_ast'] = $row['total_recruits'] - $row['total_has_ast'];
            $temp['total_no_ast_ch'] = $row['total_ch'] - $row['total_has_ast_ch'];
            $temp['total_no_ast_kcl'] = $row['total_kcl'] - $row['total_has_ast_kcl'];
            $temp['total_no_ast_kcl0'] = $row['total_kcl0'] - $row['total_has_ast_kcl0'];
            $temp['total_no_ast_kcl1'] = $row['total_kcl1'] - $row['total_has_ast_kcl1'];
            $temp['total_no_ast_kcl2'] = $row['total_kcl2'] - $row['total_has_ast_kcl2'];
            $temp['total_no_ast_kcl3'] = $row['total_kcl3'] - $row['total_has_ast_kcl3'];
            $temp['total_no_ast_kjr'] = $row['total_kjr'] - $row['total_has_ast_kjr'];
            $temp['total_no_ast_others'] = $row['total_others'] - $row['total_has_ast_others'];
            $temp['total_no_ast_staff'] = $row['total_staff'] - $row['total_has_ast_staff'];
            $temp['total_no_ast_cellphone'] = $row['total_has_ast'] - $row['total_has_ast_cellphone'];

            $temp['total_tl'] = $temp['total_ch'] + $temp['total_kcl'];
            $temp['total_sl'] = $temp['total_kcl0'] + $temp['total_kcl1'] + $temp['total_kcl2'];
            $temp['total_members'] = $temp['total_kcl3'] + $temp['total_kjr'];

            $temp['total_with_id_tl'] = $temp['total_with_id_ch'] + $temp['total_with_id_kcl'];
            $temp['total_with_id_sl'] = $temp['total_with_id_kcl0'] + $temp['total_with_id_kcl1'] + $temp['total_with_id_kcl2'];
            $temp['total_with_id_members'] = $temp['total_with_id_kcl3'] + $temp['total_with_id_kjr'];

            $temp['total_no_id_tl'] = $temp['total_no_id_ch'] + $temp['total_no_id_kcl'];
            $temp['total_no_id_sl'] = $temp['total_no_id_kcl0'] + $temp['total_no_id_kcl1'] + $temp['total_no_id_kcl2'];
            $temp['total_no_id_members'] = $temp['total_no_id_kcl3'] + $temp['total_no_id_kjr'];

            $data[] = $temp;
        }

        return new JsonResponse($data);
    }

    private function getLastDateComputed($electId, $proId)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM tbl_project_voter_summary WHERE elect_id = ? AND pro_id = ? ORDER BY created_at DESC LIMIT 1";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $electId);
        $stmt->bindValue(2, $proId);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $row == null ? null : $row['created_at'];
    }

    /**
     * @Route("/ajax_get_m_municipality_organization_summary",
     *       name="ajax_get_m_municipality_organization_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetMunicipalityDataSummary(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $electId = empty($request->get("electId")) ? null : $request->get("electId");
        $proId = empty($request->get("proId")) ? null : $request->get("proId");
        $provinceCode = empty($request->get("provinceCode")) ? 53 : $request->get('provinceCode');
        $municipalityNo = $request->get("municipalityNo");
        $createdAt = empty($request->get('createdAt')) ? null : $request->get('createdAt');

        if ($createdAt == null || $createdAt == 'null') {
            $createdAt = $this->getLastDateComputed($electId, $proId);
        }

        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT m.*,
        (SELECT COALESCE(SUM(s.total_voters),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no  AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_voters,
        (SELECT COALESCE(COUNT(DISTINCT s.precinct_no),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.province_code = ? AND s.elect_id = ?  AND s.pro_id = ? ) as total_precincts,

        (SELECT coalesce(COUNT(DISTINCT pv.clustered_precinct),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_clustered_precincts,
        (SELECT coalesce(sum(pv.total_member),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_recruits,
        (SELECT coalesce(sum(pv.total_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_ch,
        (SELECT coalesce(sum(pv.total_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl,
        (SELECT coalesce(sum(pv.total_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl0,
        (SELECT coalesce(sum(pv.total_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl1,
        (SELECT coalesce(sum(pv.total_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl2,
        (SELECT coalesce(sum(pv.total_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl3,
        (SELECT coalesce(sum(pv.total_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kjr,
        (SELECT coalesce(sum(pv.total_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_staff,
        (SELECT coalesce(sum(pv.total_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_others,

        (SELECT coalesce(sum(pv.total_with_id_member),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_recruits,
        (SELECT coalesce(sum(pv.total_with_id_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_ch,
        (SELECT coalesce(sum(pv.total_with_id_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl,
        (SELECT coalesce(sum(pv.total_with_id_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl0,
        (SELECT coalesce(sum(pv.total_with_id_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl1,
        (SELECT coalesce(sum(pv.total_with_id_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl2,
        (SELECT coalesce(sum(pv.total_with_id_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl3,
        (SELECT coalesce(sum(pv.total_with_id_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kjr,
        (SELECT coalesce(sum(pv.total_with_id_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_staff,
        (SELECT coalesce(sum(pv.total_with_id_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_others,

        (SELECT coalesce(sum(pv.total_submitted),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_submitted,
        (SELECT coalesce(sum(pv.total_has_submitted_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_ch,
        (SELECT coalesce(sum(pv.total_has_submitted_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl,
        (SELECT coalesce(sum(pv.total_has_submitted_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl0,
        (SELECT coalesce(sum(pv.total_has_submitted_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl1,
        (SELECT coalesce(sum(pv.total_has_submitted_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl2,
        (SELECT coalesce(sum(pv.total_has_submitted_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl3,
        (SELECT coalesce(sum(pv.total_has_submitted_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kjr,
        (SELECT coalesce(sum(pv.total_has_submitted_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_others,
        (SELECT coalesce(sum(pv.total_has_submitted_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_cellphone,

        (SELECT coalesce(sum(pv.total_has_ast),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast,
        (SELECT coalesce(sum(pv.total_has_ast_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_ch,
        (SELECT coalesce(sum(pv.total_has_ast_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl,
        (SELECT coalesce(sum(pv.total_has_ast_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl0,
        (SELECT coalesce(sum(pv.total_has_ast_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl1,
        (SELECT coalesce(sum(pv.total_has_ast_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl2,
        (SELECT coalesce(sum(pv.total_has_ast_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl3,
        (SELECT coalesce(sum(pv.total_has_ast_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kjr,
        (SELECT coalesce(sum(pv.total_has_ast_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_others,
        (SELECT coalesce(sum(pv.total_has_ast_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_staff,
        (SELECT coalesce(sum(pv.total_has_ast_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_cellphone,

        (SELECT coalesce(sum(pv.total_has_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_cellphone,
        (SELECT coalesce(sum(pv.total_with_id_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_cellphone,
        (SELECT coalesce(count(b.brgy_no),0) FROM psw_barangay b WHERE b.municipality_code = ? ) as total_barangays
        FROM  psw_municipality m WHERE m.province_code = ? AND m.municipality_no = ? ";

        $sql .= " ORDER BY m.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $electId);
        $stmt->bindValue(3, $proId);

        $stmt->bindValue(4, $provinceCode);
        $stmt->bindValue(5, $electId);
        $stmt->bindValue(6, $proId);

        $stmt->bindValue(7, $provinceCode);
        $stmt->bindValue(8, $electId);
        $stmt->bindValue(9, $proId);
        $stmt->bindValue(10, $createdAt);

        $stmt->bindValue(11, $provinceCode);
        $stmt->bindValue(12, $electId);
        $stmt->bindValue(13, $proId);
        $stmt->bindValue(14, $createdAt);

        $stmt->bindValue(15, $provinceCode);
        $stmt->bindValue(16, $electId);
        $stmt->bindValue(17, $proId);
        $stmt->bindValue(18, $createdAt);

        $stmt->bindValue(19, $provinceCode);
        $stmt->bindValue(20, $electId);
        $stmt->bindValue(21, $proId);
        $stmt->bindValue(22, $createdAt);

        $stmt->bindValue(23, $provinceCode);
        $stmt->bindValue(24, $electId);
        $stmt->bindValue(25, $proId);
        $stmt->bindValue(26, $createdAt);

        $stmt->bindValue(27, $provinceCode);
        $stmt->bindValue(28, $electId);
        $stmt->bindValue(29, $proId);
        $stmt->bindValue(30, $createdAt);

        $stmt->bindValue(31, $provinceCode);
        $stmt->bindValue(32, $electId);
        $stmt->bindValue(33, $proId);
        $stmt->bindValue(34, $createdAt);

        $stmt->bindValue(35, $provinceCode);
        $stmt->bindValue(36, $electId);
        $stmt->bindValue(37, $proId);
        $stmt->bindValue(38, $createdAt);

        $stmt->bindValue(39, $provinceCode);
        $stmt->bindValue(40, $electId);
        $stmt->bindValue(41, $proId);
        $stmt->bindValue(42, $createdAt);

        $stmt->bindValue(43, $provinceCode);
        $stmt->bindValue(44, $electId);
        $stmt->bindValue(45, $proId);
        $stmt->bindValue(46, $createdAt);

        $stmt->bindValue(47, $provinceCode);
        $stmt->bindValue(48, $electId);
        $stmt->bindValue(49, $proId);
        $stmt->bindValue(50, $createdAt);

        $stmt->bindValue(51, $provinceCode);
        $stmt->bindValue(52, $electId);
        $stmt->bindValue(53, $proId);
        $stmt->bindValue(54, $createdAt);

        $stmt->bindValue(55, $provinceCode);
        $stmt->bindValue(56, $electId);
        $stmt->bindValue(57, $proId);
        $stmt->bindValue(58, $createdAt);

        $stmt->bindValue(59, $provinceCode);
        $stmt->bindValue(60, $electId);
        $stmt->bindValue(61, $proId);
        $stmt->bindValue(62, $createdAt);

        $stmt->bindValue(63, $provinceCode);
        $stmt->bindValue(64, $electId);
        $stmt->bindValue(65, $proId);
        $stmt->bindValue(66, $createdAt);

        $stmt->bindValue(67, $provinceCode);
        $stmt->bindValue(68, $electId);
        $stmt->bindValue(69, $proId);
        $stmt->bindValue(70, $createdAt);

        $stmt->bindValue(71, $provinceCode);
        $stmt->bindValue(72, $electId);
        $stmt->bindValue(73, $proId);
        $stmt->bindValue(74, $createdAt);

        $stmt->bindValue(75, $provinceCode);
        $stmt->bindValue(76, $electId);
        $stmt->bindValue(77, $proId);
        $stmt->bindValue(78, $createdAt);

        $stmt->bindValue(79, $provinceCode);
        $stmt->bindValue(80, $electId);
        $stmt->bindValue(81, $proId);
        $stmt->bindValue(82, $createdAt);

        $stmt->bindValue(83, $provinceCode);
        $stmt->bindValue(84, $electId);
        $stmt->bindValue(85, $proId);
        $stmt->bindValue(86, $createdAt);

        $stmt->bindValue(87, $provinceCode);
        $stmt->bindValue(88, $electId);
        $stmt->bindValue(89, $proId);
        $stmt->bindValue(90, $createdAt);

        $stmt->bindValue(91, $provinceCode);
        $stmt->bindValue(92, $electId);
        $stmt->bindValue(93, $proId);
        $stmt->bindValue(94, $createdAt);

        $stmt->bindValue(95, $provinceCode);
        $stmt->bindValue(96, $electId);
        $stmt->bindValue(97, $proId);
        $stmt->bindValue(98, $createdAt);

        $stmt->bindValue(99, $provinceCode);
        $stmt->bindValue(100, $electId);
        $stmt->bindValue(101, $proId);
        $stmt->bindValue(102, $createdAt);

        $stmt->bindValue(103, $provinceCode);
        $stmt->bindValue(104, $electId);
        $stmt->bindValue(105, $proId);
        $stmt->bindValue(106, $createdAt);

        $stmt->bindValue(107, $provinceCode);
        $stmt->bindValue(108, $electId);
        $stmt->bindValue(109, $proId);
        $stmt->bindValue(110, $createdAt);

        $stmt->bindValue(111, $provinceCode);
        $stmt->bindValue(112, $electId);
        $stmt->bindValue(113, $proId);
        $stmt->bindValue(114, $createdAt);

        $stmt->bindValue(115, $provinceCode);
        $stmt->bindValue(116, $electId);
        $stmt->bindValue(117, $proId);
        $stmt->bindValue(118, $createdAt);

        $stmt->bindValue(119, $provinceCode);
        $stmt->bindValue(120, $electId);
        $stmt->bindValue(121, $proId);
        $stmt->bindValue(122, $createdAt);

        $stmt->bindValue(123, $provinceCode);
        $stmt->bindValue(124, $electId);
        $stmt->bindValue(125, $proId);
        $stmt->bindValue(126, $createdAt);

        $stmt->bindValue(127, $provinceCode);
        $stmt->bindValue(128, $electId);
        $stmt->bindValue(129, $proId);
        $stmt->bindValue(130, $createdAt);

        $stmt->bindValue(131, $provinceCode);
        $stmt->bindValue(132, $electId);
        $stmt->bindValue(133, $proId);
        $stmt->bindValue(134, $createdAt);

        $stmt->bindValue(135, $provinceCode);
        $stmt->bindValue(136, $electId);
        $stmt->bindValue(137, $proId);
        $stmt->bindValue(138, $createdAt);

        $stmt->bindValue(139, $provinceCode);
        $stmt->bindValue(140, $electId);
        $stmt->bindValue(141, $proId);
        $stmt->bindValue(142, $createdAt);

        $stmt->bindValue(143, $provinceCode);
        $stmt->bindValue(144, $electId);
        $stmt->bindValue(145, $proId);
        $stmt->bindValue(146, $createdAt);

        $stmt->bindValue(147, $provinceCode);
        $stmt->bindValue(148, $electId);
        $stmt->bindValue(149, $proId);
        $stmt->bindValue(150, $createdAt);

        $stmt->bindValue(151, $provinceCode);
        $stmt->bindValue(152, $electId);
        $stmt->bindValue(153, $proId);
        $stmt->bindValue(154, $createdAt);

        $stmt->bindValue(155, $provinceCode);
        $stmt->bindValue(156, $electId);
        $stmt->bindValue(157, $proId);
        $stmt->bindValue(158, $createdAt);

        $stmt->bindValue(159, $provinceCode);
        $stmt->bindValue(160, $electId);
        $stmt->bindValue(161, $proId);
        $stmt->bindValue(162, $createdAt);

        $stmt->bindValue(163, $provinceCode);
        $stmt->bindValue(164, $electId);
        $stmt->bindValue(165, $proId);
        $stmt->bindValue(166, $createdAt);

        $stmt->bindValue(167, $provinceCode);
        $stmt->bindValue(168, $electId);
        $stmt->bindValue(169, $proId);
        $stmt->bindValue(170, $createdAt);

        $stmt->bindValue(171, $provinceCode);
        $stmt->bindValue(172, $electId);
        $stmt->bindValue(173, $proId);
        $stmt->bindValue(174, $createdAt);

        $stmt->bindValue(175, $provinceCode);
        $stmt->bindValue(176, $electId);
        $stmt->bindValue(177, $proId);
        $stmt->bindValue(178, $createdAt);

        $stmt->bindValue(179, $provinceCode);
        $stmt->bindValue(180, $electId);
        $stmt->bindValue(181, $proId);
        $stmt->bindValue(182, $createdAt);

        $stmt->bindValue(183, $provinceCode . $municipalityNo);
        $stmt->bindValue(184, $provinceCode);
        $stmt->bindValue(185, $municipalityNo);

        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            $temp = $row;

            $temp['total_no_id_recruits'] = $row['total_recruits'] - $row['total_with_id_recruits'];
            $temp['total_no_id_ch'] = $row['total_ch'] - $row['total_with_id_ch'];
            $temp['total_no_id_kcl'] = $row['total_kcl'] - $row['total_with_id_kcl'];
            $temp['total_no_id_kcl0'] = $row['total_kcl0'] - $row['total_with_id_kcl0'];
            $temp['total_no_id_kcl1'] = $row['total_kcl1'] - $row['total_with_id_kcl1'];
            $temp['total_no_id_kcl2'] = $row['total_kcl2'] - $row['total_with_id_kcl2'];
            $temp['total_no_id_kcl3'] = $row['total_kcl3'] - $row['total_with_id_kcl3'];
            $temp['total_no_id_kjr'] = $row['total_kjr'] - $row['total_with_id_kjr'];
            $temp['total_no_id_staff'] = $row['total_staff'] - $row['total_with_id_staff'];
            $temp['total_no_id_others'] = $row['total_others'] - $row['total_with_id_others'];
            $temp['total_no_id_cellphone'] = $row['total_has_cellphone'] - $row['total_with_id_cellphone'];

            $temp['total_not_submitted_recruits'] = $row['total_recruits'] - $row['total_submitted'];
            $temp['total_not_submitted_ch'] = $row['total_ch'] - $row['total_has_submitted_ch'];
            $temp['total_not_submitted_kcl'] = $row['total_kcl'] - $row['total_has_submitted_kcl'];
            $temp['total_not_submitted_kcl0'] = $row['total_kcl0'] - $row['total_has_submitted_kcl0'];
            $temp['total_not_submitted_kcl1'] = $row['total_kcl1'] - $row['total_has_submitted_kcl1'];
            $temp['total_not_submitted_kcl2'] = $row['total_kcl2'] - $row['total_has_submitted_kcl2'];
            $temp['total_not_submitted_kcl3'] = $row['total_kcl3'] - $row['total_has_submitted_kcl3'];
            $temp['total_not_submitted_kjr'] = $row['total_kjr'] - $row['total_has_submitted_kjr'];
            $temp['total_not_submitted_others'] = $row['total_others'] - $row['total_has_submitted_others'];
            $temp['total_not_submitted_cellphone'] = $row['total_submitted'] - $row['total_has_submitted_cellphone'];

            $temp['total_no_ast'] = $row['total_recruits'] - $row['total_has_ast'];
            $temp['total_no_ast_ch'] = $row['total_ch'] - $row['total_has_ast_ch'];
            $temp['total_no_ast_kcl'] = $row['total_kcl'] - $row['total_has_ast_kcl'];
            $temp['total_no_ast_kcl0'] = $row['total_kcl0'] - $row['total_has_ast_kcl0'];
            $temp['total_no_ast_kcl1'] = $row['total_kcl1'] - $row['total_has_ast_kcl1'];
            $temp['total_no_ast_kcl2'] = $row['total_kcl2'] - $row['total_has_ast_kcl2'];
            $temp['total_no_ast_kcl3'] = $row['total_kcl3'] - $row['total_has_ast_kcl3'];
            $temp['total_no_ast_kjr'] = $row['total_kjr'] - $row['total_has_ast_kjr'];
            $temp['total_no_ast_others'] = $row['total_others'] - $row['total_has_ast_others'];
            $temp['total_no_ast_staff'] = $row['total_staff'] - $row['total_has_ast_staff'];
            $temp['total_no_ast_cellphone'] = $row['total_has_ast'] - $row['total_has_ast_cellphone'];

            $temp['total_tl'] = $temp['total_ch'] + $temp['total_kcl'];
            $temp['total_sl'] = $temp['total_kcl0'] + $temp['total_kcl1'] + $temp['total_kcl2'];
            $temp['total_members'] = $temp['total_kcl3'] + $temp['total_kjr'];

            $temp['total_with_id_tl'] = $temp['total_with_id_ch'] + $temp['total_with_id_kcl'];
            $temp['total_with_id_sl'] = $temp['total_with_id_kcl0'] + $temp['total_with_id_kcl1'] + $temp['total_with_id_kcl2'];
            $temp['total_with_id_members'] = $temp['total_with_id_kcl3'] + $temp['total_with_id_kjr'];

            $temp['total_no_id_tl'] = $temp['total_no_id_ch'] + $temp['total_no_id_kcl'];
            $temp['total_no_id_sl'] = $temp['total_no_id_kcl0'] + $temp['total_no_id_kcl1'] + $temp['total_no_id_kcl2'];
            $temp['total_no_id_members'] = $temp['total_no_id_kcl3'] + $temp['total_no_id_kjr'];

            $data = $temp;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_get_m_barangay_organization_summary",
     *       name="ajax_get_m_barangay_organization_summary",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetBarangayDataSummary(Request $request)
    {
        $electId = $request->get("electId");
        $proId = $request->get("proId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");
        $createdAt = empty($request->get('createdAt')) ? null : $request->get('createdAt');

        if ($createdAt == null || $createdAt == 'null') {
            $createdAt = $this->getLastDateComputed($electId, $proId);
        }

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT b.*,
        (SELECT COALESCE(SUM(s.total_voters),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_voters,
        (SELECT COALESCE(COUNT(DISTINCT s.precinct_no),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ? AND s.elect_id = ?  AND s.pro_id = ? ) as total_precincts,

        (SELECT coalesce(count(DISTINCT pv.clustered_precinct),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_clustered_precincts,
        (SELECT coalesce(sum(pv.total_member),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_recruits,
        (SELECT coalesce(sum(pv.total_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_ch,
        (SELECT coalesce(sum(pv.total_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl,
        (SELECT coalesce(sum(pv.total_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl0,
        (SELECT coalesce(sum(pv.total_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl1,
        (SELECT coalesce(sum(pv.total_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl2,
        (SELECT coalesce(sum(pv.total_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kcl3,
        (SELECT coalesce(sum(pv.total_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_kjr,
        (SELECT coalesce(sum(pv.total_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_staff,
        (SELECT coalesce(sum(pv.total_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_others,

        (SELECT coalesce(sum(pv.total_with_id_member),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_recruits,
        (SELECT coalesce(sum(pv.total_with_id_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_ch,
        (SELECT coalesce(sum(pv.total_with_id_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl,
        (SELECT coalesce(sum(pv.total_with_id_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl0,
        (SELECT coalesce(sum(pv.total_with_id_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl1,
        (SELECT coalesce(sum(pv.total_with_id_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl2,
        (SELECT coalesce(sum(pv.total_with_id_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kcl3,
        (SELECT coalesce(sum(pv.total_with_id_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_kjr,
        (SELECT coalesce(sum(pv.total_with_id_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_staff,
        (SELECT coalesce(sum(pv.total_with_id_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_others,

        (SELECT coalesce(sum(pv.total_submitted),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_submitted,
        (SELECT coalesce(sum(pv.total_has_submitted_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_ch,
        (SELECT coalesce(sum(pv.total_has_submitted_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl,
        (SELECT coalesce(sum(pv.total_has_submitted_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl0,
        (SELECT coalesce(sum(pv.total_has_submitted_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl1,
        (SELECT coalesce(sum(pv.total_has_submitted_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl2,
        (SELECT coalesce(sum(pv.total_has_submitted_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kcl3,
        (SELECT coalesce(sum(pv.total_has_submitted_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_kjr,
        (SELECT coalesce(sum(pv.total_has_submitted_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_others,
        (SELECT coalesce(sum(pv.total_has_submitted_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_submitted_cellphone,

        (SELECT coalesce(sum(pv.total_has_ast),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast,
        (SELECT coalesce(sum(pv.total_has_ast_level_1),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_ch,
        (SELECT coalesce(sum(pv.total_has_ast_level_2),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl,
        (SELECT coalesce(sum(pv.total_has_ast_level_3),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl0,
        (SELECT coalesce(sum(pv.total_has_ast_level_4),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl1,
        (SELECT coalesce(sum(pv.total_has_ast_level_5),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl2,
        (SELECT coalesce(sum(pv.total_has_ast_level_6),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kcl3,
        (SELECT coalesce(sum(pv.total_has_ast_level_7),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_kjr,
        (SELECT coalesce(sum(pv.total_has_ast_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_others,
        (SELECT coalesce(sum(pv.total_has_ast_staff),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_staff,
        (SELECT coalesce(sum(pv.total_has_ast_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_ast_cellphone,

        (SELECT coalesce(sum(pv.total_has_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_has_cellphone,
        (SELECT coalesce(sum(pv.total_with_id_cellphone),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? AND pv.created_at = ? ) AS total_with_id_cellphone

        FROM  psw_barangay b INNER JOIN psw_municipality m ON m.municipality_code = b.municipality_code
        WHERE b.municipality_code = ? AND b.brgy_no = ? ";

        $sql .= " ORDER BY b.name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $electId);
        $stmt->bindValue(3, $proId);

        $stmt->bindValue(4, $provinceCode);
        $stmt->bindValue(5, $electId);
        $stmt->bindValue(6, $proId);

        $stmt->bindValue(7, $provinceCode);
        $stmt->bindValue(8, $electId);
        $stmt->bindValue(9, $proId);
        $stmt->bindValue(10, $createdAt);

        $stmt->bindValue(11, $provinceCode);
        $stmt->bindValue(12, $electId);
        $stmt->bindValue(13, $proId);
        $stmt->bindValue(14, $createdAt);

        $stmt->bindValue(15, $provinceCode);
        $stmt->bindValue(16, $electId);
        $stmt->bindValue(17, $proId);
        $stmt->bindValue(18, $createdAt);

        $stmt->bindValue(19, $provinceCode);
        $stmt->bindValue(20, $electId);
        $stmt->bindValue(21, $proId);
        $stmt->bindValue(22, $createdAt);

        $stmt->bindValue(23, $provinceCode);
        $stmt->bindValue(24, $electId);
        $stmt->bindValue(25, $proId);
        $stmt->bindValue(26, $createdAt);

        $stmt->bindValue(27, $provinceCode);
        $stmt->bindValue(28, $electId);
        $stmt->bindValue(29, $proId);
        $stmt->bindValue(30, $createdAt);

        $stmt->bindValue(31, $provinceCode);
        $stmt->bindValue(32, $electId);
        $stmt->bindValue(33, $proId);
        $stmt->bindValue(34, $createdAt);

        $stmt->bindValue(35, $provinceCode);
        $stmt->bindValue(36, $electId);
        $stmt->bindValue(37, $proId);
        $stmt->bindValue(38, $createdAt);

        $stmt->bindValue(39, $provinceCode);
        $stmt->bindValue(40, $electId);
        $stmt->bindValue(41, $proId);
        $stmt->bindValue(42, $createdAt);

        $stmt->bindValue(43, $provinceCode);
        $stmt->bindValue(44, $electId);
        $stmt->bindValue(45, $proId);
        $stmt->bindValue(46, $createdAt);

        $stmt->bindValue(47, $provinceCode);
        $stmt->bindValue(48, $electId);
        $stmt->bindValue(49, $proId);
        $stmt->bindValue(50, $createdAt);

        $stmt->bindValue(51, $provinceCode);
        $stmt->bindValue(52, $electId);
        $stmt->bindValue(53, $proId);
        $stmt->bindValue(54, $createdAt);

        $stmt->bindValue(55, $provinceCode);
        $stmt->bindValue(56, $electId);
        $stmt->bindValue(57, $proId);
        $stmt->bindValue(58, $createdAt);

        $stmt->bindValue(59, $provinceCode);
        $stmt->bindValue(60, $electId);
        $stmt->bindValue(61, $proId);
        $stmt->bindValue(62, $createdAt);

        $stmt->bindValue(63, $provinceCode);
        $stmt->bindValue(64, $electId);
        $stmt->bindValue(65, $proId);
        $stmt->bindValue(66, $createdAt);

        $stmt->bindValue(67, $provinceCode);
        $stmt->bindValue(68, $electId);
        $stmt->bindValue(69, $proId);
        $stmt->bindValue(70, $createdAt);

        $stmt->bindValue(71, $provinceCode);
        $stmt->bindValue(72, $electId);
        $stmt->bindValue(73, $proId);
        $stmt->bindValue(74, $createdAt);

        $stmt->bindValue(75, $provinceCode);
        $stmt->bindValue(76, $electId);
        $stmt->bindValue(77, $proId);
        $stmt->bindValue(78, $createdAt);

        $stmt->bindValue(79, $provinceCode);
        $stmt->bindValue(80, $electId);
        $stmt->bindValue(81, $proId);
        $stmt->bindValue(82, $createdAt);

        $stmt->bindValue(83, $provinceCode);
        $stmt->bindValue(84, $electId);
        $stmt->bindValue(85, $proId);
        $stmt->bindValue(86, $createdAt);

        $stmt->bindValue(87, $provinceCode);
        $stmt->bindValue(88, $electId);
        $stmt->bindValue(89, $proId);
        $stmt->bindValue(90, $createdAt);

        $stmt->bindValue(91, $provinceCode);
        $stmt->bindValue(92, $electId);
        $stmt->bindValue(93, $proId);
        $stmt->bindValue(94, $createdAt);

        $stmt->bindValue(95, $provinceCode);
        $stmt->bindValue(96, $electId);
        $stmt->bindValue(97, $proId);
        $stmt->bindValue(98, $createdAt);

        $stmt->bindValue(99, $provinceCode);
        $stmt->bindValue(100, $electId);
        $stmt->bindValue(101, $proId);
        $stmt->bindValue(102, $createdAt);

        $stmt->bindValue(103, $provinceCode);
        $stmt->bindValue(104, $electId);
        $stmt->bindValue(105, $proId);
        $stmt->bindValue(106, $createdAt);

        $stmt->bindValue(107, $provinceCode);
        $stmt->bindValue(108, $electId);
        $stmt->bindValue(109, $proId);
        $stmt->bindValue(110, $createdAt);

        $stmt->bindValue(111, $provinceCode);
        $stmt->bindValue(112, $electId);
        $stmt->bindValue(113, $proId);
        $stmt->bindValue(114, $createdAt);

        $stmt->bindValue(115, $provinceCode);
        $stmt->bindValue(116, $electId);
        $stmt->bindValue(117, $proId);
        $stmt->bindValue(118, $createdAt);

        $stmt->bindValue(119, $provinceCode);
        $stmt->bindValue(120, $electId);
        $stmt->bindValue(121, $proId);
        $stmt->bindValue(122, $createdAt);

        $stmt->bindValue(123, $provinceCode);
        $stmt->bindValue(124, $electId);
        $stmt->bindValue(125, $proId);
        $stmt->bindValue(126, $createdAt);

        $stmt->bindValue(127, $provinceCode);
        $stmt->bindValue(128, $electId);
        $stmt->bindValue(129, $proId);
        $stmt->bindValue(130, $createdAt);

        $stmt->bindValue(131, $provinceCode);
        $stmt->bindValue(132, $electId);
        $stmt->bindValue(133, $proId);
        $stmt->bindValue(134, $createdAt);

        $stmt->bindValue(135, $provinceCode);
        $stmt->bindValue(136, $electId);
        $stmt->bindValue(137, $proId);
        $stmt->bindValue(138, $createdAt);

        $stmt->bindValue(139, $provinceCode);
        $stmt->bindValue(140, $electId);
        $stmt->bindValue(141, $proId);
        $stmt->bindValue(142, $createdAt);

        $stmt->bindValue(143, $provinceCode);
        $stmt->bindValue(144, $electId);
        $stmt->bindValue(145, $proId);
        $stmt->bindValue(146, $createdAt);

        $stmt->bindValue(147, $provinceCode);
        $stmt->bindValue(148, $electId);
        $stmt->bindValue(149, $proId);
        $stmt->bindValue(150, $createdAt);

        $stmt->bindValue(151, $provinceCode);
        $stmt->bindValue(152, $electId);
        $stmt->bindValue(153, $proId);
        $stmt->bindValue(154, $createdAt);

        $stmt->bindValue(155, $provinceCode);
        $stmt->bindValue(156, $electId);
        $stmt->bindValue(157, $proId);
        $stmt->bindValue(158, $createdAt);

        $stmt->bindValue(159, $provinceCode);
        $stmt->bindValue(160, $electId);
        $stmt->bindValue(161, $proId);
        $stmt->bindValue(162, $createdAt);

        $stmt->bindValue(163, $provinceCode);
        $stmt->bindValue(164, $electId);
        $stmt->bindValue(165, $proId);
        $stmt->bindValue(166, $createdAt);

        $stmt->bindValue(167, $provinceCode);
        $stmt->bindValue(168, $electId);
        $stmt->bindValue(169, $proId);
        $stmt->bindValue(170, $createdAt);

        $stmt->bindValue(171, $provinceCode);
        $stmt->bindValue(172, $electId);
        $stmt->bindValue(173, $proId);
        $stmt->bindValue(174, $createdAt);

        $stmt->bindValue(175, $provinceCode);
        $stmt->bindValue(176, $electId);
        $stmt->bindValue(177, $proId);
        $stmt->bindValue(178, $createdAt);

        $stmt->bindValue(179, $provinceCode);
        $stmt->bindValue(180, $electId);
        $stmt->bindValue(181, $proId);
        $stmt->bindValue(182, $createdAt);

        $stmt->bindValue(183, $provinceCode . $municipalityNo);
        $stmt->bindValue(184, $brgyNo);
        $stmt->execute();

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            $temp = $row;

            $temp['total_no_id_recruits'] = $row['total_recruits'] - $row['total_with_id_recruits'];
            $temp['total_no_id_ch'] = $row['total_ch'] - $row['total_with_id_ch'];
            $temp['total_no_id_kcl'] = $row['total_kcl'] - $row['total_with_id_kcl'];
            $temp['total_no_id_kcl0'] = $row['total_kcl0'] - $row['total_with_id_kcl0'];
            $temp['total_no_id_kcl1'] = $row['total_kcl1'] - $row['total_with_id_kcl1'];
            $temp['total_no_id_kcl2'] = $row['total_kcl2'] - $row['total_with_id_kcl2'];
            $temp['total_no_id_kcl3'] = $row['total_kcl3'] - $row['total_with_id_kcl3'];
            $temp['total_no_id_kjr'] = $row['total_kjr'] - $row['total_with_id_kjr'];
            $temp['total_no_id_staff'] = $row['total_staff'] - $row['total_with_id_staff'];
            $temp['total_no_id_others'] = $row['total_others'] - $row['total_with_id_others'];
            $temp['total_no_id_cellphone'] = $row['total_has_cellphone'] - $row['total_with_id_cellphone'];

            $temp['total_not_submitted_recruits'] = $row['total_recruits'] - $row['total_submitted'];
            $temp['total_not_submitted_ch'] = $row['total_ch'] - $row['total_has_submitted_ch'];
            $temp['total_not_submitted_kcl'] = $row['total_kcl'] - $row['total_has_submitted_kcl'];
            $temp['total_not_submitted_kcl0'] = $row['total_kcl0'] - $row['total_has_submitted_kcl0'];
            $temp['total_not_submitted_kcl1'] = $row['total_kcl1'] - $row['total_has_submitted_kcl1'];
            $temp['total_not_submitted_kcl2'] = $row['total_kcl2'] - $row['total_has_submitted_kcl2'];
            $temp['total_not_submitted_kcl3'] = $row['total_kcl3'] - $row['total_has_submitted_kcl3'];
            $temp['total_not_submitted_kjr'] = $row['total_kjr'] - $row['total_has_submitted_kjr'];
            $temp['total_not_submitted_others'] = $row['total_others'] - $row['total_has_submitted_others'];
            $temp['total_not_submitted_cellphone'] = $row['total_submitted'] - $row['total_has_submitted_cellphone'];

            $temp['total_no_ast'] = $row['total_recruits'] - $row['total_has_ast'];
            $temp['total_no_ast_ch'] = $row['total_ch'] - $row['total_has_ast_ch'];
            $temp['total_no_ast_kcl'] = $row['total_kcl'] - $row['total_has_ast_kcl'];
            $temp['total_no_ast_kcl0'] = $row['total_kcl0'] - $row['total_has_ast_kcl0'];
            $temp['total_no_ast_kcl1'] = $row['total_kcl1'] - $row['total_has_ast_kcl1'];
            $temp['total_no_ast_kcl2'] = $row['total_kcl2'] - $row['total_has_ast_kcl2'];
            $temp['total_no_ast_kcl3'] = $row['total_kcl3'] - $row['total_has_ast_kcl3'];
            $temp['total_no_ast_kjr'] = $row['total_kjr'] - $row['total_has_ast_kjr'];
            $temp['total_no_ast_others'] = $row['total_others'] - $row['total_has_ast_others'];
            $temp['total_no_ast_staff'] = $row['total_staff'] - $row['total_has_ast_staff'];
            $temp['total_no_ast_cellphone'] = $row['total_has_ast'] - $row['total_has_ast_cellphone'];

            $temp['total_tl'] = $temp['total_ch'] + $temp['total_kcl'];
            $temp['total_sl'] = $temp['total_kcl0'] + $temp['total_kcl1'] + $temp['total_kcl2'];
            $temp['total_members'] = $temp['total_kcl3'] + $temp['total_kjr'];

            $temp['total_with_id_tl'] = $temp['total_with_id_ch'] + $temp['total_with_id_kcl'];
            $temp['total_with_id_sl'] = $temp['total_with_id_kcl0'] + $temp['total_with_id_kcl1'] + $temp['total_with_id_kcl2'];
            $temp['total_with_id_members'] = $temp['total_with_id_kcl3'] + $temp['total_with_id_kjr'];

            $temp['total_no_id_tl'] = $temp['total_no_id_ch'] + $temp['total_no_id_kcl'];
            $temp['total_no_id_sl'] = $temp['total_no_id_kcl0'] + $temp['total_no_id_kcl1'] + $temp['total_no_id_kcl2'];
            $temp['total_no_id_members'] = $temp['total_no_id_kcl3'] + $temp['total_no_id_kjr'];

            $data = $temp;
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_m_get_kfc_voters",
     *       name="ajax_m_get_kfc_voters",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetVoters(Request $request)
    {
        $provinceCode = 53;
        $municipalityName = $request->get("municipalityName");
        $barangayName = $request->get("barangayName");
        $precinctNo = $request->get("precinctNo");
        $voterNo = $request->get('voterNo');

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM tbl_project_voter
                WHERE pro_id = 2
                AND elect_id = 3
                AND province_code = ?
                AND municipality_name = ?
                AND barangay_name = ?
                AND precinct_no = ?
                AND voter_no = ?
                ORDER BY voter_no  ASC , voter_name ASC
                LIMIT 1 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $municipalityName);
        $stmt->bindValue(3, $barangayName);
        $stmt->bindValue(4, $precinctNo);
        $stmt->bindValue(5, $voterNo);

        $stmt->execute();
        $voter = $stmt->fetch();

        if ($voter == null) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($voter);
    }

    /**
     * @Route("/ajax_m_get_kfc_voterslist",
     *       name="ajax_m_get_kfc_voterslist",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetVoterslist(Request $request)
    {
        $municipalityName = empty($request->get("municipalityName")) ? null : $request->get("municipalityName");
        $barangayName = empty($request->get("barangayName")) ? null : $request->get("barangayName");
        $voterName = empty($request->get('voterName')) ? null : $request->get('voterName');

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM tbl_project_voter
                WHERE elect_id = 3
                AND pro_id = 2
                AND (municipality_name = ? OR ? is null )
                AND (voter_name like ? OR ? is null)
                ORDER BY voter_name ASC
                LIMIT 5 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityName);
        $stmt->bindValue(2, $municipalityName);
        $stmt->bindValue(3, '%' . $voterName . '%');
        $stmt->bindValue(4, $voterName);
        $stmt->execute();
        $voters = $stmt->fetchAll();

        if ($voters == null) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($voters);
    }

    /**
     * @Route("/ajax_m_patch_kfc_voter/{proVoterId}",
     *     name="ajax_m_patch_kfc_voter",
     *    options={"expose" = true}
     * )
     * @Method("PATCH")
     */

    public function ajaxPatchKfcVoterAction($proVoterId, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $proVoter = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proVoterId' => $proVoterId,
        ]);

        if (!$proVoter) {
            return null;
        }

        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);

        $proVoter->setIsMember($request->get('isMember'));
        $proVoter->setIsBigkis($request->get('isBigkis'));
        $proVoter->setIsPulahan($request->get('isPulahan'));

        $proVoter->setIsExpired($request->get('isExpired'));

        $proVoter->setIsTransient($request->get('isTransient'));

        $proVoter->setIsBisaya($request->get('isBisaya'));
        $proVoter->setIsCuyonon($request->get('isCuyonon'));
        $proVoter->setIsTagalog($request->get('isTagalog'));
        $proVoter->setOthersSpecify(strtoupper($request->get('othersSpecify')));

        $proVoter->setNewBirthdate($request->get('birthdate'));
        $proVoter->setReligion(strtoupper($request->get('religion')));
        $proVoter->setCellphone($request->get('cellphone'));

        $proVoter->setUpdatedAt(new \DateTime());
        $proVoter->setUpdatedBy('android_app');
        $proVoter->setStatus('A');

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
     * @Route("/ajax_m_post_pending_voter",
     *       name="ajax_m_post_pending_voter",
     *        options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostPendingVoter(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $request->request->replace($data);

        $proId = $request->get('proId');
        $electId = $request->get('electId');
        $firstname = $request->get('firstname');
        $middlename = $request->get('middlename');
        $lastname = $request->get('lastname');
        $municipalityName = $request->get('municipalityName');
        $barangayName = $request->get('barangayName');
        $address = $request->get('address');
        $precinctNo = $request->get('precinctNo');
        $voterGroup = $request->get('voterGroup');
        $cellphone = $request->get('cellphone');

        $entity = new PendingVoter();
        $entity->setProId($proId);
        $entity->setElectId($electId);
        $entity->setFirstname(strtoupper($firstname));
        $entity->setMiddlename(strtoupper($middlename));
        $entity->setLastname(strtoupper($lastname));
        $entity->setMunicipalityName($municipalityName);
        $entity->setBarangayName($barangayName);
        $entity->setAddress($address);
        $entity->setPrecinctNo($precinctNo);
        $entity->setVoterGroup($voterGroup);
        $entity->setCellphone($cellphone);
        $entity->setCreatedAt(new \DateTime());
        $entity->setCreatedBy('android_app');
        $entity->setStatus('A');

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

        return new JsonResponse(null);
    }

    /**
     * @Route("/ajax_m_get_pending_voterslist",
     *       name="ajax_m_get_pending_voterslist",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetPendingVoterslist(Request $request)
    {
        $municipalityName = empty($request->get("municipalityName")) ? null : $request->get("municipalityName");
        $barangayName = empty($request->get("barangayName")) ? null : $request->get("barangayName");
        $voterName = empty($request->get('voterName')) ? null : $request->get('voterName');

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM tbl_pending_voter
                WHERE pro_id = 3
                AND elect_id = 3
                AND (municipality_name = ? OR ? is null )
                AND (barangay_name = ? OR ? is null )
                AND (
                    (firstname like ? OR  middlename like ? OR lastname like ? )
                    OR (? IS NULL AND ? IS NULL  AND ? IS NULL)
                )
                ORDER BY municipality_name ASC,firstname ASC,middlename ASC ,lastname ASC
                LIMIT 10 ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityName);
        $stmt->bindValue(2, $municipalityName);
        $stmt->bindValue(3, $barangayName);
        $stmt->bindValue(4, $barangayName);
        $stmt->bindValue(5, '%' . $voterName . '%');
        $stmt->bindValue(6, '%' . $voterName . '%');
        $stmt->bindValue(7, '%' . $voterName . '%');
        $stmt->bindValue(8, $voterName);
        $stmt->bindValue(9, $voterName);
        $stmt->bindValue(10, $voterName);

        $stmt->execute();
        $voters = $stmt->fetchAll();

        if ($voters == null) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($voters);
    }

    /**
     * @Route("/ajax_m_get_barangays_by_name/{municipalityName}",
     *       name="ajax_m_get_barangays",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetBarangaysByName(Request $request, $municipalityName)
    {

        // $em = $this->getDoctrine()->getManager();

        // $sql = "SELECT * FROM psw_barangay b
        //         WHERE b.municipality_code = ? ORDER BY b.name ASC";

        // $stmt = $em->getConnection()->prepare($sql);
        // $stmt->bindValue(1,$municipalityCode);

        // $stmt->execute();
        // $barangays = $stmt->fetchAll();

        // if(count($barangays) <= 0)
        //     return new JsonResponse(array());

        // $em->clear();

        // return new JsonResponse($barangays);

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT barangay_name FROM tbl_project_voter pv
                WHERE pv.municipality_name = ? ORDER BY pv.barangay_name ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityName);

        $stmt->execute();
        $barangays = $stmt->fetchAll();

        if (count($barangays) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($barangays);
    }

    /**
     * @Route("/ajax_m_get_precincts/{municipalityName}/{barangayName}",
     *       name="ajax_m_get_precincts",
     *        options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetPrecincts(Request $request, $municipalityName, $barangayName)
    {

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT DISTINCT precinct_no FROM tbl_project_voter pv
                WHERE pv.municipality_name = ? AND pv.barangay_name = ? ORDER BY pv.precinct_no ASC";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $municipalityName);
        $stmt->bindValue(2, $barangayName);

        $stmt->execute();
        $barangays = $stmt->fetchAll();

        if (count($barangays) <= 0) {
            return new JsonResponse(array());
        }

        $em->clear();

        return new JsonResponse($barangays);
    }

}
