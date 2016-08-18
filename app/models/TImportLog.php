<?php

class TImportLog extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var string
     */
    protected $order_req_no;

    /**
     *
     * @var integer
     */
    protected $order_req_line_no;

    /**
     *
     * @var string
     */
    protected $cster_emply_cd;

    /**
     *
     * @var string
     */
    protected $werer_name;

    /**
     *
     * @var string
     */
    protected $werer_name_kana;

    /**
     *
     * @var string
     */
    protected $sex_kbn;

    /**
     *
     * @var string
     */
    protected $rntl_sect_cd;

    /**
     *
     * @var string
     */
    protected $rent_pattern_code;

    /**
     *
     * @var string
     */
    protected $wear_start;

    /**
     *
     * @var string
     */
    protected $wear_end;

    /**
     *
     * @var string
     */
    protected $order_kbn;

    /**
     *
     * @var string
     */
    protected $order_reason_kbn;

    /**
     *
     * @var string
     */
    protected $item_cd;

    /**
     *
     * @var string
     */
    protected $size_cd;

    /**
     *
     * @var string
     */
    protected $color_cd;

    /**
     *
     * @var string
     */
    protected $werer_cd;

    /**
     *
     * @var integer
     */
    protected $quantity;

    /**
     *
     * @var string
     */
    protected $message;

    /**
     *
     * @var integer
     */
    protected $send_flg;

    /**
     *
     * @var string
     */
    protected $emply_order_req_no;

    /**
     *
     * @var string
     */
    protected $user_id;

    /**
     *
     * @var string
     */
    protected $import_time;

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
     * Method to set the value of field order_req_no
     *
     * @param string $order_req_no
     * @return $this
     */
    public function setOrderReqNo($order_req_no)
    {
        $this->order_req_no = $order_req_no;

        return $this;
    }

    /**
     * Method to set the value of field order_req_line_no
     *
     * @param integer $order_req_line_no
     * @return $this
     */
    public function setOrderReqLineNo($order_req_line_no)
    {
        $this->order_req_line_no = $order_req_line_no;

        return $this;
    }

    /**
     * Method to set the value of field cster_emply_cd
     *
     * @param string $cster_emply_cd
     * @return $this
     */
    public function setCsterEmplyCd($cster_emply_cd)
    {
        $this->cster_emply_cd = $cster_emply_cd;

        return $this;
    }

    /**
     * Method to set the value of field werer_name
     *
     * @param string $werer_name
     * @return $this
     */
    public function setWererName($werer_name)
    {
        $this->werer_name = $werer_name;

        return $this;
    }

    /**
     * Method to set the value of field werer_name_kana
     *
     * @param string $werer_name_kana
     * @return $this
     */
    public function setWererNameKana($werer_name_kana)
    {
        $this->werer_name_kana = $werer_name_kana;

        return $this;
    }

    /**
     * Method to set the value of field sex_kbn
     *
     * @param string $sex_kbn
     * @return $this
     */
    public function setSexKbn($sex_kbn)
    {
        $this->sex_kbn = $sex_kbn;

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
     * Method to set the value of field rent_pattern_code
     *
     * @param string $rent_pattern_code
     * @return $this
     */
    public function setRentPatternCode($rent_pattern_code)
    {
        $this->rent_pattern_code = $rent_pattern_code;

        return $this;
    }

    /**
     * Method to set the value of field wear_start
     *
     * @param string $wear_start
     * @return $this
     */
    public function setWearStart($wear_start)
    {
        $this->wear_start = $wear_start;

        return $this;
    }

    /**
     * Method to set the value of field wear_end
     *
     * @param string $wear_end
     * @return $this
     */
    public function setWearEnd($wear_end)
    {
        $this->wear_end = $wear_end;

        return $this;
    }

    /**
     * Method to set the value of field order_kbn
     *
     * @param string $order_kbn
     * @return $this
     */
    public function setOrderKbn($order_kbn)
    {
        $this->order_kbn = $order_kbn;

        return $this;
    }

    /**
     * Method to set the value of field order_reason_kbn
     *
     * @param string $order_reason_kbn
     * @return $this
     */
    public function setOrderReasonKbn($order_reason_kbn)
    {
        $this->order_reason_kbn = $order_reason_kbn;

        return $this;
    }

    /**
     * Method to set the value of field item_cd
     *
     * @param string $item_cd
     * @return $this
     */
    public function setItemCd($item_cd)
    {
        $this->item_cd = $item_cd;

        return $this;
    }

    /**
     * Method to set the value of field size_cd
     *
     * @param string $size_cd
     * @return $this
     */
    public function setSizeCd($size_cd)
    {
        $this->size_cd = $size_cd;

        return $this;
    }

    /**
     * Method to set the value of field color_cd
     *
     * @param string $color_cd
     * @return $this
     */
    public function setColorCd($color_cd)
    {
        $this->color_cd = $color_cd;

        return $this;
    }

    /**
     * Method to set the value of field werer_cd
     *
     * @param string $werer_cd
     * @return $this
     */
    public function setWererCd($werer_cd)
    {
        $this->werer_cd = $werer_cd;

        return $this;
    }

    /**
     * Method to set the value of field quantity
     *
     * @param integer $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Method to set the value of field message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Method to set the value of field send_flg
     *
     * @param integer $send_flg
     * @return $this
     */
    public function setSendFlg($send_flg)
    {
        $this->send_flg = $send_flg;

        return $this;
    }

    /**
     * Method to set the value of field emply_order_req_no
     *
     * @param string $emply_order_req_no
     * @return $this
     */
    public function setEmplyOrderReqNo($emply_order_req_no)
    {
        $this->emply_order_req_no = $emply_order_req_no;

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
     * Method to set the value of field import_time
     *
     * @param string $import_time
     * @return $this
     */
    public function setImportTime($import_time)
    {
        $this->import_time = $import_time;

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
     * Returns the value of field order_req_no
     *
     * @return string
     */
    public function getOrderReqNo()
    {
        return $this->order_req_no;
    }

    /**
     * Returns the value of field order_req_line_no
     *
     * @return integer
     */
    public function getOrderReqLineNo()
    {
        return $this->order_req_line_no;
    }

    /**
     * Returns the value of field cster_emply_cd
     *
     * @return string
     */
    public function getCsterEmplyCd()
    {
        return $this->cster_emply_cd;
    }

    /**
     * Returns the value of field werer_name
     *
     * @return string
     */
    public function getWererName()
    {
        return $this->werer_name;
    }

    /**
     * Returns the value of field werer_name_kana
     *
     * @return string
     */
    public function getWererNameKana()
    {
        return $this->werer_name_kana;
    }

    /**
     * Returns the value of field sex_kbn
     *
     * @return string
     */
    public function getSexKbn()
    {
        return $this->sex_kbn;
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
     * Returns the value of field rent_pattern_code
     *
     * @return string
     */
    public function getRentPatternCode()
    {
        return $this->rent_pattern_code;
    }

    /**
     * Returns the value of field wear_start
     *
     * @return string
     */
    public function getWearStart()
    {
        return $this->wear_start;
    }

    /**
     * Returns the value of field wear_end
     *
     * @return string
     */
    public function getWearEnd()
    {
        return $this->wear_end;
    }

    /**
     * Returns the value of field order_kbn
     *
     * @return string
     */
    public function getOrderKbn()
    {
        return $this->order_kbn;
    }

    /**
     * Returns the value of field order_reason_kbn
     *
     * @return string
     */
    public function getOrderReasonKbn()
    {
        return $this->order_reason_kbn;
    }

    /**
     * Returns the value of field item_cd
     *
     * @return string
     */
    public function getItemCd()
    {
        return $this->item_cd;
    }

    /**
     * Returns the value of field size_cd
     *
     * @return string
     */
    public function getSizeCd()
    {
        return $this->size_cd;
    }

    /**
     * Returns the value of field color_cd
     *
     * @return string
     */
    public function getColorCd()
    {
        return $this->color_cd;
    }

    /**
     * Returns the value of field werer_cd
     *
     * @return string
     */
    public function getWererCd()
    {
        return $this->werer_cd;
    }

    /**
     * Returns the value of field quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Returns the value of field message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the value of field send_flg
     *
     * @return integer
     */
    public function getSendFlg()
    {
        return $this->send_flg;
    }

    /**
     * Returns the value of field emply_order_req_no
     *
     * @return string
     */
    public function getEmplyOrderReqNo()
    {
        return $this->emply_order_req_no;
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
     * Returns the value of field import_time
     *
     * @return string
     */
    public function getImportTime()
    {
        return $this->import_time;
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
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return TImportLog[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return TImportLog
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
        return 't_import_log';
    }

}
