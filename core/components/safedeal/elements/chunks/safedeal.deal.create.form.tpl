<form enctype="multipart/form-data">
    <input type="hidden" name="partner_id">
    <div class="form-group mb-4">
        <label>Я выступаю как</label>
        <div class="d-flex">
            <div class="custom-control custom-radio-button">
                <input type="radio" id="RadioCustomer" name="is_customer" class="custom-control-input" checked=""
                    value="1">
                <label class="custom-control-label" for="RadioCustomer">Покупатель</label>
            </div>
            <div class="custom-control custom-radio-button">
                <input type="radio" id="RadioSeller" name="is_customer" class="custom-control-input">
                <label class="custom-control-label" for="RadioSeller" value="0">Продавец</label>
            </div>
        </div>
    </div>
    <div class="form-group mb-4">
        <label>Название сделки</label>
        <input type="text" class="form-control mb-1" name="title" placeholder="Введите названние сделки">
        {*
        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseDealDescription"
            aria-expanded="true">Добавить описание</button>
        *}
    </div>
    <div class="collapse show" id="collapseDealDescription">
        <div class="form-group mb-4">
            <label>Описание</label>
            <textarea class="form-control" name="description" placeholder="Описание сделки" rows="4"></textarea>
        </div>
    </div>
    <div class="col-6 px-0 form-group mb-2">
        <label>Сумма сделки</label>
        <div class="position-relative">
            <input class="form-control transfer-amount js-dealprice" name="price" type="text"
                data-fee="{$_modx->config.company_fee? :'0.05'}" placeholder="Введите сумму"
                data-inputmask="'alias': 'currency', 'placeholder': '0.00'">
            <span class="ruble">₽</span>
        </div>
    </div>
    <p class="text-muted text-medium mb-4">Общая комиссия составит: <span class="js-dealfee">0.00</span> ₽</p>
    <div class="col-6 px-0 form-group mb-4">
        <label>Срок сделки</label>
        <input type="text" class="w-100 datepicker today-min" name="deadline" placeholder="30/10/2022">
    </div>
    <div class="form-group mb-4">
        <label>Загрузите документы <span class="d-block text-medium text-small">Вы можете прикрепить не больше 5 файлов, размер каждого файла не должен превышать 1 Мб, допускаются следующие типы фалов: pdf, txt, doc, docx, xls, xlsx</span></label>
        <div class="custom-file">
            <input type="file" class="custom-file-input js-input-docs" id="inputDocs" name="docs[]" multiple="">
            <label class="custom-file-label" for="inputDocs">Выбрать файлы</label>
        </div>
        <ul class="nav flex-column js-docs text-muted text-small"></ul>
    </div>
    <div class="fomr-group mb-4">
        <div class="custom-control custom-radio">
            <input type="checkbox" id="RadioAgree1" name="iagreecheck" class="custom-control-input" checked="">
            <label class="custom-control-label" for="RadioAgree1">Я согласен с условиями <a
                    class="btn btn-link d-inline" href="pravovaya-informacziya.html">пользовательского
                    соглашения</a></label>
        </div>
    </div>
    <div class="fomr-group d-flex justify-content-end mb-4">
        <a href="#" class="btn btn-secondary mr-3" data-dismiss="modal">Отмена</a>
        <button class="btn btn-primary" type="submit">Создать сделку</button>
    </div>
</form>