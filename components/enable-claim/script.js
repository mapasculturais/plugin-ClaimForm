app.component('enable-claim', {
    template: $TEMPLATES['enable-claim'],

    setup() {
        const text = Utils.getTexts('enable-claim');
        return { text };
    },
    props: {
        entity: {
            type: Entity,
            required: true
        }
    },
    data() {
        let isActiveClaim = this.entity.claimDisabled === "1" ? true : false;
        let activateAttachment = this.entity.activateAttachment === "1" ? true : false;
        groupFileSample = "formClaimUploadSample";

        return {
            groupFileSample,
            isActiveClaim,
            activateAttachment,
            timeOut: null,
            newFile: {},
        }
    },
    watch: {
        'isActiveClaim'(_new,_old){
            if(_new != _old){
                this.isActive(_new);
            }
        },
        'activateAttachment'(_new,_old){
            if(_new != _old){
                this.isActiveAttachment(_new);
            }
        }
    },
    methods: {
        setFile() {
            this.newFile = this.$refs.fileSample.files[0];
            this.upload();
        },
        isActive(active) {
            this.entity.claimDisabled = active ? 1 : 0;
        },
        isActiveAttachment(active) {
            this.entity.activateAttachment = active ? 1 : 0;
        },
        upload() {
            let data = {
                group: this.groupFileSample,
                description: this.newFile.description
            };

            this.entity.upload(this.newFile, data).then((response) => {
            });

            return true;
        },
        autoSave(){
            clearTimeout(this.timeout);
                this.timeout = setTimeout(()=>{
                    this.entity.save();
            },1500);
        },
    },
    computed: {
        filesSample() {
            return this.entity.files?.[this.groupFileSample] || []
        }
        
    }
})