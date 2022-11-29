<?php
namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\Matcher\Matcher;
use Knp\Menu\Matcher\Voter\UriVoter;
use Knp\Menu\Renderer\ListRenderer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class MenuBuilder
{
    private $factory;
    private $container;

    /**
     * @param FactoryInterface $factory
     *
     * Add any other dependency you need
     * @param ContainerInterface $container
     */
    public function __construct(FactoryInterface $factory, ContainerInterface $container)
    {
        $this->factory = $factory;
        $this->container = $container;
    }

    public function getMenu($groupId){

        $data = $this->container->get("doctrine")
            ->getManager()
            ->getRepository("AppBundle:MenuItem")
            ->findBy(["groupId" => $groupId],["menuOrder" => "ASC"]);

        $normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        $menu = [];

        if(count($data) > 0 ){
            foreach($data as $item){
                $menu[] = $normalizer->normalize($item);
            }
        }

        return $this->container->get('AppBundle\Utils\Kenn')->toTree($menu);
    }

    public function createSidebarMenu(array $options)
    {
        $user = $this->container->get("security.token_storage")->getToken()->getUser();

        $menuData = $this->getMenu($user->getGroup());

        $menu = $this->factory->createItem('root')
            ->setChildrenAttribute('class','page-sidebar-menu page-header-fixed page-sidebar-menu-light page-sidebar-menu-closed ')
            ->setChildrenAttribute('data-keep-expanded',false)
            ->setChildrenAttribute('data-auto-scroll',true)
            ->setChildrenAttribute('data-slide-speed',200);

        foreach($menuData as $item){
            if(count($item['children']) == 0){
                $this->addLeaf($menu,$item);
            }elseif(count($item['children']) > 0){
                $this->addBranch($menu,$item);
            }
        }

        if($this->container->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            $menu->addChild("System Module",array(
                "route" => "manage_module",
                'extras' => [
                    'icon' => "<i class='fa fa-flask'></i>"
                ]
            ));

            $request = $this->container->get('request_stack')->getCurrentRequest();
            $routeName = str_replace($request->getBaseUrl(),'',$request->getRequestUri());
            switch (true)
            {
                case preg_match('/^\/manage_module*/', $routeName):
                    $menu->getChild('System Module')->setCurrent(true);
                    break;
            }
        }

        if(count($menu) == 0){
            $menu->addChild("No menu for this user",array("uri" => "javascript:;"));
        }



        return $menu;
    }

    private function addLeaf(&$menu,$item){
        $router = $this->container->get('router');
        //$route_type = ($item['menu_type'] == "Custom") ? 'uri' : 'route';
        if((null === $router->getRouteCollection()->get($item['menu_link']))){
            $link = "#";
            $route_type = "uri";
        }else{
            $link = $item['menu_link'];
            $route_type = "route";
        }


        $menu->addChild($item['menu_label'], array($route_type => $link,
            'extras' => [
                'icon' => $item['menu_icon'],
                'target' => $item['menu_target']
            ]
        ));
    }

    private function addBranch(&$menu,$item){
        $currentBranch = $menu->addChild($item['menu_label'], array(
            'extras' => [
                'icon' => $item['menu_icon'],
                'target' => $item['menu_target'],
                'submenu' => true
            ]
        ));

        $currentBranch->setChildrenAttribute('class','sub-menu');

        foreach($item['children'] as $childItem){
            if(count($childItem['children']) == 0){
                $this->addLeaf($currentBranch,$childItem);
            }elseif(count($childItem['children']) > 0){
                $this->addBranch($currentBranch,$childItem);
            }
        }

    }

    public function createBreadcrumbsMenu() {

        $bcmenu = $this->createSidebarMenu(array());
        return $bcmenu;
    }


}