app.component('claim-form', {
    template: $TEMPLATES['claim-form'],
    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('claim-form')
        return { text, messages }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
        phase: {
            type: Entity,
            required: true
        }
    },

    data() {
        groupFileSample = "formClaimUploadSample";
        groupFileUpload = "formClaimUpload";
        return {
            groupFileSample,
            groupFileUpload,
            newFile: null,
            uploadedFile: null,
            claim: {
                registration_id: $MAPAS.config.opportunityClaimForm.registrationId
            },
        }
    },
    
    methods: {
        shouldShowClaim() {
			const isEvaluation = this.phase.__objectType == 'evaluationmethodconfiguration';

			const isRegistrationOnly = this.phase.__objectType == 'opportunity' && !this.phase.evaluationMethodConfiguration;

			const phaseOpportunity = this.phase.__objectType == 'opportunity' ? this.phase : this.phase.opportunity;

			return phaseOpportunity.publishedRegistrations && (isRegistrationOnly || isEvaluation);
		},
       async acceptClaim() {
            this.entity.acceptClaim = 2;
            this.entity.save();
            this.reloadPage();
        },
        refuseClaim (){
            this.entity.acceptClaim = 3;
            this.entity.save();
            this.reloadPage();
        },
        deleteClaim() {
            this.entity.files?.[this.groupFileUpload].delete();
            this.entity.acceptClaim = 4;
            this.entity.save();
            this.reloadPage();
        },
        close(modal) {
            this.claim.message = '';
            modal.close();
        },
        open(modal) {
            this.claim.message = '';
            modal.open();
        },
        isActive() {
            if (this.entity.opportunity.status > 0 && this.entity.opportunity.publishedRegistrations && this.entity.opportunity.claimDisabled === "0") {
                return true;
            }
            return true;
        },
        setFile($event) {
            this.newFile = $event;
            this.entity.files[this.groupFileUpload] = this.newFile;
        },
        uploaded($event) {
            this.uploadedFile = $event;
        },
        deleteFile(){
            this.entity.files[this.groupFileUpload].delete();
        },
        reloadPage(timeout = 1500){
            setTimeout(() => {
                document.location.reload(true)
            }, timeout);
        },
    },

    computed: {
        isAdmin(){
            return $MAPAS.config.opportunityClaimForm.isAdmin;
        },
        canManipulate(){
            let canManipulate = false;
            const global = useGlobalState();
            if(this.entity.opportunity?.claimFrom && this.entity.opportunity?.claimTo) {
                if(this.entity.opportunity?.claimFrom.isPast() && this.entity.opportunity?.claimTo.isFuture()) {
                    canManipulate = true;
                }

                if(canManipulate) {
                    if(this.entity.currentUserPermissions['@control'] && this.entity.acceptClaim == 1 || this.entity.acceptClaim == 4) {
                        canManipulate = true
                    }
                }
            }

            return canManipulate;
        },
        modalTitle() {
            return this.text('Solicitar Recurso');
        },
        claimFromDate() {
            return this.entity.opportunity.claimFrom.date('numeric year') +" "+ this.entity.opportunity.claimFrom.time('long');
        },
        claimclaimTo() {
            return this.entity.opportunity.claimTo.date('numeric year') +" "+ this.entity.opportunity.claimTo.time('long');
        },
        filesSample() {
            return this.entity.opportunity.files?.[this.groupFileSample] || null;
        }
    }
});

