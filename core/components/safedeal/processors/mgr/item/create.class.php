<?php

class SafeDealItemCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'SafeDealItem';
    public $classKey = 'SafeDealItem';
    public $languageTopics = ['safedeal'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('safedeal_item_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('safedeal_item_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'SafeDealItemCreateProcessor';