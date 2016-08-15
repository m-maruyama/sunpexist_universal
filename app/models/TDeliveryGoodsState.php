<?php

class TDeliveryGoodsState extends \Phalcon\Mvc\Model
{

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
    protected $rec_order_no;

    /**
     *
     * @var integer
     */
    protected $rec_order_line_no;

    /**
     *
     * @var string
     */
    protected $rec_order_ymd;

    /**
     *
     * @var string
     */
    protected $ship_ymd;

    /**
     *
     * @var integer
     */
    protected $ship_qty;

    /**
     *
     * @var string
     */
    protected $deli_slip_no;

    /**
     *
     * @var string
     */
    protected $snd_kbn;

    /**
     *
     * @var string
     */
    protected $snd_date;

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
     *
     * @var string
     */
    protected $upd_pg_id;

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
     *
     * @var string
     */
    protected $t_order_state_comb_hkey;

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
     * Method to set the value of field rec_order_no
     *
     * @param string $rec_order_no
     * @return $this
     */
    public function setRecOrderNo($rec_order_no)
    {
        $this->rec_order_no = $rec_order_no;

        return $this;
    }

    /**
     * Method to set the value of field rec_order_line_no
     *
     * @param integer $rec_order_line_no
     * @return $this
     */
    public function setRecOrderLineNo($rec_order_line_no)
    {
        $this->rec_order_line_no = $rec_order_line_no;

        return $this;
    }

    /**
     * Method to set the value of field rec_order_ymd
     *
     * @param string $rec_order_ymd
     * @return $this
     */
    public function setRecOrderYmd($rec_order_ymd)
    {
        $this->rec_order_ymd = $rec_order_ymd;

        return $this;
    }

    /**
     * Method to set the value of field ship_ymd
     *
     * @param string $ship_ymd
     * @return $this
     */
    public function setShipYmd($ship_ymd)
    {
        $this->ship_ymd = $ship_ymd;

        return $this;
    }

    /**
     * Method to set the value of field ship_qty
     *
     * @param integer $ship_qty
     * @return $this
     */
    public function setShipQty($ship_qty)
    {
        $this->ship_qty = $ship_qty;

        return $this;
    }

    /**
     * Method to set the value of field deli_slip_no
     *
     * @param string $deli_slip_no
     * @return $this
     */
    public function setDeliSlipNo($deli_slip_no)
    {
        $this->deli_slip_no = $deli_slip_no;

        return $this;
    }

    /**
     * Method to set the value of field snd_kbn
     *
     * @param string $snd_kbn
     * @return $this
     */
    public function setSndKbn($snd_kbn)
    {
        $this->snd_kbn = $snd_kbn;

        return $this;
    }

    /**
     * Method to set the value of field snd_date
     *
     * @param string $snd_date
     * @return $this
     */
    public function setSndDate($snd_date)
    {
        $this->snd_date = $snd_date;

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
     * Method to set the value of field upd_pg_id
     *
     * @param string $upd_pg_id
     * @return $this
     */
    public function setUpdPgId($upd_pg_id)
    {
        $this->upd_pg_id = $upd_pg_id;

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
     * Method to set the value of field t_order_state_comb_hkey
     *
     * @param string $t_order_state_comb_hkey
     * @return $this
     */
    public function setTOrderStateCombHkey($t_order_state_comb_hkey)
    {
        $this->t_order_state_comb_hkey = $t_order_state_comb_hkey;

        return $this;
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
     * Returns the value of field rec_order_no
     *
     * @return string
     */
    public function getRecOrderNo()
    {
        return $this->rec_order_no;
    }

    /**
     * Returns the value of field rec_order_line_no
     *
     * @return integer
     */
    public function getRecOrderLineNo()
    {
        return $this->rec_order_line_no;
    }

    /**
     * Returns the value of field rec_order_ymd
     *
     * @return string
     */
    public function getRecOrderYmd()
    {
        return $this->rec_order_ymd;
    }

    /**
     * Returns the value of field ship_ymd
     *
     * @return string
     */
    public function getShipYmd()
    {
        return $this->ship_ymd;
    }

    /**
     * Returns the value of field ship_qty
     *
     * @return integer
     */
    public function getShipQty()
    {
        return $this->ship_qty;
    }

    /**
     * Returns the value of field deli_slip_no
     *
     * @return string
     */
    public function getDeliSlipNo()
    {
        return $this->deli_slip_no;
    }

    /**
     * Returns the value of field snd_kbn
     *
     * @return string
     */
    public function getSndKbn()
    {
        return $this->snd_kbn;
    }

    /**
     * Returns the value of field snd_date
     *
     * @return string
     */
    public function getSndDate()
    {
        return $this->snd_date;
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
     * Returns the value of field upd_pg_id
     *
     * @return string
     */
    public function getUpdPgId()
    {
        return $this->upd_pg_id;
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
     * Returns the value of field t_order_state_comb_hkey
     *
     * @return string
     */
    public function getTOrderStateCombHkey()
    {
        return $this->t_order_state_comb_hkey;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->belongsTo("t_order_state_comb_hkey", "TOrderState", "t_order_state_comb_hkey");
        // $this->hasOne('t_order_state_comb_hkey', 'TOrderState', 't_order_state_comb_hkey');
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 't_delivery_goods_state';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return TDeliveryGoodsState[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return TDeliveryGoodsState
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
