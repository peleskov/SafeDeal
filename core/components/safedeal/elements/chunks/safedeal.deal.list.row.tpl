<li class="w-100 nav-item d-flex flex-column flex-xl-row align-items-center mb-3 py-3 py-xl-4 px-xl-3 position-relative" data="{$idx}">
    <div class="col mb-3 mb-xl-0">
        <a href="{26|url:[]:['d' => $hash]}" class="btn btn-link">{$title} <span class="text-muted text-medium">(Вы {$customer_id == $_modx->user.id? 'Заказчик':'Исполнитель'})</span></a>
    </div>
    <div class="col col-xl-2 mb-3 mb-xl-0">
        <p class="text-muted text-medium mb-1 mb-lg-0">Сумма сделки</p>
        <p class="mb-0">{($price+$fee)|number:2:'.':','} р</p>
    </div>
    <div class="col col-xl-2">
        <p class="text-muted text-medium mb-1 mb-lg-0">Дата завершения</p>
        <p class="mb-0">{$deadline|date_format : '%d.%m.%Y'}</p>
    </div>
    <div class="col-3 d-flex align-items-center justify-content-end position-static">
        {switch $status}
        {case 0, 1}
        <span class="badge badge-await mr-xl-3">Ожидает подтверждения</span>
        {case 2}
        <span class="badge badge-await mr-xl-3">Ожидает оплаты</span>
        {case 3}
        <span class="badge badge-inwork mr-xl-3">В работе</span>
        {case 4}
        <span class="badge badge-await mr-xl-3">Ожидает согласования продления</span>
        {case 5}
        <span class="badge badge-await mr-xl-3">Ожидает согласия заказчика</span>
        {case 6}
        <span class="badge badge-closed mr-xl-3">Завершена</span>
        {/switch}
        <a href="{26|url:[]:['d' => $hash]}" class="btn btn-arrow d-none d-xl-inline-block"></a>
    </div>
    <a href="{26|url:[]:['d' => $hash]}" class="stretched-link"></a>
</li>
