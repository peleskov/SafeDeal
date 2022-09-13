<form{$form_id? ' id="'~$form_id~'"':''}>
    {*Важно для AjaxForm <button> на новой строке*}
    <button class="{$btn_class? :'btn btn-primary'}" type="submit">{$btn_text}</button>
</form>