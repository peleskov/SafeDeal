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

<div class="modal fade is-authorized" id="modalCancelDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Отменить сделку</h5>
                <p class="text-left">Вы подаете заявку на отмену сделки.
                    {$status != 0? '<br>В случае согласии Вашего партнера - сделка будет отменена.<br>В случае
                    несогласии - будет открыт спор.':''}
                </p>
                <p class="text-left">Вы уверены, что хотите подтвердить отмену сделки?</p>
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

<div class="modal fade is-authorized" id="modalChangeTermsDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Изменить условия сделки</h5>
                <p class="text-left">Вы подаете заявку на изменение условий сделки</p>
                <p class="text-left">Введите изменения в поля ниже:</p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column align-items-center">
                    <div class="col-6 px-0 form-group mb-4">
                        <label>Сумма сделки</label>
                        <div class="position-relative">
                            <input class="form-control transfer-amount" type="text" placeholder="Введите сумму"
                                form="dealChangeForm" name="price" value="{$price|number:2:'.':','}"
                                data-inputmask="'alias': 'currency', 'placeholder': '0.00'">
                            <span class="ruble">₽</span>
                        </div>
                    </div>
                    <div class="col-6 px-0 form-group mb-4">
                        <label>Срок сделки</label>
                        <input type="text" class="w-100 datepicker today-min" placeholder="30/10/2022" name="deadline"
                            form="dealChangeForm" value="{$deadline|date_format : '%d/%m/%Y'}">
                    </div>
                </div>
                <p>В случае согласии Вашего партнера - условия сделки будут изменены<br>
                    В случае несогласия - условия сделки не изменятся.</p>
                <div class="fomr-group d-flex justify-content-end mb-4">
                    <a href="#" class="btn btn-secondary mr-3" data-dismiss="modal">Отмена</a>
                    {'!AjaxForm'|snippet:[
                    'snippet' => 'SafeDeal',
                    'action' => 'deal/change',
                    'hash' => $.get.d,
                    'form' => 'safedeal.deal.actions.form',
                    'form_id' => 'dealChangeForm',
                    'btn_text' => 'Изменить сделку',
                    'successModalID' => 'modalSuccessChangeTermsDeal',
                    'errorModalID' => 'modalErrorChangeTermsDeal',
                    ]}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade js-hidden-refresh" id="modalSuccessChangeTermsDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Заявка на изменение условий сделки успешно подана!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/mail_sent.svg" alt="">
            </div>
        </div>
    </div>
</div>
<div class="modal fade js-hidden-refresh" id="modalErrorChangeTermsDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Заявка на изменение условий сделки не может быть подана!</h5>
                <p class="mb-0">Обновите страницу и попробуйте еще раз.</p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/mail_sent.svg" alt="">
            </div>
        </div>
    </div>
</div>

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
                        <label>Заказчик</label>
                        <p>{$customer_name}</p>
                    </div>
                    <div class="col-6 px-0 form-group mb-0">
                        <label>Исполнитель</label>
                        <p>{$partner_name}</p>
                    </div>
                </div>
                <div class="fomr-group d-flex justify-content-end mb-4">
                    <a href="#" class="btn btn-secondary mr-3" data-dismiss="modal">Отмена</a>
                    {'!AjaxForm'|snippet:[
                    'snippet' => 'SafeDeal',
                    'action' => 'deal/pay',
                    'hash' => $.get.d,
                    'form' => 'safedeal.deal.actions.form',
                    'btn_text' => 'Оплатить fake',
                    'successModalID' => 'modalSuccessPayDeal',
                    'errorModalID' => 'modalErrorPayDeal',
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

<div class="modal fade is-authorized" id="modalDisputeDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Оспорить сделку</h5>
                <p class="text-left">Приоритет нашего сервиса - Ваша безопасность, поэтому мы сделаем все возможное,
                    исключить риск мошенничества.</p>
                <p class="text-left">Если сделка пошла не по плану - заполните данные о себе для возможности передачи
                    дела в суд в случае, если партнер не будет согласен с Вашим мнением.</p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-4">
                    <label>Имя</label>
                    <input name="firstname" type="text" class="form-control" placeholder="Введите имя" form="dealDisputeForm" data-inputmask-regex="^[а-яА-Яa-zA-Z ]+$">
                </div>
                <div class="form-group mb-4">
                    <label>Фамилия</label>
                    <input name="lasname" type="text" class="form-control" placeholder="Введите фамилию" form="dealDisputeForm" data-inputmask-regex="^[а-яА-Яa-zA-Z ]+$">
                </div>
                <div class="form-group mb-4">
                    <label>Отчество</label>
                    <input name="middlename" type="text" class="w-100 form-control" placeholder="Введите отчество" form="dealDisputeForm" data-inputmask-regex="^[а-яА-Яa-zA-Z ]+$">
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <div class="col-2 form-group mb-0 px-0">
                        <label>Паспорт</label>
                        <input name="passport" type="text" class="w-100 form-control" placeholder="Серия" form="dealDisputeForm" data-inputmask-regex="^[а-яА-Яa-zA-Z0-9 ]+$">
                    </div>
                    <div class="col-5 form-groupmb-0 ">
                        <label></label>
                        <input name="passpor_number" type="text" class="form-control" placeholder="Номер" form="dealDisputeForm" data-inputmask-regex="^[0-9]+$">
                    </div>
                    <div class="col-5 form-group mb-0 px-0">
                        <label>Дата выдачи</label>
                        <input name="passpor_date" type="text" class="w-100 datepicker" placeholder="31/01/2022" form="dealDisputeForm">
                    </div>
                </div>
                <div class="form-group mb-4">
                    <label>Кем выдан</label>
                    <input name="passpor_issued" type="text" class="form-control" placeholder="Кем выдан" form="dealDisputeForm">
                </div>
                <div class="form-group mb-4">
                    <label>Город</label>
                    <select name="city" class="custom-select2">
                        {'Rowboat'|snippet:[
                        'table' => 'modx_site_cities',
                        'tpl' => 'tpl.select.cities.row',
                        'limit' => 0,
                        'sortBy' => 'name'
                        ]}
                    </select>
                </div>
                <div class="form-group mb-4">
                    <label>Улица, дом, корпус, квартира</label>
                    <input name="address" type="text" class="form-control" placeholder="Улица, дом, корпус, квартира" form="dealDisputeForm">
                </div>
                <div class="form-group mb-4">
                    <label>Опишите проблему</label>
                    <textarea name="description" class="form-control" placeholder="Описание проблемы"
                        rows="4" form="dealDisputeForm"></textarea>
                </div>
                <div class="fomr-group d-flex justify-content-end mb-4">
                    <a href="#" class="btn btn-secondary mr-3" data-dismiss="modal">Отмена</a>
                    {'!AjaxForm'|snippet:[
                    'snippet' => 'SafeDeal',
                    'action' => 'deal/dispute',
                    'hash' => $.get.d,
                    'form' => 'safedeal.deal.actions.form',
                    'form_id' => 'dealDisputeForm'
                    'btn_text' => 'Оспорить сделку',
                    'successModalID' => 'modalSuccessDisputeDeal',
                    ]}
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade js-hidden-refresh" id="modalSuccessDisputeDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Спор успешно открыт!</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/dispute_success.svg" alt="">
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalErrorDisputeDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">В данный момент Вы не можете оспорить эту сделку!</h5>
                <p class="mb-0">Обновите страницу и попробуйте еще раз.</p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/mail_sent.svg" alt="">
            </div>
        </div>
    </div>
</div>

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
