<?php
// Откликаться будет ТОЛЬКО на ajax запросы
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    return;
}

$fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
$path = $modx->getOption('pdofetch_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/', true);
if ($pdoClass = $modx->loadClass($fqn, $path, false, true)) {
    $pdoFetch = new $pdoClass($modx, $scriptProperties);
} else {
    return false;
}
$pdoFetch->addTime('pdoTools loaded');

$modx->lexicon->load('formit:default');


$messages = [];
$errors = [];
$data = ['service' => 'safedeal'];

/* Deal status code
    0 => 'Создана / Ожидает предложения партнеру',
    1 => 'Предложена / Ожидает подтверждения партнера',
    2 => 'Подтверждена партнером / Ожидает оплаты',
    3 => 'Оплачена / В работе',
    4 => 'Продление / Ожидает согласования',
    5 => 'Завершена / Ожидает согласия заказчика',
    6 => 'Закрыта / Ожидает выплаты исполнителю / Архив',
*/

switch ($scriptProperties['action']) {
    case 'deal/create':
        /* Validate fields */
        foreach ($_POST as $key => $val) {
            switch ($key) {
                case 'is_customer':
                    if (!in_array($val, [1, 0])) {
                        $errors[$key] = '';
                    }
                    break;
                case 'title':
                case 'price':
                case 'deadline':
                case 'description':
                    if (empty($val)) {
                        $errors[$key] = $modx->lexicon('formit.field_required');
                    }
                    break;
            }
        }
        if ($user = $modx->getUser()) {
            $user_id = $user->id;
            $profile = $user->getOne('Profile');
        } else {
            $errors['user_id'] = 'User ID not found!';
        }
        /* Validate fields */

        /* Create deal */
        if (empty($errors)) {
            $hash = hash('sha256', time() . $user_id . $_POST['is_customer'] . $_POST['is_company'] . $_POST['company_name'] . $_POST['fee_payer'] . $_POST['fee_payer'] . $_POST['title'] . $_POST['description'] . $_POST['price'] . strtotime($_POST['deadline']) . rand());
            $data['hash_link'] = $modx->makeUrl($scriptProperties['dealResourceID'], '', array('d' => $hash), 'full');
            $deadline =  DateTime::createFromFormat('d/m/Y H:i:s', $_POST['deadline'] . ' 00:00:00');
            $price = (float) str_replace(',', '', $_POST['price']);
            $fee = (float) $price * $modx->getOption('company_fee', null, 0.05, true);
            $deal = $modx->newObject('SafeDeal');
            $deal->set('created', time());
            $deal->set('updated', time());
            $deal->set('author_id', $user_id);
            $deal->set('is_customer', (int) $_POST['is_customer']);
            $deal->set('title', strip_tags($_POST['title']));
            $deal->set('description', strip_tags($_POST['description']));
            $deal->set('status', 0);
            $deal->set('price', $price);
            $deal->set('fee', $fee);
            $deal->set('deadline', $deadline->getTimestamp());
            $deal->set('hash', $hash);
            if (!$deal->save()) {
                $errors['deal'] = 'Can not create a deal!';
            }
        }
        /* Create deal */
        break;
    case 'deal/accept':
        if ($deal = $modx->getObject('SafeDeal', array('hash' => $scriptProperties['hash']))) {
            if ($user = $modx->getUser()) {
                $user_id = $user->id;
            } else {
                $errors['user_id'] = 'User ID not found!';
            }
            /* Accept deal */
            if (empty($errors)) {
                $deal->set('status', 2);
                $deal->set('partner_id', $user_id);
                $deal->set('updated', time());
                if (!$deal->save()) {
                    $errors['deal'] = 'Can not save a deal!';
                }
            } else {
                $errors['deal'] = 'Deal not found!';
            }
        }
        /* Accept deal */
        break;
    case 'deal/change':
        foreach ($_POST as $key => $val) {
            switch ($key) {
                case 'title':
                case 'description':
                case 'price':
                case 'deadline':
                    if (empty($val)) {
                        $errors[$key] = $modx->lexicon('formit.field_required');
                    }
                    break;
            }
        }
        if ($user = $modx->getUser()) {
            $user_id = $user->id;
        } else {
            $errors['user_id'] = 'User ID not found!';
        }
        /* Change deal */
        if (empty($errors)) {
            if ($deal = $modx->getObject('SafeDeal', array('hash' => $scriptProperties['hash']))) {
                if (in_array($deal->get('status'), [0, 1])) {
                    $deadline =  DateTime::createFromFormat('d/m/Y H:i:s', $_POST['deadline'] . ' 00:00:00');
                    $price = (float) str_replace(',', '', $_POST['price']);
                    $fee = (float) $price * $modx->getOption('company_fee', null, 0.05, true);
                    $deal->set('title', strip_tags($_POST['title']));
                    $deal->set('description', strip_tags($_POST['description']));
                    $deal->set('price', $price);
                    $deal->set('fee', $fee);
                    $deal->set('deadline', $deadline->getTimestamp());
                    $deal->set('status', 0);
                    $deal->set('updated', time());
                    if (!$deal->save()) {
                        $errors['deal'] = 'Can not save a deal!';
                    }
                } else {
                    $errors['deal'] = 'Can not change status a deal!';
                }
            } else {
                $errors['deal'] = 'Deal not found!';
            }
        }
        /* Change deal */
        break;
    case 'deal/extension/request':
        foreach ($_POST as $key => $val) {
            switch ($key) {
                case 'deadline':
                    if (empty($val)) {
                        $errors[$key] = $modx->lexicon('formit.field_required');
                    }
                    break;
            }
        }
        if ($user = $modx->getUser()) {
            $user_id = $user->id;
        } else {
            $errors['user_id'] = 'User ID not found!';
        }
        /* Extension deal */
        if (empty($errors)) {
            if ($deal = $modx->getObject('SafeDeal', array('hash' => $scriptProperties['hash']))) {
                if ($deal->get('status') == 3) {
                    $deadline =  DateTime::createFromFormat('d/m/Y H:i:s', $_POST['deadline'] . ' 00:00:00');
                    $deal->set('tmp_deadline', $deadline->getTimestamp());
                    $deal->set('status', 4);
                    $deal->set('updated', time());
                    if (!$deal->save()) {
                        $errors['deal'] = 'Can not save a deal!';
                    }
                } else {
                    $errors['deal'] = 'Can not change status a deal!';
                }
            } else {
                $errors['deal'] = 'Deal not found!';
            }
        }
        /* Extension deal */
        break;
    case 'deal/extension/accept':
        if ($user = $modx->getUser()) {
            $user_id = $user->id;
        } else {
            $errors['user_id'] = 'User ID not found!';
        }
        /* Extension deal */
        if (empty($errors)) {
            if ($deal = $modx->getObject('SafeDeal', array('hash' => $scriptProperties['hash']))) {
                if ($deal->get('status') == 4) {
                    $deal->set('deadline', $deal->get('tmp_deadline'));
                    $deal->set('status', 3);
                    $deal->set('updated', time());
                    if (!$deal->save()) {
                        $errors['deal'] = 'Can not save a deal!';
                    }
                } else {
                    $errors['deal'] = 'Can not change status a deal!';
                }
            } else {
                $errors['deal'] = 'Deal not found!';
            }
        }
        /* Extension deal */
        break;
    case 'deal/extension/cancel':
        if ($user = $modx->getUser()) {
            $user_id = $user->id;
        } else {
            $errors['user_id'] = 'User ID not found!';
        }
        /* Extension deal */
        if (empty($errors)) {
            if ($deal = $modx->getObject('SafeDeal', array('hash' => $scriptProperties['hash']))) {
                if ($deal->get('status') == 4) {
                    $deal->set('status', 3);
                    $deal->set('updated', time());
                    if (!$deal->save()) {
                        $errors['deal'] = 'Can not save a deal!';
                    }
                } else {
                    $errors['deal'] = 'Can not change status a deal!';
                }
            } else {
                $errors['deal'] = 'Deal not found!';
            }
        }
        /* Extension deal */
        break;
    case 'deal/cancel':
        /* Cancel deal */
        if ($user = $modx->getUser()) {
            $user_id = $user->id;
        } else {
            $errors['user_id'] = 'User ID not found!';
        }
        if (empty($errors)) {
            if ($deal = $modx->getObject('SafeDeal', array('hash' => $scriptProperties['hash']))) {
                if (in_array($deal->get('status'), [0, 1, 2])) {
                    $deal->set('status', 6);
                    $deal->set('funds_withdrawn', 1);
                    $deal->set('updated', time());
                    if (!$deal->save()) {
                        $errors['deal'] = 'Can not save a deal!';
                    }
                } else {
                    $errors['deal'] = 'Can not change status a deal!';
                }
            } else {
                $errors['deal'] = 'Deal not found!';
            }
        }
        /* Cancel deal */
        break;
    case 'deal/pay':
        if ($deal = $modx->getObject('SafeDeal', array('hash' => $scriptProperties['hash']))) {
            if ($user = $modx->getUser()) {
                $user_id = $user->id;
            } else {
                $errors['user_id'] = 'User ID not found!';
            }
            if ($deal->get('status') != 2) {
                $errors['deal'] = 'Can not pay this deal!';
            }
            /* Pay deal */
            if (empty($errors)) {
                $deal->set('payment_id', '123456789');
                $deal->set('payment_total', $deal->get('price') + $deal->get('fee'));
                $deal->set('status', 3);
                $deal->set('updated', time());
                if (!$deal->save()) {
                    $errors['deal'] = 'Can not save a deal!';
                }
            } else {
                $errors['deal'] = 'Deal not found!';
            }
        }
        /* Pay deal */
        break;
    case 'deal/complete':
        if ($user = $modx->getUser()) {
            $user_id = $user->id;
        } else {
            $errors['user_id'] = 'User ID not found!';
        }
        if (empty($errors)) {
            if ($deal = $modx->getObject('SafeDeal', array('hash' => $scriptProperties['hash']))) {
                if ($deal->get('status') == 3) {
                    $deal->set('status', 5);
                    $deal->set('updated', time());
                    if (!$deal->save()) {
                        $errors['deal'] = 'Can not save a deal!';
                    }
                } else {
                    $errors['deal'] = 'Can not complete this deal!';
                }
            } else {
                $errors['deal'] = 'Deal not found!';
            }
        }
        break;
    case 'deal/close':
        if ($user = $modx->getUser()) {
            $user_id = $user->id;
        } else {
            $errors['user_id'] = 'User ID not found!';
        }
        if (empty($errors)) {
            if ($deal = $modx->getObject('SafeDeal', array('hash' => $scriptProperties['hash']))) {
                if ($deal->get('status') == 5) {
                    $deal->set('status', 6);
                    $deal->set('updated', time());
                    if (!$deal->save()) {
                        $errors['deal'] = 'Can not save a deal!';
                    }
                } else {
                    $errors['deal'] = 'Can not complete this deal!';
                }
            } else {
                $errors['deal'] = 'Deal not found!';
            }
        }
        break;
    default:
        $errors['default'] = 'Action undefined';
}


if (empty($errors)) {
    $emailTPL = $modx->getOption('emailTPL', $scriptProperties, '');
    $emailSubject = $modx->getOption('emailSubject', $scriptProperties, '');
    $deal_link = $modx->makeUrl($scriptProperties['dealResourceID'] ?: 1, '', array('d' => $deal->get('hash')), 'full');

    $users = $modx->getCollection('modUser', array('id:IN' => array($deal->get('author_id'), $deal->get('partner_id'))));
    foreach ($users as $usr) {
        if ($usr->get('id') > 0) {
            $prfl = $usr->getOne('Profile');
            $messageEmail = empty($emailTPL)
                ? $action
                : $pdoFetch->getChunk($emailTPL, array(
                    'fullname' => $prfl->get('fullname'),
                    'link' => $deal_link,
                ));
            $msg = array(
                'email' => array(
                    'to' => $prfl->get('email'),
                    'subjectEmail' => $emailSubject ?: $modx->getOption('site_name') . ': Статус сделки изменился!',
                    'messageEmail' => $messageEmail,
                ),
            );
            array_push(
                $messages,
                $msg
            );
        }
    }
    $msg = array(
        'telegram' => array(
            'site' => 'Сайт: ' . $modx->getOption('site_name'),
            'id' => 'Сделка (id): ' . $deal->get('id'),
            'Ссылка: ' . $deal_link,
            'author' => 'Author (id): ' . $deal->get('author_id'),
            'partner' => 'Partner (id): ' . ($deal->get('partner_id') > 0 ? $deal->get('partner_id') : ''),
            'action' => $action
        )
    );
    array_push(
        $messages,
        $msg
    );
    if (!empty($messages)) {
        //$modx->log(1, print_r($messages, true));
        $modx->getService('mail', 'mail.modPHPMailer');
        foreach ($messages as $message) {
            /*
            if (array_key_exists('telegram', $message)) {
                $token = "5380434987:AAHsLZvoVMHeDgel0wEGL66T7Rb7wf38pp4";
                //Получить chat_id https://api.telegram.org/bot5380434987:AAHsLZvoVMHeDgel0wEGL66T7Rb7wf38pp4/getUpdates
                $chat_ids = array(
                    '5178588337',
                    '3951096',
                    '2093385478',
                    '161188495',
                );
                foreach ($chat_ids as $chat_id) {
                    file_get_contents("https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chat_id . "&text=" . urlencode(implode(PHP_EOL, $message['telegram'])));
                }
            }
            */
            if (array_key_exists('email', $message)) {
                $modx->mail->set(modMail::MAIL_FROM, $modx->getOption('site_email'));
                $modx->mail->set(modMail::MAIL_FROM_NAME, $modx->getOption('site_name'));
                $modx->mail->address('to', $message['email']['to']);
                $modx->mail->set(modMail::MAIL_SUBJECT, $message['email']['subjectEmail']);
                $modx->mail->set(modMail::MAIL_BODY, $message['email']['messageEmail']);
                $modx->mail->setHTML(true);
                if (!$modx->mail->send()) {
                    $modx->log(modX::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: ' . $modx->mail->mailer->ErrorInfo);
                }
            }
        }
        $modx->mail->reset();
    }
    return $AjaxForm->success('', array_merge($data, array('result' => true, 'message' => $scriptProperties['successMsg'], 'modalID' => $scriptProperties['successModalID'])));
} else {
    return $AjaxForm->error('', array_merge($data, array('result' => false, 'message' => $scriptProperties['errorMsg'], 'modalID' => $scriptProperties['errorModalID'], 'errors' => $errors)));
}
