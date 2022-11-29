<?php
namespace AppBundle\Controller;

use function GuzzleHttp\default_ca_bundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\ReceivedSms;
use AppBundle\Entity\SendSms;
use AppBundle\Entity\SmsTemplate;

/**
 * @Route("/sms")
 * Class SmsController
 * @package AppBundle\Controller
 */

class SmsController extends Controller 
{   
    const STATUS_ACTIVE = 'A';
    const MODULE_MAIN = "SMS_MODULE";

    /**
    * @Route("", name="sms_index", options={"main" = true})
    */

    public function indexAction(Request $request)
    {
        //$this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->render('template/sms/index.html.twig',[ 'user' => $user ]);
    }
    
     /**
     * @Route("/send-greeting", name="send_greeting",options={"expose"=true})
     */

    public function actionSendGreeting(){
        $em = $this->getDoctrine()->getManager();

        $bdate = date('m-d');

        $sql = "SELECT * FROM tbl_project_voter WHERE elect_id = ? AND pro_id = ? AND voter_group IN ('CH','KCL','KCL0','KCL1','KCL2','KCL3','KFC','DAO','KJR') AND birthdate LIKE ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1, 3);
        $stmt->bindValue(2, 2);
        $stmt->bindValue(3, "%" . $bdate . '%');
        $stmt->execute();

        
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){

            $message = "Maligayang Kaarawan Kabarangay " .  $row['voter_firstname'] .'!!!' . PHP_EOL;
            $message .= "" . PHP_EOL;
            $message .= "Greetings From:" . PHP_EOL . "KABARANGAY ATTY. GIL ACOSTA JR. and Family" . PHP_EOL;
            $message .= "Do not reply.";

            if(preg_match("/^(09)\\d{9}/",$row['cellphone'])){

                $sms = new SendSms();
                $sms->setMessageText($message);
                $sms->setMessageTo($row['cellphone']);
                $sms->setMessageFrom('Kabarangay for Change');

                $em->persist($sms);
            }
        }

        $em->flush();
        $em->clear();

        return new JsonResponse(['message' => 'ok'],200);
    }

    /**
     * @Route("/ajax_get_received_sms",
     *     name="ajax_get_received_sms",
     *     options={"expose" = true})
     *
     * @Method("GET")
     */
    
    public function ajaxGetReceivedSmsAction(Request $request){

        $filters = [];
        $filters['r.MessageFrom'] = $request->get("messageFrom");

        $columns = [
            0 => 'r.Id',
            1 => 'r.MessageFrom'
        ];
        
        $whereStmt = " AND (";

        foreach($filters as $field => $searchText)
        {
            if($searchText != "")
            {
                $whereStmt .= "{$field} LIKE '%$searchText%' OR ";
            }
        }

        $whereStmt = substr_replace($whereStmt,"",-4);

        if($whereStmt == " A"){
            $whereStmt = "";
        }else{
            $whereStmt .= ")";
        }

        $orderStmt = " ORDER BY r.SendTime DESC";

        $start = 1;
        $length = 1;

        if(null !== $request->query->get('start') && null !== $request->query->get('length')){
            $start = intval($request->query->get('start'));
            $length = intval($request->query->get('length'));
        }
       
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        $sql = "SELECT COALESCE(count(r.Id),0) FROM tbl_received_sms r";
        $stmt = $em->getConnection()->query($sql);
        $recordsTotal = $stmt->fetchColumn();

        $sql = "SELECT COALESCE(COUNT(r.Id),0) FROM tbl_received_sms r WHERE 1=1 ";

        $sql .= $whereStmt . ' ' . $orderStmt;
        $stmt = $em->getConnection()->query($sql);
        $recordsFiltered = $stmt->fetchColumn();

        $sql = "SELECT r.*
                FROM tbl_received_sms r WHERE 1=1 " . $whereStmt . ' ' . $orderStmt . " LIMIT {$length} OFFSET {$start} ";

        $stmt = $em->getConnection()->query($sql);
        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $row['MessageFrom'] = str_replace('+63','0',$row['MessageFrom']);
            $data[] = $row; 
        }

        $draw = (null !== $request->query->get('draw')) ? $request->query->get('draw') : 0;
        $res['data'] =  $data;
        $res['recordsTotal'] = $recordsTotal;
        $res['recordsFiltered'] = $recordsFiltered;
        $res['draw'] = $draw;

        $em->clear();

        return  new JsonResponse($res);
    }

    private function getMember($proId,$cellphone){
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM tbl_project_voter pv WHERE pv.cellphone = ? AND pv.pro_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$cellphone);
        $stmt->bindValue(2,$proId);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if($row){
            return $row;
        }else{
            return [
                "voter_name" => '- - - -',
                "cellphone" => '- - - - ',
                "voter_id" => null,
                "pro_voter_id" => null,
                'voter_group' => '- - - -',
                'barangay_name' => '- - - -',
                'municipality_name' => '- - - -'
            ];
        }
    }
    
    /**
     * @Route("/ajax_reply_sms/{proVoterId}/{proId}",
     *       name="ajax_reply_sms",
     *       options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostMessageSingle(Request $request,$proVoterId,$proId){
        $em = $this->getDoctrine()->getManager();
        
        $sql = "SELECT * FROM tbl_project_voter pv WHERE pv.pro_voter_id = ? AND pv.pro_id = ? ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$proVoterId);
        $stmt->bindValue(2,$proId);
        $stmt->execute();

        $voter = $stmt->fetch(\PDO::FETCH_ASSOC);
        if(!$voter)
            return new JsonResponse(null,404);

        $messageText =  $request->get('messageBody') ;

        if(empty($messageText))
            return new JsonResponse([
                'messageBody' => 'Message body cannot be empty...'
            ],400);


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
            
        $messageText = strtr($messageText , $transArr);

        if($voter){
            if(preg_match("/^(09)\\d{9}/",$voter['cellphone'])){
                $msgEntity = new SendSms();
                $msgEntity->setMessageText($messageText);
                $msgEntity->setMessageTo($voter['cellphone']);
                $em->persist($msgEntity);
                $em->flush();
            }
        }

        $em->clear();

        return new JsonResponse(['message' => 'ok']);
    }

     /**
     * @Route("/ajax_post_sms_template",
     *       name="ajax_post_sms_template",
     *       options={ "expose" = true }
     * )
     * @Method("POST")
     */

    public function ajaxPostSmsTemplate(Request $request){
     
        $entity = new SmsTemplate();

        $entity->setTemplateName($request->get("templateName"));
        $entity->setTemplateContent($request->get("templateContent"));
        $entity->setRemarks($request->get('remarks'));
    	$entity->setStatus(self::STATUS_ACTIVE);

    	$validator = $this->get('validator');
        $violations = $validator->validate($entity);

        $errors = [];

        if(count($violations) > 0){
            foreach( $violations as $violation ){
                $errors[$violation->getPropertyPath()] =  $violation->getMessage();
            }
            return new JsonResponse($errors,400);
        }
        
        $em = $this->getDoctrine()->getManager();
        $em->persist($entity);
        $em->flush();
    	$em->clear();

    	$serializer = $this->get('serializer');

    	return new JsonResponse($serializer->normalize($entity));
    }


    /**
     * @Route("/ajax_get_sms_template",
     *       name="ajax_get_sms_template",
     *       options={ "expose" = true }
     * )
     * @Method("GET")
     */

    public function ajaxGetSmsTemplates(){
        $em = $this->getDoctrine()->getManager();
        
        $templates = $em->getRepository("AppBundle:SmsTemplate")->findAll();

        if(count($templates) <= 0){
            $templates = [];
        }

        $serializer = $this->get("serializer");

        return new JsonResponse($serializer->normalize($templates));
    }

    /**
     * @Route("/http-callback", name="sms_http_callback")
     * @param Request $request
     * @return Response
     */
    public function httpCallbackAction(Request $request){
        $action = ($request->query->has("action")) ? $request->query->get("action") : null;
        
        switch($action){
            case "message_in":
                return $this->messageIn($request);
                break;
        }

        $response = new Response("result=1");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->headers->set("Content-Length", 8);

        return $response;
    }

    public function messageIn($request){
        $now = new \DateTime();

        $source = ($request->query->has('source')) ? $request->query->get('source') : null;
        $send_time = ($request->query->has('send_time')) ?  new \DateTime($request->query->get('send_time')) : $now;
        $receive_time = ($request->query->has('receive_time')) ?  new \DateTime($request->query->get('recevied_time')) : $now;
        $sms_central = ($request->query->has('sms_central')) ? $request->query->get('sms_central') : null;
        $from = ($request->query->has('from')) ? $request->query->get('from') : null;
        $to = ($request->query->has('to')) ? $request->query->get('to') : null;
        $message = ($request->query->has('message')) ? $request->query->get('message') : null;
        $message_type = ($request->query->has('message_type')) ? $request->query->get('message_type') : null;
        $message_part = ($request->query->has('message_part')) ? $request->query->get('message_part') : null;
        $message_parts_received = ($request->query->has('message_parts_received')) ? $request->query->get('message_parts_received') : null;
        $message_parts_total = ($request->query->has('message_parts_total')) ? $request->query->get('message_parts_total') : null;
        $pdu = ($request->query->has('pdu')) ? $request->query->get('pdu') : 0;
        $gateway = ($request->query->has('gateway')) ? $request->query->get('gateway') : null;
        $user_id = ($request->query->has('user_id')) ? $request->query->get('user_id') : null;
        $tlv_list = ($request->query->has('tlv_list')) ? $request->query->get('tlv_list') : null;

        $params = explode(" ", strtoupper($message),2);
        $action = $params[0];
        $data = "";

        if($action == 'INFO'){
            $this->_handleIdInquiry($from,$gateway,$params);
        }elseif($action == 'JPM'){
            $this->_handleJpmInquiry($from,$gateway,$params);
        }elseif($action == 'TBBRGY'){
            $params = explode(" ", strtoupper($message),5);
            $this->_handleTextBlastByBarangay($from,$gateway,$params);
        }elseif($action == 'TBMUN'){
            $params = explode(" ", strtoupper($message),5);
            $this->_handleTextBlastByMunicipality($from,$gateway,$params);
        }

        $response = new Response("result=1");
        $response->headers->set("X-Accel-Buffering", "no");
        $response->headers->set("Content-Length", 8);

        return $response;
    }

    private function _handleIdInquiry($from,$gateway,$message_arr){
        $em = $this->getDoctrine()->getManager();
        $proIdCode = trim(strtoupper($message_arr[1]));

        $entity = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => 3,
            'proIdCode' => $proIdCode,
            'electId' => 4
        ]);

        if(!$entity){
            $message = "Magandang araw po. Hindi po namin makita ang iyong id sa aming system. Please kindly contact your leader. Maraming salamat po.";
        }else{
            $message = "Kapamilyang JPM " . $entity->getVoterName() . PHP_EOL;
            $message .=  $entity->getBarangayName() . ', ' . $entity->getMunicipalityName() . PHP_EOL;
            $message .= "Precinct No:" . $entity->getPrecinctNo() . PHP_EOL;
            $message .= "Voter No:" . $entity->getVoterNo() . PHP_EOL;
            $message .= "Salamat sa iyong paniniwala sa adhikain ng ating butihing Governor JCA." . PHP_EOL;
            $message .= "Kayo ang totoong pamilya po namin." . PHP_EOL;
        }

        // $message .=  "Name:" . $entity->getVoterName() . PHP_EOL;
        // $message .= "Addr:" . $entity->getBarangayName() . PHP_EOL;
        // $message .= "Voter No:" . $entity->getVoterNo() .  PHP_EOL;
        // $message .= "Prec:" . $entity->getPrecinctNo() . PHP_EOL;
        // $message .= "CPrec:"  . $entity->getClusteredPrecinct() . PHP_EOL;
        // $message .= "Vote Place:" . $entity->getVotingCenter() . PHP_EOL;
      
        $message .= 'System generated.Please do not reply.';
            
        $sms = new SendSms();
        $sms->setMessageText($message);
        $sms->setMessageTo($from);
        $sms->setMessageFrom("JPM");

        $em->persist($sms);
        $em->flush();
    }

    private function _handleTextBlastByBarangay($from,$gateway,$message_arr){
        $em = $this->getDoctrine()->getManager();
        $proIdCode = trim(strtoupper($message_arr[1]));

        $municipalityName = trim(strtoupper($message_arr[1]));
        $barangayName = trim (strtoupper($message_arr[2]));
        $voterGroup  = trim(strtoupper($message_arr[3]));
        $message = trim(strtoupper($message_arr[4]));

        $sql = "SELECT * FROM tbl_project_voter pv 
                WHERE pv.elect_id = 4 AND pv.pro_id = 3 
                AND pv.province_code = 53 
                AND pv.municipality_name_alt = '{$municipalityName}' 
                AND pv.barangay_name = '{$barangayName}' ";

        if($voterGroup != 'ALL'){
            $sql .= " AND pv.voter_group = '{$voterGroup}' ";
        }else{
            $sql .= " AND pv.voter_group IN ('LGC','LOPP','LPPP','LPPP1','LPPP2','LPPP3') ";
        }

        $sql .= " AND cellphone IS NOT NULL AND cellphone <> '' ORDER BY pv.voter_name ASC ";

        $stmt = $em->getConnection()->query($sql);

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $messageText = $message;

            $name2 = strtolower($row['firstname']) . ' ' . strtolower($row['middlename']) . ' ' . strtolower($row['lastname']) . strtolower($row['ext_name']);

            $south = ['04','05','06','15','17','21','23','24'];
            $north = ['02','03','07','08','09','10','11','12','13','14','18','19','20','22'];
            $mid = ['01'];

            $lineup = "";

            if(in_array($row['municipality_no'], $south) ){
                $lineup .= "Congressman : ALVAREZ, JCA" . PHP_EOL;
                $lineup .= "Governor : SOCRATES, DENNIS " . PHP_EOL;
                $lineup .= "Vice-Governor : OLA, ONSOY" . PHP_EOL;
                $lineup .= "Board Members : " . PHP_EOL;
                $lineup .= "MAMINTA, RYAN" . PHP_EOL;
                $lineup .= "IBBA, AL" . PHP_EOL;
                $lineup .= "ROXAS, MARIVIC" . PHP_EOL;
                $lineup .= "ARZAGA, ARIS" . PHP_EOL;
            }elseif(in_array($row['municipality_no'], $north)){
                $lineup .= "Congressman : ALVAREZ, ACA" . PHP_EOL;
                $lineup .= "Governor : SOCRATES, DENNIS " . PHP_EOL;
                $lineup .= "Vice-Governor : OLA, ONSOY" . PHP_EOL;
                $lineup .= "Board Members : " . PHP_EOL;
                $lineup .= "ALVAREZ, ANTON" . PHP_EOL;
                $lineup .= "PINEDA, TOTO" . PHP_EOL;
                $lineup .= "SABANDO, MARIA ANGELA" . PHP_EOL;
                $lineup .= "FORTES, JULIUS CEASAR" . PHP_EOL;
            }elseif(in_array($row['municipality_no'], $mid)){
                $lineup .= "Congressman : ACOSTA, GIL JR." . PHP_EOL;
                $lineup .= "Governor : SOCRATES, DENNIS " . PHP_EOL;
                $lineup .= "Vice-Governor : OLA, ONSOY" . PHP_EOL;
            }

            $transArr = array(
                '{NAME1}' => ucwords(strtolower($row['voter_name'])),
                '{NAME2}' => ucwords($name2),
                '{NAME3}' => ucwords(strtolower($row['firstname'])),
                '{PRECINCTNO}' => $row['precinct_no'],
                '{BRGY}' => $row['barangay_name'],
                '{MUN}' => $row['municipality_name'],
                '{VOTERNO}' => $row['voter_no'],
                '{POS}' => $row['voter_group'],
                '{LINEUP}' => $lineup
            );

            $messageText = strtr($messageText, $transArr);
            $messageText .= 'System generated.Please do not reply.';
        
            $sms = new SendSms();
            $sms->setMessageText($messageText);
            $sms->setMessageTo($row['cellphone']);
            $sms->setMessageFrom("JPM");
    
            $em->persist($sms);
            $em->flush();
        }

    }

    private function _handleJpmInquiry($from,$gateway,$message_arr){
        $em = $this->getDoctrine()->getManager();
        $proIdCode = trim(strtoupper($message_arr[1]));

        $entity = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => 3,
            'proIdCode' => $proIdCode,
            'electId' => 4
        ]);

        
        if(!$entity){
            $message = "Magandang araw po. Hindi po namin makita ang iyong id sa aming system. Please kindly contact your leader. Maraming salamat po.";
        }else{
            $message = "Kapamilyang JPM " . $entity->getVoterName() . PHP_EOL;
            $message .=  $entity->getBarangayName() . ', ' . $entity->getMunicipalityName() . PHP_EOL;
            $message .= "Precinct No:" . $entity->getPrecinctNo() . PHP_EOL;
            $message .= "Voter No:" . $entity->getVoterNo() . PHP_EOL;
            
            $south = ['04','05','06','15','17','21','23','24'];
            $north = ['02','03','07','08','09','10','11','12','13','14','18','19','20','22'];
            $mid = ['01'];

            if(in_array($entity->getMunicipalityNo(), $south) ){
                $message .= "Congressman : ALVAREZ, JCA" . PHP_EOL;
                $message .= "Governor : SOCRATES, DENNIS " . PHP_EOL;
                $message .= "Vice-Governor : OLA, ONSOY" . PHP_EOL;
                $message .= "Board Members : " . PHP_EOL;
                $message .= "MAMINTA, RYAN" . PHP_EOL;
                $message .= "IBBA, AL" . PHP_EOL;
                $message .= "ROXAS, MARIVIC" . PHP_EOL;
                $message .= "ARZAGA, ARIS" . PHP_EOL;
            }elseif(in_array($entity->getMunicipalityNo(), $north)){
                $message .= "Congressman : ALVAREZ, ACA" . PHP_EOL;
                $message .= "Governor : SOCRATES, DENNIS " . PHP_EOL;
                $message .= "Vice-Governor : OLA, ONSOY" . PHP_EOL;
                $message .= "Board Members : " . PHP_EOL;
                $message .= "ALVAREZ, ANTON" . PHP_EOL;
                $message .= "PINEDA, TOTO" . PHP_EOL;
                $message .= "SABANDO, MARIA ANGELA" . PHP_EOL;
                $message .= "FORTES, JULIUS CEASAR" . PHP_EOL;
            }elseif(in_array($entity->getMunicipalityNo(), $mid)){
                $message .= "Congressman : ACOSTA, GIL JR." . PHP_EOL;
                $message .= "Governor : SOCRATES, DENNIS " . PHP_EOL;
                $message .= "Vice-Governor : OLA, ONSOY" . PHP_EOL;
            }
        }

        $message .= 'System generated.Please do not reply.';
            
        $sms = new SendSms();
        $sms->setMessageText($message);
        $sms->setMessageTo($from);
        $sms->setMessageFrom("JPM");

        $em->persist($sms);
        $em->flush();
    }

    private function _handleNameInquiry($from,$gateway,$message_arr){
        $em = $this->getDoctrine()->getManager();
        $voterName = trim(strtoupper($message_arr[1]));

        $entity = $em->getRepository("AppBundle:ProjectVoter")->findOneBy([
            'proId' => 2,
            'voterName' => $voterName,
            'electId' => 3
        ]);

        if($entity){
          
            $message =  "Name:" . $entity->getVoterName() . PHP_EOL;
            $message .= "Addr:" . $entity->getBarangayName() . PHP_EOL;
            $message .= "Voter No:" . $entity->getVoterNo() .  PHP_EOL;
            $message .= "Prec:" . $entity->getPrecinctNo() . PHP_EOL;
            $message .= "CPrec:"  . $entity->getClusteredPrecinct() . PHP_EOL;
            $message .= "Vote Place:" . $entity->getVotingCenter() . PHP_EOL;
            $message .= 'Do not reply.';
        
            $sms = new SendSms();
            $sms->setMessageText($message);
            $sms->setMessageTo($from);
            $sms->setMessageFrom("Kabarangay for Change");

        }else{

           $sql = "SELECT * FROM tbl_project_voter WHERE pro_id = ? AND elect_id = ? AND voter_name LIKE ? ORDER BY voter_name ASC LIMIT 10";
           $stmt = $em->getConnection()->prepare($sql);
           $stmt->bindValue(1,2);
           $stmt->bindValue(2,3);
           $stmt->bindValue(3,'%' . $voterName . '%');
           $stmt->execute();

            $matchedVoters  = array();
            $nameStr = '';

            while($row  = $stmt->fetch(\PDO::FETCH_ASSOC)){
                $matchedVoters[] = $row;    
                $nameStr .= count($matchedVoters) . '. ' . $row['voter_name'] . '/' . $row['barangay_name'] . PHP_EOL;                
            }

            if(count($matchedVoters) == 0){
                $sms = new SendSms();
                $sms->setMessageText("Voter name not on the list...");
                $sms->setMessageTo($from);
                $sms->setMessageFrom("Kabarangay for Change");
            }else{
                $message = 'Names : ' . PHP_EOL;
                $message .= $nameStr;

                $sms = new SendSms();
                $sms->setMessageText($message);
                $sms->setMessageTo($from);
                $sms->setMessageFrom("Kabarangay for Change");
            }
        }

        $em->persist($sms);
        $em->flush();
    }

    private function _handleTextBlastByMunicipality($from,$gateway,$message_arr){
        $em = $this->getDoctrine()->getManager();
        $proIdCode = trim(strtoupper($message_arr[1]));

        $municipalityName = trim(strtoupper($message_arr[1]));
        $voterGroup  = trim(strtoupper($message_arr[2]));
        $message = trim(strtoupper($message_arr[3]));

        $sql = "SELECT * FROM tbl_project_voter pv 
                WHERE pv.elect_id = 4 AND pv.pro_id = 3 
                AND pv.province_code = 53 
                AND pv.municipality_name_alt = '{$municipalityName}' ";

        if($voterGroup != 'ALL'){
            $sql .= " AND pv.voter_group = '{$voterGroup}' ";
        }else{
            $sql .= " AND pv.voter_group IN ('LGC','LOPP','LPPP','LPPP1','LPPP2','LPPP3') ";
        }

        $sql .= " AND cellphone IS NOT NULL AND cellphone <> '' ORDER BY pv.voter_name ASC ";

        $stmt = $em->getConnection()->query($sql);

        $data = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $messageText = $message;

            $name2 = strtolower($row['firstname']) . ' ' . strtolower($row['middlename']) . ' ' . strtolower($row['lastname']) . strtolower($row['ext_name']);

            $south = ['04','05','06','15','17','21','23','24'];
            $north = ['02','03','07','08','09','10','11','12','13','14','18','19','20','22'];
            $mid = ['01'];

            $lineup = "";

            if(in_array($row['municipality_no'], $south) ){
                $lineup .= "Congressman : ALVAREZ, JCA" . PHP_EOL;
                $lineup .= "Governor : SOCRATES, DENNIS " . PHP_EOL;
                $lineup .= "Vice-Governor : OLA, ONSOY" . PHP_EOL;
                $lineup .= "Board Members : " . PHP_EOL;
                $lineup .= "MAMINTA, RYAN" . PHP_EOL;
                $lineup .= "IBBA, AL" . PHP_EOL;
                $lineup .= "ROXAS, MARIVIC" . PHP_EOL;
                $lineup .= "ARZAGA, ARIS" . PHP_EOL;
            }elseif(in_array($row['municipality_no'], $north)){
                $lineup .= "Congressman : ALVAREZ, ACA" . PHP_EOL;
                $lineup .= "Governor : SOCRATES, DENNIS " . PHP_EOL;
                $lineup .= "Vice-Governor : OLA, ONSOY" . PHP_EOL;
                $lineup .= "Board Members : " . PHP_EOL;
                $lineup .= "ALVAREZ, ANTON" . PHP_EOL;
                $lineup .= "PINEDA, TOTO" . PHP_EOL;
                $lineup .= "SABANDO, MARIA ANGELA" . PHP_EOL;
                $lineup .= "FORTES, JULIUS CEASAR" . PHP_EOL;
            }elseif(in_array($row['municipality_no'], $mid)){
                $lineup .= "Congressman : ACOSTA, GIL JR." . PHP_EOL;
                $lineup .= "Governor : SOCRATES, DENNIS " . PHP_EOL;
                $lineup .= "Vice-Governor : OLA, ONSOY" . PHP_EOL;
            }

            $transArr = array(
                '{NAME1}' => ucwords(strtolower($row['voter_name'])),
                '{NAME2}' => ucwords($name2),
                '{NAME3}' => ucwords(strtolower($row['firstname'])),
                '{PRECINCTNO}' => $row['precinct_no'],
                '{BRGY}' => $row['barangay_name'],
                '{MUN}' => $row['municipality_name'],
                '{VOTERNO}' => $row['voter_no'],
                '{POS}' => $row['voter_group'],
                '{LINEUP}' => $lineup
            );

            $messageText = strtr($messageText, $transArr);
            $messageText .= 'System generated.Please do not reply.';
        
            $sms = new SendSms();
            $sms->setMessageText($messageText);
            $sms->setMessageTo($row['cellphone']);
            $sms->setMessageFrom("JPM");
    
            $em->persist($sms);
            $em->flush();
        }

    }
}
