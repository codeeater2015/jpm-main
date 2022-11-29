<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
* @Route("/report")
*/

class ReportController extends Controller 
{

    /**
     * @Route("/organization_summary", name="organization_summary_report", options={"main" = true })
     */

    public function organizationSummaryAction(Request $request)
    {
        $link = $this->getParameter('report_org_summary');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

     /**
     * @Route("/municipality-org-sum", name="report_municipality_summary", options={"main" = true })
     */

    public function municipalitySummaryAction(Request $request)
    {
        $link = $this->getParameter('report_orgnization_summary_municipality');
         $hostIp = $this->getParameter('jasper_ip');


           $iframe_url = "http://" .  $hostIp . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }


    /**
     * @Route("/province-org-sum", name="report_province_summary", options={"main" = true })
     */

    public function provinceSummaryAction(Request $request)
    {
        $link = $this->getParameter('report_orgnization_summary_province');
         $hostIp = $this->getParameter('jasper_ip');

            $iframe_url = "http://" .  $hostIp . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

    /**
     * @Route("/organization_summary_per_barangay", name="organization_summary_per_barangay_report", options={"main" = true })
     */

    public function organizationSummaryPerBarangayAction(Request $request)
    {
        $link = $this->getParameter('report_org_summary_per_barangay');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

     /**
     * @Route("/lgc_list", name="lgc_list_report", options={"main" = true })
     */

    public function lgcListAction(Request $request)
    {
        $link = $this->getParameter('report_lgc_list');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

    /**
     * @Route("/member_status_form", name="report_member_status_form_report", options={"main" = true })
     */

    public function memberStatusFormAction(Request $request)
    {
        $link = $this->getParameter('report_member_status_form');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }
    
    /**
     * @Route("/member_contact_list", name="member_contact_list_report", options={"main" = true })
     */

    public function memberContactListAction(Request $request)
    {
        $link = $this->getParameter('report_member_contact_list');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

    /**
     * @Route("/household-encoding-summary", name="household_encoding_summary_report", options={"main" = true })
     */

    public function householdEncodingSummaryAction(Request $request)
    {
        $link = $this->getParameter('report_household_encoding_summary');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

     /**
     * @Route("/recruitment-encoding-summary", name="recruitment_encoding_summary_report", options={"main" = true })
     */

    public function recruitmentEncodingSummaryAction(Request $request)
    {
        $link = $this->getParameter('report_recruitment_encoding_summary');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

    /**
     * @Route("/barangay-total-precincts", name="barangay_total_precincts_report", options={"main" = true })
     */

    public function barangayTotalPrecinctsAction(Request $request)
    {
        $link = $this->getParameter('report_barangay_total_precinct');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

    /**
     * @Route("/provincial-summary", name="provincial_summary_report", options={"main" = true })
     */

    public function provincialSummaryAction(Request $request)
    {
        $link = $this->getParameter('report_provincial_summary');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

    /**
     * @Route("/municipal-summary", name="municipal_summary_report", options={"main" = true })
     */

    public function municipalSummaryAction(Request $request)
    {
        $link = $this->getParameter('report_municipal_summary');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

     /**
     * @Route("/non-voter-list", name="non_voter_list_report", options={"main" = true })
     */

    public function nonVoterListAction(Request $request)
    {
        $link = $this->getParameter('report_non_voter_list');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

     /**
     * @Route("/member-status-by-lgc", name="member_status_by_lgc_report", options={"main" = true })
     */

    public function memberStatusByLgcAction(Request $request)
    {
        $link = $this->getParameter('report_member_status_by_lgc');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

    
     /**
     * @Route("/member-status-by-municipality", name="member_status_by_municipality_report", options={"main" = true })
     */

    public function memberStatusByMunicipalityAction(Request $request)
    {
        $link = $this->getParameter('report_member_status_by_municipality');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

     /**
     * @Route("/lgc-pjm-summary", name="lgc_pjm_summary_report", options={"main" = true })
     */

    public function lgcPjmSummaryAction(Request $request)
    {
        $link = $this->getParameter('report_lgc_pjm_summary');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

    
     /**
     * @Route("/lppp1-summary-by-municipality", name="lppp1_summary_by_municipality", options={"main" = true })
     */

    public function lppp1SummaryByMunicipality(Request $request)
    {
        $link = $this->getParameter('report_lppp1_summary_by_municipality');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

     /**
     * @Route("/upload-summary-by-municipality", name="Upload_Summary_by_Municipality", options={"main" = true })
     */

    public function uploadSummaryByMunicipality(Request $request)
    {
        $link = $this->getParameter('report_upload_summary_by_municipality');
        $hostIp = $this->getParameter('jasper_ip');

        $iframe_url = "http://" .  $hostIp . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

     /**
     * @Route("/upload-summary-by-province", name="Upload_Summary_By_Province", options={"main" = true })
     */

    public function uploadSummaryByProvince(Request $request)
    {
        $link = $this->getParameter('report_upload_summary_by_province');
        $hostIp = $this->getParameter('jasper_ip');

        $iframe_url = "http://" .  $hostIp . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

    /**
     * @Route("/vlist-by-barangay", name="vlist_by_barangay", options={"main" = true })
     */

    public function uploadVoterslistByBarangay(Request $request)
    {
        $link = $this->getParameter('vlist_by_barangay');
        $hostIp = $this->getParameter('jasper_ip');

        $iframe_url = "http://" .  $hostIp . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }
 
     /**
     * @Route("/financial-assistance-daily-summary-report", name="financial_assistance_daily_summary_report", options={"main" = true })
     */

    public function financialAssistanceDailySummaryReportAction(Request $request)
    {
        $link = $this->getParameter('report_fa_daily_summary');
        $hostIp = $this->getParameter('host_ip');

        $iframe_url = "http://" .  $hostIp . ":8080" . $link;

        return $this->render('template/reports/index.html.twig',['iframe_url' => $iframe_url]);
    }

}
