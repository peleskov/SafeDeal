<?php

/**
 * The home manager controller for SafeDeal.
 *
 */
class SafeDealHomeManagerController extends modExtraManagerController
{
    /** @var SafeDeal $SafeDeal */
    public $SafeDeal;


    /**
     *
     */
    public function initialize()
    {
        $this->SafeDeal = $this->modx->getService('SafeDeal', 'SafeDeal', MODX_CORE_PATH . 'components/safedeal/model/');
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['safedeal:default'];
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('safedeal');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->SafeDeal->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->SafeDeal->config['jsUrl'] . 'mgr/safedeal.js');
        $this->addJavascript($this->SafeDeal->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->SafeDeal->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->SafeDeal->config['jsUrl'] . 'mgr/widgets/items.grid.js');
        $this->addJavascript($this->SafeDeal->config['jsUrl'] . 'mgr/widgets/items.windows.js');
        $this->addJavascript($this->SafeDeal->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->SafeDeal->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addHtml('<script type="text/javascript">
        SafeDeal.config = ' . json_encode($this->SafeDeal->config) . ';
        SafeDeal.config.connector_url = "' . $this->SafeDeal->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "safedeal-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="safedeal-panel-home-div"></div>';

        return '';
    }
}