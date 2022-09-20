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
        <h3 class="h3">Сделка №{$id}: {$title}
            {if $author_id == $_modx->user.id && $status in [0, 1]}
                    <a href="#modalChangeDeal" class="btn btn-edit ml-2" data-toggle="modal"></a>
            {/if}
        </h3>
        {$description_html}
    </div>
    <div class="col-auto mb-4 mb-xl-0">
        {switch $status}
        {case 0, 1}
        <span class="badge badge-await">Ожидает подтверждения</span>
        {case 2}
        <span class="badge badge-await">Ожидает оплаты</span>
        {case 3}
        <span class="badge badge-inwork">В работе</span>
        {case 4}
        <span class="badge badge-await">Ожидает согласования продления</span>
        {case 5}
        <span class="badge badge-await">Ожидает согласия заказчика</span>
        {case 6}
        <span class="badge badge-closed">Завершена</span>
        {/switch}
    </div>
</div>
<div class="row">
    <div class="col">
        <p>Прикрепленные файлы:</p>
        <ul>
            {foreach $docs as $doc}
                <li><a href="{$doc.url}" target="_blank">{$doc.name_original}</a></li>
            {/foreach}
        </ul>
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
            <p class="text-muted text-medium mb-3">Комиссия ({$_modx->config.company_fee? :'5'} %)</p>
            <p class="mb-0">{$fee|number:2:'.':','} ₽</p>
        </div>
        <div class="col-6 col-xl-4 mb-4">
            <p class="text-muted text-medium mb-3">Итог</p>
            <p class="mb-0">{($price+$fee)|number:2:'.':','} ₽</p>
        </div>
        <div class="col-6 col-xl-4 py-2 mb-4{$customer_id == $_modx->user.id? ' bg-blue':''}">
            <p class="text-muted text-medium mb-3">{$customer_id == $_modx->user.id? 'Вы ':''}Покупатель</p>
            <p class="mb-0">{$customer_name}</p>
        </div>
        <div class="col-6 col-xl-4 py-2 mb-4{$executor_id == $_modx->user.id? ' bg-blue':''}">
            <p class="text-muted text-medium mb-3">{$executor_id == $_modx->user.id? 'Вы ':''}Продавец</p>
            <p class="mb-0">{$executor_name}</p>
        </div>
        <div class="col-6 col-xl-4 py-2 mb-4">
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
<div class="row align-items-center mb-4">
    <div class="col-12 col-xl-9">
        <input type="text" id="DealLink" class="form-control" value="{26|url:['scheme' => 'full']:['d' => $hash]}">
    </div>
    <div class="col-auto px-0 position-relative">
        <button type="button" class="btn btn-clipboard" data-clipboard-target="#DealLink" title="" data-original-title="Ссылка скопирована"></button>
    </div>
</div>
<div class="row flex-column-reverse flex-xl-row align-items-center justify-content-end mb-3 mb-xl-4">
    {switch $status}
        {case 0, 1}
        {* Создана / Ожидает предложения партнеру *}
        {* Предложена / Ожидает подтверждения партнера *}
            {if $author_id == $_modx->user.id}
                <div class="col-12 col-md-auto mb-3 mb-xl-0">
                    <a href="#modalChangeDeal" class="btn btn-link d-block d-md-inline-block"
                        data-toggle="modal">Редактировать сделку</a>
                </div>
                <div class="col-12 col-md-auto mb-3 mb-xl-0">
                    <a href="#modalCancelDeal" class="btn btn-link d-block d-md-inline-block"
                        data-toggle="modal">Отменить сделку</a>
                </div>
            {else}
                <div class="col-12 col-md-auto mb-3 mb-xl-0">
                    <a href="#modalCancelDeal" class="btn btn-link d-block d-md-inline-block"
                        data-toggle="modal">Отказаться от сделки</a>
                </div>
                <div class="col-12 col-md-auto mb-3 mb-xl-0">
                    <a href="#modalAcceptDeal" class="btn btn-primary" data-toggle="modal">Подтвердить сделку</a>
                </div>

            {/if}
        {case 2}
        {* Подтверждена партнером / Ожидает оплаты *}
            {if $customer_id == $_modx->user.id}
                <div class="col-12 col-md-auto mb-3 mb-xl-0">
                    <a href="#modalCancelDeal" class="btn btn-link d-block d-md-inline-block"
                        data-toggle="modal">Отменить сделку</a>
                </div>
                <div class="col-12 col-md-auto mb-3 mb-xl-0">
                    <a href="#modalPayDeal" class="btn btn-primary d-block d-md-inline-block" data-toggle="modal">Оплатить</a>
                </div>
            {/if}
        {case 3}
        {* Оплачена / В работе *}
            {if $executor_id == $_modx->user.id}
                <div class="col-12 col-md-auto mb-3 mb-xl-0">
                    <a href="#modalExtensionDeal" class="btn btn-link d-block d-md-inline-block"
                        data-toggle="modal">Продлить сделку</a>
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
            {/if}
        {case 4}
        {* Продление / Ожидает согласования *}
            {if $customer_id == $_modx->user.id}
                <div class="col-12 col-md-auto mb-3 mb-xl-0">
                    {'!AjaxForm'|snippet:[
                    'snippet' => 'SafeDeal',
                    'action' => 'deal/extension/cancel',
                    'hash' => $.get.d,
                    'form' => 'safedeal.deal.actions.form',
                    'btn_text' => 'Отменить продление сделки',
                    'btn_class' => 'btn btn-secondary',
                    'successModalID' => 'modalSuccessCancelExtensionDeal',
                    'dealResourceID' => 26,
                    'emailTPL' => 'safedeal.deal.extension.cancel.email',
                    'emailSubject' => $_modx->config.site_name~': Продление сделки отменено!',
                    ]}
                </div>
                <div class="col-12 col-md-auto mb-3 mb-xl-0">
                    {'!AjaxForm'|snippet:[
                    'snippet' => 'SafeDeal',
                    'action' => 'deal/extension/accept',
                    'hash' => $.get.d,
                    'form' => 'safedeal.deal.actions.form',
                    'btn_text' => 'Подтвердить продление сделки',
                    'successModalID' => 'modalAcceptExtensionDealSuccess',
                    'dealResourceID' => 26,
                    'emailTPL' => 'safedeal.deal.extension.accept.email',
                    'emailSubject' => $_modx->config.site_name~': Продление сделки подтверждено!',
                    ]}
                </div>
            {/if}
        {case 5}
        {* Завершена / Ожидает согласия заказчика *}
            {if $customer_id == $_modx->user.id}
                <div class="col-12 col-md-auto mb-3 mb-xl-0">
                    {'!AjaxForm'|snippet:[
                    'snippet' => 'SafeDeal',
                    'action' => 'deal/close',
                    'hash' => $.get.d,
                    'form' => 'safedeal.deal.actions.form',
                    'btn_text' => 'Закрыть сделку',
                    'successModalID' => 'modalSuccessCompleteDeal',
                    'errorModalID' => 'modalErrorCompleteDeal',
                    'dealResourceID' => 26,
                    'emailTPL' => 'safedeal.deal.complete.email',
                    'emailSubject' => $_modx->config.site_name~': Сделка завершена!',
                    ]}
                </div>
            {/if}
        {case 6}
        {* Закрыта / Ожидает выплаты исполнителю / Архив *}
        {if $executor_id == $_modx->user.id && $funds_withdrawn == 0}
            <div class="col-12 col-md-auto mb-3 mb-xl-0">
                <a href="#modalWithdrawFundsDeal" class="btn btn-primary" data-toggle="modal">Вывести средства</a>
            </div>
        {/if}
    {/switch}
</div>