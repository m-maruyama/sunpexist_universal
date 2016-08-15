<?php

class TOrderState extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    protected $t_order_state_comb_hkey;

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
    protected $order_req_ymd;

    /**
     *
     * @var string
     */
    protected $rec_order_ymd;

    /**
     *
     * @var string
     */
    protected $rntl_cont_no;

    /**
     *
     * @var string
     */
    protected $rntl_sect_cd;

    /**
     *
     * @var string
     */
    protected $job_type_cd;

    /**
     *
     * @var string
     */
    protected $job_type_item_cd;

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
    protected $size_two_cd;

    /**
     *
     * @var string
     */
    protected $whse_cd;

    /**
     *
     * @var string
     */
    protected $stk_usr_cd;

    /**
     *
     * @var string
     */
    protected $stk_usr_brnch_cd;

    /**
     *
     * @var string
     */
    protected $ship_to_cd;

    /**
     *
     * @var string
     */
    protected $ship_to_brnch_cd;

    /**
     *
     * @var string
     */
    protected $cust_to_brnch_name1;

    /**
     *
     * @var string
     */
    protected $cust_to_brnch_name2;

    /**
     *
     * @var string
     */
    protected $zip_no;

    /**
     *
     * @var string
     */
    protected $address1;

    /**
     *
     * @var string
     */
    protected $address2;

    /**
     *
     * @var string
     */
    protected $address3;

    /**
     *
     * @var string
     */
    protected $address4;

    /**
     *
     * @var integer
     */
    protected $order_qty;

    /**
     *
     * @var integer
     */
    protected $ship_qty;

    /**
     *
     * @var string
     */
    protected $ship_ymd;

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
    protected $m_item_comb_hkey;

    /**
     *
     * @var string
     */
    protected $m_job_type_comb_hkey;

    /**
     *
     * @var string
     */
    protected $m_section_comb_hkey;

    /**
     *
     * @var string
     */
    protected $t_order_comb_hkey;

    /**
     *
     * @var string
     */
    protected $m_wearer_item_comb_hkey;

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
     * Method to set the value of field order_req_ymd
     *
     * @param string $order_req_ymd
     * @return $this
     */
    public function setOrderReqYmd($order_req_ymd)
    {
        $this->order_req_ymd = $order_req_ymd;

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
     * Method to set the value of field job_type_cd
     *
     * @param string $job_type_cd
     * @return $this
     */
    public function setJobTypeCd($job_type_cd)
    {
        $this->job_type_cd = $job_type_cd;

        return $this;
    }

    /**
     * Method to set the value of field job_type_item_cd
     *
     * @param string $job_type_item_cd
     * @return $this
     */
    public function setJobTypeItemCd($job_type_item_cd)
    {
        $this->job_type_item_cd = $job_type_item_cd;

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
     * Method to set the value of field size_two_cd
     *
     * @param string $size_two_cd
     * @return $this
     */
    public function setSizeTwoCd($size_two_cd)
    {
        $this->size_two_cd = $size_two_cd;

        return $this;
    }

    /**
     * Method to set the value of field whse_cd
     *
     * @param string $whse_cd
     * @return $this
     */
    public function setWhseCd($whse_cd)
    {
        $this->whse_cd = $whse_cd;

        return $this;
    }

    /**
     * Method to set the value of field stk_usr_cd
     *
     * @param string $stk_usr_cd
     * @return $this
     */
    public function setStkUsrCd($stk_usr_cd)
    {
        $this->stk_usr_cd = $stk_usr_cd;

        return $this;
    }

    /**
     * Method to set the value of field stk_usr_brnch_cd
     *
     * @param string $stk_usr_brnch_cd
     * @return $this
     */
    public function setStkUsrBrnchCd($stk_usr_brnch_cd)
    {
        $this->stk_usr_brnch_cd = $stk_usr_brnch_cd;

        return $this;
    }

    /**
     * Method to set the value of field ship_to_cd
     *
     * @param string $ship_to_cd
     * @return $this
     */
    public function setShipToCd($ship_to_cd)
    {
        $this->ship_to_cd = $ship_to_cd;

        return $this;
    }

    /**
     * Method to set the value of field ship_to_brnch_cd
     *
     * @param string $ship_to_brnch_cd
     * @return $this
     */
    public function setShipToBrnchCd($ship_to_brnch_cd)
    {
        $this->ship_to_brnch_cd = $ship_to_brnch_cd;

        return $this;
    }

    /**
     * Method to set the value of field cust_to_brnch_name1
     *
     * @param string $cust_to_brnch_name1
     * @return $this
     */
    public function setCustToBrnchName1($cust_to_brnch_name1)
    {
        $this->cust_to_brnch_name1 = $cust_to_brnch_name1;

        return $this;
    }

    /**
     * Method to set the value of field cust_to_brnch_name2
     *
     * @param string $cust_to_brnch_name2
     * @return $this
     */
    public function setCustToBrnchName2($cust_to_brnch_name2)
    {
        $this->cust_to_brnch_name2 = $cust_to_brnch_name2;

        return $this;
    }

    /**
     * Method to set the value of field zip_no
     *
     * @param string $zip_no
     * @return $this
     */
    public function setZipNo($zip_no)
    {
        $this->zip_no = $zip_no;

        return $this;
    }

    /**
     * Method to set the value of field address1
     *
     * @param string $address1
     * @return $this
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * Method to set the value of field address2
     *
     * @param string $address2
     * @return $this
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Method to set the value of field address3
     *
     * @param string $address3
     * @return $this
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;

        return $this;
    }

    /**
     * Method to set the value of field address4
     *
     * @param string $address4
     * @return $this
     */
    public function setAddress4($address4)
    {
        $this->address4 = $address4;

        return $this;
    }

    /**
     * Method to set the value of field order_qty
     *
     * @param integer $order_qty
     * @return $this
     */
    public function setOrderQty($order_qty)
    {
        $this->order_qty = $order_qty;

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
     * Method to set the value of field m_job_type_comb_hkey
     *
     * @param string $m_job_type_comb_hkey
     * @return $this
     */
    public function setMJobTypeCombHkey($m_job_type_comb_hkey)
    {
        $this->m_job_type_comb_hkey = $m_job_type_comb_hkey;

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
     * Method to set the value of field t_order_comb_hkey
     *
     * @param string $t_order_comb_hkey
     * @return $this
     */
    public function setTOrderCombHkey($t_order_comb_hkey)
    {
        $this->t_order_comb_hkey = $t_order_comb_hkey;

        return $this;
    }

    /**
     * Method to set the value of field m_wearer_item_comb_hkey
     *
     * @param string $m_wearer_item_comb_hkey
     * @return $this
     */
    public function setMWearerItemCombHkey($m_wearer_item_comb_hkey)
    {
        $this->m_wearer_item_comb_hkey = $m_wearer_item_comb_hkey;

        return $this;
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
     * Returns the value of field order_req_ymd
     *
     * @return string
     */
    public function getOrderReqYmd()
    {
        return $this->order_req_ymd;
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
     * Returns the value of field rntl_cont_no
     *
     * @return string
     */
    public function getRntlContNo()
    {
        return $this->rntl_cont_no;
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
     * Returns the value of field job_type_cd
     *
     * @return string
     */
    public function getJobTypeCd()
    {
        return $this->job_type_cd;
    }

    /**
     * Returns the value of field job_type_item_cd
     *
     * @return string
     */
    public function getJobTypeItemCd()
    {
        return $this->job_type_item_cd;
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
     * Returns the value of field size_two_cd
     *
     * @return string
     */
    public function getSizeTwoCd()
    {
        return $this->size_two_cd;
    }

    /**
     * Returns the value of field whse_cd
     *
     * @return string
     */
    public function getWhseCd()
    {
        return $this->whse_cd;
    }

    /**
     * Returns the value of field stk_usr_cd
     *
     * @return string
     */
    public function getStkUsrCd()
    {
        return $this->stk_usr_cd;
    }

    /**
     * Returns the value of field stk_usr_brnch_cd
     *
     * @return string
     */
    public function getStkUsrBrnchCd()
    {
        return $this->stk_usr_brnch_cd;
    }

    /**
     * Returns the value of field ship_to_cd
     *
     * @return string
     */
    public function getShipToCd()
    {
        return $this->ship_to_cd;
    }

    /**
     * Returns the value of field ship_to_brnch_cd
     *
     * @return string
     */
    public function getShipToBrnchCd()
    {
        return $this->ship_to_brnch_cd;
    }

    /**
     * Returns the value of field cust_to_brnch_name1
     *
     * @return string
     */
    public function getCustToBrnchName1()
    {
        return $this->cust_to_brnch_name1;
    }

    /**
     * Returns the value of field cust_to_brnch_name2
     *
     * @return string
     */
    public function getCustToBrnchName2()
    {
        return $this->cust_to_brnch_name2;
    }

    /**
     * Returns the value of field zip_no
     *
     * @return string
     */
    public function getZipNo()
    {
        return $this->zip_no;
    }

    /**
     * Returns the value of field address1
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Returns the value of field address2
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Returns the value of field address3
     *
     * @return string
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * Returns the value of field address4
     *
     * @return string
     */
    public function getAddress4()
    {
        return $this->address4;
    }

    /**
     * Returns the value of field order_qty
     *
     * @return integer
     */
    public function getOrderQty()
    {
        return $this->order_qty;
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
     * Returns the value of field ship_ymd
     *
     * @return string
     */
    public function getShipYmd()
    {
        return $this->ship_ymd;
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
     * Returns the value of field m_item_comb_hkey
     *
     * @return string
     */
    public function getMItemCombHkey()
    {
        return $this->m_item_comb_hkey;
    }

    /**
     * Returns the value of field m_job_type_comb_hkey
     *
     * @return string
     */
    public function getMJobTypeCombHkey()
    {
        return $this->m_job_type_comb_hkey;
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
     * Returns the value of field t_order_comb_hkey
     *
     * @return string
     */
    public function getTOrderCombHkey()
    {
        return $this->t_order_comb_hkey;
    }

    /**
     * Returns the value of field m_wearer_item_comb_hkey
     *
     * @return string
     */
    public function getMWearerItemCombHkey()
    {
        return $this->m_wearer_item_comb_hkey;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->belongsTo("t_order_comb_hkey", "TOrder", "t_order_comb_hkey");
        $this->belongsTo("m_wearer_item_comb_hkey", "MWearerItem", "m_wearer_item_comb_hkey");
        // $this->belongsTo("t_order_state_comb_hkey", "TDeliveryGoodsState", "t_order_state_comb_hkey");
        $this->hasMany('t_order_state_comb_hkey', 'TDeliveryGoodsState', 't_order_state_comb_hkey');
        // $this->hasOne('t_order_comb_hkey', 'TOrder', 't_order_comb_hkey');
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 't_order_state';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return TOrderState[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return TOrderState
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
