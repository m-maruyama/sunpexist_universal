<?php

class TLog extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $log_id;

    /**
     *
     * @var string
     */
    protected $scr_name;

    /**
     *
     * @var integer
     */
    protected $log_type;

    /**
     *
     * @var integer
     */
    protected $log_level;

    /**
     *
     * @var string
     */
    protected $user_id;

    /**
     *
     * @var string
     */
    protected $ctrl_date;

    /**
     *
     * @var string
     */
    protected $access_url;

    /**
     *
     * @var string
     */
    protected $post_param;

    /**
     *
     * @var string
     */
    protected $ip_address;

    /**
     *
     * @var string
     */
    protected $user_agent;

    /**
     *
     * @var string
     */
    protected $memo;

    /**
     * Method to set the value of field log_id
     *
     * @param integer $log_id
     * @return $this
     */
    public function setLogId($log_id)
    {
        $this->log_id = $log_id;

        return $this;
    }

    /**
     * Method to set the value of field scr_name
     *
     * @param string $scr_name
     * @return $this
     */
    public function setScrName($scr_name)
    {
        $this->scr_name = $scr_name;

        return $this;
    }

    /**
     * Method to set the value of field log_type
     *
     * @param integer $log_type
     * @return $this
     */
    public function setLogType($log_type)
    {
        $this->log_type = $log_type;

        return $this;
    }

    /**
     * Method to set the value of field log_level
     *
     * @param integer $log_level
     * @return $this
     */
    public function setLogLevel($log_level)
    {
        $this->log_level = $log_level;

        return $this;
    }

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
     * Method to set the value of field ctrl_date
     *
     * @param string $ctrl_date
     * @return $this
     */
    public function setCtrlDate($ctrl_date)
    {
        $this->ctrl_date = $ctrl_date;

        return $this;
    }

    /**
     * Method to set the value of field access_url
     *
     * @param string $access_url
     * @return $this
     */
    public function setAccessUrl($access_url)
    {
        $this->access_url = $access_url;

        return $this;
    }

    /**
     * Method to set the value of field post_param
     *
     * @param string $post_param
     * @return $this
     */
    public function setPostParam($post_param)
    {
        $this->post_param = $post_param;

        return $this;
    }

    /**
     * Method to set the value of field ip_address
     *
     * @param string $ip_address
     * @return $this
     */
    public function setIpAddress($ip_address)
    {
        $this->ip_address = $ip_address;

        return $this;
    }

    /**
     * Method to set the value of field user_agent
     *
     * @param string $user_agent
     * @return $this
     */
    public function setUserAgent($user_agent)
    {
        $this->user_agent = $user_agent;

        return $this;
    }

    /**
     * Method to set the value of field memo
     *
     * @param string $memo
     * @return $this
     */
    public function setMemo($memo)
    {
        $this->memo = $memo;

        return $this;
    }

    /**
     * Returns the value of field log_id
     *
     * @return integer
     */
    public function getLogId()
    {
        return $this->log_id;
    }

    /**
     * Returns the value of field scr_name
     *
     * @return string
     */
    public function getScrName()
    {
        return $this->scr_name;
    }

    /**
     * Returns the value of field log_type
     *
     * @return integer
     */
    public function getLogType()
    {
        return $this->log_type;
    }

    /**
     * Returns the value of field log_level
     *
     * @return integer
     */
    public function getLogLevel()
    {
        return $this->log_level;
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
     * Returns the value of field ctrl_date
     *
     * @return string
     */
    public function getCtrlDate()
    {
        return $this->ctrl_date;
    }

    /**
     * Returns the value of field access_url
     *
     * @return string
     */
    public function getAccessUrl()
    {
        return $this->access_url;
    }

    /**
     * Returns the value of field post_param
     *
     * @return string
     */
    public function getPostParam()
    {
        return $this->post_param;
    }

    /**
     * Returns the value of field ip_address
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * Returns the value of field user_agent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->user_agent;
    }

    /**
     * Returns the value of field memo
     *
     * @return string
     */
    public function getMemo()
    {
        return $this->memo;
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
        return 't_log';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return TLog[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return TLog
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
