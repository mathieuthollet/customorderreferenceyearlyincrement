<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomorderReferenceYearlyIncrement extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'customorderreferenceyearlyincrement';
        $this->tab = 'administration';
        $this->version = '1.0.1';
        $this->author = 'Mathieu Thollet';
        $this->need_instance = 0;
        $this->module_key = '2435d04b50870c06677cf617cb4977b3';

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Custom Order Reference Yearly Increment');
        $this->description = $this->l('Custom order reference with configurable pattern and increment that can be resetted  every year automatically');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module ?');

        $this->overrides = array(
            array('source' => _PS_MODULE_DIR_ . $this->name . '/override/classes/order/Order.php',
                'target' => _PS_OVERRIDE_DIR_ . 'classes/order/Order.php',
                'targetdir' => _PS_OVERRIDE_DIR_ . 'classes/'),
        );

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('CUSTOMORDERREFERENCEYEARLYINCREMENT_PATTERN', '{YYYY}{MM}{DD}{INCREMENT}');
        Configuration::updateValue('CUSTOMORDERREFERENCEYEARLYINCREMENT_NEXT_INCREMENT', 1);
        Configuration::updateValue('CUSTOMORDERREFERENCEYEARLYINCREMENT_INCREMENT_LENGTH', 8);
        Configuration::updateValue('CUSTOMORDERREFERENCEYEARLYINCREMENT_YEARLY_RESET_INCREMENT', true);
        $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'orders` CHANGE `reference` `reference` VARCHAR(64)';
        Db::getInstance()->execute($sql);
        return parent::install();
    }

    public function uninstall()
    {
        Configuration::deleteByName('CUSTOMORDERREFERENCEYEARLYINCREMENT_PATTERN');
        Configuration::deleteByName('CUSTOMORDERREFERENCEYEARLYINCREMENT_NEXT_INCREMENT');
        Configuration::deleteByName('CUSTOMORDERREFERENCEYEARLYINCREMENT_INCREMENT_LENGTH');
        Configuration::deleteByName('CUSTOMORDERREFERENCEYEARLYINCREMENT_YEARLY_RESET_INCREMENT');
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';
        if (((bool)Tools::isSubmit('submitCustomorderreferenceyearlyincrementModule')) == true) {
            $this->postProcess();
            $output = $this->displayConfirmation($this->l('Settings have been updated.'));
        }
        $this->context->smarty->assign('errors', $this->_errors);
        $this->context->smarty->assign('module_dir', $this->_path);
        $output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitCustomorderreferenceyearlyincrementModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l('Available tags :') . '<br/>' .
                            $this->l('{INCREMENT} : Increment number') . '<br/>' .
                            $this->l('{YYYY} : Year') . '<br/>' .
                            $this->l('{MM} : Month') . '<br/>' .
                            $this->l('{DD} : Day') . '<br/>',
                        'name' => 'CUSTOMORDERREFERENCEYEARLYINCREMENT_PATTERN',
                        'label' => $this->l('Order reference pattern'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'CUSTOMORDERREFERENCEYEARLYINCREMENT_NEXT_INCREMENT',
                        'label' => $this->l('Next increment number'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'CUSTOMORDERREFERENCEYEARLYINCREMENT_INCREMENT_LENGTH',
                        'label' => $this->l('Length of increment number'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Yearly reset increment'),
                        'name' => 'CUSTOMORDERREFERENCEYEARLYINCREMENT_YEARLY_RESET_INCREMENT',
                        'is_bool' => true,
                        'desc' => $this->l('The increment will resetted to 1 for the first command every year'),
                        'values' => array(
                            array(
                                'id' => 'yearly_reset_increment_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'yearly_reset_increment_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'CUSTOMORDERREFERENCEYEARLYINCREMENT_PATTERN' => Configuration::get('CUSTOMORDERREFERENCEYEARLYINCREMENT_PATTERN'),
            'CUSTOMORDERREFERENCEYEARLYINCREMENT_INCREMENT_LENGTH' => Configuration::get('CUSTOMORDERREFERENCEYEARLYINCREMENT_INCREMENT_LENGTH'),
            'CUSTOMORDERREFERENCEYEARLYINCREMENT_NEXT_INCREMENT' => Configuration::get('CUSTOMORDERREFERENCEYEARLYINCREMENT_NEXT_INCREMENT'),
            'CUSTOMORDERREFERENCEYEARLYINCREMENT_YEARLY_RESET_INCREMENT' => Configuration::get('CUSTOMORDERREFERENCEYEARLYINCREMENT_YEARLY_RESET_INCREMENT'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            if ($key == 'CUSTOMORDERREFERENCEYEARLYINCREMENT_INCREMENT_LENGTH' && !is_numeric(Tools::getValue($key))) {
                $this->_errors[] = $this->l('Increment length must be numeric');
            } elseif ($key == 'CUSTOMORDERREFERENCEYEARLYINCREMENT_NEXT_INCREMENT' && !is_numeric(Tools::getValue($key))) {
                $this->_errors[] = $this->l('Next increment must be numeric');
            } else {
                Configuration::updateValue($key, Tools::getValue($key));
            }
        }
    }
}
