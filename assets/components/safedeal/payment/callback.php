<?php

define('MODX_API_MODE', true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/index.php');
$modx = new modX();
$modx->initialize('mgr');

$postData = file_get_contents('php://input');
$post = json_decode($postData, true);
if (
	isset($post['id']) &&
	isset($post['order_id']) &&
	isset($post['status']) &&
	isset($post['amount']) &&
	(int) $post['id'] > 0 &&
	(int) $post['order_id'] > 0 &&
	(float) $post['amount'] > 0
) {

	if ($SafeDeal = $modx->getService('SafeDeal', 'SafeDeal', MODX_CORE_PATH . 'components/safedeal/model/')) {
		$deal = $modx->getObject('Deal', array('id' => $post['order_id']));
		if ($deal &&
			$post['status'] == 'STATUS_PAID' &&
			(float) $post['amount'] == (($deal->get('price') + $deal->get('fee')) * 100)
		) {
			$deal->set('status', 3);
			$deal->set('paid_amount', $post['amount']);
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
			}
		}
	}
}
return true;