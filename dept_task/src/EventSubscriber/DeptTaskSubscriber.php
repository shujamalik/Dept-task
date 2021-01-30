<?php

namespace Drupal\dept_task\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DeptTaskSubscriber implements EventSubscriberInterface {

  public function loginwithauthtoken(GetResponseEvent $event) {
	  
	global $base_url;
    $current_user = \Drupal::currentUser();
	$roles = $current_user->getRoles();
	if(in_array('anonymous',$roles)){
		$token = \Drupal::request()->query->get('authtoken');
		if($token){
			$query = \Drupal::database()->select('user__field_auth_token', 'u');
			$query->fields('u',['entity_id']);
			$query->condition('u.field_auth_token_value', $token);
			$uid = $query->execute()->fetchfield();
			
			if(isset($uid)) {
				$user = User::load($uid);
				user_login_finalize($user);
				$response = new RedirectResponse($base_url.'/user');
				$response->send();
			}
		}
	}

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('loginwithauthtoken');
    return $events;
  }
}
?>