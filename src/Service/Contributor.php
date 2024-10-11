<?php
namespace App\Service;

use App\Entity\Notification\Message;
use App\Entity\Notification\ReceiverMessage;
use App\Entity\Security\Role;
use App\Message\SendEmailMessage;
use App\Message\SendSmsMessage;
use App\Repository\Security\BranchRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\MessageBusInterface;

class Contributor {

    private $em;
    private $messageBus;

    public function __construct(EntityManagerInterface $em, MessageBusInterface $messageBus, RequestStack $request, BranchRepository $branchRepository)
    {
        $this->em = $em;
        $this->messageBus = $messageBus;

//        $this->request = $request->getCurrentRequest();
//        $this->branchRepository = $branchRepository;
//        $this->session = $this->request->getSession();
//        if (!$this->session->isStarted())
//            $this->session->start();
    }

//    protected function getCurrentBranch(): ?\App\Entity\Security\Branch
//    {
//        $currentBranchId = $this->session->get('currentBranch');
//
//        return $this->branchRepository->findOneBy(['id' => $currentBranchId]);
//    }

    public function distribute($routeName, $object, $optionalObject)
    {
        $role = $this->em->getRepository(Role::class)->findOneBy(['route' => $routeName]);

        $settings = $this->em->getRepository(ReceiverMessage::class)->findBy(['role' => $role, 'branch' => $optionalObject]);

        if ($settings)
        {
            foreach ($settings as $setting){

                if ($setting->getChannel() === 'EMAIL')
                {
                    $this->messageBus->dispatch(new SendEmailMessage($setting, $object, $optionalObject));
                }

                elseif ($setting->getChannel() === 'SMS')
                {
                    $this->messageBus->dispatch(new SendSmsMessage($setting, $object, $optionalObject));
                }

                else if ($setting->getChannel() === 'EMAIL & SMS')
                {
                    $this->messageBus->dispatch(new SendSmsMessage($setting, $object->getId()));
                    $this->messageBus->dispatch(new SendEmailMessage($setting, $object->getId()));
                }
            }
        }
    }

    public function contribute($object)
    {
        $message = $this->em->getRepository(Message::class)->findOneBy(['title' => 'Peremption']);
        $settings = $this->em->getRepository(ReceiverMessage::class)->findBy(['is_enable' => true, 'message' => $message]);

        if ($settings)
        {
            foreach ($settings as $setting){

                if ($setting->getChannel() === 'EMAIL')
                {
                    $this->messageBus->dispatch(new SendEmailMessage($setting, $object->getItem()->getName(), $object));
                }

                elseif ($setting->getChannel() === 'SMS')
                {
                    $this->messageBus->dispatch(new SendSmsMessage($setting, $object->getItem()->getName(), $object));
                }

                else if ($setting->getChannel() === 'EMAIL & SMS')
                {
                    $this->messageBus->dispatch(new SendSmsMessage($setting, $object->getItem()->getName(), $object));
                    $this->messageBus->dispatch(new SendEmailMessage($setting, $object->getItem()->getName(), $object));
                }
            }
        }
    }

    public function contributionMin($object)
    {
        $message = $this->em->getRepository(Message::class)->findOneBy(['title' => 'Stock Min']);
        $settings = $this->em->getRepository(ReceiverMessage::class)->findBy(['is_enable' => true, 'message' => $message]);

        if ($settings)
        {
            foreach ($settings as $setting){

                if ($setting->getChannel() === 'EMAIL')
                {
                    $this->messageBus->dispatch(new SendEmailMessage($setting, $object->getItem()->getName(), $object));
                }

                elseif ($setting->getChannel() === 'SMS')
                {
                    $this->messageBus->dispatch(new SendSmsMessage($setting, $object->getItem()->getName(), $object));
                }

                else if ($setting->getChannel() === 'EMAIL & SMS')
                {
                    $this->messageBus->dispatch(new SendSmsMessage($setting, $object->getItem()->getName(), $object));
                    $this->messageBus->dispatch(new SendEmailMessage($setting, $object->getItem()->getName(), $object));
                }
            }
        }
    }

    public function contributionMax($object)
    {
        $message = $this->em->getRepository(Message::class)->findOneBy(['title' => 'Stock Max']);
        $settings = $this->em->getRepository(ReceiverMessage::class)->findBy(['is_enable' => true, 'message' => $message]);

        if ($settings)
        {
            foreach ($settings as $setting){

                if ($setting->getChannel() === 'EMAIL')
                {
                    $this->messageBus->dispatch(new SendEmailMessage($setting, $object->getItem()->getName(), $object));
                }

                elseif ($setting->getChannel() === 'SMS')
                {
                    $this->messageBus->dispatch(new SendSmsMessage($setting, $object->getItem()->getName(), $object));
                }

                else if ($setting->getChannel() === 'EMAIL & SMS')
                {
                    $this->messageBus->dispatch(new SendSmsMessage($setting, $object->getItem()->getName(), $object));
                    $this->messageBus->dispatch(new SendEmailMessage($setting, $object->getItem()->getName(), $object));
                }
            }
        }
    }

    public function contributionZero($object)
    {
        $message = $this->em->getRepository(Message::class)->findOneBy(['title' => 'Stock Zero']);
        $settings = $this->em->getRepository(ReceiverMessage::class)->findBy(['is_enable' => true, 'message' => $message]);

        if ($settings)
        {
            foreach ($settings as $setting){

                if ($setting->getChannel() === 'EMAIL')
                {
                    $this->messageBus->dispatch(new SendEmailMessage($setting, $object->getItem()->getName(), $object));
                }

                elseif ($setting->getChannel() === 'SMS')
                {
                    $this->messageBus->dispatch(new SendSmsMessage($setting, $object->getItem()->getName(), $object));
                }

                else if ($setting->getChannel() === 'EMAIL & SMS')
                {
                    $this->messageBus->dispatch(new SendSmsMessage($setting, $object->getItem()->getName(), $object));
                    $this->messageBus->dispatch(new SendEmailMessage($setting, $object->getItem()->getName(), $object));
                }
            }
        }
    }

}