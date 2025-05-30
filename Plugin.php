<?php

namespace ClaimForm;

use DateTime;
use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Definitions\FileGroup;
use MapasCulturais\Entities\Notification;

class Plugin extends \MapasCulturais\Plugin
{

    public function __construct(array $config = array())
    {
        /** @var App $app */
        $app = App::i();
             
        
        parent::__construct($config);
    }

    public static function preInit()
    {
        $app = App::i();

        $app->hook('app.modules.init:before', function (&$modules) {
            if (($key = array_search('OpportunityClaimForm', $modules)) !== false) {
                unset($modules[$key]);
            }
        });
    }
    

    public function _init()
    {
        /** @var App $app */
        $app = App::i();
       
        //load css
        $app->hook('GET(<<*>>.<<*>>)', function() use ($app) {
            $app->view->enqueueStyle('app-v2', 'ClaimForm', 'css/plugin-claim-form.css');
        });

        $self = $this;
        $app->_config['mailer.templates']['claim_refused'] = [
            'title' => i::__("Arquivo de recurso rejeitado"),
            'template' => 'claim_refused.html'
        ];

        $app->hook("entity(RegistrationFile).remove:after", function() use ($app, $self){
            if ($this->group === "formClaimUpload"){
                $registration = $this->owner;
                $app->disableAccessControl();
                $registration->acceptClaim = 4;
                $registration->save(true);
                $message = sprintf(i::__("Vocẽ já pode inserir um novo arquivo de recurso para inscrição %s na oportunidade %s."),$registration->number, $registration->opportunity->firstPhase->name);
                $self->createNotification($registration->owner->user, $message);
                $self->sendMailEvolutionClaim($registration, $message, i::__("Recurso reaberto para a inscrição {$registration->number}"));
                $app->enableAccessControl();
            }
        });

        $app->hook("entity(Registration).save:after", function() use ($app, $self){
            $registration = $this;
            if($registration->acceptClaim == 3) {
                $message = sprintf(i::__("O arquivo anexado ao recurso da sua inscrição %s na oportunidade %s foi rejeitado."),$registration->number, $registration->opportunity->firstPhase->name);
                $self->createNotification($registration->owner->user, $message);
                $self->sendMailEvolutionClaim($registration, $message, i::__("Arquivo de recurso rejeitado"));
            }

            if($registration->acceptClaim == 2) {
                $message = sprintf(i::__("O arquivo anexado ao recurso da sua inscrição %s na oportunidade %s foi acaito."),$registration->number, $registration->opportunity->firstPhase->name);
                $self->createNotification($registration->owner->user, $message);
                $self->sendMailEvolutionClaim($registration, $message, i::__("Arquivo de recurso aceito"));
            }
        });

        // Adiciona seção de configuração do formulário de recurso dentro da configuração do formulário
        $app->hook("component(opportunity-phase-config-evaluation):bottom", function () {
            $this->part('opportunity-claim-config', ['opportunity' => $this->controller->requestedEntity]);
        });

        // Define a permissãopara inserir arquivos na inscrição apos a mesma estar fechada
        $app->hook('can(RegistrationFile.<<*>>)', function ($user, &$result) use ($app, $self) {
            /** @var \MapasCulturais\Entities\RegistrationFile $this */
            if ($this->group === "formClaimUpload" && $this->owner->opportunity->publishedRegistrations) {
                if(!$this->owner->acceptClaim || $this->owner->acceptClaim == 1 || $this->owner->acceptClaim == 4 || $this->owner->opportunity->canUser('@control')){
                    $result = true;
                }
            }
        });

        /** Altera permissão canUserView  */
        $app->hook('entity(Registration).canUser(sendClaimMessage)', function ($user, &$canUser) use ($self) {
            $opportunity = $this->opportunity;
            // se o status for maior que 0 significa que a inscrição foi enviada
            if ($this->status > 0 && $opportunity->publishedRegistrations && $opportunity->claimDisabled && $this->canUser('view')) {
                $canUser = true;
            } else {
                $canUser = false;
            }
        });

        $app->hook('PATCH(registration.single):data', function() use ($app, $self) {
            $request = $this->data;

            $app->hook('entity(Registration).canUser(modify)', function ($user, &$canUser) use ($self, $request, $app) {
                /** @var Registration $this */
                $opportunity = $this->opportunity;
                if (in_array('acceptClaim', array_keys($request)) && $this->status > 0 && $opportunity->publishedRegistrations && $opportunity->canUser('@control')) {
                    $canUser = true;
                } 
            });

            $app->hook('entity(RegistrationMeta).canUser(create)', function ($user, &$canUser) use ($self, $request, $app) {
                /** @var Registration $this */
                $opportunity = $this->owner->opportunity;
                if (in_array('acceptClaim', array_keys($request)) && $this->owner->status > 0 && $opportunity->publishedRegistrations && $opportunity->canUser('@control')) {
                    $canUser = true;
                } 
            });
        });

        /** Envia o e-mail de recurso para o administrador */
        $app->hook('entity(Registration).file(formClaimUpload).insert:after', function () use ($self) {
            $self->sendMailClaim($this);
            $self->sendMailClaimCertificate($this);
        });

        $app->hook("component(opportunity-phase-config-data-collection):bottom", function(){
            $this->part('opportunity-claim-config');
        });

        $app->hook('component(opportunity-phases-timeline).registration:end', function () {
            $this->part('opportunity-claim-form-component');
        });

        $app->hook("module(OpportunityPhases).dataCollectionPhaseData", function(&$data) {
            $data .= ',claimEmail,claimDisabled,claimFrom,claimTo';
        });
        
        $app->hook('mapas.printJsObject:before', function () use($app) {
            if(isset($this->jsObject['registrationPhases'])) {
                foreach($this->jsObject['registrationPhases'] as &$reg) {
                    $registration = $app->repo('Registration')->find($reg['id']);
                    $reg['currentUserPermissions'] = $reg['currentUserPermissions'] ?? $registration->currentUserPermissions;
                    $reg['currentUserPermissions']['sendClaimMessage'] = $registration->canUser('sendClaimMessage');
                }
            }
        },1000);

        $app->hook('view.requestedEntity(Registration).result', function (&$result) {
            if($registration = $this->controller->requestedEntity) {
                $result['currentUserPermissions']['sendClaimMessage'] = $registration->canUser('sendClaimMessage');
            }
        });
    }

    public function validateErros($controller)
    {

        $checkFields = [
            'message' => i::__('Digite um texto explicando seu recurso'), 
            'fileId' => i::__('Anexe um arquivo')
        ];

        $errors = [];
        foreach($checkFields as $field => $message){
            if(empty($controller->data[$field]) || $controller->data[$field] == ""){
                $errors[] = $message;
            }
        }

        return $errors;
    }
    

    public function register()
    {
        /** @var App $app */
        $app = App::i();

        $this->registerOpportunityMetadata('activateAttachment', [
            'label' => i::__('Habilitar anexo de arquivo para recurso'),
            'type' => 'select',
            'options' => (object)[
                '0' => i::__('Anexo habilitado'),
                '1' => i::__('Anexo desabilitado'),
            ]
        ]);

        $this->registerOpportunityMetadata('claimDisabled', [
            'label' => i::__('Desabilitar formulário de recursos'),
            'type' => 'select',
            'options' => (object)[
                '0' => i::__('formulário de recurso habilitado'),
                '1' => i::__('formulário de recurso desabilitado'),
            ]
        ]);

        $this->registerOpportunityMetadata('claimEmail', [
            'label' => \MapasCulturais\i::__('Email de destino do formulário de recursos'),
            'validations' => [
                'v::email()' => \MapasCulturais\i::__('Email inválido')
            ]
        ]);

        $this->registerOpportunityMetadata('claimFrom', [
            'label' => \MapasCulturais\i::__('Data de inicio do recurso'),
            'type' => 'datetime',
            'unserialize' => function ($value) {
                return $value ? new DateTime($value ?: 'now') : $value;
            }
        ]);

        $this->registerOpportunityMetadata('claimTo', [
            'label' => \MapasCulturais\i::__('Data de fim do recurso'),
            'type' => 'datetime',
            'unserialize' => function ($value) {
                return $value ? new DateTime($value ?: 'now') : $value;
            }
        ]);

        $this->registerRegistrationMetadata('acceptClaim', [
            'label' => \MapasCulturais\i::__('Idicação de aceite do recurso por parte do administrador'),
            'type' => 'int',
            'default' => 1,
        ]);

        $app->registerFileGroup(
            'registration',
            new FileGroup(
                'formClaimUpload',
                [
                    'application/pdf',
                    'image/(gif|jpeg|pjpeg|png)',
    
                    // ms office
                    'application/msword',
                    'application/vnd\.openxmlformats-officedocument\.wordprocessingml\.document',
                    'application/vnd\.ms-excel',
                    'application/vnd\.openxmlformats-officedocument\.spreadsheetml\.sheet',
                    'application/vnd\.ms-powerpoint',
                    'application/vnd\.openxmlformats-officedocument\.presentationml\.presentation',
                    'application/vnd\.openxmlformats-officedocument\.presentationml\.slideshow',
    
                    // libreoffice / openoffice
                    'application/vnd\.oasis\.opendocument\.chart',
                    'application/vnd\.oasis\.opendocument\.formula',
                    'application/vnd\.oasis\.opendocument\.graphics',
                    'application/vnd\.oasis\.opendocument\.image',
                    'application/vnd\.oasis\.opendocument\.presentation',
                    'application/vnd\.oasis\.opendocument\.spreadsheet',
                    'application/vnd\.oasis\.opendocument\.text',
                    'application/vnd\.oasis\.opendocument\.text-master',
                    'application/vnd\.oasis\.opendocument\.text-web',
                ],
                'O arquivo não é um documento válido, verifique o tipo de arquivo.',
                true,
                private: true

            )
        );

        $app->registerFileGroup(
            'opportunity',
            new FileGroup(
                'formClaimUploadSample',
                [
                    '^application/pdf',
                    'application/vnd\.openxmlformats-officedocument\.wordprocessingml\.document',
                    'application/vnd\.openxmlformats-officedocument\.wordprocessingml\.template',
                    'application/vnd\.ms-word\.document\.macroEnabled\.12',
                    'application/vnd\.ms-word\.template\.macroEnabled\.12',
                    'application/x-abiword',
                    'application/msword'
                ],
                'O arquivo não é válido',
                true,
            )
        );
    }

    public function createNotification($user, $message)
    {
        $message = $message;
        $notification = new Notification;
        $notification->user = $user;
        $notification->message = $message;
        $notification->save(true);
    }
    
    public function sendMailEvolutionClaim($registration, $message, $subject = null)
    {
        /** @var App $app */
        $app = App::i();

        $opportunity = $registration->opportunity;

        $dataValue = [
            'message' => $message,
            'userName' => $registration->owner->name,
        ];

        $message = $app->renderMailerTemplate('claim_refused', $dataValue);

        if (array_key_exists('mailer.from', $app->config) && !empty(trim($app->config['mailer.from']))) {
            /*
             * Envia e-mail para o administrador da Oportunidade
             */
            if($email = $registration->owner->emailPrivado ? $registration->owner->emailPrivado : $registration->owner->emailPublico) {
                $app->createAndSendMailMessage([
                    'from' => $app->config['mailer.from'],
                    'to' => $email,
                    'subject' => $subject ?? $message['title'],
                    'body' => $message['body']
                ]);
            }
        }
    }
    

    public function sendMailClaim($entity)
    {
        /** @var App $app */
        $app = App::i();

        $registration = $entity->owner;

        $registration->checkPermission('sendClaimMessage');

        $opportunity = $registration->opportunity;

        $dataValue = [
            'opportunityName' => $opportunity->name,
            'opportunityUrl' => $opportunity->singleUrl,
            'registrationNumber' => $registration->number,
            'registrationUrl' => $registration->singleUrl,
            'date' => date('d/m/Y H:i:s', $_SERVER['REQUEST_TIME']),
            'message' => $entity->description,
            'userName' => $app->user->profile->name,
            'userUrl' => $app->user->profile->url,
            'file' =>  $entity->url
        ];

        $message = $app->renderMailerTemplate('claim_form', $dataValue);

        $email_to = $opportunity->claimEmail;

        if (!$email_to) {
            $email_to = $opportunity->owner->emailPrivado ? $opportunity->owner->emailPrivado : $opportunity->owner->emailPublico;
        }

        if (array_key_exists('mailer.from', $app->config) && !empty(trim($app->config['mailer.from']))) {
            /*
             * Envia e-mail para o administrador da Oportunidade
             */
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' => $email_to,
                'subject' => $message['title'],
                'body' => $message['body']
            ]);
        }
    }

    public function sendMailClaimCertificate($entity)
    {
        /** @var App $app */
        $app = App::i();

        $registration = $entity->owner;

        $registration->checkPermission('sendClaimMessage');

        $opportunity = $registration->opportunity;

        $dataValue = [
            'opportunityName' => $opportunity->name,
            'opportunityUrl' => $opportunity->singleUrl,
            'registrationNumber' => $registration->number,
            'registrationUrl' => $registration->singleUrl,
            'date' => date('d/m/Y H:i:s', $_SERVER['REQUEST_TIME']),
            'message' => $entity->description,
            'userName' => $registration->owner->name,
            'userUrl' => $registration->owner->url ?: "#",
            'file' =>  $entity->url
        ];

        $message = $app->renderMailerTemplate('claim_certificate', $dataValue);

        if (array_key_exists('mailer.from', $app->config) && !empty(trim($app->config['mailer.from']))) {
            /*
             * Envia e-mail para o administrador da Oportunidade
             */
            $email = $registration->owner->emailPrivado ?: $registration->owner->emailPublico ?: $registration->owner->user->email;
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' =>  $email,
                'subject' => $message['title'],
                'body' => $message['body']
            ]);
        }
    }

    public function canManipulate($registration)
    {
        $app = App::i();
        if ($this->claimOpen($registration)) {
            if ((($app->user->profile->id == $registration->owner->id)) || ($app->user->profile->id == $registration->owner->user->profile->id) || $app->user->is('saasSuperAdmin')) {
                if($registration->acceptClaim == 1 || $registration->acceptClaim == 4){
                    return true;
                }
            }
        }

        return false;
    }

    public function claimOpen($registration)
    {
        $today = new DateTime();
        if ($today >= $registration->opportunity->claimFrom && $today <= $registration->opportunity->claimTo) {
            return true;
        }
        return false;
    }
}
