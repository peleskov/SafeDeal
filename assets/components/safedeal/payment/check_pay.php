<?php
// Откликаться будет ТОЛЬКО на ajax запросы
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    return;
}

/* Подключаем MODX */
define('MODX_API_MODE', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/index.php');
$modx = new modX();
$modx->initialize('mgr');

$errors = [];
$res = ['result' => false, 'errors' => []];
$SafeDeal = $modx->getService('SafeDeal', 'SafeDeal', MODX_CORE_PATH . 'components/safedeal/model/');
if (!$SafeDeal) {
    $res['errors']['SafeDeal'] = 'Could not load SafeDeal class!';
}

if (empty($res['errors'])) {
    if ($user = $modx->getUser()) {
        $user_id = $user->id;
        $prfl = $user->getOne('Profile');
    } else {
        $res['errors']['user_id'] = 'User ID not found!';
    }
    if (!$deal = $modx->getObject('Deal', array('hash' => $_POST['hash']))) {
        $res['errors']['deal'] = 'Deal not found!';
    }
    $apiKey = $modx->getOption('safedeal_merchant_apikey');
    $payment_id = $deal->get('payment_id');
    if ($payment_id > 0) {
        /* Проверим есть ли инвойс на оплату этой сделки и если он уже оплачен сменим статус сделки*/
        $url = trim($modx->getOption('safedeal_merchant_host'), '/') . '/' . trim($modx->getOption('safedeal_merchant_invoice_url'), '/') . '?order_id=' . $deal->get('id') . '&id=' . $payment_id;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Token: ' . $apiKey
        ]);
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] == 200) {
            $result = json_decode($response, true);
            if ($result['status'] == 'STATUS_PAID' && $result['amount'] == (($deal->get('price') + $deal->get('fee')) * 100)) {
                $deal->set('status', 3);
                $deal->set('paid_amount', $result['amount']);
                $deal->set('updated', time());
                if (!$deal->save()) {
                    $res['errors']['deal'] = 'Can not save a deal!';
                }
            } else {
                $res['errors']['deal'] = 'Deal not paid! Status : ' . $result['status'] ;
            }
        }
    }
}
if(empty($res['errors'])) {
    $res = ['result' => true];
}
die(json_encode($res));
