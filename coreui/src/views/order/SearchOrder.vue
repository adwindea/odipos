<template>
    <CRow>
        <CCol>
            <CCard>
                <CCardHeader>
                    <h4>Check Order</h4>
                </CCardHeader>
                <CCardBody>
                    <CRow>
                        <CCol>
                            <CAlert
                                :show.sync="dismissCountDown"
                                color="primary"
                                fade
                            >
                                ({{dismissCountDown}}) {{ message }}
                            </CAlert>
                        </CCol>
                    </CRow>
                    <CRow>
                        <CCol>
                            <CInput
                                placeholder="Order Token"
                                label="Order Token"
                                v-model="token"
                            >
                                <template #append>
                                    <CButton color="primary" @click="checkOrder()">
                                        Search
                                    </CButton>
                                </template>
                            </CInput>
                        </CCol>
                    </CRow>
                </CCardBody>
            </CCard>
        </CCol>
    </CRow>
</template>

<script>
import axios from 'axios'
export default {
    name: 'CheckoutOrder',
    data () {
        return {
            token: '',
            detailCollapse: false,
            message: '',
            dismissSecs: 7,
            dismissCountDown: 0,
            showDismissibleAlert: false,
        }
    },
    methods: {
        countDownChanged (dismissCountDown) {
            this.dismissCountDown = dismissCountDown
        },
        showAlert () {
            this.dismissCountDown = this.dismissSecs
        },
        checkOrder() {
            let self = this
            axios.get(this.$apiAdress + '/api/order/searchOrder?token=' + localStorage.getItem("api_token"), {
                params: {
                    token: self.token,
                },
            }).then(({ data }) => {
                if(data.status == 'ok'){
                    let uuid = data.uuid
                    self.$router.push({ name:"Order Detail", params: { uuid: `${uuid.toString()}` } })
                }else{
                    self.message = data.message
                    self.showAlert()
                }
            });
        }
    },
}
</script>
