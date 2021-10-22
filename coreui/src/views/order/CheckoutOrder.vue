<template>
    <CRow>
        <CCol col="12">
            <CCard>
                <CCardHeader>
                    <h4>Order Checkout</h4>
                </CCardHeader>
                <CCardBody>
                    <!-- <CCard class="mb-0"> -->
                        <CRow>
                            <CCol col="12" md="6">
                                <!-- <CCardBody class="m-0"> -->
                                    <CInput label="Name" type="text" placeholder="Name" v-model="order.customer_name"></CInput>
                                    <CInput label="Email" type="email" placeholder="Email" v-model="order.customer_email"></CInput>
                                    <CTextarea label="Note" placeholder="Type something here" v-model="order.note"></CTextarea>
                                    <CSelect
                                        label="Payment"
                                        :value.sync="order.payment_type"
                                        :plain="true"
                                        :options="payment_type"
                                    />
                                    <CInput
                                        label="Promo"
                                        type="text"
                                        placeholder="Code"
                                        :description="promotion_warning"
                                        v-model="promotion.code"
                                        >
                                        <template #append>
                                            <CButton color="success" @click="checkPromotion()">Check</CButton>
                                        </template>
                                    </CInput>
                                <!-- </CCardBody> -->
                            </CCol>
                            <CCol col="12" md="6">
                                <!-- <CCardBody class="m-0"> -->
                                    <table class="table">
                                        <tr>
                                            <th>Item</th>
                                            <th class="text-right" style="width:125px">Qty</th>
                                            <th class="text-right">Price</th>
                                            <th class="text-right">Total</th>
                                        </tr>
                                        <tr v-for="(item, $index) in order_items" :key="$index">
                                            <td>{{ item.productName }}</td>
                                            <td class="text-right">{{ item.quantity }}</td>
                                            <td class="text-right">{{ item.price }}</td>
                                            <td class="text-right">{{ item.totalPrice }}</td>
                                        </tr>
                                        <tr v-if="order.promotion_id">
                                            <th colspan="3" >Sub Total</th>
                                            <th class="text-right">{{ order.total_price }}</th>
                                        </tr>
                                        <tr v-if="order.promotion_id">
                                            <th colspan="3" >Discount</th>
                                            <th class="text-right">{{ order.discount }}</th>
                                        </tr>
                                        <tr>
                                            <th colspan="3" >Total</th>
                                            <th class="text-right">{{ order.final_price }}</th>
                                        </tr>
                                    </table>
                                <!-- </CCardBody> -->
                            </CCol>
                        </CRow>
                    <!-- </CCard> -->
                </CCardBody>
                <CCardFooter>
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
                    <CButton color="secondary" @click="back()">Back</CButton>
                    <CButton class="float-right" color="primary" @click="createOrder()">Check Out</CButton>
                </CCardFooter>
            </CCard>
        </CCol>
    </CRow>
</template>

<script>
import axios from 'axios'
import InfiniteLoading from 'vue-infinite-loading'
export default {
    name: 'CheckoutOrder',
    data () {
        return {
            order_items: [],
            payment_type: [
                {label: 'Cash', value:0},
                {label: 'Bank Transfer', value:3},
            ],
            order: {
                customer_name: '',
                customer_email: '',
                note: '',
                discount: '',
                discount_type: '',
                promotion_id: '',
                payment_type: 0,
                final_price: 0,
            },
            promotion: {
                code: ''
            },
            promotion_warning: '',
            message: '',
            dismissSecs: 7,
            dismissCountDown: 0,
            showDismissibleAlert: false,
        }
    },
    methods: {
        checkPromotion(){
            let self = this
            axios.post(  this.$apiAdress + '/api/order/checkPromotion?token=' + localStorage.getItem("api_token"),
                {
                    code: self.promotion.code,
                }
            )
            .then(function (response) {
                if(response.data.status){
                    self.order.promotion_id = response.data.promotion_id
                    self.order.discount = response.data.disc
                    self.order.final_price = response.data.final_price
                    self.order.total_price = response.data.total_price
                }else{
                    self.promotion = {}
                    self.order.promotion_id = ''
                    self.order.discount = ''
                    self.order.final_price = ''
                    self.order.total_price = ''
                }
                self.promotion_warning = response.data.mess
            })
        },
        getOrderItems($state) {
            let self = this
            // if(self.order.uuid !== ''){
                axios.get(this.$apiAdress + '/api/order/getCart?token=' + localStorage.getItem("api_token"), {
                    params: {
                        page: self.orderPage,
                        // uuid: self.order.uuid
                    },
                }).then(({ data }) => {
                        // self.order_items.push(...data.order_items.data);
                        self.order.final_price = data.finalPrice
                        self.order_items = data.order_items
                });
            // }
        },
        back(){
            this.$router.go(-1)
        },
        createOrder() {
            let self = this;
            axios.post(  this.$apiAdress + '/api/order/createOrder?token=' + localStorage.getItem("api_token"), {
                    params: {
                        customer_name : self.customer_name,
                        customer_email : self.customer_email,
                        note: self.note,
                        discount: self.discount,
                        discount_type: self.discount_type,
                        promotion_id: self.promotion_id,
                        payment_type: self.payment_type,
                        final_price: self.final_price,
                    }
                }
            )
            .then(function (response) {

            }).catch(function (error) {
                if(error.response.data.message == 'The given data was invalid.'){
                    self.message = '';
                    for (let key in error.response.data.errors) {
                        if (error.response.data.errors.hasOwnProperty(key)) {
                            self.message += error.response.data.errors[key][0] + '  ';
                        }
                    }
                    self.showAlert();
                }else{
                    console.log(error);
                    self.$router.push({ path: 'login' });
                }
            });
        },
        countDownChanged (dismissCountDown) {
            this.dismissCountDown = dismissCountDown
        },
        showAlert () {
            this.dismissCountDown = this.dismissSecs
        },
    },
    mounted(){
        this.getOrderItems();
    },
}
</script>
