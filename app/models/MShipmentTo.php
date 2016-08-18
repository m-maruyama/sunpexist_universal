<?php

class MShipmentTo extends \Phalcon\Mvc\Model
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
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return MShipmentTo[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return MShipmentTo
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
        return 'm_shipment_to';
    }

}
