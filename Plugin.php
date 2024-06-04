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

        $app->hook('app.modules.init:before', function (&$modules) use ($config) {
            if (($key = array_search('OpportunityClaimForm', $modules)) !== false) {
                unset($modules[$key]);
            }
        });
        
        parent::__construct($config);
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
                $registration->acceptClaim = false;
                $registration->save(true);
                $message = sprintf(i::__("O arquivo anexado ao recurso da sua inscrição %s na oportunidade %s foi rejeitado."),$registration->number, $registration->opportunity->firstPhase->name);
                $self->createNotification($registration->owner->user, $message);
                $self->sendMailRefusedClaim($registration, $message);
                $app->enableAccessControl();
            }
        });

        // Adiciona seção de configuração do formulário de recurso dentro da configuração do formulário
        $app->hook("view.partial(singles/opportunity-registrations--export):after", function () {
            $this->part('claim-configuration', ['opportunity' => $this->controller->requestedEntity]);
        });

        // Define a permissãopara inserir arquivos na inscrição apos a mesma estar fechada
        $app->hook('can(RegistrationFile.<<*>>)', function ($user, &$result) use ($app, $self) {
            /** @var \MapasCulturais\Entities\RegistrationFile $this */
            if ($this->group === "formClaimUpload" && $this->owner->opportunity->publishedRegistrations) {
                if(!$this->owner->acceptClaim || $app->user->is('saasSuperAdmin')){
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

        /** Envia o e-mail de recurso para o administrador */
        $app->hook('entity(Registration).file(formClaimUpload).insert:after', function () use ($self) {
            $self->sendMailClaim($this);
            $self->sendMailClaimCertificate($this);
        });

        $app->hook('app.plugins.preInit:before', function () use($app) {
            
            $app->hook("component(opportunity-phase-config-data-collection):bottom", function(){
                $this->part('opportunity-claim-config');
            });

            $app->hook('component(opportunity-phases-timeline).registration:end', function () {
                $registration = $this->controller->requestedEntity;
                if($registration->canUser('sendClaimMessage')){
                    $this->part('opportunity-claim-form-component');
                }
            });
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
            'type' => 'bool',
            'default' => false,
            'serialize' => function($value){
                return $value == 1 ? true : false;
            },
            'unserialize' => function($value){
                return $value == 1 ? true : false;
            }
        ]);

        $app->registerFileGroup(
            'registration',
            new FileGroup(
                'formClaimUpload',
                ['^application/pdf'],
                'O arquivo não é valido',
                true
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
                'O arquivo não é valido',
                true
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
    
    public function sendMailRefusedClaim($registration, $message)
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
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' => $registration->owner->emailPrivado,
                'subject' => $message['title'],
                'body' => $message['body']
            ]);
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
            $app->createAndSendMailMessage([
                'from' => $app->config['mailer.from'],
                'to' => $registration->owner->emailPrivado,
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
                if(!$registration->acceptClaim){
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
