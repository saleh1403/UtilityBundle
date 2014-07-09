<?php
namespace NyroDev\UtilityBundle\Services;

class VideoService extends AbstractService {

	public function getChacheKey($url, $prefix = '') {
		return $prefix.sha1($url);
	}
	
	public function data($url, $force = false) {
		$cache = $this->get('winzou_cache');
		/* @var $cache \winzou\CacheBundle\Cache\LifetimeFileCache */
		
		$cacheKey = $this->getChacheKey($url, 'videoUrlParser_');
		if ($force || !$cache->contains($cacheKey)) {
			
			$embedUrl = new \Embed\Url($url);
			$service = \Embed\Embed::create($embedUrl);
			/* @var $service \Embed\Adapters\Adapter */
			
			if ($service) {
				$data = array(
					'type'=>$service->getType(),
					'url'=>$service->getUrl(),
					'title'=>$service->getTitle(),
					'description'=>$service->getDescription(),
					'image'=>$service->getImage(),
					'code'=>$service->getCode(),
					'width'=>$service->getWidth(),
					'height'=>$service->getHeight(),
					'aspectRatio'=>$service->getAspectRatio(),
					'urlEmbed'=>null
				);
				if ($data['code'] && strpos($data['code'], '<iframe') === 0) {
					$dom = new \DOMDocument();
					$dom->loadHTML($data['code']);
					$data['urlEmbed'] = $dom->getElementsByTagName('iframe')->item(0)->getAttribute('src');
					if (strpos($data['urlEmbed'], 'youtube') !== false)
						$data['urlEmbed'].= '&wmode=opaque';
				}
			} else {
				$data = array();
			}
			
			$cache->save($cacheKey, $data, 24 * 60 * 60);
		} else {
			$data = $cache->fetch($cacheKey);
		}
		return $data;
	}

}

