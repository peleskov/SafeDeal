<div class="row d-none d-xl-flex mb-4">
    <div class="col">
        <ul class="nav breadcrumbs">
            <li class="nav-item mr-2"><a href="{20|url}" class="nav-link">Мои сделки</a></li>
            <li class="nav-item"><span class="nav-link">{$title}</span></li>
        </ul>
    </div>
</div>
<div class="row flex-column flex-xl-row  justify-content-between mb-xl-4">
    <div class="col-12 col-xl-9 order-1 order-xl-0">
        <h3 class="h3">Сделка: {$title}
            {if $author_id == $_modx->user.id}
            {if $status in [0, 1, 2]}
            <a href="#modalChangeTermsDeal" class="btn btn-edit ml-2" data-toggle="modal"></a>
            {/if}
            {else}
            {if $status in [1, 2]}
            <a href="#modalChangeTermsDeal" class="btn btn-edit ml-2" data-toggle="modal"></a>
            {/if}
            {/if}
        </h3>
        {$description}
    </div>
    <div class="col-auto mb-4 mb-xl-0">
        {switch $status}
        {case 0}
        <span class="badge badge-await">Ожидает согласования</span>
        {case 1}
        <span class="badge badge-await">Ожидает оплаты</span>
        {case 2}
        <span class="badge badge-inwork">В работе</span>
        {case 3}
        <span class="badge badge-closed">Ожидает согласования отмены</span>
        {case 4}
        <span class="badge badge-closed">Ожидает согласования изменений</span>
        {case 5}
        <span class="badge badge-dispute">Открыт спор</span>
        {case 6}
        <span class="badge badge-closed">Завершена</span>
        {/switch}
    </div>
</div>
<div class="row">
    <div class="col-12 col-xl-9 d-flex flex-wrap px-0">
        <div class="col-6 col-xl-4 mb-4">
            <p class="text-muted text-medium mb-3">Сумма сделки</p>
            {if $status == 4 && $tmp_price > 0}
            <p class="mb-0">{$tmp_price|number:2:'.':','} ₽ <span
                    class="text-medium text-crossed text-muted mr-3">{$price|number:2:'.':','} ₽</span></p>
            {else}
            <p class="mb-0">{$price|number:2:'.':','} р</p>
            {/if}
        </div>
        <div class="col-6 col-xl-4 mb-4">
            <p class="text-muted text-medium mb-3">Комиссия (
                {switch $fee_payer}
                {case 0}
                Платит заказчик
                {case 1}
                Платит исполнитель
                {case 2}
                50/50
                {/switch}
                )
            </p>
            <p class="mb-0">
                {if $status == 4 && $tmp_fee > 0}
                {$tmp_fee|number:2:'.':','} ₽
                <span class="text-medium text-crossed text-muted ml-3">{$fee|number:2:'.':','} ₽</span>
                {else}
                {$fee|number:2:'.':','} ₽
                {/if}
            </p>
        </div>
        <div class="col-6 col-xl-4 mb-4">
            <p class="text-muted text-medium mb-3">Итог</p>
            <p class="mb-0">
                {if $status == 4 && $tmp_price > 0}
                {($tmp_price+$tmp_fee)|number:2:'.':','} ₽
                <span class="text-medium text-crossed text-muted ml-3">{($price+$fee)|number:2:'.':','} ₽</span>
                {else}
                {($price+$fee)|number:2:'.':','} ₽
                {/if}
            </p>
        </div>
        <div class="col-6 col-xl-4 mb-4">
            <p class="text-muted text-medium mb-3">Заказчик</p>
            <p class="mb-0">{$customer_name}</p>
        </div>
        <div class="col-6 col-xl-4 mb-4">
            <p class="text-muted text-medium mb-3">Исполнитель</p>
            <p class="mb-0">{$partner_name}</p>
        </div>
        <div class="col-6 col-xl-4 mb-4">
            <p class="text-muted text-medium mb-3">Дата завершения</p>
            <p class="mb-0">
            {if $status == 4 && $tmp_deadline > 0}
            {$tmp_deadline|date_format : '%d.%m.%Y'}
            <span class="text-medium text-crossed text-muted ml-3">{$deadline|date_format : '%d.%m.%Y'}</span>
            {else}
            {$deadline|date_format : '%d.%m.%Y'}
            {/if}
            </p>
        </div>
    </div>
</div>
<div class="row flex-column-reverse flex-xl-row align-items-center justify-content-end mb-3 mb-xl-4">
    {switch $status}
    {case 0}
    {* Ожидает согласования / Новая сделка *}
    <div class="col-12 col-md-auto mb-3 mb-xl-0">
        <a href="#modalCancelDeal" class="btn btn-link d-block d-md-inline-block" class="btn btn-link"
            data-toggle="modal">Отменить сделку</a>
    </div>
    {if $author_id == $_modx->user.id}
    <div class="col-12 col-md-auto mb-3 mb-xl-0">
        <a href="#modalChangeTermsDeal" class="btn btn-link d-block d-md-inline-block" class="btn btn-link"
            data-toggle="modal">Изменить сделку</a>
    </div>
    {/if}
    {if $author_id != $_modx->user.id}
    <div class="col-12 col-md-auto mb-3 mb-xl-0">
        {'!AjaxForm'|snippet:[
        'snippet' => 'SafeDeal',
        'action' => 'deal/accept',
        'hash' => $.get.d,
        'status' => $status,
        'form' => 'safedeal.deal.actions.form',
        'btn_text' => 'Подтвердить сделку',
        'successModalID' => 'modalAcceptDealSuccess',
        'dealResourceID' => 26,
        'emailTPL' => 'safedeal.deal.confirm.email',
        'emailSubject' => $_modx->config.site_name~': Сделка подтверждена!',
        ]}
    </div>
    {/if}
    {case 1}
    {* Согласована / Не оплачена *}
    <div class="col-12 col-md-auto mb-3 mb-xl-0">
        <a href="#modalChangeTermsDeal" class="btn btn-link d-block d-md-inline-block" class="btn btn-link"
            data-toggle="modal">Изменить сделку</a>
    </div>
    <div class="col-12 col-md-auto mb-3 mb-xl-0">
        <a href="#modalCancelDeal" class="btn btn-link d-block d-md-inline-block" class="btn btn-link"
            data-toggle="modal">Отменить сделку</a>
    </div>
    {if $customer_id == $_modx->user.id}
    <div class="col-12 col-md-auto mb-3 mb-xl-0">
        <a href="#modalPayDeal" class="btn btn-primary d-block d-md-inline-block" data-toggle="modal">Оплатить</a>
    </div>
    {/if}

    {case 2}
    {* Оплачена / В работе *}
    <div class="col-12 col-md-auto mb-3 mb-xl-0">
        <a href="#modalChangeTermsDeal" class="btn btn-link d-block d-md-inline-block" class="btn btn-link"
            data-toggle="modal">Изменить сделку</a>
    </div>
    <div class="col-12 col-md-auto mb-3 mb-xl-0">
        <a href="#modalCancelDeal" class="btn btn-link d-block d-md-inline-block" class="btn btn-link"
            data-toggle="modal">Отменить сделку</a>
    </div>
    <div class="col-12 col-md-auto mb-3 mb-xl-0">
        <a href="#modalDisputeDeal" class="btn btn-link d-block d-md-inline-block" data-toggle="modal">Оспорить
            сделку</a>
    </div>
    <div class="col-12 col-md-auto mb-3 mb-xl-0">
        {'!AjaxForm'|snippet:[
        'snippet' => 'SafeDeal',
        'action' => 'deal/complete',
        'hash' => $.get.d,
        'form' => 'safedeal.deal.actions.form',
        'btn_text' => 'Завершить сделку',
        'successModalID' => 'modalSuccessCompleteDeal',
        'errorModalID' => 'modalErrorCompleteDeal',
        'dealResourceID' => 26,
        'emailTPL' => 'safedeal.deal.complete.email',
        'emailSubject' => $_modx->config.site_name~': Сделка завершена!',
        ]}
    </div>
    {case 3, 4}
    {* Ожидает согласования отмены / Ожидает согласования изменений *}
    <div class="col-12 col-md-auto mb-3 mb-xl-0">
        <a href="#modalDisputeDeal" class="btn btn-link d-block d-md-inline-block" data-toggle="modal">Оспорить
            сделку</a>
    </div>
    {if $initiator_id != $_modx->user.id}
    <div class="col-12 col-md-auto mb-3 mb-xl-0">
        {'!AjaxForm'|snippet:[
        'snippet' => 'SafeDeal',
        'action' => 'deal/accept',
        'hash' => $.get.d,
        'form' => 'safedeal.deal.actions.form',
        'btn_text' => 'Подтвердить сделку',
        'successModalID' => 'modalAcceptDealSuccess',
        'dealResourceID' => 26,
        'emailTPL' => 'safedeal.deal.confirm.email',
        'emailSubject' => $_modx->config.site_name~': Сделка подтверждена!',
        ]}
    </div>
    {/if}
    {case 5}
    {* Открыт спор *}
    {case 6}
    {* Завершена *}
    {/switch}
</div>