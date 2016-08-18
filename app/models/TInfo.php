<?php

class TInfo extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $index;

    /**
     *
     * @var string
     */
    protected $corporate_id;

    /**
     *
     * @var string
     */
    protected $message;

    /**
     *
     * @var integer
     */
    protected $display_order;

    /**
     *
     * @var string
     */
    protected $open_date;

    /**
     *
     * @var string
     */
    protected $close_date;

    /**
     *
     * @var string
     */
    protected $rgst_date;

    /**
     *
     * @var string
     */
    protected $rgst_user_id;

    /**
     *
     * @var string
     */
    protected $upd_date;

    /**
     *
     * @var string
     */
    protected $upd_user_id;

    /**
     * Method to set the value of field index
     *
     * @param integer $index
     * @return $this
     */
    public function setIndex($index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * Method to set the value of field corporate_id
     *
     * @param string $corporate_id
     * @return $this
     */
    public function setCorporateId($corporate_id)
    {
        $this->corporate_id = $corporate_id;

        return $this;
    }

    /**
     * Method to set the value of field message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Method to set the value of field display_order
     *
     * @param integer $display_order
     * @return $this
     */
    public function setDisplayOrder($display_order)
    {
        $this->display_order = $display_order;

        return $this;
    }

    /**
     * Method to set the value of field open_date
     *
     * @param string $open_date
     * @return $this
     */
    public function setOpenDate($open_date)
    {
        $this->open_date = $open_date;

        return $this;
    }

    /**
     * Method to set the value of field close_date
     *
     * @param string $close_date
     * @return $this
     */
    public function setCloseDate($close_date)
    {
        $this->close_date = $close_date;

        return $this;
    }

    /**
     * Method to set the value of field rgst_date
     *
     * @param string $rgst_date
     * @return $this
     */
    public function setRgstDate($rgst_date)
    {
        $this->rgst_date = $rgst_date;

        return $this;
    }

    /**
     * Method to set the value of field rgst_user_id
     *
     * @param string $rgst_user_id
     * @return $this
     */
    public function setRgstUserId($rgst_user_id)
    {
        $this->rgst_user_id = $rgst_user_id;

        return $this;
    }

    /**
     * Method to set the value of field upd_date
     *
     * @param string $upd_date
     * @return $this
     */
    public function setUpdDate($upd_date)
    {
        $this->upd_date = $upd_date;

        return $this;
    }

    /**
     * Method to set the value of field upd_user_id
     *
     * @param string $upd_user_id
     * @return $this
     */
    public function setUpdUserId($upd_user_id)
    {
        $this->upd_user_id = $upd_user_id;

        return $this;
    }

    /**
     * Returns the value of field index
     *
     * @return integer
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Returns the value of field corporate_id
     *
     * @return string
     */
    public function getCorporateId()
    {
        return $this->corporate_id;
    }

    /**
     * Returns the value of field message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the value of field display_order
     *
     * @return integer
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * Returns the value of field open_date
     *
     * @return string
     */
    public function getOpenDate()
    {
        return $this->open_date;
    }

    /**
     * Returns the value of field close_date
     *
     * @return string
     */
    public function getCloseDate()
    {
        return $this->close_date;
    }

    /**
     * Returns the value of field rgst_date
     *
     * @return string
     */
    public function getRgstDate()
    {
        return $this->rgst_date;
    }

    /**
     * Returns the value of field rgst_user_id
     *
     * @return string
     */
    public function getRgstUserId()
    {
        return $this->rgst_user_id;
    }

    /**
     * Returns the value of field upd_date
     *
     * @return string
     */
    public function getUpdDate()
    {
        return $this->upd_date;
    }

    /**
     * Returns the value of field upd_user_id
     *
     * @return string
     */
    public function getUpdUserId()
    {
        return $this->upd_user_id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return TInfo[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return TInfo
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 't_info';
    }

}
