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
* @Route("/org-summary")
*/

class OrganizationSummaryController extends Controller 
{
    const STATUS_ACTIVE = 'A';
    const MODULE_MAIN = "ORGANIZATION_SUMMARY";

    /**
     * @Route("", name="organization_summary_index", options={"main" = true})
     */

    public function indexAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $hostIp = $this->getParameter('host_ip');

        return $this->render('template/organization-summary/index.html.twig', ['user' => $user, 'hostIp' => $hostIp]);
    }

    
}
