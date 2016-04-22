//============================================== 
//  Чтение/Запись контекста

function TransitContext(p_action, p_context, p_text)
{
  var  kernel ;

                    kernel=parent.frames["menu"] ;
  if(kernel==null)  kernel=parent.parent.frames["menu"] ;

  if(p_action=="save")
  {
    if(p_context=="password")  kernel.document.getElementById("glbPassword").value=p_text ;
    if(p_context=="session" )  kernel.document.getElementById("glbSession" ).value=p_text ;
    if(p_context=="value"   )  kernel.document.getElementById("glbValue"   ).value=p_text ;
    if(p_context=="callback")  kernel.document.getElementById("glbCallBack").value=p_text ;
    if(p_context=="user"    )  kernel.document.getElementById("glbUser"    ).value=p_text ;
  }
  else
  {
    if(p_context=="password")  return(kernel.document.getElementById("glbPassword").value) ;
    if(p_context=="session" )  return(kernel.document.getElementById("glbSession" ).value) ;
    if(p_context=="value"   )  return(kernel.document.getElementById("glbValue"   ).value) ;
    if(p_context=="callback")  return(kernel.document.getElementById("glbCallBack").value) ;
    if(p_context=="user"    )  return(kernel.document.getElementById("glbUser"    ).value) ;

       window.alert("Unknown p_context in TransitContext function") ;
  }
}
//============================================== 
//  Формирование строки случайных символов

function GetRandomString(p_size)
{
  var  Alphabet ;
  var  Alphabet_size ;
  var  result ;

    Alphabet     ="abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ0123456789" ;
    Alphabet_size= Alphabet.length ;

             result="" ;
 
  while(result.length<p_size)  result+=Alphabet[Math.random()*Alphabet_size|0] ;

  return result ;
}
//============================================== 
//  Шифрование строки

function Crypto_encode(p_text, p_key)
{
  var  result ;

   if(p_text=="")
   {
         result="" ;
   }
   else
   {
             result=CryptoJS.DES.encrypt(p_text, p_key) ;
   }

      return result ;
}
//============================================== 
//  Расшифровывание строки

function Crypto_decode(p_text, p_key)
{
  var  result ;

   if(p_text=="")
   {
         return "" ;
   }
   else
   {
             result=CryptoJS.DES.decrypt(p_text, p_key) ;
      return result.toString(CryptoJS.enc.Utf8) ;
   }
}
//============================================== 
//  Генерация контрольной группы символов

function Check_generate()
{
  return "Check"+GetRandomString(32) ;
}
//============================================== 
//  Проверка контрольной  группы символов

function Check_validate(p_text)
{
  if(p_text.substr(0,5)=="Check")  return true ;

     return false ;
}
//============================================== 
//  Генерация пары ключей электронной подписи
//
//   0 - secret key, 1 - public key

function Sign_generate()
{
  var  Key_pair= new Array(2) ;

	Key_pair[0]="RSA"+GetRandomString(32) ;
	Key_pair[1]= Key_pair[0] ;

  return Key_pair ;
}
//============================================== 
//  Формирование подписанного контента

function Sign_encode(p_text, p_sender_key, p_receiver_key)
{
 var  data_1 ;
 var  data_1_s ;
 var  data_2 ;

   data_1  =Crypto_encode(p_text, p_sender_key  ) ;
   data_1_s=""+data_1 ;
   data_2  =Crypto_encode(data_1_s, p_receiver_key) ;

  return data_2 ;
}
//============================================== 
//  Восстановление подписанного контента

function Sign_decode(p_text, p_sender_key, p_receiver_key)
{
 var  data_1 ;
 var  data_2 ;

   data_1=Crypto_decode(p_text, p_receiver_key) ;
   data_2=Crypto_decode(data_1, p_sender_key  ) ;

  return data_2 ;
}
//============================================== 
//  Отображение страницы подсказки

  function GoToHelp()
  {
    var  v_help_html ;
    var  v_pos ;

         v_help_html=window.location.pathname ;
               v_pos=v_help_html.lastIndexOf('/') ;
         v_help_html=v_help_html.substr(v_pos+1) ;
               v_pos=v_help_html.indexOf('.') ;
         v_help_html=v_help_html.substr(0, v_pos) ;

    window.open("help/"+v_help_html+".html") ;
  } 
//============================================== 
//  Переход в окно обратной связи

  function GoToCallBack()
  {
    var  v_session ;
    var  v_form ;
    var  v_pos ;

         v_session=TransitContext("restore","session","") ;

         v_form=window.location.pathname ;
         v_pos =v_form.lastIndexOf('/') ;
         v_form=v_form.substr(v_pos+1) ;
         v_pos =v_form.indexOf('.') ;
         v_form=v_form.substr(0, v_pos) ;

    window.open("callback.php"+"?Session="+v_session+"&Form="+v_form) ;
  } 
