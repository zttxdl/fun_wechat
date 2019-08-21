<?php
/**
 * Created by PhpStorm.
 * User: zhangtaotao
 * Date: 2019/6/28
 * Time: 2:56 PM
 */

namespace app\common\service;
use PHPMailer\PHPMailer\PHPMailer;
use think\Model;


// 这里继承model的意义是，方便在控制器端，通过model('Email','service') 的方式进行调用， 其实完全可不继承model ，直接在控制器端通过 new Email() 的方式进行调用
class Email extends Model
{
    /**
     * 通过邮箱进行验证码的传输-----发件人小明，收件人小红
     * @param email $email  [收件人邮箱地址]
     * @param int  $code    [验证码]
     * @throws \PHPMailer\Exception [邮件处理异常]
     */
    public function send_email($email,$code){
        $toemail=$email;//定义收件人的邮箱
        $sendmail = '*************@163.com'; //发件人邮箱
        $sendmailpswd = "zbdaikawctzeghfj"; //客户端授权密码,而不是邮箱的登录密码，就是手机发送短信之后弹出来的一长串的密码
        $send_name = '小明';// 设置发件人信息，如邮件格式说明中的发件人，
        $to_name = '小红';//设置收件人信息，如邮件格式说明中的收件人
        $mail = new PHPMailer();
        $mail->isSMTP();// 使用SMTP服务
        $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
        $mail->Host = "smtp.163.com";// 发送方的SMTP服务器地址 163邮箱：smtp.163.com，qq邮箱：smtp.qq.com
        $mail->SMTPAuth = true;// 是否使用身份验证
        $mail->Username = $sendmail;//// 发送方的
        $mail->Password = $sendmailpswd;//客户端授权密码,而不是邮箱的登录密码！
        $mail->SMTPSecure = "";// 链接方式 如果使用QQ邮箱；需要把此项改为  ssl,如果是163邮箱，则为空
        $mail->Port = 25;//  sina端口110或25） //qq  465 587
        $mail->setFrom($sendmail, $send_name);// 设置发件人信息，如邮件格式说明中的发件人，
        $mail->addAddress($toemail, $to_name);// 设置收件人信息，如邮件格式说明中的收件人，
        $mail->addReplyTo($sendmail, $send_name);// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
        $mail->Subject = "这是邮件的标题！";// 邮件标题
        $mail->Body = "这是一个测试邮件，您的验证码是： $code 记得回复我哟！么么哒...";// 邮件正文
       if(!$mail->send()){
           return json_error(400,$mail->ErrorInfo);//返回数据格式自己定义的一个函数
       }else{
            return json_success(200,"验证码已经发送，请注意查收");
       }
   }
}