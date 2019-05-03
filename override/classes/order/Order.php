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

class Order extends OrderCore
{
    public static function generateReference()
    {
        $reference = Configuration::get('CUSTOMORDERREFERENCEYEARLYINCREMENT_PATTERN');
        if (strstr($reference, '{INCREMENT}') !== false) {
            $increment = (int)Configuration::get('CUSTOMORDERREFERENCEYEARLYINCREMENT_NEXT_INCREMENT');
            if (Configuration::get('CUSTOMORDERREFERENCEYEARLYINCREMENT_YEARLY_RESET_INCREMENT')) {
                $sql = 'SELECT COUNT(`id_order`) FROM `' . _DB_PREFIX_ . 'orders` WHERE DATE_FORMAT(`date_add`, "%Y") = ' . (int)date('Y');
                if (Db::getInstance()->getValue($sql) == 0) {
                    $increment = 1;
                }
            }
            Configuration::updateValue('CUSTOMORDERREFERENCEYEARLYINCREMENT_NEXT_INCREMENT', $increment + 1);
            while (Tools::strlen($increment) < Configuration::get('CUSTOMORDERREFERENCEYEARLYINCREMENT_INCREMENT_LENGTH')) {
                $increment = '0' . (string)$increment;
            }
            $reference = str_replace('{INCREMENT}', $increment, $reference);
        }
        $reference = str_replace('{YYYY}', date('Y'), $reference);
        $reference = str_replace('{MM}', date('m'), $reference);
        $reference = str_replace('{DD}', date('d'), $reference);
        if ($reference != '') {
            return $reference;
        } else {
            return parent::generateReference();
        }
    }
}
