<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-files-list
    mc-container
    mc-modal
    mc-icon
');
?>
<div v-if="isActive() && !isAdmin">
    <mc-modal :title="modalTitle" classes="opportunity-claim-form" button-classes="opportunity-claim-form__buttonlabel">
        <template #default>
            <div>
                <input type="file" @change="setFile" ref="fileUpload"> 
                <a href="">{{entity.files[groupFileUpload]?.name}}</a>
                <button @click="deleteFile()"><?php i::_e('Deletar') ?></button>
            </div>
            <div v-if="filesSample">
                <ul>
                    <li>
                        <a :href="filesSample.url" target="_blank" download><?php i::_e('Baixe o arquivo modelo para o anexo') ?> <mc-icon name="download"></mc-icon></a>
                    </li>
                </ul>
            </div>

            <div class="opportunity-claim-form__content">
                <h5 class="semibold opportunity-claim-form__label"><?php i::_e('Descreva abaixo os motivos do recurso') ?></h5>
                <textarea v-model="claim.message" id="message" class="opportunity-claim-form__textarea"></textarea>
            </div>
        </template>
        <template #actions="modal">
            <button class="button button--text delete-registration " @click="close(modal)"><?php i::_e('Cancelar') ?></button>
            <button class="button button--primary" @click="sendClain(modal)"><?php i::_e('Solicitar') ?></button>
        </template>
        <template #button="modal">
            <h5 class="opportunity-claim-form__resource bold"><?php i::_e('Discorda do resultado?') ?></h5>
            <div>
                <?php i::_e('Período do recurso de recurso'); ?> {{claimFromDate}} a {{claimclaimTo}}
            </div>
            <button class="button button--primary-outline" @click="open(modal)"><?php i::_e('Solicitar Recurso') ?></button>
        </template>
    </mc-modal>
</div>

<div v-if="filesUpload && isActive() && isAdmin">
    <div>
        <entity-files-list :entity="entity" group="formClaimUpload" title="<?php i::_e('Arquivo de recurso anexado') ?>"></entity-files-list>
    </div>
    <div>
        <button class="button button--primary" v-if="!entity.acceptClaim" @click="acceptClaim()"><?php i::_e('Aceitar arquivo') ?></button>
        <button class="button button--primary" @click="refuseClaim()"><?php i::_e('Recusar arquivo') ?></button>
    </div>
</div>