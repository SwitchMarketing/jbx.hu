<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class AppConfig extends BaseConfig
{

    /**
     * maintenanceMode
     *
     * @var int
     */
    public $maintenanceMode = (ENVIRONMENT == 'production');

    /**
     * companyName
     * 
     * @var string
     */
    public $companyName = 'JBX Trade Kft.';

    
    /**
     * companyAddress
     * 
     * @var string
     */
    public $companyAddress = '2040 Budaörs, Ébner György köz 4.';

    /**
     * companyTaxId
     * 
     * @var string
     */
    public $companyTaxId = '';

        
    /**
     * businessName
     * 
     * @var string
     */
    public $businessName = '';

    
    /**
     * businessAddress
     * 
     * @var string
     */
    public $businessAddress = '2040 Budaörs, Ébner György köz 4.';

    
    /**
     * siteName
     *
     * @var string
     */
    public $siteName  = 'jbx.hu';
        

    /**
     * siteEmail
     *
     * @var string
     */
    public $siteEmail = 'info@jbx.hu';

    /**
     * siteEmail2
     *
     * @var string
     */
    public $siteEmail2 = '';

    
    /**
     * leadEmail
     *
     * @var string
     */
    public $leadEmail = 'teszt@switchmarketing.hu';


    /**
     * sitePhone
     *
     * @var string
     */
    public $sitePhone = '+36 30 572 0752';

    /**
     * sitePhone2
     *
     * @var string
     */
    public $sitePhone2 = '';
        
    /**
     * defaultTitle
     *
     * @var string
     */
    public $defaultTitle = 'Axelent ipari gépbiztonsági rendszerek - JBX';

        
    /**
     * defaultDescription
     *
     * @var string
     */
    public $defaultDescription = 'Axelent - piacvezető ipari gépbiztonság. Ismerje meg a széleskörű és hatékony kábeltálca, biztonsági kerítés, ütközésvédelemi és raktározási rendszereinket.';


    /**
     * defaultKeywords
     * 
     * @var string
     */
    public $defaultKeywords = '';
}