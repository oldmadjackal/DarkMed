//============================================== 
//  Объект VLI

function Vli()
{
   this.sign   = 1 ;
   this.data   =Array(256);
   this.data[0]= 0 ;
   this.data[1]=NaN ;
   this.log  =null ;
}

//============================================== 
//  Объект RSA

function Rsa()
{
   this.base = 0 ;
   this.keyN =new Vli() ;
   this.keyD =new Vli() ;
   this.keyE =new Vli() ;
   this.log  =null ;
}

///============================================== 
//  Метод - задание VLI по стрoке десятичного числа

Vli.prototype.assignDecimal=function(p_text)
{
  var  digits_10=Array(512) ;
  var  digits_2 =Array(2048) ;
  var  i, n, p, s, b, k ;

  
  for(i=0 ; p_text.length>0 ; i++) 
  {
     digits_10[i]=parseInt(p_text.substr(   p_text.length-1)) ;
          p_text =         p_text.substr(0, p_text.length-1) ;
  }

  for(n=0 ; ; n++)
  {
          digits_2 [n] =digits_10[0]%2 ;
          digits_10[0]-=digits_2 [n]  ;

     if(isNaN(digits_10[1]) && digits_10[0]==0)  break ;

      for(i=0 ; i<digits_10.length  && !isNaN(digits_10[i]) ; i++) ;
     
          i-- ;

      for(p=0 ; i>=0 ; i--)
      {
                    p    = digits_10[i]%2 ;
          digits_10[i  ] =(digits_10[i]-p)/2 ;
          digits_10[i-1]+=10*p ;

         if(digits_10[i]==0 && isNaN(digits_10[i+1]))  digits_10[i]=NaN ; 
      }
  }
     
  for(n=0, s=0, b=1, i=0, k=0 ; i<digits_2.length  && !isNaN(digits_2[i]) ; i++)
  {
      s+=digits_2[i]*b ;
      b =b*2 ;

    if(i%16==15) 
    {
         this.data[n]=s ;
                   n++ ;
                   s =0 ;
                   b =1 ;
    }      
  }

    if(i%16>0) 
    { 
          this.data[n]=s ;
                    n++ ;
    }
    
          this.data[n]=NaN ;
}

//============================================== 
//  Метод - задание VLI по стрoке 16-тичного числа

Vli.prototype.assignHex=function(p_text)
{
  var  i ;
  
       p_text=p_text.replace(/ /g, "") ;
       p_text=p_text.replace(/-/g, "") ;
  
  for(i=0 ;  ; i++) 
  {
     if(p_text.length<=4) {
            this.data[i]=parseInt(p_text, 16) ;
                                   break ;
                          }
          
            this.data[i]=parseInt(p_text.substr(-4, 4), 16) ;
                 p_text =         p_text.substr( 0, p_text.length-4) ;
  }

                    i++ ;   
          this.data[i]=NaN ;
}

//============================================== 
//  Метод - выдача значения VLI как 16-ричного числа

Vli.prototype.getHex=function()
{
  var  i, row ;
  
     row="" ;

  for(i=0 ; i<this.data.length  && !isNaN(this.data[i]) ; i++)
  {
              tmp="0000"+this.data[i].toString(16) ;
    if(i==0)  row=tmp.substr(-4) ;
    else      row=tmp.substr(-4)+row ; 
  }

  if(this.sign<0)  row="-"+row ;

   return(row) ;
}

Vli.prototype.getHex_=function()
{
  var  i, row ;
  
     row="" ;

  for(i=0 ; i<this.data.length  && !isNaN(this.data[i]) ; i++)
  {
              tmp="0000"+this.data[i].toString(16) ;
    if(i==0)  row=tmp.substr(-4) ;
    else      row=tmp.substr(-4)+"-"+row ; 
  }

  if(this.sign>0)  row="+"+row ;
  else             row="-"+row ;

   return(row) ;
}

//============================================== 
//  Метод - выдача значения VLI как десятичного числа

Vli.prototype.getDecimal=function()
{
  var  digits_10=Array(512) ;
  var  digits_2 =Array(2048) ;
  var  power_10 =Array(512) ;
  var  i, n, hex, x_dgt, shift, tmp ;

  
    hex=this.getHex() ; 

  for(n=0, i=hex.length-1 ; i>=0 ; n+=4, i--) 
  {
     digits_2[n  ]=0 ;
     digits_2[n+1]=0 ;
     digits_2[n+2]=0 ;
     digits_2[n+3]=0 ;

       x_dgt=parseInt(hex.substr(i, 1), 16) ;        
    if(x_dgt>=8) {  digits_2[n+3]=1 ;  x_dgt-=8 ;  }
    if(x_dgt>=4) {  digits_2[n+2]=1 ;  x_dgt-=4 ;  }
    if(x_dgt>=2) {  digits_2[n+1]=1 ;  x_dgt-=2 ;  }
    if(x_dgt==1)    digits_2[n  ]=1 ;
  }   

     digits_10[0]=0 ;
      power_10[0]=1 ;

  for(n=0 ; i<digits_2.length  && !isNaN(digits_2[n]) ; n++)
  {

     if(digits_2[n]==1)
     {
       for(shift=0, i=0 ; i<power_10.length  && !isNaN(power_10[i]) ; i++)
       {
         if(isNaN(digits_10[i]))  digits_10[i]=0 ;
                 
                                   tmp =digits_10[i]+power_10[i]+shift ;
                           digits_10[i]=tmp%10 ;
         if(digits_10[i]!=tmp)   shift = 1 ;
         else                    shift = 0 ;                
       }

         if(shift==1)  digits_10[i]=1 ;
     }

       for(shift=0, i=0 ; i<power_10.length  && !isNaN(power_10[i]) ; i++)
       {
                                  tmp =power_10[i]*2+shift ;
                           power_10[i]=tmp%10 ;
         if(power_10[i]!=tmp)   shift = 1 ;
         else                   shift = 0 ; 
       }

         if(shift==1)  power_10[i]=1 ;
  }

    for(row="", i=0 ; i<digits_10.length  && !isNaN(digits_10[i]) ; i++)  row=digits_10[i].toString()+row ;

  if(this.sign>0)  row="+"+row ;
  else             row="-"+row ;
    
  return(row) ;
}

//============================================== 
//  Метод - задание VLI

Vli.prototype.setVli=function(p_src)
{
   this.data=[].concat(p_src.data) ;
   this.sign=          p_src.sign ; 
}

//============================================== 
//  Формирование случайного числа

Vli.prototype.random=function(p_bytes)
{
  var  Alphabet ;
  var  Alphabet_size ;
  var  result ;

    Alphabet     ="abcdef0123456789" ;
    Alphabet_size= Alphabet.length ;
    TopAlpha     ="cdef" ;
    TopAlpha_size= TopAlpha.length ;

                                  result =TopAlpha[Math.random()*TopAlpha_size|0] ;
  while(result.length<p_bytes*2)  result+=Alphabet[Math.random()*Alphabet_size|0] ;

      this.assignHex(result) ;

  return result ;
}

//============================================== 
//  Метод - сложение VLI и 'короткого' числа

Vli.prototype.addNumber=function(p_number)
{
  var  i, shift, tmp ;
  
   if((this.sign>0 && p_number>0) ||
      (this.sign<0 && p_number<0)   ) 
   {
                                   tmp =this.data[0]+Math.abs(p_number) ;
                           this.data[0]=tmp%65536 ;
         if(this.data[0]!=tmp)   shift = 1 ;
         else                    shift = 0 ;

     for(i=1 ; i<this.data.length && !isNaN(this.data[i]) ; i++) 
     {
         if(shift==0)  break ;
         
                                   tmp =this.data[i]+shift ;
                           this.data[i]=tmp%65536 ;
         if(this.data[i]!=tmp)   shift = 1 ;
         else                    shift = 0 ;
     }

         if(shift==1)  this.data[i]=1 ;

   }
   else 
   {
           
   }

  return ; 
}

//============================================== 
//  Метод - Вычитание VLI и 'короткого' числа

Vli.prototype.subNumber=function(p_number)
{
  var  i ;

   if((this.sign>0 && p_number>0) ||
      (this.sign<0 && p_number<0)   ) 
   {
      if(this.data[0]>=Math.abs(p_number)) 
      {
          this.data[0]-=Math.abs(p_number) ;
                 return ;
      } 

            this.data[0]=this.data[0]+65536-Math.abs(p_number) ;

     for(i=1 ; i<this.data.length && !isNaN(this.data[i]) ; i++) 
     {
         if(this.data[i]>0)
         {
            this.data[i]-=1 ;
                 break ;
         }

            this.data[i]=65536-1 ;
     }

        if(this.data[i]==0 && isNaN(this.data[i+1]))  this.data[i]=NaN ;
   }
   else 
   {
           
   }

  return ; 
}

//============================================== 
//  Метод - умножение VLI на 'короткое' числа

Vli.prototype.mulNumber=function(p_number)
{
  var  i, shift, tmp ;

   if((this.sign>0 && p_number>0) ||
      (this.sign<0 && p_number<0)   )  this.sign= 1 ;
   else                                this.sign=-1 ;
         p_number=Math.abs(p_number) ;

  for(shift=0, i=0 ; i<this.data.length && !isNaN(this.data[i]) ; i++) 
  {
                tmp =this.data[i]*p_number+shift ;
        this.data[i]=tmp%65536 ;
              shift =(tmp-this.data[i])/65536 ;          
  }
   
     if(shift>0)  this.data[i]=shift ;
   
  return ; 
}

//============================================== 
//  Метод - деление VLI на 'короткое' числа

Vli.prototype.divNumber=function(p_number)
{
  var  i, shift, tmp ;

   if((this.sign>0 && p_number>0) ||
      (this.sign<0 && p_number<0)   )  this.sign= 1 ;
   else                                this.sign=-1 ;

         p_number=Math.abs(p_number) ;
   
  for(i=0 ; i<this.data.length && !isNaN(this.data[i]) ; i++) ;

      i-- ;
  
  for(shift=0 ; i>=0 ; i--)
  {
                tmp =shift*65536+this.data[i] ;
     if(tmp<p_number)
     {
                        this.data[i]= 0 ;
                              shift =tmp ;
     } 
     else
     {
              shift = tmp%p_number ;          
        this.data[i]=(tmp-shift)/p_number ;             
     }             
  }
   
   
  return(shift) ; 
}

//============================================== 
//  Метод - сложение VLI и VLI

Vli.prototype.addVli=function(p_vli)
{
  var  i, shift, tmp, len ;

   if((this.sign>0 && p_vli.sign>0) ||
      (this.sign<0 && p_vli.sign<0)   ) 
   {

      for(len=0 ; len<this .data.length && !isNaN(this .data[len]) ; len++) ;
      for(      ; len<p_vli.data.length && !isNaN(p_vli.data[len]) ; len++) ;

      for(shift=0, i=0 ; i<len ; i++)
      {
         if(isNaN(this .data[i]))  this .data[i]=0 ;
         if(isNaN(p_vli.data[i]))  p_vli.data[i]=0 ;
                 
                    tmp =this.data[i]+p_vli.data[i]+shift ;
            this.data[i]=tmp%65536 ;
               
         if(this.data[i]!=tmp)   shift = 1 ;
         else                    shift = 0 ;              
      }

         if(shift==1)  this.data[i]=1 ;

   }
   else 
   {
                    p_vli.sign=-p_vli.sign ;
        this.subVli(p_vli) ;
                    p_vli.sign=-p_vli.sign ;        
   }

  return ; 
}

//============================================== 
//  Метод - вычитание VLI и VLI

Vli.prototype.subVli=function(p_vli)
{
  var  res=new Vli()  ;
  var  i, shift, tmp , len, len1, len2 ;

       this.isZero() ;
      p_vli.isZero() ;

   if((this.sign>0 && p_vli.sign>0) ||
      (this.sign<0 && p_vli.sign<0)   ) 
   {
     for(len1=0 ; len1<this.data.length  && !isNaN(this.data[len1]) ; len1++) ;
     for(len2=0 ; len2<p_vli.data.length && !isNaN(p_vli.data[len2]) ; len2++) ;

       if(len2>len1) 
       {
           res.setVli(p_vli) ;
           res.subVli(this) ;
           res.sign=-res.sign ;

          this.setVli(res) ;
           return ;        
       }

       if(len2==len1) 
        for(i=len1-1 ; i>=0 ; i--)
               if(this.data[i]>p_vli.data[i])  break ;
          else if(this.data[i]<p_vli.data[i])
          {
              res.setVli(p_vli) ;
              res.subVli(this) ;
              res.sign=-res.sign ;

             this.setVli(res) ;
              return ;        
          }

     for(shift=0, i=0 ; i<this.data.length && !isNaN(this.data[i]) ; i++)
     {
         if(isNaN(p_vli.data[i]))  tmp=this.data[i]              -shift ;
         else                      tmp=this.data[i]-p_vli.data[i]-shift ;

         if(tmp>=0)
         {
            this.data[i]=tmp ;
                  shift = 0 ;
         }
         else
         {
            this.data[i]=tmp+65536 ;
                  shift = 1 ;
         }
     }

    for(i-- ; i>0 ; i--)
    {
      if(this.data[i]>0)  break ;
      
         this.data[i]=NaN ;
    }
     
   }
   else 
   {
                    p_vli.sign=-p_vli.sign ;
        this.addVli(p_vli) ;
                    p_vli.sign=-p_vli.sign ;        
   }

  return ; 
}

//============================================== 
//  Метод - умножение VLI на VLI

Vli.prototype.mulVli=function(p_vli)
{
  var  res=new Vli()  ;
  var  dlt=new Vli()  ;
  var  i, n, shift, tmp, num, len ;

//----------------------------------- Определение знака 

   if((this.sign>0 && p_vli.sign>0) ||
      (this.sign<0 && p_vli.sign<0)   )  this.sign= 1 ;
   else                                  this.sign=-1 ;

//----------------------------------- Собственно умножение 

  for(n=0 ; n<p_vli.data.length && !isNaN(p_vli.data[n]) ; n++) 
  {
//- - - - - - - - - - - - - - - - - - Разрядное умножение 
              num=p_vli.data[n] ;

    for(shift=0, i=0 ; i<this.data.length && !isNaN(this.data[i]) ; i++) 
    {
                  tmp =this.data[i]*num+shift ;
           dlt.data[i]= tmp%65536 ;
                shift =(tmp-dlt.data[i])/65536 ;          
    }
   
     if(shift>0)
     { 
         dlt.data[i]=shift ;
                  i++ ;
     }             

         dlt.data[i]=NaN ;
//- - - - - - - - - - - - - - - - - - Суммирование разрядных результатов 
      for(len=0 ; len<dlt.data.length   && !isNaN(dlt.data[len  ]) ; len++) ;
      for(      ; len<res.data.length-n && !isNaN(res.data[len+n]) ; len++) ;

      for(shift=0, i=0 ; i<len ; i++)
      {
         if(isNaN(res.data[i+n]))  res.data[i+n]=0 ;
         if(isNaN(dlt.data[i  ]))  dlt.data[i  ]=0 ;
                 
                     tmp =res.data[i+n]+dlt.data[i]+shift ;
            res.data[i+n]=tmp%65536 ;
               
         if(res.data[i+n]!=tmp)   shift = 1 ;
         else                     shift = 0 ;              
      }

         if(shift==1)  res.data[i+n]=1 ;
//- - - - - - - - - - - - - - - - - -
  }
//-----------------------------------

    this.data=[].concat(res.data) ;

  return ; 
}


//============================================== 
//  Метод - деление VLI на VLI

Vli.prototype.divVli=function(p_vli)
{
  var  res=new Vli() ;
  var  rem=new Vli() ;
  var  out=new Vli() ;
  var  i, j, k, m, n, z, value, shift, tmp, num, len1, len2, rem_flag ;

//----------------------------------- Определение знака 

   if((this.sign>0 && p_vli.sign>0) ||
      (this.sign<0 && p_vli.sign<0)   )  this.sign= 1 ;
   else                                  this.sign=-1 ;

//----------------------------------- Если делитель больше делимого или равен ему

    for(len1=0 ; len1< this.data.length && !isNaN( this.data[len1]) ; len1++) ;
    for(len2=0 ; len2<p_vli.data.length && !isNaN(p_vli.data[len2]) ; len2++) ;

       len1-- ; 
       len2-- ; 

                   rem_flag=0 ;
   if(len1 >len2)  rem_flag=1 ;
   if(len1==len2) 
   {
      for(i=len1 ; i>=0 ; i--)
        if(this.data[i]!=p_vli.data[i]) 
        {
           if(this.data[i]>p_vli.data[i])  rem_flag=1 ;
               break ;  
        }

        if(i<0)
        {
          this.data[0]= 1 ;
          this.data[1]=NaN ;
             return(rem) ;                
        }
        
   }

   if(rem_flag==0)
   {
       rem.data   =[].concat(this.data) ;
      this.data[0]= 0 ;
      this.data[1]=NaN ;
        return(rem) ;
   }
   
//----------------------------------- Собственно деление 

        out.data[0]=0 ;

  for(j=0, z=len1 ; z>=len2 ; )
  {
        out.data[j+1]=0 ;
//- - - - - - - - - - - - -  Определение разряда делимого
                                 value =      this.data[z]
     if(!isNaN(this.data[z+1]))  value+=65536*this.data[z+1] ;

     if(value< p_vli.data[len2])
     {
            j++ ;  z-- ;  continue ;
     }
     if(value==p_vli.data[len2])
     {
          for(i=len2 ; i>=0 ; i--) 
            if(this.data[z-len2+i]!=p_vli.data[i])  break ;

            if(this.data[z-len2+i]<p_vli.data[i]) {  j++ ;  z-- ;  continue ;  }
     }

     if(value==p_vli.data[len2])  value=  1 ;
     else                         value=Math.floor(value/(p_vli.data[len2]+1)) ;

        out.data[j]+=value ;
//- - - - - - - - - - - - - - - - - - Разрядное умножение 
  for(shift=0, i=0 ; i<=len2 ; i++) 
  {
                tmp =p_vli.data[i]*value+shift ;
         res.data[i]=tmp%65536 ;
              shift =(tmp-res.data[i])/65536 ;          
  }
   
     if(shift>0) {  res.data[i]=shift ;
                             i++ ;       }
                           n=i ;
//- - - - - - - - - - - - - - - - - - Разрядное вычитание 
     for(shift=0, k=0 ; k<n || shift>0; k++)
     {
              m=z-len2+k ;
         if(k<n)  tmp=this.data[m]-res.data[k]-shift ;
         else     tmp=this.data[m]            -shift ;

         if(tmp>=0)
         {
            this.data[m]=tmp ;
                  shift = 0 ;
         }
         else
         {
            this.data[m]=tmp+65536 ;
                  shift = 1 ;
         }
     }
//- - - - - - - - - - - - - - - - - -
  }

//-----------------------------------

     rem.data=[].concat(this.data) ;

   for(i=len1 ; i>0 ; i--)
     if(rem.data[i]==0)  rem.data[i]=NaN ;
     else                 break ;

   for(i=0 ; i<j ; i++)
     this.data[i]=out.data[j-i-1] ;

     this.data[j]=NaN ;

  return(rem) ; 
}

//============================================== 
//  Метод - сдвиг VLI вправо на 1 бит

Vli.prototype.rshiftVli=function()
{
  var  i, shift1, shift2, len ;

    for(i=0 ; i<this.data.length && !isNaN(this.data[i]) ; i++) ;

         len=i-1 ;

         shift1=0 ;
         shift2=0 ;
         
    for(i=len ; i>=0 ; i--)
    {
             shift1 = 0x8000 * (this.data[i] & 0x0001) ;
        this.data[i]=(this.data[i]>>1) + shift2 ;
             shift2 = shift1 ;              
    }

   if(len>0 && this.data[len]==0)  this.data[len]=NaN ;

  return ; 
}

//============================================== 
//  Метод - проверка на равенство 0

Vli.prototype.isZero=function()
{
  var  i ;

    if(      this.data[0]==0 && 
       isNaN(this.data[1])      )  return(1) ;
        
    for(i=0 ; i<this.data.length && !isNaN(this.data[i]) ; i++) ;
         
    for(i-- ; i>0 ; i--)
    {
      if(this.data[i]>0)  break ;
      
         this.data[i]=NaN ;
    }


  return ; 
}

//============================================== 
//  Вычисляет  Z=mode(B^D, C)

Vli.prototype.powerMode=function(p_b, p_c, p_d)
{
  var  x=new Vli() ;
  var  y=new Vli() ;
  var  z=new Vli() ;
  var  tmp=new Vli() ;

     x.setVli(p_b) ;
     y.setVli(p_d) ;
     z.assignHex("1") ;

  while(!y.isZero()) 
  {
     if(!(y.data[0] & 1))
     {
           y.rshiftVli() ;         
           x.mulVli(x) ;
         x=x.divVli(p_c) ;
     }

           y.subNumber(1) ;
           z.mulVli(x) ;

         z=z.divVli(p_c) ;
  }

      this.data=[].concat(z.data) ;
  
  return ;
}

//====================================================================
//                                                              
//           Подбирает целое число, близжайшее данному          
//              (модификация идет в сторону уменьшения)         
//                                                              
//    В качества критерия простоты числа используется малая     
//  терема Ферма:                                               
//                mode(X^(P-1), P)=1  .                         
//                                                              
//    В качестве числа X используется произведение простых чисел
//  от 3 до F, такого что X*(F+1)>P                             

Vli.prototype.toSimple=function()
{
  var  simple=[
      3,     5,     7,    11,    13,    17,    19,    23,    29,    31,     37,    41,    43,    47,    53,    59,    61,    67,    71,    73, 
     79,    83,    89,    97,   101,   103,   107,   109,   113,   127,    131,   137,   139,   149,   151,   157,   163,   167,   173,   179, 
    181,   191,   193,   197,   199,   211,   223,   227,   229,   233,    239,   241,   251,   257,   263,   269,   271,   277,   281,   283, 
    293,   307,   311,   313,   317,   331,   337,   347,   349,   353,    359,   367,   373,   379,   383,   389,   397,   401,   409,   419, 
    421,   431,   433,   439,   443,   449,   457,   461,   463,   467,    479,   487,   491,   499,   503,   509,   521,   523,   541,   547, 
    557,   563,   569,   571,   577,   587,   593,   599,   601,   607,    613,   617,   619,   631,   641,   643,   647,   653,   659,   661, 
    673,   677,   683,   691,   701,   709,   719,   727,   733,   739,    743,   751,   757,   761,   769,   773,   787,   797,   809,   811, 
    821,   823,   827,   829,   839,   853,   857,   859,   863,   877,    881,   883,   887,   907,   911,   919,   929,   937,   941,   947, 
    953,   967,   971,   977,   983,   991,   997,  1009,  1013,  1019,   1021,  1031,  1033,  1039,  1049,  1051,  1061,  1063,  1069,  1087, 
   1091,  1093,  1097,  1103,  1109,  1117,  1123,  1129,  1151,  1153,   1163,  1171,  1181,  1187,  1193,  1201,  1213,  1217,  1223,  1229, 
   1231,  1237,  1249,  1259,  1277,  1279,  1283,  1289,  1291,  1297,   1301,  1303,  1307,  1319,  1321,  1327,  1361,  1367,  1373,  1381, 
   1399,  1409,  1423,  1427,  1429,  1433,  1439,  1447,  1451,  1453,   1459,  1471,  1481,  1483,  1487,  1489,  1493,  1499,  1511,  1523, 
   1531,  1543,  1549,  1553,  1559,  1567,  1571,  1579,  1583,  1597,   1601,  1607,  1609,  1613,  1619,  1621,  1627,  1637,  1657,  1663, 
   1667,  1669,  1693,  1697,  1699,  1709,  1721,  1723,  1733,  1741,   1747,  1753,  1759,  1777,  1783,  1787,  1789,  1801,  1811,  1823, 
   1831,  1847,  1861,  1867,  1871,  1873,  1877,  1879,  1889,  1901,   1907,  1913,  1931,  1933,  1949,  1951,  1973,  1979,  1987,  1993, 
   1997,  1999,  2003,  2011,  2017,  2027,  2029,  2039,  2053,  2063,   2069,  2081,  2083,  2087,  2089,  2099,  2111,  2113,  2129,  2131, 
   2137,  2141,  2143,  2153,  2161,  2179,  2203,  2207,  2213,  2221,   2237,  2239,  2243,  2251,  2267,  2269,  2273,  2281,  2287,  2293, 
   2297,  2309,  2311,  2333,  2339,  2341,  2347,  2351,  2357,  2371,   2377,  2381,  2383,  2389,  2393,  2399,  2411,  2417,  2423,  2437, 
   2441,  2447,  2459,  2467,  2473,  2477,  2503,  2521,  2531,  2539,   2543,  2549,  2551,  2557,  2579,  2591,  2593,  2609,  2617,  2621, 
   2633,  2647,  2657,  2659,  2663,  2671,  2677,  2683,  2687,  2689,   2693,  2699,  2707,  2711,  2713,  2719,  2729,  2731,  2741,  2749, 
   2753,  2767,  2777,  2789,  2791,  2797,  2801,  2803,  2819,  2833,   2837,  2843,  2851,  2857,  2861,  2879,  2887,  2897,  2903,  2909, 
   2917,  2927,  2939,  2953,  2957,  2963,  2969,  2971,  2999,  3001,   3011,  3019,  3023,  3037,  3041,  3049,  3061,  3067,  3079,  3083, 
   3089,  3109,  3119,  3121,  3137,  3163,  3167,  3169,  3181,  3187,   3191,  3203,  3209,  3217,  3221,  3229,  3251,  3253,  3257,  3259, 
   3271,  3299,  3301,  3307,  3313,  3319,  3323,  3329,  3331,  3343,   3347,  3359,  3361,  3371,  3373,  3389,  3391,  3407,  3413,  3433, 
   3449,  3457,  3461,  3463,  3467,  3469,  3491,  3499,  3511,  3517,   3527,  3529,  3533,  3539,  3541,  3547,  3557,  3559,  3571,  3581, 
   3583,  3593,  3607,  3613,  3617,  3623,  3631,  3637,  3643,  3659,   3671,  3673,  3677,  3691,  3697,  3701,  3709,  3719,  3727,  3733, 
   3739,  3761,  3767,  3769,  3779,  3793,  3797,  3803,  3821,  3823,   3833,  3847,  3851,  3853,  3863,  3877,  3881,  3889,  3907,  3911, 
   3917,  3919,  3923,  3929,  3931,  3943,  3947,  3967,  3989,  4001,   4003,  4007,  4013,  4019,  4021,  4027,  4049,  4051,  4057,  4073, 
   4079,  4091,  4093,  4099,  4111,  4127,  4129,  4133,  4139,  4153,   4157,  4159,  4177,  4201,  4211,  4217,  4219,  4229,  4231,  4241, 
   4243,  4253,  4259,  4261,  4271,  4273,  4283,  4289,  4297,  4327,   4337,  4339,  4349,  4357,  4363,  4373,  4391,  4397,  4409,  4421, 
   4423,  4441,  4447,  4451,  4457,  4463,  4481,  4483,  4493,  4507,   4513,  4517,  4519,  4523,  4547,  4549,  4561,  4567,  4583,  4591, 
   4597,  4603,  4621,  4637,  4639,  4643,  4649,  4651,  4657,  4663,   4673,  4679,  4691,  4703,  4721,  4723,  4729,  4733,  4751,  4759, 
   4783,  4787,  4789,  4793,  4799,  4801,  4813,  4817,  4831,  4861,   4871,  4877,  4889,  4903,  4909,  4919,  4931,  4933,  4937,  4943, 
   4951,  4957,  4967,  4969,  4973,  4987,  4993,  4999,  5003,  5009,   5011,  5021,  5023,  5039,  5051,  5059,  5077,  5081,  5087,  5099, 
   5101,  5107,  5113,  5119,  5147,  5153,  5167,  5171,  5179,  5189,   5197,  5209,  5227,  5231,  5233,  5237,  5261,  5273,  5279,  5281, 
   5297,  5303,  5309,  5323,  5333,  5347,  5351,  5381,  5387,  5393,   5399,  5407,  5413,  5417,  5419,  5431,  5437,  5441,  5443,  5449, 
   5471,  5477,  5479,  5483,  5501,  5503,  5507,  5519,  5521,  5527,   5531,  5557,  5563,  5569,  5573,  5581,  5591,  5623,  5639,  5641, 
   5647,  5651,  5653,  5657,  5659,  5669,  5683,  5689,  5693,  5701,   5711,  5717,  5737,  5741,  5743,  5749,  5779,  5783,  5791,  5801, 
   5807,  5813,  5821,  5827,  5839,  5843,  5849,  5851,  5857,  5861,   5867,  5869,  5879,  5881,  5897,  5903,  5923,  5927,  5939,  5953, 
   5981,  5987,  6007,  6011,  6029,  6037,  6043,  6047,  6053,  6067,   6073,  6079,  6089,  6091,  6101,  6113,  6121,  6131,  6133,  6143, 
   6151,  6163,  6173,  6197,  6199,  6203,  6211,  6217,  6221,  6229,   6247,  6257,  6263,  6269,  6271,  6277,  6287,  6299,  6301,  6311, 
   6317,  6323,  6329,  6337,  6343,  6353,  6359,  6361,  6367,  6373,   6379,  6389,  6397,  6421,  6427,  6449,  6451,  6469,  6473,  6481, 
   6491,  6521,  6529,  6547,  6551,  6553,  6563,  6569,  6571,  6577,   6581,  6599,  6607,  6619,  6637,  6653,  6659,  6661,  6673,  6679, 
   6689,  6691,  6701,  6703,  6709,  6719,  6733,  6737,  6761,  6763,   6779,  6781,  6791,  6793,  6803,  6823,  6827,  6829,  6833,  6841, 
   6857,  6863,  6869,  6871,  6883,  6899,  6907,  6911,  6917,  6947,   6949,  6959,  6961,  6967,  6971,  6977,  6983,  6991,  6997,  7001, 
   7013,  7019,  7027,  7039,  7043,  7057,  7069,  7079,  7103,  7109,   7121,  7127,  7129,  7151,  7159,  7177,  7187,  7193,  7207,  7211, 
   7213,  7219,  7229,  7237,  7243,  7247,  7253,  7283,  7297,  7307,   7309,  7321,  7331,  7333,  7349,  7351,  7369,  7393,  7411,  7417, 
   7433,  7451,  7457,  7459,  7477,  7481,  7487,  7489,  7499,  7507,   7517,  7523,  7529,  7537,  7541,  7547,  7549,  7559,  7561,  7573,
   7577,  7583,  7589,  7591,  7603,  7607,  7621,  7639,  7643,  7649,   7669,  7673,  7681,  7687,  7691,  7699,  7703,  7717,  7723,  7727, 
   7741,  7753,  7757,  7759,  7789,  7793,  7817,  7823,  7829,  7841,   7853,  7867,  7873,  7877,  7879,  7883,  7901,  7907,  7919,  7927,
   0 ] ;

    var  fast=new Array(10) ;

    var  x=new Vli() ;
    var  y=new Vli() ;
    var  p_1=new Vli() ;
    var  i, j, k, len, rem, fast_flag ;

/*-------------------------------------------- Подбор числа-критерия */

                 this.isZero() ;

    for(len=0 ; len<this.data.length && !isNaN(this.data[len]) ; len++) ;

         x.data[0]=simple[0] ;

   for(k=1 ; k<simple.length ; k++) 
   {
         x.mulNumber(simple[k]) ;

      if(!isNaN(x.data[len  ])                                  )  break ;
      if(!isNaN(x.data[len-1]) && x.data[len-1]>this.data[len-1])  break ;
   }

         x.divNumber(simple[k]) ;
         x.isZero() ;

/*---------------------------------------- Подготовка базового числа */

       if((this.data[0]&1)==0)  this.subNumber(1) ;                 /* Устраняем четность */

/*--------------------------------------------- Поиск простого числа */

    do {
            this.subNumber(2) ;                                     /* Модиф.искомое число */
/*- - - - - - - - - - - - - - - - - - - - - - -  Ускоренная проверка */
              
         for(fast_flag=0, j=0 ; j<10 ; j++) 
         {
               fast[j]-- ;
            if(fast[j]==0) {  fast[j]=simple[j] ;  fast_flag=1 ; }
         }

             if(fast_flag)  continue ;
/*- - - - - - - - - - - - - - - - - - - - - - - -  Проверка делением */
     if(this.log!=null)  this.log(this.getHex_()) ;

       for(i=0 ; i<1000 ; i++)  {

                y.data=[].concat(this.data) ;

	    rem=y.divNumber(simple[i]) ;

	 if(rem==0)  break ;
				}

     if(this.log!=null)  this.log("Factor "+i) ;

         if(i<10)  fast[i]=simple[i] ;

         if(i<1000)  continue ; 
/*- - - - - - - - - - - - - - - - -  Проверка по малой теореме Ферма */
                p_1.data=[].concat(this.data) ;
                p_1.subNumber(1) ;                                  /* Вычисляем P-1 */

     if(this.log!=null)  this.log("   x= "+   x.getHex_()) ;
     if(this.log!=null)  this.log("this= "+this.getHex_()) ;
     if(this.log!=null)  this.log(" p-1= "+ p_1.getHex_()) ;

		y.powerMode(x, this, p_1) ;                         /* Вычисляем критерий */

     if(this.log!=null)  this.log(" Rem= "+ y.getHex_()) ;

             if(y.data[0]==1 && isNaN(y.data[1]))  break ;          /* Проверка критерия на равенство 1 */
/*- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -*/
       } while(1) ;

/*-------------------------------------------------------------------*/

}

/*********************************************************************/
/*                                                                   */
/*              Определение модально-обратного числа                 */
/*                                                                   */
/*    Определяет число E, такое, что  mod(Y*E, X)=1                  */
/*                                                                   */

Vli.prototype.inverseModal=function(p_x, p_y)

{
  var  A ;
  var  z=new Vli ;
  var  t=new Vli ;
  var  tmp=new Vli ;
  var  i, len  ;

/*---------------------------------------------------- Инициализация */

      A      =new Array(4) ;
      A[1]   =new Array(3) ;
      A[2]   =new Array(3) ;
      A[3]   =new Array(3) ;

      A[1][1]=new Vli ;
      A[1][2]=new Vli ;
      A[2][1]=new Vli ;
      A[2][2]=new Vli ;
      A[3][1]=new Vli ;
      A[3][2]=new Vli ;

      A[1][1].setVli   (p_x) ;
      A[1][2].setVli   (p_y) ;
      A[2][1].assignHex("1") ;
      A[2][2].assignHex("0") ;
      A[3][1].assignHex("0") ;
      A[3][2].assignHex("1") ;

/*---------------------------------------------- Итеррационный поиск */

     do {

	   if(A[1][2].isZero())  break ;                            /* Контроль выходного условия - A(1,2)!=0 */
                 z.setVli(A[1][1]) ;
                 z.divVli(A[1][2]) ;                                /* Z=A(1,1)/A(1,2) */

	 for(i=1 ; i<=3 ; i++) {

                 tmp.setVli(A[i][2]) ;                              /*   Tmp =A(i,2)          */

                   t.setVli(z) ;                                    /* A(i,2)=A(i,1)-Z*A(i,2) */
                   t.mulVli(A[i][2]) ;
             A[i][2].setVli(A[i][1]) ;
             A[i][2].subVli(t) ;

             A[i][1].setVli(tmp) ;                                  /* A(i,1)= Tmp            */
			       }

	} while(1) ;

/*--------------------------------------------------------- Концовка */

              this.data=[].concat(A[3][1].data) ;

/*-------------------------------------------------------------------*/

}

/*********************************************************************/
/*                                                                   */
/*              Генерация RSA ключей с базой P_BASE байт             */

Rsa.prototype.generateKeys=function(p_base)

{
    var  p =new Vli()  ;
    var  q =new Vli()  ;
    var  n =new Vli()  ;
    var  d =new Vli()  ;
    var  e =new Vli()  ;
    var  p1=new Vli()  ;
    var  q1=new Vli()  ;
    var  eu=new Vli()  ;
    var  e =new Vli()  ;
    var  t =new Vli()  ;
    var  r =new Vli()  ;

/*--------------------------------------------- Общий цикл генерации */

 do {

/*--------------------------------------------- Поиск ключей P, Q, N */

     if(this.log!=null)  this.log("Generate P...") ;

             p.log=this.log ;
             p.random(p_base) ;
             p.toSimple() ;        /* Генерируем случайное простое P */

     if(this.log!=null)  this.log("P generated") ;
     if(this.log!=null)  this.log("Generate Q...") ;

             q.log=this.log ;
             q.random(p_base) ;
             q.toSimple() ;        /* Генерируем случайное простое Q */

     if(this.log!=null)  this.log("Q generated") ;

             n.setVli(p) ;         /* N=P*Q */
             n.mulVli(q) ;

/*----------------------------------------------- Поиск ключей D и E */

             p1.setVli(p) ;        /* Eu=(P-1)*(Q-1) */
             p1.subNumber(1) ;
             q1.setVli(q) ;
             q1.subNumber(1) ;
             eu.setVli(p1) ;
             eu.mulVli(q1) ;
   do {
/*- - - - - - - - - - - - - - - - - - - - - - - - - -  Поиск ключа D */
             t.random(p_base) ;

     do {

     if(this.log!=null)  this.log("New D generate...") ;

             d.log=this.log ;
             d.setVli(p) ;         /* Ищем D, большее P и Q и взаимно-простое с Eu */
             d.addVli(q) ;
             d.addVli(t) ;
             d.toSimple() ;

             t.setVli(eu) ;
           r=t.divVli(d) ;

        if(!r.isZero())  break ;

         } while(1) ;
/*- - - - - - - - - - - - - - - - - - - - - - - - - -  Поиск ключа Е */
     if(this.log!=null)  this.log("D generated") ;
     if(this.log!=null)  this.log("E calculation") ;

             e.inverseModal(eu, d) ;                                /* Обращаем D по модулю Eu */

             t.setVli(d) ;
             t.mulVli(e) ;
           r=t.divVli(eu) ;

           r.isZero() ;

     if(this.log!=null)  this.log("Check E for Eu relation") ;

        if(r.data[0]==1 && isNaN(r.data[1]))  break ;

      } while(1) ;

/*-------------------------------------------------- Проверка ключей */

   var  data_i=new Vli() ;
   var  data_o=new Vli() ;

     if(this.log!=null)  this.log("Check keys by encode/decode") ;

         data_i.assignHex("abcdef") ;
         data_o.powerMode(data_i, n, e) ;
         data_i.powerMode(data_o, n, d) ;

       if(data_i.getHex()=="00abcdef") break ;

    } while(1) ;

     if(this.log!=null)  this.log("Keys generation complete") ;

/*------------------------------------------------ Сохранение ключей */

    this.base=p_base ;
    this.keyN.setVli(n) ;
    this.keyE.setVli(e) ;
    this.keyD.setVli(d) ;

/*-------------------------------------------------------------------*/

}

/*********************************************************************/
/*                                                                   */
/*                        Извлечение ключей                          */

Rsa.prototype.getSecretKey=function()

{
  return(this.base+":"+this.keyE.getHex()+":"+this.keyN.getHex()) ;
}

Rsa.prototype.getPublicKey=function()

{
  return(this.base+":"+this.keyD.getHex()+":"+this.keyN.getHex()) ;
}

/*********************************************************************/
/*                                                                   */
/*                        Установка ключей                           */

Rsa.prototype.setSecretKey=function(p_text)

{
    var  check ;
    var  words=p_text.split(":") ;

   if(words.length!=3)  return(-1) ;

      this.base= parseInt(words[0]) ;
      this.keyE.assignHex(words[1]) ;
      this.keyN.assignHex(words[2]) ;

     check=this.base+":"+this.keyE.getHex()+":"+this.keyN.getHex() ;
   if(check!=p_text)  return(-1) ;

  return(0) ;
}

Rsa.prototype.setPublicKey=function(p_text)

{
    var  check ;
    var  words=p_text.split(":") ;

   if(words.length!=3)  return(-1) ;

      this.base= parseInt(words[0]) ;
      this.keyD.assignHex(words[1]) ;
      this.keyN.assignHex(words[2]) ;

     check=this.base+":"+this.keyD.getHex()+":"+this.keyN.getHex() ;
   if(check!=p_text)  return(-1) ;

  return(0) ;
}

/*********************************************************************/
/*                                                                   */
/*              RSA-кодирование на ключах E и N                      */

Rsa.prototype.encodeText=function(p_text)

{
  var  hex, tmp, i ;
  
   for(hex="", i=0 ; i<p_text.length ; i++) 
   {
       tmp ="0000"+p_text.charCodeAt(i).toString(16) ;
       hex+=tmp.substr(-4) ;
   }

   return(this.encode(hex)) ;
}

Rsa.prototype.encode=function(p_text)

{
   var  data_i=new Vli() ;
   var  data_o=new Vli() ;
   var  chunk ;
   var  chunk_size ;
   var  block ;
   var  block_size ;
   var  buff_i ;
   var  buff_o ;
   var  i ;


         buff_i=p_text ;
         buff_o=   "" ;

        chunk_size=2*(this.base*2-1) ;
        block_size=2*(this.base*2  ) ;

     while(buff_i.length>0)
     {
         chunk=buff_i.substr(0, chunk_size) ;
        buff_i=buff_i.substr(chunk_size) ;

         data_i.assignHex(chunk) ;
         data_o.powerMode(data_i, this.keyN, this.keyE) ;

            block=data_o.getHex() ;

       for(i=block.length ; i<block_size ; i++)  block="0"+block ;

            buff_o=buff_o+block ;
     }

            buff_o=p_text.length+":"+buff_o ;

   return(buff_o) ;
}

/*********************************************************************/
/*                                                                   */
/*              RSA-декодирование на ключах D и N                    */

Rsa.prototype.decodeText=function(p_code)

{
  var  hex, tmp, i ;

       hex=this.decode(p_code)

   for(tmp="", i=0 ; i<hex.length ; i+=4) 
       tmp+=String.fromCharCode(parseInt(hex.substr(i, 4), 16)) ;

   return(tmp) ;
}

Rsa.prototype.decode=function(p_code)

{
   var  data_i=new Vli() ;
   var  data_o=new Vli() ;
   var  words ;
   var  size_o ;
   var  chunk ;
   var  chunk_size ;
   var  block ;
   var  block_size ;
   var  buff_i ;
   var  buff_o ;
   var  shift ;
   var  i ;


          words=p_code.split(":") ;
         size_o=parseInt(words[0])
         buff_i=words[1] ;
         buff_o=   "" ;

        block_size=2*(this.base*2  ) ;
        chunk_size=2*(this.base*2-1) ;

     while(buff_i.length>0)
     {
         block=buff_i.substr(0, block_size) ;
        buff_i=buff_i.substr(block_size) ;

         data_i.assignHex(block) ;
         data_o.powerMode(data_i, this.keyN, this.keyD) ;

            chunk=data_o.getHex() ;

       for(i=chunk.length ; i<chunk_size ; i++)  chunk="0"+chunk ;

          if(size_o<chunk_size)  chunk_size=size_o ;

             shift=chunk.length-chunk_size ;
          if(shift>0)  chunk=chunk.substr(shift) ;

            size_o-=chunk_size ;
            buff_o+=chunk ;
     }


   return(buff_o) ;
}

