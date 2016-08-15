<?php

class MWearerItem extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    protected $m_wearer_item_comb_hkey;

    /**
     *
     * @var string
     */
    protected $werer_cd;

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
     * @var integer
     */
    protected $input_qty;

    /**
     *
     * @var string
     */
    protected $order_sts_kbn;

    /**
     *
     * @var string
     */
    protected $upd_kbn;

    /**
     *
     * @var string
     */
    protected $web_upd_date;

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
    protected $del_kbn;

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
    protected $m_wearer_std_comb_hkey;

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
     * Method to set the value of field input_qty
     *
     * @param integer $input_qty
     * @return $this
     */
    public function setInputQty($input_qty)
    {
        $this->input_qty = $input_qty;

        return $this;
    }

    /**
     * Method to set the value of field order_sts_kbn
     *
     * @param string $order_sts_kbn
     * @return $this
     */
    public function setOrderStsKbn($order_sts_kbn)
    {
        $this->order_sts_kbn = $order_sts_kbn;

        return $this;
    }

    /**
     * Method to set the value of field upd_kbn
     *
     * @param string $upd_kbn
     * @return $this
     */
    public function setUpdKbn($upd_kbn)
    {
        $this->upd_kbn = $upd_kbn;

        return $this;
    }

    /**
     * Method to set the value of field web_upd_date
     *
     * @param string $web_upd_date
     * @return $this
     */
    public function setWebUpdDate($web_upd_date)
    {
        $this->web_upd_date = $web_upd_date;

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
     * Method to set the value of field del_kbn
     *
     * @param string $del_kbn
     * @return $this
     */
    public function setDelKbn($del_kbn)
    {
        $this->del_kbn = $del_kbn;

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
     * Method to set the value of field m_wearer_std_comb_hkey
     *
     * @param string $m_wearer_std_comb_hkey
     * @return $this
     */
    public function setMWearerStdCombHkey($m_wearer_std_comb_hkey)
    {
        $this->m_wearer_std_comb_hkey = $m_wearer_std_comb_hkey;

        return $this;
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
     * Returns the value of field werer_cd
     *
     * @return string
     */
    public function getWererCd()
    {
        return $this->werer_cd;
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
     * Returns the value of field input_qty
     *
     * @return integer
     */
    public function getInputQty()
    {
        return $this->input_qty;
    }

    /**
     * Returns the value of field order_sts_kbn
     *
     * @return string
     */
    public function getOrderStsKbn()
    {
        return $this->order_sts_kbn;
    }

    /**
     * Returns the value of field upd_kbn
     *
     * @return string
     */
    public function getUpdKbn()
    {
        return $this->upd_kbn;
    }

    /**
     * Returns the value of field web_upd_date
     *
     * @return string
     */
    public function getWebUpdDate()
    {
        return $this->web_upd_date;
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
     * Returns the value of field del_kbn
     *
     * @return string
     */
    public function getDelKbn()
    {
        return $this->del_kbn;
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
     * Returns the value of field m_wearer_std_comb_hkey
     *
     * @return string
     */
    public function getMWearerStdCombHkey()
    {
        return $this->m_wearer_std_comb_hkey;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->belongsTo("m_wearer_std_comb_hkey", "MWearerStd", "m_wearer_std_comb_hkey");
        $this->hasOne('m_item_comb_hkey', 'MItem', 'm_item_comb_hkey');
        $this->hasOne('m_job_type_comb_hkey', 'MJobType', 'm_job_type_comb_hkey');
        $this->hasOne('m_section_comb_hkey', 'MSection', 'm_section_comb_hkey');
        $this->hasMany("m_wearer_item_comb_hkey", "TOrderState", "m_wearer_item_comb_hkey");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'm_wearer_item';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return MWearerItem[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return MWearerItem
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
