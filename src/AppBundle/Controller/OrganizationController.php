<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use AppBundle\Entity\ProjectVoterSummary;
use AppBundle\Entity\ProjectVoterSummaryAssigned;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;

use Box\Spout\Writer\Style\Border;
use Box\Spout\Writer\Style\BorderBuilder;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;

/**
* @Route("/organization")
*/

class OrganizationController extends Controller 
{
    const MODULE_MAIN = "ORGANIZATION_SUMMARY";
    const MODULE_PHOTO_SUMMARY = "ORGANIZATION_PHOTO_SUMMARY";

	/**
    * @Route("/summary", name="organizatoin_summary", options={"main" = true })
    */

    public function organizationSummaryAction(Request $request)
    {
        $this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');
        $reportUrl = $this->getParameter('report_url');
       
        return $this->render('template/organization/organization_summary.html.twig',
            [ 
                'user' => $user,
                'hostIp' => $hostIp,
                'imgUrl' => $imgUrl,
                'reportUrl' => $reportUrl
            ]);
    }
    
	/**
    * @Route("/target-summary", name="organizatoin_target_summary", options={"main" = true })
    */

    public function organizationTargetSummaryAction(Request $request)
    {
        $this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');
        $reportUrl = $this->getParameter('report_url');
       
        return $this->render('template/organization/organization_target_summary.html.twig',
            [ 
                'user' => $user, 
                "hostIp" => $hostIp, 
                'imgUrl' => $imgUrl,
                'reportUrl' => $reportUrl 
            ]);
    }

    /**
    * @Route("/photo-summary", name="organizatoin_photo_summary", options={"main" = true })
    */

    public function organizationPhotoSummaryAction(Request $request)
    {
        $this->denyAccessUnlessGranted("entrance",self::MODULE_PHOTO_SUMMARY);

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');
        $imgUrl = $this->getParameter('img_url');
       
        return $this->render('template/organization/organization_photo_summary.html.twig',[ 'user' => $user, "hostIp" => $hostIp, 'imgUrl' => $imgUrl ]);
    }

    /**
    * @Route("/ajax_select2_summary_dates", 
    *       name="ajax_select2_summary_dates",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxSelect2SummaryDates(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $electId = $request->get('electId');
        $proId = $request->get('proId');
        
        $searchText = trim(strtoupper($request->get('searchText')));
        $searchText = '%' . strtoupper($searchText) . '%';

        $sql = "SELECT DISTINCT created_at 
                FROM tbl_project_voter_summary 
                WHERE (created_at LIKE ? OR ? IS NULL) AND elect_id = ? AND pro_id = ? 
                ORDER BY created_at DESC LIMIT 20";
        
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$searchText);
        $stmt->bindValue(2,empty($request->get('searchText')) ? null : $request->get('searchText'));
        $stmt->bindValue(3,$electId);
        $stmt->bindValue(4,$proId);
        $stmt->execute();

        $dates = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $dates[] = $row;
        }

        return new JsonResponse($dates);
    }
    
    /**
    * @Route("/ajax_get_province_organization_summary", 
    *       name="ajax_get_province_organization_summary",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetProvinceOrganizationSummary(Request $request){
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $provinceCode  =  empty($request->get("provinceCode")) ? 53  : $request->get("provinceCode");
        $electId = empty($request->get("electId")) ? null : $request->get("electId");
        $proId = empty($request->get("proId")) ? null : $request->get("proId");
        $createdAt = empty($request->get('createdAt')) ? null : $request->get('createdAt');

        if($createdAt == null)
            $createdAt = $this->getLastDateComputed($electId,$proId);
        
        $sql = "SELECT m.*,
        (SELECT coalesce( SUM(pv.total_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ? AND pv.pro_id = ? ) as total_voters,
        (SELECT coalesce( count(DISTINCT pv.brgy_no),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ? AND pv.pro_id = ? ) as total_barangays,
        (SELECT coalesce( count(pv.sum_id),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ? AND pv.pro_id = ? ) as total_precincts,

        (SELECT coalesce(count(DISTINCT pv.clustered_precinct),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ? AND pv.pro_id = ? AND pv.created_at = ? ) as total_clustered_precincts,
        (SELECT coalesce(sum(pv.total_member),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ?  AND pv.created_at = ? ) AS total_recruits,
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
        WHERE m.province_code = ? ";
        
        $accessFilter = "";        
        
        if(!$user->getIsAdmin()){
            //$accessFilter = $this->getMunicipalityAccessFilter($user->getId());
        }
        
        $sql .= $accessFilter . " ORDER BY m.name ASC";
        
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$electId);
        $stmt->bindValue(3,$proId);

        $stmt->bindValue(4,$provinceCode);
        $stmt->bindValue(5,$electId);
        $stmt->bindValue(6,$proId);

        $stmt->bindValue(7,$provinceCode);
        $stmt->bindValue(8,$electId);
        $stmt->bindValue(9,$proId);

        $stmt->bindValue(10,$provinceCode);
        $stmt->bindValue(11,$electId);
        $stmt->bindValue(12,$proId);
        $stmt->bindValue(13,$createdAt);

        $stmt->bindValue(14,$provinceCode);
        $stmt->bindValue(15,$electId);
        $stmt->bindValue(16,$proId);
        $stmt->bindValue(17,$createdAt);

        $stmt->bindValue(18,$provinceCode);
        $stmt->bindValue(19,$electId);
        $stmt->bindValue(20,$proId);
        $stmt->bindValue(21,$createdAt);
        
        $stmt->bindValue(22,$provinceCode);
        $stmt->bindValue(23,$electId);
        $stmt->bindValue(24,$proId);
        $stmt->bindValue(25,$createdAt);

        $stmt->bindValue(26,$provinceCode);
        $stmt->bindValue(27,$electId);
        $stmt->bindValue(28,$proId);
        $stmt->bindValue(29,$createdAt);

        $stmt->bindValue(30,$provinceCode);
        $stmt->bindValue(31,$electId);
        $stmt->bindValue(32,$proId);
        $stmt->bindValue(33,$createdAt);

        $stmt->bindValue(34,$provinceCode);
        $stmt->bindValue(35,$electId);
        $stmt->bindValue(36,$proId);
        $stmt->bindValue(37,$createdAt);
        
        $stmt->bindValue(38,$provinceCode);
        $stmt->bindValue(39,$electId);
        $stmt->bindValue(40,$proId);
        $stmt->bindValue(41,$createdAt);

        $stmt->bindValue(42,$provinceCode);
        $stmt->bindValue(43,$electId);
        $stmt->bindValue(44,$proId);
        $stmt->bindValue(45,$createdAt);

        $stmt->bindValue(46,$provinceCode);
        $stmt->bindValue(47,$electId);
        $stmt->bindValue(48,$proId);
        $stmt->bindValue(49,$createdAt);

        $stmt->bindValue(50,$provinceCode);
        $stmt->bindValue(51,$electId);
        $stmt->bindValue(52,$proId);
        $stmt->bindValue(53,$createdAt);
        
        $stmt->bindValue(54,$provinceCode);
        $stmt->bindValue(55,$electId);
        $stmt->bindValue(56,$proId);
        $stmt->bindValue(57,$createdAt);

        $stmt->bindValue(58,$provinceCode);
        $stmt->bindValue(59,$electId);
        $stmt->bindValue(60,$proId);
        $stmt->bindValue(61,$createdAt);

        $stmt->bindValue(62,$provinceCode);
        $stmt->bindValue(63,$electId);
        $stmt->bindValue(64,$proId);
        $stmt->bindValue(65,$createdAt);

        $stmt->bindValue(66,$provinceCode);
        $stmt->bindValue(67,$electId);
        $stmt->bindValue(68,$proId);
        $stmt->bindValue(69,$createdAt);
        
        $stmt->bindValue(70,$provinceCode);
        $stmt->bindValue(71,$electId);
        $stmt->bindValue(72,$proId);
        $stmt->bindValue(73,$createdAt);
        
        $stmt->bindValue(74,$provinceCode);
        $stmt->bindValue(75,$electId);
        $stmt->bindValue(76,$proId);
        $stmt->bindValue(77,$createdAt);

        $stmt->bindValue(78,$provinceCode);
        $stmt->bindValue(79,$electId);
        $stmt->bindValue(80,$proId);
        $stmt->bindValue(81,$createdAt);
        
        $stmt->bindValue(82,$provinceCode);
        $stmt->bindValue(83,$electId);
        $stmt->bindValue(84,$proId);
        $stmt->bindValue(85,$createdAt);
        
        $stmt->bindValue(86,$provinceCode);
        $stmt->bindValue(87,$electId);
        $stmt->bindValue(88,$proId);
        $stmt->bindValue(89,$createdAt);
        
        $stmt->bindValue(90,$provinceCode);
        $stmt->bindValue(91,$electId);
        $stmt->bindValue(92,$proId);
        $stmt->bindValue(93,$createdAt);
        
        $stmt->bindValue(94,$provinceCode);
        $stmt->bindValue(95,$electId);
        $stmt->bindValue(96,$proId);
        $stmt->bindValue(97,$createdAt);
        
        $stmt->bindValue(98,$provinceCode);
        $stmt->bindValue(99,$electId);
        $stmt->bindValue(100,$proId);
        $stmt->bindValue(101,$createdAt);
        
        $stmt->bindValue(102,$provinceCode);
        $stmt->bindValue(103,$electId);
        $stmt->bindValue(104,$proId);
        $stmt->bindValue(105,$createdAt);

        $stmt->bindValue(106,$provinceCode);
        $stmt->bindValue(107,$electId);
        $stmt->bindValue(108,$proId);
        $stmt->bindValue(109,$createdAt);
        
        $stmt->bindValue(110,$provinceCode);
        $stmt->bindValue(111,$electId);
        $stmt->bindValue(112,$proId);
        $stmt->bindValue(113,$createdAt);

        $stmt->bindValue(114,$provinceCode);
        $stmt->bindValue(115,$electId);
        $stmt->bindValue(116,$proId);
        $stmt->bindValue(117,$createdAt);
        
        $stmt->bindValue(118,$provinceCode);
        $stmt->bindValue(119,$electId);
        $stmt->bindValue(120,$proId);
        $stmt->bindValue(121,$createdAt);
    
        $stmt->bindValue(122,$provinceCode);
        $stmt->bindValue(123,$electId);
        $stmt->bindValue(124,$proId);
        $stmt->bindValue(125,$createdAt);
        
        $stmt->bindValue(126,$provinceCode);
        $stmt->bindValue(127,$electId);
        $stmt->bindValue(128,$proId);
        $stmt->bindValue(129,$createdAt);
        
        $stmt->bindValue(130,$provinceCode);
        $stmt->bindValue(131,$electId);
        $stmt->bindValue(132,$proId);
        $stmt->bindValue(133,$createdAt);
        
        $stmt->bindValue(134,$provinceCode);
        $stmt->bindValue(135,$electId);
        $stmt->bindValue(136,$proId);
        $stmt->bindValue(137,$createdAt);
        
        $stmt->bindValue(138,$provinceCode);
        $stmt->bindValue(139,$electId);
        $stmt->bindValue(140,$proId);
        $stmt->bindValue(141,$createdAt);

        $stmt->bindValue(142,$provinceCode);
        $stmt->bindValue(143,$electId);
        $stmt->bindValue(144,$proId);
        $stmt->bindValue(145,$createdAt);
        
        $stmt->bindValue(146,$provinceCode);
        $stmt->bindValue(147,$electId);
        $stmt->bindValue(148,$proId);
        $stmt->bindValue(149,$createdAt);

        $stmt->bindValue(150,$provinceCode);
        $stmt->bindValue(151,$electId);
        $stmt->bindValue(152,$proId);
        $stmt->bindValue(153,$createdAt);

        $stmt->bindValue(154,$provinceCode);
        $stmt->bindValue(155,$electId);
        $stmt->bindValue(156,$proId);
        $stmt->bindValue(157,$createdAt);

        $stmt->bindValue(158,$provinceCode);
        $stmt->bindValue(159,$electId);
        $stmt->bindValue(160,$proId);
        $stmt->bindValue(161,$createdAt);

        $stmt->bindValue(162,$provinceCode);
        $stmt->bindValue(163,$electId);
        $stmt->bindValue(164,$proId);
        $stmt->bindValue(165,$createdAt);

        $stmt->bindValue(166,$provinceCode);
        $stmt->bindValue(167,$electId);
        $stmt->bindValue(168,$proId);
        $stmt->bindValue(169,$createdAt);

        $stmt->bindValue(170,$provinceCode);
        $stmt->bindValue(171,$electId);
        $stmt->bindValue(172,$proId);
        $stmt->bindValue(173,$createdAt);

        $stmt->bindValue(174,$provinceCode);
        $stmt->bindValue(175,$electId);
        $stmt->bindValue(176,$proId);
        $stmt->bindValue(177,$createdAt);

        $stmt->bindValue(178,$provinceCode);
        $stmt->bindValue(179,$electId);
        $stmt->bindValue(180,$proId);
        $stmt->bindValue(181,$createdAt);

        $stmt->bindValue(182,$provinceCode);
        $stmt->bindValue(183,$electId);
        $stmt->bindValue(184,$proId);
        $stmt->bindValue(185,$createdAt);

        $stmt->bindValue(186,$provinceCode);
        $stmt->execute();

        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
           
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
            $temp['total_no_id_members'] = $temp['total_no_id_kcl3'] + $temp['total_with_id_kjr'];

            $data[] = $temp; 
        }
        
        return new JsonResponse($data);
    }

    /**
    * @Route("/ajax_get_municipality_organization_summary", 
    *       name="ajax_get_municipality_organization_summary",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetMunicipalityDataSummary(Request $request){
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $electId = empty($request->get("electId")) ? null : $request->get("electId");
        $proId = empty($request->get("proId")) ? null : $request->get("proId");
        $provinceCode = empty($request->get("provinceCode")) ? 53 : $request->get('provinceCode');
        $municipalityNo = $request->get("municipalityNo");
        $createdAt = empty($request->get('createdAt')) ? null : $request->get('createdAt');
        
        if($createdAt == null)
            $createdAt = $this->getLastDateComputed($electId,$proId);

        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT b.*,
        (SELECT COALESCE(SUM(pv.total_others),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ? AND pv.pro_id = ? ) as total_voters,
        (SELECT COALESCE(COUNT(DISTINCT pv.precinct_no),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ?  AND pv.pro_id = ? ) as total_precincts,

        (SELECT coalesce(count(DISTINCT pv.clustered_precinct),0) FROM tbl_project_voter_summary pv WHERE pv.municipality_no = m.municipality_no AND pv.brgy_no = b.brgy_no AND pv.province_code = ? AND pv.elect_id = ? AND pv.pro_id = ? AND pv.created_at = ? ) as total_clustered_precincts,
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
        WHERE b.municipality_code = ? ";
        
        $accessFilter  = "";

        if(!$user->getIsAdmin() && $user->getStrictAccess()){
            //$accessFilter = $this->getBarangayAccessFilter($user->getId(),$municipalityNo);
        }
      
        $sql .= $accessFilter . " ORDER BY b.name ASC";
        
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
        $stmt->execute();

        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            
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

    /**
    * @Route("/ajax_get_barangay_organization_summary", 
    *       name="ajax_get_barangay_organization_summary",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetBarangayDataSummary(Request $request){
        $electId = $request->get("electId");
        $proId = $request->get("proId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");
        $createdAt = empty($request->get('createdAt')) ? null : $request->get('createdAt');

        if($createdAt == null)
            $createdAt = $this->getLastDateComputed($electId,$proId);
   
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT  pv.precinct_no, pv.total_others as total_voters, pv.total_member as total_recruits, pv.total_level_1 AS total_ch, pv.total_level_2 AS total_kcl, 
        pv.total_level_3 AS total_kcl_0, pv.total_level_4 AS total_kcl_1, pv.total_level_5 AS total_kcl_2, pv.total_level_6 AS total_kcl_3, pv.total_level_7 AS total_kjr,
        pv.total_staff AS total_dao, pv.total_others,
        
        pv.total_with_id_member as total_with_id_recruits, pv.total_with_id_level_1 AS total_with_id_ch, pv.total_with_id_level_2 AS total_with_id_kcl, 
        pv.total_with_id_level_3 AS total_with_id_kcl_0, pv.total_with_id_level_4 AS total_with_id_kcl_1, pv.total_with_id_level_5 AS total_with_id_kcl_2, pv.total_with_id_level_6 AS total_with_id_kcl_3, pv.total_with_id_level_7 AS total_with_id_kjr,
        pv.total_with_id_staff AS total_with_id_dao, pv.total_with_id_others,

        pv.total_submitted, pv.total_has_submitted_level_1 AS total_has_submitted_ch, pv.total_has_submitted_level_2 AS total_has_submitted_kcl, 
        pv.total_has_submitted_level_3 AS total_has_submitted_kcl_0, pv.total_has_submitted_level_4 AS total_has_submitted_kcl_1, pv.total_has_submitted_level_5 AS total_has_submitted_kcl_2, pv.total_has_submitted_level_6 AS total_has_submitted_kcl_3, pv.total_has_submitted_level_7 AS total_has_submitted_kjr,
       
        pv.total_has_cellphone, pv.total_with_id_cellphone, pv.clustered_precinct,
        (SELECT COUNT(DISTINCT ppv.clustered_precinct) FROM tbl_project_voter_summary ppv WHERE ppv.province_code = pv.province_code AND ppv.municipality_no = pv.municipality_no AND ppv.brgy_no = pv.brgy_no AND ppv.elect_id = pv.elect_id AND ppv.pro_id = pv.pro_id AND ppv.created_at = pv.created_at ) AS total_clustered_precincts 
        FROM tbl_project_voter_summary pv 
         
        WHERE pv.province_code = ? AND pv.municipality_no = ? AND pv.brgy_no = ? AND pv.elect_id = ? AND pv.pro_id = ? AND pv.created_at = ? GROUP BY pv.precinct_no ASC";
        
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->bindValue(3,$brgyNo);
        $stmt->bindValue(4,$electId);
        $stmt->bindValue(5,$proId);
        $stmt->bindValue(6,$createdAt);
        
        $stmt->execute();
        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $temp = $row;
        
            $temp['total_no_id_recruits'] = $row['total_recruits'] - $row['total_with_id_recruits'];
            $temp['total_no_id_ch'] = $row['total_ch'] - $row['total_with_id_ch'];
            $temp['total_no_id_kcl'] = $row['total_kcl'] - $row['total_with_id_kcl'];
            $temp['total_no_id_kcl_0'] = $row['total_kcl_0'] - $row['total_with_id_kcl_0'];
            $temp['total_no_id_kcl_1'] = $row['total_kcl_1'] - $row['total_with_id_kcl_1'];
            $temp['total_no_id_kcl_2'] = $row['total_kcl_2'] - $row['total_with_id_kcl_2'];
            $temp['total_no_id_kcl_3'] = $row['total_kcl_3'] - $row['total_with_id_kcl_3'];
            $temp['total_no_id_kjr'] = $row['total_kjr'] - $row['total_with_id_kjr'];
            $temp['total_no_id_staff'] = $row['total_dao'] - $row['total_with_id_dao'];
            $temp['total_no_id_others'] = $row['total_others'] - $row['total_with_id_others'];
            $temp['total_no_id_cellphone'] = $row['total_has_cellphone'] - $row['total_with_id_cellphone'];

            $temp['total_tl'] = $temp['total_ch'] + $temp['total_kcl'];
            $temp['total_sl'] = $temp['total_kcl_0'] + $temp['total_kcl_1'] + $temp['total_kcl_2'];
            $temp['total_members'] = $temp['total_kcl_3'] + $temp['total_kjr'];

            $temp['total_with_id_tl'] = $temp['total_with_id_ch'] + $temp['total_with_id_kcl'];
            $temp['total_with_id_sl'] = $temp['total_with_id_kcl_0'] + $temp['total_with_id_kcl_1'] + $temp['total_with_id_kcl_2'];
            $temp['total_with_id_members'] = $temp['total_with_id_kcl_3'] + $temp['total_kjr'];

            $temp['total_no_id_tl'] = $temp['total_no_id_ch'] + $temp['total_no_id_kcl'];
            $temp['total_no_id_sl'] = $temp['total_no_id_kcl_0'] + $temp['total_no_id_kcl_1'] + $temp['total_no_id_kcl_2'];
            $temp['total_no_id_members'] = $temp['total_no_id_kcl_3']  + $temp['total_no_id_kjr'];

            $data[] = $temp;
        }

        return new JsonResponse($data);
    }


    /**
    * @Route("/ajax_get_barangay_organization_summary_assigned", 
    *       name="ajax_get_barangay_organization_summary_assigned",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetBarangayDataSummaryAssigned(Request $request){
        $electId = $request->get("electId");
        $proId = $request->get("proId");
        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");
        $createdAt = empty($request->get('createdAt')) ? null : $request->get('createdAt');

        if($createdAt == null)
            $createdAt = $this->getLastDateComputed($electId,$proId);
   
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT s.* ,pv.total_member as total_recruits, pv.total_level_1 AS total_ch, pv.total_level_2 AS total_kcl, 
        pv.total_level_3 AS total_kcl_0, pv.total_level_4 AS total_kcl_1, pv.total_level_5 AS total_kcl_2, pv.total_level_6 AS total_kcl_3, pv.total_level_7 AS total_kjr,
        pv.total_staff AS total_dao, pv.total_others,
        
        pv.total_with_id_member as total_with_id_recruits, pv.total_with_id_level_1 AS total_with_id_ch, pv.total_with_id_level_2 AS total_with_id_kcl, 
        pv.total_with_id_level_3 AS total_with_id_kcl_0, pv.total_with_id_level_4 AS total_with_id_kcl_1, pv.total_with_id_level_5 AS total_with_id_kcl_2, pv.total_with_id_level_6 AS total_with_id_kcl_3, pv.total_with_id_level_7 AS total_with_id_kjr,
        pv.total_with_id_staff AS total_with_id_dao, pv.total_with_id_others,

        pv.total_submitted, pv.total_has_submitted_level_1 AS total_has_submitted_ch, pv.total_has_submitted_level_2 AS total_has_submitted_kcl, 
        pv.total_has_submitted_level_3 AS total_has_submitted_kcl_0, pv.total_has_submitted_level_4 AS total_has_submitted_kcl_1, pv.total_has_submitted_level_5 AS total_has_submitted_kcl_2, pv.total_has_submitted_level_6 AS total_has_submitted_kcl_3, pv.total_has_submitted_level_7 AS total_has_submitted_kjr,
       
        pv.total_has_cellphone, pv.total_with_id_cellphone, pv.clustered_precinct,
        (SELECT COUNT(DISTINCT ppv.clustered_precinct) FROM tbl_project_voter_summary_assigned ppv WHERE ppv.province_code = pv.province_code AND ppv.municipality_no = pv.municipality_no AND ppv.brgy_no = pv.brgy_no AND ppv.elect_id = pv.elect_id AND ppv.pro_id = pv.pro_id AND ppv.created_at = pv.created_at ) AS total_clustered_precincts 
        FROM tbl_voter_summary s  INNER JOIN tbl_project_voter_summary_assigned pv 
        ON s.pro_id = pv.pro_id AND s.elect_id = pv.elect_id AND pv.province_code = s.province_code AND pv.municipality_no = s.municipality_no AND pv.precinct_no = s.precinct_no AND pv.brgy_no = s.brgy_no 
        WHERE pv.province_code = ? AND pv.municipality_no = ? AND pv.brgy_no = ? AND pv.elect_id = ? AND pv.pro_id = ? AND pv.created_at = ? GROUP BY s.precinct_no ASC";
        
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->bindValue(3,$brgyNo);
        $stmt->bindValue(4,$electId);
        $stmt->bindValue(5,$proId);
        $stmt->bindValue(6,$createdAt);
        
        $stmt->execute();
        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $temp = $row;
        
            $temp['total_no_id_recruits'] = $row['total_recruits'] - $row['total_with_id_recruits'];
            $temp['total_no_id_ch'] = $row['total_ch'] - $row['total_with_id_ch'];
            $temp['total_no_id_kcl'] = $row['total_kcl'] - $row['total_with_id_kcl'];
            $temp['total_no_id_kcl_0'] = $row['total_kcl_0'] - $row['total_with_id_kcl_0'];
            $temp['total_no_id_kcl_1'] = $row['total_kcl_1'] - $row['total_with_id_kcl_1'];
            $temp['total_no_id_kcl_2'] = $row['total_kcl_2'] - $row['total_with_id_kcl_2'];
            $temp['total_no_id_kcl_3'] = $row['total_kcl_3'] - $row['total_with_id_kcl_3'];
            $temp['total_no_id_kjr'] = $row['total_kjr'] - $row['total_with_id_kjr'];
            $temp['total_no_id_staff'] = $row['total_dao'] - $row['total_with_id_dao'];
            $temp['total_no_id_others'] = $row['total_others'] - $row['total_with_id_others'];
            $temp['total_no_id_cellphone'] = $row['total_has_cellphone'] - $row['total_with_id_cellphone'];

            $temp['total_tl'] = $temp['total_ch'] + $temp['total_kcl'];
            $temp['total_sl'] = $temp['total_kcl_0'] + $temp['total_kcl_1'] + $temp['total_kcl_2'];
            $temp['total_members'] = $temp['total_kcl_3'] + $temp['total_kjr'];

            $temp['total_with_id_tl'] = $temp['total_with_id_ch'] + $temp['total_with_id_kcl'];
            $temp['total_with_id_sl'] = $temp['total_with_id_kcl_0'] + $temp['total_with_id_kcl_1'] + $temp['total_with_id_kcl_2'];
            $temp['total_with_id_members'] = $temp['total_with_id_kcl_3'] + $temp['total_kjr'];

            $temp['total_no_id_tl'] = $temp['total_no_id_ch'] + $temp['total_no_id_kcl'];
            $temp['total_no_id_sl'] = $temp['total_no_id_kcl_0'] + $temp['total_no_id_kcl_1'] + $temp['total_no_id_kcl_2'];
            $temp['total_no_id_members'] = $temp['total_no_id_kcl_3']  + $temp['total_no_id_kjr'];
            $temp['is_assigned'] = 1;

            $data[] = $temp;
        }

        return new JsonResponse($data);
    }

    /**
    * @Route("/ajax_get_last_date_computed", 
    *       name="ajax_get_last_date_computed",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetLastDateComputed(Request $request){
        $electId = empty($request->get("electId")) ? null : $request->get("electId");
        $proId = empty($request->get("proId")) ? null : $request->get("proId");

        return new JsonResponse(['createdAt' => $this->getLastDateComputed($electId,$proId)]);
    }

    private function getLastDateComputed($electId,$proId){
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM tbl_project_voter_summary WHERE elect_id = ? AND pro_id = ? ORDER BY created_at DESC LIMIT 1";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$electId);
        $stmt->bindValue(2,$proId);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $row == null ? null : $row['created_at'];
    }

    /**
    * @Route("/ajax_get_municipality_organization_photo_summary", 
    *       name="ajax_get_municipality_organization_photo_summary",
    *		options={ "expose" = true }
    * )
    * @Method("GET")
    */

    public function ajaxGetMunicipalityDataPhotoSummary(Request $request){
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $electId = empty($request->get("electId")) ? null : $request->get("electId");
        $proId = empty($request->get("proId")) ? null : $request->get("proId");
        $provinceCode = empty($request->get("provinceCode")) ? 53 : $request->get('provinceCode');
        $municipalityNo = $request->get("municipalityNo");
        $photoDate = empty($request->get("photoDate")) ? new \DateTime() : new \DateTime($request->get("photoDate"));
        
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT b.*,
        (SELECT COALESCE(SUM(s.total_voters),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ? AND s.elect_id = ? AND s.pro_id = ? ) as total_voters,
        (SELECT COALESCE(COUNT(DISTINCT s.precinct_no),0) FROM tbl_voter_summary s WHERE s.municipality_no = m.municipality_no AND s.brgy_no = b.brgy_no AND s.province_code = ? AND s.elect_id = ?  AND s.pro_id = ? ) as total_precincts,
        (SELECT COALESCE(COUNT(pv.pro_voter_id),0) FROM tbl_project_voter pv INNER JOIN tbl_voter v ON v.voter_id = pv.voter_id WHERE pv.has_photo = ? AND pv.photo_at LIKE ? AND v.municipality_no = m.municipality_no AND v.brgy_no = b.brgy_no AND v.province_code = ? AND v.elect_id = ?  AND pv.pro_id = ? ) AS total_recruits,
        (SELECT COALESCE(COUNT(pv.pro_voter_id),0) FROM tbl_project_voter pv INNER JOIN tbl_voter v ON v.voter_id = pv.voter_id WHERE pv.has_photo = ? AND pv.photo_at LIKE ? AND ( pv.photo_at >= ? AND pv.photo_at <= ? ) AND v.municipality_no = m.municipality_no AND v.brgy_no = b.brgy_no AND v.province_code = ? AND v.elect_id = ?  AND pv.pro_id = ? ) AS total_recruits_morning,
        (SELECT COALESCE(COUNT(pv.pro_voter_id),0) FROM tbl_project_voter pv INNER JOIN tbl_voter v ON v.voter_id = pv.voter_id WHERE pv.has_photo = ? AND pv.photo_at LIKE ? AND ( pv.photo_at >= ? AND pv.photo_at <= ? ) AND v.municipality_no = m.municipality_no AND v.brgy_no = b.brgy_no AND v.province_code = ? AND v.elect_id = ?  AND pv.pro_id = ? ) AS total_recruits_afternoon
      
        FROM  psw_barangay b INNER JOIN psw_municipality m ON m.municipality_code = b.municipality_code
        WHERE b.municipality_code = ? HAVING total_recruits > 0";
        
        $accessFilter  = "";

        if(!$user->getIsAdmin() && $user->getStrictAccess()){
            //$accessFilter = $this->getBarangayAccessFilter($user->getId(),$municipalityNo);
        }
      
        $sql .= $accessFilter . " ORDER BY b.name ASC";
        
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, $provinceCode);
        $stmt->bindValue(2, $electId);
        $stmt->bindValue(3, $proId);
        $stmt->bindValue(4, $provinceCode);
        $stmt->bindValue(5, $electId);
        $stmt->bindValue(6, $proId);
        
        $stmt->bindValue(7, 1);
        $stmt->bindValue(8, $photoDate->format('Y-m-d') . '%');
        $stmt->bindValue(9, $provinceCode);
        $stmt->bindValue(10, $electId);
        $stmt->bindValue(11, $proId);

        $stmt->bindValue(12, 1);
        $stmt->bindValue(13, $photoDate->format('Y-m-d') . '%');
        $stmt->bindValue(14, $photoDate->format('Y-m-d') . ' 00:00');
        $stmt->bindValue(15, $photoDate->format('Y-m-d') . ' 13:00');
        $stmt->bindValue(16, $provinceCode);
        $stmt->bindValue(17, $electId);
        $stmt->bindValue(18, $proId);
        
        $stmt->bindValue(19, 1);
        $stmt->bindValue(20, $photoDate->format('Y-m-d') . '%');
        $stmt->bindValue(21, $photoDate->format('Y-m-d') . ' 13:01');
        $stmt->bindValue(22, $photoDate->format('Y-m-d') . ' 23:59');
        $stmt->bindValue(23, $provinceCode);
        $stmt->bindValue(24, $electId);
        $stmt->bindValue(25, $proId);

        $stmt->bindValue(26, $provinceCode . $municipalityNo);
        $stmt->execute();

        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }
                
        return new JsonResponse($data);
    }

     /**
     * @Route("/ajax_datatable_organization_summary_item_detail",
     *     name="ajax_datatable_organization_summary_item_detail",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

    public function datatableOrganizationItemDetailAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
      
        $filters = array();
                
        $filters['pv.elect_id'] = $request->get("electId");
        $filters['pv.pro_id'] = $request->get('proId');

        $filters['pv.voter_name'] = $request->get("voterName");
        $filters['pv.municipality_no'] = $request->get("municipalityNo");
        $filters['pv.province_code'] = $request->get('provinceCode');
        $filters['pv.brgy_no'] = $request->get("brgyNo");
        $filters['pv.precinct_no'] = $request->get("precinctNo");
        $filters['pv.voter_group'] = $request->get('voterGroup');
        $filters['pv.has_photo'] = $request->get('hasPhoto');

        $columns = array(
            1 => 'pv.voter_name',
            2 => 'pv.voter_group',
            3 => 'b.name',
            4 => 'pv.precinct_no'
        );

        $exactArr = array(
            'pv.voter_id',
            'pv.elect_id',
            'pv.pro_id',
            'pv.has_submitted',
            'pv.has_id',
            'pv.has_ast',
            'pv.voter_group',
            'pv.status'
        );

        $optionalArr = array(
            'pv.municipality_no',
            'pv.brgy_no',
            'pv.precinct_no',
            'pv.assigned_precinct',
            'pv.province_code'
        );

        $whereStmt = " AND (";

        foreach($filters as $field => $searchText){
            
            $searchText = trim($searchText);

            if($searchText != "" && $searchText != 'undefined'){
               if(in_array($field,$exactArr)){
                    if($field == 'pv.voter_group'){
                        if($searchText == 'ALL'){
                            $whereStmt .= " (pv.voter_group IS NOT NULL AND pv.voter_group <> '') AND "; 
                        }else{
                            $groups = explode(',',str_replace(' ','',$searchText));
                           
                            if(count($groups) > 0 ){
                                $whereStmt .= ' ( ';

                                foreach($groups as $group){
                                    if(!empty($group)){
                                        $whereStmt .= "{$field} = '{$group}' OR "; 
                                    }
                                }

                                $whereStmt = substr_replace($whereStmt,"",-3);
                                $whereStmt .= ' ) AND ';
                            }
                        }
                    }elseif(is_numeric($searchText) && $searchText == 0){
                        $whereStmt .= "({$field} = '{$searchText}' OR {$field} IS NULL) AND "; 
                    }else{
                        $whereStmt .= "{$field} = '{$searchText}' AND "; 
                    }
               }elseif(in_array($field,$optionalArr)){
                    $temp = $searchText == "" ? null : "'{$searchText}  '";
                    $whereStmt .= "({$field} = '{$searchText}' OR {$temp} IS NULL) AND ";
               }else{
                    $whereStmt .= "{$field} LIKE '%{$searchText}%' AND "; 
               }
            }
        }

        $whereStmt = substr_replace($whereStmt,"",-4);

        $whereStmt  = $whereStmt . " AND pv.precinct_no <> '' AND pv.precinct_no IS NOT NULL "; 

        if($whereStmt == " A"){
            $whereStmt = "";
        }else{
            $whereStmt .= ")";
        }

        $orderStmt = "";

        if(null !== $request->query->get('order'))
            $orderStmt = $this->genOrderStmt($request,$columns);
        
        $start = 0;
        $length = 1;

        if(null !== $request->query->get('start') && null !== $request->query->get('length')){
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(pv.pro_voter_id),0) FROM tbl_project_voter pv WHERE pv.pro_id = {$filters['pv.pro_id']} ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(pv.pro_voter_id),0) FROM tbl_project_voter pv WHERE 1 ";
    
        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT pv.* FROM tbl_project_voter pv
                WHERE 1  " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while($row  = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] =  $row;
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] =  $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        $em->clear();

        return  new JsonResponse($res);
    }
    
    private function genOrderStmt($request,$columns){

        $orderStmt = "ORDER BY  ";

        for ( $i=0 ; $i<intval(count($request->query->get('order'))); $i++ )
        {
            if ( $request->query->get('columns')[$request->query->get('order')[$i]['column']]['orderable'] )
            {
                $orderStmt .= " ".$columns[$request->query->get('order')[$i]['column']]." ".
                    ($request->query->get('order')[$i]['dir']==='asc' ? 'ASC' : 'DESC') .", ";
            }
        }

        $orderStmt = substr_replace( $orderStmt, "", -2 );
        if ( $orderStmt == "ORDER BY" )
        {
            $orderStmt = "";
        }

        return $orderStmt;
    }

    /**
     * @Route("/ajax_datatable_organization_download_excel",
     *     name="ajax_datatable_organization_download_excel",
     *     options={"expose" = true}
     *     )
     * @Method("GET")
     */

    public function ajaxDatatableOrganizationDownloadExcel(Request $request){
        $user = $this->get('security.token_storage')->getToken()->getUser();
       
        $filters = array();
                
        $filters['pv.elect_id'] = $request->get("electId");
        $filters['pv.pro_id'] = $request->get('proId');

        $filters['pv.voter_name'] = $request->get("voterName");
        $filters['pv.municipality_no'] = $request->get("municipalityNo");
        $filters['pv.province_code'] = $request->get('provinceCode');
        $filters['pv.brgy_no'] = $request->get("brgyNo");
        $filters['pv.precinct_no'] = $request->get("precinctNo");
        $filters['pv.assigned_precinct'] = $request->get("assignedPrecinct");
        $filters['pv.voter_group'] = $request->get('voterGroup');
        $filters['pv.has_id'] = $request->get('hasId');
        $filters['pv.status'] = 'A';

        $columns = array(
            1 => 'pv.voter_name',
            2 => 'pv.voter_group',
            3 => 'b.name',
            4 => 'pv.precinct_no'
        );

        $exactArr = array(
            'pv.voter_id',
            'pv.elect_id',
            'pv.pro_id',
            'pv.has_submitted',
            'pv.has_id',
            'pv.has_ast',
            'pv.voter_group',
            'pv.status'
        );

        $optionalArr = array(
            'pv.municipality_no',
            'pv.brgy_no',
            'pv.precinct_no',
            'pv.assigned_precinct',
            'pv.province_code'
        );

        $whereStmt = " AND (";

        foreach($filters as $field => $searchText){
            
            $searchText = trim($searchText);

            if($searchText != "" && $searchText != 'undefined'){
               if(in_array($field,$exactArr)){
                    if($field == 'pv.voter_group'){
                        if($searchText == 'ALL'){
                            $whereStmt .= " (pv.voter_group IS NOT NULL AND pv.voter_group <> '') AND "; 
                        }else{
                            $groups = explode(',',str_replace(' ','',$searchText));
                           
                            if(count($groups) > 0 ){
                                $whereStmt .= ' ( ';

                                foreach($groups as $group){
                                    if(!empty($group)){
                                        $whereStmt .= "{$field} = '{$group}' OR "; 
                                    }
                                }

                                $whereStmt = substr_replace($whereStmt,"",-3);
                                $whereStmt .= ' ) AND ';
                            }
                        }
                    }elseif(is_numeric($searchText) && $searchText == 0){
                        $whereStmt .= "({$field} = '{$searchText}' OR {$field} IS NULL) AND "; 
                    }else{
                        $whereStmt .= "{$field} = '{$searchText}' AND "; 
                    }
               }elseif(in_array($field,$optionalArr)){
                    $temp = $searchText == "" ? null : "'{$searchText}  '";
                    $whereStmt .= "({$field} = '{$searchText}' OR {$temp} IS NULL) AND ";
               }else{
                    $whereStmt .= "{$field} LIKE '%{$searchText}%' AND "; 
               }
            }
        }

        $whereStmt = substr_replace($whereStmt,"",-4);

        if($whereStmt == " A"){
            $whereStmt = "";
        }else{
            $whereStmt .= ")";
        }

        $orderStmt = "";

        if(null !== $request->query->get('order'))
            $orderStmt = $this->genOrderStmt($request,$columns);
        
        $start = 0;
        $length = 1;

        if(null !== $request->query->get('start') && null !== $request->query->get('length')){
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(pv.voter_id),0) FROM tbl_project_voter pv WHERE pv.pro_id = {$filters['pv.pro_id']} ";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(pv.voter_id),0) FROM tbl_project_voter pv WHERE 1 AND (pv.is_10 <> 1 OR pv.is_10 IS NULL) ";
    
        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT pv.* FROM tbl_project_voter pv
                WHERE 1 AND (pv.is_10 <> 1 OR pv.is_10 IS NULL) " . $whereStmt . ' ' . $orderStmt;

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        $filename =  md5(uniqid(rand(), true)) . ".xlsx";
        $fileRoot = __DIR__.'/../../../web/uploads/exports/';

        $defaultStyle = (new StyleBuilder())
                ->setFontName('Arial')
                ->setFontSize(11)
                ->build();

        $headingStyle = (new StyleBuilder())
                ->setFontName('Arial')
                ->setFontSize(11)
                ->setFontBold()
                ->build();

        $writer =  WriterFactory::create(Type::XLSX);
        $writer->setDefaultRowStyle($defaultStyle);
        $writer->openToFile($fileRoot . $filename);
        $writer->addRowWithStyle(['KFC ID #','NAME','POSITION','MUNICIPALITY','BARANGAY','PRECINCT'],$headingStyle);

        while($row  = $stmt->fetch(\PDO::FETCH_ASSOC)){
           $writer->addRow([
               $row['pro_id_code'],
               $row['voter_name'],
               $row['voter_group'],
               $row['municipality_name'],
               $row['barangay_name'],
               $row['precinct_no']
           ]);

        }

        $writer->close();

        $response = new BinaryFileResponse($fileRoot . $filename);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }


    /**
    * @Route("/ajax_update_project_voter_summary_by_municipality/{electId}/{proId}/{provinceCode}/{municipalityNo}", 
    * 	name="ajax_update_project_voter_summary_by_municipality",
    *	options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxUpdateProjectVoterSummaryByMunicipality($electId,$proId,$provinceCode,$municipalityNo)
    {
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM psw_barangay WHERE municipality_code = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode . $municipalityNo);
        $stmt->execute();

        $barangays = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach($barangays as $barangay){
            $this->updateProjectVoterSummary($electId,$proId,$provinceCode,$municipalityNo,$barangay['brgy_no']);
        }

        return new JsonResponse(null,200);
    }

    /**
    * @Route("/ajax_update_project_voter_summary/{electId}/{proId}/{provinceCode}/{municipalityNo}/{brgyNo}", 
    * 	name="ajax_update_project_voter_summary",
    *	options={"expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxUpdateProjectVoterSummary($electId,$proId,$provinceCode,$municipalityNo,$brgyNo)
    {
        $this->updateProjectVoterSummary($electId,$proId,$provinceCode,$municipalityNo,$brgyNo);
        
        return new JsonResponse([ 'message' => 'done' ],200);
    }

    private function updateProjectVoterSummary($electId,$proId,$provinceCode,$municipalityNo,$brgyNo){

        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $currentDate = date('Y-m-d');

        $sql = "SELECT * FROM tbl_project WHERE pro_id = ? ";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$proId);
        $stmt->execute();

        $project = $stmt->fetch(\PDO::FETCH_ASSOC);

        if($project == null){
            return new JsonResponse('not found');
        }

        $sql = "DELETE FROM tbl_project_voter_summary 
                WHERE province_code = ? 
                AND municipality_no = ? 
                AND brgy_no = ? 
                AND elect_id = ? 
                AND pro_id = ? 
                AND created_at = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->bindValue(3,$brgyNo);
        $stmt->bindValue(4,$electId);
        $stmt->bindValue(5,$project['pro_id']);
        $stmt->bindValue(6,$currentDate);
        $stmt->execute();

        $sql = "SELECT DISTINCT brgy_no,precinct_no
                FROM tbl_project_voter 
                WHERE province_code = ? AND municipality_no = ? 
                AND brgy_no = ? AND elect_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->bindValue(3,$brgyNo);
        $stmt->bindValue(4,$electId);
    
        $stmt->execute();
        $data = [];
        
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $data[] = $row;
        }

        foreach($data as $row){

            $sql = "SELECT 
            
            COALESCE(COUNT(CASE WHEN pv.voter_group <> 'DAO' then 1 end), 0) as total_member,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'LGC' then 1 end), 0) as total_level_1,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'LOPP' then 1 end), 0) as total_level_2,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP' then 1 end), 0) as total_level_3,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP1' then 1 end), 0) as total_level_4,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP2' then 1 end), 0) as total_level_5,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP3' then 1 end), 0) as total_level_6,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'JPM' then 1 end), 0) as total_level_7,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'KCL5' then 1 end), 0) as total_level_8,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'DAO' then 1 end), 0) as total_staff,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'KFC' then 1 end), 0) as total_others,
            COALESCE(COUNT(CASE WHEN (pv.cellphone IS NOT NULL AND pv.cellphone <> '') AND pv.voter_group <> 'DAO' then 1 end), 0) as total_has_cellphone,

            COALESCE(COUNT(CASE WHEN pv.has_photo = 1 then 1 end), 0) as total_with_id_member,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'LGC' AND pv.has_photo  = 1 THEN 1 END), 0) AS total_with_id_level_1,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'LOPP' AND pv.has_photo = 1 THEN 1 END), 0) AS total_with_id_level_2,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP' AND pv.has_photo = 1 THEN 1 END), 0) AS total_with_id_level_3,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP1' AND pv.has_photo = 1 THEN 1 END), 0) AS total_with_id_level_4,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP2' AND pv.has_photo = 1 THEN 1 END), 0) AS total_with_id_level_5,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'LPPP3' AND pv.has_photo = 1 THEN 1 END), 0) AS total_with_id_level_6,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'JPM' AND pv.has_photo = 1 THEN 1 END), 0) AS total_with_id_level_7,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'KCL5' AND pv.has_photo = 1 THEN 1 END), 0) AS total_with_id_level_8,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'DAO' AND pv.has_photo = 1 THEN 1 END), 0) AS total_with_id_staff,
            COALESCE(COUNT(CASE WHEN pv.voter_group = 'KFC' AND pv.has_photo = 1 THEN 1 END), 0) AS total_with_id_others,
            COALESCE(COUNT(CASE WHEN (pv.cellphone IS NOT NULL AND pv.cellphone <> '') AND pv.voter_group <> 'DAO' AND pv.has_photo  = 1 then 1 end), 0) as total_with_id_cellphone

            FROM tbl_project_voter pv 
            WHERE (pv.voter_group IS NOT NULL AND pv.voter_group <> '') AND pv.province_code = ? AND pv.municipality_no = ? 
            AND pv.brgy_no= ? AND pv.precinct_no = ? AND pv.elect_id = ? AND pv.pro_id = ? 
            AND pv.voter_group IN ('LGC','LOPP','LPPP','LPPP1','LPPP2','LPPP3','JPM') AND pv.precinct_no <> '' AND pv.precinct_no IS NOT NULL ";
            
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1,$provinceCode);
            $stmt->bindValue(2,$municipalityNo);
            $stmt->bindValue(3,$brgyNo);
            $stmt->bindValue(4,$row['precinct_no']);
            $stmt->bindValue(5,$electId);
            $stmt->bindValue(6,$proId);
            $stmt->execute();
            
            $summary = $stmt->fetch(\PDO::FETCH_ASSOC);

            $sql = "SELECT 
                    COUNT(pv.pro_voter_id) as total_voter 
                    FROM tbl_project_voter pv
                    WHERE pv.province_code = ? AND pv.municipality_no = ? 
                    AND pv.brgy_no= ? AND pv.precinct_no = ? AND pv.elect_id = ? 
                    AND pv.pro_id = ? AND pv.precinct_no <> '' AND pv.precinct_no IS NOT NULL ";
            
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1,$provinceCode);
            $stmt->bindValue(2,$municipalityNo);
            $stmt->bindValue(3,$brgyNo);
            $stmt->bindValue(4,$row['precinct_no']);
            $stmt->bindValue(5,$electId);
            $stmt->bindValue(6,$proId);
            $stmt->execute();
            
            $totalVoter = $stmt->fetchColumn();

            $entity = new ProjectVoterSummary();
            $entity->setProvinceCode($provinceCode);
            $entity->setMunicipalityNo($municipalityNo);
            $entity->setBrgyNo($brgyNo);
            $entity->setPrecinctNo($row['precinct_no']);
            $entity->setElectId($electId);
            $entity->setProId($proId);
            $entity->setTotalMember($summary['total_member']);
            $entity->setTotalOthers($totalVoter);
            $entity->setTotalHasCellphone($summary['total_has_cellphone']);
            $entity->setTotalLevel1($summary['total_level_1']);
            $entity->setTotalLevel2($summary['total_level_2']);
            $entity->setTotalLevel3($summary['total_level_3']);
            $entity->setTotalLevel4($summary['total_level_4']);
            $entity->setTotalLevel5($summary['total_level_5']);
            $entity->setTotalLevel6($summary['total_level_6']);
            $entity->setTotalLevel7($summary['total_level_7']);
            $entity->setTotalLevel8($summary['total_level_8']);
            $entity->setTotalStaff($summary['total_staff']);

            $entity->setTotalWithIdLevel1($summary['total_with_id_level_1']);
            $entity->setTotalWithIdLevel2($summary['total_with_id_level_2']);
            $entity->setTotalWithIdLevel3($summary['total_with_id_level_3']);
            $entity->setTotalWithIdLevel4($summary['total_with_id_level_4']);
            $entity->setTotalWithIdLevel5($summary['total_with_id_level_5']);
            $entity->setTotalWithIdLevel6($summary['total_with_id_level_6']);
            $entity->setTotalWithIdLevel7($summary['total_with_id_level_7']);
            $entity->setTotalWithIdLevel8($summary['total_with_id_level_8']);
            $entity->setTotalWithIdStaff($summary['total_with_id_staff']);
            $entity->setTotalWithIdOthers($summary['total_with_id_others']);
            $entity->setTotalWithIdMember($summary['total_with_id_member']);
            $entity->setTotalWithIdCellphone($summary['total_with_id_cellphone']);

            $entity->setCreatedAt(new \DateTime($currentDate));
            $entity->setCreatedBy($user->getUsername());

            $em->persist($entity);
            $em->flush();
        }
    }


    /**
    * @Route("/ajax_patch_barangay_status", 
    * 	name="ajax_patch_barangay_status",
    *	options={"expose" = true}
    * )
    * @Method("PATCH")
    */

    public function patchBarangayStatus(Request $request){
        $em = $this->getDoctrine()->getManager();

        $municipalityCode = $request->get("municipalityCode");
        $brgyNo = $request->get('brgyNo');
        $isFavorite = $request->get("isFavorite");

        if($this->isTogglable($isFavorite)){
            $isFavorite = (bool)$isFavorite;

            $sql = "UPDATE psw_barangay SET is_favorite = ? WHERE municipality_code = ? AND brgy_no = ? ";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue(1,$isFavorite);
            $stmt->bindValue(2,$municipalityCode);
            $stmt->bindValue(3,$brgyNo);
            $stmt->execute();
        }

        return new JsonResponse([
            "municipalityCode" => $municipalityCode,
            "brgyNo" => $brgyNo,
            "isFavorite" => $isFavorite
        ]);
    }
    
    private function isTogglable($value){
        return $value != null && $value != "" && ($value == 0 ||  $value == 1);
    }

}
