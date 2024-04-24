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
    public $companyName = 'JBX';

    
    /**
     * companyAddress
     * 
     * @var string
     */
    public $companyAddress = '1134 Budapest, Váci út 22-24';

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
    public $businessAddress = '1134 Budapest, Váci út 22-24';

    
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
    public $sitePhone = '+36-1-555-6666';

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