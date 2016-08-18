<?php

class TReturnedResults extends \Phalcon\Mvc\Model
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
    protected $order_req_no;

    /**
     *
     * @var integer
     */
    protected $order_req_line_no;

    /**
     *
     * @var string
     */
    protected $rntl_cont_no;

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
    protected $werer_cd;

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
     *
     * @var string
     */
    protected $m_item_comb_hkey;

    /**
     *
     * @var string
     */
    protected $t_returned_plan_info_comb_hkey;

    /**
     *
     * @var string
     */
    protected $m_section_comb_hkey;

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
     * Method to set the value of field order_req_no
     *
     * @param string $order_req_no
     * @return $this
     */
    public function setOrderReqNo($order_req_no)
    {
        $this->order_req_no = $order_req_no;

        return $this;
    }

    /**
     * Method to set the value of field order_req_line_no
     *
     * @param integer $order_req_line_no
     * @return $this
     */
    public function setOrderReqLineNo($order_req_line_no)
    {
        $this->order_req_line_no = $order_req_line_no;

        return $this;
    }

    /**
     * Method to set the value of field rntl_cont_no
     *
     * @param string $rntl_cont_no
     * @return $this
     */
    public function setRntlContNo($rntl_cont_no)
    {
        $this->rntl_cont_no = $rntl_cont_no;

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
     * Method to set the value of field werer_cd
     *
     * @param string $werer_cd
     * @return $this
     */
    public function setWererCd($werer_cd)
    {
        $this->werer_cd = $werer_cd;

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
    public function setReturnDate($return_date)
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
    public function setReturnQty($return_qty)
    {
        $this->return_qty = $return_qty;

        return $this;
    }

    /**
     * Method to set the value of field m_item_comb_hkey
     *
     * @param string $m_item_comb_hkey
     * @return $this
     */
    public function setMItemCombHkey($m_item_comb_hkey)
    {
        $this->m_item_comb_hkey = $m_item_comb_hkey;

        return $this;
    }

    /**
     * Method to set the value of field t_returned_plan_info_comb_hkey
     *
     * @param string $t_returned_plan_info_comb_hkey
     * @return $this
     */
    public function setTReturnedPlanInfoCombHkey($t_returned_plan_info_comb_hkey)
    {
        $this->t_returned_plan_info_comb_hkey = $t_returned_plan_info_comb_hkey;

        return $this;
    }

    /**
     * Method to set the value of field m_section_comb_hkey
     *
     * @param string $m_section_comb_hkey
     * @return $this
     */
    public function setMSectionCombHkey($m_section_comb_hkey)
    {
        $this->m_section_comb_hkey = $m_section_comb_hkey;

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
     * Returns the value of field order_req_no
     *
     * @return string
     */
    public function getOrderReqNo()
    {
        return $this->order_req_no;
    }

    /**
     * Returns the value of field order_req_line_no
     *
     * @return integer
     */
    public function getOrderReqLineNo()
    {
        return $this->order_req_line_no;
    }

    /**
     * Returns the value of field rntl_cont_no
     *
     * @return string
     */
    public function getRntlContNo()
    {
        return $this->rntl_cont_no;
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
     * Returns the value of field werer_cd
     *
     * @return string
     */
    public function getWererCd()
    {
        return $this->werer_cd;
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
    public function getReturnDate()
    {
        return $this->return_date;
    }

    /**
     * Returns the value of field return_qty
     *
     * @return integer
     */
    public function getReturnQty()
    {
        return $this->return_qty;
    }

    /**
     * Returns the value of field m_item_comb_hkey
     *
     * @return string
     */
    public function getMItemCombHkey()
    {
        return $this->m_item_comb_hkey;
    }

    /**
     * Returns the value of field t_returned_plan_info_comb_hkey
     *
     * @return string
     */
    public function getTReturnedPlanInfoCombHkey()
    {
        return $this->t_returned_plan_info_comb_hkey;
    }

    /**
     * Returns the value of field m_section_comb_hkey
     *
     * @return string
     */
    public function getMSectionCombHkey()
    {
        return $this->m_section_comb_hkey;
    }

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
        $this->belongsTo('t_returned_plan_info_comb_hkey', 'TReturnedPlanInfo', 't_returned_plan_info_comb_hkey');
        $this->hasOne('m_item_comb_hkey', 'MItem', 'm_item_comb_hkey');
        $this->hasOne('m_section_comb_hkey', 'MSection', 'm_section_comb_hkey');
        $this->hasOne('rent_pattern_code', 'MJobType', 'job_type_cd');
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return TReturnedResults[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return TReturnedResults
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
        return 't_returned_results';
    }

}
