<?php

class TmpTReturnedResults extends \Phalcon\Mvc\Model
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
    protected $order_no;

    /**
     *
     * @var string
     */
    protected $item_cd;

    /**
     *
     * @var string
     */
    protected $color_cd;

    /**
     *
     * @var string
     */
    protected $size_cd;

    /**
     *
     * @var string
     */
    protected $cster_emply_cd;

    /**
     *
     * @var string
     */
    protected $rntl_sect_cd;

    /**
     *
     * @var string
     */
    protected $rent_pattern_code;

    /**
     *
     * @var string
     */
    protected $order_date;

    /**
     *
     * @var string
     */
    protected $return_date;

    /**
     *
     * @var integer
     */
    protected $return_qty;

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
     * Method to set the value of field order_no
     *
     * @param string $order_no
     * @return $this
     */
    public function setOrderNo($order_no)
    {
        $this->order_no = $order_no;

        return $this;
    }

    /**
     * Method to set the value of field item_cd
     *
     * @param string $item_cd
     * @return $this
     */
    public function setItemCd($item_cd)
    {
        $this->item_cd = $item_cd;

        return $this;
    }

    /**
     * Method to set the value of field color_cd
     *
     * @param string $color_cd
     * @return $this
     */
    public function setColorCd($color_cd)
    {
        $this->color_cd = $color_cd;

        return $this;
    }

    /**
     * Method to set the value of field size_cd
     *
     * @param string $size_cd
     * @return $this
     */
    public function setSizeCd($size_cd)
    {
        $this->size_cd = $size_cd;

        return $this;
    }

    /**
     * Method to set the value of field cster_emply_cd
     *
     * @param string $cster_emply_cd
     * @return $this
     */
    public function setCsterEmplyCd($cster_emply_cd)
    {
        $this->cster_emply_cd = $cster_emply_cd;

        return $this;
    }

    /**
     * Method to set the value of field rntl_sect_cd
     *
     * @param string $rntl_sect_cd
     * @return $this
     */
    public function setRntlSectCd($rntl_sect_cd)
    {
        $this->rntl_sect_cd = $rntl_sect_cd;

        return $this;
    }

    /**
     * Method to set the value of field rent_pattern_code
     *
     * @param string $rent_pattern_code
     * @return $this
     */
    public function setRentPatternCode($rent_pattern_code)
    {
        $this->rent_pattern_code = $rent_pattern_code;

        return $this;
    }

    /**
     * Method to set the value of field order_date
     *
     * @param string $order_date
     * @return $this
     */
    public function setOrderDate($order_date)
    {
        $this->order_date = $order_date;

        return $this;
    }

    /**
     * Method to set the value of field return_date
     *
     * @param string $return_date
     * @return $this
     */
    public function setreturnDate($return_date)
    {
        $this->return_date = $return_date;

        return $this;
    }

    /**
     * Method to set the value of field return_qty
     *
     * @param integer $return_qty
     * @return $this
     */
    public function setreturnQty($return_qty)
    {
        $this->return_qty = $return_qty;

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
     * Returns the value of field order_no
     *
     * @return string
     */
    public function getOrderNo()
    {
        return $this->order_no;
    }

    /**
     * Returns the value of field item_cd
     *
     * @return string
     */
    public function getItemCd()
    {
        return $this->item_cd;
    }

    /**
     * Returns the value of field color_cd
     *
     * @return string
     */
    public function getColorCd()
    {
        return $this->color_cd;
    }

    /**
     * Returns the value of field size_cd
     *
     * @return string
     */
    public function getSizeCd()
    {
        return $this->size_cd;
    }

    /**
     * Returns the value of field cster_emply_cd
     *
     * @return string
     */
    public function getCsterEmplyCd()
    {
        return $this->cster_emply_cd;
    }

    /**
     * Returns the value of field rntl_sect_cd
     *
     * @return string
     */
    public function getRntlSectCd()
    {
        return $this->rntl_sect_cd;
    }

    /**
     * Returns the value of field rent_pattern_code
     *
     * @return string
     */
    public function getRentPatternCode()
    {
        return $this->rent_pattern_code;
    }

    /**
     * Returns the value of field order_date
     *
     * @return string
     */
    public function getOrderDate()
    {
        return $this->order_date;
    }

    /**
     * Returns the value of field return_date
     *
     * @return string
     */
    public function getreturnDate()
    {
        return $this->return_date;
    }

    /**
     * Returns the value of field return_qty
     *
     * @return integer
     */
    public function getreturnQty()
    {
        return $this->return_qty;
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
        return 'tmp_t_returned_results';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return TmpTReturnedResults[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return TmpTReturnedResults
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
