<div class="modal fade js-hidden-refresh" id="successSafeDealCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Сделка успешно создана!</h5>
                <p class="mb-0">Следить за ней вы можете в своем личном кабинете в разделе “мои сделки”!</p>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-4">
                    <label>Ссылка на сделку</label>
                    <div class="d-flex align-items-center">
                        <div class="col px-0 mr-3">
                            <input type="text" id="DealLink" class="form-control">
                        </div>
                        <div class="col-auto px-0 position-relative">
                            <button type="button" class="btn btn-clipboard" data-clipboard-target="#DealLink"
                                title="Ссылка скопирована"></button>
                        </div>
                    </div>
                </div>
                <img class="maxw-100" src="{$_modx->config.assets_url}theme/imgs/deal_success.svg" alt="">
            </div>
        </div>
    </div>
</div>
