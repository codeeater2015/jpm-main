<?php
/**
 * Created by PhpStorm.
 * User: Yoh Kenn
 * Date: 9/29/2016
 * Time: 3:47 PM
 */

namespace AppBundle\Security\Authorization;


use AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SystemVoter extends Voter
{
    const ENTRANCE = 'entrance';
    const VIEW = 'view';
    const EDIT = 'edit';
    const ADD = 'add';
    const DELETE = 'delete';
    const EXPORT = 'export';
    const BULK_SENDING = 'bulk sending';
    const DCMS_ADD_REMARKS = 'add remarks';
    const DCMS_MARK_AS_READ = 'mark as read';

    private $container;
    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager, ContainerInterface $container)
    {
        $this->decisionManager = $decisionManager;
        $this->container = $container;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed $subject The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool True if the attribute and subject are supported, false otherwise
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::ENTRANCE,self::VIEW, self::EDIT, self::ADD, self::DELETE, self::EXPORT, self::BULK_SENDING, self::DCMS_ADD_REMARKS,self::DCMS_MARK_AS_READ))) {
            return false;
        }

        return true;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     *
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {

        $user = $token->getUser();

        if(!$user instanceof User){
            return false;
        }

        if ($this->decisionManager->decide($token, array('ROLE_SUPER_ADMIN'))) {
            return true;
        }

        $group = $user->getGroup()->getId();
        $em = $this->container->get('doctrine')->getManager();
        $data = $em->getRepository("AppBundle:GroupPermission")->checkGroupPermission($attribute,$group,$subject)->getResult();

        if(count($data) > 0){
            return true;
        }else{
            return false;
        }

        throw new \LogicException('This code should not be reached!');
    }
}