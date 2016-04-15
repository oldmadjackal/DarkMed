CREATE  OR REPLACE
  VIEW V_REGISTRY_OLDTIME (login_old)
    AS select u.login 
         from users u
        WHERE u.Date_Reg < DATE_ADD(NOW(), INTERVAL -3 DAY)
          AND u.Email_confirm = 'N';
