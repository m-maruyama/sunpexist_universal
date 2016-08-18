<?php

class MGencode extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    protected $cls_cd;

    /**
     *
     * @var string
     */
    protected $gen_cd;

    /**
     *
     * @var string
     */
    protected $gen_name;

    /**
     *
     * @var integer
     */
    protected $dsp_order;

    /**
     *
     * @var string
     */
    protected $relation_cls_cd;

    /**
     *
     * @var string
     */
    protected $relation_gen_cd;

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
     * Method to set the value of field cls_cd
     *
     * @param string $cls_cd
     * @return $this
     */
    public function setClsCd($cls_cd)
    {
        $this->cls_cd = $cls_cd;

        return $this;
    }

    /**
     * Method to set the value of field gen_cd
     *
     * @param string $gen_cd
     * @return $this
     */
    public function setGenCd($gen_cd)
    {
        $this->gen_cd = $gen_cd;

        return $this;
    }

    /**
     * Method to set the value of field gen_name
     *
     * @param string $gen_name
     * @return $this
     */
    public function setGenName($gen_name)
    {
        $this->gen_name = $gen_name;

        return $this;
    }

    /**
     * Method to set the value of field dsp_order
     *
     * @param integer $dsp_order
     * @return $this
     */
    public function setDspOrder($dsp_order)
    {
        $this->dsp_order = $dsp_order;

        return $this;
    }

    /**
     * Method to set the value of field relation_cls_cd
     *
     * @param string $relation_cls_cd
     * @return $this
     */
    public function setRelationClsCd($relation_cls_cd)
    {
        $this->relation_cls_cd = $relation_cls_cd;

        return $this;
    }

    /**
     * Method to set the value of field relation_gen_cd
     *
     * @param string $relation_gen_cd
     * @return $this
     */
    public function setRelationGenCd($relation_gen_cd)
    {
        $this->relation_gen_cd = $relation_gen_cd;

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
     * Returns the value of field cls_cd
     *
     * @return string
     */
    public function getClsCd()
    {
        return $this->cls_cd;
    }

    /**
     * Returns the value of field gen_cd
     *
     * @return string
     */
    public function getGenCd()
    {
        return $this->gen_cd;
    }

    /**
     * Returns the value of field gen_name
     *
     * @return string
     */
    public function getGenName()
    {
        return $this->gen_name;
    }

    /**
     * Returns the value of field dsp_order
     *
     * @return integer
     */
    public function getDspOrder()
    {
        return $this->dsp_order;
    }

    /**
     * Returns the value of field relation_cls_cd
     *
     * @return string
     */
    public function getRelationClsCd()
    {
        return $this->relation_cls_cd;
    }

    /**
     * Returns the value of field relation_gen_cd
     *
     * @return string
     */
    public function getRelationGenCd()
    {
        return $this->relation_gen_cd;
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
     * Method to set the value of field gen_num1
     *
     * @param integer $gen_num1
     * @return $this
     */
    public function setGenNum1($gen_num1)
    {
        $this->gen_num1 = $gen_num1;

        return $this;
    }

    /**
     * Method to set the value of field gen_num2
     *
     * @param integer $gen_num2
     * @return $this
     */
    public function setGenNum2($gen_num2)
    {
        $this->gen_num2 = $gen_num2;

        return $this;
    }

    /**
     * Method to set the value of field gen_num3
     *
     * @param integer $gen_num3
     * @return $this
     */
    public function setGenNum3($gen_num3)
    {
        $this->gen_num3 = $gen_num3;

        return $this;
    }

    /**
     * Method to set the value of field gen_chr1
     *
     * @param integer $gen_chr1
     * @return $this
     */
    public function setGenChr1($gen_chr1)
    {
        $this->gen_chr1 = $gen_chr1;

        return $this;
    }

    /**
     * Method to set the value of field gen_chr2
     *
     * @param string $gen_chr2
     * @return $this
     */
    public function setGenChr2($gen_chr2)
    {
        $this->gen_chr2 = $gen_chr2;

        return $this;
    }

    /**
     * Method to set the value of field gen_chr3
     *
     * @param string $gen_chr3
     * @return $this
     */
    public function setGenChr3($gen_chr3)
    {
        $this->gen_chr3 = $gen_chr3;

        return $this;
    }

    /**
     * Returns the value of field gen_num1
     *
     * @return integer
     */
    public function getGenNum1()
    {
        return $this->gen_num1;
    }

    /**
     * Returns the value of field gen_num2
     *
     * @return integer
     */
    public function getGenNum2()
    {
        return $this->gen_num2;
    }

    /**
     * Returns the value of field gen_num3
     *
     * @return integer
     */
    public function getGenNum3()
    {
        return $this->gen_num3;
    }

    /**
     * Returns the value of field gen_chr1
     *
     * @return integer
     */
    public function getGenChr1()
    {
        return $this->gen_chr1;
    }

    /**
     * Returns the value of field gen_chr2
     *
     * @return string
     */
    public function getGenChr2()
    {
        return $this->gen_chr2;
    }

    /**
     * Returns the value of field gen_chr3
     *
     * @return string
     */
    public function getGenChr3()
    {
        return $this->gen_chr3;
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
     * @return MGencode[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return MGencode
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
        return 'm_gencode';
    }

}
