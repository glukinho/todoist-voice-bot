# todoist-voice-bot
Telegram bot for creating Todoist tasks using voice messages

Телеграм бот для создания задач в Todoist из голосовых сообщений.

Отправляем боту голосовое сообщение, он получает звуковой файл, конвертирует его в Opus, загоняет в Yandex Cloud SpeechKit, получает распознанный текст, создает в Todoist задачу во входящих с этим текстом.

Писалось на Centos 7 + apache 2.4.6 + php 5.4.16 + opus-tools

## Как использовать
0. Запускаем telegram бота как обычно (web-сервер, сертификат, webhook). Обращения webhook направляем в файл todoist-voice-bot.php
1. В файле users.json перечисляем пользователей telegram, которым бот будет отвечать: telegram id (обязательно), todoist API token (обязательно), username (для удобства, в логах будет видно)
2. В файл yandex-iam-token.json кладем IAM-токен от Яндекса. Подробности здесь: https://cloud.yandex.ru/docs/speechkit/concepts/auth
3. Шлем боту голосовое сообщение, он его распознает (через Яндекс) и создает в Todoist во входящих задачу с распознанным текстом.

## Ссылки
Яндекс SpeechKit: https://cloud.yandex.ru/docs/speechkit/
Todoist REST API: https://developer.todoist.com/rest/v8/