<?php

class MJobType extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    protected $m_job_type_comb_hkey;

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
    protected $job_type_name;

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
     * Method to set the value of field job_type_name
     *
     * @param string $job_type_name
     * @return $this
     */
    public function setJobTypeName($job_type_name)
    {
        $this->job_type_name = $job_type_name;

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
     * Returns the value of field m_job_type_comb_hkey
     *
     * @return string
     */
    public function getMJobTypeCombHkey()
    {
        return $this->m_job_type_comb_hkey;
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
     * Returns the value of field job_type_name
     *
     * @return string
     */
    public function getJobTypeName()
    {
        return $this->job_type_name;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->belongsTo("m_job_type_comb_hkey", "TOrder", "m_job_type_comb_hkey");
        $this->belongsTo("m_job_type_comb_hkey", "MWearerStd", "m_job_type_comb_hkey");
        $this->belongsTo("m_job_type_comb_hkey", "MWearerItem", "m_job_type_comb_hkey");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'm_job_type';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return MJobType[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return MJobType
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
