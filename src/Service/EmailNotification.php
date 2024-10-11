<?php
namespace App\Service;

use App\Entity\Notification\NotificationHistory;
use App\Repository\Notification\ReceiverMessageRepository;
use App\Repository\Security\BranchRepository;
use App\Repository\Security\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\BodyRenderer;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

class EmailNotification{

    /**
     * @var MailerInterface
     */
    private $mailer;

    /*
     * @var Environment
     */
    private $renderer;

    public function __construct(MailerInterface $mailer, Security $security, Environment $renderer, ReceiverMessageRepository $receiverNotifications, RoleRepository $roles, EntityManagerInterface $manager, BranchRepository $branchRepository)
    {
        $this->mailer = $mailer;
        $this->receiverNotifications = $receiverNotifications;
        $this->em = $manager;
        $this->roles = $roles;
        $this->renderer = $renderer;
        $this->security = $security;
        $this->branchRepository = $branchRepository;

    }

    public function internalEmail($receiver, $title, $messageNotification, $module, $channel, $object, $optionalObject){

        $message = str_replace('%t', $object->getReference(), $messageNotification->getDescription());
        $message = str_replace('%s', $object->getSource()->getCode(), $message);
        $message = str_replace('%d', $object->getDestination()->getCode(), $message);

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@globalerpcm.com', 'GlobalERP'))
            ->to($receiver->getEmail())
            ->subject($title)
            ->htmlTemplate('mail/mail_notification.html.twig')

            // pass variables (name => value) to the template
            ->context([
                'message' => $message,
                'receiver' => $receiver,
                //'object' => $object
            ]);

        $renderer = new BodyRenderer($this->renderer);

        $renderer->render($email);

        try {
            $this->mailer->send($email);

            $history = new NotificationHistory();
            $history->setMessage($message);
            $history->setReceiver($receiver);
            $history->setModule($module);
            $history->setStatus('success');
            $history->setDetail('email:success');
            //$history->setCreatedAt(new \DateTime());
            $history->setUser($this->security->getUser());
            $history->setChannel($channel);
            $history->setBranch($this->branchRepository->findOneBy(['id' => $optionalObject->getId()]));
            $this->em->persist($history);
            $this->em->flush();
            ;

        } catch (TransportExceptionInterface $e) {

            $history = new NotificationHistory();
            $history->setMessage($message);
            $history->setReceiver($receiver);
            $history->setModule($module);
            $history->setStatus($e->getMessage());
            $history->setDetail($e->getTraceAsString());
            //$history->setCreatedAt(new \DateTime());
            $history->setUser($this->security->getUser());
            $history->setChannel($channel);
            $history->setBranch($this->branchRepository->findOneBy(['id' => $optionalObject->getId()]));
            $this->em->persist($history);
            $this->em->flush();

            // some error prevented the email sending; display an
            $e->getMessage();
            // error message or try to resend the message
            $e->getDebug();
        }

    }

    public function alertEmail($receiver, $title, $messageNotification, $module, $channel, $object, $optionalObject){

        $description = str_replace('%r', $optionalObject->getReference(), $messageNotification->getDescription());
        $description = str_replace('%i', $optionalObject->getItem()->getName(),$description);
        $description = str_replace('%p', $optionalObject->getLostAt()->format('d/m/Y'),$description);

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@globalerpcm.com', 'GlobalERP'))
            ->to($receiver->getEmail())
            ->subject($title)
            ->htmlTemplate('mail/mail_notification_alert.html.twig')

            // pass variables (name => value) to the template
            ->context([
                'message' => $messageNotification,
                'receiver' => $receiver,
                'description' => $description
            ]);

        $renderer = new BodyRenderer($this->renderer);

        $renderer->render($email);

        try {
            $this->mailer->send($email);

            $history = new NotificationHistory();
            $history->setMessage($messageNotification);
            $history->setReceiver($receiver);
            $history->setModule($module);
            $history->setStatus('success');
            $history->setDetail('success');
            //$history->setCreatedAt(new \DateTime());
            $history->setUser($this->security->getUser());
            $history->setChannel($channel);
            //$history->setBranch($this->security->getUser()->getBranch());
            $this->em->persist($history);
            $this->em->flush();
            ;

        } catch (TransportExceptionInterface $e) {

            $history = new NotificationHistory();
            $history->setMessage($messageNotification);
            $history->setReceiver($receiver);
            $history->setModule($module);
            $history->setStatus($e->getMessage());
            $history->setDetail($e->getTraceAsString());
            //$history->setCreatedAt(new \DateTime());
            $history->setUser($this->security->getUser());
            $history->setChannel($channel);
            //$history->setBranch($this->security->getUser()->getBranch());
            $this->em->persist($history);
            $this->em->flush();

            // some error prevented the email sending; display an
            $e->getMessage();
            // error message or try to resend the message
            $e->getDebug();
        }

    }

    public function alertMinEmail($receiver, $title, $messageNotification, $module, $channel, $object, $optionalObject){

        $description = str_replace('%i', $optionalObject->getItem()->getName(), $messageNotification->getDescription());
        $description = str_replace('%q', $optionalObject->getAvailable(),$description);
        $description = str_replace('%m', $optionalObject->getItem()->getStockMin(),$description);

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@globalerpcm.com', 'GlobalERP'))
            ->to($receiver->getEmail())
            ->subject($title)
            ->htmlTemplate('mail/mail_notification_alert.html.twig')

            // pass variables (name => value) to the template
            ->context([
                'message' => $messageNotification,
                'receiver' => $receiver,
                'description' => $description
            ]);

        $renderer = new BodyRenderer($this->renderer);

        $renderer->render($email);

        try {
            $this->mailer->send($email);

            $history = new NotificationHistory();
            $history->setMessage($messageNotification);
            $history->setReceiver($receiver);
            $history->setModule($module);
            $history->setStatus('success');
            $history->setDetail('success');
            //$history->setCreatedAt(new \DateTime());
            $history->setUser($this->security->getUser());
            $history->setChannel($channel);
            //$history->setBranch($this->security->getUser()->getBranch());
            $this->em->persist($history);
            $this->em->flush();
            ;

        } catch (TransportExceptionInterface $e) {

            $history = new NotificationHistory();
            $history->setMessage($messageNotification);
            $history->setReceiver($receiver);
            $history->setModule($module);
            $history->setStatus($e->getMessage());
            $history->setDetail($e->getTraceAsString());
            //$history->setCreatedAt(new \DateTime());
            $history->setUser($this->security->getUser());
            $history->setChannel($channel);
            //$history->setBranch($this->security->getUser()->getBranch());
            $this->em->persist($history);
            $this->em->flush();

            // some error prevented the email sending; display an
            $e->getMessage();
            // error message or try to resend the message
            $e->getDebug();
        }

    }

    public function alertMaxEmail($receiver, $title, $messageNotification, $module, $channel, $object, $optionalObject){

        $description = str_replace('%i', $optionalObject->getItem()->getName(), $messageNotification->getDescription());
        $description = str_replace('%q', $optionalObject->getAvailable(),$description);
        $description = str_replace('%m', $optionalObject->getItem()->getStockMax(),$description);

        $email = (new TemplatedEmail())
            ->from(new Address('noreply@globalerpcm.com', 'GlobalERP'))
            ->to($receiver->getEmail())
            ->subject($title)
            ->htmlTemplate('mail/mail_notification_alert.html.twig')

            // pass variables (name => value) to the template
            ->context([
                'message' => $messageNotification,
                'receiver' => $receiver,
                'description' => $description
            ]);

        $renderer = new BodyRenderer($this->renderer);

        $renderer->render($email);

        try {
            $this->mailer->send($email);

            $history = new NotificationHistory();
            $history->setMessage($messageNotification);
            $history->setReceiver($receiver);
            $history->setModule($module);
            $history->setStatus('success');
            $history->setDetail('success');
            //$history->setCreatedAt(new \DateTime());
            $history->setUser($this->security->getUser());
            $history->setChannel($channel);
            //$history->setBranch($this->security->getUser()->getBranch());
            $this->em->persist($history);
            $this->em->flush();
            ;

        } catch (TransportExceptionInterface $e) {

            $history = new NotificationHistory();
            $history->setMessage($messageNotification);
            $history->setReceiver($receiver);
            $history->setModule($module);
            $history->setStatus($e->getMessage());
            $history->setDetail($e->getTraceAsString());
            //$history->setCreatedAt(new \DateTime());
            $history->setUser($this->security->getUser());
            $history->setChannel($channel);
            //$history->setBranch($this->security->getUser()->getBranch());
            $this->em->persist($history);
            $this->em->flush();

            // some error prevented the email sending; display an
            $e->getMessage();
            // error message or try to resend the message
            $e->getDebug();
        }

    }

//    public function internalEmail($route){
//
//        $activeRole = $this->roles->findOneBy(['route' => $route]);
//        $activeNotifications = $this->receiverNotifications->findBy(['role' => $activeRole, 'is_enable' => true, 'is_archive' => false, 'channel' => 'EMAIL']);
//
//        foreach ($activeNotifications as $activeNotification)
//        {
//            $email = (new TemplatedEmail())
//                ->from('noreply@globalerpcm.com')
//                ->to($activeNotification->getReceiver()->getEmail())
//                ->subject($activeNotification->getMessage()->getTitle())
//                ->htmlTemplate('mail/mail_notification.html.twig')
//
//                // pass variables (name => value) to the template
//                ->context([
//                    'message' => $activeNotification->getMessage(),
//                    'receiver' => $activeNotification->getReceiver()
//                ]);
//
//            $renderer = new BodyRenderer($this->renderer);
//
//            $renderer->render($email);
//
//            try {
//                $this->mailer->send($email);
//
//				$history = new NotificationHistory();
//				$history->setMessage($activeNotification->getMessage());
//				$history->setReceiver($activeNotification->getReceiver());
//				$history->setStatus('SEND');
//				//$history->setCreatedAt(new \DateTime());
//				$history->setUser($this->security->getUser());
//                $history->setChannel($activeNotification->getChannel());
//				//$history->setBranch($this->security->getUser()->getBranch());
//                $this->em->persist($history);
//                $this->em->flush();
//				;
//
//            } catch (TransportExceptionInterface $e) {
//
//				$history = new NotificationHistory();
//				$history->setMessage($activeNotification->getMessage());
//				$history->setReceiver($activeNotification->getReceiver());
//				$history->setStatus($e->getMessage());
//				//$history->setCreatedAt(new \DateTime());
//                $history->setUser($this->security->getUser());
//                $history->setChannel($activeNotification->getChannel());
//                //$history->setBranch($this->security->getUser()->getBranch());
//                $this->em->persist($history);
//                $this->em->flush();
//
//                // some error prevented the email sending; display an
//                $e->getMessage();
//                // error message or try to resend the message
//                $e->getDebug();
//            }
//
//        }
//
//
//    }

//    public function recoveryPasswordEmailConfirm($receiver, $message){
//        $email = (new TemplatedEmail())
//            ->from('noreply@globalerpcm.com')
//            ->to($receiver)
//            ->subject('Recovery password')
//            ->htmlTemplate('mail/mail_confirm_password.html.twig')
//
//            // pass variables (name => value) to the template
//            ->context([
//                'message' => $message,
//                'receiver' => $receiver
//            ]);
//
//        $renderer = new BodyRenderer($this->renderer);
//
//        $renderer->render($email);
//
//        try {
//            $this->mailer->send($email);
//
//            $history = new NotificationHistory();
//            $history->setStatus('SEND');
//            $history->setCreatedAt(new \DateTime());
//            $history->setUser($this->security->getUser());
//            $this->em->persist($history);
//            $this->em->flush();
//
//        } catch (TransportExceptionInterface $e) {
//
//            $history = new NotificationHistory();
//            $history->setStatus($e->getMessage());
//            $history->setCreatedAt(new \DateTime());
//            $this->em->persist($history);
//            $this->em->flush();
//
//            // some error prevented the email sending; display an
//            $e->getMessage();
//            // error message or try to resend the message
//            $e->getDebug();
//        }
//    }
//
//    public function userMailNotification($receiver, $message, $subject){
//        $email = (new TemplatedEmail())
//            ->from('noreply@globalerpcm.com')
//            ->to($receiver)
//            ->subject($subject)
//            ->htmlTemplate('mail/user_mail_notification.html.twig')
//
//            // pass variables (name => value) to the template
//            ->context([
//                'message' => $message,
//                'receiver' => $receiver
//            ]);
//
//        $renderer = new BodyRenderer($this->renderer);
//
//        $renderer->render($email);
//
//        try {
//            $this->mailer->send($email);
//
//            $history = new NotificationHistory();
//            $history->setStatus('SEND');
//            $history->setCreatedAt(new \DateTime());
//            $history->setUser($this->security->getUser());
//            $this->em->persist($history);
//            $this->em->flush();
//
//        } catch (TransportExceptionInterface $e) {
//
//            $history = new NotificationHistory();
//            $history->setStatus($e->getMessage());
//            $history->setCreatedAt(new \DateTime());
//            $history->setUser($this->security->getUser());
//            $this->em->persist($history);
//            $this->em->flush();
//
//            // some error prevented the email sending; display an
//            $e->getMessage();
//            // error message or try to resend the message
//            $e->getDebug();
//        }
//    }

}

