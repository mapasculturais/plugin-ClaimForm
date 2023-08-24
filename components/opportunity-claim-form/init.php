<?php
$entity = $this->controller->requestedEntity;

$claimOpen = false;
$canManipulate = false; 
if($entity instanceof MapasCulturais\Entities\Registration){
    $today = new DateTime();
    if ($today >= $entity->opportunity->claimFrom && $today <= $entity->opportunity->claimTo) {
        $claimOpen = true;
    }
    
    if ($claimOpen) {
        if ((($app->user->profile->id == $entity->owner->id)) || ($app->user->profile->id == $entity->owner->user->profile->id) || $app->user->is('saasSuperAdmin')) {
            if(!$entity->acceptClaim){
                $canManipulate = true;
            }
        }
    }
}


$config = [
    'registrationId' => $entity->id,
    'canManipulate' => $canManipulate,
    'isAdmin' => $app->user->is('admin'),
];

$this->jsObject['config']['opportunityClaimForm'] = $config;