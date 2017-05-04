Add this to routing.yml

````
fos_user_change_password:
    defaults: { _controller: 'OpstalentUserBundle:ChangePassword:changePassword' }
    path: '/change-password'
    methods: [POST]

fos_user_resetting_send_email:
    defaults: { _controller: 'OpstalentUserBundle:Resetting:sendEmail' }
    path: '/resetting/send-email'
    methods: [POST]

fos_user_resetting_reset:
    defaults: { _controller: 'OpstalentUserBundle:Resetting:reset' }
    path: '/resetting/reset/{token}'
    methods: [POST]
````