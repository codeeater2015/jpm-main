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

use Box\Spout\Writer\WriterFactory;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\Border;
use Box\Spout\Writer\Style\BorderBuilder;

use AppBundle\Entity\Voter;
use AppBundle\Entity\VoterHistory;
use AppBundle\Entity\VoterApprovalHdr;
use AppBundle\Entity\VoterApprovalDtl;
use AppBundle\Entity\VoterNetwork;

/**
* @Route("/network-report")
*/

class VoterNetworkReportController extends Controller 
{
    const STATUS_ACTIVE = 'A';
    const STATUS_PENDING = 'PEN';
    const STATUS_INACTIVE = 'I';
    const MODULE_MAIN = "VOTER_NETWORK_REPORT";

	/**
    * @Route("", name="voter_network_report_index", options={"main" = true })
    */

    public function indexAction(Request $request)
    {   
        $this->denyAccessUnlessGranted("entrance",self::MODULE_MAIN);
        $user = $this->get('security.token_storage')->getToken()->getUser();
        return $this->render('template/voter-network-report/index.html.twig',['user' => $user]);
    }

    /**
    * @Route("/ajax_get_network_report_nodes", 
    *       name="ajax_get_network_report_nodes",
    *		options={ "expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxGetNetworkReportNodes(Request $request){
        $em = $this->getDoctrine()->getManager();

        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");
        $maxDeep = $request->get("maxDeep");
        
        $municipality = $this->getMunicipality($provinceCode,$municipalityNo);
        $barangay = $this->getBarangay($provinceCode,$municipalityNo,$brgyNo);

        $sql = "SELECT * FROM tbl_voter_network WHERE municipality_no = ? AND brgy_no = ? AND node_level = ?";
        $stmt= $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$municipalityNo);
        $stmt->bindValue(2,$brgyNo);
        $stmt->bindValue(3,1);
        $stmt->execute();
        
        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $row['children'] = $this->getChildNodes($row['node_id']);
            $data[] = $row;
        }

        $rows = [];
        $counter = 0;
        foreach($data as $item){
            $counter++;
            $row = [];
            
            if($maxDeep > 3){
                $row[] = $counter . '. ' .  $item['node_label'];
                for($i = 1;$i < $maxDeep;$i++){
                    $row[] = "";
                }
                $row[] =  count($item['children']);
            }else{
                $row = [
                    $counter . '. ' .  $item['node_label'] . '-' . $item['precinct_no'],
                    "",
                    "",
                    "",
                    count($item['children'])
                ];
            }
            
            $rows[] = $row;

            $childRows = [];
            
            if(count($item['children']) > 0)
                $childRows = $this->getChildRows($item['children'],$maxDeep);
                
            $rows = array_merge($rows,$childRows);
        }

        return new JsonResponse($rows);
    }

     /**
    * @Route("/ajax_export_network_report_nodes", 
    *       name="ajax_export_network_report_nodes",
    *		options={ "expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxExportNetworkReportNodes(Request $request){
        $em = $this->getDoctrine()->getManager();
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");
        $maxDeep = $request->get("maxDeep");
        $provinceCode = $request->get("provinceCode");
        
        $municipality = $this->getMunicipality($provinceCode,$municipalityNo);
        $barangay = $this->getBarangay($provinceCode,$municipalityNo,$brgyNo);

        $sql = "SELECT * FROM tbl_voter_network WHERE municipality_no = ? AND brgy_no = ? AND node_level = ? ORDER BY node_label ASC";
        $stmt= $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$municipalityNo);
        $stmt->bindValue(2,$brgyNo);
        $stmt->bindValue(3,1);
        $stmt->execute();
        
        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $row['children'] = $this->getChildNodes($row['node_id']);
            $data[] = $row;
        }

        $rows = [];
        $counter = 0;
        foreach($data as $item){
            $counter++;
            $row = [];
            
            if($maxDeep > 3){
                $row[] = $counter . '. ' .  $item['node_label'];
                for($i = 1;$i < $maxDeep;$i++){
                    $row[] = "";
                }
                $row[] =  count($item['children']);
            }else{
                $row = [
                    $counter . '. ' .  $item['node_label'] . '-' . $item['precinct_no'],
                    "",
                    "",
                    "",
                    count($item['children'])
                ];
            }
            
            $rows[] = $row;

            $childRows = [];
            
            if(count($item['children']) > 0)
                $childRows = $this->getChildRows($item['children'],$maxDeep);
                
            $rows = array_merge($rows,$childRows);
        }
        
        $filename =  $municipalityNo .'_' . $brgyNo . "_hierarchy_list.xlsx";
        $fileRoot = __DIR__.'/../../../web/uploads/';

        if(file_exists($fileRoot . $filename)){
            unlink($fileRoot . $filename);
        }

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($fileRoot . $filename);

       

        $writer->addRow([
            "City/Municipality : " . $municipality['name']
        ]);

        $writer->addRow([
            "Barangay : " . $barangay['name'],
        ]);

        $writer->addRow([
            ""
        ]);

        $writer->addRow([
            "No. of Registered Voter : " . $barangay['total_voter']
        ]);

        
        $writer->addRow([
            "No. of Recruited Voter : " . $barangay['total_recruits'] 
        ]);

        $writer->addRow([
            "Percentage : " . number_format($barangay['percentage'],2) . ' %'
        ]);

        $writer->addRow([
            ""
        ]);
        
        $columns = [];
        $columns[] = 'PARENT';
        $lastColumn = 0;

        if($maxDeep > 3){
            $lastColumn = $maxDeep;
            for($i = 1;$i < $maxDeep;$i++){
                $columns[] = 'MEMBER ' . $i;
            }
        }else{
            $columns[] = "MEMBER 1";
            $columns[] = "MEMBER 2";
            $columns[] = "MEMBER 3";

            $lastColumn = 4;
        }

        $columns[] = 'TOTAL NO. OF MEMBERS';
        $writer->addRow($columns);
    
        foreach($rows as &$row){
            if(is_numeric($row[$lastColumn]) && $row[$lastColumn] == 0)
                $row[$lastColumn] = "";
            elseif(is_array($row[$lastColumn]))
                $row[$lastColumn] = "";

            $writer->addRow($row);
        }

        $writer->close();

        $response = new BinaryFileResponse($fileRoot . $filename);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }

    public function getChildRows($nodes,$maxDeep){
        $rows = [];
        $counter = 0;

        foreach($nodes as $item){
            $counter++;
            $row = [];

            if($maxDeep > 3){
                for($i = 0;$i < $maxDeep;$i++){
                    if(($i + 1) ==  $item['node_level'])
                        $row[] = $counter . '. ' . $item['node_label'] . '-' . $item['precinct_no'];
                    else
                        $row[] = "";
                }

                $row[] = ""; //count($item['children']);
            }else{
                $row = [
                    $item['node_level'] ==  1 ? $counter . '. ' . $item['node_label'] . '-' . $item['precinct_no'] : "",
                    $item['node_level'] ==  2 ? $counter . '. ' . $item['node_label'] . '-' . $item['precinct_no'] : "",
                    $item['node_level'] ==  3 ? $counter . '. ' . $item['node_label'] . '-' . $item['precinct_no'] : "",
                    $item['node_level'] ==  4 ? $counter . '. ' . $item['node_label'] . '-' . $item['precinct_no'] : "",
                    count($item['children'])
                ];
            }
           
            $rows[] = $row;

            $childRows = [];
            
            if(count($item['children']) > 0)
                $childRows = $this->getChildRows($item['children'],$maxDeep);
                
            $rows = array_merge($rows,$childRows);
        }

        return $rows;
    }

    private function getChildNodes($nodeId){
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM tbl_voter_network WHERE parent_id = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$nodeId);
        $stmt->execute();

        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $row['children'] = $this->getChildNodes($row['node_id']);
            $data[] = $row;
        }
    

        return $data;
    }

    private function getBarangay($provinceCode, $municipalityNo,$brgyNo){
        
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT 
            (SELECT COALESCE(COUNT(n.node_id),0) FROM tbl_voter_network n WHERE n.province_code = ? AND n.municipality_no = ? AND n.brgy_no = ?) as total_recruits,
            (SELECT COALESCE(COUNT(v.voter_id),0) FROM tbl_voter v WHERE v.province_code=  ? AND v.municipality_no = ? AND v.brgy_no  = ? ) AS total_voter,
            b.* FROM psw_barangay b WHERE b.municipality_code = ? AND b.brgy_no = ?";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->bindValue(3,$brgyNo);
        $stmt->bindValue(4,$provinceCode);
        $stmt->bindValue(5,$municipalityNo);
        $stmt->bindValue(6,$brgyNo);
        $stmt->bindValue(7,$provinceCode . $municipalityNo);
        $stmt->bindValue(8,$brgyNo);
    
        $stmt->execute();
        
        $barangay = $stmt->fetch(\PDO::FETCH_ASSOC);

        if(empty($barangay))
            return [
                "total_recruits" => 0 ,
                "total_voter" => 0,
                "percentage" => 0
            ];

        $barangay['percentage'] = $barangay['total_voter'] != 0 ? ($barangay['total_recruits'] / $barangay['total_voter']) * 100 : 0;

        return $barangay;
    }
    
    private function getMunicipality($provinceCode, $municipalityNo){
        $em = $this->getDoctrine()->getManager();

        $sql = "SELECT * FROM psw_municipality m WHERE m.province_code = ? AND m.municipality_no = ? LIMIT 1";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->execute();

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
    * @Route("/ajax_export_network_report_nodes_option2", 
    *       name="ajax_export_network_report_nodes_option2",
    *		options={ "expose" = true}
    * )
    * @Method("GET")
    */

    public function ajaxExportNetworkReportNodesOption2(Request $request){
        $em = $this->getDoctrine()->getManager();

        $provinceCode = $request->get("provinceCode");
        $municipalityNo = $request->get("municipalityNo");
        $brgyNo = $request->get("brgyNo");

        $municipality = $this->getMunicipality($provinceCode, $municipalityNo);
        $barangay = $this->getBarangay($provinceCode, $municipalityNo, $brgyNo);

        $sql = "SELECT n.*,b.name AS barangay_name FROM tbl_voter_network n 
                INNER JOIN psw_municipality m ON m.municipality_no = n.municipality_no AND m.province_code = ?
                INNER JOIN psw_barangay b ON b.brgy_no = n.brgy_no AND b.municipality_code = m.municipality_code
                WHERE n.municipality_no = ? AND n.brgy_no = ? AND n.node_level = ?";

        $stmt= $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$provinceCode);
        $stmt->bindValue(2,$municipalityNo);
        $stmt->bindValue(3,$brgyNo);
        $stmt->bindValue(4,1);
        $stmt->execute();
        
        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $row['children'] = $this->getChildNodes2($row['node_id'],$row['node_label'],$row['node_label']);
            $row['parent_thread'] = "";
            $row['parent_node'] = "";
            $data[] = $row;
        }

        $rows = [];
        foreach($data as $item){
            $rows[] = $item;
            $childRows = [];

            if(count($item['children']) > 0){
                $childRows = $this->getChildRowsOption2($item['children'],$item['node_label'],$item['node_label']);
            }
            
            $rows = array_merge($rows,$childRows);
        }
        
        $filename =  $municipalityNo .'_' . $brgyNo . "_hierarchy_list.xlsx";
        $fileRoot = __DIR__.'/../../../web/uploads/';

        if(file_exists($fileRoot . $filename)){
            unlink($fileRoot . $filename);
        }

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToFile($fileRoot . $filename);

        $style = (new StyleBuilder())
                    ->setFontBold()
                    ->build();
        
        $leaderStyle = (new StyleBuilder())
                ->setFontBold()
                ->setFontColor(Color::WHITE)
                ->setBackgroundColor(Color::GREEN)
                ->build();
         
        $writer->addRowWithStyle([
            "Name",
            "Barangay",
            "Precinct No.",
            "Level",
            "Is Leader",
            "Leader",
            "Leader Head"
        ],$style);
        
        foreach($rows as $item){
            if($item['node_level'] == 1){
                $writer->addRowWithStyle([  
                    $item['node_label'],
                    $item['barangay_name'],
                    $item['precinct_no'],
                    $item['node_level'],
                    $item['node_level'] == 1 ? "YES" : "NO",
                    $item['parent_node'],
                    $item['parent_thread']
                ],$leaderStyle);
            }else{
                $writer->addRow([  
                    $item['node_label'],
                    $item['barangay_name'],
                    $item['precinct_no'],
                    $item['node_level'],
                    $item['node_level'] == 1 ? "YES" : "NO",
                    $item['parent_node'],
                    $item['parent_thread']
                ]);
            }
        }

        $writer->close();

        $response = new BinaryFileResponse($fileRoot . $filename);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);

        return $response;
    }

    private function getChildNodes2($nodeId,$parentNode,$parentThread){
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT n.*,b.name as barangay_name FROM tbl_voter_network n 
                INNER JOIN psw_municipality m ON m.municipality_no = n.municipality_no AND m.province_code = 53
                INNER JOIN psw_barangay b ON b.brgy_no = n.brgy_no AND b.municipality_code = m.municipality_code 
                WHERE n.parent_id = ?";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->bindValue(1,$nodeId);
        $stmt->execute();

        $data = [];

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $row['parent_thread'] = $parentThread;
            $row['parent_node'] = $parentNode;
            $row['children'] = $this->getChildNodes2($row['node_id'],$row['node_label'],$parentThread);
            $data[] = $row;
        }

        return $data;
    }

    public function getChildRowsOption2($nodes,$parentNode,$parentThread){
        $rows = [];
        $childRows = [];

        foreach($nodes as &$item){
            $rows[] = $item;
            $childRows = [];
            $item['parent_thread'] = $parentThread;
            $item['parent_node'] = $parentNode;

            if(count($item['children']) > 0){
                $childRows = $this->getChildRowsOption2($item['children'],$item['node_label'],$parentThread);
            }

            $rows = array_merge($rows,$childRows);
        }

        return $rows;
    }
}
