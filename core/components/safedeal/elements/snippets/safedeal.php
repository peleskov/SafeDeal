<?php
// Откликаться будет ТОЛЬКО на ajax запросы
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest') {
    return;
}

$modx->lexicon->load('formit:default');

$errors = [];
$data = ['service' => 'safedeal'];
/* Deal status code
    0 => 'Ожидает согласования / Новая сделка',
    1 => 'Согласована / Не оплачена',
    2 => 'Оплачена / В работе',
    3 => 'Ожидает согласования / Запрос на отмену',
    4 => 'Ожидает согласования / Запрос на изменение',
    5 => 'Открыт спор',
    6 => 'Завершена',
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
                case 'is_company':
                    if (!in_array($val, [1, 0])) {
                        $errors[$key] = '';
                    }
                    break;
                case 'company_name':
                    if ($_POST['is_company'] == 1 && empty($val)) {
                        $errors[$key] = $modx->lexicon('formit.field_required');
                    }
                    break;
                case 'fee_payer':
                    if (!in_array($val, [0, 1, 2])) {
                        $errors[$key] = '';
                    }
                    break;
                case 'title':
                case 'price':
                case 'fee_payer':
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
        /* Validate fields */

        /* Create deal */
        if (empty($errors)) {
            $hash = hash('sha256', time() . $user_id . $_POST['is_customer'] . $_POST['is_company'] . $_POST['company_name'] . $_POST['fee_payer'] . $_POST['fee_payer'] . $_POST['title'] . $_POST['description'] . $_POST['price'] . strtotime($_POST['deadline']) . rand());
            $data['hash_link'] = $modx->makeUrl($scriptProperties['pageDealID'], '', array('d' => $hash), 'full');
            $deadline =  DateTime::createFromFormat('d/m/Y H:i:s', $_POST['deadline'] . ' 00:00:00');
            $fee_payer = (int) $_POST['fee_payer'];
            $price = (float) str_replace(',', '', $_POST['price']);
            $fee = (float) $price * $modx->getOption('company_fee', null, 0.05, true);
            if ($fee_payer == 1) {
                $price = $price - $fee;
            } elseif ($fee_payer == 2) {
                $price = $price - $fee / 2;
            }
            $deal = $modx->newObject('SafeDeal');
            $deal->set('created', time());
            $deal->set('updated', time());
            $deal->set('author_id', $user_id);
            $deal->set('is_customer', (int) $_POST['is_customer']);
            $deal->set('is_company', (int) $_POST['is_company']);
            $deal->set('company_name', strip_tags($_POST['company_name']));
            $deal->set('fee_payer', $fee_payer);
            $deal->set('title', strip_tags($_POST['title']));
            $deal->set('description', strip_tags($_POST['description']));
            $deal->set('status', 0);
            $deal->set('payment_id', 0);
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
                switch ($deal->get('status')) {
                    case 0:
                        $deal->set('status', 1);
                        $deal->set('partner_id', $user_id);
                        break;
                    case 3:
                        $deal->set('status', 6);
                        break;
                    case 4:
                        $deal_cost = $deal->get('tmp_price') + $deal->get('tmp_fee');
                        if ($deal->get('payment_total') > 0 && $deal->get('payment_total') >= $deal_cost) { // Сделка уже оплачена
                            $deal->set('status', 2);
                        } else {
                            $deal->set('status', 1);
                        }
                        if($deal->get('tmp_price') > 0){
                            $deal->set('price', $deal->get('tmp_price'));
                            $deal->set('fee', $deal->get('tmp_fee'));
                        }
                        if($deal->get('tmp_deadline') > 0){
                            $deal->set('deadline', $deal->get('tmp_deadline'));
                        }
                        $deal->set('initiator_id', 0);
                        $deal->set('tmp_price', 0);
                        $deal->set('tmp_fee', 0);
                        $deal->set('tmp_deadline', 0);
                        break;
                }
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
                if (in_array($deal->get('status'), [0, 1, 2])) {
                    $deadline =  DateTime::createFromFormat('d/m/Y H:i:s', $_POST['deadline'] . ' 00:00:00');
                    $fee_payer = $deal->get('fee_payer');
                    $price = (float) str_replace(',', '', $_POST['price']);
                    $fee = (float) $price * $modx->getOption('company_fee', null, 0.05, true);
                    if ($fee_payer == 1) {
                        $price = $price - $fee;
                    } elseif ($fee_payer == 2) {
                        $price = $price - $fee / 2;
                    }
                    if ($deal->get('status') == 0) {
                        $deal->set('price', $price);
                        $deal->set('fee', $fee);
                        $deal->set('deadline', $deadline->getTimestamp());
                    } else {
                        $deal->set('initiator_id', $user_id);
                        if ($price > 0 && $price != $deal->get('price')) {
                            $deal->set('tmp_price', $price);
                            $deal->set('tmp_fee', $fee);
                        }
                        if ($deadline->getTimestamp() != $deal->get('deadline')) {
                            $deal->set('tmp_deadline', $deadline->getTimestamp());
                        }
                        $deal->set('status', 4);
                    }
                } else {
                    $errors['deal'] = 'Can not change status a deal!';
                }
                $deal->set('updated', time());
                if (!$deal->save()) {
                    $errors['deal'] = 'Can not save a deal!';
                }
            } else {
                $errors['deal'] = 'Deal not found!';
            }
        }
        /* Change deal */
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
                if ($deal->get('status') == 0) {
                    $deal->set('status', 6);
                } else {
                    $deal->set('initiator_id', $user_id);
                    $deal->set('status', 3);
                }
                $deal->set('updated', time());
                if (!$deal->save()) {
                    $errors['deal'] = 'Can not save a deal!';
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
            if ($deal->get('status') != 1) {
                $errors['deal'] = 'Can not pay this deal!';
            }
            /* Pay deal */
            if (empty($errors)) {
                $deal->set('payment_id', '123456789');
                $deal->set('payment_total', $deal->get('price') + $deal->get('fee'));
                $deal->set('status', 2);
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
    case 'deal/dispute':
        foreach ($_POST as $key => $val) {
            if (empty($val)) {
                $errors[$key] = $modx->lexicon('formit.field_required');
            }
        }
        if ($user = $modx->getUser()) {
            $user_id = $user->id;
        } else {
            $errors['user_id'] = 'User ID not found!';
        }
        if (empty($errors)) {
            if ($deal = $modx->getObject('SafeDeal', array('hash' => $scriptProperties['hash']))) {
                if ($deal->get('status') != 5) {
                    $deal->set('status', 5);
                    $deal->set('updated', time());
                    if (!$deal->save()) {
                        $errors['deal'] = 'Can not save a deal!';
                    }
                } else {
                    $errors['deal'] = 'Can not dispute this deal!';
                }
            } else {
                $errors['deal'] = 'Deal not found!';
            }
        }

        break;
    case 'deal/complete':
        if ($user = $modx->getUser()) {
            $user_id = $user->id;
        } else {
            $errors['user_id'] = 'User ID not found!';
        }
        if (empty($errors)) {
            if ($deal = $modx->getObject('SafeDeal', array('hash' => $scriptProperties['hash']))) {
                if ($deal->get('status') == 2) {
                    $deal->set('status', 6);
                    $deal->set('updated', time());
                    if (!$deal->save()) {
                        $errors['deal'] = 'Can not save a deal!';
                    }
                } else {
                    $errors['deal'] = 'Can not dispute this deal!';
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
    return $AjaxForm->success('', array_merge($data, array('result' => true, 'message' => $scriptProperties['successMsg'], 'modalID' => $scriptProperties['successModalID'])));
} else {
    return $AjaxForm->error('', array_merge($data, array('result' => false, 'message' => $scriptProperties['errorMsg'], 'modalID' => $scriptProperties['errorModalID'], 'errors' => $errors)));
}
