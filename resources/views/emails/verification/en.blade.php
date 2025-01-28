<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
            font-family: Arial, sans-serif;
        }
        img {
            border: 0;
            display: block;
            outline: none;
            text-decoration: none;
        }
        .content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .block {
            border-radius: 20px;
            overflow: hidden;
        }
        .button {
            border-radius: 8px;
        }
        ul {
            padding-left: 20px;
            list-style-type: disc;
        }
    </style>
</head>
<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#f8f8f8" style="font-family: Arial, sans-serif; color: #333333;">
    <tr>
        <td align="center" style="padding: 20px;">
            <table class="content" width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff;">
                <!-- Логотип -->
                <tr class="block">
                    <td align="center" style="padding: 20px; background-color: #ffffff">
                        <img src="https://wallone.app/static/img/logo-small.png" alt="Wallone" width="120" />
                    </td>
                </tr>
                <!-- Основное изображение -->
                <tr class="block">
                    <td align="center" style="padding: 20px; background-color: #f0f4ff;">
                        <img src="https://www.pngall.com/wp-content/uploads/15/Example-PNG.png" alt="Wallone Welcome" width="100%" style="max-width: 600px; height: auto; border-radius: 20px;" />
                    </td>
                </tr>
                <!-- Заголовок -->
                <tr class="block">
                    <td align="center" style="padding: 20px; font-size: 24px; color: #333333; font-weight: bold; background-color: #ffffff;">
                        Добро пожаловать, {{ $username }}!
                    </td>
                </tr>
                <!-- Содержимое -->
                <tr class="block">
                    <td align="left" style="padding: 20px; font-size: 16px; line-height: 1.6; color: #555555; background-color: #ffffff;">
                        <p>Теперь вы можете:</p>
                        <ul>
                            <li>Публиковать свои творческие работы!</li>
                            <li>Добавлять любимые работы в коллекции и избранное.</li>
                            <li>Ставить лайки работам других авторов.</li>
                            <li>Общаться с авторами и пользователями сайта.</li>
                            <li>И многое другое!</li>
                        </ul>
                        <p>Ваш код для подтверждения email: <strong>{{ $code }}</strong></p>
                    </td>
                </tr>
                <!-- Футер -->
                <tr class="block">
                    <td align="center" style="padding: 20px; font-size: 12px; color: #777777; background-color: #f8f8f8;">
                        Если у вас есть вопросы, напишите нам: <a href="mailto:helper@wallone.app" style="color: #007AFF; text-decoration: none;">helper@wallone.app</a><br />
                        Вы можете изменить настройки уведомлений в <a href="https://wallone.app/profile#settings" style="color: #007AFF; text-decoration: none;">настройках профиля</a>.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
