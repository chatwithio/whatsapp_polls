<?php

// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Entity\Messages;
use App\Message\WhatsappNotification;
use App\Service\MessageService;
use DateTimeInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Repository\MessagesRepository;
use App\Service\BotService;
use App\Service\WhatsappService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Message;

#[AsMessageHandler]
class WhatsappNotificationHandler
{
    private $logger;
    private  $em;
    private $messageService;
    private $doctrine;

    public function __construct(LoggerInterface $logger, MessageService $messageService, EntityManagerInterface $em, EntityManagerInterface $doctrine)
    {
        $this->logger = $logger;
        $this->messageService = $messageService;
        $this->em = $em;
        $this->doctrine = $doctrine;
    }

    public function __invoke(WhatsappNotification $message)
    {
        $content = $message->getContent();
        $jsonDecodedMessage = json_decode($content);

       // $this->logger->info("Message sent!");
        $mensaje = $jsonDecodedMessage->{'contacts'};


        $previousMessage = $this->getPreviousMessage($mensaje[0]->wa_id);

        if($previousMessage){

            $this->textType= -1;

            //decide what/if we send a message

            $textToBeSent =  $this->gettextToBeSent($previousMessage, $jsonDecodedMessage);
            //send the message

            $m = $this->sendMessage($textToBeSent, $jsonDecodedMessage);

            //save the data to the database
            $this->saveData($m);

            //wait 2 hours until we send the results of the poll
            sleep(10); //7200 secs = 2 hours

            //send results form the poll and then store the message
            $result = $this->resultsPoll($m);
            $this->saveData($result);
        }


    }

    private function getPreviousMessage($waId){
        return $this->em->getRepository(Messages::class)->findOneBy([
            'wa_id' => $waId
        ],
            [
                'id' => 'DESC'
            ]);
    }

    private function gettextToBeSent($previousMessage, $message)
    {
        $prevText = $previousMessage->getText();

        $msg_messages = $message->{'messages'};
        $textMessage = $msg_messages[0]->text->body;
        $textToBeSent = 'This is not a valid answer, sorry';
        $textType=-1;


        if ($prevText == '1') {  //get text of the next message depending on the previous message
            $textToBeSent = "Thanks for answering the poll!";
            $this->textType=01;
        }else if($prevText == '2'){
            $textToBeSent = "Thanks for answering the poll!";
            $this->textType=02;
        }else if($prevText == '3'){
            $textToBeSent = "Thanks for answering the poll!";
            $this->textType=03;
        } else if($prevText == '4'){
            $textToBeSent = "Thanks for answering the poll!";
            $this->textType=04;
        }

        return $textToBeSent;
    }

    private function sendMessage($textToBeSent,$message){  //send message with text depending on previous message
        $msg = $message->{'contacts'};
        $this->messageService->sendWhatsAppText(
            $msg[0]->wa_id,
            $textToBeSent
        );
        return $message;
    }

    private function saveData($message){ //save data of the sent message

        $msg_contacts = $message -> {'contacts'};
        $msg_messages = $message ->{'messages'};
        $messages = new Messages();
        $messages->setWaId($msg_contacts[0]->wa_id);
        $messages->setText($msg_messages[0]->text->body); // "template"
        $messages->setIdText($this->textType);
        $this->em->persist($messages);
        $this->em->flush();
    }
    private function resultsPoll($message){
        //count results of the poll with a query

        $countRed =  $this->em->getRepository(Messages::class)->count(array('text' => 1));
        $countBlue = $this->em->getRepository(Messages::class)->count(array('text' => 2));
        $countYellow = $this->em->getRepository(Messages::class)->count(array('text' => 3));
        $countGreen = $this->em->getRepository(Messages::class)->count(array('text' => 4));

        $resultsPoll = "The results of the poll are the followings
        Red: $countRed votes.
        Blue: $countBlue votes.
        Yellow: $countYellow votes.
        Green: $countGreen votes.";

        $this->textType=1;

        $msg = $message->{'contacts'};

        $this->messageService->sendWhatsAppText(
            $msg[0]->wa_id,
            $resultsPoll
        );
        return $message;


    }
}
