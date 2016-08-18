<?php

class TInquiry extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $index;

    /**
     *
     * @var string
     */
    protected $corporate_id;

    /**
     *
     * @var string
     */
    protected $rntl_sect_cd;

    /**
     *
     * @var string
     */
    protected $interrogator_name;

    /**
     *
     * @var string
     */
    protected $category_name;

    /**
     *
     * @var string
     */
    protected $interrogator_date;

    /**
     *
     * @var string
     */
    protected $interrogator_info;

    /**
     *
     * @var string
     */
    protected $interrogator_answer;

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
     * Method to set the value of field index
     *
     * @param integer $index
     * @return $this
     */
    public function setIndex($index)
    {
        $this->index = $index;

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
     * Method to set the value of field interrogator_name
     *
     * @param string $interrogator_name
     * @return $this
     */
    public function setInterrogatorName($interrogator_name)
    {
        $this->interrogator_name = $interrogator_name;

        return $this;
    }

    /**
     * Method to set the value of field category_name
     *
     * @param string $category_name
     * @return $this
     */
    public function setCategoryName($category_name)
    {
        $this->category_name = $category_name;

        return $this;
    }

    /**
     * Method to set the value of field interrogator_date
     *
     * @param string $interrogator_date
     * @return $this
     */
    public function setInterrogatorDate($interrogator_date)
    {
        $this->interrogator_date = $interrogator_date;

        return $this;
    }

    /**
     * Method to set the value of field interrogator_info
     *
     * @param string $interrogator_info
     * @return $this
     */
    public function setInterrogatorInfo($interrogator_info)
    {
        $this->interrogator_info = $interrogator_info;

        return $this;
    }

    /**
     * Method to set the value of field interrogator_answer
     *
     * @param string $interrogator_answer
     * @return $this
     */
    public function setInterrogatorAnswer($interrogator_answer)
    {
        $this->interrogator_answer = $interrogator_answer;

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
     * Returns the value of field index
     *
     * @return integer
     */
    public function getIndex()
    {
        return $this->index;
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
     * Returns the value of field rntl_sect_cd
     *
     * @return string
     */
    public function getRntlSectCd()
    {
        return $this->rntl_sect_cd;
    }

    /**
     * Returns the value of field interrogator_name
     *
     * @return string
     */
    public function getInterrogatorName()
    {
        return $this->interrogator_name;
    }

    /**
     * Returns the value of field category_name
     *
     * @return string
     */
    public function getCategoryName()
    {
        return $this->category_name;
    }

    /**
     * Returns the value of field interrogator_date
     *
     * @return string
     */
    public function getInterrogatorDate()
    {
        return $this->interrogator_date;
    }

    /**
     * Returns the value of field interrogator_info
     *
     * @return string
     */
    public function getInterrogatorInfo()
    {
        return $this->interrogator_info;
    }

    /**
     * Returns the value of field interrogator_answer
     *
     * @return string
     */
    public function getInterrogatorAnswer()
    {
        return $this->interrogator_answer;
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
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 't_inquiry';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return TInquiry[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return TInquiry
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
