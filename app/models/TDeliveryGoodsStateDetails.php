<?php

class TDeliveryGoodsStateDetails extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    protected $corporate_id;

    /**
     *
     * @var string
     */
    protected $ship_no;

    /**
     *
     * @var integer
     */
    protected $ship_line_no;

    /**
     *
     * @var string
     */
    protected $individual_ctrl_no;

    /**
     *
     * @var integer
     */
    protected $quantity;

    /**
     *
     * @var string
     */
    protected $receipt_status;

    /**
     *
     * @var string
     */
    protected $receipt_date;

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
     * Method to set the value of field ship_no
     *
     * @param string $ship_no
     * @return $this
     */
    public function setShipNo($ship_no)
    {
        $this->ship_no = $ship_no;

        return $this;
    }

    /**
     * Method to set the value of field ship_line_no
     *
     * @param integer $ship_line_no
     * @return $this
     */
    public function setShipLineNo($ship_line_no)
    {
        $this->ship_line_no = $ship_line_no;

        return $this;
    }

    /**
     * Method to set the value of field individual_ctrl_no
     *
     * @param string $individual_ctrl_no
     * @return $this
     */
    public function setIndividualCtrlNo($individual_ctrl_no)
    {
        $this->individual_ctrl_no = $individual_ctrl_no;

        return $this;
    }

    /**
     * Method to set the value of field quantity
     *
     * @param integer $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Method to set the value of field receipt_status
     *
     * @param string $receipt_status
     * @return $this
     */
    public function setReceiptStatus($receipt_status)
    {
        $this->receipt_status = $receipt_status;

        return $this;
    }

    /**
     * Method to set the value of field receipt_date
     *
     * @param string $receipt_date
     * @return $this
     */
    public function setReceiptDate($receipt_date)
    {
        $this->receipt_date = $receipt_date;

        return $this;
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
     * Returns the value of field ship_no
     *
     * @return string
     */
    public function getShipNo()
    {
        return $this->ship_no;
    }

    /**
     * Returns the value of field ship_line_no
     *
     * @return integer
     */
    public function getShipLineNo()
    {
        return $this->ship_line_no;
    }

    /**
     * Returns the value of field individual_ctrl_no
     *
     * @return string
     */
    public function getIndividualCtrlNo()
    {
        return $this->individual_ctrl_no;
    }

    /**
     * Returns the value of field quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Returns the value of field receipt_status
     *
     * @return string
     */
    public function getReceiptStatus()
    {
        return $this->receipt_status;
    }

    /**
     * Returns the value of field receipt_date
     *
     * @return string
     */
    public function getReceiptDate()
    {
        return $this->receipt_date;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 't_delivery_goods_state_details';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return TDeliveryGoodsStateDetails[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return TDeliveryGoodsStateDetails
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
