<?php

class EpgItem implements JsonSerializable
{
    private $chn;
    private $ref;
    private $start;
    private $end;
    private $title;
    private $restriction;

    /**
     * EpgItem constructor.
     * @param $chn
     * @param $ref
     * @param $start DateTime
     * @param $end DateTime
     * @param $title
     * @param $restriction
     */
    public function __construct($chn, $ref, $start, $end, $title, $restriction)
    {
        $this->chn = $chn;
        $this->ref = $ref;
        $this->start = $start;
        $this->end = $end;
        $this->title = $title;
        $this->restriction = $restriction;
    }

    /**
     * @return mixed
     */
    public function getChn()
    {
        return $this->chn;
    }

    /**
     * @param mixed $chn
     */
    public function setChn($chn)
    {
        $this->chn = $chn;
    }

    /**
     * @return mixed
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param mixed $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * @return DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param DateTime $start
     */
    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @return DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param DateTime $end
     */
    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getRestriction()
    {
        return $this->restriction;
    }

    /**
     * @param mixed $restriction
     */
    public function setRestriction($restriction)
    {
        $this->restriction = $restriction;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return [
            'i' => $this->ref,
            's' => $this->start->format('Y-m-d H:i:s'),
            'e' => $this->end->format('Y-m-d H:i:s'),
            't' => $this->title,
            'r' => strval($this->restriction)
        ];
    }
}