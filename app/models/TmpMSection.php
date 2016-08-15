<?php

class TmpMSection extends \Phalcon\Mvc\Model
{

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
    protected $rntl_sect_name;

    /**
     *
     * @var string
     */
    protected $std_ship_to_cd;

    /**
     *
     * @var string
     */
    protected $std_ship_to_brnch_cd;

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
     * Method to set the value of field rntl_sect_name
     *
     * @param string $rntl_sect_name
     * @return $this
     */
    public function setRntlSectName($rntl_sect_name)
    {
        $this->rntl_sect_name = $rntl_sect_name;

        return $this;
    }

    /**
     * Method to set the value of field std_ship_to_cd
     *
     * @param string $std_ship_to_cd
     * @return $this
     */
    public function setStdShipToCd($std_ship_to_cd)
    {
        $this->std_ship_to_cd = $std_ship_to_cd;

        return $this;
    }

    /**
     * Method to set the value of field std_ship_to_brnch_cd
     *
     * @param string $std_ship_to_brnch_cd
     * @return $this
     */
    public function setStdShipToBrnchCd($std_ship_to_brnch_cd)
    {
        $this->std_ship_to_brnch_cd = $std_ship_to_brnch_cd;

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
     * Returns the value of field rntl_sect_name
     *
     * @return string
     */
    public function getRntlSectName()
    {
        return $this->rntl_sect_name;
    }

    /**
     * Returns the value of field std_ship_to_cd
     *
     * @return string
     */
    public function getStdShipToCd()
    {
        return $this->std_ship_to_cd;
    }

    /**
     * Returns the value of field std_ship_to_brnch_cd
     *
     * @return string
     */
    public function getStdShipToBrnchCd()
    {
        return $this->std_ship_to_brnch_cd;
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
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'tmp_m_section';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return TmpMSection[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return TmpMSection
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
