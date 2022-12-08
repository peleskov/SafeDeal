<?php
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

if (!isset($outputSeparator)) {
    $outputSeparator = "\n";
}

switch ($action) {
    case 'deal/details':
        if ($hash) {
            if ($deal = $modx->getObject('Deal', array('hash' => $hash))) {
                $description_html = '';
                if ($dscrp = $deal->get('description')) {
                    foreach (explode("\n", $dscrp) as $str) {
                        $d[] = '<p>' . $str . '</p>';
                    }
                    $description_html = implode(' ', $d);
                }
                $customer_name = $scriptProperties['userNotFoundMsg'];
                $partner_name = $scriptProperties['userNotFoundMsg'];
                if ($deal->get('is_customer') == 1) {
                    $customer_id = $deal->get('author_id');
                    if ($customer = $modx->getObject('modUser', $customer_id)) {
                        $customer_prfl = $customer->getOne('Profile');
                        $customer_name = $customer_prfl->get('fullname');
                    }

                    $executor_id = $deal->get('partner_id');
                    if ($executor = $modx->getObject('modUser', $executor_id)) {
                        $executor_prfl = $executor->getOne('Profile');
                        $executor_name = $executor_prfl->get('fullname');
                    }
                } else {
                    $executor_id = $deal->get('author_id');
                    if ($executor = $modx->getObject('modUser', $executor_id)) {
                        $executor_prfl = $executor->getOne('Profile');
                        $executor_name = $executor_prfl->get('fullname');
                    }

                    $customer_id = $deal->get('partner_id');
                    if ($customer = $modx->getObject('modUser', $customer_id)) {
                        $customer_prfl = $customer->getOne('Profile');
                        $customer_name = $customer_prfl->get('fullname');
                    }
                }

                $item = array_merge(array(
                    'id' => $deal->get('id'),
                    'author_id' => $deal->get('author_id'),
                    'customer_id' => $customer_id,
                    'executor_id' => $executor_id,
                    'customer_name' => $customer_name,
                    'executor_name' => $executor_name,
                    'title' => $deal->get('title'),
                    'description' => $deal->get('description'),
                    'description_html' => $description_html,
                    'status' => $deal->get('status'),
                    'price' => $deal->get('price'),
                    'fee' => $deal->get('fee'),
                    'funds_withdrawn' => $deal->get('funds_withdrawn'),
                    'deadline' => $deal->get('deadline'),
                    'docs' => json_decode($deal->get('docs'), true),
                    'tmp_deadline' => $deal->get('tmp_deadline'),
                ), $scriptProperties);
                $items[] = empty($tpl)
                    ? $pdoFetch->getChunk('', $item)
                    : $pdoFetch->getChunk($tpl, $item);
                $output = array_merge(array('wrapper' => implode($outputSeparator, $items)), $scriptProperties);
                $output = empty($tplOut)
                    ? $pdoFetch->getChunk('', $items)
                    : $pdoFetch->getChunk($tplOut, $output);
                return $output;
            }
        }
        $empty = empty($tplEmpty)
            ? '<pre>Result empty!</pre>'
            : $pdoFetch->getChunk($tplEmpty);
        $output = empty($tplOut)
            ? $pdoFetch->getChunk('', $empty)
            : $pdoFetch->getChunk($tplOut, array('wrapper' => $empty));
        return $output;
        break;
    case 'deal/list':
        $user = $modx->getUser();
        $where = array(
            array(
                array(
                    'author_id' => $user->id,
                ),
                array(
                    'OR:partner_id:=' => $user->id,
                )
            )
        );

        if ($scriptProperties['archive'] == 1) {
            $where[] = array('status' => '6');
        } elseif ($scriptProperties['archive'] == '-1') {
            $where[] = array('status:!=' => '6');
        } elseif (!empty($stts)) {
            $where[] = array('status:IN' => explode(',', $stts));
        }
        if (!empty($date)) {
            $deadline =  DateTime::createFromFormat('d/m/Y H:i:s', $date . ' 00:00:00');
            $where['deadline'] = $deadline->getTimestamp();
        }
        if (!empty($min)) {
            $where['price:>='] = $min;
        }
        if (!empty($max)) {
            $where['price:<='] = $max;
        }
        $q = $modx->newQuery('Deal');
        $q->where($where);
        $total = $modx->getCount('Deal', $q);
        $totalVar = $modx->getOption('totalVar', $scriptProperties, 'total');
        $modx->setPlaceholder($totalVar, $total);
        $limit = $modx->getOption('limit', $scriptProperties, 1);
        $offset = $modx->getOption('offset', $scriptProperties, 0);

        $sortby = $modx->getOption('sortby', $scriptProperties, 'created');
        $sortdir = $modx->getOption('sortdir', $scriptProperties, 'DESC');
        $q->sortby('`Deal`.`' . $sortby . '`', $sortdir);

        $q->limit($limit, $offset);
        $q->prepare();
        //$modx->log(1, $q->toSQL());
        $deals = $modx->getCollection('Deal', $q);

        $items = array();
        $idx = 0;
        $prices = [];
        foreach ($deals as $k => $deal) {
            $idx += 1;
            $prices[] = $deal->get('price');
            if ($deal->get('is_customer') == 1) {
                $customer_id = $deal->get('author_id');
            } else {
                $customer_id = $deal->get('partner_id');
            }

            $item = array_merge(array(
                'id' => $deal->get('id'),
                'idx' => $idx,
                'title' => $deal->get('title'),
                'price' => $deal->get('price'),
                'deadline' => $deal->get('deadline'),
                'status' => $deal->get('status'),
                'customer_id' => $customer_id,
                'hash' => $deal->get('hash'),
            ), $scriptProperties);
            $items[] = empty($tpl)
                ? '<pre>' . $pdoFetch->getChunk('', $item) . '</pre>'
                : $pdoFetch->getChunk($tpl, $item);
        }
        if (count($items) > 0) {
            $output = array_merge(array('wrapper' => implode($outputSeparator, $items), 'range_price' => '[ ' . min($prices) . ',' . max($prices) . ' ]'), $scriptProperties);
            $output = empty($tplOut)
                ? $pdoFetch->getChunk('', $items)
                : $pdoFetch->getChunk($tplOut, $output);
        } else {
            $empty = empty($tplEmpty)
                ? '<p>Result empty!</p>'
                : $pdoFetch->getChunk($tplEmpty);
            $output = empty($tplOut)
                ? $pdoFetch->getChunk('', $empty)
                : $pdoFetch->getChunk($tplOut, array('wrapper' => $empty));
        }
        return $output;

        break;
    case 'deal/notices':
        /* Пример сниппета
                {'!getSafedeals'|snippet:[
                    'action' => 'deal/notices',
                    'limit' => 100,
                    'where' => '{"user_id":2,"active":1}'
                ]|print}
            */
        $user = $modx->getUser();
        $where = $modx->fromJSON($modx->getOption('where', $scriptProperties, ''));
        $limit = $modx->getOption('limit', $scriptProperties, 1000);


        $q = $modx->newQuery('DealNotice');
        $q->where($where);
        $q->limit($limit);
        $q->prepare();
        $notices = $modx->getCollection('DealNotice', $q);
        $out = [];
        foreach ($notices as $k => $notice) {
            $out[] = $notice->toArray();
        }
        return $out;

        break;
}
return false;
