app.component('opportunity-claim-form', {
    template: $TEMPLATES['opportunity-claim-form'],
    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('opportunity-claim-form')
        return { text, messages }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },
    },

    data() {
        groupFileSample = "formClaimUploadSample";
        groupFileUpload = "formClaimUpload";
        newFile = {};
        return {
            groupFileSample,
            groupFileUpload,
            newFile,
            claim: {
                registration_id: $MAPAS.config.opportunityClaimForm.registrationId
            },
        }
    },
    
    methods: {
        acceptClaim() {
            this.entity.acceptClaim = true;
            this.entity.save();
        },
        refuseClaim (){
            this.entity.files?.[this.groupFileUpload].delete();
            this.entity.acceptClaim = false;
            this.entity.save();
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
        async sendClain(modal) {
            let api = new API();
            let url = Utils.createUrl('opportunity', 'sendOpportunityClaimMessage');
            await api.POST(url, this.claim).then(data => {
                this.messages.success(this.text('Solicitação de recurso enviada'));
                this.close(modal);
            });
        },
        upload() {
            let data = {
                    group: this.groupFileUpload,
                    description: this.newFile.description
                };
            this.entity.upload(this.newFile, data).then((response) => {
                    // console.log(response)
            });
            return true;
        },
    },

    computed: {
        isAdmin(){
            return $MAPAS.config.opportunityClaimForm.isAdmin;
        },
        canManipulate(){
            return $MAPAS.config.opportunityClaimForm.canManipulate;
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
        },
        filesUpload(){
            return this.entity.files?.[this.groupFileUpload] || null;
        }
    },
});

