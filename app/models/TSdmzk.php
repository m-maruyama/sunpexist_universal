<?php

class TSdmzk extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    protected $zkzkcd;

    /**
     *
     * @var string
     */
    protected $zkwhcd;

    /**
     *
     * @var string
     */
    protected $zkprcd;

    /**
     *
     * @var string
     */
    protected $zkclor;

    /**
     *
     * @var string
     */
    protected $zksize;

    /**
     *
     * @var integer
     */
    protected $zkrc2;

    /**
     *
     * @var string
     */
    protected $label;

    /**
     *
     * @var string
     */
    protected $zk_status_cd;

    /**
     *
     * @var string
     */
    protected $rent_pattern_data;

    /**
     *
     * @var string
     */
    protected $m_item_comb_hkey;

    /**
     * Method to set the value of field zkzkcd
     *
     * @param string $zkzkcd
     * @return $this
     */
    public function setZkzkcd($zkzkcd)
    {
        $this->zkzkcd = $zkzkcd;

        return $this;
    }

    /**
     * Method to set the value of field zkwhcd
     *
     * @param string $zkwhcd
     * @return $this
     */
    public function setZkwhcd($zkwhcd)
    {
        $this->zkwhcd = $zkwhcd;

        return $this;
    }

    /**
     * Method to set the value of field zkprcd
     *
     * @param string $zkprcd
     * @return $this
     */
    public function setZkprcd($zkprcd)
    {
        $this->zkprcd = $zkprcd;

        return $this;
    }

    /**
     * Method to set the value of field zkclor
     *
     * @param string $zkclor
     * @return $this
     */
    public function setZkclor($zkclor)
    {
        $this->zkclor = $zkclor;

        return $this;
    }

    /**
     * Method to set the value of field zksize
     *
     * @param string $zksize
     * @return $this
     */
    public function setZksize($zksize)
    {
        $this->zksize = $zksize;

        return $this;
    }

    /**
     * Method to set the value of field zkrc2
     *
     * @param integer $zkrc2
     * @return $this
     */
    public function setZkrc2($zkrc2)
    {
        $this->zkrc2 = $zkrc2;

        return $this;
    }

    /**
     * Method to set the value of field label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Method to set the value of field zk_status_cd
     *
     * @param string $zk_status_cd
     * @return $this
     */
    public function setZkStatusCd($zk_status_cd)
    {
        $this->zk_status_cd = $zk_status_cd;

        return $this;
    }

    /**
     * Method to set the value of field rent_pattern_data
     *
     * @param string $rent_pattern_data
     * @return $this
     */
    public function setRentPatternData($rent_pattern_data)
    {
        $this->rent_pattern_data = $rent_pattern_data;

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
     * Returns the value of field zkzkcd
     *
     * @return string
     */
    public function getZkzkcd()
    {
        return $this->zkzkcd;
    }

    /**
     * Returns the value of field zkwhcd
     *
     * @return string
     */
    public function getZkwhcd()
    {
        return $this->zkwhcd;
    }

    /**
     * Returns the value of field zkprcd
     *
     * @return string
     */
    public function getZkprcd()
    {
        return $this->zkprcd;
    }

    /**
     * Returns the value of field zkclor
     *
     * @return string
     */
    public function getZkclor()
    {
        return $this->zkclor;
    }

    /**
     * Returns the value of field zksize
     *
     * @return string
     */
    public function getZksize()
    {
        return $this->zksize;
    }

    /**
     * Returns the value of field zkrc2
     *
     * @return integer
     */
    public function getZkrc2()
    {
        return $this->zkrc2;
    }

    /**
     * Returns the value of field label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns the value of field zk_status_cd
     *
     * @return string
     */
    public function getZkStatusCd()
    {
        return $this->zk_status_cd;
    }

    /**
     * Returns the value of field rent_pattern_data
     *
     * @return string
     */
    public function getRentPatternData()
    {
        return $this->rent_pattern_data;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->hasOne('m_item_comb_hkey', 'MItem', 'm_item_comb_hkey');
        $this->hasOne('rent_pattern_data', 'MRentPatternForSdmzk', 'rent_pattern_data');
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 't_sdmzk';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return TSdmzk[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return TSdmzk
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
