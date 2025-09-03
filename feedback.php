<?php
/**
 * Created by PhpStorm.
 * User: Stanislav Sviridenko
 * Date: 25.04.2023
 * Time: 00:13
 */

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
