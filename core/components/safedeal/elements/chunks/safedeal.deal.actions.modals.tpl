{* Изменение сделки *}
<div class="modal fade" id="modalChangeDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Изменить сделку</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-4">
                    <label>Название сделки</label>
                    <input type="text" class="form-control mb-1" name="title" placeholder="Введите названние сделки"
                        value="{$title}" form="formChangeDeal">
                </div>
                <div class="form-group mb-4">
                    <label>Описание</label>
                    <textarea class="form-control" name="description" placeholder="Описание сделки"
                        rows="4" form="formChangeDeal">{$description}</textarea>
                </div>
                <div class="form-group mb-4">
                    <label>Загрузите документы <span class="d-block text-medium text-small">Вы можете прикрепить не больше 5 файлов, размер каждого файла не должен превышать 1 Мб, допускаются следующие типы фалов: pdf, txt, doc, docx, xls, xlsx</span></label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input js-input-docs" id="inputDocs" name="docs[]" multiple="" form="formChangeDeal"> 
                        <label class="custom-file-label" for="inputDocs" >Выбрать файлы</label>
                    </div>
                    <ul class="nav flex-column js-docs text-muted text-small"></ul>
                </div>
                {if $docs|iterable}
                <div class="form-group mb-4">
                    <label>Прикрепленные файлы:</label>
                    {set $doc_ids = []}
                    <ul class="nav">
                        {foreach $docs as $k => $doc}
                            {set $doc_ids[] = $k}
                            <li class="border-bottom mb-1">
                                <a href="{$doc.url}" target="_blank">{$doc.name_original}</a>
                                <button type="button" class="btn btn-trash p-3 js-remove-doc" data-docid="{$k}"></button>
                            </li>
                        {/foreach}
                    </ul>
                    <input type="hidden" name="doc_ids" value="{$doc_ids|join}" form="formChangeDeal">
                </div>
                {/if}
                <div class="col-6 px-0 form-group mb-2">
                    <label>Сумма сделки</label>
                    <div class="position-relative">
                        <input class="form-control transfer-amount js-dealprice" name="price" type="text"
                            data-fee="{$_modx->config.company_fee? :'0.05'}" placeholder="Введите сумму"
                            data-inputmask="'alias': 'currency', 'placeholder': '0.00'" value="{$price}" form="formChangeDeal">
                        <span class="ruble">₽</span>
                    </div>
                </div>
                <p class="text-muted text-medium mb-4">Общая комиссия составит: <span class="js-dealfee">{$fee}</span>
                    ₽</p>
                <div class="col-6 px-0 form-group mb-4">
                    <label>Срок сделки</label>
                    <input type="text" class="w-100 datepicker today-min" name="deadline" placeholder="30/10/2022"
                        value="{$deadline|date_format : '%d/%m/%Y'}" form="formChangeDeal">
                </div>
                <div class="fomr-group d-flex justify-content-end mb-4">
                    <a href="#" class="btn btn-secondary mr-3" data-dismiss="modal">Отмена</a>
                    {'!AjaxForm'|snippet:[
                    'snippet' => 'SafeDeal',
                    'action' => 'deal/change',
                    'hash' => $.get.d,
                    'form' => 'safedeal.deal.actions.form',
                    'form_id' => 'formChangeDeal',
                    'enctype' => 'multipart/form-data',
                    'docsDirPath' => $_modx->config.assets_path~'docs/usr_'~$_modx->user.id,
                    'btn_text' => 'Изменить сделку',
                    'successModalID' => 'modalChangeDealSuccess',
                    'errorModalID' => 'modalChangeDealError',
                    'dealResourceID' => 26,
                    'emailTPL' => 'safedeal.deal.change.email',
                    'emailSubject' => $_modx->config.site_name~': Сделка изменена!',
                    ]}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade js-hidden-refresh" id="modalChangeDealSuccess" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Сделка успешно изменена!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/deal_success.svg" alt="">
            </div>
        </div>
    </div>
</div>
<div class="modal fade js-hidden-refresh" id="modalChangeDealError" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Сделка не может быть изменена!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/mail_sent.svg" alt="">
            </div>
        </div>
    </div>
</div>
{* Изменение сделки *}
{* Отмена сделки *}
<div class="modal fade is-authorized" id="modalCancelDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Отменить сделку</h5>
                <p class="text-left">Сделка будет отменена и перенесена в Архив</p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="fomr-group d-flex justify-content-end mb-4">
                    <a href="#" class="btn btn-secondary mr-3" data-dismiss="modal">Отмена</a>
                    {'!AjaxForm'|snippet:[
                    'snippet' => 'SafeDeal',
                    'action' => 'deal/cancel',
                    'hash' => $.get.d,
                    'form' => 'safedeal.deal.actions.form',
                    'btn_text' => 'Оменить сделку',
                    'successModalID' => 'modalCancelSuccess',
                    'dealResourceID' => 26,
                    'emailTPL' => 'safedeal.deal.cancel.email',
                    'emailSubject' => $_modx->config.site_name~': Сделка отменена!',
                    ]}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade js-hidden-refresh" id="modalCancelSuccess" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">{$status == 0? 'Сделка успешно отменена!':'Заявка на отмену сделки успешно
                    подана!'}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/cancel_success.svg" alt="">
            </div>
        </div>
    </div>
</div>
{* Отмена сделки *}
{* Подтвердить сделку *}
<div class="modal fade" id="modalAcceptDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Подтвердить сделку!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-body" id="modalAcceptDealBody">
                    <p class="text-left">Сделка ожидает Вашего подтверждения.</p>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" id="RadioAgreeAccept1" class="js-iagreecheck custom-control-input"
                                checked="" data-form="#formAcceptDeal" data-parent="#modalAcceptDealBody">
                            <label class="custom-control-label" for="RadioAgreeAccept1">Я согласен с условиями <a
                                    class="btn btn-link d-inline" href="pravovaya-informacziya.html">пользовательского
                                    соглашения</a></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" id="RadioAgreeAccept2" class="js-iagreecheck custom-control-input"
                                checked="" data-form="#formAcceptDeal" data-parent="#modalAcceptDealBody">
                            <label class="custom-control-label" for="RadioAgreeAccept2">Принимаю условия и срок сделки</label>
                        </div>
                    </div>
                </div>
                <div class="fomr-group d-flex justify-content-end mb-4">
                    <a href="#" class="btn btn-secondary mr-3" data-dismiss="modal">Отмена</a>
                    {'!AjaxForm'|snippet:[
                    'snippet' => 'SafeDeal',
                    'action' => 'deal/accept',
                    'hash' => $.get.d,
                    'form' => 'safedeal.deal.actions.form',
                    'form_id' => 'formAcceptDeal',
                    'btn_text' => 'Подтвердить сделку',
                    'successModalID' => 'modalAcceptDealSuccess',
                    'dealResourceID' => 26,
                    'emailTPL' => 'safedeal.deal.confirm.email',
                    'emailSubject' => $_modx->config.site_name~': Сделка подтверждена!',
                    ]}
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div class="modal fade js-hidden-refresh" id="modalAcceptDealSuccess" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Сделка успешно подтверждена!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/deal_success.svg" alt="">
            </div>
        </div>
    </div>
</div>
{* Подтвердить сделку *}
{* Оплатить сделку *}
<div class="modal fade is-authorized" id="modalPayDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Оплатить сделку</h5>
                <p class="text-left">Вы ознакомились с условиями и сроками исполнения сделки, согласны с ними и хотите
                    оплатить.</p>
                <p class="text-left">Информация о сделке представлена ниже</p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
                <hr>
            </div>
            <div class="modal-body">
                <div class="d-flex">
                    <div class="col-12 px-0 form-group mb-3">
                        <p class="mb-0"><b>{$title}</b></p>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="col-6 px-0 form-group mb-0">
                        <label>Сумма сделки</label>
                        <p>{($price+$fee)|number:2:'.':','} ₽</p>
                    </div>
                    <div class="col-6 px-0 form-group mb-0">
                        <label>Срок сделки</label>
                        <p>{$deadline|date_format : '%d.%m.%Y'}</p>
                    </div>
                </div>
                <div class="d-flex">
                    <div class="col-6 px-0 form-group mb-0">
                        <label>Вы Заказчик</label>
                        <p>{$customer_name}</p>
                    </div>
                    <div class="col-6 px-0 form-group mb-0">
                        <label>Исполнитель</label>
                        <p>{$executor_name}</p>
                    </div>
                </div>
                <div class="fomr-group d-flex justify-content-end mb-4">
                    <a href="#" class="btn btn-secondary mr-3" data-dismiss="modal">Отмена</a>
                    {'!AjaxForm'|snippet:[
                    'snippet' => 'SafeDeal',
                    'action' => 'deal/pay',
                    'hash' => $.get.d,
                    'form' => 'safedeal.deal.actions.form',
                    'btn_text' => 'Оплата (тест)',
                    'successModalID' => 'modalSuccessPayDeal',
                    'errorModalID' => 'modalErrorPayDeal',
                    'dealResourceID' => 26,
                    'emailTPL' => 'safedeal.deal.pay.email',
                    'emailSubject' => $_modx->config.site_name~': Сделка оплачена!',
                    ]}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade js-hidden-refresh" id="modalSuccessPayDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Ваша сделка успешно оплачена!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/mail_sent.svg" alt="">
            </div>
        </div>
    </div>
</div>
<div class="modal fade js-hidden-refresh" id="modalErrorPayDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">В данный момент Вы не можете оплатить эту сделку!</h5>
                <p class="mb-0">Обновите страницу и попробуйте еще раз.</p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/mail_sent.svg" alt="">
            </div>
        </div>
    </div>
</div>
{* Оплатить сделку *}
{* Продлить сделку *}
<div class="modal fade is-authorized" id="modalExtensionDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Продлить срок сделки</h5>
                <p class="text-left">Вы подаете заявку на продление срока сделки</p>
                <p class="text-left">Введите изменения в поля ниже:</p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column align-items-center">
                    <div class="col-6 px-0 form-group mb-4">
                        <label>Срок сделки</label>
                        <input type="text" class="w-100 datepicker today-min" placeholder="30/10/2022" name="deadline"
                            form="dealExtensionForm" value="{$deadline|date_format : '%d/%m/%Y'}" >
                    </div>
                </div>
                <p>В случае согласия Вашего партнера - срок сделки будет изменен<br>
                    В случае несогласия - срок сделки останется прежним.</p>
                <div class="fomr-group d-flex justify-content-end mb-4">
                    <a href="#" class="btn btn-secondary mr-3" data-dismiss="modal">Отмена</a>
                    {'!AjaxForm'|snippet:[
                    'snippet' => 'SafeDeal',
                    'action' => 'deal/extension/request',
                    'hash' => $.get.d,
                    'form' => 'safedeal.deal.actions.form',
                    'form_id' => 'dealExtensionForm',
                    'btn_text' => 'Изменить срок сделки',
                    'successModalID' => 'modalSuccesExtensionDeal',
                    'errorModalID' => 'modalErrorExtensionDeal',
                    'dealResourceID' => 26,
                    'emailTPL' => 'safedeal.deal.extension.email',
                    'emailSubject' => $_modx->config.site_name~': Изменение срока сделки!',
                    ]}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade js-hidden-refresh" id="modalSuccesExtensionDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Заявка на продление срока сделки успешно подана!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/mail_sent.svg" alt="">
            </div>
        </div>
    </div>
</div>
<div class="modal fade js-hidden-refresh" id="modalErrorExtensionDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Заявка на продление срока сделки не может быть подана!</h5>
                <p class="mb-0">Обновите страницу и попробуйте еще раз.</p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/mail_sent.svg" alt="">
            </div>
        </div>
    </div>
</div>
{* Продлить сделку *}
{* Подтвердить продление сделки *}
<div class="modal fade js-hidden-refresh" id="modalAcceptExtensionDealSuccess" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Продление сделки успешно подтверждено!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/deal_success.svg" alt="">
            </div>
        </div>
    </div>
</div>
<div class="modal fade js-hidden-refresh" id="modalSuccessCancelExtensionDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Продление сделки успешно отклонено!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/deal_success.svg" alt="">
            </div>
        </div>
    </div>
</div>
{* Подтвердить продление сделки *}
{* Завершение сделки *}
<div class="modal fade js-hidden-refresh" id="modalSuccessCompleteDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Сделка успешно завершена!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/dispute_success.svg" alt="">
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalErrorCompleteDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">В данный момент Вы не можете завершить эту сделку!</h5>
                <p class="mb-0">Обновите страницу и попробуйте еще раз.</p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/mail_sent.svg" alt="">
            </div>
        </div>
    </div>
</div>
{* Завершение сделки *}
{* Выовд средств *}
<div class="modal fade" id="modalWithdrawFundsDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Сделка успешно завершена!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <button type="button" class="btn btn-primary" data-dismiss="modal" aria-label="Close">Получить деньги</button>
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/dispute_success.svg" alt="">
            </div>
        </div>
    </div>
</div>
{* Выовд средств *}
