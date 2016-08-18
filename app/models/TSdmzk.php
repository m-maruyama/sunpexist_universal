<?php

class TSdmzk extends \Phalcon\Mvc\Model
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
    protected $rntl_cont_no;

    /**
     *
     * @var string
     */
    protected $zkzkcd;

    /**
     *
     * @var string
     */
    protected $zkwhcd;

    /**
     *
     * @var string
     */
    protected $zkprcd;

    /**
     *
     * @var string
     */
    protected $zkclor;

    /**
     *
     * @var integer
     */
    protected $zksize_display_order;

    /**
     *
     * @var string
     */
    protected $zksize;

    /**
     *
     * @var string
     */
    protected $label;

    /**
     *
     * @var string
     */
    protected $zk_status_cd;

    /**
     *
     * @var integer
     */
    protected $total_qty;

    /**
     *
     * @var integer
     */
    protected $new_qty;

    /**
     *
     * @var integer
     */
    protected $used_qty;

    /**
     *
     * @var integer
     */
    protected $rtn_proc_qty;

    /**
     *
     * @var integer
     */
    protected $rtn_plan_qty;

    /**
     *
     * @var integer
     */
    protected $in_use_qty;

    /**
     *
     * @var integer
     */
    protected $other_ship_qty;

    /**
     *
     * @var integer
     */
    protected $discarded_qty;

    /**
     *
     * @var string
     */
    protected $rent_pattern_data;

    /**
     *
     * @var string
     */
    protected $m_item_comb_hkey;

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
     * Method to set the value of field zkzkcd
     *
     * @param string $zkzkcd
     * @return $this
     */
    public function setZkzkcd($zkzkcd)
    {
        $this->zkzkcd = $zkzkcd;

        return $this;
    }

    /**
     * Method to set the value of field zkwhcd
     *
     * @param string $zkwhcd
     * @return $this
     */
    public function setZkwhcd($zkwhcd)
    {
        $this->zkwhcd = $zkwhcd;

        return $this;
    }

    /**
     * Method to set the value of field zkprcd
     *
     * @param string $zkprcd
     * @return $this
     */
    public function setZkprcd($zkprcd)
    {
        $this->zkprcd = $zkprcd;

        return $this;
    }

    /**
     * Method to set the value of field zkclor
     *
     * @param string $zkclor
     * @return $this
     */
    public function setZkclor($zkclor)
    {
        $this->zkclor = $zkclor;

        return $this;
    }

    /**
     * Method to set the value of field zksize_display_order
     *
     * @param integer $zksize_display_order
     * @return $this
     */
    public function setZksizeDisplayOrder($zksize_display_order)
    {
        $this->zksize_display_order = $zksize_display_order;

        return $this;
    }

    /**
     * Method to set the value of field zksize
     *
     * @param string $zksize
     * @return $this
     */
    public function setZksize($zksize)
    {
        $this->zksize = $zksize;

        return $this;
    }

    /**
     * Method to set the value of field label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Method to set the value of field zk_status_cd
     *
     * @param string $zk_status_cd
     * @return $this
     */
    public function setZkStatusCd($zk_status_cd)
    {
        $this->zk_status_cd = $zk_status_cd;

        return $this;
    }

    /**
     * Method to set the value of field total_qty
     *
     * @param integer $total_qty
     * @return $this
     */
    public function setTotalQty($total_qty)
    {
        $this->total_qty = $total_qty;

        return $this;
    }

    /**
     * Method to set the value of field new_qty
     *
     * @param integer $new_qty
     * @return $this
     */
    public function setNewQty($new_qty)
    {
        $this->new_qty = $new_qty;

        return $this;
    }

    /**
     * Method to set the value of field used_qty
     *
     * @param integer $used_qty
     * @return $this
     */
    public function setUsedQty($used_qty)
    {
        $this->used_qty = $used_qty;

        return $this;
    }

    /**
     * Method to set the value of field rtn_proc_qty
     *
     * @param integer $rtn_proc_qty
     * @return $this
     */
    public function setRtnProcQty($rtn_proc_qty)
    {
        $this->rtn_proc_qty = $rtn_proc_qty;

        return $this;
    }

    /**
     * Method to set the value of field rtn_plan_qty
     *
     * @param integer $rtn_plan_qty
     * @return $this
     */
    public function setRtnPlanQty($rtn_plan_qty)
    {
        $this->rtn_plan_qty = $rtn_plan_qty;

        return $this;
    }

    /**
     * Method to set the value of field in_use_qty
     *
     * @param integer $in_use_qty
     * @return $this
     */
    public function setInUseQty($in_use_qty)
    {
        $this->in_use_qty = $in_use_qty;

        return $this;
    }

    /**
     * Method to set the value of field other_ship_qty
     *
     * @param integer $other_ship_qty
     * @return $this
     */
    public function setOtherShipQty($other_ship_qty)
    {
        $this->other_ship_qty = $other_ship_qty;

        return $this;
    }

    /**
     * Method to set the value of field discarded_qty
     *
     * @param integer $discarded_qty
     * @return $this
     */
    public function setDiscardedQty($discarded_qty)
    {
        $this->discarded_qty = $discarded_qty;

        return $this;
    }

    /**
     * Method to set the value of field rent_pattern_data
     *
     * @param string $rent_pattern_data
     * @return $this
     */
    public function setRentPatternData($rent_pattern_data)
    {
        $this->rent_pattern_data = $rent_pattern_data;

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
     * Returns the value of field corporate_id
     *
     * @return string
     */
    public function getCorporateId()
    {
        return $this->corporate_id;
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
     * Returns the value of field zkzkcd
     *
     * @return string
     */
    public function getZkzkcd()
    {
        return $this->zkzkcd;
    }

    /**
     * Returns the value of field zkwhcd
     *
     * @return string
     */
    public function getZkwhcd()
    {
        return $this->zkwhcd;
    }

    /**
     * Returns the value of field zkprcd
     *
     * @return string
     */
    public function getZkprcd()
    {
        return $this->zkprcd;
    }

    /**
     * Returns the value of field zkclor
     *
     * @return string
     */
    public function getZkclor()
    {
        return $this->zkclor;
    }

    /**
     * Returns the value of field zksize_display_order
     *
     * @return integer
     */
    public function getZksizeDisplayOrder()
    {
        return $this->zksize_display_order;
    }

    /**
     * Returns the value of field zksize
     *
     * @return string
     */
    public function getZksize()
    {
        return $this->zksize;
    }

    /**
     * Returns the value of field label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns the value of field zk_status_cd
     *
     * @return string
     */
    public function getZkStatusCd()
    {
        return $this->zk_status_cd;
    }

    /**
     * Returns the value of field total_qty
     *
     * @return integer
     */
    public function getTotalQty()
    {
        return $this->total_qty;
    }

    /**
     * Returns the value of field new_qty
     *
     * @return integer
     */
    public function getNewQty()
    {
        return $this->new_qty;
    }

    /**
     * Returns the value of field used_qty
     *
     * @return integer
     */
    public function getUsedQty()
    {
        return $this->used_qty;
    }

    /**
     * Returns the value of field rtn_proc_qty
     *
     * @return integer
     */
    public function getRtnProcQty()
    {
        return $this->rtn_proc_qty;
    }

    /**
     * Returns the value of field rtn_plan_qty
     *
     * @return integer
     */
    public function getRtnPlanQty()
    {
        return $this->rtn_plan_qty;
    }

    /**
     * Returns the value of field in_use_qty
     *
     * @return integer
     */
    public function getInUseQty()
    {
        return $this->in_use_qty;
    }

    /**
     * Returns the value of field other_ship_qty
     *
     * @return integer
     */
    public function getOtherShipQty()
    {
        return $this->other_ship_qty;
    }

    /**
     * Returns the value of field discarded_qty
     *
     * @return integer
     */
    public function getDiscardedQty()
    {
        return $this->discarded_qty;
    }

    /**
     * Returns the value of field rent_pattern_data
     *
     * @return string
     */
    public function getRentPatternData()
    {
        return $this->rent_pattern_data;
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
     * Method to set the value of field zkrc2
     *
     * @param integer $zkrc2
     * @return $this
     */
    public function setZkrc2($zkrc2)
    {
        $this->zkrc2 = $zkrc2;

        return $this;
    }

    /**
     * Returns the value of field zkrc2
     *
     * @return integer
     */
    public function getZkrc2()
    {
        return $this->zkrc2;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->hasOne('m_item_comb_hkey', 'MItem', 'm_item_comb_hkey');
        $this->hasOne('rent_pattern_data', 'MRentPatternForSdmzk', 'rent_pattern_data');
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return TSdmzk[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return TSdmzk
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
        return 't_sdmzk';
    }

}
