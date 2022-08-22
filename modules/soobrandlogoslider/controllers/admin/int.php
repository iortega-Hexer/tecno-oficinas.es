<?php
/**
* 2012-2016 PrestaSOO Inc
*
* NOTICE OF LICENSE
*
* This is a commercial license
* Do not allow to re-sales, edit without permission from PrestaSOO.
* International Registered Trademark & Property of PrestaSOO
*
* @author    Frankie <addons@prestasoo.com>
* @copyright PrestaSOO.com
* @license   Commercial License. All right reserved
*/

if (!class_exists('SOOUpgrade', false))
{
class SOOUpgrade
{
	public function file_get_contents_curl($url) {
	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

	    $data = curl_exec($ch);
	    curl_close($ch);

	    return $data;
	}
	public static function update($authentical)
	{
		$return = true;
		$upgrade = Tools::file_get_contents('https://www.prestasoo.com/docs/upgrade/authentical.php?authentical='.$authentical);
		if ($upgrade == '')
			$upgrade = SOOUpgrade::file_get_contents_curl('https://www.prestasoo.com/docs/upgrade/authentical.php?authentical='.$authentical);
		$modula = explode('|', $upgrade);
		$kout = count($modula);
		for ($i = 0; $i < $kout; $i++)
		{
			if (!file_exists(_PS_MODULE_DIR_.'/'.$modula[$i]))
			{
				$cacho = Tools::file_get_contents(_PS_ROOT_DIR_.Module::CACHE_FILE_MUST_HAVE_MODULES_LIST);
				if (strpos($cacho, 'PrestaSOO.com') == false)
				{
				@chmod(_PS_ROOT_DIR_.Module::CACHE_FILE_MUST_HAVE_MODULES_LIST, 0777);
				$cache = (bool)file_put_contents(_PS_ROOT_DIR_.Module::CACHE_FILE_MUST_HAVE_MODULES_LIST, '<?xml version="1.0" encoding="UTF-8"?><modules>'.Tools::file_get_contents('https://www.prestasoo.com/docs/upgrade/'.$modula[$i].'/update.txt').'</modules>');
				}
				elseif (strpos($cacho, $modula[$i]) == false)
				{
					$cacho = str_replace('</modules>', '', $cacho);
					@chmod(_PS_ROOT_DIR_.Module::CACHE_FILE_MUST_HAVE_MODULES_LIST, 0777);
					$cache = (bool)file_put_contents(_PS_ROOT_DIR_.Module::CACHE_FILE_MUST_HAVE_MODULES_LIST, $cacho);
					$tempkontent = Tools::file_get_contents('https://www.prestasoo.com/docs/upgrade/'.$modula[$i].'/update.txt');
					if ($tempkontent == '')
					$tempkontent = SOOUpgrade::file_get_contents_curl('https://www.prestasoo.com/docs/upgrade/'.$modula[$i].'/update.txt');
					$cache = (bool)file_put_contents(_PS_ROOT_DIR_.Module::CACHE_FILE_MUST_HAVE_MODULES_LIST, $tempkontent.'</modules>', FILE_APPEND);
				}
				else
				{
					return false;
				}
			}
			if ($i == ($kout - 1))
			@chmod(_PS_ROOT_DIR_.Module::CACHE_FILE_MUST_HAVE_MODULES_LIST, 0644);
		}
		return $return;
	}

    public static function version($authentical)
    {
        $return = true;
        $version = Tools::file_get_contents('https://www.prestasoo.com/docs/version/authentical.php?authentical='.$authentical);
        if ($version == '') {
            $version = SOOUpgrade::file_get_contents_curl('https://www.prestasoo.com/docs/version/authentical.php?authentical='.$authentical);
        }
        return $version;
    }
    public static function kontent($authentical)
    {
        $return = true;
        $kontent = Tools::file_get_contents('https://www.prestasoo.com/docs/version/kontent.php?authentical='.$authentical);
        if ($kontent == '') {
            $kontent = SOOUpgrade::file_get_contents_curl('https://www.prestasoo.com/docs/version/kontent.php?authentical='.$authentical);
        }
        return $kontent;
    }
}
}
?>