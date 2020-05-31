<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class PriceInformer extends Module
{
    public function __construct()
    {
        $this->name = 'priceinformer';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Korovina Nataliya';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7.6.5',
            'max' => _PS_VERSION_,
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Price Informer');
        $this->description = $this->l('Price Informer description');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('MYMODULE_NAME')) {
            $this->warning = $this->l('No name provided');
        }
    }

    public function install()
    {
        return parent::install() && $this->registerHook('displayFooter');
    }

    public function hookDisplayFooter()
    {
        $priceFrom = (int)Configuration::get('PRICE_FROM');
        $priceTo = (int)Configuration::get('PRICE_TO');
        $db = \Db::getInstance();
        $sql = sprintf(
            'SELECT COUNT(*) FROM %sproduct WHERE price >= %d AND price <= %d',
            _DB_PREFIX_,
            $priceFrom,
            $priceTo
        );
        $count = $db->getValue($sql);

        return sprintf('Товаров в диапазоне цены<br> от %d до %d - %dшт.', $priceFrom, $priceTo, $count);
    }

    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name)) {
            $priceFrom = (int)Tools::getValue('PRICE_FROM');
            $priceTo = (int)Tools::getValue('PRICE_TO');
            Configuration::updateValue('PRICE_FROM', $priceFrom);
            Configuration::updateValue('PRICE_TO', $priceTo);
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }

        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
            ],
            'input'  => [
                [
                    'type'     => 'text',
                    'label'    => $this->l('Цена от'),
                    'name'     => 'PRICE_FROM',
                    'size'     => 20,
                    'required' => true,
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->l('Цена до'),
                    'name'     => 'PRICE_TO',
                    'size'     => 20,
                    'required' => true,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn -default pull - right',
            ],
        ];

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.' & configure = '.$this->name;

        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.' & configure = '.$this->name.' & save'.$this->name.
                    ' & token = '.Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex.' & token = '.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list'),
            ],
        ];

        $helper->fields_value['PRICE_FROM'] = Tools::getValue('PRICE_FROM', Configuration::get('PRICE_FROM'));
        $helper->fields_value['PRICE_TO'] = Tools::getValue('PRICE_TO', Configuration::get('PRICE_TO'));

        return $helper->generateForm($fieldsForm);
    }
}