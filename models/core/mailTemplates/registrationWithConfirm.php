<?php

$template = [
    'subject' => [
        'ru' => 'Регистрация на сайте {*siteName*}',
    ],
    'template' => [
        'ru' => '<p>Здравствуйте!</p><p>На сайте {*siteName*} по адресу {*siteUrl*} появилась регистрационная запись, в которой был указал ваш электронный адрес (e-mail).</p>
<p>При заполнении регистрационной формы было указано следующее имя пользователя:</p>
<p>=================================== <br/>
Имя пользователя (login): {*login*} <br/>
===================================</p>
<p>Подтверждение регистрации производится один раз и необходимо для повышения безопасности сайта и защиты его от злоумышленников.</p>
<p>Чтобы активировать вашу учетную запись, необходимо перейти по ссылке: {*activationUrl*}</p>
<p>После активации учетной записи вы сможете войти в форум, используя выбранные вами имя пользователя (login) и пароль. С этого момента вы сможете оставлять сообщения.</p>
<p>Благодарим за регистрацию!</p>
<p>--</p>
<p>С уважением, <br/>
Администрация {*siteName*}. <br/>
{*siteUrl*}</p>'
    ],
];