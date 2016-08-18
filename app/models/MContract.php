<?php

class MContract extends \Phalcon\Mvc\Model
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
    protected $rntl_cont_name;

    /**
     *
     * @var string
     */
    protected $cont_ymd;

    /**
     *
     * @var string
     */
    protected $cont_start_ymd;

    /**
     *
     * @var string
     */
    protected $cont_end_ymd;

    /**
     *
     * @var string
     */
    protected $cont_sts_kbn;

    /**
     *
     * @var string
     */
    protected $month_total_d;

    /**
     *
     * @var string
     */
    protected $expen_total_d;

    /**
     *
     * @var string
     */
    protected $req_total_d;

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
    protected $rntl_emply_cont_name;

    /**
     *
     * @var integer
     */
    protected $individual_flg;

    /**
     *
     * @var integer
     */
    protected $receipt_flg;

    /**
     *
     * @var string
     */
    protected $rntl_cont_flg;

    /**
     *
     * @var string
     */
    protected $purchase_cont_flg;

    /**
     *
     * @var string
     */
    protected $sub_cont_flg1;

    /**
     *
     * @var string
     */
    protected $sub_cont_flg2;

    /**
     *
     * @var string
     */
    protected $sub_cont_flg3;

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
     * Method to set the value of field rntl_cont_name
     *
     * @param string $rntl_cont_name
     * @return $this
     */
    public function setRntlContName($rntl_cont_name)
    {
        $this->rntl_cont_name = $rntl_cont_name;

        return $this;
    }

    /**
     * Method to set the value of field cont_ymd
     *
     * @param string $cont_ymd
     * @return $this
     */
    public function setContYmd($cont_ymd)
    {
        $this->cont_ymd = $cont_ymd;

        return $this;
    }

    /**
     * Method to set the value of field cont_start_ymd
     *
     * @param string $cont_start_ymd
     * @return $this
     */
    public function setContStartYmd($cont_start_ymd)
    {
        $this->cont_start_ymd = $cont_start_ymd;

        return $this;
    }

    /**
     * Method to set the value of field cont_end_ymd
     *
     * @param string $cont_end_ymd
     * @return $this
     */
    public function setContEndYmd($cont_end_ymd)
    {
        $this->cont_end_ymd = $cont_end_ymd;

        return $this;
    }

    /**
     * Method to set the value of field cont_sts_kbn
     *
     * @param string $cont_sts_kbn
     * @return $this
     */
    public function setContStsKbn($cont_sts_kbn)
    {
        $this->cont_sts_kbn = $cont_sts_kbn;

        return $this;
    }

    /**
     * Method to set the value of field month_total_d
     *
     * @param string $month_total_d
     * @return $this
     */
    public function setMonthTotalD($month_total_d)
    {
        $this->month_total_d = $month_total_d;

        return $this;
    }

    /**
     * Method to set the value of field expen_total_d
     *
     * @param string $expen_total_d
     * @return $this
     */
    public function setExpenTotalD($expen_total_d)
    {
        $this->expen_total_d = $expen_total_d;

        return $this;
    }

    /**
     * Method to set the value of field req_total_d
     *
     * @param string $req_total_d
     * @return $this
     */
    public function setReqTotalD($req_total_d)
    {
        $this->req_total_d = $req_total_d;

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
     * Method to set the value of field rntl_emply_cont_name
     *
     * @param string $rntl_emply_cont_name
     * @return $this
     */
    public function setRntlEmplyContName($rntl_emply_cont_name)
    {
        $this->rntl_emply_cont_name = $rntl_emply_cont_name;

        return $this;
    }

    /**
     * Method to set the value of field individual_flg
     *
     * @param integer $individual_flg
     * @return $this
     */
    public function setIndividualFlg($individual_flg)
    {
        $this->individual_flg = $individual_flg;

        return $this;
    }

    /**
     * Method to set the value of field receipt_flg
     *
     * @param integer $receipt_flg
     * @return $this
     */
    public function setReceiptFlg($receipt_flg)
    {
        $this->receipt_flg = $receipt_flg;

        return $this;
    }

    /**
     * Method to set the value of field rntl_cont_flg
     *
     * @param string $rntl_cont_flg
     * @return $this
     */
    public function setRntlContFlg($rntl_cont_flg)
    {
        $this->rntl_cont_flg = $rntl_cont_flg;

        return $this;
    }

    /**
     * Method to set the value of field purchase_cont_flg
     *
     * @param string $purchase_cont_flg
     * @return $this
     */
    public function setPurchaseContFlg($purchase_cont_flg)
    {
        $this->purchase_cont_flg = $purchase_cont_flg;

        return $this;
    }

    /**
     * Method to set the value of field sub_cont_flg1
     *
     * @param string $sub_cont_flg1
     * @return $this
     */
    public function setSubContFlg1($sub_cont_flg1)
    {
        $this->sub_cont_flg1 = $sub_cont_flg1;

        return $this;
    }

    /**
     * Method to set the value of field sub_cont_flg2
     *
     * @param string $sub_cont_flg2
     * @return $this
     */
    public function setSubContFlg2($sub_cont_flg2)
    {
        $this->sub_cont_flg2 = $sub_cont_flg2;

        return $this;
    }

    /**
     * Method to set the value of field sub_cont_flg3
     *
     * @param string $sub_cont_flg3
     * @return $this
     */
    public function setSubContFlg3($sub_cont_flg3)
    {
        $this->sub_cont_flg3 = $sub_cont_flg3;

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
     * Returns the value of field rntl_cont_name
     *
     * @return string
     */
    public function getRntlContName()
    {
        return $this->rntl_cont_name;
    }

    /**
     * Returns the value of field cont_ymd
     *
     * @return string
     */
    public function getContYmd()
    {
        return $this->cont_ymd;
    }

    /**
     * Returns the value of field cont_start_ymd
     *
     * @return string
     */
    public function getContStartYmd()
    {
        return $this->cont_start_ymd;
    }

    /**
     * Returns the value of field cont_end_ymd
     *
     * @return string
     */
    public function getContEndYmd()
    {
        return $this->cont_end_ymd;
    }

    /**
     * Returns the value of field cont_sts_kbn
     *
     * @return string
     */
    public function getContStsKbn()
    {
        return $this->cont_sts_kbn;
    }

    /**
     * Returns the value of field month_total_d
     *
     * @return string
     */
    public function getMonthTotalD()
    {
        return $this->month_total_d;
    }

    /**
     * Returns the value of field expen_total_d
     *
     * @return string
     */
    public function getExpenTotalD()
    {
        return $this->expen_total_d;
    }

    /**
     * Returns the value of field req_total_d
     *
     * @return string
     */
    public function getReqTotalD()
    {
        return $this->req_total_d;
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
     * Returns the value of field rntl_emply_cont_name
     *
     * @return string
     */
    public function getRntlEmplyContName()
    {
        return $this->rntl_emply_cont_name;
    }

    /**
     * Returns the value of field individual_flg
     *
     * @return integer
     */
    public function getIndividualFlg()
    {
        return $this->individual_flg;
    }

    /**
     * Returns the value of field receipt_flg
     *
     * @return integer
     */
    public function getReceiptFlg()
    {
        return $this->receipt_flg;
    }

    /**
     * Returns the value of field rntl_cont_flg
     *
     * @return string
     */
    public function getRntlContFlg()
    {
        return $this->rntl_cont_flg;
    }

    /**
     * Returns the value of field purchase_cont_flg
     *
     * @return string
     */
    public function getPurchaseContFlg()
    {
        return $this->purchase_cont_flg;
    }

    /**
     * Returns the value of field sub_cont_flg1
     *
     * @return string
     */
    public function getSubContFlg1()
    {
        return $this->sub_cont_flg1;
    }

    /**
     * Returns the value of field sub_cont_flg2
     *
     * @return string
     */
    public function getSubContFlg2()
    {
        return $this->sub_cont_flg2;
    }

    /**
     * Returns the value of field sub_cont_flg3
     *
     * @return string
     */
    public function getSubContFlg3()
    {
        return $this->sub_cont_flg3;
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
     * @return MContract[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return MContract
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
        return 'm_contract';
    }

}
