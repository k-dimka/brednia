<?php

class Mailer
{
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $this->fromEmail = 'verter@brednia.ru';
        $this->fromName = 'Бредня.ру';
    }

    /**
     * Отправляет письмо.
     *
     * @param string $to      Email получателя
     * @param string $subject Тема письма
     * @param string $body    HTML-тело письма
     * @return bool           true при успехе, false при ошибке
     */
    public function send(string $to, string $subject, string $body): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            "From: {$this->fromName} <{$this->fromEmail}>",
            "Reply-To: {$this->fromEmail}",
            'X-Mailer: PHP/' . phpversion(),
        ];

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }

    /**
     * Отправляет тестовое письмо для проверки работы почты.
     */
    public function sendTest(string $to): bool
    {
        $subject = '🧪 Тестовое письмо — Бредня.ру';
        $body = $this->buildTemplate('Тестовое письмо', '
            <p>Если вы читаете это — почтовый сервер настроен правильно.</p>
            <p>Сервер: mail.hoster.ru</p>
            <p>Отправитель: verter@brednia.ru</p>
            <p style="color: #8B7340;">Бредня.ру — аналитика за копейки</p>
        ');

        return $this->send($to, $subject, $body);
    }

    /**
     * Письмо после регистрации.
     */
    public function sendWelcome(string $to): bool
    {
        $subject = 'Добро пожаловать в Бредня.ру';
        $body = $this->buildTemplate('Добро пожаловать!', '
            <p>Вы зарегистрировались на сайте <a href="https://brednia.ru" style="color:#00FF41;">Бредня.ру</a>.</p>
            <p>Ваш email: ' . htmlspecialchars($to) . '</p>
            <p>Получите свой первый шуточный аналитический дашборд.</p>
            <p><a href="https://brednia.ru" style="display:inline-block; padding:12px 24px; background:#00FF41; color:#0B0E14; text-decoration:none; font-weight:bold; border-radius:4px;">Пополнить счёт</a></p>
        ');

        return $this->send($to, $subject, $body);
    }

    /**
     * Оборачивает контент в HTML-шаблон письма.
     */
    private function buildTemplate(string $title, string $content): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head><meta charset="utf-8"></head>
        <body style="margin:0; padding:0; background:#0B0E14; font-family:Arial,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; margin:40px auto; background:#0F131C; border:1px solid #2A2A2A; border-radius:4px;">
                <tr>
                    <td style="padding:30px; text-align:center;">
                        <h1 style="color:#00FF41; font-size:24px; margin:0 0 8px;">БРЕДНЯ.РУ</h1>
                        <p style="color:#FFB000; font-size:16px; margin:0;">' . htmlspecialchars($title) . '</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 30px 20px; color:#FFB000; font-size:14px; line-height:1.6;">
                        ' . $content . '
                    </td>
                </tr>
                <tr>
                    <td style="padding:20px 30px; border-top:1px solid #2A2A2A; text-align:center;">
                        <p style="color:#8B7340; font-size:11px; margin:0;">Бредня.ру — шуточная аналитика за копейки</p>
                        <p style="color:#8B7340; font-size:11px; margin:4px 0 0;">verter@brednia.ru</p>
                    </td>
                </tr>
            </table>
        </body>
        </html>';
    }
}
