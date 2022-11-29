<?php

namespace AppBundle\Utils;

use Symfony\Component\Form\FormInterface;

class Kenn
{
    public function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }

     public function in_multiarray($search_for, $search_in) {
         foreach ($search_in as $element) {
             if ( ($element === $search_for) ){
                 return true;
             }elseif(is_array($element)){
                 $result = $this->in_multiarray($search_for, $element);
                 if($result == true)
                     return true;
             }
         }
         return false;
     }

     public function getControllerName($request_attributes){

         $pattern = "/Controller\\\\System\\\\([a-zA-Z]*)Controller/";

         $matches = array();
         preg_match($pattern, $request_attributes, $matches);

         return $matches[1];
     }

    public  function toTree(&$menus){

        $map = array(
            0 => array('children' => array())
        );

        foreach ($menus as &$menu) {
            $menu['children'] = array();
            $map[$menu['menu_id']] = &$menu;
        }

        foreach ($menus as &$menu) {
            $map[$menu['parent_id']]['children'][] = &$menu;
        }

        return $map[0]['children'];
    }

    public function replace_null_with_empty_string($array)
    {
        foreach ($array as $key => $value)
        {
            if(is_array($value))
                $array[$key] = $this->replace_null_with_empty_string($value);
            else
            {
                if (is_null($value))
                    $array[$key] = "";
            }
        }
        return $array;
    }

    public function sortBy($field, &$array, $direction = 'asc')
    {
        usort($array, create_function('$a, $b', '
            $a = $a["' . $field . '"];
            $b = $b["' . $field . '"];
    
            if ($a == $b)
            {
                return 0;
            }
    
            return ($a ' . ($direction == 'desc' ? '>' : '<') .' $b) ? -1 : 1;
        '));

        return true;
    }

}
