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

$SafeDeal = $modx->getService('SafeDeal', 'SafeDeal', MODX_CORE_PATH . 'components/safedeal/model/', $scriptProperties);
if (!$SafeDeal) {
    return 'Could not load SafeDeal class!';
}

$modx->lexicon->load('formit:default');

$messages = [];
$errors = [];
$notify = true;
$data = ['service' => 'safedeal'];

/* Deal status code
    0 => 'Создана / Ожидает предложения партнеру',
    1 => 'Предложена / Ожидает подтверждения партнера',
    2 => 'Подтверждена партнером / Ожидает оплаты',
    3 => 'Оплачена / В работе',
    31 => 'В работе / Забираю посылку',
    32 => 'В работе / В пути на адрес доставки',
    4 => 'Продление / Ожидает согласования',
    5 => 'Завершена / Ожидает согласия заказчика',
    6 => 'Закрыта / Ожидает выплаты исполнителю / Архив',
*/

/* DealNotice action code
    0 => 'Создана',
    1 => 'Отменена',
    2 => 'Подтверждена',
    3 => 'Изменена',
    4 => 'Запрос на продление',
    5 => 'Отменено продление',
    6 => 'Подтверждено продление',
    7 => 'Оплачена',
    8 => 'Завершена',
    9 => 'Закрыта',
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
        if (isset($_FILES['docs']) && !empty($_FILES['docs'])) {
            $docs = [];
            foreach ($_FILES['docs'] as $k => $l) {
                foreach ($l as $i => $v) {
                    $docs[$i][$k] = $v;
                }
            }
            $docMaxSize = $modx->getOption('imageMaxSize', $scriptProperties, 1048576);
            $validExt = explode(',', $modx->getOption('validExt', $scriptProperties, 'pdf,txt,doc,docx,xls,xlsx'));
            foreach ($docs as $key => $doc) {
                if ($doc['error'] == 0 && is_uploaded_file($doc['tmp_name'])) {
                    if ($doc['size'] >= $docMaxSize) {
                        $err = $doc['name'] . ': Размер файла превышает допустимый лимит.' . PHP_EOL;
                        $errors['docs[]'] = empty($errors['docs[]']) ? $err : $errors['docs[]'] . $err;
                        break;
                    }
                    $docExt = trim(mb_strtolower(pathinfo($doc['name'], PATHINFO_EXTENSION)));
                    if (!in_array($docExt, $validExt)) {
                        $err = $doc['name'] . ': Не допустимый тип файла.' . PHP_EOL;
                        $errors['docs[]'] = empty($errors['docs[]']) ? $err : $errors['docs[]'] . $err;
                    }
                }
            }
        }
        /* Validate fields */

        /* Create deal */
        if (empty($errors)) {
            if (!empty($docs)) {
                $docsDirPath = rtrim($modx->getOption('docsDirPath', $scriptProperties, MODX_ASSETS_PATH . 'docs/usr_' . $user_id), '/');
                if (!file_exists($docsDirPath)) mkdir($docsDirPath, 0755, true);
                $new_docs = [];
                foreach ($docs as $key => $doc) {
                    $docExt = trim(mb_strtolower(pathinfo($doc['name'], PATHINFO_EXTENSION)));
                    $newDocName = md5($doc['name'] . rand()) . '.' . $docExt;
                    $newDocPath = $docsDirPath . '/' . $newDocName;
                    if (move_uploaded_file($doc['tmp_name'], $newDocPath)) {
                        $newDoc = str_replace(MODX_BASE_PATH, '', $newDocPath);
                        $new_docs[] = array(
                            'name' => $newDocName,
                            'name_original' => $doc['name'],
                            'path' => $newDoc,
                            'url' => $newDoc,
                            'full_url' => MODX_SITE_URL . $newDoc,
                            'size' => $doc['size'],
                            'extension' => $docExt
                        );
                    } else {
                        $errors['docs'][$doc['name']] = 'Не удалось сохранить файл.';
                    }
                }
            }
            $hash = hash('sha256', time() . $user_id . $_POST['is_customer'] . $_POST['title'] . $_POST['description'] . $_POST['price'] . strtotime($_POST['deadline']) . rand());
            $data['hash_link'] = $modx->makeUrl($scriptProperties['dealResourceID'], '', array('d' => $hash), 'full');
            $deadline_post = str_replace('.', '/', $_POST['deadline']);
            $deadline =  DateTime::createFromFormat('d/m/Y H:i:s',  "$deadline_post 00:00:00");
            $price = (float) str_replace(',', '', $_POST['price']);
            $fee = (float) $price * $modx->getOption('company_fee', null, 0.05, true);
            $deal = $modx->newObject('Deal');
            $deal->set('created', time());
            $deal->set('updated', time());
            $deal->set('author_id', $user_id);
            $deal->set('is_customer', (int) $_POST['is_customer']);
            $deal->set('title', strip_tags($_POST['title']));
            if (!empty($_POST['partner_id'])) {
                $deal->set('partner_id', (int) $_POST['partner_id']);
            }
            if (!empty($_POST['advert_id'])) {
                $deal->set('advert_id', (int) $_POST['advert_id']);
            }
            $deal->set('description', strip_tags($_POST['description']));
            $deal->set('status', 0);
            $deal->set('price', $price);
            $deal->set('fee', $fee);
            $deal->set('deadline', $deadline->getTimestamp());
            $deal->set('hash', $hash);
            $deal->set('docs', json_encode($new_docs, true));
            if (isset($_POST['extended'])) {
                if (is_array($_POST['extended'])) {
                    $deal->set('extended', json_encode($_POST['extended'], true));
                } else {
                    $deal->set('extended', strip_tags($_POST['extended']));
                }
            }
            if ($deal->save()) {
                $notice = $modx->newObject('DealNotice');
                $notice->set('created', time());
                $notice->set('user_id', $user_id);
                $notice->set('is_customer', $deal->get('is_customer'));
                $notice->set('action', 0);
                $notice->set('deal_id', $deal->get('id'));
                $notice->set('hash', $deal->get('hash'));
                $notice->save();
                if ($deal->get('partner_id') > 0) {
                    $notice = $modx->newObject('DealNotice');
                    $notice->set('created', time());
                    $notice->set('user_id', $deal->get('partner_id'));
                    if ($deal->get('is_customer') == 1) {
                        $notice->set('is_customer', 0);
                    } else {
                        $notice->set('is_customer', 1);
                    }
                    $notice->set('action', 0);
                    $notice->set('deal_id', $deal->get('id'));
                    $notice->set('hash', $deal->get('hash'));
                    $notice->save();
                }
            } else {
                $errors['deal'] = 'Can not save a deal!';
            }
        }
        /* Create deal */
        break;
    case 'deal/accept':
        if ($deal = $modx->getObject('Deal', array('hash' => $scriptProperties['hash']))) {
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
                if ($deal->save()) {
                    $notice = $modx->newObject('DealNotice');
                    $notice->set('created', time());
                    $notice->set('user_id', $user_id);
                    $notice->set('is_customer', $deal->get('is_customer'));
                    $notice->set('action', 2);
                    $notice->set('deal_id', $deal->get('id'));
                    $notice->set('hash', $deal->get('hash'));
                    $notice->save();
                    if ($deal->get('partner_id') > 0) {
                        $notice = $modx->newObject('DealNotice');
                        $notice->set('created', time());
                        $notice->set('user_id', $deal->get('partner_id'));
                        if ($deal->get('is_customer') == 1) {
                            $notice->set('is_customer', 0);
                        } else {
                            $notice->set('is_customer', 1);
                        }
                        $notice->set('action', 2);
                        $notice->set('deal_id', $deal->get('id'));
                        $notice->set('hash', $deal->get('hash'));
                        $notice->save();
                    }
                } else {
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
        if (isset($_FILES['docs']) && !empty($_FILES['docs'])) {
            $docs = [];
            foreach ($_FILES['docs'] as $k => $l) {
                foreach ($l as $i => $v) {
                    $docs[$i][$k] = $v;
                }
            }
            $docMaxSize = $modx->getOption('imageMaxSize', $scriptProperties, 1048576);
            $validExt = explode(',', $modx->getOption('validExt', $scriptProperties, 'pdf,txt,doc,docx,xls,xlsx'));
            foreach ($docs as $key => $doc) {
                if ($doc['error'] == 0 && is_uploaded_file($doc['tmp_name'])) {
                    if ($doc['size'] >= $docMaxSize) {
                        $err = $doc['name'] . ': Размер файла превышает допустимый лимит.' . PHP_EOL;
                        $errors['docs[]'] = empty($errors['docs[]']) ? $err : $errors['docs[]'] . $err;
                        break;
                    }
                    $docExt = trim(mb_strtolower(pathinfo($doc['name'], PATHINFO_EXTENSION)));
                    if (!in_array($docExt, $validExt)) {
                        $err = $doc['name'] . ': Не допустимый тип файла.' . PHP_EOL;
                        $errors['docs[]'] = empty($errors['docs[]']) ? $err : $errors['docs[]'] . $err;
                    }
                }
            }
        }


        /* Change deal */
        if (empty($errors)) {
            if ($deal = $modx->getObject('Deal', array('hash' => $scriptProperties['hash']))) {
                if (in_array($deal->get('status'), [0, 1])) {
                    $new_docs = [];
                    $cur_docs = [];
                    if (!empty($docs)) {
                        $docsDirPath = rtrim($modx->getOption('docsDirPath', $scriptProperties, MODX_ASSETS_PATH . 'docs/usr_' . $user_id), '/');
                        if (!file_exists($docsDirPath)) mkdir($docsDirPath, 0755, true);
                        foreach ($docs as $key => $doc) {
                            $docExt = trim(mb_strtolower(pathinfo($doc['name'], PATHINFO_EXTENSION)));
                            $newDocName = md5($doc['name'] . rand()) . '.' . $docExt;
                            $newDocPath = $docsDirPath . '/' . $newDocName;
                            if (move_uploaded_file($doc['tmp_name'], $newDocPath)) {
                                $newDoc = str_replace(MODX_BASE_PATH, '', $newDocPath);
                                $new_docs[] = array(
                                    'name' => $newDocName,
                                    'name_original' => $doc['name'],
                                    'path' => $newDoc,
                                    'url' => $newDoc,
                                    'full_url' => MODX_SITE_URL . $newDoc,
                                    'size' => $doc['size'],
                                    'extension' => $docExt
                                );
                            } else {
                                $errors['docs'][$doc['name']] = 'Не удалось сохранить файл.';
                            }
                        }
                    }

                    if (isset($_POST['doc_ids']) && !empty($_POST['doc_ids'])) {
                        $doc_ids = explode(',', $_POST['doc_ids']);
                        $cur_docs = json_decode($deal->get('docs'), true);
                        foreach ($cur_docs as $k => $cur_doc) {
                            if (!in_array($k, $doc_ids)) {
                                unset($cur_docs[$k]);
                            }
                        }
                    }
                    $up_docs = '';
                    if (!empty($new_docs)) {
                        $new_docs = array_merge($new_docs, $cur_docs);
                        $up_docs = json_encode($new_docs, true);
                    } else if (!empty($cur_docs)) {
                        $new_docs = $cur_docs;
                        $up_docs = json_encode($new_docs, true);
                    }

                    $deadline_post = str_replace('.', '/', $_POST['deadline']);
                    $deadline =  DateTime::createFromFormat('d/m/Y H:i:s',  "$deadline_post 00:00:00");
                    $price = (float) str_replace(',', '', $_POST['price']);
                    $fee = (float) $price * $modx->getOption('company_fee', null, 0.05, true);
                    $deal->set('title', strip_tags($_POST['title']));
                    $deal->set('description', strip_tags($_POST['description']));
                    $deal->set('price', $price);
                    $deal->set('fee', $fee);
                    $deal->set('deadline', $deadline->getTimestamp());
                    $deal->set('status', 0);
                    $deal->set('updated', time());
                    $deal->set('docs', $up_docs);
                    if ($deal->save()) {
                        $notice = $modx->newObject('DealNotice');
                        $notice->set('created', time());
                        $notice->set('user_id', $user_id);
                        $notice->set('is_customer', $deal->get('is_customer'));
                        $notice->set('action', 3);
                        $notice->set('deal_id', $deal->get('id'));
                        $notice->set('hash', $deal->get('hash'));
                        $notice->save();
                        if ($deal->get('partner_id') > 0) {
                            $notice = $modx->newObject('DealNotice');
                            $notice->set('created', time());
                            $notice->set('user_id', $deal->get('partner_id'));
                            if ($deal->get('is_customer') == 1) {
                                $notice->set('is_customer', 0);
                            } else {
                                $notice->set('is_customer', 1);
                            }
                            $notice->set('action', 3);
                            $notice->set('deal_id', $deal->get('id'));
                            $notice->set('hash', $deal->get('hash'));
                            $notice->save();
                        }
                    } else {
                        $errors['deal'] = 'Can not save a deal!';
                    }
                } else {
                    $errors['deal'] = 'Can not change deal!';
                }
            } else {
                $errors['deal'] = 'Deal not found!';
            }
        }
        /* Change deal */
        break;
    case 'deal/setstatus':
        if (empty($scriptProperties['status'])) {
            $errors['status'] = $modx->lexicon('formit.field_required');
        }
        if (empty($scriptProperties['hash'])) {
            $errors['hash'] = $modx->lexicon('formit.field_required');
        }
        if ($user = $modx->getUser()) {
            $user_id = $user->id;
        } else {
            $errors['user_id'] = 'User ID not found!';
        }

        /* Set status deal */
        if (empty($errors)) {
            if ($deal = $modx->getObject('Deal', array('hash' => $scriptProperties['hash']))) {
                $deal->set('status', $scriptProperties['status']);
                if ($deal->save()) {
                    $notice = $modx->newObject('DealNotice');
                    $notice->set('created', time());
                    $notice->set('user_id', $user_id);
                    $notice->set('is_customer', $deal->get('is_customer'));
                    $notice->set('action', 3);
                    $notice->set('deal_id', $deal->get('id'));
                    $notice->set('hash', $deal->get('hash'));
                    $notice->save();
                    if ($deal->get('partner_id') > 0) {
                        $notice = $modx->newObject('DealNotice');
                        $notice->set('created', time());
                        $notice->set('user_id', $deal->get('partner_id'));
                        if ($deal->get('is_customer') == 1) {
                            $notice->set('is_customer', 0);
                        } else {
                            $notice->set('is_customer', 1);
                        }
                        $notice->set('action', 3);
                        $notice->set('deal_id', $deal->get('id'));
                        $notice->set('hash', $deal->get('hash'));
                        $notice->save();
                    }
                } else {
                    $errors['deal'] = 'Can not save a deal!';
                }
            } else {
                $errors['deal'] = 'Deal not found!';
            }
        }
        /* Set status deal */
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
            if ($deal = $modx->getObject('Deal', array('hash' => $scriptProperties['hash']))) {
                if ($deal->get('status') == 3) {
                    $deadline_post = str_replace('.', '/', $_POST['deadline']);
                    $deadline =  DateTime::createFromFormat('d/m/Y H:i:s',  "$deadline_post 00:00:00");
                    $deal->set('tmp_deadline', $deadline->getTimestamp());
                    $deal->set('status', 4);
                    $deal->set('updated', time());
                    if ($deal->save()) {
                        $notice = $modx->newObject('DealNotice');
                        $notice->set('created', time());
                        $notice->set('user_id', $user_id);
                        $notice->set('is_customer', $deal->get('is_customer'));
                        $notice->set('action', 4);
                        $notice->set('deal_id', $deal->get('id'));
                        $notice->set('hash', $deal->get('hash'));
                        $notice->save();
                        if ($deal->get('partner_id') > 0) {
                            $notice = $modx->newObject('DealNotice');
                            $notice->set('created', time());
                            $notice->set('user_id', $deal->get('partner_id'));
                            if ($deal->get('is_customer') == 1) {
                                $notice->set('is_customer', 0);
                            } else {
                                $notice->set('is_customer', 1);
                            }
                            $notice->set('action', 4);
                            $notice->set('deal_id', $deal->get('id'));
                            $notice->set('hash', $deal->get('hash'));
                            $notice->save();
                        }
                    } else {
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
            if ($deal = $modx->getObject('Deal', array('hash' => $scriptProperties['hash']))) {
                if ($deal->get('status') == 4) {
                    $deal->set('deadline', $deal->get('tmp_deadline'));
                    $deal->set('status', 3);
                    $deal->set('updated', time());
                    if ($deal->save()) {
                        $notice = $modx->newObject('DealNotice');
                        $notice->set('created', time());
                        $notice->set('user_id', $user_id);
                        $notice->set('is_customer', $deal->get('is_customer'));
                        $notice->set('action', 6);
                        $notice->set('deal_id', $deal->get('id'));
                        $notice->set('hash', $deal->get('hash'));
                        $notice->save();
                        if ($deal->get('partner_id') > 0) {
                            $notice = $modx->newObject('DealNotice');
                            $notice->set('created', time());
                            $notice->set('user_id', $deal->get('partner_id'));
                            if ($deal->get('is_customer') == 1) {
                                $notice->set('is_customer', 0);
                            } else {
                                $notice->set('is_customer', 1);
                            }
                            $notice->set('action', 6);
                            $notice->set('deal_id', $deal->get('id'));
                            $notice->set('hash', $deal->get('hash'));
                            $notice->save();
                        }
                    } else {
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
            if ($deal = $modx->getObject('Deal', array('hash' => $scriptProperties['hash']))) {
                if ($deal->get('status') == 4) {
                    $deal->set('status', 3);
                    $deal->set('updated', time());
                    if ($deal->save()) {
                        $notice = $modx->newObject('DealNotice');
                        $notice->set('created', time());
                        $notice->set('user_id', $user_id);
                        $notice->set('is_customer', $deal->get('is_customer'));
                        $notice->set('action', 5);
                        $notice->set('deal_id', $deal->get('id'));
                        $notice->set('hash', $deal->get('hash'));
                        $notice->save();
                        if ($deal->get('partner_id') > 0) {
                            $notice = $modx->newObject('DealNotice');
                            $notice->set('created', time());
                            $notice->set('user_id', $deal->get('partner_id'));
                            if ($deal->get('is_customer') == 1) {
                                $notice->set('is_customer', 0);
                            } else {
                                $notice->set('is_customer', 1);
                            }
                            $notice->set('action', 5);
                            $notice->set('deal_id', $deal->get('id'));
                            $notice->set('hash', $deal->get('hash'));
                            $notice->save();
                        }
                    } else {
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
            if ($deal = $modx->getObject('Deal', array('hash' => $scriptProperties['hash']))) {
                if (in_array($deal->get('status'), [0, 1, 2])) {
                    $deal->set('status', 6);
                    $deal->set('funds_withdrawn', 1);
                    $deal->set('updated', time());
                    if ($deal->save()) {
                        $notice = $modx->newObject('DealNotice');
                        $notice->set('created', time());
                        $notice->set('user_id', $user_id);
                        $notice->set('is_customer', $deal->get('is_customer'));
                        $notice->set('action', 1);
                        $notice->set('deal_id', $deal->get('id'));
                        $notice->set('hash', $deal->get('hash'));
                        $notice->save();
                        if ($deal->get('partner_id') > 0) {
                            $notice = $modx->newObject('DealNotice');
                            $notice->set('created', time());
                            $notice->set('user_id', $deal->get('partner_id'));
                            if ($deal->get('is_customer') == 1) {
                                $notice->set('is_customer', 0);
                            } else {
                                $notice->set('is_customer', 1);
                            }
                            $notice->set('action', 1);
                            $notice->set('deal_id', $deal->get('id'));
                            $notice->set('hash', $deal->get('hash'));
                            $notice->save();
                        }
                    } else {
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
        $post_data = [];
        //Проверяем поля формы
        foreach (['cc_num', 'cc_holder', 'cc_month', 'cc_year', 'cc_cvc'] as $field) { // обязательные поля
            if (empty($_POST[$field])) {
                $errors[$field] = 'Это поле не может быть пустым.';
            } else {
                switch ($field) {
                    case 'cc_num':
                        $cc_card = preg_replace("/[^0-9]/", '', $_POST[$field]);
                        if (strlen($cc_card) != 16) {
                            $errors[$field] = 'Не верный номер карты.';
                        }
                        $post_data[$field] = $cc_card;
                        break;
                    case 'cc_month':
                        $cc_month = preg_replace("/[^0-9]/", '', $_POST[$field]);
                        if ((int) $cc_month < 1 || (int) $cc_month > 12) {
                            $errors[$field] = 'Не верный месяц.';
                        }
                        $post_data[$field] = $cc_month;
                        break;
                    case 'cc_year':
                        $cc_year = preg_replace("/[^0-9]/", '', $_POST[$field]);
                        if ((int) $cc_year < date("y")) {
                            $errors[$field] = 'Не верный год.';
                        }
                        $post_data[$field] = $cc_year;
                        break;
                    case 'cc_cvc':
                        $cc_cvc = preg_replace("/[^0-9]/", '', $_POST[$field]);
                        if ((int) $cc_cvc < 0 || (int) $cc_cvc > 999) {
                            $errors[$field] = 'Не верный код.';
                        }
                        $post_data[$field] = $cc_cvc;
                        break;
                    case 'cc_holder':
                        $post_data[$field] = strip_tags($_POST[$field]);
                        if (preg_match("/[^a-zA-Z ]+/", $post_data[$field])) {
                            $errors[$field] = 'Укажите Имя и Фамилию только латинскими буквами!';
                        }
                }
            }
        }
        if ($user = $modx->getUser()) {
            $user_id = $user->id;
            $prfl = $user->getOne('Profile');
        } else {
            $errors['user_id'] = 'User ID not found!';
        }
        if ($deal = $modx->getObject('Deal', array('hash' => $scriptProperties['hash']))) {
        } else {
            $errors['deal'] = 'Deal not found!';
        }
        if ($deal->get('status') >= 2) {
            $errors['deal'] = 'Can not pay this deal!';
        }
        $apiKey = $modx->getOption('safedeal_merchant_apikey');
        $payment_id = $deal->get('payment_id');
        $result = array('status' => '');
        if (empty($errors) && $payment_id > 0) {
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
                    if ($deal->save()) {
                        $notice = $modx->newObject('DealNotice');
                        $notice->set('created', time());
                        $notice->set('user_id', $user_id);
                        $notice->set('is_customer', $deal->get('is_customer'));
                        $notice->set('action', 7);
                        $notice->set('deal_id', $deal->get('id'));
                        $notice->set('hash', $deal->get('hash'));
                        $notice->save();
                        if ($deal->get('partner_id') > 0) {
                            $notice = $modx->newObject('DealNotice');
                            $notice->set('created', time());
                            $notice->set('user_id', $deal->get('partner_id'));
                            if ($deal->get('is_customer') == 1) {
                                $notice->set('is_customer', 0);
                            } else {
                                $notice->set('is_customer', 1);
                            }
                            $notice->set('action', 7);
                            $notice->set('deal_id', $deal->get('id'));
                            $notice->set('hash', $deal->get('hash'));
                            $notice->save();
                        }
                    } else {
                        $errors['deal'] = 'Can not save a deal!';
                    }
                } elseif (!in_array($result['status'], ['STATUS_EXPIRED', 'STATUS_INIT'])) {
                    $errors['payment'] = 'При обработке платежа произошла ошибка, для уточнения деталей обратитесь к Администрации сайта.';
                }
            } else {
                $errors['merchant'] = 'Error in server response! Code response: ' . $info['http_code'];
            }
        }
        /* Инвойс просрочен или его еще нет сделаем новый */
        if (empty($errors)) {
            if ((isset($result['status']) && $result['status'] == 'STATUS_EXPIRED') || $payment_id == 0) {
                $requestParams = array(
                    'order_id' => $deal->get('id'),
                    'amount' => (int) (($deal->get('price') + $deal->get('fee')) * 100),
                    'email' => $prfl->get('email'),
                    'return_url' => $modx->makeUrl($scriptProperties['dealResourceID'], '', array('d' => $deal->get('hash'), 'action' => 'success'), 'full'),
                    'callback_url' => $modx->getOption('site_url') . $modx->getOption('safedeal_merchant_result_url'),
                    'fail_url' => $modx->makeUrl($scriptProperties['dealResourceID'], '', array('d' => $deal->get('hash'), 'action' => 'failure'), 'full'),
                    'processing_url' => $modx->makeUrl($scriptProperties['dealResourceID'], '', array('d' => $deal->get('hash'), 'action' => 'processing'), 'full'),
                    'payer_name' => $prfl->get('fullname'),
                    'payer_phone' => $prfl->get('mobilephone'),
                    'payer_email' => $prfl->get('email'),
                    'ttl' => (int) $modx->getOption('safedeal_merchant_ttl', null, 24),
                    'currency' => $modx->getOption('safedeal_merchant_currency', null, 'RUB'),
                    'merchant' => array(
                        'id' => $modx->getOption('safedeal_merchant_id'),
                        'name' => $modx->getOption('site_name'),
                        'url' => $modx->getOption('site_url'),
                    ),
                );
                $request = json_encode($requestParams);
                $curlOptions = array(
                    CURLOPT_URL => trim($modx->getOption('safedeal_merchant_host'), '/') . '/' . trim($modx->getOption('safedeal_merchant_invoice_url'), '/'),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Authorization: Token: ' . $apiKey
                    ],
                    CURLOPT_POSTFIELDS => $request,
                );

                $ch = curl_init();
                curl_setopt_array($ch, $curlOptions);

                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    $result = curl_error($ch);
                } else {
                    $result = json_decode($response, true);
                }
                $info = curl_getinfo($ch);
                curl_close($ch);

                if (in_array($info['http_code'], ['200', '201'])) {
                    $deal->set('payment_id', $result['id']);
                    $deal->set('updated', time());

                    if ($deal->save()) {
                        $result['status'] == 'STATUS_INIT';
                    } else {
                        $errors['deal'] = 'Can not save a deal!';
                    }
                } else {
                    $errors['merchant'] = 'Error in server response! Code response: ' . $info['http_code'];
                }
            }
        }
        /* Есть не оплаченный инвойс, оплатим */
        if (empty($errors)) {
            if (isset($result['status']) && $result['status'] == 'STATUS_INIT') {
                $paymentData = array(
                    'cardNumber' => $post_data['cc_num'],
                    'cardHolder' => $post_data['cc_holder'],
                    'expireMonth' => $post_data['cc_month'],
                    'expireYear' => $post_data['cc_year'],
                    'cvv' => $post_data['cc_cvc'],
                );
                $request = json_encode($paymentData);
                $curlOptions = array(
                    CURLOPT_URL => trim($modx->getOption('safedeal_merchant_host'), '/') . '/' . trim($modx->getOption('safedeal_merchant_payform_url'), '/') . '/' . $deal->get('payment_id'),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Authorization: Token: ' . $apiKey
                    ],
                    CURLOPT_POSTFIELDS => $request,
                );


                $ch = curl_init();
                curl_setopt_array($ch, $curlOptions);

                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    $result = curl_error($ch);
                } else {
                    $result = json_decode($response, true);
                }
                $info = curl_getinfo($ch);
                curl_close($ch);

                if (in_array($info['http_code'], ['200', '201'])) {
                    $data['location'] = $result['url'];
                    $notify = false;
                } else {
                    $errors['merchant'] = 'Error in server response! Code response: ' . $info['http_code'];
                }
            }
        }
        break;
    case 'deal/pay_fake':
        $post_data = [];
        //Проверяем поля формы
        foreach (['cc_num', 'cc_holder', 'cc_month', 'cc_year', 'cc_cvc'] as $field) { // обязательные поля
            if (empty($_POST[$field])) {
                $errors[$field] = 'Это поле не может быть пустым.';
            } else {
                switch ($field) {
                    case 'cc_num':
                        $cc_card = preg_replace("/[^0-9]/", '', $_POST[$field]);
                        if (strlen($cc_card) != 16) {
                            $errors[$field] = 'Не верный номер карты.';
                        }
                        $post_data[$field] = $cc_card;
                        break;
                    case 'cc_month':
                        $cc_month = preg_replace("/[^0-9]/", '', $_POST[$field]);
                        if ((int) $cc_month < 1 || (int) $cc_month > 12) {
                            $errors[$field] = 'Не верный месяц.';
                        }
                        $post_data[$field] = $cc_month;
                        break;
                    case 'cc_year':
                        $cc_year = preg_replace("/[^0-9]/", '', $_POST[$field]);
                        if ((int) $cc_year < date("y")) {
                            $errors[$field] = 'Не верный год.';
                        }
                        $post_data[$field] = $cc_year;
                        break;
                    case 'cc_cvc':
                        $cc_cvc = preg_replace("/[^0-9]/", '', $_POST[$field]);
                        if ((int) $cc_cvc < 0 || (int) $cc_cvc > 999) {
                            $errors[$field] = 'Не верный код.';
                        }
                        $post_data[$field] = $cc_cvc;
                        break;
                    case 'cc_holder':
                        $post_data[$field] = strip_tags($_POST[$field]);
                        if (preg_match("/[^a-zA-Z ]+/", $post_data[$field])) {
                            $errors[$field] = 'Укажите Имя и Фамилию только латинскими буквами!';
                        }
                }
            }
        }
        if ($user = $modx->getUser()) {
            $user_id = $user->id;
            $prfl = $user->getOne('Profile');
        } else {
            $errors['user_id'] = 'User ID not found!';
        }
        if ($deal = $modx->getObject('Deal', array('hash' => $scriptProperties['hash']))) {
        } else {
            $errors['deal'] = 'Deal not found!';
        }
        if ($deal->get('status') >= 2) {
            $errors['deal'] = 'Can not pay this deal!';
        }
        $result = array('status' => '');
        if (empty($errors)) {
            /* Сменим статус сделки для фейковой оплаты*/
            $deal->set('status', 3);
            $deal->set('paid_amount', $_POST['price']);
            $deal->set('updated', time());
            if ($deal->save()) {
                $notice = $modx->newObject('DealNotice');
                $notice->set('created', time());
                $notice->set('user_id', $deal->get('author_id'));
                $notice->set('is_customer', $deal->get('is_customer'));
                $notice->set('action', 7);
                $notice->set('deal_id', $deal->get('id'));
                $notice->set('hash', $deal->get('hash'));
                $notice->save();
                if ($deal->get('partner_id') > 0) {
                    $notice = $modx->newObject('DealNotice');
                    $notice->set('created', time());
                    $notice->set('user_id', $deal->get('partner_id'));
                    if ($deal->get('is_customer') == 1) {
                        $notice->set('is_customer', 0);
                    } else {
                        $notice->set('is_customer', 1);
                    }
                    $notice->set('action', 7);
                    $notice->set('deal_id', $deal->get('id'));
                    $notice->set('hash', $deal->get('hash'));
                    $notice->save();
                }
            } else {
                $errors['deal'] = 'Can not save a deal!';
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
            if ($deal = $modx->getObject('Deal', array('hash' => $scriptProperties['hash']))) {
                $deal->set('status', 5);
                $deal->set('updated', time());
                if ($deal->save()) {
                    $notice = $modx->newObject('DealNotice');
                    $notice->set('created', time());
                    $notice->set('user_id', $user_id);
                    $notice->set('is_customer', $deal->get('is_customer'));
                    $notice->set('action', 8);
                    $notice->set('deal_id', $deal->get('id'));
                    $notice->set('hash', $deal->get('hash'));
                    $notice->save();
                    if ($deal->get('partner_id') > 0) {
                        $notice = $modx->newObject('DealNotice');
                        $notice->set('created', time());
                        $notice->set('user_id', $deal->get('partner_id'));
                        if ($deal->get('is_customer') == 1) {
                            $notice->set('is_customer', 0);
                        } else {
                            $notice->set('is_customer', 1);
                        }
                        $notice->set('action', 8);
                        $notice->set('deal_id', $deal->get('id'));
                        $notice->set('hash', $deal->get('hash'));
                        $notice->save();
                    }
                } else {
                    $errors['deal'] = 'Can not save a deal!';
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
            if ($deal = $modx->getObject('Deal', array('hash' => $scriptProperties['hash']))) {
                $deal->set('status', 6);
                $deal->set('updated', time());
                if ($deal->save()) {
                    $notice = $modx->newObject('DealNotice');
                    $notice->set('created', time());
                    $notice->set('user_id', $user_id);
                    $notice->set('is_customer', $deal->get('is_customer'));
                    $notice->set('action', 9);
                    $notice->set('deal_id', $deal->get('id'));
                    $notice->set('hash', $deal->get('hash'));
                    $notice->save();
                    if ($deal->get('partner_id') > 0) {
                        $notice = $modx->newObject('DealNotice');
                        $notice->set('created', time());
                        $notice->set('user_id', $deal->get('partner_id'));
                        if ($deal->get('is_customer') == 1) {
                            $notice->set('is_customer', 0);
                        } else {
                            $notice->set('is_customer', 1);
                        }
                        $notice->set('action', 9);
                        $notice->set('deal_id', $deal->get('id'));
                        $notice->set('hash', $deal->get('hash'));
                        $notice->save();
                    }
                } else {
                    $errors['deal'] = 'Can not save a deal!';
                }
            } else {
                $errors['deal'] = 'Deal not found!';
            }
        }
        break;
    case 'notice/remove':
        $notify = false;
        if (empty($_POST['id']) || !(int) $_POST['id'] > 0) {
            $errors['notice'] = 'Notice ID not correct!';
        }
        if (!$notice = $modx->getObject('DealNotice', (int) $_POST['id'])) {
            $errors['notice'] = 'Notice ID not found!';
        }
        if (!$notice->remove()) {
            $errors['notice'] = 'Notice can not remove!';
        }
        break;
    default:
        $errors['default'] = 'Action undefined';
}


if (empty($errors)) {
    if ($notify) {
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
    }
    return $AjaxForm->success('', array_merge($data, array('result' => true, 'message' => $scriptProperties['successMsg'], 'modalID' => $scriptProperties['successModalID'], 'location' => $modx->makeUrl($redirectID))));
} else {
    return $AjaxForm->error('', array_merge($data, array('result' => false, 'message' => $scriptProperties['errorMsg'], 'modalID' => $scriptProperties['errorModalID'], 'errors' => $errors)));
}
