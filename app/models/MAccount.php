<?php

class MAccount extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    protected $accnt_no;

    /**
     *
     * @var string
     */
    protected $corporate_id;

    /**
     *
     * @var string
     */
    protected $user_id;

    /**
     *
     * @var string
     */
    protected $pass_word;

    /**
     *
     * @var string
     */
    protected $tentative_pass_word;

    /**
     *
     * @var string
     */
    protected $hash;

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
    protected $button1_use_flg;

    /**
     *
     * @var string
     */
    protected $button2_use_flg;

    /**
     *
     * @var string
     */
    protected $button3_use_flg;

    /**
     *
     * @var string
     */
    protected $button4_use_flg;

    /**
     *
     * @var string
     */
    protected $button5_use_flg;

    /**
     *
     * @var string
     */
    protected $button6_use_flg;

    /**
     *
     * @var string
     */
    protected $button7_use_flg;

    /**
     *
     * @var string
     */
    protected $button8_use_flg;

    /**
     *
     * @var string
     */
    protected $button9_use_flg;

    /**
     *
     * @var string
     */
    protected $button10_use_flg;

    /**
     *
     * @var string
     */
    protected $button11_use_flg;

    /**
     *
     * @var string
     */
    protected $button12_use_flg;

    /**
     *
     * @var string
     */
    protected $button13_use_flg;

    /**
     *
     * @var string
     */
    protected $button14_use_flg;

    /**
     *
     * @var string
     */
    protected $button15_use_flg;

    /**
     *
     * @var string
     */
    protected $button16_use_flg;

    /**
     *
     * @var string
     */
    protected $button17_use_flg;

    /**
     *
     * @var string
     */
    protected $button18_use_flg;

    /**
     *
     * @var string
     */
    protected $button19_use_flg;

    /**
     *
     * @var string
     */
    protected $button20_use_flg;

    /**
     *
     * @var string
     */
    protected $button21_use_flg;

    /**
     *
     * @var string
     */
    protected $button22_use_flg;

    /**
     *
     * @var string
     */
    protected $button23_use_flg;

    /**
     *
     * @var string
     */
    protected $button24_use_flg;

    /**
     *
     * @var string
     */
    protected $button25_use_flg;

    /**
     *
     * @var string
     */
    protected $button26_use_flg;

    /**
     *
     * @var string
     */
    protected $button27_use_flg;

    /**
     *
     * @var string
     */
    protected $button28_use_flg;

    /**
     *
     * @var string
     */
    protected $button29_use_flg;

    /**
     *
     * @var string
     */
    protected $button30_use_flg;

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
     *
     * @var string
     */
    protected $login_disp_name;

    /**
     *
     * @var string
     */
    protected $mail_address;

    /**
     *
     * @var integer
     */
    protected $del_flg;

    /**
     * Method to set the value of field accnt_no
     *
     * @param integer $accnt_no
     * @return $this
     */
    public function setAccntNo($accnt_no)
    {
        $this->accnt_no = $accnt_no;

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
     * Method to set the value of field tentative_pass_word
     *
     * @param string $tentative_pass_word
     * @return $this
     */
    public function setTentativePassWord($tentative_pass_word)
    {
        $this->tentative_pass_word = $tentative_pass_word;

        return $this;
    }

    /**
     * Method to set the value of field hash
     *
     * @param string $hash
     * @return $this
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

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
     * Method to set the value of field button1_use_flg
     *
     * @param string $button1_use_flg
     * @return $this
     */
    public function setButton1UseFlg($button1_use_flg)
    {
        $this->button1_use_flg = $button1_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button2_use_flg
     *
     * @param string $button2_use_flg
     * @return $this
     */
    public function setButton2UseFlg($button2_use_flg)
    {
        $this->button2_use_flg = $button2_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button3_use_flg
     *
     * @param string $button3_use_flg
     * @return $this
     */
    public function setButton3UseFlg($button3_use_flg)
    {
        $this->button3_use_flg = $button3_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button4_use_flg
     *
     * @param string $button4_use_flg
     * @return $this
     */
    public function setButton4UseFlg($button4_use_flg)
    {
        $this->button4_use_flg = $button4_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button5_use_flg
     *
     * @param string $button5_use_flg
     * @return $this
     */
    public function setButton5UseFlg($button5_use_flg)
    {
        $this->button5_use_flg = $button5_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button6_use_flg
     *
     * @param string $button6_use_flg
     * @return $this
     */
    public function setButton6UseFlg($button6_use_flg)
    {
        $this->button6_use_flg = $button6_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button7_use_flg
     *
     * @param string $button7_use_flg
     * @return $this
     */
    public function setButton7UseFlg($button7_use_flg)
    {
        $this->button7_use_flg = $button7_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button8_use_flg
     *
     * @param string $button8_use_flg
     * @return $this
     */
    public function setButton8UseFlg($button8_use_flg)
    {
        $this->button8_use_flg = $button8_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button9_use_flg
     *
     * @param string $button9_use_flg
     * @return $this
     */
    public function setButton9UseFlg($button9_use_flg)
    {
        $this->button9_use_flg = $button9_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button10_use_flg
     *
     * @param string $button10_use_flg
     * @return $this
     */
    public function setButton10UseFlg($button10_use_flg)
    {
        $this->button10_use_flg = $button10_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button11_use_flg
     *
     * @param string $button11_use_flg
     * @return $this
     */
    public function setButton11UseFlg($button11_use_flg)
    {
        $this->button11_use_flg = $button11_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button12_use_flg
     *
     * @param string $button12_use_flg
     * @return $this
     */
    public function setButton12UseFlg($button12_use_flg)
    {
        $this->button12_use_flg = $button12_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button13_use_flg
     *
     * @param string $button13_use_flg
     * @return $this
     */
    public function setButton13UseFlg($button13_use_flg)
    {
        $this->button13_use_flg = $button13_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button14_use_flg
     *
     * @param string $button14_use_flg
     * @return $this
     */
    public function setButton14UseFlg($button14_use_flg)
    {
        $this->button14_use_flg = $button14_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button15_use_flg
     *
     * @param string $button15_use_flg
     * @return $this
     */
    public function setButton15UseFlg($button15_use_flg)
    {
        $this->button15_use_flg = $button15_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button16_use_flg
     *
     * @param string $button16_use_flg
     * @return $this
     */
    public function setButton16UseFlg($button16_use_flg)
    {
        $this->button16_use_flg = $button16_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button17_use_flg
     *
     * @param string $button17_use_flg
     * @return $this
     */
    public function setButton17UseFlg($button17_use_flg)
    {
        $this->button17_use_flg = $button17_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button18_use_flg
     *
     * @param string $button18_use_flg
     * @return $this
     */
    public function setButton18UseFlg($button18_use_flg)
    {
        $this->button18_use_flg = $button18_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button19_use_flg
     *
     * @param string $button19_use_flg
     * @return $this
     */
    public function setButton19UseFlg($button19_use_flg)
    {
        $this->button19_use_flg = $button19_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button20_use_flg
     *
     * @param string $button20_use_flg
     * @return $this
     */
    public function setButton20UseFlg($button20_use_flg)
    {
        $this->button20_use_flg = $button20_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button21_use_flg
     *
     * @param string $button21_use_flg
     * @return $this
     */
    public function setButton21UseFlg($button21_use_flg)
    {
        $this->button21_use_flg = $button21_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button22_use_flg
     *
     * @param string $button22_use_flg
     * @return $this
     */
    public function setButton22UseFlg($button22_use_flg)
    {
        $this->button22_use_flg = $button22_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button23_use_flg
     *
     * @param string $button23_use_flg
     * @return $this
     */
    public function setButton23UseFlg($button23_use_flg)
    {
        $this->button23_use_flg = $button23_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button24_use_flg
     *
     * @param string $button24_use_flg
     * @return $this
     */
    public function setButton24UseFlg($button24_use_flg)
    {
        $this->button24_use_flg = $button24_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button25_use_flg
     *
     * @param string $button25_use_flg
     * @return $this
     */
    public function setButton25UseFlg($button25_use_flg)
    {
        $this->button25_use_flg = $button25_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button26_use_flg
     *
     * @param string $button26_use_flg
     * @return $this
     */
    public function setButton26UseFlg($button26_use_flg)
    {
        $this->button26_use_flg = $button26_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button27_use_flg
     *
     * @param string $button27_use_flg
     * @return $this
     */
    public function setButton27UseFlg($button27_use_flg)
    {
        $this->button27_use_flg = $button27_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button28_use_flg
     *
     * @param string $button28_use_flg
     * @return $this
     */
    public function setButton28UseFlg($button28_use_flg)
    {
        $this->button28_use_flg = $button28_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button29_use_flg
     *
     * @param string $button29_use_flg
     * @return $this
     */
    public function setButton29UseFlg($button29_use_flg)
    {
        $this->button29_use_flg = $button29_use_flg;

        return $this;
    }

    /**
     * Method to set the value of field button30_use_flg
     *
     * @param string $button30_use_flg
     * @return $this
     */
    public function setButton30UseFlg($button30_use_flg)
    {
        $this->button30_use_flg = $button30_use_flg;

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
     * Method to set the value of field login_disp_name
     *
     * @param string $login_disp_name
     * @return $this
     */
    public function setLoginDispName($login_disp_name)
    {
        $this->login_disp_name = $login_disp_name;

        return $this;
    }

    /**
     * Method to set the value of field mail_address
     *
     * @param string $mail_address
     * @return $this
     */
    public function setMailAddress($mail_address)
    {
        $this->mail_address = $mail_address;

        return $this;
    }

    /**
     * Method to set the value of field del_flg
     *
     * @param integer $del_flg
     * @return $this
     */
    public function setDelFlg($del_flg)
    {
        $this->del_flg = $del_flg;

        return $this;
    }

    /**
     * Returns the value of field accnt_no
     *
     * @return integer
     */
    public function getAccntNo()
    {
        return $this->accnt_no;
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
     * Returns the value of field user_id
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->user_id;
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
     * Returns the value of field tentative_pass_word
     *
     * @return string
     */
    public function getTentativePassWord()
    {
        return $this->tentative_pass_word;
    }

    /**
     * Returns the value of field hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
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
     * Returns the value of field button1_use_flg
     *
     * @return string
     */
    public function getButton1UseFlg()
    {
        return $this->button1_use_flg;
    }

    /**
     * Returns the value of field button2_use_flg
     *
     * @return string
     */
    public function getButton2UseFlg()
    {
        return $this->button2_use_flg;
    }

    /**
     * Returns the value of field button3_use_flg
     *
     * @return string
     */
    public function getButton3UseFlg()
    {
        return $this->button3_use_flg;
    }

    /**
     * Returns the value of field button4_use_flg
     *
     * @return string
     */
    public function getButton4UseFlg()
    {
        return $this->button4_use_flg;
    }

    /**
     * Returns the value of field button5_use_flg
     *
     * @return string
     */
    public function getButton5UseFlg()
    {
        return $this->button5_use_flg;
    }

    /**
     * Returns the value of field button6_use_flg
     *
     * @return string
     */
    public function getButton6UseFlg()
    {
        return $this->button6_use_flg;
    }

    /**
     * Returns the value of field button7_use_flg
     *
     * @return string
     */
    public function getButton7UseFlg()
    {
        return $this->button7_use_flg;
    }

    /**
     * Returns the value of field button8_use_flg
     *
     * @return string
     */
    public function getButton8UseFlg()
    {
        return $this->button8_use_flg;
    }

    /**
     * Returns the value of field button9_use_flg
     *
     * @return string
     */
    public function getButton9UseFlg()
    {
        return $this->button9_use_flg;
    }

    /**
     * Returns the value of field button10_use_flg
     *
     * @return string
     */
    public function getButton10UseFlg()
    {
        return $this->button10_use_flg;
    }

    /**
     * Returns the value of field button11_use_flg
     *
     * @return string
     */
    public function getButton11UseFlg()
    {
        return $this->button11_use_flg;
    }

    /**
     * Returns the value of field button12_use_flg
     *
     * @return string
     */
    public function getButton12UseFlg()
    {
        return $this->button12_use_flg;
    }

    /**
     * Returns the value of field button13_use_flg
     *
     * @return string
     */
    public function getButton13UseFlg()
    {
        return $this->button13_use_flg;
    }

    /**
     * Returns the value of field button14_use_flg
     *
     * @return string
     */
    public function getButton14UseFlg()
    {
        return $this->button14_use_flg;
    }

    /**
     * Returns the value of field button15_use_flg
     *
     * @return string
     */
    public function getButton15UseFlg()
    {
        return $this->button15_use_flg;
    }

    /**
     * Returns the value of field button16_use_flg
     *
     * @return string
     */
    public function getButton16UseFlg()
    {
        return $this->button16_use_flg;
    }

    /**
     * Returns the value of field button17_use_flg
     *
     * @return string
     */
    public function getButton17UseFlg()
    {
        return $this->button17_use_flg;
    }

    /**
     * Returns the value of field button18_use_flg
     *
     * @return string
     */
    public function getButton18UseFlg()
    {
        return $this->button18_use_flg;
    }

    /**
     * Returns the value of field button19_use_flg
     *
     * @return string
     */
    public function getButton19UseFlg()
    {
        return $this->button19_use_flg;
    }

    /**
     * Returns the value of field button20_use_flg
     *
     * @return string
     */
    public function getButton20UseFlg()
    {
        return $this->button20_use_flg;
    }

    /**
     * Returns the value of field button21_use_flg
     *
     * @return string
     */
    public function getButton21UseFlg()
    {
        return $this->button21_use_flg;
    }

    /**
     * Returns the value of field button22_use_flg
     *
     * @return string
     */
    public function getButton22UseFlg()
    {
        return $this->button22_use_flg;
    }

    /**
     * Returns the value of field button23_use_flg
     *
     * @return string
     */
    public function getButton23UseFlg()
    {
        return $this->button23_use_flg;
    }

    /**
     * Returns the value of field button24_use_flg
     *
     * @return string
     */
    public function getButton24UseFlg()
    {
        return $this->button24_use_flg;
    }

    /**
     * Returns the value of field button25_use_flg
     *
     * @return string
     */
    public function getButton25UseFlg()
    {
        return $this->button25_use_flg;
    }

    /**
     * Returns the value of field button26_use_flg
     *
     * @return string
     */
    public function getButton26UseFlg()
    {
        return $this->button26_use_flg;
    }

    /**
     * Returns the value of field button27_use_flg
     *
     * @return string
     */
    public function getButton27UseFlg()
    {
        return $this->button27_use_flg;
    }

    /**
     * Returns the value of field button28_use_flg
     *
     * @return string
     */
    public function getButton28UseFlg()
    {
        return $this->button28_use_flg;
    }

    /**
     * Returns the value of field button29_use_flg
     *
     * @return string
     */
    public function getButton29UseFlg()
    {
        return $this->button29_use_flg;
    }

    /**
     * Returns the value of field button30_use_flg
     *
     * @return string
     */
    public function getButton30UseFlg()
    {
        return $this->button30_use_flg;
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
     * Returns the value of field login_disp_name
     *
     * @return string
     */
    public function getLoginDispName()
    {
        return $this->login_disp_name;
    }

    /**
     * Returns the value of field mail_address
     *
     * @return string
     */
    public function getMailAddress()
    {
        return $this->mail_address;
    }

    /**
     * Returns the value of field del_flg
     *
     * @return integer
     */
    public function getDelFlg()
    {
        return $this->del_flg;
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
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("public");
        $this->hasOne("account_no", "MContractResource", "account_no");
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

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'm_account';
    }

}
