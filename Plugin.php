<?php

namespace ClaimForm;

use DateTime;
use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Definitions\FileGroup;

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

        $self = $this;

        $app->hook('GET(registration.acceptClaim)', function () use ($app) {
            $this->requireAuthentication();

            if ($app->user->is('admin')) {
                $file = $app->repo('file')->find($this->data['id']);
                $registration = $file->owner;

                $app->disableAccessControl();
                $registration->acceptClaim = true;
                $registration->save(true);
                $app->enableAccessControl();

                $url = $app->createUrl('inscricao');
                $app->redirect($url . "/" . $registration->id);
            }
        });

        $app->hook("entity(RegistrationFile).remove:after", function() use ($app){
            if ($this->group === "formClaimUpload"){
                $registration = $this->owner;
                $app->disableAccessControl();
                $registration->acceptClaim = false;
                $registration->save(true);
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
            if ($this->status > 0 && $opportunity->publishedRegistrations && !$opportunity->claimDisabled && $this->canUser('view')) {
                $canUser = true;
            } else {
                $canUser = false;
            }
        });

        /** Coloca o template do recurso dentro da tela de inscrição */
        $app->hook('template(registration.view.registration-sidebar-rigth):end', function () use ($app, $self) {
            /** @var Theme $this */
            $this->enqueueStyle('app', 'claim-form-css', 'css/claim-form.css');
            $app->view->jsObject['angularAppDependencies'][] = 'ng.claim-form';
            $app->view->enqueueScript('app', 'ng.claim-form', 'js/ng.claim-form.js');

            $registration = $this->controller->requestedEntity;
            if ($registration->canUser('sendClaimMessage')) {
                $canManipulate = $self->canManipulate($registration);
                $claim_open = $self->claimOpen($registration);
                $this->part('claim-form-upload', ['entity' => $registration, 'canManipulate' => $canManipulate, 'claim_open' => $claim_open]);
            };
        });

        /** Envia o e-mail de recurso para o administrador */
        $app->hook('entity(Registration).file(formClaimUpload).insert:after', function ($args) use ($self) {
            $self->sendMailClaim($this);
            $self->sendMailClaimCertificate($this);
        });

        // adiciona o botão de recurso na lista de
        $app->hook("template(opportunity.<<*>>.user-registration-table--registration--status):end", function ($registration, $opportunity) {
            if ($registration->canUser('sendClaimMessage')) {
                $this->part('message-registration-status-table');
            }
        });
    }

    public function register()
    {
        /** @var App $app */
        $app = App::i();

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
            'type' => 'date',
            'unserialize' => function ($value) {
                return new DateTime($value);
            }
        ]);

        $this->registerOpportunityMetadata('claimTo', [
            'label' => \MapasCulturais\i::__('Data de fim do recurso'),
            'type' => 'date',
            'unserialize' => function ($value) {
                return new DateTime($value);
            }
        ]);

        $this->registerRegistrationMetadata('acceptClaim', [
            'label' => \MapasCulturais\i::__('Idicação de aceite do recurso por parte do administrador'),
            'type' => 'bool',
            'default' => false
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

            if ($app->user->equals($registration->owner->user) || $app->user->is('saasSuperAdmin')) {
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
