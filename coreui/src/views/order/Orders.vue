<template>
    <CRow>
        <CCol col="12">
            <CModal
            title="Close Order"
            :show.sync="closeModal"
            >
                <CRow>
                    <CCol>
                        <p>Are you sure to close order {{order_number}}?</p>
                    </CCol>
                </CRow>
                <CRow class="pt-3 text-center" v-if="payimage">
                    <CCol>
                        <h5>Total bill {{final_price}} IDR</h5>
                        <img class="pt-2" style="max-width:100%;" :src="payimage">
                    </CCol>
                </CRow>
                <footer slot="footer">
                    <CButton color="danger" class="text-center" @click="saveOrder(uuid)">Close Order</CButton>
                </footer>
            </CModal>
            <CModal
            title="Delete Order"
            :show.sync="deleteModal"
            >
                <p>Are you sure to delete order {{order_number}}?</p>
                <footer slot="footer">
                    <CButton color="danger" class="text-center" @click="deleteOrder(uuid)">Delete Order</CButton>
                </footer>
            </CModal>
            <transition name="slide">
                <CCard>
                    <CCardHeader>
                        <h4>
                            Orders
                            <CButton color="primary" @click="addOrder()" class="mb-3 float-right"><CIcon name="cilPlus"></CIcon></CButton>
                        </h4>
                    </CCardHeader>
                    <CCardBody>
                        <CDataTable
                            hover
                            sorter
                            :items="items"
                            :fields="fields"
                            :items-per-page="10"
                            items-per-page-select
                            :tableFilter="{ placeholder: 'Type to search'}"
                            pagination
                        >
                            <template #token="{item}">
                                <td class="text-center">
                                    {{item.token}}<br>{{item.order_number}}
                                </td>
                            </template>
                            <template #status="{item}">
                                <td class="text-center">
                                    <CBadge v-if="item.status == 1 && item.is_paid == 0" color="success">Waiting Payment</CBadge>
                                    <CBadge v-if="item.status == 1 && item.is_paid == 1" color="warning">Waiting Confirmation</CBadge>
                                    <CBadge v-if="item.status == 2" color="danger">Closed</CBadge>
                                    <br>
                                    <span v-if="item.payment_type == 0">Cash</span>
                                    <span v-if="item.payment_type == 1">QRIS</span>
                                    <span v-if="item.payment_type == 3">Transfer</span>
                                </td>
                            </template>
                            <template #customer_name="{item}">
                                <td>
                                    {{item.customer_name}}<br>
                                    {{item.customer_email}}
                                </td>
                            </template>
                            <template #price_total="{item}">
                                <td>
                                    {{item.price_total}}
                                </td>
                            </template>
                            <template #discount="{item}">
                                <td>
                                    {{item.discount}}
                                </td>
                            </template>
                            <template #final_price="{item}">
                                <td>
                                    {{item.final_price}}
                                </td>
                            </template>
                            <template #note="{item}">
                                <td>
                                    {{item.note}}
                                </td>
                            </template>
                            <template #payimage="{item}">
                                <td>
                                    <a :href="item.payimage" target="_blank"><img style="height:32px;" :src="item.payimage"></a>
                                </td>
                            </template>
                            <template #cashier="{item}">
                                <td>
                                    {{item.cashier}}
                                </td>
                            </template>
                            <template #action="{item}">
                                <td class="text-center">
                                    <CButton v-if="item.status < 2" color="primary" @click="closeOrder(item.uuid,item.order_number,item.payimage,item.final_price)"><CIcon name="cilCheck"></CIcon></CButton>
                                    <CButton v-if="item.status < 2 && userRole.includes('admin')" color="danger" @click="delOrder(item.uuid,item.order_number)"><CIcon name="cilTrash"></CIcon></CButton>
                                    <CButton v-if="item.status == 2" color="info" @click="printReceipt(item.uuid)"><CIcon name="cilPrint"></CIcon></CButton>
                                    <CButton color="warning" @click="showOrder(item.uuid)"><CIcon name="cilMagnifyingGlass"></CIcon></CButton>
                                </td>
                            </template>
                        </CDataTable>
                    </CCardBody>
                </CCard>
            </transition>
        </CCol>
    </CRow>
</template>

<script>
import axios from 'axios'
export default {
    name: 'Orders',
    data () {
        return {
            fields: [
                {key:'token', _classes:'text-center'},
                {key:'status', _classes:'text-center'},
                {key:'customer_name'},
                {key:'price_total'},
                {key:'discount'},
                {key:'final_price'},
                {key:'note'},
                {key:'cashier'},
                {key:'payimage', _classes:'text-center'},
                {key:'action', _classes:'text-center'}
            ],
            items: [],
            uuid: '',
            role: '',
            order_number: '',
            payimage: '',
            finalprice: '',
            closeModal: false,
            deleteModal: false,
            userRole: localStorage.getItem('roles'),
        }
    },
    methods: {
        getOrder (){
            let self = this;
            axios.get(  this.$apiAdress + '/api/order?token=' + localStorage.getItem("api_token"))
            .then(function (response) {
                self.items = response.data.orders;
                self.role = response.data.role;
                self.you = response.data.you;
            }).catch(function (error) {
                console.log(error);
                self.$router.push({ path: '/login' });
            });
        },
        addOrder(){
            this.$router.push({path: 'order/create'});
        },
        showOrder(uuid){
            this.$router.push({path: `order/${uuid.toString()}/orderDetail`});
        },
        printReceipt(uuid){
            this.$router.push({path: `/print/${uuid.toString()}/receipt`})
        },
        closeOrder(uuid,order_number,payimage,final_price){
            this.order_number = order_number
            this.uuid = uuid
            this.payimage = payimage
            this.final_price = final_price
            this.closeModal = true
        },
        delOrder(uuid,order_number){
            this.order_number = order_number
            this.uuid = uuid
            this.deleteModal = true
        },
        saveOrder(uuid,stat){
            let self = this
            axios.post(  this.$apiAdress + '/api/order/saveOrder?token=' + localStorage.getItem("api_token"),
                {
                    uuid: uuid
                }
            )
            .then(function (response) {
                self.closeModal = false
                self.getOrder()
            })
        },
        deleteOrder(uuid){
            let self = this
            axios.post(  this.$apiAdress + '/api/order/delete?token=' + localStorage.getItem("api_token"),
                {
                    uuid: uuid
                }
            )
            .then(function (response) {
                self.deleteModal = false
                self.getOrder()
            })
        }
    },
    mounted(){
        this.getOrder();
    }
}
</script>
