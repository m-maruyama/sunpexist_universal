<?php

class MContractResource extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    protected $rntl_cont_resc_id;

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
    protected $rntl_sect_cd;

    /**
     *
     * @var integer
     */
    protected $account_no;

    /**
     *
     * @var integer
     */
    protected $update_ok_flg;

    /**
     *
     * @var integer
     */
    protected $order_input_ok_flg;

    /**
     *
     * @var integer
     */
    protected $order_send_ok_flg;

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
     * Method to set the value of field rntl_cont_resc_id
     *
     * @param string $rntl_cont_resc_id
     * @return $this
     */
    public function setRntlContRescId($rntl_cont_resc_id)
    {
        $this->rntl_cont_resc_id = $rntl_cont_resc_id;

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
     * Method to set the value of field account_no
     *
     * @param integer $account_no
     * @return $this
     */
    public function setAccountNo($account_no)
    {
        $this->account_no = $account_no;

        return $this;
    }

    /**
     * Method to set the value of field update_ok_flg
     *
     * @param integer $update_ok_flg
     * @return $this
     */
    public function setUpdateOkFlg($update_ok_flg)
    {
        $this->update_ok_flg = $update_ok_flg;

        return $this;
    }

    /**
     * Method to set the value of field order_input_ok_flg
     *
     * @param integer $order_input_ok_flg
     * @return $this
     */
    public function setOrderInputOkFlg($order_input_ok_flg)
    {
        $this->order_input_ok_flg = $order_input_ok_flg;

        return $this;
    }

    /**
     * Method to set the value of field order_send_ok_flg
     *
     * @param integer $order_send_ok_flg
     * @return $this
     */
    public function setOrderSendOkFlg($order_send_ok_flg)
    {
        $this->order_send_ok_flg = $order_send_ok_flg;

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
     * Returns the value of field rntl_cont_resc_id
     *
     * @return string
     */
    public function getRntlContRescId()
    {
        return $this->rntl_cont_resc_id;
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
     * Returns the value of field rntl_sect_cd
     *
     * @return string
     */
    public function getRntlSectCd()
    {
        return $this->rntl_sect_cd;
    }

    /**
     * Returns the value of field account_no
     *
     * @return integer
     */
    public function getAccountNo()
    {
        return $this->account_no;
    }

    /**
     * Returns the value of field update_ok_flg
     *
     * @return integer
     */
    public function getUpdateOkFlg()
    {
        return $this->update_ok_flg;
    }

    /**
     * Returns the value of field order_input_ok_flg
     *
     * @return integer
     */
    public function getOrderInputOkFlg()
    {
        return $this->order_input_ok_flg;
    }

    /**
     * Returns the value of field order_send_ok_flg
     *
     * @return integer
     */
    public function getOrderSendOkFlg()
    {
        return $this->order_send_ok_flg;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->hasOne('account_no', 'MAccount', 'account_no');
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'm_contract_resource';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return MContractResource[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return MContractResource
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
