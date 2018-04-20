<?php

namespace LyVirgo;

class Netease{
    private $AppKey='Your AppKey';
    private $AppSecret='Your AppSecret';

    private function curl_post($url,$data)
    {
        $header = $this->getHeader();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(['AppKey: '.$this->AppKey,'Content-Type:application/x-www-form-urlencoded;charset=utf-8'],$header));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * 请求头
     * @return array
     */
    private function getHeader()
    {
        $Nonce = str_random(64);
        $CurTime = strval(time());
        $CheckSum = $this->getCheckSum($Nonce, $CurTime);
        $header = ['Nonce:' . $Nonce, 'CurTime:' . $CurTime, 'CheckSum:' . $CheckSum];
        return $header;
    }

    private function getCheckSum($Nonce,$CurTime)
    {
        return hash('sha1',$this->AppSecret.$Nonce.$CurTime);
    }

    /**
     * 验证消息抄送
     * @param string $checksum
     * @param string $curtime
     * @param string $md5
     * @param string $input
     * @return bool
     */
    public function checkMsg($checksum='',$curtime='',$md5='',$input='')
    {
        if(empty($checksum) || empty($curtime) || empty($md5)){
            return false;
        }
        $varMd5=md5($input);
        if($md5!=$varMd5){
            return false;
        }
        $varChecksum=$this->getCheckSum($md5,$curtime);
        if($checksum==$varChecksum){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 创建聊天室
     * @param string $creator
     * @param string $name
     * @param string $announcement
     * @return string
     */
    public function chatroomCreate($creator='',$name='',$announcement='')
    {
        $url='https://api.netease.im/nimserver/chatroom/create.action';
        $data=['creator'=>$creator,'name'=>$name,'announcement'=>$announcement];
        $res=$this->curl_post($url,http_build_query($data));
        $res=json_decode($res,true);
        if(isset($res['code']) && $res['code']=='200'){
            return $res['chatroom']['roomid'];
        }else{
            return '';
        }
    }

    /**
     * 请求聊天室地址
     * @param string $roomid
     * @param string $accid
     * @param int $clienttype
     * @return mixed
     */
    public function chatroomRequestAddr($roomid='',$accid='',$clienttype=1)
    {
        $url='https://api.netease.im/nimserver/chatroom/requestAddr.action';
        $data=['roomid'=>$roomid,'accid'=>$accid,'clienttype'=>$clienttype];
        $res=$this->curl_post($url,http_build_query($data));
        $res=json_decode($res,true);
        if(isset($res['code']) && $res['code']=='200' && isset($res['addr'])){
            return $res['addr'];
        }else{
            return [];
        }
    }

    /**
     * 封禁网易云通信ID
     * @param $accid
     * @return bool
     */
    public function userBlock($accid)
    {
        $url='https://api.netease.im/nimserver/user/block.action';
        $data=['accid'=>$accid,'needkick'=>'true'];
        $res=$this->curl_post($url,http_build_query($data));
        $res=json_decode($res,true);
        if(isset($res['code']) && $res['code']=='200'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 解禁网易云通信ID
     * @param $accid
     * @return bool
     */
    public function userUnblock($accid)
    {
        $url='https://api.netease.im/nimserver/user/unblock.action';
        $data=['accid'=>$accid];
        $res=$this->curl_post($url,http_build_query($data));
        $res=json_decode($res,true);
        if(isset($res['code']) && $res['code']=='200'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 删除聊天室云端历史消息
     * @param array $data
     * @return bool
     */
    public function chatroomDeleteHistoryMessage($data=[])
    {
        $url='https://api.netease.im/nimserver/chatroom/deleteHistoryMessage.action';
        $res=$this->curl_post($url,http_build_query($data));
        $res=json_decode($res,true);
        if(isset($res['code']) && $res['code']=='200'){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取用户名片
     * @param $accids
     * @return array
     */
    public function userGetUinfos($accids)
    {
        $url='https://api.netease.im/nimserver/user/getUinfos.action';
        $data=['accids'=>json_encode($accids)];
        $res=$this->curl_post($url,http_build_query($data));
        $res=json_decode($res,true);
        if(isset($res['code']) && $res['code']=='200'){
            return $res['uinfos'];
        }else{
            return [];
        }
    }
}