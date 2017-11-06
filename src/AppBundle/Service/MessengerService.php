<?php
/**
 * Created by PhpStorm.
 * User: jobvink
 * Date: 06-11-17
 * Time: 18:07
 */

namespace AppBundle\Service;


class MessengerService
{
    private static $messenger;
    private $messages = [];

    final private function __construct(){}
    final private function __clone(){}
    final private function __wakeup(){}

    public static function getInstance() : MessengerService {
        if (!MessengerService::$messenger instanceof self) {
            MessengerService::$messenger = new self();
        }
        return MessengerService::$messenger;
    }

    public function add(Message $message) {
        array_push($this->messages, $message);
    }

    public function render() {
        $messages = [];
        foreach ($this->messages as $message){
            /** @var Message $message */
            array_push($messages, $message->get());
            array_shift($this->messages);
        }
        return $messages;
    }

}