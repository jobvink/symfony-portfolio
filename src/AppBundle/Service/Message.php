<?php
/**
 * Created by PhpStorm.
 * User: jobvink
 * Date: 06-11-17
 * Time: 18:23
 */

namespace AppBundle\Service;


class Message
{
    private $type;
    private $message;

    /**
     * Message constructor.
     * @param $type
     * @param $message
     */
    public function __construct($type = null, $message = null)
    {
        $this->type = $type;
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function get()
    {
        return [
            'type' => $this->type,
            'message' => $this->message
        ];
    }

}