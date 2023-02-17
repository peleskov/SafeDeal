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
                        $executor_phone = $executor_prfl->get('fullname');
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
                $archive = [];
                if ($arc = $modx->getCollection('DealArchive', array('deal_id' => $deal->get('id')))) {
                    foreach ($arc as $a) {
                        $archive[] = $a->get('user_id');
                    }
                }
                $item = array_merge($deal->toArray(), array(
                    'customer_id' => $customer_id,
                    'executor_id' => $executor_id,
                    'customer_name' => $customer_name,
                    'executor_name' => $executor_name,
                    'description_html' => $description_html,
                    'docs' => json_decode($deal->get('docs'), true),
                    'archive' => $archive,
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
        $where = json_decode($modx->getOption('where', $scriptProperties, '{}'), true);
        $where[] =
            array(
                array(
                    'author_id' => $user->id,
                ),
                array(
                    'OR:partner_id:=' => $user->id,
                )
            );

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
        $q->select('`Deal`.*, `DealArchive`.*');
        if($scriptProperties['archive'] == 1) {
            $q->leftJoin('DealArchive','DealArchive', '`Deal`.`id` = `DealArchive`.`deal_id` AND `DealArchive`.`user_id` = ' . $user->id);
            $where['DealArchive.user_id:IS NOT'] = null;
        } else {
            $q->leftJoin('DealArchive','DealArchive', '`Deal`.`id` = `DealArchive`.`deal_id` AND `DealArchive`.`user_id` = ' . $user->id);
            $where['DealArchive.user_id:IS'] = null;
        }
        $q->where($where);
        $query = $modx->getOption('query', $scriptProperties, '');
        if ($query != '') {
            $query_sanitize = $modx->sanitizeString($query);
            $search = array();
            $search[] = 'MATCH(`Deal`.`title`) AGAINST ("' . $query_sanitize . '" IN BOOLEAN MODE)';
            foreach (explode(' ', $query_sanitize) as $s) {
                if (mb_strlen($s) > 3) {
                    $search[] = '`Deal`.`title` LIKE "%' . $s . '%"';
                    $search[] = '`Deal`.`description` LIKE "%' . $s . '%"';
                }
            }
        }        
        if (!empty($search)) {
            $q->where(array($search), xPDOQuery::SQL_OR);
        }
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
        if (isset($returnIDs)) {
            $output = [];
            foreach ($deals as $k => $deal) {
                array_push($output, $deal->get('id'));
            }
        } elseif (isset($returnAdvertIDs)) {
            $output = [];
            foreach ($deals as $k => $deal) {
                array_push($output, $deal->get('advert_id'));
            }
        } else {
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
                    'advert_id' => $deal->get('advert_id'),
                    'extended' => $deal->get('extended'),
                    'updated' => $deal->get('updated'),
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
            }
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