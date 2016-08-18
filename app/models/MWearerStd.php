<?php

class MWearerStd extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    protected $m_wearer_std_comb_hkey;

    /**
     *
     * @var string
     */
    protected $corporate_id;

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
    protected $cster_emply_cd;

    /**
     *
     * @var string
     */
    protected $werer_name;

    /**
     *
     * @var string
     */
    protected $werer_name_kana;

    /**
     *
     * @var string
     */
    protected $sex_kbn;

    /**
     *
     * @var string
     */
    protected $werer_sts_kbn;

    /**
     *
     * @var string
     */
    protected $resfl_ymd;

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
    protected $rntl_cont_no_bef;

    /**
     *
     * @var string
     */
    protected $rntl_sect_cd_bef;

    /**
     *
     * @var string
     */
    protected $job_type_cd_bef;

    /**
     *
     * @var string
     */
    protected $werer_sts_kbn_bef;

    /**
     *
     * @var string
     */
    protected $resfl_ymd_bef;

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
    protected $m_job_type_comb_hkey;

    /**
     *
     * @var string
     */
    protected $m_section_comb_hkey;

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
     * Method to set the value of field werer_name
     *
     * @param string $werer_name
     * @return $this
     */
    public function setWererName($werer_name)
    {
        $this->werer_name = $werer_name;

        return $this;
    }

    /**
     * Method to set the value of field werer_name_kana
     *
     * @param string $werer_name_kana
     * @return $this
     */
    public function setWererNameKana($werer_name_kana)
    {
        $this->werer_name_kana = $werer_name_kana;

        return $this;
    }

    /**
     * Method to set the value of field sex_kbn
     *
     * @param string $sex_kbn
     * @return $this
     */
    public function setSexKbn($sex_kbn)
    {
        $this->sex_kbn = $sex_kbn;

        return $this;
    }

    /**
     * Method to set the value of field werer_sts_kbn
     *
     * @param string $werer_sts_kbn
     * @return $this
     */
    public function setWererStsKbn($werer_sts_kbn)
    {
        $this->werer_sts_kbn = $werer_sts_kbn;

        return $this;
    }

    /**
     * Method to set the value of field resfl_ymd
     *
     * @param string $resfl_ymd
     * @return $this
     */
    public function setResflYmd($resfl_ymd)
    {
        $this->resfl_ymd = $resfl_ymd;

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
     * Method to set the value of field rntl_cont_no_bef
     *
     * @param string $rntl_cont_no_bef
     * @return $this
     */
    public function setRntlContNoBef($rntl_cont_no_bef)
    {
        $this->rntl_cont_no_bef = $rntl_cont_no_bef;

        return $this;
    }

    /**
     * Method to set the value of field rntl_sect_cd_bef
     *
     * @param string $rntl_sect_cd_bef
     * @return $this
     */
    public function setRntlSectCdBef($rntl_sect_cd_bef)
    {
        $this->rntl_sect_cd_bef = $rntl_sect_cd_bef;

        return $this;
    }

    /**
     * Method to set the value of field job_type_cd_bef
     *
     * @param string $job_type_cd_bef
     * @return $this
     */
    public function setJobTypeCdBef($job_type_cd_bef)
    {
        $this->job_type_cd_bef = $job_type_cd_bef;

        return $this;
    }

    /**
     * Method to set the value of field werer_sts_kbn_bef
     *
     * @param string $werer_sts_kbn_bef
     * @return $this
     */
    public function setWererStsKbnBef($werer_sts_kbn_bef)
    {
        $this->werer_sts_kbn_bef = $werer_sts_kbn_bef;

        return $this;
    }

    /**
     * Method to set the value of field resfl_ymd_bef
     *
     * @param string $resfl_ymd_bef
     * @return $this
     */
    public function setResflYmdBef($resfl_ymd_bef)
    {
        $this->resfl_ymd_bef = $resfl_ymd_bef;

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
     * Returns the value of field m_wearer_std_comb_hkey
     *
     * @return string
     */
    public function getMWearerStdCombHkey()
    {
        return $this->m_wearer_std_comb_hkey;
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
     * Returns the value of field cster_emply_cd
     *
     * @return string
     */
    public function getCsterEmplyCd()
    {
        return $this->cster_emply_cd;
    }

    /**
     * Returns the value of field werer_name
     *
     * @return string
     */
    public function getWererName()
    {
        return $this->werer_name;
    }

    /**
     * Returns the value of field werer_name_kana
     *
     * @return string
     */
    public function getWererNameKana()
    {
        return $this->werer_name_kana;
    }

    /**
     * Returns the value of field sex_kbn
     *
     * @return string
     */
    public function getSexKbn()
    {
        return $this->sex_kbn;
    }

    /**
     * Returns the value of field werer_sts_kbn
     *
     * @return string
     */
    public function getWererStsKbn()
    {
        return $this->werer_sts_kbn;
    }

    /**
     * Returns the value of field resfl_ymd
     *
     * @return string
     */
    public function getResflYmd()
    {
        return $this->resfl_ymd;
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
     * Returns the value of field rntl_cont_no_bef
     *
     * @return string
     */
    public function getRntlContNoBef()
    {
        return $this->rntl_cont_no_bef;
    }

    /**
     * Returns the value of field rntl_sect_cd_bef
     *
     * @return string
     */
    public function getRntlSectCdBef()
    {
        return $this->rntl_sect_cd_bef;
    }

    /**
     * Returns the value of field job_type_cd_bef
     *
     * @return string
     */
    public function getJobTypeCdBef()
    {
        return $this->job_type_cd_bef;
    }

    /**
     * Returns the value of field werer_sts_kbn_bef
     *
     * @return string
     */
    public function getWererStsKbnBef()
    {
        return $this->werer_sts_kbn_bef;
    }

    /**
     * Returns the value of field resfl_ymd_bef
     *
     * @return string
     */
    public function getResflYmdBef()
    {
        return $this->resfl_ymd_bef;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->hasMany("m_wearer_std_comb_hkey", "MWearerItem", "m_wearer_std_comb_hkey");
        $this->hasOne('m_item_comb_hkey', 'MItem', 'm_item_comb_hkey');
        $this->hasOne('m_job_type_comb_hkey', 'MJobType', 'm_job_type_comb_hkey');
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return MWearerStd[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return MWearerStd
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
        return 'm_wearer_std';
    }

}
