<?php

class MInputItem extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    protected $m_input_item_comb_hkey;

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
    protected $job_type_item_name;

    /**
     *
     * @var integer
     */
    protected $std_input_qty;

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
    protected $tag_mng_obj_kbn;

    /**
     *
     * @var string
     */
    protected $input_item_name;

    /**
     *
     * @var integer
     */
    protected $size_add_flg;

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
    protected $m_job_type_comb_hkey;

    /**
     * Method to set the value of field m_input_item_comb_hkey
     *
     * @param string $m_input_item_comb_hkey
     * @return $this
     */
    public function setMInputItemCombHkey($m_input_item_comb_hkey)
    {
        $this->m_input_item_comb_hkey = $m_input_item_comb_hkey;

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
     * Method to set the value of field job_type_item_name
     *
     * @param string $job_type_item_name
     * @return $this
     */
    public function setJobTypeItemName($job_type_item_name)
    {
        $this->job_type_item_name = $job_type_item_name;

        return $this;
    }

    /**
     * Method to set the value of field std_input_qty
     *
     * @param integer $std_input_qty
     * @return $this
     */
    public function setStdInputQty($std_input_qty)
    {
        $this->std_input_qty = $std_input_qty;

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
     * Method to set the value of field tag_mng_obj_kbn
     *
     * @param string $tag_mng_obj_kbn
     * @return $this
     */
    public function setTagMngObjKbn($tag_mng_obj_kbn)
    {
        $this->tag_mng_obj_kbn = $tag_mng_obj_kbn;

        return $this;
    }

    /**
     * Method to set the value of field input_item_name
     *
     * @param string $input_item_name
     * @return $this
     */
    public function setInputItemName($input_item_name)
    {
        $this->input_item_name = $input_item_name;

        return $this;
    }

    /**
     * Method to set the value of field size_add_flg
     *
     * @param integer $size_add_flg
     * @return $this
     */
    public function setSizeAddFlg($size_add_flg)
    {
        $this->size_add_flg = $size_add_flg;

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
     * Returns the value of field m_input_item_comb_hkey
     *
     * @return string
     */
    public function getMInputItemCombHkey()
    {
        return $this->m_input_item_comb_hkey;
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
     * Returns the value of field job_type_item_name
     *
     * @return string
     */
    public function getJobTypeItemName()
    {
        return $this->job_type_item_name;
    }

    /**
     * Returns the value of field std_input_qty
     *
     * @return integer
     */
    public function getStdInputQty()
    {
        return $this->std_input_qty;
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
     * Returns the value of field tag_mng_obj_kbn
     *
     * @return string
     */
    public function getTagMngObjKbn()
    {
        return $this->tag_mng_obj_kbn;
    }

    /**
     * Returns the value of field input_item_name
     *
     * @return string
     */
    public function getInputItemName()
    {
        return $this->input_item_name;
    }

    /**
     * Returns the value of field size_add_flg
     *
     * @return integer
     */
    public function getSizeAddFlg()
    {
        return $this->size_add_flg;
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
     * Returns the value of field m_job_type_comb_hkey
     *
     * @return string
     */
    public function getMJobTypeCombHkey()
    {
        return $this->m_job_type_comb_hkey;
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
     * @return MInputItem[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return MInputItem
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
        return 'm_input_item';
    }

}
