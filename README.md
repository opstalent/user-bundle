Add this to routing.yml

````
fos_user_change_password:
    defaults: { _controller: 'OpstalentUserBundle:ChangePassword:changePassword' }

fos_user_resetting_send_email:
    defaults: { _controller: 'OpstalentUserBundle:Resetting:sendEmail' }

fos_user_resetting_reset:
    defaults: { _controller: 'OpstalentUserBundle:Resetting:reset' }
````