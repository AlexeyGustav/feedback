<?php
/**
 * Created by PhpStorm.
 * User: Stanislav Sviridenko
 * Date: 25.04.2023
 * Time: 00:13
 */
// ========================================
// СТАРЫЙ КОД (ЗАКОММЕНТИРОВАН)
// ========================================
/*
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array('code' => '0');

    $full_name = (isset($_POST['name']) ? $_POST['name'] : null);
    $email     = (isset($_POST['email']) ? $_POST['email'] : null);
    $question  = (isset($_POST['question']) ? $_POST['question'] : null);

    if (is_null($full_name) || is_null($email) || is_null($question)) {
        echo '';
    } else {
        $to      = 'gustavaleksej9@gmail.com';
        // $to      = 'stanislav@sviridenko.com';
        $subject = 'Message from pifet.ru';
        $headers = 'From: info@pifet.ru' . "\r\n" .
            'Reply-To: info@pifet.ru' . "\r\n" .
            'X-Mailer: PHP/' . phpversion() .
            "MIME-Version: 1.0\r\n" .
            "Content-type: text/html; charset=utf-8\r\n";

        $message = 'Сообщение с сайта<br/>' . PHP_EOL .
            "Имя: " . $full_name . '<br/>' . PHP_EOL .
            "E-mail: " . $email . PHP_EOL . '<br/>'.
            "Вопрос: " .  '<br/>'.PHP_EOL
            . '---------------------------------- <br/>'. PHP_EOL
            .  $question  . '<br/>'. PHP_EOL
            . '---------------------------------- <br/>'. PHP_EOL;

        try {
            mail($to, $subject, $message, $headers);
            $response['code'] = 1;
            } catch (Exception $e) {
            $response['code'] = -1;
            $response['error'] = $e->getMessage();
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
*/

// ========================================
// НОВЫЙ КОД С COMPOSER AUTOLOAD
// ========================================

// ВАЖНО: Никакого вывода до заголовков!
ob_start(); // Буферизуем вывод

// Отключаем вывод ошибок в браузер
error_reporting(0);
ini_set('display_errors', 0);

// Подключаем Composer autoloader
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Подключаем конфигурацию
require_once 'mail_config.php';

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean(); // Очищаем буфер
    header('Content-Type: application/json');
    echo json_encode(['code' => -1, 'error' => 'Только POST запросы']);
    exit;
}

// Получаем данные из формы
$full_name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$question = $_POST['question'] ?? '';

// Проверяем, что все поля заполнены
if (empty($full_name) || empty($email) || empty($question)) {
    ob_end_clean(); // Очищаем буфер
    header('Content-Type: application/json');
    echo json_encode(['code' => -1, 'error' => 'Не все поля заполнены']);
    exit;
}

// Очищаем буфер перед отправкой заголовков
ob_end_clean();

try {
    // Создаем экземпляр PHPMailer
    $mail = new PHPMailer(true);
    
    // Настройки сервера
    $mail->isSMTP();
    $mail->Host = GMAIL_SMTP;
    $mail->SMTPAuth = true;
    $mail->Username = GMAIL_USER;
    $mail->Password = GMAIL_PASS;
    $mail->SMTPSecure = GMAIL_SECURE;
    $mail->Port = GMAIL_PORT;
    $mail->CharSet = 'UTF-8';
    
    // Настройки письма
    $mail->setFrom(GMAIL_USER, 'Feedback Form');
    $mail->addAddress('gustavaleksej9@gmail.com', 'Gustav');
    $mail->addReplyTo($email, $full_name);
    
    $mail->isHTML(true);
    $mail->Subject = 'Сообщение с сайта pifet.ru';
    
    // HTML содержимое письма
    $mail->Body = '
    <html>
    <head>
        <title>Сообщение с сайта</title>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #f8f9fa; padding: 15px; border-radius: 5px; }
            .content { margin: 20px 0; }
            .field { margin: 10px 0; }
            .label { font-weight: bold; color: #333; }
            .value { margin-left: 10px; }
            .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>📧 Новое сообщение с сайта pifet.ru</h2>
            </div>
            <div class="content">
                <div class="field">
                    <span class="label">👤 Имя:</span>
                    <span class="value">' . htmlspecialchars($full_name) . '</span>
                </div>
                <div class="field">
                    <span class="label">📧 Email:</span>
                    <span class="value">' . htmlspecialchars($email) . '</span>
                </div>
                <div class="field">
                    <span class="label"> Сообщение:</span>
                    <div class="value" style="margin-top: 10px; padding: 15px; background: #f8f9fa; border-radius: 5px; border-left: 4px solid #007bff;">
                        ' . nl2br(htmlspecialchars($question)) . '
                    </div>
                </div>
            </div>
            <div class="footer">
                <p><small>📅 Отправлено: ' . date('d.m.Y H:i:s') . '</small></p>
                <p><small>🌐 Источник: ' . $_SERVER['HTTP_HOST'] . '</small></p>
            </div>
        </div>
    </body>
    </html>';
    
    // Текстовая версия письма
    $mail->AltBody = "
    Новое сообщение с сайта pifet.ru
    
    Имя: $full_name
    Email: $email
    Сообщение: $question
    
    Отправлено: " . date('d.m.Y H:i:s');
    
    // Отправляем письмо
    $mail->send();
    
    // Успешный ответ
    $response = [
        'code' => 1, 
        'message' => 'Письмо отправлено успешно!',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
} catch (Exception $e) {
    // Ошибка отправки
    $response = [
        'code' => -1, 
        'error' => 'Ошибка отправки: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

// Возвращаем JSON ответ
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit;
?>
