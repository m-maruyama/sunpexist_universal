<?php

class MAccount extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    protected $user_id;

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
    protected $pass_word;

    /**
     *
     * @var string
     */
    protected $user_name;

    /**
     *
     * @var string
     */
    protected $use_limit;

    /**
     *
     * @var integer
     */
    protected $err_qty;

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
    protected $old_pass_word;

    /**
     *
     * @var string
     */
    protected $last_pass_word_upd_date;

    /**
     *
     * @var string
     */
    protected $user_type;

    /**
     *
     * @var string
     */
    protected $position_name;

    /**
     *
     * @var integer
     */
    protected $login_err_count;

    /**
     * Method to set the value of field user_id
     *
     * @param string $user_id
     * @return $this
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

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
     * Method to set the value of field pass_word
     *
     * @param string $pass_word
     * @return $this
     */
    public function setPassWord($pass_word)
    {
        $this->pass_word = $pass_word;

        return $this;
    }

    /**
     * Method to set the value of field user_name
     *
     * @param string $user_name
     * @return $this
     */
    public function setUserName($user_name)
    {
        $this->user_name = $user_name;

        return $this;
    }

    /**
     * Method to set the value of field use_limit
     *
     * @param string $use_limit
     * @return $this
     */
    public function setUseLimit($use_limit)
    {
        $this->use_limit = $use_limit;

        return $this;
    }

    /**
     * Method to set the value of field err_qty
     *
     * @param integer $err_qty
     * @return $this
     */
    public function setErrQty($err_qty)
    {
        $this->err_qty = $err_qty;

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
     * Method to set the value of field old_pass_word
     *
     * @param string $old_pass_word
     * @return $this
     */
    public function setOldPassWord($old_pass_word)
    {
        $this->old_pass_word = $old_pass_word;

        return $this;
    }

    /**
     * Method to set the value of field last_pass_word_upd_date
     *
     * @param string $last_pass_word_upd_date
     * @return $this
     */
    public function setLastPassWordUpdDate($last_pass_word_upd_date)
    {
        $this->last_pass_word_upd_date = $last_pass_word_upd_date;

        return $this;
    }

    /**
     * Method to set the value of field user_type
     *
     * @param string $user_type
     * @return $this
     */
    public function setUserType($user_type)
    {
        $this->user_type = $user_type;

        return $this;
    }

    /**
     * Method to set the value of field position_name
     *
     * @param string $position_name
     * @return $this
     */
    public function setPositionName($position_name)
    {
        $this->position_name = $position_name;

        return $this;
    }

    /**
     * Method to set the value of field login_err_count
     *
     * @param integer $login_err_count
     * @return $this
     */
    public function setLoginErrCount($login_err_count)
    {
        $this->login_err_count = $login_err_count;

        return $this;
    }

    /**
     * Returns the value of field user_id
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->user_id;
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
     * Returns the value of field pass_word
     *
     * @return string
     */
    public function getPassWord()
    {
        return $this->pass_word;
    }

    /**
     * Returns the value of field user_name
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->user_name;
    }

    /**
     * Returns the value of field use_limit
     *
     * @return string
     */
    public function getUseLimit()
    {
        return $this->use_limit;
    }

    /**
     * Returns the value of field err_qty
     *
     * @return integer
     */
    public function getErrQty()
    {
        return $this->err_qty;
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
     * Returns the value of field old_pass_word
     *
     * @return string
     */
    public function getOldPassWord()
    {
        return $this->old_pass_word;
    }

    /**
     * Returns the value of field last_pass_word_upd_date
     *
     * @return string
     */
    public function getLastPassWordUpdDate()
    {
        return $this->last_pass_word_upd_date;
    }

    /**
     * Returns the value of field user_type
     *
     * @return string
     */
    public function getUserType()
    {
        return $this->user_type;
    }

    /**
     * Returns the value of field position_name
     *
     * @return string
     */
    public function getPositionName()
    {
        return $this->position_name;
    }

    /**
     * Returns the value of field login_err_count
     *
     * @return integer
     */
    public function getLoginErrCount()
    {
        return $this->login_err_count;
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
        return 'm_account';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return MAccount[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return MAccount
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
