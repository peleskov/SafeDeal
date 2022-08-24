<form>
    <div class="form-group mb-4">
        <label>Я выступаю как</label>
        <div class="d-flex">
            <div class="custom-control custom-radio-button">
                <input type="radio" id="RadioCustomer" name="is_customer" class="custom-control-input"
                    checked="" value="1">
                <label class="custom-control-label" for="RadioCustomer">Покупатель</label>
            </div>
            <div class="custom-control custom-radio-button">
                <input type="radio" id="RadioSeller" name="is_customer" class="custom-control-input">
                <label class="custom-control-label" for="RadioSeller" value="0">Продавец</label>
            </div>
        </div>
    </div>
    <div class="form-group mb-4">
        <label>Заключаю сделку как</label>
        <div class="d-flex">
            <div class="custom-control custom-radio-button">
                <input type="radio" id="RadioIndividual" name="is_company"
                    class="custom-control-input" checked="" value="0">
                <label class="custom-control-label" for="RadioIndividual" data-toggle="collapse"
                    data-target="#collapseCompanyName" aria-expanded="false">Физическое лицо</label>
            </div>
            <div class="custom-control custom-radio-button">
                <input type="radio" id="RadioCompany" name="is_company" class="custom-control-input" value="1">
                <label class="custom-control-label" for="RadioCompany" data-toggle="collapse"
                    data-target="#collapseCompanyName" aria-expanded="false">Компания</label>
            </div>
        </div>
    </div>
    <div class="collapse" id="collapseCompanyName">
        <div class="form-group mb-4">
            <label>Введите название компании</label>
            <input type="text" class="form-control" name="company_name"
                placeholder="Введите название компании">
        </div>
    </div>
    <div class="form-group mb-4">
        <label>Название сделки</label>
        <input type="text" class="form-control mb-1" name="title"
            placeholder="Введите названние сделки">
        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseDealDescription"
            aria-expanded="false">Добавить описание</button>
    </div>
    <div class="collapse" id="collapseDealDescription">
        <div class="form-group mb-4">
            <label>Описание</label>
            <textarea class="form-control" name="description" placeholder="Описание сделки"
                rows="4"></textarea>
        </div>
    </div>
    <div class="col-6 px-0 form-group mb-2">
        <label>Сумма сделки</label>
        <div class="position-relative">
            <input class="form-control transfer-amount js-dealprice" name="price" type="text" data-fee="{$_modx->config.company_fee? :'0.05'}"
                placeholder="Введите сумму" data-inputmask="'alias': 'currency', 'placeholder': '0.00'">
            <span class="ruble">₽</span>
        </div>
    </div>
    <p class="text-muted text-medium mb-4">Общая комиссия составит: <span class="js-dealfee">0.00</span> ₽</p>
    <div class="form-group mb-4">
        <label>Кто платит комиссию?</label>
        <div class="d-flex">
            <div class="custom-control custom-radio mr-3">
                <input type="radio" id="RadioWhoPayI" name="fee_payer" class="custom-control-input"
                    checked="" value="0">
                <label class="custom-control-label" for="RadioWhoPayI">Я</label>
            </div>
            <div class="custom-control custom-radio mr-3">
                <input type="radio" id="RadioWhoPayPartner" name="fee_payer"
                    class="custom-control-input" value="1">
                <label class="custom-control-label" for="RadioWhoPayPartner">Партнер</label>
            </div>
            <div class="custom-control custom-radio">
                <input type="radio" id="RadioWhoPayHalf" name="fee_payer" class="custom-control-input" value="2">
                <label class="custom-control-label" for="RadioWhoPayHalf">50/50</label>
            </div>
        </div>
    </div>
    <div class="col-6 px-0 form-group mb-4">
        <label>Срок сделки</label>
        <input type="text" class="w-100 datepicker today-min" name="deadline" placeholder="30/10/2022">
    </div>
    <div class="fomr-group d-flex justify-content-end mb-4">
        <a href="#" class="btn btn-secondary mr-3" data-dismiss="modal">Отмена</a>
        <button class="btn btn-primary" type="submit">Создать сделку</button>
    </div>
</form>
