<?php
namespace AppBundle\Controller;

use AppBundle\Entity\DataUpdateDetail;
use AppBundle\Entity\DataUpdateHeader;
use AppBundle\Entity\ProjectVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/manage-updates")
 */

class UpdateManagerController extends Controller
{
    const STATUS_ACTIVE = 'A';
    const MODULE_MAIN = "UPDATE_MANAGER";

    /**
     * @Route("", name="update_manager_index", options={"main" = true})
     */

    public function indexAction(Request $request)
    {
        // $this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');

        return $this->render('template/update-manager/index.html.twig', ['user' => $user, 'hostIp' => $hostIp]);
    }

    /**
     * @Route("/ajax_get_did_change_voter/{proId}/{electId}",
     *   name="ajax_get_did_change_voter",
     *   options={"expose" = true}
     * )
     * @Method("GET")
     */

    public function ajaxGetDidChangeVoter(Request $request, $proId, $electId)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT pv.barangay_name,pv.voter_name,pv.pro_id_code,pv.voter_id,pv.pro_voter_id,pv.updated_at
                FROM tbl_project_voter pv WHERE pv.did_changed = 1 AND pv.pro_id = ? AND  pv.elect_id =  ? 
                ORDER BY pv.voter_name ASC LIMIT 500";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $proId);
        $stmt->bindValue(2, $electId);
        $stmt->execute();
        $data = array();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }

        $em->clear();

        return new JsonResponse($data);
    }

    /**
     * @Route("/ajax_post_updated_records",
     *   name="ajax_post_updated_records",
     *   options={"expose" = true}
     * )
     * @Method("POST")
     */

    public function ajaxPostUpdatedRecords(Request $request)
    {

        $self = $this;
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $emRemote = $this->getDoctrine()->getManager("remote");

        $remoteIP = $this->getParameter('remote_ip_address');
        $remoteDatasource = $this->getParameter("remote_datasource");

        $projectVoters = $request->get('projectVoters');

        if (count($projectVoters) <= 0) {
            return new JsonResponse(['projectVoters' => "Action denied. You cannot proceed on importing  data with an empty list."], 400);
        }

        $response = new StreamedResponse();
        $response->headers->set("Cache-Control", "no-cache, must-revalidate");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->setStatusCode(200);

        $response->setCallback(function () use ($self, $em, $emRemote, $user, $projectVoters, $remoteIP, $remoteDatasource) {

            $header = new DataUpdateHeader();
            $header->setDataSource("dada server");
            $header->setTotalCount(count($projectVoters));
            $header->setCreatedAt(new \DateTime());
            $header->setCreatedBy($user->getUsername());
            $header->setStatus("A");

            $em->persist($header);
            $em->flush();

            $processed = 0;
            $imported = 0;
            $total = count($projectVoters);
            $currPercentage = 0;
            $prevPercentage = 0;

            foreach ($projectVoters as $proVoterId) {
                $processed++;

                $localProjectVoter = $em->getRepository("AppBundle:ProjectVoter")
                    ->find($proVoterId);
                    
                $projectVoter = $emRemote->getRepository("AppBundle:ProjectVoter")
                    ->find($proVoterId);
                    
            
                $detail = new DataUpdateDetail();
                $detail->setHdrId($header->getHdrId());
                $detail->setProVoterId($localProjectVoter->getProVoterId());
                $detail->setProId($localProjectVoter->getProId());
                $detail->setElectId($localProjectVoter->getElectId());
                $detail->setProIdCode($localProjectVoter->getProIdCode());
                $detail->setVoterName($localProjectVoter->getVoterName());
                $detail->setCellphone($localProjectVoter->getCellphone());
                $detail->setVoterGroup($localProjectVoter->getVoterGroup());
                $detail->setPosition($localProjectVoter->getPosition());
                $detail->setHasPhoto($localProjectVoter->getHasPhoto());
                $detail->setPhotoAt($localProjectVoter->getPhotoAt());
                $detail->setHasId($localProjectVoter->getHasId());
                $detail->setUpdatedAt($localProjectVoter->getUpdatedAt());
                $detail->setUpdatedBy($localProjectVoter->getUpdatedBy());
                $detail->setCreatedAt(new \DateTime());
                $detail->setCreatedBy($user->getUsername());

                if ($projectVoter && $localProjectVoter) {

                    $detail->setStatus('A');

                    $projectVoter->setProIdCode($localProjectVoter->getProIdCode());
                    $projectVoter->setGeneratedIdNo($localProjectVoter->getGeneratedIdNo());
                    $projectVoter->setDateGenerated($localProjectVoter->getDateGenerated());
                    $projectVoter->setCellphone($localProjectVoter->getCellphone());
                    $projectVoter->setVoterGroup($localProjectVoter->getVoterGroup());
                    $projectVoter->setPosition($localProjectVoter->getPosition());
                    $projectVoter->setHasPhoto($localProjectVoter->getHasPhoto());
                    $projectVoter->setPhotoAt($localProjectVoter->getPhotoAt());
                    $projectVoter->setHasId($localProjectVoter->getHasId());
                    $projectVoter->setDidChanged(0);
                    $projectVoter->setIsNonVoter($localProjectVoter->getIsNonVoter());
                    $projectVoter->setUpdatedAt($localProjectVoter->getUpdatedAt());
                    $projectVoter->setUpdatedBy($localProjectVoter->getUpdatedBy());
                    $projectVoter->setStatus($localProjectVoter->getStatus());

                    $imported++;

                }else {
                    $detail->setStatus('C');
                }

                $localProjectVoter->setDidChanged(0);

                $em->persist($detail);

                $em->flush();
                $emRemote->flush();

                // $rootDir = __DIR__ . '/../../../web/uploads/images/';
                // $filename = $projectVoter->getProId() . '_' . $projectVoter->getGeneratedIdNo();
                // $imagePath = $rootDir . $filename . '.jpg';

                // $remoteImgSrcUrl = "http://" . $remoteIP . "/voter/photo/";

                // file_put_contents($imagePath, fopen($remoteImgSrcUrl . $filename, 'r'));

                $currPercentage = (int) (($processed / $total) * 100);

                if ($currPercentage != $prevPercentage) {
                    $prevPercentage = $currPercentage;

                    echo $currPercentage;
                }

                ob_flush();
                flush();
            }

            $header->setTotalProcessed($processed);
            $header->setTotalImported($imported);

            $em->flush();

            $em->clear();
            $emRemote->clear();
        });

        return $response;
    }

    /**
     * @Route("/ajax_get_data_import_datatable",
     *     name="ajax_get_data_import_datatable",
     *     options={"expose" = true})
     *
     * @Method("GET")
     */

    public function dataImportDatatableAction(Request $request)
    {

        $filters = [];
        $filters['h.created_at'] = $request->get("createdAt");

        $columns = [
            0 => 'h.pro_id',
            1 => 'h.data_source',
            2 => 'h.total_count',
            3 => 'h.total_processed',
            4 => 'h.total_imported',
            5 => 'h.created_at',
            6 => 'h.created_by',
        ];

        $whereStmt = " AND (";

        foreach ($filters as $field => $searchText) {
            if ($searchText != "") {
                $whereStmt .= "{$field} LIKE '%$searchText%' AND ";
            }
        }

        $whereStmt = substr_replace($whereStmt, "", -4);

        if ($whereStmt == " A") {
            $whereStmt = "";
        } else {
            $whereStmt .= ")";
        }

        $orderStmt = " ORDER BY h.hdr_id DESC";

        $start = 1;
        $length = 1;

        if (null !== $request->query->get('start') && null !== $request->query->get('length')) {
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(h.hdr_id),0) FROM tbl_data_update_header h";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(h.hdr_id),0) FROM tbl_data_update_header h WHERE 1=1 ";

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT h.* FROM tbl_data_update_header h WHERE 1=1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        $em->clear();

        return new JsonResponse($res);
    }

    /**
     * @Route("/ajax_get_data_import_detail_datatable",
     *     name="ajax_get_data_import_detail_datatable",
     *     options={"expose" = true})
     *
     * @Method("GET")
     */

    public function dataImportDetailDatatableAction(Request $request)
    {

        $filters = [];

        $filters['d.hdr_id'] = $request->get("hdrId");
        $filters['d.voter_name'] = $request->get("voterName");
        $filters['d.status'] = $request->get("status");
        $filters['d.has_id'] = $request->get("hasId");
        $filters['d.has_photo'] = $request->get("hasPhoto");

        $columns = [
            1 => 'd.voter_name',
            2 => 'd.voter_group',
            3 => 'd.has_photo',
            4 => 'd.has_id',
            5 => 'd.cellphone',
            6 => 'd.updated_at',
            7 => 'd.updated_by',
            8 => 'd.status',
        ];

        $whereStmt = " AND (";

        foreach ($filters as $field => $searchText) {
            if ($searchText != "") {
                if ($field == 'd.hdr_id' || $field == 'd.status') {
                    $whereStmt .= "{$field} = '$searchText' AND ";
                } else {
                    $whereStmt .= "{$field} LIKE '%$searchText%' AND ";
                }
            }
        }

        $whereStmt = substr_replace($whereStmt, "", -4);

        if ($whereStmt == " A") {
            $whereStmt = "";
        } else {
            $whereStmt .= ")";
        }

        $orderStmt = " ORDER BY d.voter_name";

        $start = 1;
        $length = 1;

        if (null !== $request->query->get('start') && null !== $request->query->get('length')) {
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(d.dtl_id),0) FROM tbl_data_update_detail d";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(d.dtl_id),0) FROM tbl_data_update_detail d WHERE 1=1 ";

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT d.* FROM tbl_data_update_detail d WHERE 1=1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] = $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        $em->clear();

        return new JsonResponse($res);
    }
}
