<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-file
    mc-icon
    mc-link
    mc-modal
');
?>

<div class="opportunity-enable-claim">
    <h4 class="bold opportunity-enable-claim__title">Recurso</h4>
    <div class="opportunity-enable-claim__input ">
        <input type="checkbox" id="resource" v-model="isActiveClaim" @click="autoSave()" />
        <label for="resource"><?= i::__("Habilitar solicitação de recurso") ?></label>
    </div>
    <div v-if="isActiveClaim">
        <div class="opportunity-enable-claim__email">
            <label class="opportunity-enable-claim__label" for="input">
                <h5 class="semibold opportunity-enable-claim__subtitle"><?= i::__("Insira o email que receberá as solicitações") ?></h5>
            </label>
            <div class="opportunity-enable-claim__save">
                <input type="text" v-model="entity.claimEmail" @change="autoSave()" />
            </div>
            <div>
                <input type="checkbox" v-model="activateAttachment" @change="autoSave()" />
                <label for="resource"><?= i::__("Habilitar anexo de arquivo para recurso") ?></label><br>
            </div>
        </div>
        <div v-if="activateAttachment">
            <div class="grid-12">
                <entity-field :entity="entity" classes="col-3 sm:col-12" prop="claimFrom"></entity-field>
                <entity-field :entity="entity" classes="col-3 sm:col-12" prop="claimTo"></entity-field>
            </div>
            <div>
                <div>
                    <h4><?php i::_e('Envie um modelo de documento que deve ser anexado pelos solicitantes') ?></h4>
                    <div>
                        <entity-file :entity="entity" groupName="formClaimUploadSample" title="" editable></entity-file>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>