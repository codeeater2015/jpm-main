<?php

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("notification")
 * Class NotificationController
 * @package AppBundle\Controller
 */
class NotificationController extends Controller
{
    public function suspensionNotificationAction(){
        $rcenters = $this->getUserRcenter();
        $em_pgpis = $this->getDoctrine()->getManager('pgpis');

        $conn = $em_pgpis->getConnection();
        $stmt = $conn->executeQuery("SELECT hdr.dv_date,hdr.dv_code,ohdr.obr_date,ohdr.obr_desc,ohdr.rc_code,hdr.dv_desc,hdr.dv_name,hdr.suspension_date,hdr.suspension_reason, 
                  hdr.suspension_by, hdr.suspension_resolved
                FROM ACC_DV_HDR hdr
                INNER JOIN ACC_GF_DV_DTL dtl ON dtl.dv_code = hdr.dv_code
                INNER JOIN ACC_GF_RECEIVING_HDR rhdr ON rhdr.recv_code = dtl.recv_code
                INNER JOIN BGT_OBLIGATION_HDR ohdr ON ohdr.obr_code = rhdr.obr_code
                WHERE hdr.status = 'SUS'
                AND ohdr.obr_status > 0
                AND ohdr.rc_code IN ($rcenters)
                ORDER BY hdr.suspension_date DESC",
            array());
        $suspensions = $stmt->fetchAll();

        return $this->render('tpl/_notification.html.twig',[
            "suspensions" => $suspensions
        ]);
    }

    /**
     * @Route("/load-suspension-details", name="load_suspension_details", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loadSuspensionDetailsAction(Request $request){

        $dv_code = $request->request->get('id');
        $em_pgpis = $this->getDoctrine()->getManager('pgpis');

        $conn = $em_pgpis->getConnection();
        $stmt = $conn->executeQuery("SELECT hdr.dv_code,ohdr.obr_date,ohdr.obr_desc,ohdr.rc_code,ohdr.obr_refnum_type,
                ohdr.obr_remarks,ohdr.total_amount,p.payee_desc as obr_name,hdr.dv_date,hdr.dv_name,hdr.remarks as dv_remarks,
                hdr.dv_desc,hdr.current_amount as dv_amount,hdr.suspension_date,hdr.suspension_reason,hdr.suspension_by, hdr.suspension_resolved
                FROM ACC_DV_HDR hdr
                INNER JOIN ACC_GF_DV_DTL dtl ON dtl.dv_code = hdr.dv_code
                INNER JOIN ACC_GF_RECEIVING_HDR rhdr ON rhdr.recv_code = dtl.recv_code
                INNER JOIN BGT_OBLIGATION_HDR ohdr ON ohdr.obr_code = rhdr.obr_code
                INNER JOIN PGP_PAYEE p ON p.payee_code = ohdr.payee_code
                WHERE hdr.dv_code = ?",array($dv_code));

        $suspension = $stmt->fetch();

        return $this->render("tpl/_notification_view_details.html.twig",[
            "suspension" => $suspension
        ]);
    }

    public function getUserRcenter(){

        $rcenter = $this->getUser()->getRcenter();
        $rcenters = array();
        foreach($rcenter as $row){
            $rcenters[] = "'".$row->getRcCode()."'";
        }
        $rcenters = (count($rcenters) > 0) ? implode(",", $rcenters) : "''";

        return $rcenters;
    }
}