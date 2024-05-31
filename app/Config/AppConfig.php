<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class AppConfig extends BaseConfig
{

    /**
     * maintenanceMode
     *
     * @var int
     */
    public $maintenanceMode = false;

    /**
     * companyName
     * 
     * @var string
     */
    public $companyName = 'JBX Trade Kft.';


    /**
     * companyFullName
     * 
     * @var string
     */
    public $companyFullName = 'JBX Trade Korlátolt Felelősségű Társaság';
    
    
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
    public $companyTaxId = '32539055-2-13';

    /**
     * companyRegNo
     * 
     * @var string
     */
    public $companyRegNo = '13-09-233581';
        
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
    public $sitePhone = '+36 70 559 1144';

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

    /**
     * socialLinkFacebook
     * 
     * @var string
     */
    public $socialLinkFacebook = 'https://www.facebook.com/people/JBX-ipari-g%C3%A9pbiztons%C3%A1g/61560428935686/';

    /**
     * socialLinkInstagram
     * 
     * @var string
     */
    public $socialLinkInstagram = 'https://www.instagram.com/jbx.hu/';

    /**
     * socialLinkLinkedIn
     * 
     * @var string
     */
    public $socialLinkLinkedIn = 'https://www.linkedin.com/company/jbx-hu';
}