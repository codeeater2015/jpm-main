<?php
/**
 * Created by PhpStorm.
 * User: PGP_ITD03
 * Date: 09/15/2017
 * Time: 10:09 AM
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\SendSms;

class SendGreetingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('appbundle:send_greeting')
            ->setDescription('Send birthday greetings');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $default_em = $this->getContainer()->get('doctrine')->getManager();
        $rescue_em = $this->getContainer()->get('doctrine')->getManager("rescue");

        $sql = "SELECT pd.name, pd.cellphone FROM PSW_PROFILE_DTL pd WHERE pd.birthday LIKE ? ";

        $stmt = $rescue_em->getConnection()->prepare($sql);
        $stmt->bindValue(1, "%" . date('m-d'));
        $stmt->execute();

        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){

            $message = "Happy Birthday " .  $row['name'] . PHP_EOL;
            $message .= "Wishing my friend a beautiful day. Hopes and dreams sending your way. May all be good and all come true on this very special day for you." . PHP_EOL;
            $message .= "Greetings from the Provincial Government of Palawan." . PHP_EOL;
            $message .= "Do not reply.";

            if(preg_match("/^(09)\\d{9}/",$row['cellphone'])){
                $sms = new SendSms();
                $sms->setMessageText($message);
                $sms->setMessageTo($row['cellphone']);
                $sms->setMessageFrom('PGP-IHELP');
                $sms->setPriority(100);
                $default_em->persist($sms);
            }
        }

        $default_em->flush();
        $default_em->clear();
    }
}