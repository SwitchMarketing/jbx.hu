<?php 

namespace App\Libraries;

class BuildPage {
    

    public static $style;
    public static $script;

    protected static function Header($args = [], $tpl = "header")
	{

        // default FB img
        $fbphoto = base_url();

        // the user agent
        $agent = new \CodeIgniter\HTTP\UserAgent();

        // locale
        $locale = service('request')->getLocale();        
        
        $data = [
            'base'         => base_url(),   
            'locale'       => $locale,   
            'title'        => (isset($args['title']) && !empty($args['title']))         ? $args['title']        : config('Config\\AppConfig')->defaultTitle,
            'desc'         => (isset($args['desc']) && !empty($args['desc']))           ? $args['desc']         : config('Config\\AppConfig')->defaultDescription,						
            'section'      => (isset($args['section']))                                 ? $args['section']      : '',
            'data'         => (isset($args['data']))                                    ? $args['data']         : '',
            'devicetype'   => ($agent->isMobile())                                      ? 'mobile'              : 'desktop',
            'googleVerify' => getenv('GOOGLE_SITE_VERIFICATION') ?? '',
            'gtmId'        => getenv('GTM_ID') ?? '',
            'gmapApiKey'   => getenv('GMAP_API_KEY') ?? '',
            'fbVerify'     => getenv('FB_DOMAIN_VERIFICATION') ?? '',
            'fbPixel'      => getenv('FB_PIXEL_ID') ?? '',
            'css'          => minifier(self::$style.'.min.css')            
        ];

        // OG        
        $data['og_url']    = (isset($args['og_url']))                                  ? $args['og_url']       : current_url();
        $data['og_title']  = (isset($args['og_title']) && !empty($args['og_title']))   ? $args['og_title']     : $data['title'];
        $data['og_desc']   = (isset($args['og_desc']) && !empty($args['og_desc']))     ? $args['og_desc']      : $data['desc'];
        $data['og_img']    = (isset($args['og_img']) && !empty($args['og_img']))       ? $args['og_img']       : $fbphoto;  
        
        
		return view($tpl, $data);
    }
    
    protected static function Footer($tpl = "footer")
	{
        $data = [
            'js' => minifier(self::$script.'.min.js')
        ];
        
		return view($tpl, $data);
	}

    public static function render($tpl = 'home', $args = []) {

        self::$style = isset($args['style']) ? $args['style'] : 'styles';
        self::$script = isset($args['script']) ? $args['script'] : 'scripts';

        $header_tpl = isset($args['header_tpl']) ? $args['header_tpl'] : 'header';
        $footer_tpl = isset($args['footer_tpl']) ? $args['footer_tpl'] : 'footer';

        echo self::Header(isset($args['header']) ? $args['header'] : [], $header_tpl);
        echo view($tpl, isset($args['body']) ? $args['body'] : []);
        echo self::Footer($footer_tpl);

    }

     
    /**
     * maintenance
     * 
     * karbantartási oldal
     *
     * @param  mixed $args
     * @param  mixed $tpl
     * @return void
     */
    public static function maintenance($args = [], $tpl = 'maintenance') {
        echo view($tpl, $args);
    }


} 