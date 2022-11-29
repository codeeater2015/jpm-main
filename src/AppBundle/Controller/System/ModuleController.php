<?php

namespace AppBundle\Controller\System;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\ModuleType;
use AppBundle\Entity\Module;
use AppBundle\Entity\Permission;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


/**
 * @Route("/manage_module")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
*/

class ModuleController extends Controller
{
    /**
     * @Route("", defaults={"page" = 1}, name="manage_module")
     * @Route("/page/{page}", requirements={"page": "[1-9]\d*"}, name="manage_module_paginated")
     * @param Request $request
     * @param $page
     * @return Response
     */
    public function indexAction(Request $request, $page){

        $searchField = "All";
        $searchValue = "";
        $sWhere = "";
        $columns = array('m.moduleName', 'm.moduleDesc');
        $columnsField = array(
            'module_name' => 'm.moduleName',
            'module_desc' => 'm.moduleDesc'
        );

        if($request->query->get('form')['searchField']){
            $searchField = $request->query->get('form')['searchField'];
        }

        if($request->query->get('form')['searchValue']){
            $searchValue = $request->query->get('form')['searchValue'];
        }

        if(!empty($searchValue)){
            if ($searchField === "All"){
                $sWhere = "WHERE (";
                for($i=0;$i<count($columns);$i++){
                    $sWhere .= " ".$columns[$i]." LIKE '%".str_replace("'","''",$searchValue)."%' OR ";
                }
                $sWhere = substr_replace( $sWhere, "", -3 );
                $sWhere .= ')';
            }else{
                $sWhere = "WHERE ".$columnsField[$searchField]." LIKE '%".str_replace("'","''",$searchValue)."%'";
            }
        }

        $filterForm = $this->createFormBuilder()
                            ->add('searchValue', TextType::class, array(
                                'data' => $searchValue,
                                'required' => false
                            ))
                            ->add('searchField', ChoiceType::class, array(
                                'data' => $searchField,
                                'choices' => array(
                                    'All' => 'All',
                                    'Module Name' => 'module_name',
                                    'Module Desc' => 'module_desc'
                                ),
                                'choice_translation_domain' => false
                            ))
                            ->getForm();

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT m FROM AppBundle:Module m ".$sWhere);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $page,
            10,
            array('distinct' => false,'defaultSortFieldName' => 'm.sortOrder', 'defaultSortDirection' => 'asc')
        );

        $pagination->setUsedRoute('manage_module_paginated');

        $params = $pagination->getParams();
        $params[$pagination->getPaginatorOption('pageParameterName')] = 1;
        $action = $this->get('router')->generate($pagination->getRoute(),$params,defined('Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_PATH') ? UrlGeneratorInterface::ABSOLUTE_PATH : false);

        return $this->render("template/system/manage_module/index.html.twig",[
            "modules" => $pagination,
            "filter_form" => $filterForm->createView(),
            "action" => $action
        ]);
    }

    /**
     * @Route("/create", name="create_module")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function createAction(Request $request){

        $module = new Module();

        $form = $this->createForm(ModuleType::class, $module);

        $form->handleRequest($request);

        if ($request->getMethod() == "POST") {
            if ($form->isSubmitted() && $form->isValid()) {

                $icon = '<i class="fa '.$form['moduleIcon']->getData().'"></i>';
                $module_name = strtoupper(str_replace(" ","_",$form['moduleName']->getData()));
                $module_label = ucwords($form['moduleLabel']->getData());

                $module->setModuleName($module_name);
                $module->setModuleLabel($module_label);
                $module->setModuleIcon($icon);

                $em = $this->getDoctrine()->getManager();
                $em->persist($module);

                $em->flush();
                $em->clear();


                $this->addFlash(
                    'success',
                    'module.created_successfully'
                );

                if ($request->request->has('save_return')) {
                    return $this->redirectToRoute('manage_module');
                }

                return $this->redirectToRoute('create_module');
            }
        }
        return $this->render("template/system/manage_module/create.html.twig",[
            "form" => $form->createView()
            //"form_permission" => $form_permission->createView()
        ]);
    }

    /**
     * @Route("/{module_id}/edit", name="edit_module")
     * @param Request $request
     * @param $module_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction(Request $request, $module_id){

        $em = $this->getDoctrine()->getManager();
        $module = $em->getRepository('AppBundle:Module')->find($module_id);

        $form = $this->createForm(ModuleType::class,$module);

        $form->handleRequest($request);

        if ($request->getMethod() == "POST") {
            if ($form->isSubmitted() && $form->isValid()) {

                $icon = '<i class="fa '.$form['moduleIcon']->getData().'"></i>';
                $moduleName = strtoupper(str_replace(" ","_",$form['moduleName']->getData()));
                $moduleLabel = ucwords($form['moduleLabel']->getData());

                $module->setModuleName($moduleName);
                $module->setModuleLabel($moduleLabel);
                $module->setModuleIcon($icon);

                $em->flush();
                $em->clear();

                $this->addFlash(
                    'success',
                    'module.updated_successfully'
                );

                if ($request->request->has('save_return')) {
                    return $this->redirectToRoute('manage_module');
                }

                return $this->redirectToRoute('edit_module', array("module_id" => strtolower($module_id)));
            }
        }
        return $this->render("template/system/manage_module/edit.html.twig",[
            "form" => $form->createView(),
            "module" => $module

        ]);
    }

    /**
     * @Route("/ajax_remove", name="ajax_remove_module", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxRemoveAction(Request $request){

        if ($request->isXmlHttpRequest()) {
            $id = $request->request->get('id');

            $em = $this->getDoctrine()->getManager();
            $module = $em->getRepository('AppBundle:Module')->find($id);

            $em->remove($module);
            $em->flush();

            $response = array("success" => true, "message" => "You have successfully remove module <b>".$module->getModuleLabel()."</b>.");

            return new JsonResponse($response);
        }

        return new JsonResponse(array("success" => false, "message" => "Ajax Only"));
    }


    public function getAllMainRouteName()
    {
//        $availableRoutes = [];
//        foreach ($this->get('router')->getRouteCollection()->all() as $name => $route) {
//            $route = $route->compile();
//            if( strpos($name, "_") !== 0 ){
//            $emptyVars = [];
//            foreach( $route->getVariables() as $v ){
//                $emptyVars[ $v ] = $v;
//            }
//            $url = $this->generateUrl( $name, $emptyVars );
//            $availableApiRoutes[] = ["name" => $name, "url" => $url, "variables" => $route->getVariables()];
//            }
//        }
        $availableRoutes = [];
        foreach ($this->get('router')->getRouteCollection()->all() as $name => $route) {
            if(null !== $route->getOption("main")){
                $availableApiRoutes[] = ["name" => $name];
            }
        }

        return $availableRoutes;
    }

    /**
     * @Route("/{module_id}/module_permission", name="module_permission")
     * @param Request $request
     * @param $module_id
     * @return Response
     */
    public function permissionAction(Request $request, $module_id){
        $em = $this->getDoctrine()->getManager();
        $permissions = $em->getRepository("AppBundle:Permission")->findBy(["moduleId" => $module_id]);

        return $this->render("template/system/manage_module/permission.html.twig",[
            "module_id" => $module_id,
            "permissions" => $permissions
        ]);
    }

    /**
     * @Route("/ajax_add_module_permission", name="ajax_add_module_permission", options={"expose"=true})
     * @Method("POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxAddModulePermission(Request $request){

        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();

            $module_id = $request->request->get('module_id');
            $name = $request->request->get('name');
            $description = $request->request->get('description');

            $module = $em->getRepository('AppBundle:Module')->find($module_id);

            $new_permission = new Permission();
            $new_permission->setModuleId($module);
            $new_permission->setPermissionName($name);
            $new_permission->setPermissionDesc($description);

            $em->persist($new_permission);
            $em->flush();
            $id = $new_permission->getId();

            $response = array("success" => true, "message" => "Permission Successfully Added.",
                                "data" => array(
                                    "id" => $id,
                                    "name" => $name,
                                    "description" => $description
                                )
                        );

            return new JsonResponse($response);
        }

        return new JsonResponse(array("success" => false, "message" => "Ajax Only"));
    }

    /**
     * @Route("/ajax_Update_module_permission", name="ajax_update_module_permission", options={"expose"=true})
     * @Method("PATCH")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxUpdateModulePermission(Request $request){

        if ($request->isXmlHttpRequest()) {
            $em = $this->getDoctrine()->getManager();

            $id = $request->request->get('id');
            $name = $request->request->get('name');
            $description = $request->request->get('description');

            $permission = $em->getRepository('AppBundle:Permission')->find($id);

            $permission->setPermissionName($name);
            $permission->setPermissionDesc($description);

            $em->persist($permission);
            $em->flush();
            $id = $permission->getId();

            $response = array("success" => true, "message" => "Permission Successfully Updated.",
                                "data" => array(
                                    "id" => $id,
                                    "name" => $name,
                                    "description" => $description
                                )
                            );

            return new JsonResponse($response);
        }

        return new JsonResponse(array("success" => false, "message" => "Ajax Only"));
    }

    /**
     * @Route("/ajax_remove_module_permission", name="ajax_remove_module_permission", options={"expose"=true})
     * @Method("DELETE")
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxRemoveModulePermission(Request $request){
        $em = $this->getDoctrine()->getManager();

        $id = $request->query->get('id');

        $permission = $em->getRepository('AppBundle:Permission')->find($id);
        $em->remove($permission);
        $em->flush();
        $response = array("success" => true, "message" => "Permission Successfully Remove.");

        return new JsonResponse($response);

    }
}
