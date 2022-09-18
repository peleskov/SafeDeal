<div class="modal fade is-authorized" id="modalMakeDeal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header d-block text-center">
                <h5 class="modal-title">Создать сделку</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {'!AjaxForm' | snippet : [
                    'snippet' => 'SafeDeal',
                    'action' => 'deal/create',
                    'form' => 'safedeal.deal.create.form',
                    'dealResourceID' => 26,
                    'docsDirPath' => $_modx->config.assets_path~'docs/usr_'~$_modx->user.id,
                    'docMaxSize' => 1048576,
                    'successMsg' => 'Сделка успешно создана!',
                    'successModalID' => 'successSafeDealCreateModal',
                    'errorMsg' => 'Форма содержит ошибки попробуйте еще раз.',
                    'emailTPL' => 'safedeal.deal.create.email',
                    'emailSubject' => $_modx->config.site_name~': Создана сделка!',
                ]}                
            </div>
        </div>
    </div>
</div>
